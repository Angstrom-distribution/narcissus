<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2008, 2009 - all rights reserved 
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
	$machine = escapeshellcmd(basename($_POST["machine"]));
} else {
	print "Invalid machine";
	exit;
}

if (isset($_POST["name"]) && $_POST["name"] != "") {
	$name = escapeshellcmd(basename($_POST["name"]));
} else {
	$name = "unnamed";
}

if (isset($_POST["pkgs"]) && $_POST["pkgs"] != "") {
	$pkg = $_POST["pkgs"];
} else {
	$pkg = "task-boot";
}

if (isset($_POST["release"]) && $_POST["release"] != "") {
	$release = $_POST["release"];
} else {
	$release = "stable";
}

if (isset($_POST["imagetype"]) && $_POST["imagetype"] != "") {
	$imagetype = $_POST["imagetype"];
} else {
	$imagetype = "tbz2";
	$imagesuffix = "tar.bz2";
}

switch($imagetype) {
case "tbz2":
			$imagesuffix = "tar.bz2";
			break;
case "ubifs":
			$imagesuffix = "ubi";
			break;
default:
			$imagesuffix = $imagetype;
}

switch($action) {
case "assemble_image":
			print "assembling\n";
			assemble_image($machine, $name, $imagetype);
			break;
case "configure_image":
			print "configuring\n";
			configure_image($machine, $name, $release);
			break;
case "show_image_link":
			show_image_link($machine, $name, $imagesuffix);
			break;
case "install_package":
			print "installing $pkg\n";
			install_package($machine, $name, $pkg);
			break;
}


function show_image_link($machine, $name, $imagesuffix) {
	if (file_exists("deploy/$machine/$name-image-$machine.$imagesuffix")) {
		$randomname = substr(md5(time()), 0, 6);
		mkdir("deploy/$machine/$randomname");
		rename("deploy/$machine/$name-image-$machine.$imagesuffix", "deploy/$machine/$randomname/$name-image-$machine.$imagesuffix");	
		$imgsize = round(filesize("deploy/$machine/$randomname/$name-image-$machine.$imagesuffix") / (1024 * 1024),2);
		print "<br>Click to download <a href='deploy/$machine/$randomname/$name-image-$machine.$imagesuffix'>your $name image for $machine</a> [$imgsize MiB]\n<br/><br/>This will get automatically deleted after 3 days.";
	} else {
		print "Image not found, something went wrong :/";
	}
	if (file_exists("deploy/$machine/$name-image-$machine-sd.img.gz")) {
		rename("deploy/$machine/$name-image-$machine-sd.img.gz", "deploy/$machine/$randomname/$name-image-$machine-sd.img.gz");
		$imgsize = round(filesize("deploy/$machine/$randomname/$name-image-$machine-sd.img.gz") / (1024 * 1024),2);
		print("<br/><br/>This <a href='deploy/$machine/$randomname/$name-image-$machine-sd.img.gz'>raw SD card image</a> has its vfat partition populated with the bootloader and kernel, but has an <b>empty</b> ext3 partition. You can extract the tarball to that partition to make it ready to boot."); 
	}
}

function configure_image($machine, $name, $release) {
	print "Machine: $machine, name: $name\n";
	passthru ("scripts/configure-image.sh $machine $name-image $release && exit");
}

function install_package($machine, $name, $pkg) {
	print "Machine: $machine, name: $name, pkg: $pkg\n";
	passthru ("scripts/install-package.sh $machine $name-image $pkg && exit", $installretval);
	print "<div id=\"retval\">$installretval</div>";
}

function assemble_image($machine, $name, $imagetype) {
	print "Machine: $machine, name: $name, type: $imagetype\n";
	passthru ("fakeroot scripts/assemble-image.sh $machine $name-image $imagetype && exit", $installretval);
	print "<div id=\"retval-image\">$installretval</div>";
}



?>
