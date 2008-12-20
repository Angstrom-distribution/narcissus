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
var package = "";


function configureImage(){
    showHideElement('form',0);
    showHideElement('intro',0);


	document.getElementById('image_link').innerHTML = "";
	document.getElementById('configure_progress').innerHTML = "";
	document.getElementById('image_progress').innerHTML = "";

    packagestring = concatArray(document.entry_form.pkg);

	if (packagestring == "" || packagestring == " ") {
 		document.getElementById('status').innerHTML = "You have to select at least one task, try 'task-boot' to get a minimal set.";
   		return; 
	}

    packagestring += concatArray(document.entry_form.wm); 
    packagestring += concatArray(document.entry_form.devel);
    packagestring += concatArray(document.entry_form.packages);

	packagelist = packagestring.split(" ");

	document.getElementById('status').innerHTML = "<br/>\n<br/>\nConfiguring image";
    
    var params = 'action=configure_image&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value;
	http.open('post', 'backend.php');

	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.onreadystatechange = configureProgress; 
	http.send(params);
}

function assembleImage(){
    var params = 'action=assemble_image&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value;
	http.open('post', 'backend.php');
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.onreadystatechange = assembleProgress; 
	http.send(params);
}

function installPackage(){
            if (package != "" && package != " ") {
                var params = 'action=install_package&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value + '&pkgs=' + package;
                http.open('post', 'backend.php');
                http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                http.onreadystatechange = installProgress; 
                http.send(params);
            }
}

function showImagelink(){
    var params = 'action=show_image_link&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value;
	http.open('post', 'backend.php');
	
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.onreadystatechange = imageDisplay; 
	http.send(params);
}

function configureProgress(){
    if(http.readyState == 4){
		var response = http.responseText;
		document.getElementById('status').innerHTML += ": complete<br/>\n<br/>\n";
        showHideElement('configure_progress',0);
        document.getElementById('configure_progress').innerHTML = response;
        package = packagelist.shift();
        document.getElementById('status').innerHTML += "Installed packages: ";
		installPackage("test");
	}
}

function installProgress(){
    if(http.readyState == 4){
        document.getElementById('status').innerHTML += " " + package ;
        if (packagelist.length > 1) {
            package = packagelist.shift();
            if (package != "" && package != " ") {
                installPackage(package);
            }
        }     
        else {
            assembleImage(package);
        }        
	}
}

function assembleProgress(){
    if(http.readyState == 1){
    	document.getElementById('status').innerHTML += "<br>\nAssembling image";
    }	
    if(http.readyState == 4){ 
		var response = http.responseText;
		showHideElement('image_progress',0);
        document.getElementById('image_progress').innerHTML = response;
		showImagelink();
	}
}

function imageDisplay(){
		if(http.readyState == 4){
        var response = http.responseText;
		document.getElementById('image_link').innerHTML = response;
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
