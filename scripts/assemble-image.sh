#!/bin/sh
# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008, 2009 - all rights reserved 

MACHINE=$1
IMAGENAME=$2

WORKDIR="${PWD}"

TARGET_DIR="${WORKDIR}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"
export D="${TARGET_DIR}"
export OPKG_OFFLINE_ROOT="${TARGET_DIR}"
export OFFLINE_ROOT="${TARGET_DIR}"
export IPKG_OFFLINE_ROOT="${TARGET_DIR}"
export PATH=${WORKDIR}/bin:${PATH}

if ! [ -e ${TARGET_DIR}/etc/opkg.conf ] ; then
	print "Initial filesystem not found, something went wrong in the configure step!"
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

echo "tarring up filesystem"
( cd  ${TARGET_DIR} ; nice -n10 tar cjf ../${IMAGENAME}-${MACHINE}.tar.bz2 . ; RETVAL=$? 

if [ "${MACHINE}" = "beagleboard" ] ; then
	MD5SUM_SD="$(md5sum ${TARGET_DIR}/boot/uImage | awk '{print $1}')"	
	if [ -e ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}.img.gz ] ; then
		echo "Cached SD image found, using that"	
		cp ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}.img.gz ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd.img.gz
	else
		echo "No cached SD image found, generating new one"
		zcat ${WORKDIR}/conf/${MACHINE}/sd/sd.img.gz > sd.img
		/sbin/fdisk -l -u sd.img

		/sbin/losetup -v -o 32256 /dev/loop1 sd.img
	
		echo "mount /dev/loop1"
		mount /dev/loop1
		"echo copying files to vfat"
		cp -v ${WORKDIR}/conf/${MACHINE}/sd/MLO /mnt/narcissus/sd_image1/MLO
		cp -v ${WORKDIR}/conf/${MACHINE}/sd/u-boot.bin /mnt/narcissus/sd_image1/u-boot.bin
		if [ -e ${TARGET_DIR}/boot/uImage ] ;then 
			cp -v ${TARGET_DIR}/boot/uImage /mnt/narcissus/sd_image1/uImage
		else
			cp -v ${WORKDIR}/conf/${MACHINE}/sd/uImage.bin /mnt/narcissus/sd_image1/uImage
		fi
		umount /dev/loop1
	
		/sbin/losetup -d /dev/loop1
		gzip -c sd.img > ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd.img.gz
		cp ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd.img.gz ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}.img.gz
	fi
fi
)

echo "removing target dir"
rm -rf ${TARGET_DIR}

exit ${RETVAL}

