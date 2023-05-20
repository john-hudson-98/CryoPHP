<?php

    session_start();

    require_once('CryoPHP/Cryo.php');

    Cryo::Application(json_decode(file_get_contents("cryo.config") , true));

?>