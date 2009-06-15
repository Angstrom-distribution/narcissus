#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008, 2009 - all rights reserved 

MACHINE=$1
IMAGENAME=$2
RELEASE=$3

CACHEDIR="${PWD}/deploy/cache"
TARGET_DIR="${PWD}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"
export D="${TARGET_DIR}"
export OPKG_OFFLINE_ROOT="${TARGET_DIR}"
export OFFLINE_ROOT="${TARGET_DIR}"
export IPKG_OFFLINE_ROOT="${TARGET_DIR}"
export PATH=${WORKDIR}/bin:${PATH}

if [ -e ${TARGET_DIR} ] ; then
	echo "Stale directory found, removing  it"
	rm ${TARGET_DIR} -rf
fi

if [ -e ${CACHEDIR} ] ; then
	CACHE="--cache ${CACHEDIR}"
fi

if [ -e conf/${MACHINE}/arch.conf ] ; then
	mkdir -p ${OPKG_CONFDIR_TARGET}
	mkdir -p ${TARGET_DIR}/usr/lib/opkg
	cp conf/${MACHINE}/arch.conf ${OPKG_CONFDIR_TARGET}
	echo "Configuring for ${MACHINE}"
else
	echo "Machine config not found for machine ${MACHINE}"
	exit 0
fi

if [ -e conf/${MACHINE}/configs/${RELEASE}/ ] ; then
	echo "Distro configs for ${RELEASE} found"
else
	echo "Distro configs for ${RELEASE} NOT found, defaulting to stable"
	RELEASE="stable"
fi

echo "dest root /" > ${TARGET_DIR}/etc/opkg.conf 
echo "lists_dir ext /var/lib/opkg" >> ${TARGET_DIR}/etc/opkg.conf 

echo "running: opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install conf/${MACHINE}/configs/${RELEASE}/angstrom-feed-config*"
yes | bin/opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install conf/${MACHINE}/configs/${RELEASE}/angstrom-feed-config* 
echo "running: opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf update"
bin/opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf update
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install opkg-nogpg

echo "Configure done"
