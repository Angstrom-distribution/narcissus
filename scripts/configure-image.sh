#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008 - all rights reserved 

MACHINE=$1
IMAGENAME=$2

TARGET_DIR="${PWD}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"

if [ -e ${TARGET_DIR} ] ; then
	echo "Stale directory found, removing  it"
	rm ${TARGET_DIR} -rf
fi

if [ -e conf/${MACHINE}/arch.conf ] ; then
	mkdir -p ${OPKG_CONFDIR_TARGET}
	mkdir -p ${TARGET_DIR}/usr/lib/opkg
	cp conf/${MACHINE}/arch.conf ${OPKG_CONFDIR_TARGET}
else
	echo "Machine config not found for machine ${MACHINE}:"
	ls conf/${MACHINE}/
	exit 0
fi

echo "dest root /" > ${TARGET_DIR}/etc/opkg.conf 
echo "lists_dir ext /var/lib/opkg" >> ${TARGET_DIR}/etc/opkg.conf 

echo "installing initial /dev directory"
mkdir -p ${TARGET_DIR}/dev
fakeroot bin/makedevs -r ${TARGET_DIR} -D conf/devtable.txt

bin/opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install conf/${MACHINE}/angstrom-feed-config* 
bin/opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf update
