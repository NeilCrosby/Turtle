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

removeMagicQuotes($_POST);
$commands = (isset($_POST['commands'])) ? $_POST['commands'] : '';

$error = false;
try {
    $turtle = new Turtle($commands);
} catch (Exception $e) {
    $error = $e->getMessage();
}

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
        
        <? if ($error): ?>

            <p>Error: <?= $error; ?></p>

        <? else: ?>

            <p>
                <img src="image.php?commands=<?= urlencode($turtle->getNormalisedTokens()); ?>">
            </p>
            <p>Normalised commands: <?= $turtle->getNormalisedTokens(); ?></p>

        <? endif; ?>
    </body>
</html>