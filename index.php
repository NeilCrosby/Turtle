<?php

require_once('toolbox.php');

removeMagicQuotes($_POST);
$commands = (isset($_POST['commands'])) 
          ? $_POST['commands'] 
          : <<<LOGO
; here's a repeatable procedure. Lets call it "hexagon"
TO hexagon :size :color
    SETC :color
    REPEAT 6 [ FD :size RT 60 ]
END

; now lets draw some hexagons
REPEAT 12 [ 
    RT 15 hexagon 50 "0,127,0
    RT 15 hexagon 30 "0,0,255
]

; now move the pen so we're in position for the red hexagons
; don't forget - commands don't have to be on separate lines - it's all just tokens in LogoLand.
PENUP FORWARD 110 RT 90 FORWARD 16 LT 150 PENDOWN

make "color 70
REPEAT 12 [
    REPEAT 18 [ hexagon 10 :color RT 30 ]
    MAKE "color SUM :color 15 ; add 15 to the value of the previous colour to make it brighter
    PENUP LT 220 FD 75 PENDOWN ; move the turtle to its next position
]
LOGO;

$error = false;
try {
    $turtle = new Turtle($commands);
} catch (Exception $e) {
    $error = $e->getMessage();
}

?><!DOCTYPE html>
<html lang="en">
    <head>
        <title>Simple Turtle Graphics Parser (written in PHP)</title>
        <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.4.1/build/reset-fonts-grids/reset-fonts-grids.css">
        <style type="text/css">
        html {
            font-family: Arial, sans-serif;
            background: #101010;
            color: #cfcfcf;
        }
 
        #doc4 {
            background: #101010;
        }
 
        #hd {
            margin: 0 4px -1em 0;
            padding-top: 1em;
            position: relative;
        }
        
        a {
            color: #dd621f;
        }
 
        h1 {
            font-size: 500%;
            font-weight: bold;
            font-family: Georgia, Palatino, "Palatino Linotype", Times, "Times New Roman", serif;
            letter-spacing: -0.02em;
            position: relative;
            padding-left: 0.3em;
        }
 
        h1 span {
            font-size: 26%;
            font-weight: normal;
        }
 
        h1 span.simple {
            display: block;
            position: absolute;
            left: -16px;
            top: 30px;
            writing-mode: tb-rl;
            -webkit-transform: rotate(90deg);	
            -moz-transform: rotate(90deg);
            -ms-transform: rotate(90deg);
            -o-transform: rotate(90deg);
            transform: rotate(90deg);
        }
        
        h2 {
            font-size: 138.5%;
            margin: 1em 4px 1em 0;
            font-family: Georgia, Palatino, "Palatino Linotype", Times, "Times New Roman", serif;
        }
         
        #bd .turtle-input {
            width: 60%;
            float: left;
        }
        
        #bd .turtle-input label {
            display: block;
            position: absolute;
            text-indent: -9000px;
        }
        
        #bd .turtle-input textarea {
            width: 95%;
            height: 350px;
        }
        
        #bd .turtle-input .submit {
            text-align: right;
            width: 95%;
        }
        
        #bd .turtle-error,
        #bd .turtle-output {
            width: 38%;
            float: left;
        }
        
        #bd .turtle-normalised {
            clear: both;
            padding: 0.5em 0;
        }
        
        #bd .turtle-output img {
            border: 1px solid #cfcfcf;
        }
 
        </style>
    </head>
    <body id="doc4">
        <div id="hd">
            <h1>
                <span class="simple">Simple</span> 
                Turtle Graphics Parser 
                <span>(written in PHP)</span>
            </h1>
            <p>
                Written due to a challenge at work. 
                <a href="https://github.com/NeilCrosby/Turtle/blob/master/README.markdown">Documentation</a>
                and 
                <a href="https://github.com/NeilCrosby/Turtle">code available on github</a>.
            </p>
        </div>
        <div id="bd">
            <div class="turtle-input">
                <h2>The Input:</h2>
                <form action="" method="post">
                    <p>
                        <label for="commands">Turtle Commands:</label>
                        <textarea id="commands" name="commands" cols="40" rows="20"><?= $commands; ?></textarea>
                    </p>
                    <p class="submit">
                        <input type="submit" value="Move that Turtle!">
                    </p>
                </form>
            </div>
        
            <? if ($error): ?>
                <div class="turtle-error">
                    <h2>The Errors:</h2>
                    <p><?= $error; ?></p>
                </div>
            <? else: ?>

                <div class="turtle-output">
                    <h2>The Output:</h2>
                    <p>
                        <img src="image.php?commands=<?= urlencode($turtle->getNormalisedTokens()); ?>">
                    </p>
                </div>
                
                <div class="turtle-normalised">
                    <h2>The Normalised Commands:</h2>
                    <p><?= $turtle->getNormalisedTokens(); ?></p>
                </div>

            <? endif; ?>
        </div>
        <div id="ft">
            <p>
                Written by 
                <a href="http://neilcrosby.com/" rel="me">Neil Crosby</a>
                in 2011.
            </p>
        </div>
    </body>
</html>