<?php

function __autoload($class_name) {
    require_once($class_name.'.php');
}

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

?>