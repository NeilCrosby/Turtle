<?php

require_once('Turtle.php');

$turtle = new Turtle($_GET['commands'], 200, 200);

$filename = str_replace(' ', '-', $_GET['commands']);

header ("Content-Disposition: Attachment;filename=$filename.png"); 
header ('Content-Type: image/png');
echo $turtle->getImage();
