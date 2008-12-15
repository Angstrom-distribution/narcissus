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

if (isset($_GET["action"]) && $_GET["action"] != "") {
	$action = $_GET["action"];
} else {
	print "Invalid action: $action";
	exit;
}

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

switch($action) {
case "assemble_image":
			assemble_image($machine, $name, $base_pkg_set);
			break;
case "configure_image":
			configure_image($machine, $name, $base_pkg_set);
			break;
case "show_image_link":
			show_image_link($machine, $name);
			break;
}


function show_image_link($machine, $name) {
	if (file_exists("deploy/$machine/$name-image-$machine.tar.bz2")) {
		print "<br>Click to download <a href='deploy/$machine/$name-image-$machine.tar.bz2'>your image</a>";
	} else {
		print "Image not found, something went wrong :/";
	}
}

function configure_image($machine, $name) {
	print "<pre>";
	system ("scripts/configure-image.sh $machine $name-image");
	print "</pre>";
}

function assemble_image($machine, $name, $pkgs) {
	print "<pre>";
	system ("scripts/assemble-image.sh $machine $name-image");
	print "</pre>";
}



?>
