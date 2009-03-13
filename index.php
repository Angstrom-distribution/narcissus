<html>
<head>
<title>Narcissus - Online image builder for the angstrom distribution</title>
<script language="javascript" type="text/javascript" src="scripts/js/MochiKit.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/internal_request.js"></script>
<link rel="stylesheet" type="text/css" title="dominion" href="css/dominion.css" media="screen" />
</head>
<body onLoad="toggleVisibility('devel') ; toggleVisibility('devman') ; toggleVisibility('packages')">
<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2008, 2009 - all rights reserved 
 */

function machine_dropdown() {
$machine = array();

if ($handle = opendir ('./conf/'))
  {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir ($handle)))
      {
    	if ($file != "." && $file != ".." && file_exists("./conf/$file/arch.conf"))
      	{
	 $machine[] = $file;
      	}
      }
    closedir ($handle);
  }

sort($machine);
foreach($machine as $value) {
	print ("\t<option value=\"$value\">$value</option>\n");
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

<hr width="80%"/>
Base system:<br/><br/>
<?

$repourl = "http://www.angstrom-distribution.org/repo/?pkgname";

$base_array = array("small (<a href='$repourl=task-boot' target='foo'>task-boot</a>)" => "task-boot", 
		    "regular (<a href='$repourl=task-base' target='foo'>task-base</a>)" => "task-base",
		    "extended (<a href='$repourl=task-base-extended' target='foo'>task-base-extended</a>)" => "task-base-extended");

$wm_array = array("Matchbox" => "angstrom-x11-base-depends angstrom-gpe-task-base",
		  "Illume" => "e-wm e-wm-config-illume angstrom-x11-base-depends angstrom-gpe-task-base",
		  "Enlightenment" => "e-wm e-wm-config-standard e-wm-config-default angstrom-x11-base-depends angstrom-gpe-task-base",
		  "Metacity" => "metacity angstrom-x11-base-depends angstrom-gpe-task-base");

$devel_array = array("Python" => "python-core python-modules",
		     "Perl" => "perl perl-modules",
		     "Toolchain" => "task-native-sdk",
		     "OProfile" => "oprofile",
		     "GDB" => "gdb gdbserver",
		     "Busybox replacements" => "task-proper-tools");

$packages_array = array("Abiword" => "abiword",
			"Aircrack-ng" => "aircrack-ng",
			"All kernel modules" => "kernel-modules",
			"Alsa utils" => "alsa-utils-alsamixer alsa-utils-aplay alsa-utils-amixer alsa-utils-aconnect alsa-utils-iecset alsa-utils-speakertest alsa-utils-aseqnet alsa-utils-aseqdump alsa-utils-alsaconf alsa-utils-alsactl",
			"Apache" => "apache2",
			"Beagleboard demo" => "task-beagleboard-demo",
            "Bluez" => "bluez-utils",
			"Boa" => "boa",
			"cwiid" => "cwiid",
			"Cherokee" => "cherokee",
			"Duke Nukem 3D" => "duke3d",
			"Doom (prboom)" => "prboom",
			"E-uae" => "e-uae",
			"Ekiga" => "ekiga",
			"Epiphany web browser" => "epiphany",
			"Evince" => "evince",
			"Fennec" => "fennec",
			"Firefox" => "firefox",
			"Flite" => "flite libflite-cmu-us-kal1",
			"Gdbserver" => "gdbserver",
			"Gimp" => "gimp",
			"Gnome Games" => "gnome-games",
			"Gnumeric" => "gnumeric",
			"Gnuradio" => "gnuradio",
			"Git" => "git",
			"GPE PIM suite" => "task-gpe-pim",
			"GSM0710muxd" => "gsm0710muxd",
			"Gstreamer" => "gst-plugins-bad-meta gst-plugins-base-meta gst-plugins-good-meta gst-plugins-ugly-meta ",
			"I2C-tools" => "i2c-tools",
			"JamVM" => "jamvm",
			"Kismet" => "kismet",
			"LCD4Linux" => "lc4linux",
			"LIRC" => "lirc",
			"Mediatomb" => "mediatomb",
			"Midori web browser" => "midori",
			"Moblin connection manager" => "connman",
			"Moblin connection manager GTK+ applet" => "connman-gnome",
			"MPlayer" => "mplayer",
			"MythTV" => "mythtv mythtv-theme-blue mythtv-theme-default",
			"Nmap" => "nmap",
			"NTP" => "ntp",
			"NTPclient" => "ntpclient",
			"NTPdate" => "ntpdate",
			"Numptyphysics" => "numptyphysics",
			"Octave" => "octave",
			"OpenCV" => "opencv-samples",
			"Pidgin IM" => "pidgin",
			"Pimlico" => "contacts dates tasks",
			"Powertop" => "powertop",
			"Quake 1" => "sdlquake",
			"Quake 2" => "quake2",
			"Quake 2 (quetoo)" => "quetoo",
			"Quake 3 (ioq3)" => "ioquake3",
			"Samba" => "samba",
			"Screen" => "screen",
			"ScummVM" => "scummvm",
			"Stalonetray" => "stalonetray",
			"Wireless-tools" => "wireless-tools",
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
print "X11 window managers:<br/><br/>\n";
foreach ($wm_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"wm\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "<hr width='80%'/>\n\n";
print "<img src='img/expand.gif' onClick=\"toggleVisibility('devel');\"> Development packages:<br/><br/>\n";
print "<div id='devel'>";
foreach ($devel_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"devel\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "</div>";

print "<hr width='80%'/>\n\n";

print "<img src='img/expand.gif' onClick=\"toggleVisibility('packages');\"> Additional packages:<br/><br/>\n";
print "<div id='packages'>";
foreach ($packages_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"packages\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "</div>";
print "<hr width='80%'/>\n\n";

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

<br/><br/>Patches are welcome for the <a href="http://dominion.thruhere.net/git/?p=narcissus.git;a=summary">narcissus sources</a>
</body>
</html>
