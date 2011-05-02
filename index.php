<?php

require_once('toolbox.php');

removeMagicQuotes($_POST);
removeMagicQuotes($_GET);

$commands = (isset($_POST['commands'])) 
          ? $_POST['commands']
          : '';

if (!$commands) {
    $example = (isset($_GET['example'])) ? $_GET['example'] : 'complex';
    $example = filter_var($example, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

    $filename = "examples/$example.logo";
    if (file_exists($filename)) {
        $commands = file_get_contents($filename);
    }
}

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
            margin: 1.5em 4px 0.5em 0;
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
            height: 328px;
        }
        
        #bd .turtle-input .submit {
            text-align: right;
            width: 95%;
        }
        
        #bd .examples {
            margin-bottom: 0.5em;
        }
        
        #bd .examples * {
            display: inline;
        }
        
        #bd .examples ol:before {
            content: "[";
        }
        
        #bd .examples ol:after {
            content: "]";
        }
        
        #bd .examples li {
            border-right: 2px solid #cfcfcf;
            padding: 0 5px;
        }
        
        #bd .examples li.last {
            border: none;
            padding-right: 0;
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
                <h2>
                    The Input<? if ($example): ?> (currently using the "<?= $example?>" example)<? endif; ?>:
                </h2>
                
                <div class="examples">
                    <p>
                        Write your own, or use an example:
                    </p>
                    <ol>
                        <li>
                            <a href="?example=square">square</a>
                        </li>
                        <li>
                            <a href="?example=centered">centered</a>
                        </li>
                        <li>
                            <a href="?example=variables">variables</a>
                        </li>
                        <li>
                            <a href="?example=procedures">procedures</a>
                        </li>
                        <li class="last">
                            <a href="?example=complex">complex</a>
                        </li>
                    </ol>
                </div>
                
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