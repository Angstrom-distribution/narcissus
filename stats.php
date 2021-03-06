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

$buildcount = array();

$handle = fopen ("./deploy/stats.txt", "a+");
while ($stats = fscanf($handle, "%s %s\n")) {
    list ($timestamp, $machine) = $stats;
    if(isset($startdate)) {
        $enddate = $timestamp;
    } else {
        $startdate = $timestamp;
    }
    if (isset($buildcount[$machine])) {
        $buildcount[$machine] = $buildcount[$machine] +1 ;
    } else {
        $buildcount[$machine] = 1;
    }
    $total++;
}
fclose ($handle);

$intervaldays = round(($enddate - $startdate) / (60 * 60 * 24),1);

arsort($buildcount);

$i = 0;
$j = 0;

?>
<script language="javascript">
var options = {
	"colorScheme": PlotKit.Base.palette(PlotKit.Base.baseColors()[0]),
	"padding": {left: 0, right: 0, top: 10, bottom: 30},
	"xTicks": [<? foreach($buildcount as $key => $value) { print ("{v:$j, label:\"$key\"},\n"); $j = $j +1;} print "],"; ?> 
	"drawYAxis": false
};

function drawGraph() {
    var layout = new PlotKit.Layout("pie", options);
    layout.addDataset("Usage count", [<? foreach($buildcount as $value) { print ("[$i, $value],"); $i = $i +1;} ?>]);
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
Statistics for the online image builder

<div><canvas id="graph" height="80%" width="100%"></canvas></div>

<? 
$total = 0;
foreach($buildcount as $value) { 
	$total = $total + $value;
}
$buildrate = round($total/$intervaldays);
print("Total: $total builds in $intervaldays days<br/>");
print("Average: $buildrate builds per day<br/>");

?>
</body>
</html>
