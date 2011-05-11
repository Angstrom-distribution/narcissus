<html>
<head>
<title>Narcissus - Online image builder for the Angstrom distribution</title>

<meta http-equiv="refresh" content="600">

<script language="javascript" type="text/javascript" src="scripts/js/jquery-1.4.4.min.js"></script>
<script language="javascript" type="text/javascript" src="scripts/js/jquery.isotope.min.js"></script>
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
$maxbuilds = 0;

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

ksort($builds, SORT_STRING);

$timeframe = ( date("Y", $lastdate) - date("Y", $firstdate) ) * 365 +  date("z", $lastdate) - date("z",$firstdate);

if (isset($_GET["machine"])) {
    $selectedmachine = $_GET["machine"];
}


if (isset($_GET["timeframe"])) {
	$maxdays = $_GET["timeframe"] + 1;
	if ($timeframe > $maxdays) $timeframe = $maxdays -1;
}

if ($timeframe < 80) {
	$hfactor = 3.1;
} else {
	$hfactor = 1;
}	

$modulovar = ceil($timeframe / 4) ;

for ($i = 0 ; $i <= $timeframe ; $i++) {
    $statsdate = date("Ymd",$lastdate - ( ($timeframe - $i) * 86400 ) ) ;
	if ( $i % $modulovar == 1 ) { 
		$xtick = date("d F Y", $lastdate - ( ($timeframe - $i) * 86400 )) ;
	} else {
		$xtick = "";
	}
	$xticks .= "{v:$i, label:\"$xtick\"},\n";
	foreach($builds as $machine => $foo) {
		if (isset($builds[$machine][$statsdate])) {
			$buildcount = $builds[$machine][$statsdate];
			$yvars[$machine] .= "[ $i, $buildcount ], \n";
			if($maxbuilds < $builds[$machine][$statsdate]) $maxbuilds = $builds[$machine][$statsdate];
			$didbuild[$machine] = 1;
		} else {
			$yvars[$machine] .= "[ $i, 0 ], \n";
		}
	}
}	

?>
<script language="javascript">
var options = {
	"colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
	"padding": {left: 0, right: 0, top: 10, bottom: 30},
	"xTicks": [<? print $xticks; ?> ],
	"drawYAxis": false,
	"yAxis": [0, <?print $maxbuilds; ?>],
	"yTickPrecision": 0
};

function drawGraph() {
    if (parseInt(navigator.appVersion)>3) {
        if (navigator.appName=='Netscape') {
            winW = window.innerWidth - 30;
            winH = (window.innerHeight - 30) * 0.9;
        }
        if (navigator.appName.indexOf('Microsoft')!=-1) {
            winW = document.body.offsetWidth - 30;
            winH = (document.body.offsetHeight - 30) * 0.9;
        }
    }
	
	<?

	if(!isset($selectedmachine)) {
	foreach($builds as $machine => $foo) {
		$machineyvars = $yvars[$machine];
		if($didbuild[$machine] == 1) {
			print("
			  var layout = new PlotKit.Layout(\"bar\", options);
			  layout.addDataset(\"$machine usage count\", [$machineyvars]);
			  layout.evaluate();
			  var canvas = MochiKit.DOM.getElement(\"graph-$machine\");
			  
			  canvas.setAttribute('width', winW/$hfactor);
			  canvas.setAttribute('height', winH/5);
			  var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, {});
			  plotter.render();
			  ");
		}
	}
	} else {
        $machine = $selectedmachine;
		$machineyvars = $yvars[$machine];
        print("
              var layout = new PlotKit.Layout(\"bar\", options);
              layout.addDataset(\"$machine usage count\", [$machineyvars]);
              layout.evaluate();
              var canvas = MochiKit.DOM.getElement(\"graph-$machine\");
              
              canvas.setAttribute('width', winW/$hfactor);
              canvas.setAttribute('height', winH/5);
              var plotter = new PlotKit.SweetCanvasRenderer(canvas, layout, {});
              plotter.render();
              ");

	}
	?>
}
MochiKit.DOM.addLoadEvent(drawGraph);

</script>
</head>
<body>
Statistics for the online image builder, number of builds per day<br>
<br>
<div id="container">
<?

    if(!isset($selectedmachine)) {
		foreach ($builds as $machine => $foo) {
			if($didbuild[$machine] == 1) print("<div class='item'><table align=left><td><br>$machine<br><div id='div-$machine'><canvas id='graph-$machine'></canvas></div></td></table></div>\n");
		}
	} else {
		print("<div class='item'><table align=left><td><br>$selectedmachine<br><div id='div-$machine'><canvas id='graph-$selectedmachine'></canvas></div></td></table></div>\n");
	}
?>
</div>
<br clear=all><br>Total builds for all machines: <? print $total; ?>
</body>
 <script>
$('#container').isotope({
  // options
  itemSelector : '.item',
  //layoutMode : 'fitRows'
});
</script>
</html>
