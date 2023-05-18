<?php

    ini_set("display_errors" , 1);
    error_reporting(E_ALL);

    session_start();

    @mkdir("src");
    @mkdir("test");
    @mkdir("Plugins");

    $GLOBALS['startTime'] = microtime(true);

    require_once('Cryo/Boilerplate.php');

    \Cryo\Boilerplate::registerAutoloader();

    if ( !file_exists('src/index.php') ) {
        file_put_contents("src/index.php" , "<?php \Cryo\Mvc::Application(); ?>");
    }
    
    if ( stristr($_SERVER['SERVER_NAME'] , 'localhost') ) {
        //is dev.
        if ( stristr($_SERVER['REQUEST_URI'] , '/testsuite/') ) {
            if ( !file_exists('test/index.php') ) {
                file_put_contents("test/index.php" , "<?php \Cryo\Mvc::TestSuite(); ?>");
            }
            require_once('test/index.php');
            die();
        }
    }

    require_once('src/index.php');

?>