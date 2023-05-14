<?php

    ini_set("display_errors" , 1);
    error_reporting(E_ALL);

    session_start();

    @mkdir("src");
    @mkdir("Plugins");


    require_once('Cryo/Boilerplate.php');

    \Cryo\Boilerplate::registerAutoloader();

    if ( !file_exists('src/index.php') ) {
        file_put_contents("src/index.php" , "<?php \Cryo\Mvc::Application(); ?>");
    }
    require_once('src/index.php');

?>