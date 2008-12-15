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

/* Function called to get the product categories list */
function assembleImage(){
	document.getElementById('image_progress').innerHTML = "";
	/* Create the request. The first argument to the open function is the method (POST/GET),
		and the second argument is the url... 
		document contains references to all items on the page
		We can reference document.entry_form.machine.value and we will
		be referencing the dropdown list. The selectedIndex property will give us the 
		index of the selected item. 
	*/
	http.open('get', 'backend.php?action=assemble_image&machine=' + document.entry_form.machine.value + '&name=' + document.entry_form.name.value);
	
	/* Define a function to call once a response has been received. This will be our handleProductCategories function that we define below. */
	http.onreadystatechange = imageProgress; 
	/* Send the data. We use something other than null when we are sending using the POST method. */
	http.send(null);
}

/* Function called to handle the list that was returned from the internal_request.php file.. */
function imageProgress(){
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
		document.getElementById('image_progress').innerHTML = response;
	}
}





