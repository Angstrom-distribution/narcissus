#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008, 2009 - all rights reserved 

echo "cleaning up stale files"
find /tmp -name 'opkg*' -mtime +2 -exec rm -rv {} \;
find deploy -depth -mindepth 2 -maxdepth 2 -mtime +2 -exec rm -rv {} \;
find work -depth -mindepth 1 -maxdepth 2 -mtime +1 -exec rm -rfv {} \;

MACHINE=$1
IMAGENAME=$2
RELEASE=$3

if [ -e ${PWD}/conf/host-config ] ; then
	. ${PWD}/conf/host-config
	echo "Loaded host config"
fi

if [ -e ${TARGET_DIR} ] ; then
	echo "Stale directory found, removing it"
	rm ${TARGET_DIR} -rf
fi

if [ -e ${CACHEDIRIPK} ] ; then
	echo "cache directory found, using that"
	CACHE="--cache ${CACHEDIRIPK}"
else
	echo "cache directory not found, creating it"
	mkdir -p ${CACHEDIRIPK}
	chmod a+rw ${CACHEDIRIPK} || true
	CACHE="--cache ${CACHEDIRIPK}"
fi

mkdir -p ${OPKG_TMP_DIR}

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
fi

echo "dest root /" > ${TARGET_DIR}/etc/opkg.conf 
echo "lists_dir ext /var/lib/opkg" >> ${TARGET_DIR}/etc/opkg.conf 

echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} install conf/${MACHINE}/configs/${RELEASE}/angstrom-feed-config*"
yes | bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} install conf/${MACHINE}/configs/${RELEASE}/angstrom-feed-config* 
echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} update"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} update
echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} upgrade"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} upgrade
echo "Configure done"
