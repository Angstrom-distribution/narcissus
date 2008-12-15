#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008 - all rights reserved 

MACHINE=$1
IMAGENAME=$2

TARGET_DIR="${PWD}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"


if ![ -e ${TARGET_DIR}/etc/opkg.conf ] ; then
	print "Initial filesystem not found, something went wrong in the configure step!"
	exit 0
fi§

yes | bin/opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install task-boot

( cd  ${TARGET_DIR} ; tar cjf ../${IMAGENAME}-${MACHINE}.tar.bz2 . )

rm -rf ${TARGET_DIR}


