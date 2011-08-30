<?php
require_once("../../../wp-config.php");
header('Content-Type: text/javascript');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
global $flickr_post_table;
?>
var wppd_dir = "<?php echo get_option('siteurl'); ?>/wp-content/plugins/photo-dropper/";
var htmlBefore = "<?php echo addslashes($wpdb->get_var("SELECT value FROM $flickr_post_table WHERE name='htmlBefore'")); ?>";
var htmlAfter = "<?php echo addslashes($wpdb->get_var("SELECT value FROM $flickr_post_table WHERE name='htmlAfter'")); ?>";
var wp_version = "<?php global $wp_version; echo $wp_version; ?>";

function wppd_performFilter(destId) {
	var filter = document.getElementById("flickr-filter").value;
	var page = document.getElementById("flickr-page").value;
	var size = document.getElementById("flickr-size");
	
	if(filter != document.getElementById("flickr-old-filter").value) {
		page = 1;
	}	
	var query = "filter=" + filter + "&fpage=" + page +"&size=thumbnail";
	var jurl = wppd_dir + "flickr-ajax.php";
	wppd_displayLoading(destId);
	
	/*
	 * 1.0.5 - Trent Gardner
	 * Converted all AJAX requests to use jQuery because wp-admin doesn't include
	 * prototype by default anymore.
	 */
	if(typeof(jQuery) != "undefined") {
		// jQuery request
		var flickr_ajax = jQuery.ajax({
			type: "POST",
			url: jurl,
			data: query,
			error: function() {
				wppd_returnError(destId);
			},
			success: function(msg) {
				document.getElementById(destId).innerHTML = msg;
			}
		});
	} else {
		// Prototype Alternative
		var flickr_ajax = new Ajax.Updater({success: destId}, jurl, {method: 'get', parameters: query, onFailure: function(){ wppd_returnError(destId); }});
	}
	
	return false;
}


function getPage(link, destId) {
	var query_array = link.getAttribute("href").split("?");
	var query = query_array[query_array.length - 1];	
	var jurl = wppd_dir + "flickr-ajax.php";
	wppd_displayLoading(destId);
	
	if(typeof(jQuery) != "undefined") {
		// jQuery request
		var flickr_ajax = jQuery.ajax({
			type: "POST",
			url: jurl,
			data: query,
			error: function() {
				wppd_returnError(destId);
			},
			success: function(msg) {
				document.getElementById(destId).innerHTML = msg;
			}
		});
	} else {
		// Prototype Alternative
		var flickr_ajax = new Ajax.Updater({success: destId}, jurl, {method: 'get', parameters: query, onFailure: function(){ wppd_returnError(destId); }});
	}
		
	return false;
}

function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			oldonload();
			func();
		};
	}
}

function wppd_insertImage(imagesrc,imagealt,owner,id,name,person,license_url) {
	var imgHTML = "";
	var target = "";
	var image_link_url = 'http://www.flickr.com/photos/' + owner + "/" + id + '/';
	var licence_person_url = "http://www.flickr.com/people/"+person+"/";
	var image = document.getElementById('image-' + id);
	license_url = license_url.split('||');
	
	var licenceHTML = '<a href="'+license_url[1]+'" title="'+license_url[0]+'" target="_blank"><img src="'+wppd_dir+'images/cc.png" alt="Creative Commons License" border="0" width="16" height="16" align="absmiddle" /></a> <a href="http://www.photodropper.com/photos/" target="_blank">photo</a> credit: <a href="'+image_link_url+'" title="'+person+'" target="_blank">'+person+'</a>';
	
	licenceHTML = '<br /><small>' + licenceHTML + '</small>';
	
	imgHTML = '<a href="http://www.flickr.com/photos/' + owner + "/" + id + '/" title="' + imagealt + '"' + ' target="_blank">';
	imgHTML = imgHTML + '<img src="' + imagesrc + '" alt="' + imagealt + '" border="0" /></a>';
	imgHTML = htmlBefore + imgHTML + licenceHTML + htmlAfter;

	top.send_to_editor(imgHTML);
	top.tb_remove();
	return false;
}

function wppd_returnError(destId) {
	var element = document.getElementById(destId);
	if(!element) {
		return;
	}
	element.innerHTML = "Unexpected error occured while performing an AJAX request";
}

function wppd_displayLoading(destId) {
	var element = document.getElementById(destId);
	if(!element) {
		return;
	}
	var image = document.createElement("img");
	image.setAttribute("alt", "loading...");
	image.setAttribute("src", wppd_dir + "images/loading.gif");
	image.className = "loading";
	element.innerHTML = "";
	element.appendChild(image);
}

function wppd_insertAtCursor(myField, myValue) {
	// IE support
	if (document.selection) {
		myField.focus();
		var sel = document.selection.createRange();
		sel.text = myValue;
	}
	// MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart === 0) {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
	} else {
		myField.value += myValue;
	}
}

function keyHit(e) {
	if (!e) var e = window.event;
	if (e.keyCode) code = e.keyCode;
	else if (e.which) code = e.which;
	
	if (code == 13) {
		return wppd_performFilter('wppd-ajax');
	}
	
}
