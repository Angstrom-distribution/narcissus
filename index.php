<html>
<head>
<title>Narcissus - Online image builder for the angstrom distribution</title>
<script language="javascript" type="text/javascript" src="./internal_request.js"></script>
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
<form name="entry_form" onsubmit="javascript:assembleImage();return false">
  Machine:

<select name="machine">
<? machine_dropdown(); ?>
</select >

  Image name:

  <input type="text" name="name" id="name" />

</form>

<div id="image_progress"></div>

</body>
</html>
