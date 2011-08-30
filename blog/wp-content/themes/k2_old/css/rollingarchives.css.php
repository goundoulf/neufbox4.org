<?php require('header.php'); ?>

/* This file contains the CSS for the 'Advanced Navigation' features, including livesearch and rolling archives */

#pagetrack #dragHelper {
	overflow: visible !important;
}

#rollingarchives {
	position: absolute;
	padding: 20px 0;
	display: block;
	width: 500px;
	top: 0;
	}

#rollingarchives a:hover {
	text-decoration: underline;
	}

#rollnavigation {
	position: relative;
	}

#rollnavigation a:active, #rollnavigation a:focus {
	outline: none;
	}

#rollprevious,
#rollnext,
#rollload,
#rollhome,
#rollpages,
#rolldates {
	position: absolute;
	top: 3px;
	}

#rollprevious:hover,
#rollnext:hover,
#rollhome:hover {
	text-decoration: underline;
	cursor: pointer;
	}

#rollhome {
	display: none;
	left: 54px;
	background: url('../images/house.png') no-repeat center center;
	width: 16px;
	height: 16px;
	}

#rollload {
	background: url('../images/spinner.gif') no-repeat center center;
	top: 1px;
	left: 50%;
	margin-left: -8px;
	width: 16px;
	height: 16px;
	}

#rollload span,
#rollhome span {
	display: none;
	}

#rollnext,
#rollprevious {
	color: #666;
	font-weight: bold;
	}

#rollpages {
	top: 3px;
	left: 55px;
	color: #aaa;
	}

#rollhover {
	position: absolute;
	top: 7px;
	left: -47px;
	z-index: 100;
	height: 45px;
	background: url('../images/rollhover.png') no-repeat center top;
	}

#rollhover,
#rolldates {
	width: 100px;
	margin-left: 5px;
}

#rollhover {
	top: 11px;
	}
	
#rolldates {
	position: absolute;
	color: #999;
	text-align: center;
	font-size: .9em;
	top: 22px;
	margin: 0;
	}

#rollprevious {
	left: 0;
	text-align: left;
	}

#rollnext {
	right: 0;
	text-align: right;
	}

.emptypage #rollnavigation {
	visibility: hidden;
	}

.firstpage #rollprevious {
	visibility: visible;
	}


.firstpage #rollnext,
.firstpage #rollhome {
	visibility: hidden;
	}

.nthpage #rollnext,
.nthpage #rollprevious,
.nthpage #rollhome {
	visibility: visible;
	}

.lastpage #rollnext,
.lastpage #rollhome {
	visibility: visible;
	}

.lastpage #rollprevious {
	visibility: hidden;
	}

#pagetrackwrap {
	position: absolute;
	top: 6px;
	left: 130px;
	width: 240px;
	padding-right: 9px;
	background: url('../images/sliderbgright.png') no-repeat right center;
	}

#pagetrack {
	height: 7px;
	background: url('../images/sliderbgleft.png') no-repeat left center;
	}

#pagehandle {
	top: -5px;
	width: 17px;
	height: 17px;
	background: url('../images/sliderhandle.png') no-repeat center center;
	cursor: col-resize;
	}

#primarycontent {
	clear: both;
	}

div.trimmed .hentry {
	background: #f5f5f5;
	}

div.trimmed .hentry.alt {
	background: transparent;
	}

#texttrimmer {
	position: absolute;
	width: 55px;
	height: 15px;
	top: 10px;
	right: 55px;
	}

.firstpage #texttrimmer,
.firstpage #pagetrackwrap {
	visibility: hidden;
	}

.nthpage #texttrimmer,
.nthpage #pagetrackwrap,
.lastpage #texttrimmer,
.lastpage #pagetrackwrap {
	visibility: visible;
	}

#trimmertrackwrap {
	background: url('../images/sliderbgright.png') no-repeat right center;
	height: 7px;
	width: 50px;
	top: 6px;
	padding-right: 4px;
	position: absolute;
	display: none;
	}

#trimmertrack {
	background: url('../images/sliderbgleft.png') no-repeat left center;
	height: 7px;
	}

#trimmerhandle {
	width: 7px;
	height: 7px;
	background: url('../images/sliderhandle.png') no-repeat center center;
	cursor: col-resize;
	}

#trimmermore,
#trimmerless {
	display: none;
	position: absolute;
	top: 0;
	height: 16px;
	width: 16px;
	}

#trimmermore span,
#trimmerless span {
	display: none;
	}

#trimmermore:hover,
#trimmerless:hover {
	cursor: pointer;
	}

#trimmermore {
	right: 0;
	background: url('../images/trim_more.png');
	}

#trimmerless {
	left: 0;
	background: url('../images/trim_less.png');
	}

#trimmertrim:hover, #trimmeruntrim:hover {
	text-decoration: underline;
	}

#trimmertrim, #trimmeruntrim {
	cursor: pointer;
	position: absolute;
	top: 13px;
	width: 50px;
	text-align: center;
	color: #999;
	}

.trimmed #trimmertrim {
	display: none;
	}

#trimmeruntrim {
	display: none;
	}

.trimmed #trimmeruntrim {
	display: block;
	}

body.smartposition #rollingarchives {
	position: fixed;
	top: 0px;
	background: #fff;
	border-bottom: 1px solid #eee;
	width: 500px;
	z-index: 10;
	padding-top: 10px;
	padding-bottom: 30px;
	}

body.smartposition #trimmertrim, body.smartposition #trimmeruntrim {
	top: 3px;
	}

#dynamic-content { /* Make room for the rolling archives */
	padding-top: 30px;
	}

body.onepageonly #dynamic-content { /* For the rare case of having only one page of content on the frontpage */
	padding-top: 0;
	}