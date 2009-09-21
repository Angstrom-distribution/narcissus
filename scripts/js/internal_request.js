/* The following function creates an XMLHttpRequest object... */

function createRequestObject(){
	var request_o; //declare the variable to hold the object.
	var browser = navigator.appName; //find the browser name
	if(browser == "Microsoft Internet Explorer"){
		/* Create the object using MSIE's method */
		request_o = new ActiveXObject("Microsoft.XMLHTTP");
	}else{
		/* Create the object using other browser's method */
		request_o = new XMLHttpRequest();
	}
	return request_o; //return the object
}

/* The variable http will hold our new XMLHttpRequest object. */
var http = createRequestObject(); 
var packagelist = new Array;
var packagestring = "";
var opackage = "";
var progress_text = ""
var FAIL_image = "<img src='img/X_mark.png'>";
var succes_image = "<img src='img/Green_tick'>";
var repourl = "http://www.angstrom-distribution.org/repo/?pkgname=";

//var workerurl = 'http://dominion.thruhere.net/koen/narcissus/backend.php';
//var workerurl = 'http://amethyst.openembedded.net/~koen/narcissus/backend.php';
var workerurl = "backend.php";

function configureImage(){
    showHideElement('intro',0);
    showHideElement('image_progress',0);
	
	document.getElementById('image_link').innerHTML = "";
	document.getElementById('configure_progress').innerHTML = "";
	document.getElementById('image_progress').innerHTML = "";
	
    packagestring = concatArray(document.entry_form.pkg);
	
	if (packagestring == "" || packagestring == " ") {
 		document.getElementById('imgstatus').innerHTML = "You have to select at least one task, try 'task-boot' to get a minimal set.";
   		Highlight('base');
		return; 
	}

    var devmanager = "";
    for (i = 0; i < document.entry_form.devmanager.length; i++) {
        if (document.entry_form.devmanager[i].checked) {
            devmanager = document.entry_form.devmanager[i].value
        }
    }  
  
    packagestring += " " + devmanager + " angstrom-version tinylogin initscripts sysvinit sysvinit-pidof ";
    packagestring += " " + concatArray(document.entry_form.devel);
	packagestring += " " + concatArray(document.entry_form.console_packages);
	
	packagestring += " " + document.entry_form.environment.value;
	if(document.entry_form.environment.selectedIndex == 1) {
		packagestring += " " + concatArray(document.entry_form.wm); 
		packagestring += " " + concatArray(document.entry_form.x11_packages);
	}

	var packagelisttemp = packagestring.split(" ");
	packagelist = unique(packagelisttemp);
	
	progress_text = "<br/><br/><table>\n";
	progress_text += "<tr><td colspan=\"2\">Preconfiguring image</td><td></td><td id='td-configure'></td></tr>\n";
	progress_text += "<tr><td colspan=\"2\">Installing packages:</td><td></td><td id='td-package'></td></tr>\n";
	
	for (var i in packagelist) {
		if (packagelist[i] != "" && packagelist[i] != " ") {
			progress_text += "<tr><td>&nbsp;</td><td><a href='" + repourl + packagelist[i] + "' target='foo'>" + packagelist[i] + "</a></td><td>&nbsp;</td><td><div id=\"td-" +  packagelist[i] + "\"></div></td></tr>\n";
		}	
	}
	
	progress_text += "<tr><td colspan=\"2\">Assembling image</td><td></td><td id='td-assemble'></td></tr>\n";
	progress_text += "</table>\n";
	
	document.getElementById('pkg_progress').innerHTML = progress_text;
	
    var params = 'action=configure_image&machine=' + document.entry_form.machine.value + '&release=' + document.entry_form.configs.value + '&name=' + document.entry_form.name.value;
	http.open('post', workerurl, true);
	
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.onreadystatechange = configureProgress; 
	http.send(params);
	slideUp('form');
}

function assembleImage(){

    var imagetype = "";
    for (i = 0; i < document.entry_form.imagetype.length; i++) {
        if (document.entry_form.imagetype[i].checked) {
            imagetype = document.entry_form.imagetype[i].value
        }
    }

    var params = 'action=assemble_image&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value + '&imagetype=' + imagetype;
	http.open('post', workerurl, true);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.onreadystatechange = assembleProgress; 
	http.send(params);
}

function installPackage(){
	if (packagelist != "" && packagelist != " ") {
		var params = 'action=install_package&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value + '&pkgs=' + packagelist;
		http.open('post', workerurl, true);
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http.onreadystatechange = installProgress; 
		http.send(params);
	}
}

function showImagelink(){
    var imagetype = "";
    for (i = 0; i < document.entry_form.imagetype.length; i++) {
        if (document.entry_form.imagetype[i].checked) {
            imagetype = document.entry_form.imagetype[i].value
        }
    }

    var params = 'action=show_image_link&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value + '&imagetype=' + imagetype;
	http.open('post', workerurl, true);
	
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.onreadystatechange = imageDisplay; 
	http.send(params);
}

function configureProgress(){
    if(http.readyState == 4){
		var response = http.responseText;
        showHideElement('configure_progress', 0);
        document.getElementById('configure_progress').innerHTML = response;
		document.getElementById('td-configure').innerHTML = succes_image;
		installPackage("test");
	}
}

function installProgress(){
	if(http.readyState == 4){
		var response = http.responseText;
		document.getElementById('image_progress').innerHTML = response;
		if(document.getElementById('imgsize')) {
			document.getElementById('imgstatus').innerHTML = "<br/>\nCurrent uncompressed image size: " + document.getElementById('imgsize').innerHTML.split(" ")[1];
		}	
        for(var i=0; i < packagelist.length; i++){
			var progress_id = 'td-' + packagelist[i];	
			var return_code = packagelist[i] + '-returncode';
			// We grep for an error code, so '0' is indeed an error
			if(document.getElementById(return_code)) {
				if(document.getElementById(return_code).innerHTML == "0") {
					document.getElementById(progress_id).innerHTML = FAIL_image;
				}	
				else {
					document.getElementById(progress_id).innerHTML = succes_image;
				}
			}
		}
		assembleImage("test");
	}
}

function assembleProgress(){
	if(http.readyState == 4){ 
		var response = http.responseText;
		showHideElement('image_progress',0);
        document.getElementById('image_progress').innerHTML = response;
		if(document.getElementById('retval-image').innerHTML == "0") {
			document.getElementById('td-assemble').innerHTML = succes_image;
		}
		else {
			document.getElementById('td-assemble').innerHTML = FAIL_image;
		}		
		showImagelink();
	}
}

function imageDisplay(){
	if(http.readyState == 4){
        var response = http.responseText;
		if(document.getElementById('imgsize')) {
			var image_size = "<br/>\nCurrent uncompressed image size: " + document.getElementById('imgsize').innerHTML.split(" ")[1];
			document.getElementById('imgstatus').innerHTML = image_size;
		}
		document.getElementById('image_link').innerHTML = response;
		pulsate(document.getElementById('image_link'));
	}
}

function showHideElement(elementId, showHideFlag) {
	var elementObj = document.getElementById(elementId);
	if(showHideFlag == 1) {
		elementObj.style.display = '';
	} 
	else if(showHideFlag == 0) {
		elementObj.style.display = 'none';
	}
}

function toggleVisibility(elementId) {
	var elementObj = document.getElementById(elementId);
	if (elementObj.style.display == '') {
		elementObj.style.display = 'none';
	}
	else {
		elementObj.style.display = '';
	}
}

function concatArray(varArray) {
    var packageslist = "";
    for(var i=0; i < varArray.length; i++){
		if(varArray[i].checked) {
			packageslist += varArray[i].value + " ";
		}
    }    
    return packageslist   
}

function unique(a)
{
	var r = new Array();
o:for(var i = 0, n = a.length; i < n; i++) {
	for(var x = i + 1 ; x < n; x++)
	{
		if(a[x]==a[i]) continue o;
	}
	r[r.length] = a[i];
}
	return r;
}
