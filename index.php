<html>
<head>
<title>Narcissus - Online image builder for the angstrom distribution</title>
<script language="javascript" type="text/javascript" src="scripts/js/internal_request.js"></script>
<link rel="stylesheet" type="text/css" title="dominion" href="css/dominion.css" media="screen" />
</head>
<body>
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

This is a proof of concept online image builder for the Angstrom distribution. The basic operation is simple:
<ol>
<li>select machine from dropdown list</li>
<li>check packages you want to have included</li>
<li>Change the random name for the image into the name you want it to be called</li>
<li>press enter</li>
</ol>

<div id="form" class="kader"><form name="entry_form" onsubmit="javascript:configureImage();return false">
Machine:
<select name="machine">
<? machine_dropdown(); ?>
</select >

<hr width="80%"/>
Package selections:<br/><br/>
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

foreach ($base_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"pkg\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "<hr width='80%'/>\n\n";

foreach ($wm_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"wm\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "<hr width='80%'/>\n\n";

foreach ($devel_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"devel\" value=\"$pkgdepends\">$pkg<br/>\n");
}
print "<hr width='80%'/>\n\n";

?>
Image name:
  <input type="text" name="name" id="name" value="random-<?print(substr(md5(time()),0,8));?>"/>
<hr width="80%"/>
<center><input type="submit" value="Build me!"/></submit></form></div>

<br clear='all'/>
<div id="status"></div>
<div id="image_link"></div>
<div id="configure_progress"></div>
<div id="image_progress"></div>


</body>
</html>
