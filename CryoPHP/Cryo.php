<?php

    class Cryo {

        public static function Application(array $appConfig) {
            $lib = glob("CryoPHP/Lib/*.php");
            //autoload libraries.
            foreach($lib as $library){
                require_once($library);
            }

            
        }

    }

?>