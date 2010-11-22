<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2010 - GPLv2
 *
 */

passthru ("find /tmp -name 'opkg*' -mtime +2 -exec rm -r {} \;&& exit");
passthru ("find deploy -depth -mindepth 1 -mtime +3 -delete && exit");

?>
