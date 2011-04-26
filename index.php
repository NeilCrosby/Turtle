<?php

require_once('Turtle.php');

$commands = (isset($_POST['commands'])) ? $_POST['commands'] : '';

$turtle = new Turtle($commands);

?><!DOCTYPE html>
<html lang="en">
    <head>
        <title>Turtle</title>
    </head>
    <body>
        <h1>Turtle</h1>
        <form action="" method="post">
            <p>
                <label for="commands">Turtle Commands:</label>
                <textarea id="commands" name="commands" cols="40" rows="20"><?= $commands; ?></textarea>
            </p>
            <p>
                <input type="submit">
            </p>
        </form>
        <p>
            <img src="image.php?commands=<?= urlencode($turtle->getNormalisedTokens()); ?>">
        </p>
        <p>Normalised commands: <?= $turtle->getNormalisedTokens(); ?></p>
        <p>Error: <?= $turtle->getError(); ?></p>
    </body>
</html>