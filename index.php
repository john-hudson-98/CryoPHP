<?php

    ini_set("display_errors" , 1);
    error_reporting(E_ALL);

    session_start();

    @mkdir("src");
    @mkdir("Plugins");


    require_once('Cryo/Boilerplate.php');

    \Cryo\Boilerplate::registerAutoloader();


    require_once('src/index.php');

?>