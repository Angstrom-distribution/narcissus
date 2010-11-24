<pre>
<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2010 - GPLv2
 *
 */

print "rm /tmp\n";
passthru ("find /tmp -name 'opkg*' -mtime +2 -exec rm -rv {} \;&& exit");
print "rm deploy\n";
passthru ("find deploy -depth -mindepth 2 -maxdepth 2 -mtime +3 -exec rm -rv {} \;&& exit");
print "rm work\n";
passthru ("find work -depth -mindepth 1 -maxdepth 2 -mtime +1 -exec rm -rfv {} \;&& exit");

?>
</pre>
