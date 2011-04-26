<?php

require_once('Turtle.php');

$turtle = new Turtle($_GET['commands'], 200, 200);

header ('Content-Type: image/png');
echo $turtle->getImage();
