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
<div id="form" class="kader"><form name="entry_form" onsubmit="javascript:configureImage();return false">
  Machine:

<select name="machine">
<? machine_dropdown(); ?>
</select >

  Image name:
  <input type="text" name="name" id="name" />
<hr width="80%"/>
Package selections:<br/><br/>
<?
$package_array = array("task-base" => "task-base", "X11" => "angstrom-x11-base-depends", "Enlightenment" => "e-wm e-wm-config-standard e-wm-config-default" );

foreach ($package_array as $pkg => $pkgdepends) {
	print("<input type=\"checkbox\" name=\"pkg\" value=\"$pkgdepends\">$pkg<br/>\n");
}
?>
</form></div>

<br clear='all'/>
<div id="status"></div>
<div id="image_link"></div>
<div id="configure_progress"></div>
<div id="image_progress"></div>


</body>
</html>
