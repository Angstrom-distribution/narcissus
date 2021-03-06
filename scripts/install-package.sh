#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2008, 2009 - all rights reserved 

MACHINE=$1
IMAGENAME=$2
PACKAGE=$3

if [ -e ${PWD}/conf/host-config ] ; then
	. ${PWD}/conf/host-config
fi

if ! [ -e ${TARGET_DIR}/etc/opkg.conf ] ; then
	print "Initial filesystem not found, something went wrong in the configure step!"
	exit 0
fi

if [ -e ${CACHEDIRIPK} ] ; then
	CACHE="--cache ${CACHEDIRIPK}"
fi

if [ -e ${TARGET_DIR}/log.txt ] ; then
	rm ${TARGET_DIR}/log.txt
fi

export PSEUDO_DISABLED=0

OPKGARGS="${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf"

packagelist="$(echo ${PACKAGE} | tr -d '[~;:]' | sed s:,:\ :g | sort | uniq)"

echo $packagelist > ${TARGET_DIR}.txt

echo "installing $packagelist"
for pkg in $packagelist ; do
	# Sleep N seconds when the load on the buildserver is too high
	#sh ${PWD}/scripts/sleep.sh

	echo "running: ${FAKEROOT} opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} install $pkg"
	yes | ${FAKEROOT} bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} install $pkg | tee ${TARGET_DIR}/log.txt
	grep -e "rror oc" -e "ollected er" ${TARGET_DIR}/log.txt
	echo "<div id=\"${pkg}-returncode\">$?</div><br/>"
done

mkdir -p ${TARGET_DIR}/tmp/
echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} list_installed | grep locale-base | awk '{print $1}'"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf list_installed -t ${OPKG_TMP_DIR} | grep locale-base | awk '{print $1}' > ${TARGET_DIR}/tmp/installed-translations
for translation in $(cat ${TARGET_DIR}/tmp/installed-translations | awk -F- '{print $3}') en; do
	echo angstrom-locale-${translation}-feed-config 
done | xargs bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} install

echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} list_installed | awk '{print $1}' |sort | uniq"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} list_installed | awk '{print $1}' |sort | uniq > ${TARGET_DIR}/tmp/installed-packages
for i in $(cat ${TARGET_DIR}/tmp/installed-packages | grep -v locale) ; do
	for translation in $(cat ${TARGET_DIR}/tmp/installed-translations | awk -F- '{print $3 ; print $3"-"$4}') en-us ; do
			translation_split=$(echo ${translation} | awk -F '-' '{print $1}')
			echo locale-base-${translation}
			echo ${i}-locale-${translation}
			echo ${i}-locale-${translation_split}
	done
done | sort | uniq > ${TARGET_DIR}/tmp/wanted-locale-packages

# REVISIT: check if it really isn't needed to update again
#echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} update"
#bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} update

echo "running: opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} list | awk '{print $1}' |grep locale |sort | uniq"
bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} list | awk '{print $1}' |grep locale |sort | uniq > ${TARGET_DIR}/tmp/available-locale-packages

cat ${TARGET_DIR}/tmp/wanted-locale-packages ${TARGET_DIR}/tmp/available-locale-packages | sort | uniq -d > ${TARGET_DIR}/tmp/pending-locale-packages

if [ -s ${TARGET_DIR}/tmp/pending-locale-packages ] ; then
	for i in $(cat ${TARGET_DIR}/tmp/pending-locale-packages) ; do
		echo "running: bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} -nodeps install $i"
		bin/opkg-cl ${CACHE} -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} -nodeps install $i
	done
fi

echo "<div id=\"imgsize\">" $(du ${TARGET_DIR} -hs) "</div>"


