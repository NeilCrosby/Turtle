<?php

require_once('Turtle.php');

function removeMagicQuotes (&$array) {
    if (!get_magic_quotes_gpc()) {
        return;
    }

    foreach ($array as $key => $val) {
        if (is_array($val)) {
            removeMagicQuotes($array[$key], $trim);
        } else {
            $array[$key] = stripslashes($val);
        }
    }   
}

removeMagicQuotes($_GET);

$turtle = new Turtle($_GET['commands'], 200, 200);

$filename = str_replace(
    array(' ', ':', '"'), 
    array('-', 'c', 'q'), 
    $_GET['commands']
);

header ("Content-Disposition: Attachment;filename=$filename.png"); 
header ('Content-Type: image/png');
echo $turtle->getImage();
