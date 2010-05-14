#!/bin/sh
# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2010 - MIT licensed 

MACHINE=$1
IMAGENAME=$2
IMAGETYPE=$3

if [ -e ${PWD}/conf/host-config ] ; then
	. ${PWD}/conf/host-config
fi

if [ -e ${PWD}/conf/${MACHINE}/machine-config ] ; then
	. ${PWD}/conf/${MACHINE}/machine-config
fi

# Hardcode x86_64 for now, needs a GUI selector in the future
HOST_SDK_ARCH="$(uname -m)"

PACKAGE_ARCHS="$(cat ${TARGET_DIR}/etc/opkg/arch.conf | awk '{print $2}' | xargs echo)"
PACKAGE_SDK_ARCHS="$(cat ${TARGET_DIR}/etc/opkg/arch.conf | awk "{print \"${HOST_SDK_ARCH}-\" \$2 \"-sdk\"}" | xargs echo)"

if [ -e  ${TARGET_DIR}/etc/angstrom-version ] ; then
	TARGET_SYS="$(cat ${TARGET_DIR}/etc/angstrom-version | grep Target | awk -F": " '{print $2}')"
else
	TARGET_SYS="unknown-angstrom-linux"
fi

export OPKG_TARGET="opkg-cl -f ${OPKGCONF_TARGET} -o ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}"


function do_tar() 
{
	echo "tarring up SDK"
	( fakeroot tar cfj ${TARGET_DIR}/${TOOLCHAIN_OUTPUTNAME}-extras.tar.bz2 .
	  cd ${SDK_OUTPUT}
	  fakeroot tar cfj ${TARGET_DIR}/${TOOLCHAIN_OUTPUTNAME}.tar.bz2 .
	  RETVAL=$? )
}

modify_opkg_conf () {
	OUTPUT_OPKGCONF_TARGET="${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/${sysconfdir}/opkg.conf"
	OUTPUT_OPKGCONF_HOST="${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/${sysconfdir}/opkg-sdk.conf"
	OUTPUT_OPKGCONF_SDK="${SDK_OUTPUT}/${sysconfdir}/opkg-sdk.conf"
	rm -f ${OUTPUT_OPKGCONF_TARGET}
	rm -f ${OUTPUT_OPKGCONF_HOST}
	rm -f ${OUTPUT_OPKGCONF_SDK}

	if [ -e ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/${sysconfdir}/opkg/arch.conf ] ; then
		echo "Creating empty opkg.conf since arch.conf is already present"
		echo > ${OUTPUT_OPKGCONF_TARGET}
	else
		opkgarchs="${PACKAGE_ARCHS}"
		priority=1
		for arch in ${opkgarchs}; do
				echo "arch ${arch} ${priority}" >> ${OUTPUT_OPKGCONF_TARGET};
				if [ -n "${TOOLCHAIN_FEED_URI}" ] ; then
					echo "src/gz ${arch} ${TOOLCHAIN_FEED_URI}/${arch}" >> ${OUTPUT_OPKGCONF_TARGET};
				fi
				priority=$(expr ${priority} + 5);
		done
	fi
}

function do_assemble_sdk()
{
	rm -rf ${SDK_OUTPUT}
	mkdir -p ${SDK_OUTPUT}
	mkdir -p ${SDK_OUTPUT}${libdir}/opkg/
	mkdir -p ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}${libdir}/opkg/

	package_generate_ipkg_conf

	for arch in ${PACKAGE_ARCHS}; do
		revipkgarchs="$arch $revipkgarchs"
	done

	echo "${OPKG_HOST} update"
	${OPKG_HOST} update
	${OPKG_HOST} -force-depends install ${TOOLCHAIN_HOST_TASK}

	echo "${OPKG_TARGET} update"
	${OPKG_TARGET} update
	${OPKG_TARGET} install ${TOOLCHAIN_TARGET_TASK}

	# Remove packages in the exclude list which were installed by dependencies
	if [ ! -z "${TOOLCHAIN_TARGET_EXCLUDE}" ]; then
		${OPKG_TARGET} remove -force-depends ${TOOLCHAIN_TARGET_EXCLUDE}
	fi

	install -d ${SDK_OUTPUT}/${SDKPATH}/usr/lib/opkg
	mv ${SDK_OUTPUT}/usr/lib/opkg/* ${SDK_OUTPUT}/${SDKPATH}/usr/lib/opkg/
	rm -Rf ${SDK_OUTPUT}/usr/lib

	# Clean up empty directories from excluded packages
	find ${SDK_OUTPUT} -depth -type d -empty -print0 | xargs -r0 /bin/rmdir

	install -d ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/${sysconfdir}
	install -m 0644 ${OPKGCONF_TARGET} ${OPKGCONF_SDK} ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/${sysconfdir}/

	install -d ${SDK_OUTPUT}/${SDKPATH}/${sysconfdir}
	install -m 0644 ${OPKGCONF_SDK} ${SDK_OUTPUT}/${SDKPATH}/${sysconfdir}/

	# extract and store ipks, pkgdata and shlibs data
	target_pkgs=`cat ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/usr/lib/opkg/status | grep Package: | cut -f 2 -d ' '`
	mkdir -p ${SDK_OUTPUT2}/${SDKPATH}/ipk/
	mkdir -p ${SDK_OUTPUT2}/${SDKPATH}/pkgdata/runtime/
	mkdir -p ${SDK_OUTPUT2}/${SDKPATH}/${TARGET_SYS}/shlibs/
	for pkg in $target_pkgs ; do
		for arch in $revipkgarchs; do
			pkgnames=${DEPLOY_DIR_IPK}/$arch/${pkg}_*_$arch.ipk
			if [ -e $pkgnames ]; then
				oenote "Found $pkgnames"
				cp $pkgnames ${SDK_OUTPUT2}/${SDKPATH}/ipk/
				orig_pkg=`ipkg-list-fields $pkgnames | grep OE: | cut -d ' ' -f2`
				pkg_subdir=$arch${TARGET_VENDOR}-linux
				mkdir -p ${SDK_OUTPUT2}/${SDKPATH}/pkgdata/$pkg_subdir/runtime
				cp ${TMPDIR}/pkgdata/$pkg_subdir/$orig_pkg ${SDK_OUTPUT2}/${SDKPATH}/pkgdata/$pkg_subdir/
				subpkgs=`cat ${TMPDIR}/pkgdata/$pkg_subdir/$orig_pkg | grep PACKAGES: | cut -b 10-`
				for subpkg in $subpkgs; do
					cp ${TMPDIR}/pkgdata/$pkg_subdir/runtime/$subpkg ${SDK_OUTPUT2}/${SDKPATH}/pkgdata/$pkg_subdir/runtime/
					if [ -e ${TMPDIR}/pkgdata/$pkg_subdir/runtime/$subpkg.packaged ];then
						cp ${TMPDIR}/pkgdata/$pkg_subdir/runtime/$subpkg.packaged ${SDK_OUTPUT2}/${SDKPATH}/pkgdata/$pkg_subdir/runtime/
					fi
					if [ -e ${STAGING_DIR_TARGET}/shlibs/$subpkg.list ]; then
						cp ${STAGING_DIR_TARGET}/shlibs/$subpkg.* ${SDK_OUTPUT2}/${SDKPATH}/${TARGET_SYS}/shlibs/
					fi
				done
				break
			fi
		done
	done

	# add missing link to libgcc_s.so.1
	# libgcc-dev should be responsible for that, but it's not getting built
	# RP: it gets smashed up depending on the order that gcc, gcc-cross and 
	# gcc-cross-sdk get built :( (30/11/07)
	ln -sf libgcc_s.so.1 ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/lib/libgcc_s.so

	# With sysroot support, gcc expects the default C++ headers to be
	# in a specific place.
	install -d ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/include
	mv ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/usr/include/c++ \
		${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS}/include/

	# Fix or remove broken .la files
	for i in `find ${SDK_OUTPUT}/${SDKPATH}/${TARGET_SYS} -name \*.la`; do
		sed -i 	-e "/^dependency_libs=/s,\([[:space:]']\)${base_libdir},\1\$SDK_PATH/\$TARGET_SYS${base_libdir},g" \
			-e "/^dependency_libs=/s,\([[:space:]']\)${libdir},\1\$SDK_PATH/\$TARGET_SYS${libdir},g" \
			-e "/^dependency_libs=/s,\-\([LR]\)${base_libdir},-\1\$SDK_PATH/\$TARGET_SYS${base_libdir},g" \
			-e "/^dependency_libs=/s,\-\([LR]\)${libdir},-\1\$SDK_PATH/\$TARGET_SYS${libdir},g" \
			-e 's/^installed=yes$/installed=no/' $i
	done
	rm -f ${SDK_OUTPUT}/${SDKPATH}/lib/*.la

	# Setup site file for external use
	siteconfig=${SDK_OUTPUT}/${SDKPATH}/site-config
	touch $siteconfig
	for sitefile in ${CONFIG_SITE} ; do
		cat $sitefile >> $siteconfig
	done

	# Create environment setup script
	script=${SDK_OUTPUT}/${SDKPATH}/environment-setup
	touch $script
	echo 'export SDK_PATH=${SDKPATH}' >> $script
	echo 'export TARGET_SYS=${TARGET_SYS}' >> $script
	echo 'export PATH=$SDK_PATH/bin:$PATH' >> $script
	echo 'export CPATH=$SDK_PATH/$TARGET_SYS/usr/include:$CPATH' >> $script
	echo 'export LIBTOOL_SYSROOT_PATH=$SDK_PATH/$TARGET_SYS' >> $script
	echo 'export PKG_CONFIG_SYSROOT_DIR=$SDK_PATH/$TARGET_SYS' >> $script
	echo 'export PKG_CONFIG_PATH=$SDK_PATH/$TARGET_SYS${libdir}/pkgconfig' >> $script
	echo 'export CONFIG_SITE=$SDK_PATH/site-config' >> $script
	echo 'alias opkg="LD_LIBRARY_PATH=$SDK_PATH/lib $SDK_PATH/bin/opkg-cl -f $SDK_PATH/${sysconfdir}/opkg-sdk.conf -o $SDK_PATH"' >> $script
	echo 'alias opkg-target="LD_LIBRARY_PATH=$SDK_PATH/lib $SDK_PATH/bin/opkg-cl -f $SDK_PATH/$TARGET_SYS${sysconfdir}/opkg.conf -o $SDK_PATH/$TARGET_SYS"' >> $script

	# Add version information
	versionfile=${SDK_OUTPUT}/${SDKPATH}/version
	touch $versionfile
	echo 'Distro: ${DISTRO}' >> $versionfile
	echo 'Distro Version: ${DISTRO_VERSION}' >> $versionfile
	#echo 'Metadata Revision: ${METADATA_REVISION}' >> $versionfile
	echo 'Timestamp: $(date -u --rfc-3339=seconds)' >> $versionfile

	modify_opkg_conf

	# Package it up
	do_tar
}

if ! [ -e ${TARGET_DIR}/etc/opkg.conf ] ; then
	echo "Initial filesystem not found, something went wrong in the configure step!"
	exit 0
fi

export TOOLCHAIN_HOST_TASK="task-sdk-host"
export TOOLCHAIN_TARGET_TASK="task-sdk-bare"
export TOOLCHAIN_TARGET_EXCLUDE=""

TOOLCHAIN_FEED_URI="${ANGSTROM_FEED_URI}"

do_assemble_sdk

exit ${RETVAL}


