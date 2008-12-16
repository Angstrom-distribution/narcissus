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


function configureImage(){
	/* Create the request. The first argument to the open function is the method (POST/GET),
		and the second argument is the url... 
		document contains references to all items on the page
		We can reference document.entry_form.machine.value and we will
		be referencing the dropdown list. The selectedIndex property will give us the 
		index of the selected item. 
	*/
	document.getElementById('image_link').innerHTML = "";
	document.getElementById('configure_progress').innerHTML = "";
	document.getElementById('image_progress').innerHTML = "";
	document.getElementById('status').innerHTML = "Busy configuring image, please wait...";

	var packagelist = "task-boot ";
    packagelist += concatArray(document.entry_form.pkg);
    packagelist += concatArray(document.entry_form.wm);
    packagelist += concatArray(document.entry_form.devel);
	
	var params = 'action=configure_image&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value + '&pkgs=' + packagelist;
	http.open('post', 'backend.php');

	//Send the proper header information along with the request
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", params.length);
	http.setRequestHeader("Connection", "close");
	
	/* Define a function to call once a response has been received. This will be our handleProductCategories function that we define below. */
	http.onreadystatechange = configureProgress; 
	/* Send the data. We use something other than params when we are sending using the POST method. */
	http.send(params);
}

function assembleImage(){
	/* Create the request. The first argument to the open function is the method (POST/GET),
		and the second argument is the url... 
		document contains references to all items on the page
		We can reference document.entry_form.machine.value and we will
		be referencing the dropdown list. The selectedIndex property will give us the 
		index of the selected item. 
	*/
	document.getElementById('status').innerHTML = "Busy assembling image, please wait...";
    var params = 'action=assemble_image&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value;
	http.open('post', 'backend.php');

	//Send the proper header information along with the request
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", params.length);
	http.setRequestHeader("Connection", "close");
	
	/* Define a function to call once a response has been received. This will be our handleProductCategories function that we define below. */
	http.onreadystatechange = assembleProgress; 
	/* Send the data. We use something other than params when we are sending using the POST method. */
	http.send(params);
}

function showImagelink(){
	/* Create the request. The first argument to the open function is the method (POST/GET),
		and the second argument is the url... 
		document contains references to all items on the page
		We can reference document.entry_form.machine.value and we will
		be referencing the dropdown list. The selectedIndex property will give us the 
		index of the selected item. 
	*/
    var params = 'action=show_image_link&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value;
	http.open('post', 'backend.php');
	
	//Send the proper header information along with the request
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", params.length);
	http.setRequestHeader("Connection", "close");

	/* Define a function to call once a response has been received. This will be our handleProductCategories function that we define below. */
	http.onreadystatechange = imageDisplay; 
	/* Send the data. We use something other than params when we are sending using the POST method. */
	http.send(params);
}

function configureProgress(){
	/* Make sure that the transaction has finished. The XMLHttpRequest object 
		has a property called readyState with several states:
		0: Uninitialized
		1: Loading
		2: Loaded
		3: Interactive
		4: Finished */

	if(http.readyState == 4){ //Finished loading the response
		/* We have got the response from the server-side script,
			let's see just what it was. using the responseText property of 
			the XMLHttpRequest object. */
		var response = http.responseText;
		/* And now we want to change the image_progress <div> content.
			we do this using an ability to get/change the content of a page element 
			that we can find: innerHTML. */
		showHideElement('configure_progress',0);
		document.getElementById('configure_progress').innerHTML = response;
		assembleImage();
	}
}

function assembleProgress(){
	/* Make sure that the transaction has finished. The XMLHttpRequest object 
		has a property called readyState with several states:
		0: Uninitialized
		1: Loading
		2: Loaded
		3: Interactive
		4: Finished */

	if(http.readyState == 4){ //Finished loading the response
		/* We have got the response from the server-side script,
			let's see just what it was. using the responseText property of 
			the XMLHttpRequest object. */
		var response = http.responseText;
		/* And now we want to change the image_progress <div> content.
			we do this using an ability to get/change the content of a page element 
			that we can find: innerHTML. */
		showHideElement('image_progress',0);
		document.getElementById('image_progress').innerHTML = response;
		showImagelink();
	}
}

function imageDisplay(){
	/* Make sure that the transaction has finished. The XMLHttpRequest object 
		has a property called readyState with several states:
		0: Uninitialized
		1: Loading
		2: Loaded
		3: Interactive
		4: Finished */
		
		if(http.readyState == 4){ //Finished loading the response
		/* We have got the response from the server-side script,
			let's see just what it was. using the responseText property of 
			the XMLHttpRequest object. */
		var response = http.responseText;
		/* And now we want to change the image_progress <div> content.
			we do this using an ability to get/change the content of a page element 
			that we can find: innerHTML. */
		document.getElementById('status').innerHTML = "Image assembly complete";
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
    var packagelist = " ";
    for(var i=0; i < varArray.length; i++){
		if(varArray[i].checked) {
			packagelist += varArray[i].value + " ";
		}
    }    
    return packagelist   
}