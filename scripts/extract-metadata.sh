#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2010- all rights reserved 

# This script extracts metadata from the cache dir to use in e.g. manifests

if [ -e ${PWD}/conf/host-config ] ; then
	. ${PWD}/conf/host-config
	echo "Loaded host config"
fi

if [ -e ${CACHEDIR} ] ; then
	( cd ${CACHEDIR}
	rm -f  metadata.txt
	for i in *ipk ; do
		dpkg-deb -I $i > control
		PACKAGE="$(grep 'Package:' control | awk -F': ' '{print $2}')"
		FILENAME="$(echo $i | awk -F, '{print $NF}')"
		LICENSE="$(grep 'License:' control | awk -F': ' '{print $2}')"
		VERSION="$(grep 'Version:' control | head -n1 | awk -F': ' '{print $2}' | awk -F':' '{print $NF}')"
		RECIPE="$(grep 'OE:' control | awk -F': ' '{print $2}')"
		RAWSRC="$(grep 'Source:' control | awk '{print $2}')"
		if [ "$RAWSRC" == "file://SUPPORTED" ]; then
			SOURCE="http://www.codesourcery.com/gnu_toolchains/arm/portal/release858"
		else
			SOURCE="$(echo $RAWSRC | grep -v 'file://' | sed 's/;.*//')"
		fi
		echo "$PACKAGE,$FILENAME,$LICENSE,$VERSION,$RECIPE,$SOURCE" >> metadata.txt
		rm -f control
	done )

	touch conf/metadata.txt
	cat conf/metadata.txt >> ${CACHEDIR}/metadata.txt
	cat ${CACHEDIR}/metadata.txt | sort | uniq > conf/metadata.txt
fi
