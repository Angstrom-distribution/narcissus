#!/bin/sh
# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008, 2009 - all rights reserved 

MACHINE=$1
IMAGENAME=$2
IMAGETYPE=$3

if [ -e ${PWD}/conf/host-config ] ; then
	. ${PWD}/conf/host-config
fi

if [ -e ${PWD}/conf/${MACHINE}/machine-config ] ; then
	. ${PWD}/conf/${MACHINE}/machine-config
fi

function make_sdimg() 
{
if [ "${MACHINE}" = "beagleboard" ] ; then
	MD5SUM_SD="$(md5sum ${TARGET_DIR}/boot/uImage | awk '{print $1}')"	
	if [ -e ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}.img.gz ] ; then
		echo "Cached SD image found, using that"	
		cp ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}.img.gz ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd.img.gz
	else
		echo "No cached SD image found, generating new one"
		zcat ${WORKDIR}/conf/${MACHINE}/sd/sd.img.gz > sd.img
		/sbin/fdisk -l -u sd.img
		
		BYTES_PER_SECTOR="$(/sbin/fdisk -l -u sd.img | grep Units | awk '{print $9}')"
		VFAT_SECTOR_OFFSET="$(/sbin/fdisk -l -u sd.img | grep img1 | awk '{print $3}')"

		LOOP_DEV="/dev/loop0"
		echo "/sbin/losetup -v -o $(expr ${BYTES_PER_SECTOR} "*" ${VFAT_SECTOR_OFFSET}) ${LOOP_DEV} sd.img"
		/sbin/losetup -v -o $(expr ${BYTES_PER_SECTOR} "*" ${VFAT_SECTOR_OFFSET}) ${LOOP_DEV} sd.img
	
		echo "mount ${LOOP_DEV}"
		mount ${LOOP_DEV}
		"echo copying files to vfat"
		cp -v ${WORKDIR}/conf/${MACHINE}/sd/MLO /mnt/narcissus/sd_image1/MLO
		cp -v ${WORKDIR}/conf/${MACHINE}/sd/u-boot.bin /mnt/narcissus/sd_image1/u-boot.bin
		if [ -e ${TARGET_DIR}/boot/uImage-2.6* ] ;then 
			cp -v ${TARGET_DIR}/boot/uImage-2.6* /mnt/narcissus/sd_image1/uImage
			echo "Copied from /boot"
		else
			cp -v ${WORKDIR}/conf/${MACHINE}/sd/uImage.bin /mnt/narcissus/sd_image1/uImage
			echo "Using uImage from narcissus, no uImage found in rootfs"
		fi

		echo "files in sd image:" $(ls /mnt/narcissus/sd_image1/)
		export MD5SUM_SD="$(md5sum /mnt/narcissus/sd_image1/uImage | awk '{print $1}')"
		echo "MD5 of file in vfat partition: ${MD5SUM_SD}"
		
		umount ${LOOP_DEV}
	
		/sbin/losetup -d ${LOOP_DEV}
		gzip -c sd.img > ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd.img.gz
		cp ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd.img.gz ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}.img.gz
	fi
fi
}

function do_tar() 
{
	echo "tarring up filesystem"
	( cd ${TARGET_DIR}
	  tar cjf ../${IMAGENAME}-${MACHINE}.tar.bz2 .
	  RETVAL=$?
	  make_sdimg )
}

function do_ubifs()
{
	echo "creating ubi volume"
	( cd ${TARGET_DIR}/../
	  echo \[ubifs\] > ubinize.cfg
	  echo mode=ubi >> ubinize.cfg 
	  echo image=${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubifs >> ubinize.cfg
	  echo vol_id=0 >> ubinize.cfg
	  echo vol_type=dynamic >> ubinize.cfg
	  echo vol_name=${UBI_VOLNAME} >> ubinize.cfg
	  echo vol_flags=autoresize >> ubinize.cfg
	  echo "running: mkfs.ubifs -r ${IMAGE_ROOTFS} -o ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubifs ${MKUBIFS_ARGS} && ubinize -o ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubi ${UBINIZE_ARGS} ubinize.cfg"
	  mkfs.ubifs -r ${IMAGE_ROOTFS} -o ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubifs ${MKUBIFS_ARGS} && ubinize -o ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubi ${UBINIZE_ARGS} ubinize.cfg )
}

function do_jffs2()
{
	echo "creating jffs2 image"
	mkfs.jffs2 -x lzo --root=${IMAGE_ROOTFS} --faketime --output=${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.jffs2 ${EXTRA_IMAGECMD_jffs2}
}

function do_ext2()
{
	echo "creating ext2 image"
	export ROOTFS_SIZE="$(du -ks ${IMAGE_ROOTFS} | awk '{print 65536 + $1}')"
	echo "running: genext2fs -b ${ROOTFS_SIZE} -d ${IMAGE_ROOTFS} ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ext2 ${EXTRA_IMAGECMD_ext2}"
	genext2fs -b ${ROOTFS_SIZE} -d ${IMAGE_ROOTFS} ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ext2 ${EXTRA_IMAGECMD_ext2}
}

if ! [ -e ${TARGET_DIR}/etc/opkg.conf ] ; then
	echo "Initial filesystem not found, something went wrong in the configure step!"
	exit 0
fi

echo "installing initial /dev directory"
mkdir -p ${TARGET_DIR}/dev
bin/makedevs -r ${TARGET_DIR} -D conf/devtable.txt

if [ -e ${TARGET_DIR}/log.txt ] ; then
	rm ${TARGET_DIR}/log.txt
fi

echo "Running preinsts"

for i in ${TARGET_DIR}/usr/lib/opkg/info/*.preinst; do
	if [ -f $i ] && ! sh $i; then
		echo "Running: opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf flag unpacked `basename $i .preinst`"
		opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf flag unpacked `basename $i .preinst`
	fi
done 

echo "Running postinsts"

for i in ${TARGET_DIR}/usr/lib/opkg/info/*.postinst; do
	if [ -f $i ] && ! sh $i configure; then
		echo "Running: opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf flag unpacked `basename $i .postinst`"
		opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf flag unpacked `basename $i .postinst`
	fi
done 

echo "removing opkg index files"
rm ${TARGET_DIR}/var/lib/opkg/* || true
rm ${TARGET_DIR}/usr/lib/opkg/lists/* || true

# Add timestamp
date "+%m%d%H%M%Y" > ${TARGET_DIR}/etc/timestamp

# Add opendns to resolv.conf
rm -f ${TARGET_DIR}/etc/resolv.conf
echo "nameserver 208.67.222.222" > ${TARGET_DIR}/etc/resolv.conf
echo "nameserver 208.67.220.220" >> ${TARGET_DIR}/etc/resolv.conf

echo "$(date -u +%s) ${MACHINE} $(du ${TARGET_DIR} -hs | awk '{print $1}')" >> ${WORKDIR}/deploy/stats.txt || true

echo "<div id=\"imgsize\">" $(du ${TARGET_DIR} -hs) "</div>\n"

case ${IMAGETYPE} in
	jffs2)
		do_jffs2;;
	ubifs)
		do_ubifs;;
	tbz2)
		do_tar;;
	ext2)
		do_ext2;;
	*)
		do_tar;;
esac

echo "removing target dir"
rm -rf ${TARGET_DIR}

exit ${RETVAL}


