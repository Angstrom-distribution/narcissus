<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2008 - all rights reserved 
 *
 * basic operation:
 * 1) select machine and assemble arch.conf
 * 2) select package set and print to file
 * 3) have daemon prime rootfs with opkg-collateral and angstrom-feed-configs
 * 4) have daemon install package set
 * 5) have daemon tar it up
 */


$base_pkg_set = " task-base angstrom-version ";

if (isset($_GET["machine"])) {
	$machine = $_GET["machine"];
} else {
	print "Invalid machine";
	exit;
}

if (isset($_GET["name"]) && $_GET["name"] != "") {
	$name = $_GET["name"];
} else {
	$name = "unnamed";
}

function assemble_image($machine, $name, $pkgs) {
	system ("scripts/assemble-image.sh $machine $name-image");
}


print "Online image builder for angstrom\n<br> configured for $machine and $name-image\n<br>";
print "<pre>";
assemble_image($machine, $name, $base_pkg_set);
print "<pre>";

if (file_exists("deploy/$machine/$machine-$name-image.tar.bz2")) {
	print "<p><a href='deploy/$machine/$machine-$name-image.tar.bz2'>your image!</a>";
} else {
	print "Image not found, something went wrong :/";
}

?>
