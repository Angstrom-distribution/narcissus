<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2010 - GPLv2
 *
 */

passthru ("find /tmp -name 'opkg*' -mtime +2 -exec rm -r {} \;&& exit");
passthru ("find deploy -depth -mindepth 2 -mtime +3 -exec rm -r {} \;&& exit");
passthru ("find work -depth -mindepth 1 -maxdepth 2 -mtime +2 -exec rm -rf {} \;&& exit");

?>
