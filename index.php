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
	print "<pre>";
	print "<b>bin/opkg-cl -o foo -f foo/etc/opkg.conf update</b><p>";
	system("bin/opkg-cl -o foo -f foo/etc/opkg.conf update");
	print "\n<b>bin/opkg-cl -o foo -f foo/etc/opkg.conf install $pkgs</b><p>";
	system("bin/opkg-cl -o foo -f foo/etc/opkg.conf install $pkgs");
	print "\n<b>tar cjf ../deploy/foo.tar.bz2</b><p>";
	system ("cd foo ;tar cjf ../deploy/foo.tar.bz2 .; cd ..");
	print "</pre>";
	print "<a href='deploy/foo.tar.bz2'>your image</b>";
}

assemble_image($base_pkg_set);

?>
