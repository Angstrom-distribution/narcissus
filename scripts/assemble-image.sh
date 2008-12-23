#!/bin/sh
# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008 - all rights reserved 

MACHINE=$1
IMAGENAME=$2

WORKDIR="${PWD}"

TARGET_DIR="${WORKDIR}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"

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

