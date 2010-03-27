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
	toggleVisibility('expert');

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

function guruChange() {
	toggleVisibility('expert');
}
</script>

<link rel="stylesheet" type="text/css" title="dominion" href="css/dominion.css" media="screen" />
</head>
<body onLoad="initForm();"><?
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
		print ("\t\t\t\t<option value=\"$value\">$value</option>\n");
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

?>
<div id="summary"></div><form name="entry_form" onsubmit="javascript:configureImage(); toggleVisibility('buildbutton'); return false">
	<div id="settings">
		<div id="welcomedialog">
			Welcome!<br/><br/>This is an online tool to create so called 'rootfs' images for your favourite device. This page will guide through the basic options and will close to let you select the additional packages you want.<br/><br/>
		</div>
		<br/><b>Base settings:</b><br/><br/>
		<div id="machinedialog" class="nblock">
			Select the machine you want to build your rootfs image for:<br/><br/>
			<select name="machine">
<? machine_dropdown(); ?>
			</select > 
		<br/>
		</div>
		<div id='imagename' class="nblock">
			Choose your image name.<br/><font size="-2">This is used in the filename offered for download, makes it easier to distinguish between rootfs images after downloading.</font><br/><br/>
			<input type="text" name="name" id="name" value="random-<?print(substr(md5(time()),0,8));?>"/>
		</div>

		<div id="guru" class="nblock">
			Choose the complexity of the options below.<br/><font size="-2"><i>simple</i> will hide the options most users don't need to care about and <i>advanced</i> will give you lots of options to fiddle with.</font><br/><br/>
			<select name="guru" onChange="guruChange(this)">
				<option value="basic">simple</option>
				<option value="hard">advanced</option>
			</select>
		</div>

		<div id="expert">
		<br/><b>Advanced settings:</b><br/><br/>

			<div id="releasedialog" class="nblock">
				Select the release you want to base your rootfs image on.<br/><font size="-2">The 'stable' option will give you a working system, but will not have the latest versions of packages. The 'unstable' option will give you access to all the latest packages the developers have uploaded, but is known to break every now and then.</font><br/><br/>
				<select name="configs">
					<option value="unstable">unstable</option>
					<option value="stable">stable</option>
				</select>
			</div>
			<div id="basesystemdialog" class="nblock">
				Base system<br/> <font size="-2">Each entry down is a superset of the one above it. Busybox will give you only busybox, usefull for e.g. small ramdisks. Task-boot will give you the minimal set of drivers and packages you need to boot. Task-base will give you drivers for non-essential features of your system, e.g. bluetooth. Options below that will include even more drivers for a smoother experience with USB based devices.</font><br/><br/>
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
		
		</div>

	</div>
</div>

<div id="packageblock">
		<div id='environment'>
			<br/><b>User environment selection:</b><br/><br/>
            <div class="nblock">Console gives you a bare commandline interface where you can install a GUI into later on. X11 will install an X-window environment and present you with a Desktop Environment option below. Opie is a qt/e 2.0 based environment for PDA style devices.<br/><br/>
			<select name="environment" onChange="environmentChange(this)">
				<option value="">Console only</option>
				<option value="angstrom-x11-base-depends">X11</option>
				<option value="task-opie-base task-opie-base-applets task-opie-base-inputmethods task-opie-base-apps task-opie-base-settings task-opie-base-decorations task-opie-base-styles task-opie-base-pim task-opie-extra-settings task-opie-bluetooth task-opie-irda">Opie</option>
			</select>
			</div>
			<div id='x11_wm_block'>
				<br/><b>X11 Desktop Environments:</b><br/><br/>
				<div  class="nblock">
					<input type="checkbox" name="wm" value="angstrom-gpe-task-base e-wm e-wm-config-standard e-wm-config-default">Enlightenment<br/>
					<input type="checkbox" name="wm" value="angstrom-task-gnome shadow bash">GNOME<br/>
					<input type="checkbox" name="wm" value="task-xfce46-base task-xfce46-extras shadow">Xfce 4.6<br/>
					<input type="checkbox" name="wm" value="angstrom-gpe-task-base">Matchbox<br/>
					<input type="checkbox" name="wm" value=" angstrom-gpe-task-base e-wm e-wm-config-illume">Illume<br/>
				</div>
			</div>
        </div>
        
<br/><b>Additional packages selection:</b><br/><br/>
	Select additional packages below, click the <img src='img/expand.gif'> icon to expand or collaps a section. When you're done, click the 'build me!' button.<br/>

	<br/>
	<div id='x11_packages_block'>
	<img src='img/expand.gif' onClick="toggleVisibility('x11_packages');"> Additional X11 packages:<br/>
		<div id='x11_packages' class="nblock">
			<input type="checkbox" name="x11_packages" value="abiword">Abiword<br/>
			<input type="checkbox" name="x11_packages" value="duke3d">Duke Nukem 3D<br/>
			<input type="checkbox" name="x11_packages" value="prboom">Doom (prboom)<br/>
			<input type="checkbox" name="x11_packages" value="e-uae">E-uae<br/>
			<input type="checkbox" name="x11_packages" value="ekiga">Ekiga<br/>
			<input type="checkbox" name="x11_packages" value="epiphany">Epiphany web browser<br/>
			<input type="checkbox" name="x11_packages" value="evince">Evince<br/>
			<input type="checkbox" name="x11_packages" value="fennec">Fennec<br/>
			<input type="checkbox" name="x11_packages" value="firefox">Firefox<br/>
			<input type="checkbox" name="x11_packages" value="fldigi">FLDigi<br/>
			<input type="checkbox" name="x11_packages" value="gimp">Gimp<br/>
			<input type="checkbox" name="x11_packages" value="gnome-games">Gnome Games<br/>
			<input type="checkbox" name="x11_packages" value="gnumeric">Gnumeric<br/>
			<input type="checkbox" name="x11_packages" value="task-gpe-pim">GPE PIM suite<br/>
			<input type="checkbox" name="x11_packages" value="midori">Midori web browser<br/>
			<input type="checkbox" name="x11_packages" value="connman-gnome">Moblin connection manager GTK+ applet<br/>
			<input type="checkbox" name="x11_packages" value="mythtv mythtv-frontend">MythTV<br/>
			<input type="checkbox" name="x11_packages" value="numptyphysics">Numptyphysics<br/>
			<input type="checkbox" name="x11_packages" value="pidgin">Pidgin IM<br/>
			<input type="checkbox" name="x11_packages" value="contacts dates tasks">Pimlico<br/>
			<input type="checkbox" name="x11_packages" value="sdlquake">Quake 1<br/>
			<input type="checkbox" name="x11_packages" value="quake2">Quake 2<br/>
			<input type="checkbox" name="x11_packages" value="quetoo">Quake 2 (quetoo)<br/>
			<input type="checkbox" name="x11_packages" value="ioquake3">Quake 3 (ioq3)<br/>
			<input type="checkbox" name="x11_packages" value="scummvm">ScummVM<br/>
			<input type="checkbox" name="x11_packages" value="sdrshell">SDR-shell<br/>
			<input type="checkbox" name="x11_packages" value="stalonetray">Stalonetray<br/>
			<input type="checkbox" name="x11_packages" value="totem">Totem media player<br/>
			<input type="checkbox" name="x11_packages" value="wireshark">Wireshark<br/>
			<input type="checkbox" name="x11_packages" value="zhone frameworkd">Zhone<br/>
		</div>
	</div>

	<img src='img/expand.gif' onClick="toggleVisibility('devel');"> Development packages:<br/>
	<div id='devel' class="nblock">
		<input type="checkbox" name="devel" value="python-core python-modules">Python<br/>
		<input type="checkbox" name="devel" value="perl perl-modules">Perl<br/>
		<input type="checkbox" name="devel" value="mono mono-mcs">Mono (C#, .NET)<br/>
		<input type="checkbox" name="devel" value="task-native-sdk">Toolchain<br/>
		<input type="checkbox" name="devel" value="oprofile">OProfile<br/>
		<input type="checkbox" name="devel" value="gdb gdbserver">GDB<br/>
		<input type="checkbox" name="devel" value="task-proper-tools">Busybox replacements<br/>
	</div>

	<img src='img/expand.gif' onClick="toggleVisibility('console_packages');"> Additional console packages:<br/>
	<div id='console_packages' class="nblock">
		<input type="checkbox" name="console_packages" value="aircrack-ng">Aircrack-ng<br/>
		<input type="checkbox" name="console_packages" value="kernel-modules">All kernel modules<br/>
		<input type="checkbox" name="console_packages" value="alsa-utils-alsamixer alsa-utils-aplay alsa-utils-amixer alsa-utils-aconnect alsa-utils-iecset alsa-utils-speakertest alsa-utils-aseqnet alsa-utils-aseqdump alsa-utils-alsaconf alsa-utils-alsactl">Alsa utils<br/>
		<input type="checkbox" name="console_packages" value="task-beagleboard-demo">Beagleboard demo<br/>
		<input type="checkbox" name="console_packages" value="bluez-utils">Bluez<br/>
		<input type="checkbox" name="console_packages" value="cwiid">cwiid<br/>
		<input type="checkbox" name="console_packages" value="dvb-azap dvb-tzap dvb-czap dvb-szap dvb-scan wscan dvbstream dvbtune">DVB-utils<br/>
		<input type="checkbox" name="console_packages" value="flite libflite-cmu-us-kal1">Flite<br/>
		<input type="checkbox" name="console_packages" value="gdbserver">Gdbserver<br/>
		<input type="checkbox" name="console_packages" value="gnuradio">Gnuradio<br/>
		<input type="checkbox" name="console_packages" value="git">Git<br/>
		<input type="checkbox" name="console_packages" value="gsm0710muxd">GSM0710muxd<br/>
		<input type="checkbox" name="console_packages" value="gst-plugins-bad-meta gst-plugins-base-meta gst-plugins-good-meta gst-plugins-ugly-meta ">Gstreamer<br/>
		<input type="checkbox" name="console_packages" value="i2c-tools">I2C-tools<br/>
		<input type="checkbox" name="console_packages" value="jamvm">JamVM<br/>
		<input type="checkbox" name="console_packages" value="julius">Julius speech recognizer<br/>
		<input type="checkbox" name="console_packages" value="kismet">Kismet<br/>
		<input type="checkbox" name="console_packages" value="lcd4linux">LCD4Linux<br/>
		<input type="checkbox" name="console_packages" value="lirc">LIRC<br/>
		<input type="checkbox" name="console_packages" value="mediatomb">Mediatomb<br/>
		<input type="checkbox" name="console_packages" value="mplayer">MPlayer<br/>
		<input type="checkbox" name="console_packages" value="mythtv-backend">Mythtv backend<br/>
		<input type="checkbox" name="console_packages" value="octave">Octave<br/>
		<input type="checkbox" name="console_packages" value="opencv-samples">OpenCV<br/>
		<input type="checkbox" name="console_packages" value="powertop">Powertop<br/>
		<input type="checkbox" name="console_packages" value="qt4-embedded-demos qt4-embedded-plugin-gfxdriver-gfxvnc qt4-embedded">QT/e 4<br/>
		<input type="checkbox" name="console_packages" value="screen">Screen<br/>
		<input type="checkbox" name="console_packages" value="vdr">Video Disc Recoder<br/>
	</div>

	<img src='img/expand.gif' onClick="toggleVisibility('network_packages');"> Network related packages:<br/>
	<div id='network_packages' class="nblock">
		<input type="checkbox" name="network_packages" value="apache2">Apache<br/>
		<input type="checkbox" name="network_packages" value="boa">Boa<br/>
		<input type="checkbox" name="network_packages" value="cherokee">Cherokee<br/>
		<input type="checkbox" name="network_packages" value="dropbear">Dropbear SSH server<br/>
		<input type="checkbox" name="network_packages" value="connman">Moblin connection manager<br/>
		<input type="checkbox" name="network_packages" value="networkmanager networkmanager-openvpn">NetworkManager<br/>
		<input type="checkbox" name="network_packages" value="network-manager-applet">NetworkManager GUI applet<br/>
		<input type="checkbox" name="network_packages" value="nmap">Nmap<br/>
		<input type="checkbox" name="network_packages" value="ntp">NTP<br/>
		<input type="checkbox" name="network_packages" value="ntpclient">NTPclient<br/>
		<input type="checkbox" name="network_packages" value="ntpdate">NTPdate<br/>
		<input type="checkbox" name="network_packages" value="rtorrent">Rtorrent<br/>
		<input type="checkbox" name="network_packages" value="samba">Samba<br/>
		<input type="checkbox" name="network_packages" value="wireless-tools">Wireless-tools<br/>
	</div>

	<img src='img/expand.gif' onClick="toggleVisibility('platform_packages');"> Platform specific packages:<br/>
	<div id='platform_packages' class="nblock">

	<br/>Texas Instruments OMAP family:<br/>
		<input type="checkbox" name="platform_packages" value="gstreamer-ti">Texas Instruments Gstreamer plugins<br/>
		<input type="checkbox" name="platform_packages" value="libgles-omap3">PowerVR SGX drivers for OMAP3<br/>
		<input type="checkbox" name="platform_packages" value="libgles-omap3-rawdemos">PowerVR SGX demos for framebuffer<br/>
		<input type="checkbox" name="platform_packages" value="libgles-omap3-x11demos">PowerVR SGX demos for X11<br/>
		<input type="checkbox" name="platform_packages" value="qt4-embedded-plugin-gfxdriver-gfxpvregl">PowerVR SGX gfxdriver plugin for QT/embedded<br/>
		<input type="checkbox" name="platform_packages" value="qt4-plugin-graphicssystems-glgraphicssystem">PowerVR SGX gfxdriver plugin for QT/X11<br/>
		<input type="checkbox" name="platform_packages" value="bc-cube-x11">TI texture streaming demo for X11<br/>
		<input type="checkbox" name="platform_packages" value="bc-cube-fb">TI texture streaming demo for framebuffer<br/>
		<input type="checkbox" name="platform_packages" value="quake3-pandora-gles libgles-omap3">Quake 3 (GLES)<br/>
		<input type="checkbox" name="platform_packages" value="ti-julius-demo">Julius demo for Texas Instruments<br/>

	<br/>Texas Instruments DaVinci family:<br/>
		<input type="checkbox" name="platform_packages" value="gstreamer-ti">Texas Instruments Gstreamer plugins<br/>
		<input type="checkbox" name="platform_packages" value="ti-julius-demo">Julius demo for Texas Instruments<br/>

	<br/>Marvell XScale Family:<br/>
		<input type="checkbox" name="platform_packages" value="pxaregs">PXA register utility<br/>

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
