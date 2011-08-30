<?php require('gzip-header-css.php'); ?>

/*
	HUMANIZED MESSAGES 1.0
	idea - http://www.humanized.com/weblog/2006/09/11/monolog_boxes_and_transparent_messages
	home - http://humanmsg.googlecode.com
*/

html, body {
	height: 100%; /* Damn you IE! */
}

.humanMsg {
	font: normal 20px/50px Helvetica, Arial, Sans-Serif;
	letter-spacing: -1px;
	position: fixed;
	top: 130px;
	left: 25%;
	width: 50%;
	color: white;
	background-color: black;
	text-align: center; 
	display: none;
	opacity: 0;
	z-index: 100000;
}

.humanMsg .round {
    border-left: solid 2px white;
	border-right: solid 2px white;
    font-size: 1px; height: 2px;
	}

.humanMsg p {
	padding: .3em;
	display: inline; 
	}

.humanMsg a {
	display: none;
	}
	
#humanMsgLog {
	font: normal 10px Helvetica, Arial, Sans-Serif;
	color: white;
	position: fixed;
	bottom: 0;
	left: 0;
	width: 100%;
	max-height: 200px;
	display: none;
	z-index: 10000;
	}

#humanMsgLog p {
	position: relative;
	left: 50%;
	width: 200px;
	margin: 0;
	margin-left: -100px;
	padding: 0 10px;
	line-height: 20px;
	background: #333;
	text-align: center;
	white-space: pre;
	cursor: pointer;
	}

#humanMsgLog p:hover {
	background: #222;
	}

#humanMsgLog ul {
	background: #eee url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAARCAIAAACaSvE/AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAE1JREFUeNqEjVEKACAIQ516Ee//5wVNCjIjaB/iY5vC3YkoIgDkVDOjQ5pqLCI3r2bLFzNzyydvM4uKqfJPKN4vyl9LO/7o3/6PhwADAIWkFPjc5eRrAAAAAElFTkSuQmCC) repeat-x;
	margin: 0;
	padding: 0;
	position: relative;
	max-height: 180px;
	overflow: auto;
	display: none;
	}

#humanMsgLog ul li {
	color: #555;
	font-size: 12px;
	list-style-type: none;
	border-bottom: 1px solid #ddd;
	line-height: 40px;
	display: none;
	padding: 0 20px;
	position: relative;
	overflow: hidden;
	white-space: pre;
	}

#humanMsgLog ul li:first-child {
	margin-top: 1px;
	}
	
#humanMsgLog ul li .error {
	color: orangered;
	}

#humanMsgLog ul li .indent {
	position: absolute;
	top: 0;
	left: 100px;
	margin-right: 200px;
	height: inherit;
	}