<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$env = (".env");

$handle = fopen($env, "r");

if ($handle){
    while(($line = fgets($handle)) !== false){
        $array = explode("=", $line);
        if (count($array) == 2){
            $name = trim($array[0]);
            $value = trim($array[1]);
            defined($name) || define($name, $value);
        }
    }

    fclose($handle);
}else{
    die("Unable to set up");
}