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

if (isset($_POST["action"]) && $_POST["action"] != "") {
	$action = $_POST["action"];
} else {
	print "Invalid action: $action";
	exit;
}

if (isset($_POST["machine"])) {
	$machine = $_POST["machine"];
} else {
	print "Invalid machine";
	exit;
}

if (isset($_POST["name"]) && $_POST["name"] != "") {
	$name = $_POST["name"];
} else {
	$name = "unnamed";
}

if (isset($_POST["pkgs"]) && $_POST["pkgs"] != "") {
		$pkg = $_POST["pkgs"];
} else {
    $pkg = "task-boot";
}

switch($action) {
case "assemble_image":
			assemble_image($machine, $name);
			break;
case "configure_image":
			configure_image($machine, $name, $pkg);
			break;
case "show_image_link":
			show_image_link($machine, $name);
			break;
}


function show_image_link($machine, $name) {
	if (file_exists("deploy/$machine/$name-image-$machine.tar.bz2")) {
		$imgsize = round(filesize("deploy/$machine/$name-image-$machine.tar.bz2") / (1024 * 1024),2);
		print "<br>Click to download <a href='deploy/$machine/$name-image-$machine.tar.bz2'>your image</a> [$imgsize MiB]";
	} else {
		print "Image not found, something went wrong :/";
	}
}

function configure_image($machine, $name, $pkgs) {
	print "<pre>";
	system ("scripts/configure-image.sh $machine $name-image");
	$handle = fopen("deploy/$machine/$name-image-packages.txt", "w");
	if ($handle) {
		fwrite($handle, $pkgs);
		fclose($handle);
	} else {
		print "handle failed!";
	}
	
	print "</pre>";
}

function assemble_image($machine, $name) {
	print "<pre>";
	system ("scripts/assemble-image.sh $machine $name-image");
	print "</pre>";
}



?>
