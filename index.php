<?php

    session_start();

    ini_set("display_errors" , 1);
    error_reporting(E_ALL);

    require_once('CryoPHP/Cryo.php');

    Cryo::Application(json_decode(file_get_contents("cryo.config") , true));

?>