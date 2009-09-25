<html>
<head>
<title>Narcissus - Online image builder for the angstrom distribution</title>
<script language="javascript" type="text/javascript" src="scripts/js/MochiKit.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/internal_request.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/jquery-1.3.2.min.js"></script>

<script language="javascript" type="text/javascript">
function initForm() {
	toggleVisibility('packageblock');
	
	toggleVisibility('machinedialog');
	toggleVisibility('releasedialog');
	toggleVisibility('basesystemdialog');
	toggleVisibility('devman');
	toggleVisibility('environment');
	toggleVisibility('imagetypebox');
	toggleVisibility('imagename');

	toggleVisibility('buildbutton');
	toggleVisibility('patchbox');
	
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
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2008, 2009 - all rights reserved 
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

$base_array = array("small (<a href='$repourl=task-boot' target='foo'>task-boot</a>)" => "task-boot", 
					"regular (<a href='$repourl=task-base' target='foo'>task-base</a>)" => "task-base",
					"extended (<a href='$repourl=task-base-extended' target='foo'>task-base-extended</a>)" => "task-base-extended");

$env_array = array("Console only" => "",
				   "X11" => "angstrom-x11-base-depends angstrom-gpe-task-base",
				   "Opie" => "task-opie-base task-opie-base-applets task-opie-base-inputmethods task-opie-base-apps task-opie-base-settings task-opie-base-decorations task-opie-base-styles task-opie-base-pim task-opie-extra-settings task-opie-bluetooth task-opie-irda");

$wm_array = array("Matchbox" => "",
				  "Illume" => "e-wm e-wm-config-illume",
				  "Enlightenment" => "e-wm e-wm-config-standard e-wm-config-default",
				  "Metacity" => "metacity");

$devel_array = array("Python" => "python-core python-modules",
					 "Perl" => "perl perl-modules",
					 "Toolchain" => "task-native-sdk",
					 "OProfile" => "oprofile",
					 "GDB" => "gdb gdbserver",
					 "Busybox replacements" => "task-proper-tools");

$console_packages_array = array("Aircrack-ng" => "aircrack-ng",
								"All kernel modules" => "kernel-modules",
								"Alsa utils" => "alsa-utils-alsamixer alsa-utils-aplay alsa-utils-amixer alsa-utils-aconnect alsa-utils-iecset alsa-utils-speakertest alsa-utils-aseqnet alsa-utils-aseqdump alsa-utils-alsaconf alsa-utils-alsactl",
								"Apache" => "apache2",
								"Beagleboard demo" => "task-beagleboard-demo",
								"Bluez" => "bluez-utils",
								"Boa" => "boa",
								"cwiid" => "cwiid",
								"Cherokee" => "cherokee",
								"Dropbear SSH server" => "dropbear",
								"Flite" => "flite libflite-cmu-us-kal1",
								"Gdbserver" => "gdbserver",
								"Gnuradio" => "gnuradio",
								"Git" => "git",
								"GSM0710muxd" => "gsm0710muxd",
								"Gstreamer" => "gst-plugins-bad-meta gst-plugins-base-meta gst-plugins-good-meta gst-plugins-ugly-meta ",
								"I2C-tools" => "i2c-tools",
								"JamVM" => "jamvm",
								"Julius speech recognizer" => "julius",
								"Julius demo for Texas Instruments" => "ti-julius-demo",
								"Kismet" => "kismet",
								"LCD4Linux" => "lc4linux",
								"LIRC" => "lirc",
								"Mediatomb" => "mediatomb",
								"Moblin connection manager" => "connman",
								"MPlayer" => "mplayer",
								"Nmap" => "nmap",
								"NTP" => "ntp",
								"NTPclient" => "ntpclient",
								"NTPdate" => "ntpdate",
								"Octave" => "octave",
								"OpenCV" => "opencv-samples",
								"Powertop" => "powertop",
								"Rtorrent" => "rtorrent",
								"Samba" => "samba",
								"Screen" => "screen",
								"Wireless-tools" => "wireless-tools");

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
							"MythTV" => "mythtv mythtv-theme-blue mythtv-theme-default",
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
							"Wireshark" => "wireshark",
							"Zhone" => "zhone frameworkd");



?><form name="entry_form" onsubmit="javascript:configureImage(); toggleVisibility('buildbutton'); return false"><!-- #dialog is the id of a DIV defined in the code below --><div id="boxes">
	<div id="dialog" class="window">
		<div id="welcomedialog">
			Welcome!<br/><br/>This is an online tool to create so called 'rootfs' images for your favourite device. This wizard will guide through the basic options and will close to let you select the additional packages you want.<br/>
			<br/><table width='100%' valign='bottom'><tr><td align='right'><a href="#machinedialog" onClick="toggleVisibility('welcomedialog') ; toggleVisibility('machinedialog');">Machine selection &gt;</a></td></table>
		</div>
		<div id="machinedialog">
			Select the machine you want to build your rootfs image for:<br/><br/>
			<select name="machine">
				<? machine_dropdown(); ?>
			</select > 
		<br/>
		     <table width='100%' valign='bottom'><tr><td align='right'><a href="#releasedialog" onClick="toggleVisibility('machinedialog') ; toggleVisibility('releasedialog');">Release selection &gt;</a></td></table>
		</div>
		<div id="releasedialog">
			Select the release you want to base your rootfs image on.<br/><font size="-2">The 'stable' option will give you a working system, but will not have the latest versions of packages. The 'unstable' option will give you access to all the latest packages the developers have uploaded, but is known to break every now and then.</font><br/><br/>
			<select name="configs">
				<option value="stable">stable</option>
				<option value="unstable">unstable</option>
			</select>
			<br/><table width='100%' valign='bottom'><tr><td align='left'><a href="#machinedialog" onClick="toggleVisibility('releasedialog') ; toggleVisibility('machinedialog');">&lt; Machine selection</a></td>
			     <td align='right'><a href="#basesystemdialog" onClick="toggleVisibility('releasedialog') ; toggleVisibility('basesystemdialog');">Base system selection &gt;</a></td></table>
		</div>
		<div id="basesystemdialog">
			Base system<br> <font size="-2">Each entry down is a superset of the one above it. Task-boot will give you the minimal set of drivers and packages you need to boot. Task-base will give you drivers for non-essential features of your system, e.g. bluetooth. Options below that will include even more drivers for a smoother experience with USB based devices.</font><br/><br/>

				<? 
				foreach ($base_array as $pkg => $pkgdepends) {
					print("<input type=\"radio\" name=\"pkg\" value=\"$pkgdepends\">$pkg<br/>\n");
				}
				?>
				<br/>
				<table width='100%' valign='bottom'><tr><td align='left'><a href="#releasedialog" onClick="toggleVisibility('basesystemdialog') ; toggleVisibility('releasedialog');">&lt;Release selection</a></td>
				<td align='right'><a href="#devmandialog" onClick="toggleVisibility('basesystemdialog') ; toggleVisibility('devman');">Devicemanager selection &gt;</a></td></table>
		</div>

		<div id='devman'>
			Select the /dev manager.<br/><font size="-2">Udev is generally the best choice, only select mdev for fixed-function devices and if you know what you're doing</font><br/><br/>
			<input name="devmanager" type="radio" checked="checked" value="udev">udev
			<input name="devmanager" type="radio" value="busybox-mdev">mdev
			<br/>
			<table width='100%' valign='bottom'><tr><td align='left'><a href="#basesystemdialog" onClick="toggleVisibility('devman') ; toggleVisibility('basesystemdialog');">&lt; Base system selection</a></td>
			<td align='right'><a href="#environment" onClick="toggleVisibility('devman') ; toggleVisibility('environment');">Environment selection &gt;</a></td></table>
		</div>

		<div id='environment'>
			Select the preferred user environment.<br/><font size="-2">Console gives you a bare commandline interface where you can install a GUI into later on. X11 will install an X-window environment and present you with a windowmanager option during the package selection phase. Opie is a qt/e 2.0 based environment for PDA style devices.</font><br/><br/>
			<select name="environment" onChange="environmentChange(this)">
			<? foreach($env_array as $env => $pkgs) {
				print ("\t<option value=\"$pkgs\">$env</option>\n");
			}?>
			</select>
			<br/>
			<table width='100%' valign='bottom'><tr><td align='left'><a href="#devmandialog" onClick="toggleVisibility('environment') ; toggleVisibility('devman');">&lt; Devicemanager selection</a></td>
			<td align='right'><a href="#imagetype" onClick="toggleVisibility('environment') ; toggleVisibility('imagetypebox');">Image type &gt;</a></td></table>
		</div>

		<div id='imagetypebox'>
Select the type of image you want.<br/><font size="-2">The 'tar.bz2' option is the most versatile choice since it can be easily converted to other formats later on. The practicality of the other formats depends too much on the device in question to give meaningfull advice here, so we leave that op to you :)</font><br/><br/>
			<input name="imagetype" type="radio" checked="checked" value="tbz2">tar.bz2
			<input name="imagetype" type="radio" value="ext2">ext2
			<input name="imagetype" type="radio" value="ubifs">ubifs2
			<input name="imagetype" type="radio" value="jffs2">jffs2
			<br/>
			<table width='100%' valign='bottom'><tr><td align='left'><a href="#environment" onClick="toggleVisibility('imagetypebox') ; toggleVisibility('environment');">&lt; Environment selection</a></td>
			<td align='right'><a href="#imagename" onClick="toggleVisibility('imagetypebox') ; toggleVisibility('imagename');">Image name &gt;</a></td></table>

		</div>
		<div id='imagename'>
			Image name.<br/><font size="-2">This is used in the filename offered for download, makes it easier to distinguish between rootfs images after downloading.</font><br/><br/>
			<input type="text" name="name" id="name" value="random-<?print(substr(md5(time()),0,8));?>"/>
			<!-- close button is defined as close class -->
			<table width='100%' valign='bottom'><tr><td align='left'><a href="#environment" onClick="toggleVisibility('imagename') ; toggleVisibility('imagetypebox');">&lt; Image type</a></td>
<td align='right'><a href="#final" onClick="$('#mask, .window').hide(); toggleVisibility('packageblock'); toggleVisibility('buildbutton'); toggleVisibility('patchbox'); toggleVisibility('imagename');">Package selection &gt;</a></td></table>

		</div>

	</div>
	<!-- Do not remove div #mask, because you'll need it to fill the whole screen -->	
	<div id="mask"></div>
</div>

<div id="packageblock">
	<div id='x11_wm_block'>
	<br>X11 window managers:<br>
	<? foreach ($wm_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"wm\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>
	</div>

<br/><br/><br/>

	<div id='x11_packages_block'>
	<img src='img/expand.gif' onClick="toggleVisibility('x11_packages');"> Additional X11 packages:<br/><br/>
		<div id='x11_packages'>
		<?foreach ($x11_packages_array as $pkg => $pkgdepends) {
			print("<input type=\"checkbox\" name=\"x11_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
		}?>
		</div>
	</div>

<br/><br/><br/>

	<div id='devel'>
	<?foreach ($devel_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"devel\" value=\"$pkgdepends\">$pkg<br/>\n");
	}?>
	</div>

<br/><br/><br/>

	<div id='console_packages'>
	<?foreach ($console_packages_array as $pkg => $pkgdepends) {
		print("<input type=\"checkbox\" name=\"console_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
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
