<?php

    session_start();

    ini_set("display_errors" , 1);
    error_reporting(E_ALL);

    require_once('CryoPHP/Cryo.php');
    
    $conf = [];

    if ( file_exists("cryo.config") ) {
        $conf = json_decode(file_get_contents("cryo.config") , true);
    }

    Cryo::Application($conf);

?>