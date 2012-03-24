<?php

require_once('toolbox.php');

removeMagicQuotes($_GET);

$turtle = new Turtle($_GET['commands'], 350, 350);

$maxFilenameLength = 20;

$filename = str_replace(
    array(' ', ':', '"', '?'), 
    array('-', 'c', 'q', 'p'), 
    $_GET['commands']
);

if ( strlen($filename) > $maxFilenameLength ) {
	$filenameMd5 = md5($filename);

	$filename = substr( $filename, 0, $maxFilenameLength ) . '_' . $filenameMd5;
}

header ("Content-Disposition: Attachment;filename=$filename.png"); 
header ('Content-Type: image/png');
echo $turtle->getImage();
