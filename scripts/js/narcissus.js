/* Narcissus Online Image generator

(c) Koen Kooi 2008 - 2010

This is licensed under the terms of the GPLv2

*/

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

function showSummary(){
    document.getElementById('summary').innerHTML = '<b>Summary:</b><br/><br/>Machine: ' + document.entry_form.machine.value + '<br/>Release: ' + document.entry_form.configs.value + '<br/>Name: ' + document.entry_form.name.value;
}

function configureImage(){
    showHideElement('image_progress',0);
	
	document.getElementById('image_link').innerHTML = "";
	document.getElementById('configure_progress').innerHTML = "";
	document.getElementById('image_progress').innerHTML = "";
	
    packagestring = concatArray(document.entry_form.pkg);
	
	if (packagestring == "" || packagestring == " ") {
 		document.getElementById('imgstatus').innerHTML = "You have to select the base system, try 'task-boot' to get a minimal set.";
		return; 
	}
	
	document.getElementById('beverage').innerHTML = "Depending on the load of this machine and the feed server the process might take a few <b>minutes</b>, so get a beverage of your choice and <b>DON'T</b> hit refresh."
	
	slideUp('packageblock');
    slideUp('settings');
    
	
    var devmanager = "";
    for (i = 0; i < document.entry_form.devmanager.length; i++) {
        if (document.entry_form.devmanager[i].checked) {
            devmanager = document.entry_form.devmanager[i].value
        }
    }  
	
    packagestring += " " + devmanager + " angstrom-version tinylogin initscripts sysvinit sysvinit-pidof ";
    packagestring += " " + concatArray(document.entry_form.devel);
    packagestring += " " + concatArray(document.entry_form.console_packages);
    packagestring += " " + concatArray(document.entry_form.platform_packages);
    packagestring += " " + concatArray(document.entry_form.network_packages);
	
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
	
    showSummary();
    
	document.getElementById('pkg_progress').innerHTML = progress_text;
    var params = 'action=configure_image&machine=' + document.entry_form.machine.value + '&release=' + document.entry_form.configs.value + '&name=' + document.entry_form.name.value;

    $.ajax({
       type: "POST",
       url: workerurl,
       data: params,
       success: function(msg){
        showHideElement('configure_progress', 0);
        document.getElementById('configure_progress').innerHTML = msg;
		document.getElementById('td-configure').innerHTML = succes_image;
		installPackage("test");
       }
     });
}

function assembleImage(){
	
    var imagetype = "";
    for (i = 0; i < document.entry_form.imagetype.length; i++) {
        if (document.entry_form.imagetype[i].checked) {
            imagetype = document.entry_form.imagetype[i].value
        }
    }
	
    var params = 'action=assemble_image&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value + '&imagetype=' + imagetype;
    $.ajax({
           type: "POST",
           url: workerurl,
           data: params,
           success: function(msg){
                showHideElement('image_progress',0);
                document.getElementById('image_progress').innerHTML = msg;
                if(document.getElementById('retval-image').innerHTML == "0") {
                    document.getElementById('td-assemble').innerHTML = succes_image;
                }
                else {
                    document.getElementById('td-assemble').innerHTML = FAIL_image;
                }		
                showImagelink();
            }
        });

}

function installPackage(){
	if (packagelist != "" && packagelist != " ") {
		var params = 'action=install_package&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value + '&pkgs=' + packagelist;
        $.ajax({
           type: "POST",
           url: workerurl,
           data: params,
           success: function(msg){
                document.getElementById('image_progress').innerHTML = msg;
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
         });
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
    $.ajax({
       type: "POST",
       url: workerurl,
       data: params,
       success: function(msg){
       		if(document.getElementById('imgsize')) {
                var image_size = "<br/>\nCurrent uncompressed image size: " + document.getElementById('imgsize').innerHTML.split(" ")[1];
                document.getElementById('imgstatus').innerHTML = image_size;
            }
            document.getElementById('image_link').innerHTML = msg;
            pulsate(document.getElementById('image_link'))
        }
     });
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

function launchWindow(id)
{
	//select all the a tag with name equal to modal
	//	$('a[name=modal]').click(function(e) {
	//Cancel the link behavior
	//		e.preventDefault();
	//Get the A tag
	//		var id = $(this).attr('href');
	
	//Get the screen height and width
	var maskHeight = $(document).height() * 0.99;
	var maskWidth = $(window).width() * 0.99;
	
	//Set height and width to mask to fill up the whole screen
	$('#mask').css({'width':maskWidth,'height':maskHeight});
	$('#mask').css('top',  0);
	
	//transition effect		
	$('#mask').fadeIn(1000);	
	$('#mask').fadeTo("slow",0.8);	
	
	//Get the window height and width
	var winH = $(window).height();
	var winW = $(window).width();
	
	$(document.getElementById('dialog')).css('width' , winW/2); 
	$(document.getElementById('dialog')).css('height' , winH*0.7);
	$(id).height = winH*0.75;
	if($(id).height > $(document.getElementById('dialog')).height) {
		$(document.getElementById('dialog')).css('height' , $(id).height);
	}
	
	//Set the popup window to center
	$(id).css('top',  winH/2-$(id).height()/2);
	$(id).css('left', winW/2-$(id).width()/2);
	
	//transition effect
	$(id).fadeIn(2000); 
	
	//	});
	
	//if close button is clicked
	$('.window .close').click(function (e) {
							  //Cancel the link behavior
							  e.preventDefault();
							  $('#mask, .window').hide();
							  });		
	
	//if mask is clicked
	$('#mask').click(function () {
					 $(this).hide();
					 $('.window').hide();
					 });
}


