<?php require('gzip-header-css.php'); ?>

/* This file contains the CSS for the 'K2 Options' admin panel */

.wrap {
	font-family: Helvetica, Verdana, 'Lucida Grande', Arial, sans-serif;
	border: none;
	margin: 0px auto;
	}

.wrap a {
	color: #0A5199;
	border: none;
	white-space: pre;
	}

.configstuff {
	position: relative;
	width: 600px;
	margin: 0px auto;
	padding-top: 40px; /* For floating .savebutton */
	}

.savebutton {
	position: absolute;
	top: 0px;
	width: 600px;
	margin: 0px auto;
	padding: 10px 0px;
	}

#save {
	float: right;
	background: #222;
	color: white;
	border: none;
	padding: 5px 30px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	}

body.smartposition .savebutton {
	border-bottom: 1px solid #ddd;
	position: fixed;
	top: 0px;
	background: #f9fcfe;
	z-index: 100;
	}

.container {
	margin: 30px 0px 20px;
	position: relative;
	}

h3 {
	font: 1.6em/1.4 Helvetica, Arial, sans-serif;
	color: #333;
	width: 225px;
	margin: 0px;
	overflow: hidden;
	white-space: pre;
	}

.configstuff .description {
	margin-top: 5px;
	color: #999;
	}

.error {
	color: #666;
	}

.form-list dt {
	font-size: 1.1em;
	color: #666;
	width: 225px;
	margin-bottom: 0px 0px 6px;
	overflow: hidden;
	text-align: right;
	float: left;
	padding: 4px 0;
	}

.form-list dd {
	margin-left: 240px;
}

.form-list dd.description {
	margin-left: 0px;
	}

.main-option {
	position: absolute;
	left: 240px;
	top: 0px;
	margin: 0px;
	}

.main-option input[type=checkbox] {
	margin-top: 5px;
	}

.secondary-option {
	margin-left: 240px;
	}

.configstuff input[type=text],
.configstuff select {
	color: #333;
	width: 240px;
	border: 1px solid #ccc;
	}

.configstuff option {
	padding: 0px;
	}

.uninstall {
	border: none;
	margin-top: 2px;
	padding: 30px 20px 50px;
	background: #eee url('../images/sbmmanager/sbmbg.png') top repeat-x;
	}

.uninstall input {
	padding: 3px 5px;
	}

.uninstall .configstuff {
	padding-top: 0px;
	}

#wpcontent {
	padding-bottom: 0px;
	}

#footer {
	display: none;
	}

.center {
	text-align: center;
	}