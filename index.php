<html>
<head>
<title>Narcissus - Online image builder for the angstrom distribution</title>
<script language="javascript" type="text/javascript" src="scripts/js/MochiKit.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/narcissus.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/jquery-1.4.2.min.js"></script>

<script language="javascript" type="text/javascript">
function initForm() {
	toggleVisibility('packageblock');
/*	
	toggleVisibility('machinedialog');
	toggleVisibility('releasedialog');
	toggleVisibility('basesystemdialog');
	toggleVisibility('devman');
	toggleVisibility('imagetypebox');
	toggleVisibility('imagename');
	
	toggleVisibility('buildbutton');
	toggleVisibility('patchbox');
*/	
	toggleVisibility('devel');
	toggleVisibility('console_packages');
	toggleVisibility('platform_packages');
	toggleVisibility('network_packages');

	toggleVisibility('packageblock');
	toggleVisibility('x11_packages');
	
	environmentChange();
}

function environmentChange() {
	if(document.entry_form.environment.selectedIndex == 1) {
		showHideElement('x11_packages_block', 1);
		showHideElement('x11_wm_block', 1);
	}
	else {
		showHideElement('x11_packages_block', 0);
		showHideElement('x11_wm_block', 0);
	}
}
</script>

<link rel="stylesheet" type="text/css" title="dominion" href="css/dominion.css" media="screen" />
</head>
<body onLoad="initForm() ; launchWindow(dialog);"><?
/* Narcissus - online image builder for the angstrom distribution
 * Koen Kooi (c) 2008 - 2010 - all rights reserved 
 */

function machine_dropdown()
{
	$machine = array();
	
	if ($handle = opendir ('./conf/'))
    {
		/* This is the correct way to loop over the directory. */
		while (false !== ($file = readdir ($handle)))
		{
			if ($file != "." && $file != ".."
				&& file_exists ("./conf/$file/arch.conf"))
			{
				$machine[] = $file;
			}
		}
		closedir ($handle);
    }
	
	sort ($machine);
	foreach ($machine as $value)
	{
		print ("\t<option value=\"$value\">$value</option>\n");
	}
}

function config_dropdown()
{
	$configs = array();
	foreach ($machine as $machine_value)
	{
		if ($handle = opendir ('./conf/$machine_value'))
		{
			while (false !== ($file = readdir ($handle)))
			{
				if ($file != "." && $file != ".."
					&& file_exists ("./conf/$machine_value/configs/$file/"))
				{
					$configs[$machine_value][] = $file;
				}
			}
			closedir ($handle);
		}
		
		sort ($configs);
		foreach ($configs as $value)
		{
			print ("\t<option value=\"$value\">$value</option>\n");
		}
	}
}

$repourl = "http://www.angstrom-distribution.org/repo/?pkgname";

$env_array = array("Console only" => "",
				   "X11" => "angstrom-x11-base-depends",
				   "Opie" => "task-opie-base task-opie-base-applets task-opie-base-inputmethods task-opie-base-apps task-opie-base-settings task-opie-base-decorations task-opie-base-styles task-opie-base-pim task-opie-extra-settings task-opie-bluetooth task-opie-irda");

$wm_array = array("Enlightenment" => "angstrom-gpe-task-base e-wm e-wm-config-standard e-wm-config-default",
				  "GNOME" => "angstrom-task-gnome shadow bash",
                  "Xfce 4.6" => "task-xfce46-base task-xfce46-extras shadow",
                  "Matchbox" => "angstrom-gpe-task-base",
				  "Illume" => " angstrom-gpe-task-base e-wm e-wm-config-illume");

$devel_array = array("Python" => "python-core python-modules",
					 "Perl" => "perl perl-modules",
					 "Mono (C#, .NET)" => "mono mono-mcs",
					 "Toolchain" => "task-native-sdk",
					 "OProfile" => "oprofile",
					 "GDB" => "gdb gdbserver",
					 "Busybox replacements" => "task-proper-tools");

$console_packages_array = array("Aircrack-ng" => "aircrack-ng",
								"All kernel modules" => "kernel-modules",
								"Alsa utils" => "alsa-utils-alsamixer alsa-utils-aplay alsa-utils-amixer alsa-utils-aconnect alsa-utils-iecset alsa-utils-speakertest alsa-utils-aseqnet alsa-utils-aseqdump alsa-utils-alsaconf alsa-utils-alsactl",
								"Beagleboard demo" => "task-beagleboard-demo",
								"Bluez" => "bluez-utils",
								"cwiid" => "cwiid",
								"DVB-utils" => "dvb-azap dvb-tzap dvb-czap dvb-szap dvb-scan wscan dvbstream dvbtune",
								"Flite" => "flite libflite-cmu-us-kal1",
								"Gdbserver" => "gdbserver",
								"Gnuradio" => "gnuradio",
								"Git" => "git",
								"GSM0710muxd" => "gsm0710muxd",
								"Gstreamer" => "gst-plugins-bad-meta gst-plugins-base-meta gst-plugins-good-meta gst-plugins-ugly-meta ",
								"I2C-tools" => "i2c-tools",
								"JamVM" => "jamvm",
								"Julius speech recognizer" => "julius",
								"Kismet" => "kismet",
								"LCD4Linux" => "lcd4linux",
								"LIRC" => "lirc",
								"Mediatomb" => "mediatomb",
								"MPlayer" => "mplayer",
								"Mythtv backend" => "mythtv-backend",
								"Octave" => "octave",
								"OpenCV" => "opencv-samples",
								"Powertop" => "powertop",
								"QT/e 4" => "qt4-embedded-demos qt4-embedded-plugin-gfxdriver-gfxvnc qt4-embedded",
								"Screen" => "screen",
								"Video Disc Recoder" => "vdr");

$x11_packages_array = array("Abiword" => "abiword",
							"Duke Nukem 3D" => "duke3d",
							"Doom (prboom)" => "prboom",
							"E-uae" => "e-uae",
							"Ekiga" => "ekiga",
							"Epiphany web browser" => "epiphany",
							"Evince" => "evince",
							"Fennec" => "fennec",
							"Firefox" => "firefox",
							"FLDigi" => "fldigi",
							"Gimp" => "gimp",
							"Gnome Games" => "gnome-games",
							"Gnumeric" => "gnumeric",
							"GPE PIM suite" => "task-gpe-pim",
							"Midori web browser" => "midori",
							"Moblin connection manager GTK+ applet" => "connman-gnome",
							"MythTV" => "mythtv mythtv-frontend",
							"Numptyphysics" => "numptyphysics",
							"Pidgin IM" => "pidgin",
							"Pimlico" => "contacts dates tasks",
							"Quake 1" => "sdlquake",
							"Quake 2" => "quake2",
							"Quake 2 (quetoo)" => "quetoo",
							"Quake 3 (ioq3)" => "ioquake3",
                            "ScummVM" => "scummvm",
							"SDR-shell" => "sdrshell",
							"Stalonetray" => "stalonetray",
							"Totem media player" => "totem",
							"Wireshark" => "wireshark",
							"Zhone" => "zhone frameworkd");

$network_packages_array = array("Apache" => "apache2",
								"Boa" => "boa",
								"Cherokee" => "cherokee",
								"Dropbear SSH server" => "dropbear",
								"Moblin connection manager" => "connman",
								"NetworkManager" => "networkmanager networkmanager-openvpn",
								"NetworkManager GUI applet" => "network-manager-applet",
								"Nmap" => "nmap",
								"NTP" => "ntp",
								"NTPclient" => "ntpclient",
								"NTPdate" => "ntpdate",	
								"Rtorrent" => "rtorrent",
								"Samba" => "samba",
								"Wireless-tools" => "wireless-tools"
								);

$platform_omap_packages_array = array("Texas Instruments Gstreamer plugins" => "gstreamer-ti",
									  "PowerVR SGX drivers for OMAP3" => "libgles-omap3",
									  "PowerVR SGX demos for framebuffer" => "libgles-omap3-rawdemos",
									  "PowerVR SGX demos for X11" => "libgles-omap3-x11demos",
									  "PowerVR SGX gfxdriver plugin for QT/embedded" => "qt4-embedded-plugin-gfxdriver-gfxpvregl",
									  "PowerVR SGX gfxdriver plugin for QT/X11" => "qt4-plugin-graphicssystems-glgraphicssystem",
									  "Quake 3 (GLES)" => "quake3-pandora-gles libgles-omap3",
									  "Julius demo for Texas Instruments" => "ti-julius-demo");

$platform_davinci_packages_array = array("Texas Instruments Gstreamer plugins" => "gstreamer-ti",
										  "Julius demo for Texas Instruments" => "ti-julius-demo");

$platform_pxa_packages_array = array("PXA register utility" => "pxaregs");



?>
<div id="summary"></div><form name="entry_form" onsubmit="javascript:configureImage(); toggleVisibility('buildbutton'); return false">
	<div id="settings">
		<div id="welcomedialog">
			Welcome!<br/><br/>This is an online tool to create so called 'rootfs' images for your favourite device. This wizard will guide through the basic options and will close to let you select the additional packages you want.<br/>
		</div>
		<div id="machinedialog" class="nblock">
			Select the machine you want to build your rootfs image for:<br/><br/>
			<select name="machine">
				<? machine_dropdown(); ?>
			</select > 
		<br/>
		</div>
		<div id="releasedialog" class="nblock">
			Select the release you want to base your rootfs image on.<br/><font size="-2">The 'stable' option will give you a working system, but will not have the latest versions of packages. The 'unstable' option will give you access to all the latest packages the developers have uploaded, but is known to break every now and then.</font><br/><br/>
			<select name="configs">
				<option value="unstable">unstable</option>
				<option value="stable">stable</option>
			</select>
		</div>
		<div id="basesystemdialog" class="nblock">
			Base system<br> <font size="-2">Each entry down is a superset of the one above it. Busybox will give you only busybox, usefull for e.g. small ramdisks. Task-boot will give you the minimal set of drivers and packages you need to boot. Task-base will give you drivers for non-essential features of your system, e.g. bluetooth. Options below that will include even more drivers for a smoother experience with USB based devices.</font><br/><br/>
				<input type="radio" name="pkg" value="busybox">bare bones (<a href='http://www.angstrom-distribution.org/repo/?pkgname=busybox' target='foo'>busybox</a>)<br/>
				<input type="radio" name="pkg" value="task-boot">small (<a href='http://www.angstrom-distribution.org/repo/?pkgname=task-boot' target='foo'>task-boot</a>)<br/>
				<input type="radio" name="pkg" value="task-base" checked="checked">regular (<a href='http://www.angstrom-distribution.org/repo/?pkgname=task-base' target='foo'>task-base</a>)<br/>
				<input type="radio" name="pkg" value="task-base-extended">extended (<a href='http://www.angstrom-distribution.org/repo/?pkgname=task-base-extended' target='foo'>task-base-extended</a>)<br/>
				<br/>
		</div>

		<div id='devman' class="nblock">
			Select the /dev manager.<br/><font size="-2">Udev is generally the best choice, only select mdev for fixed-function devices and if you know what you're doing. Kernel will use the in-kernel <a href='http://lwn.net/Articles/330985/'>devtmpfs</a> feature present in 2.6.32 and newer</font><br/><br/>
			<input name="devmanager" type="radio" checked="checked" value="udev">udev
			<input name="devmanager" type="radio" value="busybox-mdev">mdev
			<input name="devmanager" type="radio" value=" ">kernel
			<br/>
		</div>

		<div id='imagetypebox' class="nblock">
Select the type of image you want.<br/><font size="-2">The 'tar.bz2' option is the most versatile choice since it can be easily converted to other formats later on. The practicality of the other formats depends too much on the device in question to give meaningfull advice here, so we leave that op to you :)</font><br/><br/>
			<input name="imagetype" type="radio" checked="checked" value="tbz2">tar.bz2
			<input name="imagetype" type="radio" value="ext2">ext2
			<input name="imagetype" type="radio" value="ubifs">ubifs2
			<input name="imagetype" type="radio" value="jffs2">jffs2
			<br/>

		</div>
		<div id='imagename' class="nblock">
			Image name.<br/><font size="-2">This is used in the filename offered for download, makes it easier to distinguish between rootfs images after downloading.</font><br/><br/>
			<input type="text" name="name" id="name" value="random-<?print(substr(md5(time()),0,8));?>"/>
		</div>
	</div>
</div>

<div id="packageblock">
		<div id='environment'>
			<br/><b>User environment selection:</b><br/><br/>
            <div class="nblock">Console gives you a bare commandline interface where you can install a GUI into later on. X11 will install an X-window environment and present you with a Desktop Environment option below. Opie is a qt/e 2.0 based environment for PDA style devices.<br/><br/>
			<select name="environment" onChange="environmentChange(this)">
			<? foreach($env_array as $env => $pkgs) {
				print ("\t<option value=\"$pkgs\">$env</option>\n");
			}?>
			</select>
			</div>
        </div>
        
<br/><b>Additional packages selection:</b><br/><br/>
	Select additional packages below, click the <img src='img/expand.gif'> icon to expand or collaps a section. When you're done, click the 'build me!' button.<br/>
	<div id='x11_wm_block' class="nblock">
	<br>X11 Desktop Environments:<br>
	<? foreach ($wm_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"wm\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>
	</div>
	<br/>
	<div id='x11_packages_block' class="nblock">
	<img src='img/expand.gif' onClick="toggleVisibility('x11_packages');"> Additional X11 packages:<br/>
		<div id='x11_packages'>
		<?foreach ($x11_packages_array as $pkg => $pkgdepends) {
			print("<input type=\"checkbox\" name=\"x11_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
		}?>
		</div>
	</div>

	<img src='img/expand.gif' onClick="toggleVisibility('devel');"> Development packages:<br/>
	<div id='devel' class="nblock">
	<?foreach ($devel_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"devel\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>
	</div>

	<img src='img/expand.gif' onClick="toggleVisibility('console_packages');"> Additional console packages:<br/>
	<div id='console_packages' class="nblock">
	<?foreach ($console_packages_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"console_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>
	</div>

	<img src='img/expand.gif' onClick="toggleVisibility('network_packages');"> Network related packages:<br/>
	<div id='network_packages' class="nblock">
	<?foreach ($network_packages_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"network_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>
	</div>

	<img src='img/expand.gif' onClick="toggleVisibility('platform_packages');"> Platform specific packages:<br/>
	<div id='platform_packages' class="nblock">

	<br/>Texas Instruments OMAP family:<br/>
	<?foreach ($platform_omap_packages_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"platform_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>

	<br>Texas Instruments DaVinci family:<br/>
	<?foreach ($platform_davinci_packages_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"platform_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>

	<br/>Marvell XScale Family:<br/>
	<?foreach ($platform_pxa_packages_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"platform_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>

	</div>

</div>

<div id="buildbutton">
<center><input type="submit" value="Build me!"/></center></form>
</div>

<br clear='all'/>
<div id="beverage"></div>
<div id="pkg_progress"></div>
<div id="image_link"></div>
<div id="configure_progress"></div>
<div id="image_progress"></div>
<div id="imgstatus"></div>

<div id="patchbox"><br/><br/>Patches are welcome for the <a href="http://dominion.thruhere.net/git/cgit.cgi/narcissus/">narcissus sources</a></div>
</body>
</html>
