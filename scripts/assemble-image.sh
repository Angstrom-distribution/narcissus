#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008 - all rights reserved 

MACHINE=$1
IMAGENAME=$2

TARGET_DIR="${PWD}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"
PACKAGELISTFILE="${PWD}/deploy/${MACHINE}/${IMAGENAME}-packages.txt"

if ![ -e ${TARGET_DIR}/etc/opkg.conf ] ; then
	print "Initial filesystem not found, something went wrong in the configure step!"
	exit 0
fi

if [ -e ${PACKAGELISTFILE} ] ; then
	packagelist="$(cat ${PACKAGELISTFILE} | tr -d '[~;:]' | sort | uniq)"
else
	packagelist="task-boot"
fi

echo "installing initial /dev directory"
mkdir -p ${TARGET_DIR}/dev
bin/makedevs -r ${TARGET_DIR} -D conf/devtable.txt

echo "installing $packagelist"
yes | bin/opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install $packagelist

( cd  ${TARGET_DIR} ; tar cjf ../${IMAGENAME}-${MACHINE}.tar.bz2 . )

if [ -e ${PACKAGELISTFILE} ] ; then
	rm ${PACKAGELISTFILE}
fi

rm -rf ${TARGET_DIR}


