#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008 - all rights reserved 

MACHINE=$1
IMAGENAME=$2

TARGET_DIR="${PWD}/deploy/${MACHINE}/${IMAGENAME}"
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

( cd  ${TARGET_DIR} ; tar cjvf ../${IMAGENAME}-${MACHINE}.tar.bz2 . )
du ${TARGET_DIR} -hs
du ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.tar.bz2 -hs

rm -rf ${TARGET_DIR}


