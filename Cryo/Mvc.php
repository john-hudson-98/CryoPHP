<?php

    namespace Cryo;

    use Mvc\Controller;

    class Mvc {
        // loads all the .cryo.php files in the src directory
        // and listens routes the current web request to 
        // the correct controller

        public static function Application(){
            
            foreach(glob("Cryo/Mvc/Annotations/*.php") as $bean){
                require_once($bean);
            }

            $response = Mvc\RouterService::route();

            if ( is_array($response) ) {
                header("Content-Type: application/json");
                die(json_encode($response));
            }
            
        }
        public static function getFiles($dir){
            $out = [];
            foreach(glob($dir . '/*') as $file){
                if ( is_dir($file) ) {
                    $out = array_merge($out , self::getFiles($dir . '/' . $file));
                } else {
                    $out[] = $file;
                }
            }
            return $out;
        }
        public static function TestSuite() {
            // get all tests then show a pretty UI
            $phptests = [];
            $cryotests = [];
            foreach(self::getFiles('test') as $file){
                if ( stristr($file , '.test.php') ) {
                    $phptests[] = $file;
                }
                if ( stristr($file , '.test.cryo') ) {
                    $cryotests[] = $file;
                }
            }
            
        }
        
    }

?>