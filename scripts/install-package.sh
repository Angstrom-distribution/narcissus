#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008, 2009 - all rights reserved 

MACHINE=$1
IMAGENAME=$2
PACKAGE=$3

CACHEDIR="${PWD}/deploy/cache"
TARGET_DIR="${PWD}/deploy/${MACHINE}/${IMAGENAME}"
OPKG_CONFDIR_TARGET="${TARGET_DIR}/etc/opkg"
PACKAGELISTFILE="${PWD}/deploy/${MACHINE}/${IMAGENAME}-packages.txt"

export D="${TARGET_DIR}"
export OPKG_OFFLINE_ROOT="${TARGET_DIR}"
export OFFLINE_ROOT="${TARGET_DIR}"
export IPKG_OFFLINE_ROOT="${TARGET_DIR}"
export PATH=${WORKDIR}/bin:${PATH}

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

packagelist="$(echo ${PACKAGE} | tr -d '[~;:]' | sed s:,:\ :g | sort | uniq)"

echo "installing $packagelist"
for pkg in $packagelist ; do
	echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install $pkg"
	yes | bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install $pkg | tee ${TARGET_DIR}/log.txt
	grep -e "rror oc" -e "ollected er" ${TARGET_DIR}/log.txt
	echo "<div id=\"${pkg}-returncode\">$?</div><br/>"
done

mkdir -p ${TARGET_DIR}/tmp/
echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf list_installed | grep locale-base | awk '{print $1}'"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf list_installed | grep locale-base | awk '{print $1}' > ${TARGET_DIR}/tmp/installed-translations
for translation in $(cat ${TARGET_DIR}/tmp/installed-translations | awk -F- '{print $3}') en; do
	echo angstrom-locale-${translation}-feed-config 
done | xargs bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf install

echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf list_installed | awk '{print $1}' |sort | uniq"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf list_installed | awk '{print $1}' |sort | uniq > ${TARGET_DIR}/tmp/installed-packages
for i in $(cat ${TARGET_DIR}/tmp/installed-packages) ; do
	for translation in $(cat ${TARGET_DIR}/tmp/installed-translations | awk -F- '{print $3 ; print $3"-"$4}') ; do
		echo ${i}-locale-${translation}
	done
done | sort | uniq > ${TARGET_DIR}/tmp/wanted-locale-packages

echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf update"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf update

echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf list | awk '{print $1}' |grep locale |sort | uniq"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf list | awk '{print $1}' |grep locale |sort | uniq > ${TARGET_DIR}/tmp/available-locale-packages

cat ${TARGET_DIR}/tmp/wanted-locale-packages ${TARGET_DIR}/tmp/available-locale-packages | sort | uniq -d > ${TARGET_DIR}/tmp/pending-locale-packages
cat ${TARGET_DIR}/tmp/installed-packages ${TARGET_DIR}/tmp/pending-locale-packages | grep locale | sort | uniq -u > ${TARGET_DIR}/tmp/translation-list

cat ${TARGET_DIR}/tmp/translation-list | xargs bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -nodeps install

echo "<div id=\"imgsize\">" $(du ${TARGET_DIR} -hs) "</div>"


