<html>
<head>
<title>Narcissus - Online image builder for the Angstrom distribution</title>

<meta http-equiv="refresh" content="600">

<script language="javascript" type="text/javascript" src="scripts/js/MochiKit.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/plotkit/Base.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/plotkit/Layout.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/plotkit/Canvas.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/plotkit/SweetCanvas.js"></script>

<link rel="stylesheet" type="text/css" title="dominion" href="css/dominion.css" media="screen" />
<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2008,2009 - all rights reserved 
 */

$builds = array("beagleboard" => array("20090621" => 0));
$total = 0;

$firstdate = time() + 500;
$lastdate = 0;

$handle = fopen ("./deploy/stats.txt", "a+");
while ($stats = fscanf($handle, "%s %s\n")) {
    list ($timestamp, $machine) = $stats;
	$builddate = date("Ymd", $timestamp);
	if (isset($builds[$machine][$builddate])) {
		$builds[$machine][$builddate] = $builds[$machine][$builddate] +1;
	} else {
		$builds[$machine][$builddate]  = 1;
	}
	$total++;
	if($lastdate < $timestamp) $lastdate = $timestamp;
	if($firstdate > $timestamp) $firstdate = $timestamp;
}
fclose ($handle);
$timeframe = ( date("Y", $lastdate) - date("Y", $firstdate) ) * 365 +  date("z", $lastdate) - date("z",$firstdate);

$machine = "beagleboard";
for ($i = 0 ; $i <= $timeframe ; $i++) {
    $statsdate = date("Ymd",$firstdate + ( $i * 86400 )) ;
	if ( $i % 30 == 1 ) { 
		$xtick = date("d F Y",$firstdate + ( $i * 86400 )) ;
	} else {
		$xtick = "";
	}
	$xticks .= "{v:$i, label:\"$xtick\"},\n";
	if (isset($builds[$machine][$statsdate])) {
        $buildcount = $builds[$machine][$statsdate];
		$yvars .= "[ $i, $buildcount ], \n";
    } else {
        $yvars .= "[ $i, 0 ], \n";
    }
}	

?>
<script language="javascript">
var options = {
   "colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
   "padding": {left: 0, right: 0, top: 10, bottom: 30},
   "xTicks": [<? print $xticks; ?> ],
   "drawYAxis": false
};

function drawGraph() {
    var layout = new PlotKit.Layout("bar", options);
    layout.addDataset("Usage count", [<? print $yvars; ?>]);
    layout.evaluate();
    var canvas = MochiKit.DOM.getElement("graph");
    
	if (parseInt(navigator.appVersion)>3) {
		if (navigator.appName=="Netscape") {
			winW = window.innerWidth - 30;
  			winH = (window.innerHeight - 30) * 0.9;
		}
		if (navigator.appName.indexOf("Microsoft")!=-1) {
			winW = document.body.offsetWidth - 30;
			winH = (document.body.offsetHeight - 30) * 0.9;
		}
	}
	canvas.setAttribute('width', winW);
	canvas.setAttribute('height', winH);
	var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, {});
    plotter.render();
}
MochiKit.DOM.addLoadEvent(drawGraph);
</script>
</head>
<body>
Statistics for the online image builder, number of builds per day for <? print $machine; ?><br>:

<div><canvas id="graph" height="80%" width="100%"></canvas></div>
<br><br>Total builds for all machines: <? print $total; ?>
</body>
</html>
