<?php
# Bashfr extension
# <bashfr />

$wgExtensionFunctions[] = "wfBashfrExtension";

function wfBashfrExtension() {
    global $wgParser;
    $wgParser->setHook( "bashfr", "renderBashfr" );
}

function renderBashfr( $input, $argv, &$parser ) {
//$fortunes = explode(chr(13).chr(10)."%".chr(13).chr(10), file_get_contents('bashfr_fortunes'));
$fortunes = explode("\n%\n", file_get_contents('extensions/bashfr/bashfr_fortunes'));
$fortune = htmlentities($fortunes[rand(0, count($fortunes) - 1)], ENT_QUOTES);
$output = nl2br(ereg_replace('[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]', '<a href="\\0">\\0</a>', $fortune));
return $output;
}
?>
