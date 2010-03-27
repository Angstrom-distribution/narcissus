<?
/* Narcissus - online image builder for the angstrom distribution
 * Koen Kooi (c) 2008 - 2010 - all rights reserved 
 */

function machine_dropdown()
{
	$machine = array();
	
	if ($handle = opendir ('./conf/'))
    {
		/* This is the correct way to loop over the directory. */
		while (false !== ($file = readdir ($handle)))
		{
			if ($file != "." && $file != ".."
				&& file_exists ("./conf/$file/arch.conf"))
			{
				$machine[] = $file;
			}
		}
		closedir ($handle);
    }
	
	sort ($machine);
	foreach ($machine as $value)
	{
		print ("\t\t\t\t<option value=\"$value\">$value</option>\n");
	}
}

function config_dropdown()
{
	$configs = array();
	foreach ($machine as $machine_value)
	{
		if ($handle = opendir ('./conf/$machine_value'))
		{
			while (false !== ($file = readdir ($handle)))
			{
				if ($file != "." && $file != ".."
					&& file_exists ("./conf/$machine_value/configs/$file/"))
				{
					$configs[$machine_value][] = $file;
				}
			}
			closedir ($handle);
		}
		
		sort ($configs);
		foreach ($configs as $value)
		{
			print ("\t<option value=\"$value\">$value</option>\n");
		}
	}
}

$repourl = "http://www.angstrom-distribution.org/repo/?pkgname";

?>
<div id='machinelist'>
			Select the machine you want to build your rootfs image for:<br/><br/>
			<select name="machine">
<? machine_dropdown(); ?>
			</select > 
		<br/>
</div>
