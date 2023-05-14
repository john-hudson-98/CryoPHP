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
                header("Location: application/json");
                die(json_encode($response));
            }
            
        }
        
    }

?>