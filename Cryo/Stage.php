<?php

    namespace Cryo;

    /**
     * @description - This class is supposed to help work out whether
     *              - the website is in development mode or not
     *              - sure localhost is easy to check against
     *              - but if this is run in docker on multiple ports
     *              - as a microservice, dev mode will always be true
    */

    class Stage {
        public static function isDev() : bool {
            if ( file_exists(".development") ) {
                // allow a development flag to be set by users
                // this is no good for the development of the 
                // framework though as git will track this, 
                // i can add this to gitignore though.
                return true;
            }
            // I need to add multiple checks for this though, as there maybe false positives
            if ( file_exists("src/.env.local") && !file_exists("src/.env.production")  ) {
                return true;
            }

            return false;
        }
    }

?>