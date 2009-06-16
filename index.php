<html>
<head>
<title>Narcissus - Online image builder for the angstrom distribution</title>
<script language="javascript" type="text/javascript" src="scripts/js/MochiKit.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/internal_request.js"></script>

<script language="javascript" type="text/javascript">
function initForm() {
	toggleVisibility('devel');
	toggleVisibility('devman');
	toggleVisibility('console_packages');
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
<body onLoad="initForm()">
<?
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
?>

This is a proof of concept online image builder for the Angstrom distribution. <div id="intro">The basic operation is simple:
<ol>
<li>select machine from dropdown list</li>
<li>select packages you want to have included, click the <img src='img/expand.gif'> sign to expand a section.</li>
<li>Change the random name for the image into the name you want it to be called</li>
<li>Click the "build me!" button</li>
</ol></div>
<br/>
Depending on the load of this machine and the feed server the process might take a few <b>minutes</b>, so get a beverage of your choice and <b>DON'T</b> hit refresh.
<br/><br/>
<div id="form" class="kader"><form name="entry_form" onsubmit="javascript:configureImage();return false">
Machine:
<select name="machine">
<? machine_dropdown(); ?>
</select >
<br>
Release:
<select name="configs">
	<option value="stable">stable</option>
	<option value="unstable">unstable</option>
</select>

<hr width="80%"/>
Base system:<br/><br/>
<?

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

print "<div id='base'>";
foreach ($base_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"pkg\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "</div>";
print "<hr width='80%'/>\n\n";
?>
<img src='img/expand.gif' onClick="toggleVisibility('devman');"> /dev manager:<br/>

<div id='devman'>
<input name="devmanager" type="radio" checked="checked" value="udev">udev
<input name="devmanager" type="radio" value="busybox-mdev">mdev
</div>
<hr width='80%'/>
<?
print "Environment:\n";
print "<select name=\"environment\" onChange=\"environmentChange(this)\">";
foreach($env_array as $env => $pkgs) {
	print ("\t<option value=\"$pkgs\">$env</option>\n");
}
print "</select>";
print "<br>";

print "<div id='x11_wm_block'>";
print "<br>X11 window managers:<br>\n";
foreach ($wm_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"wm\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "</div>";

print "<hr width='80%'/>\n\n";
print "<img src='img/expand.gif' onClick=\"toggleVisibility('devel');\"> Development packages:<br/><br/>\n";
print "<div id='devel'>";
foreach ($devel_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"devel\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "</div>";

print "<hr width='80%'/>\n\n";

print "<img src='img/expand.gif' onClick=\"toggleVisibility('console_packages');\"> Additional console packages:<br/><br/>\n";
print "<div id='console_packages'>";
foreach ($console_packages_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"console_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "</div>";
print "<hr width='80%'/>\n\n";

print "<div id='x11_packages_block'>";
print "<img src='img/expand.gif' onClick=\"toggleVisibility('x11_packages');\"> Additional X11 packages:<br/><br/>\n";
print "<div id='x11_packages'>";
foreach ($x11_packages_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"x11_packages\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "</div>";
print "<hr width='80%'/>\n\n";
print "</div>";

?>
Image name:
  <input type="text" name="name" id="name" value="random-<?print(substr(md5(time()),0,8));?>"/>
<hr width="80%"/>
<center><input type="submit" value="Build me!"/></center></form></div>

<br clear='all'/>
<div id="pkg_progress"></div>
<div id="image_link"></div>
<div id="configure_progress"></div>
<div id="image_progress"></div>
<div id="imgstatus"></div>

<br/><br/>Patches are welcome for the <a href="http://dominion.thruhere.net/git/cgit.cgi/narcissus/">narcissus sources</a>
</body>
</html>
