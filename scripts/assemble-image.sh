#!/bin/sh
# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008 - all rights reserved 

MACHINE=$1
IMAGENAME=$2

WORKDIR="${PWD}"

TARGET_DIR="${WORKDIR}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"
export D="${TARGET_DIR}"
export OPKG_OFFLINE_ROOT="${TARGET_DIR}"
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

echo "Running postinstinsts"

for i in ${TARGET_DIR}/usr/lib/opkg/info/*.postinst; do
	if [ -f $i ] && ! sh $i configure; then
		echo "Running: opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf flag unpacked `basename $i .postinst`"
		opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf flag unpacked `basename $i .postinst`
	fi
done 

ls ${TARGET_DIR}/sbin -la

echo "removing opkg index files"
rm ${TARGET_DIR}/var/lib/opkg/* || true

echo "<div id=\"imgsize\">" $(du ${TARGET_DIR} -hs) "</div>\n"

echo "tarring up filesystem"
( cd  ${TARGET_DIR} ; tar cjf ../${IMAGENAME}-${MACHINE}.tar.bz2 . ; RETVAL=$? 

if [ "${MACHINE}" = "beagleboard" ] ; then
	zcat ${WORKDIR}/conf/${MACHINE}/sd/sd.img.gz > sd.img
	/sbin/fdisk -l -u sd.img

	/sbin/losetup -v -o 32256 /dev/loop1 sd.img
	
	echo "mount /dev/loop1"
	mount /dev/loop1
	"echo copying files to vfat"
	cp -v ${WORKDIR}/conf/${MACHINE}/sd/MLO /mnt/narcissus/sd_image1/MLO
	cp -v ${WORKDIR}/conf/${MACHINE}/sd/u-boot.bin /mnt/narcissus/sd_image1/u-boot.bin
	if [ -e ${TARGET_DIR}/boot/uImage ] ;then 
		cp -v ${TARGET_DIR}/boot/uImage /mnt/narcissus/sd_image1/uImage.bin
	else
		cp -v ${WORKDIR}/conf/${MACHINE}/sd/uImage.bin /mnt/narcissus/sd_image1/uImage.bin
	fi
	umount /dev/loop1
	
	/sbin/losetup -d /dev/loop1
	gzip -c sd.img > ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd.img.gz
fi
)

echo "removing target dir"
rm -rf ${TARGET_DIR}

exit ${RETVAL}

