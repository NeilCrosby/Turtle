<?php

require_once('toolbox.php');

removeMagicQuotes($_GET);

$turtle = new Turtle($_GET['commands'], 350, 350);

$filename = str_replace(
    array(' ', ':', '"'), 
    array('-', 'c', 'q'), 
    $_GET['commands']
);

header ("Content-Disposition: Attachment;filename=$filename.png"); 
header ('Content-Type: image/png');
echo $turtle->getImage();
