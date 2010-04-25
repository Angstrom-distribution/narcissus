#!/bin/sh

# Narcissus - Online image builder for the angstrom distribution
# Koen Kooi (c) 2010- all rights reserved 

# This script extracts metadata from the cache dir to use in e.g. manifests

if [ -e ${PWD}/conf/host-config ] ; then
    . ${PWD}/conf/host-config
fi

echo "<div id='machinelist'>" > machine-list.html
echo "			Select the machine you want to build your rootfs image for:<br/><br/>" >> machine-list.html
echo "			<select name='machine' onChange='showValues();'>" >> machine-list.html

for machine in $(find conf/ -name "arch.conf" | awk -F/ '{print $2}' | sort | uniq) ; do
	echo "				<option value=\"$machine\">$machine</option>" >> machine-list.html

	echo "<div id='configlist'>" > conf/$machine/config-list.html
	echo "                <select name=\"configs\">" >> conf/$machine/config-list.html
	for config in $(ls conf/$machine/configs/ | sort -r ) ; do
		echo "                    <option value=\"$(basename $config)\">$(basename $config)</option>" >> conf/$machine/config-list.html
	done                
	echo "                </select>" >> conf/$machine/config-list.html
	echo "</div>" >> conf/$machine/config-list.html

done

echo "			</select><br/>" >> machine-list.html
echo "</div>" >> machine-list.html



