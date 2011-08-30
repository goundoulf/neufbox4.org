<?php require('gzip-header-css.php'); ?>

/* CSS for the Sidebar Manager page */

#parentwrapper {
	position: relative;
	width: 100%;
	}

#parentwrapper a {
	border: none;
	}

h2 {
	position: absolute;
	top: 13px;
	left: 10px;
	font: 20px Helvetica, Arial, Sans-Serif;
	color: #777;
	background: none;
	}

.wrap {
	position: relative;
	padding: 1px 0 0 10px;
	background: #eee url('../images/sbmmanager/sbmbg.png') repeat-x;
	min-width: 700px;
	min-height: 520px;
	max-width: 100%;
	height: 430px;
	margin: 0;
	border: none;
	border-bottom: 1px solid #ddd;
	z-index: 10;
	overflow: hidden;
}

#overlay {
	position: fixed;
	top: 0px;
	left: 0px;
	z-index: 600;
	width: 100%;
	height: 100%;
	background: black;
	display:none;
	}

.sbmheader {
	height: 55px;
	}

.droppable ul, #availablemodulescontainer ul {
	margin: 0 8px 0 9px;
	}

.wrap ul {
	padding: 0 0 10000px; /* Make sure columns stretch to the bottom */
	}

h3 {
	margin: 10px 0 20px;
	font-size: 1.4em;
	line-height: 40px;
	font-weight: normal;
	text-align:center;
	color: #333;
	}

.backuprestore {
	position: absolute;
	top: 20px;
	left: 230px;
	text-transform: lowercase;
	font-size: 10px;
	line-height: 19px;
	width: 139px;
	}

.backuprestore a {
	color: #eee;
	}

#backupsbm, #restoresbm {
	position: absolute;
	width: 67px;
	text-align: center;
	height: 19px;
	}

#backupsbm {
	right: 0;
	width: 66px;
	padding-right: 3px;
	background: url('../images/sbmmanager/backuprestore.png') right top no-repeat;
	}

#backupsbm:hover {
	background-position: right bottom;
	}

body.nomodules #backupsbm, body.nomodules #backupsbm:hover {
	background-position: right top;
	cursor: default;
	opacity: 0;
	}

#restoresbm {
	padding-left: 3px;
	background: url('../images/sbmmanager/backuprestore.png') left top no-repeat;
	}

#restoresbm:hover {
	background-position: left bottom;
	}

body.nomodules #restoresbm {
	background: url('../images/sbmmanager/undo.png') left top no-repeat;
	padding: 0;
	width: 70px;
	}

#backupsbmwindow {
	position: absolute;
	top: 20px;
	left: 95px;
	background: url('../images/sbmmanager/restorepopupbg.png') left top no-repeat;
	padding: 5px;
	display: block;
	width: 340px;
	height: 50px;
	/*opacity: 0;
	filter: alpha(opacity = 0);*/
	}

#backupsbmwindow input[type=file] {
	position: absolute;
	left: 15px;
	top: 20px;
	color: #ddd;
	border: none;
	background: none;
	font-size: 11px;
	}

#backupsbmwindow button {
	font-size: 11px;
	position: absolute;
	right: 17px;
	top:21px;
	}

#undo {
	position: absolute;
	top: 20px;
	left: 385px;
	width: 70px;
	height: 19px;
	font-size: 10px;
	line-height: 19px;
	text-transform: lowercase;
	color: white;
	text-align: center;
	background: url('../images/sbmmanager/undo.png') left top no-repeat;
	display: none;
	outline: none;
	}

#undo:hover {
	background-position: left bottom;
	}

#undo span {
	position: absolute;
	top: 2px;
	right: 1px;
	width: 16px;
	line-height: 16px;
	font-size: 9px;
	background: url('../images/sbmmanager/buttoncircle.png') no-repeat;
	}

body.nomodules #undo {
	left: 310px;
	}

#do {
	outline: none;
	height: 19px;
	position: absolute;
	top: 18px;
	font-size: 10px;
	line-height: 19px;
	left: 350px;
	width: 120px;
	text-transform: lowercase;
	color: white;
	text-align: center;
	background: url('../images/sbmmanager/undo.png') left top no-repeat;
	}

#columnsform {
	right: 20px;
	top: 15px;
	position: absolute;
	border: default;
	background: default;
	}

#columnsform select {
	background-color: none;
	padding: 1px;
	width: 170px;
	}

	
/* Inside the column area */
	
.initloading {
	position: absolute;
	top: 220px;
	right: 50%;
	width: 300px;
	margin-right: -150px;
	text-align: center;
	text-transform: lowercase;
	font-size: 5em;
	color: #ccc;
	letter-spacing: 1px;
	}

.containerwrap {
	width: 100%;
	clear: both;
	}

.container {
	/*opacity: 0;
	filter: alpha(opacity = 0);*/
	float: left;
	border-right: 1px solid #d8d8d8;
	margin-right: -1px;
	}
	
#disabledcontainer {
	border-right: none;
	margin-right: 0px;
	}

.darkenright {
	position: absolute;
	height: 100%;
	background: #999;
	/*opacity: 0;
	filter: alpha(opacity = 0);*/
	left: 1090px;
	width: 100%;
	border-left: 1px solid black;
	}

#trashcontainer {
	position: absolute;
	top: 1px;
	background: url('../images/sbmmanager/trashbg.png') right repeat-y;
	z-index: -10;
	padding: 0 12px 0 10px;
	margin-left: -10px;
	display: none;
}

.spinner, .spinner:hover {
	background: white url('../images/spinner.gif') no-repeat center center;
	}


/* Modules Styling */

.module {
	position: static; /* ? */
	list-style-type: none;
	color: #eee;
	cursor: move;
	height: 35px; 
	background: url('../images/sbmmanager/modulebg-left.png') left top no-repeat;
	margin: 0 0 3px;
	}

.slidingdoor {
	position: relative;
	height: 35px; 
	background: url('../images/sbmmanager/modulebg.png') right top no-repeat;
	margin-left: 5px;
	}

#disabled .module {
	/*opacity: .75;
	filter: alpha(opacity = 75);*/
	}

.module span {
	position: absolute;
	overflow: hidden;
	}

.modulewrapper {
/*	display: block; ? */
	height: 35px; 
	width: 80%;
	}

#availablemodules .slidingdoor,
#availablemodules .modulewrapper {
	height: 25px;
	}
	
#trash #sortHelper .modulewrapper {
	left: 25px;
	}

.module span.name {
	top: 6px;
	font: 12px Helvetica, Arial;
	line-height: 15px;
	display: block;
	}

.name, .croppedname {
	left: 1px;
	}

.type {
	top: 20px;
	left: 1px;
	font: 9px Helvetica, Arial;
	line-height: 10px;
	color: #999;
	}

.optionslink {
	position: absolute;
	top: 9px;
	right: 10px;
	height: 17px;
	width: 17px;
	background: url('../images/sbmmanager/optionsbutton.png') center top no-repeat;
	cursor: pointer;
	}	

.optionslink:hover {
	background: url('../images/sbmmanager/optionsbutton.png') no-repeat center bottom;
	}

.deletelink {
	position: absolute;
	top: 9px;
	right: 35px;
	height: 17px;
	width: 17px;
	background: url('../images/sbmmanager/deletebutton.png') center top no-repeat;
	cursor: pointer;
	/*opacity: .5;
	filter: alpha(opacity = 50);*/
	}	

.deletelink:hover {
	background: url('../images/sbmmanager/deletebutton.png') no-repeat center bottom;
	/*opacity: .9;
	filter: alpha(opacity = 90);*/
	}


/* Modules scheduled for deletion */
.trashed {
	height: 0;
	overflow: hidden;
	margin: 0;
	}

.trashed a {
	display: none;
	}


/* Available Modules */

.availablemodule {
	height: 25px;
	background: url('../images/sbmmanager/amodulebg-left.png') no-repeat;
	background-position: 0% 0px;
	}

.availablemodule .slidingdoor {
	background: url('../images/sbmmanager/amodulebg.png') no-repeat;
	background-position: 100% 0px;
	}

.availablemodule:hover, .availablemodule:hover .slidingdoor {
	background-position: 0% -50px;
	}

.availablemodule:hover .slidingdoor {
	background-position: 100% -50px;
	}


/* Marker Styling */

.marker {
	height: 35px;
	/*opacity: .15 !important;
	filter: alpha(opacity = 15) !important;*/
	}

.marker div * {
	display: none;
	}

.marker, .marker:hover {
	cursor: wait;
	}

.hovering {
	background: #f4f4f4;
}

#dragHelper {
	z-index: 10000;
	}

#dragHelper a {
	border: none;
	}

.selecthelper {
	background: #999;
	border: 1px dashed #333;
	}

.openoptions {
	padding: 1px 3px;
	margin: 0 0 0 auto;
	}


/* Options Window */

#optionswindow {
	position: fixed;
	top: 100px;
	left: 50%;
	margin-left: -250px;
	font-size: .9em;
	color: #aaa;
	width: 500px;
	z-index: 1000;
	}

#optionswindow table {
	border-collapse: collapse;
	}	

#optionswindow table td {
	vertical-align: top;
	}

.optt, .optb {
	height: 30px;
	}

.optl, .optr {
	width: 30px;
	}

.opttl {
	background: url('../images/sbmmanager/opttl.png') right bottom no-repeat;
	}

.optt {
	background: url('../images/sbmmanager/optt.png') bottom repeat-x;
	}

.opttr {
	background: url('../images/sbmmanager/opttr.png') left bottom no-repeat;
	}

.optl {
	background: url('../images/sbmmanager/optl.png') right repeat-y;
	}

.optr {
	background: url('../images/sbmmanager/optr.png') left repeat-y;
	}

.optbl {
	background: url('../images/sbmmanager/optbl.png') right top no-repeat;
	}

.optb {
	background: url('../images/sbmmanager/optb.png') top repeat-x;
	}

.optbr {
	background: url('../images/sbmmanager/optbr.png') left top no-repeat;
	}

.opttabs, .optcontents, .optbuttons {
	background: #ddd;
	}

.tabs a {
	text-decoration: none;
	font-weight: bold;
	}

#optionswindow fieldset {
	border: none;
	margin: 0;
	padding: 0;
}

.optcontents {
	position: relative;
	background: #ddd;
	}

#options {
	width: 440px;
	height: 360px;
	background: #ddd;
	}

.optbuttons {
	text-align: right;
	}

.optionsspinner {
	background: url('../images/spinner.gif') no-repeat center center !important;
	}

#optionswindow.optionsspinner .module-options-form {
	display: none;
	}

#optionswindow h2 {
	width: 100%;
	margin: 0;
}

#closelink {
	z-index: 1000;
	border: none;
	position: absolute;
	top: 10px;
	right: 10px;
	height: 30px;
	width: 30px;
	background: url('../images/sbmmanager/widget_close.png') no-repeat center center;
	display: none;
}

.tabbg {
	background: url('../images/sbmmanager/tabsbg.png') repeat-x bottom;
	height: 30px;
	width: 100%;
	position: relative;
	padding: 0 8px;
	margin-left: -8px;
}


#optionstab, #advancedtab, #displaytab {
	background: url('../images/sbmmanager/tabbg.png') no-repeat center bottom;
	border: none;
	outline: none;
	padding: 18px 10px 3px;
	color: #fff;
	position: absolute;
	bottom: 0;
	width: 90px;
	text-align: center;
	font-size: 11px;
}

#advancedtab {
	display: none;
	}

#displaytab-content legend {
	color: #333;
	font-size: 1.1em;
	line-height: 1.6em;
	font-weight: normal !important;
	}

#displaytab-content div {
	padding-bottom: 4px;
	}

#displaytab-content input {
	margin-left: 10px;
	}

#displaytab-content label {
	line-height: 1.6em;
	margin-left: 6px;
	}

.toggle-item {
	font-size: .8em;
	}

input[type=checkbox] {
	background: none;
	border: none;
	padding: 0;
	}
	
#specific-pages {
	height: 150px;
	overflow-x: hidden;
	overflow-y: scroll;
	padding: 5px;
	border: 1px solid #999;
	background: #e8e8e8;
	}

.checkbox-list {
	overflow: auto;
	list-style-type: none;
	padding: 3px 0;
	margin: 0;
	}

.checkbox-list li {
	margin: 0 0 0 40px;
	}

.checkbox-list input[type=checkbox] {
	margin: 3px 0 0 -20px !important;
	}

.checkbox-list label {
	padding: 2px;
	}

.checkbox-list label:hover {
	background: #ddd;
	}

.tools {
	position: relative;
	height: 20px;
	display: none;
	}

.checkoruncheck {
	display: inline;
	position: absolute;
	top: 3px;
	left: 10px;
	margin: 0;
	}

.showorhide {
	position: absolute;
	top: 6px;
	right: 0;
	margin: 0;
	display: inline;
	}

.showorhide input {
	margin: 0 !important;
	}

.showorhide label {
	margin: 0 10px 0 2px !important;
	}

#output-css-file {
	width: 390px;
	}

.selected {
	background: url('../images/sbmmanager/tabbgselected.png') no-repeat center bottom !important;
	color: #333 !important;
}

#optionstab {
	left: 5px;
	}

#advancedtab {
	left: 110px;
	}

#displaytab {
	left: 110px;
	}

#options label {
	color: #333;
	}

#options .defaultoptions {
	border-bottom: 1px solid #333;
	}

#name-container .titlelabel {
	display: block;
	}

#options #module-name {
	width: 280px;
	margin-right: 10px;
	padding: 3px;
}

#options textarea {
	width: 410px;
	}

#about-module-blurp {
	height: 160px;
	}

#php-module-code {
	height: 190px;
	}

#options #type-container {
	display: none;	
	color: #888;
	}

#options #type-container span {
	color: white;
	font-weight: bold;
	margin-right: 10px;
	}

#advancedtab-content, #displaytab-content, #module-update-success, #module-update-error {
	display: none;
	}

.submitbuttons {
	position: absolute;
	bottom: 33px;
	right: 35px;
	margin: 0;
}

.submitbuttons input {
	padding: 5px;
	}

.optionkeys { /* Not being used currently */
	position: absolute;
	bottom: 7px;
	left: 15px;
	font-size: 9px;
	color: #444;
	}


/* Misc. */

.humanMsg {
	top: 165px !important;
	}

.loader {
	background: white url('../images/spinner.gif') no-repeat 5px 4px;
	}
	
.helptext {
	font-size: .85em;
	line-height: 3em;
	color: #999;
	}

#error {
	display: none;
	}
	
#wpcontent {
	padding-bottom: 0;
	}

#footer {
	display: none; /* WP footer is cramping our style... */
	}
	