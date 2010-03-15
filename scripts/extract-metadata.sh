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
		LICENSE="$(grep License control | awk -F': ' '{print $2}')"
		FILENAME="$(echo $i |  awk -F, '{print $NF}')"
		VERSION="$(grep Version control | awk -F': ' '{print $2}' | awk -F':' '{print $NF}')"
		echo "$FILENAME,$LICENSE,$VERSION" >> metadata.txt
	done )

	touch conf/metadata.txt
	cat conf/metadata.txt >> ${CACHEDIR}/metadata.txt
	cat ${CACHEDIR}/metadata.txt | sort | uniq > conf/metadata.txt
fi
