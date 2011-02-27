#!/bin/bash
# Narcissus - Online image builder for the angstrom distribution
# Copyright (C) 2008 - 2011 Koen Kooi
# Copyright (C) 2010        Denys Dmytriyenko
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2 as
# published by the Free Software Foundation.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

MACHINE=$1
IMAGENAME=$2
IMAGETYPE=$3
MANIFEST=$4
SDK=$5
SDKARCH=$6

if [ -e ${PWD}/conf/host-config ] ; then
	. ${PWD}/conf/host-config
fi

if [ -e ${PWD}/conf/${MACHINE}/machine-config ] ; then
	. ${PWD}/conf/${MACHINE}/machine-config
fi

echo "Fakeroot config: ${FAKEROOT}"

function make_sdimg() 
{

#sd-master-1GiB.img.gz

if [ -e ${WORKDIR}/conf/${MACHINE}/sd ] ; then
	MD5SUM_SD="$(md5sum ${TARGET_DIR}/boot/uImage | awk '{print $1}')"	

	for sdsize in $(ls ${WORKDIR}/conf/${MACHINE}/sd/sd-master* | sed -e s:${WORKDIR}/conf/${MACHINE}/sd/sd-master-::g -e 's:.img.gz::g' | xargs echo) ; do

	echo "SD size: $sdsize"

	if [ -e ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}-$sdsize.img.gz ] ; then
		echo "Cached SD image found, using that"	
		echo "cp ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}-$sdsize.img.gz ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd-$sdsize.img.gz"
		cp ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}-$sdsize.img.gz ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd-$sdsize.img.gz
	else
		echo "No cached SD image found, generating new one"
		zcat ${WORKDIR}/conf/${MACHINE}/sd/sd-master-$sdsize.img.gz > sd.img
		/sbin/fdisk -l -u sd.img

		# Output looks like:
		# Disk sd-master-1GiB.img: 0 MB, 0 bytes
		# 255 heads, 63 sectors/track, 0 cylinders, total 0 sectors
		# Units = sectors of 1 * 512 = 512 bytes
		# Sector size (logical/physical): 512 bytes / 512 bytes
		# I/O size (minimum/optimal): 512 bytes / 512 bytes
		# Disk identifier: 0x00000000
		# 
		#             Device Boot      Start         End      Blocks   Id  System
		# sd-master-1GiB.img1   *          63      144584       72261    c  W95 FAT32 (LBA)
		# sd-master-1GiB.img2          144585     1959929      907672+  83  Linux
	
	
		BYTES_PER_SECTOR="$(/sbin/fdisk -l -u sd.img | grep Units | awk '{print $9}')"
		VFAT_SECTOR_OFFSET="$(/sbin/fdisk -l -u sd.img | grep img1 | awk '{print $3}')"
		EXT3_SECTOR_OFFSET="$(/sbin/fdisk -l -u sd.img | grep img2 | awk '{print $2}')"

		LOOP_DEV="/dev/loop1"
		LOOP_DEV_FS="/dev/loop2"

		# VFAT
		echo "/sbin/losetup -v -o $(expr ${BYTES_PER_SECTOR} "*" ${VFAT_SECTOR_OFFSET}) ${LOOP_DEV} sd.img"
		/sbin/losetup -v -o $(expr ${BYTES_PER_SECTOR} "*" ${VFAT_SECTOR_OFFSET}) ${LOOP_DEV} sd.img

		# EXT3
		echo "/sbin/losetup -v -o $(expr ${BYTES_PER_SECTOR} "*" ${EXT3_SECTOR_OFFSET}) ${LOOP_DEV_FS} sd.img"
		/sbin/losetup -v -o $(expr ${BYTES_PER_SECTOR} "*" ${EXT3_SECTOR_OFFSET}) ${LOOP_DEV_FS} sd.img
	
		echo "mount ${LOOP_DEV}"
		mount ${LOOP_DEV}

		echo "mount ${LOOP_DEV_FS}"
		mount ${LOOP_DEV_FS}

		# report mount status to log
		mount | grep loop

		echo "copying files to vfat"
		if [ -e ${WORKDIR}/conf/${MACHINE}/sd/MLO ] ; then
			cp -v ${WORKDIR}/conf/${MACHINE}/sd/MLO /mnt/narcissus/sd_image1/MLO
		else
			rm -f /mnt/narcissus/sd_image1/MLO		
		fi
		if [ -e ${TARGET_DIR}/boot/u-boot-*.bin ] ;then
			cp -v ${TARGET_DIR}/boot/u-boot-*.bin /mnt/narcissus/sd_image1/u-boot.bin
			echo "Copied u-boot from /boot"
		else
			cp -v ${WORKDIR}/conf/${MACHINE}/sd/u-boot.bin /mnt/narcissus/sd_image1/u-boot.bin
			echo "Using u-boot from narcissus, no u-boot.bin found in rootfs"
		fi
		if [ -e ${TARGET_DIR}/boot/uImage-2.6* ] ;then 
			cp -v ${TARGET_DIR}/boot/uImage-2.6* /mnt/narcissus/sd_image1/uImage
			echo "Copied uImage from /boot"
		else
			cp -v ${WORKDIR}/conf/${MACHINE}/sd/uImage.bin /mnt/narcissus/sd_image1/uImage
			echo "Using uImage from narcissus, no uImage found in rootfs"
		fi

		echo "${FAKEROOT} ${WORKDIR}/scripts/populate-sdimg.sh $MACHINE $IMAGENAME"
		${FAKEROOT} ${WORKDIR}/scripts/populate-sdimg.sh $MACHINE $IMAGENAME

		echo "Remounting ${LOOP_DEV}"
		umount ${LOOP_DEV}
		mount ${LOOP_DEV}

		echo "files in sd image:" $(du -hs /mnt/narcissus/sd_image1/*)
		export MD5SUM_SD="$(md5sum /mnt/narcissus/sd_image1/uImage | awk '{print $1}')"
		echo "MD5 of file in vfat partition: ${MD5SUM_SD}"

		echo "copying file system"
		tar xzf ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.tar.gz -C /mnt/narcissus/sd_image2
	
		echo "umount ${LOOP_DEV}"	
		umount ${LOOP_DEV}
		echo "umount ${LOOP_DEV_FS}"
		umount ${LOOP_DEV_FS}
	
		/sbin/losetup -d ${LOOP_DEV}
		/sbin/losetup -d ${LOOP_DEV_FS}

		echo "gzip -c sd.img > ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd-$sdsize.img.gz"
		gzip -c sd.img > ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd-$sdsize.img.gz
		echo "cp ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd-$sdsize.img.gz ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}-$sdsize.img.gz"
		cp ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}-sd-$sdsize.img.gz ${WORKDIR}/conf/${MACHINE}/sd/sd-${MD5SUM_SD}-$sdsize.img.gz
	fi
	done
fi
}

function do_tar() 
{
	echo "tarring up filesystem"
	( cd ${TARGET_DIR}
	  echo "${FAKEROOT} tar cfz ../${IMAGENAME}-${MACHINE}.tar.gz ."
	  ${FAKEROOT} tar cfz ../${IMAGENAME}-${MACHINE}.tar.gz .
	  RETVAL=$?
	  make_sdimg )
}

function do_ubifs()
{
	echo "creating ubi volume"
	( cd ${TARGET_DIR}/../
	  echo \[ubifs\] > ubinize.cfg
	  echo mode=ubi >> ubinize.cfg 
	  echo image=${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubifs >> ubinize.cfg
	  echo vol_id=0 >> ubinize.cfg
	  echo vol_type=dynamic >> ubinize.cfg
	  echo vol_name=${UBI_VOLNAME} >> ubinize.cfg
	  echo vol_flags=autoresize >> ubinize.cfg
	  echo "running: mkfs.ubifs -r ${IMAGE_ROOTFS} -o ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubifs ${MKUBIFS_ARGS} && ubinize -o ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubi ${UBINIZE_ARGS} ubinize.cfg"
	  mkfs.ubifs -r ${IMAGE_ROOTFS} -o ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubifs ${MKUBIFS_ARGS} && ubinize -o ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ubi ${UBINIZE_ARGS} ubinize.cfg )
}

function do_jffs2()
{
	echo "creating jffs2 image"
	mkfs.jffs2 -x lzo --root=${IMAGE_ROOTFS} --faketime --output=${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.jffs2 ${EXTRA_IMAGECMD_jffs2}
}

function do_ext2()
{
	echo "creating ext2 image"
	export ROOTFS_SIZE="$(du -ks ${IMAGE_ROOTFS} | awk '{print 65536 + $1}')"
	echo "running: genext2fs -b ${ROOTFS_SIZE} -d ${IMAGE_ROOTFS} ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ext2 ${EXTRA_IMAGECMD_ext2}"
	genext2fs -b ${ROOTFS_SIZE} -d ${IMAGE_ROOTFS} ${TARGET_DIR}/../${IMAGENAME}-${MACHINE}.ext2 ${EXTRA_IMAGECMD_ext2}
}

function print_header()
{
	cat > ${TARGET_DIR}-manifest.html << EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<head>
  <title>${DISTRO} Filesystem Software Manifest</title>
  <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
</head>
<body lang="EN-US">
<b>${DISTRO} Filesystem Software Manifest</b><br/>
<br/>
<b>Legend</b>
<table border="1" style="border-collapse: collapse;">
  <tbody>
	<tr style="">
	  <td style="width: 100pt;" >Package Name</td>
	  <td>The name of the application or files</td>
	</tr>
	<tr style="">
	  <td>Version</td>
	  <td>Version of the application or files</td>
	</tr>
	<tr style="">
	  <td>License</td>
	  <td>Name of the license or licenses that apply to the Package.</td>
	</tr>
	<tr style="">
	  <td>Location </td>
	  <td>The directory name and path on the media (or in an archive) where the Package is located.</td>
	</tr>
	<tr style="">
	  <td>Delivered As</td>
	  <td>This field will either be
&ldquo;Source&rdquo;, &ldquo;Binary&rdquo; or
&ldquo;Source and Binary&rdquo; and is the form the content of
the Package is delivered in.&nbsp; If the Package is delivered in
an archive format, this field applies to the contents of the archive.
If the word Limited is used with Source, as in &ldquo;Limited
Source&rdquo; or &ldquo;Limited Source and Binary&rdquo;
then only portions of the Source for the application are provided.</td>
	</tr>
	<tr style="">
	  <td>Modified </td>
	  <td>This field will either be &ldquo;Yes&rdquo;, &ldquo;No&rdquo;
or &ldquo;OE&rdquo;. A &ldquo;Yes&rdquo; means ${COMPANY} had made changes to the
Package. A &ldquo;No&rdquo; means ${COMPANY} has not made any changes. An
&ldquo;OE&rdquo; means the Package has been modified by OpenEmbedded.
</td>
	</tr>
	<tr style="">
	  <td><a name="_ftnref1">Obtained from</a><a title="" href="#_ftn1">[1]</a>
	  </td>
	  <td>
This field specifies where ${COMPANY} obtained the Package from. It may be a URL to an Open Source site, a 3<sup>rd</sup> party company name or ${COMPANY}.
If this field contains a link to an Open Source package, the date it was downloaded is also recorded.</td>
	</tr>
  </tbody>
</table>

<br/>
<b>Manifest</b><br/>
EOF
}

function print_footer()
{
	cat >> ${TARGET_DIR}-manifest.html << EOF
<p><a name='_ftn1'></a><b><a href='#_ftn1' title=''>[1]</a> Any links appearing on this manifest were verified
at the time it was created. ${COMPANY} makes no guarantee that they will remain active in the future.</b></p></body></html>
EOF
}

function do_manifest()
{
	# Print list of installed packages and their filenames
	echo "Print list of installed packages and their filenames to work/${MACHINE}/${IMAGENAME}-installed-packages.txt"

	if [ -e conf/metadata.txt ] ; then
		METADATACACHE="1"
	fi

	# Create a directory to bundle up the sources for the image
	mkdir -p ${TARGET_DIR}-sources
	echo 'Options +Indexes' > ${TARGET_DIR}-sources/.htaccess
	echo 'Options +FollowSymLinks' >> ${TARGET_DIR}-sources/.htaccess

	for pkg in $(opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} list_installed | awk '{print $1}') ; do 
		echo -n "<tr><td rowspan=2><a href='http://www.angstrom-distribution.org/repo/?pkgname=${pkg}' target='npkg'>$pkg</a></td>"
		PKGNAME="$(opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} info $pkg | grep Filename | head -n1 | awk '{print $2}')"

		if [ $METADATACACHE = "1"  ] ; then
			PATTERN="${PKGNAME}"
			OENAME="$(grep $PATTERN conf/metadata.txt | awk -F, '{print $5}')"
			FILENAME="$(grep $PATTERN conf/metadata.txt | awk -F, '{print $2}')"
			LICENSE="$(grep $PATTERN conf/metadata.txt | awk -F, '{print $3}')"
			VERSION="$(grep $PATTERN conf/metadata.txt | awk -F, '{print $4}')"
			SOURCE="$(grep $PATTERN conf/metadata.txt | awk -F, '{print $6}')"
			if [ -z "${SOURCE}" ]; then
				SOURCE="${DISTRO}/OE metadata"
			else
				SOURCE="<a href=\"$SOURCE\">$SOURCE</a>"
				# hack hack hack! This hardcodes the location of the sources, which sucks
				SHORTLICENSE="$(echo ${LICENSE} | awk '{print $1}' | awk -F/ '{print $1}')"
				if [ -d ${WORKDIR}/../sources/${SHORTLICENSE}/${OENAME} ] ; then
					cp -a ${WORKDIR}/../sources/${SHORTLICENSE}/${OENAME} ${TARGET_DIR}-sources
				fi
			fi
			echo -n "<td rowspan=2>$VERSION</td><td rowspan=2>$LICENSE</td><td rowspan=2>Binary</td rowspan=2><td rowspan=2></td><td>Location</td><td>$FILENAME</td></tr><tr><td>Obtained from</td><td>$SOURCE</td></tr>"
		else
			FILENAME="$(opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} info $pkg | grep Filename | head -n1 | awk '{print $2}')"
			echo -n "<td rowspan=2></td><td rowspan=2></td><td rowspan=2>Binary</td rowspan=2><td rowspan=2></td><td>Location</td><td>$FILENAME</td></tr><tr><td>Obtained from</td><td>$SOURCE</td></tr>"		
		fi
		echo "</tr>"
	done > ${TARGET_DIR}-installed-packages.txt

	# Write out manifest
	echo "Write out manifest"

	print_header
	echo "Narcissus package list: " >> ${TARGET_DIR}-manifest.html
	cat ${TARGET_DIR}.txt >> ${TARGET_DIR}-manifest.html

	echo "<p/>" >> ${TARGET_DIR}-manifest.html
	echo "Sample OE image recipe: <a href='${IMAGENAME}.bb'>${IMAGENAME}.bb</a>" >> ${TARGET_DIR}-manifest.html

	echo "<p/>" >> ${TARGET_DIR}-manifest.html
	echo "Complete package list:<br/>" >> ${TARGET_DIR}-manifest.html
	echo "<p/><table border='1' style='border-collapse: collapse;'><tr><td>Package Name</td><td>Version</td><td>License</td><td>Delivered as</td><td>Modified</td><td></td><td></td></tr>" >> ${TARGET_DIR}-manifest.html
	cat ${TARGET_DIR}-installed-packages.txt >> ${TARGET_DIR}-manifest.html
	echo "</table>" >> ${TARGET_DIR}-manifest.html

	print_footer
}

function do_oeimage()
{
	# Write sample OE image
	echo "Write sample OE image"

	echo "export IMAGE_BASENAME = \"${IMAGENAME}\"" > ${TARGET_DIR}.bb
	echo "IMAGE_INSTALL = \" $(cat ${TARGET_DIR}.txt) \"" >> ${TARGET_DIR}.bb
	echo "inherit image" >> ${TARGET_DIR}.bb
}

if ! [ -e ${TARGET_DIR}/etc/opkg.conf ] ; then
	echo "Initial filesystem not found, something went wrong in the configure step!"
	exit 0
fi

echo "installing initial /dev directory"
mkdir -p ${TARGET_DIR}/dev
bin/makedevs -r ${TARGET_DIR} -D conf/devtable.txt

if [ -e ${TARGET_DIR}/log.txt ] ; then
	rm ${TARGET_DIR}/log.txt
fi

echo "Running preinsts"

for i in ${TARGET_DIR}/usr/lib/opkg/info/*.preinst; do
	#sh ${PWD}/scripts/sleep.sh
	if [ -f $i ] && ! sh $i; then
		echo "Running: opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} flag unpacked `basename $i .preinst`"
		opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} flag unpacked `basename $i .preinst`
	fi
done 

echo "Running postinsts"

for i in ${TARGET_DIR}/usr/lib/opkg/info/*.postinst; do
	#sh ${PWD}/scripts/sleep.sh
	if [ -f $i ] && ! sh $i configure; then
		echo "Running: opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} flag unpacked `basename $i .postinst`"
		opkg-cl -o ${TARGET_DIR} -f ${TARGET_DIR}/etc/opkg.conf -t ${OPKG_TMP_DIR} flag unpacked `basename $i .postinst`
	fi
done 

if [ "$MANIFEST" = "yes" ] ; then
	do_manifest
fi

echo "removing opkg index files"
rm ${TARGET_DIR}/var/lib/opkg/* || true
rm ${TARGET_DIR}/usr/lib/opkg/lists/* || true
rm ${TARGET_DIR}/linuxrc || true

# Add timestamp
date "+%m%d%H%M%Y" > ${TARGET_DIR}/etc/timestamp

# Add opendns to resolv.conf
rm -f ${TARGET_DIR}/etc/resolv.conf
echo "nameserver 208.67.222.222" > ${TARGET_DIR}/etc/resolv.conf
echo "nameserver 208.67.220.220" >> ${TARGET_DIR}/etc/resolv.conf

echo "$(date -u +%s) ${MACHINE} $(du ${TARGET_DIR} -hs | awk '{print $1}')" >> ${WORKDIR}/deploy/stats.txt || true

echo "<div id=\"imgsize\">" $(du ${TARGET_DIR} -hs) "</div>\n"

export PSEUDO_DISABLED=0

${FAKEROOT} do_oeimage

case ${IMAGETYPE} in
	jffs2)
		do_jffs2;;
	ubifs)
		do_ubifs;;
	tgz)
		do_tar;;
	ext2)
		do_ext2;;
	*)
		do_tar;;
esac

case ${SDK} in
	toolchain)
		echo "Generating toolchain"
		bash scripts/assemble-sdk.sh $MACHINE $IMAGENAME $SDK $SDKARCH;;
	sdk)
		echo "Generating SDK"
		bash scripts/assemble-sdk.sh $MACHINE $IMAGENAME $SDK $SDKARCH;;
	*)
		echo "Not generating toolchain or SDK";;
esac

echo "removing target dir"
rm -rf ${PSEUDO_LOCALSTATEDIR} ${OPKG_TMP_DIR} ${TARGET_DIR}

exit ${RETVAL}


