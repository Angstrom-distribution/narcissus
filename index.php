<html>
<head>
<title>Narcissus - Online image builder for the angstrom distribution</title>
<script language="javascript" type="text/javascript" src="scripts/js/MochiKit.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/internal_request.js"></script>
<link rel="stylesheet" type="text/css" title="dominion" href="css/dominion.css" media="screen" />
</head>
<body onLoad="toggleVisibility('devel') ; toggleVisibility('packages')">
<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2008 - all rights reserved 
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

<div id="form" class="kader"><form name="entry_form" onsubmit="javascript:configureImage();return false">
Machine:
<select name="machine">
<? machine_dropdown(); ?>
</select >

<hr width="80%"/>
Base system:<br/><br/>
<?
$base_array = array("task-boot" => "task-boot", 
                    "task-base" => "task-base",
                    "task-base-extended" => "task-base-extended");

$wm_array = array("Matchbox" => "angstrom-x11-base-depends angstrom-gpe-task-base",
                  "Illume" => "e-wm e-wm-config-illume angstrom-x11-base-depends angstrom-gpe-task-base",
                  "Enlightenment" => "e-wm e-wm-config-standard e-wm-config-default angstrom-x11-base-depends angstrom-gpe-task-base",
                  "Metacity" => "metacity angstrom-x11-base-depends angstrom-gpe-task-base");

$devel_array = array("Python" => "python-core python-modules",
                     "Perl" => "perl perl-modules",
                     "Toolchain" => "task-native-sdk",
                     "Busybox replacements" => "task-proper-tools");

$packages_array = array("Abiword" => "abiword",
                        "Aircrack-ng" => "aircrack-ng",
                        "All kernel modules" => "kernel-modules",
                        "Alsa utils" => "alsa-utils-alsamixer alsa-utils-aplay alsa-utils-amixer alsa-utils-aconnect alsa-utils-iecset alsa-utils-speakertest alsa-utils-aseqnet alsa-utils-aseqdump alsa-utils-alsaconf alsa-utils-alsactl",
                        "Apache" => "apache2",
                        "Bluez" => "bluez-utils",
                        "Boa" => "boa",
                        "Cherokee" => "cherokee",
                        "Duke Nukem 3D" => "duke3d",
                        "E-uae" => "e-uae",
                        "Epiphany" => "epiphany",
                        "Evince" => "evince",
                        "Fennec" => "fennec",
                        "Firefox" => "firefox",
                        "Gdbserver" => "gdbserver",
                        "Gimp" => "gimp",
                        "Gnome Games" => "gnome-games",
                        "Gnumeric" => "gnumeric",
                        "Gnuradio" => "gnuradio",
                        "Git" => "git",
                        "GSM0710muxd" => "gsm0710muxd",
                        "I2C-tools" => "i2c-tools",
                        "JamVM" => "jamvm",
                        "Kismet" => "kismet",
                        "Mediatomb" => "mediatomb",
                        "MPlayer" => "mplayer",
                        "MythTV" => "mythtv mythtv-theme-blue mythtv-theme-default",
                        "Nmap" => "nmap",
                        "Numptyphysics" => "numptyphysics",
                        "Octave" => "octave",
                        "OpenCV" => "opencv-samples",
                        "Pidgin IM" => "pidgin",
                        "Samba" => "samba",
                        "Screen" => "screen",
                        "ScummVM" => "scummvm");

print "<div id='base'>";
foreach ($base_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"pkg\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "</div>";
print "<hr width='80%'/>\n\n";

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
