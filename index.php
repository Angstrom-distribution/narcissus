<html>
<head>
<title>Narcissus - Online image builder for the angstrom distribution</title>
<script language="javascript" type="text/javascript" src="./internal_request.js"></script>
<link rel="stylesheet" type="text/css" title="dominion" href="css/dominion.css" media="screen" />
</head>
<body>
<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2008 - all rights reserved 
 */

function machine_dropdown() {
if ($handle = opendir ('./conf/'))
  {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir ($handle)))
      {
    if ($file != "." && $file != "..")
      {
        print ("<option value='$file'>$file</option>");
      }
      }
    closedir ($handle);
  }
}

?>

This is a proof of concept online image builder for the Angstrom distribution. The basic operation is simple:
<ol>
<li>select machine from dropdown list</li>
<li>check packages you want to have included</li>
<li>enter the preferred name for the image that will be built</li>
<li>press enter</li>
</ol>

<div id="form" class="kader"><form name="entry_form" onsubmit="javascript:configureImage();return false">
Machine: <select name="machine">
<? machine_dropdown(); ?>
</select >

<hr width="80%"/>
Package selections:<br/><br/>
<?
$package_array = array("task-base" => "task-base", 
                       "X11" => "angstrom-x11-base-depends angstrom-gpe-task-base", 
                       "Enlightenment" => "e-wm e-wm-config-standard e-wm-config-default angstrom-x11-base-depends angstrom-gpe-task-base",
                       "Metacity" => "metacity angstrom-x11-base-depends angstrom-gpe-task-base",
                       "Samba" => "samba",
                       "Apache" => "apache2",
                       "Python" => "python-core python-modules",
                       "Perl" => "perl perl-modules",
                       "Toolchain" => "task-native-sdk",
                       "Busybox replacements" => "task-proper-tools", 
                       "Mplayer" => "mplayer");

foreach ($package_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"pkg\" value=\"$pkgdepends\">$pkg<br/>\n");
}
?>
<hr width="80%"/>
 Image name:
  <input type="text" name="name" id="name" />
</form></div>

<br clear='all'/>
<div id="status"></div>
<div id="image_link"></div>
<div id="configure_progress"></div>
<div id="image_progress"></div>


</body>
</html>
