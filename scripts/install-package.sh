#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008 - all rights reserved 

MACHINE=$1
IMAGENAME=$2
PACKAGE=$3

CACHEDIR="${PWD}/deploy/cache"
TARGET_DIR="${PWD}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"
PACKAGELISTFILE="${PWD}/deploy/${MACHINE}/${IMAGENAME}-packages.txt"

if ! [ -e ${TARGET_DIR}/etc/opkg.conf ] ; then
	print "Initial filesystem not found, something went wrong in the configure step!"
	exit 0
fi

if [ -e ${CACHEDIR} ] ; then
	CACHE="--cache ${CACHEDIR}"
fi

if [ -e ${TARGET_DIR}/log.txt ] ; then
	rm ${TARGET_DIR}/log.txt
fi

packagelist="$(echo ${PACKAGE} | tr -d '[~;:]' | sort | uniq)"

echo "installing $packagelist"
echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install $packagelist"
yes | bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install $packagelist | tee ${TARGET_DIR}/log.txt
du ${TARGET_DIR} -hs
grep -e "rror oc" -e "ollected er" ${TARGET_DIR}/log.txt
exit $?

