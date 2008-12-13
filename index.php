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

function pkg_selection() {
	print "foo!";
}

function assemble_image($pkgs) {
	system ("scripts/assemble-image.sh beagleboard test-image");
}


print "Online image builder for angstrom\n<br> configured for beagleboard and test-image\n<br>";
print "<pre>";
assemble_image($base_pkg_set);
print "<pre>";

print "<p><a href='deploy/beagleboard/beagleboard-test-image.tar.bz2'>your image!</a>";

?>
