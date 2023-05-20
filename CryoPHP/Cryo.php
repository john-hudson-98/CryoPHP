<?php

    class Cryo {

        private static $customAutoloaders = [];

        public static function Application(array $appConfig) {
            $lib = glob("CryoPHP/Lib/*.php");
            //autoload libraries.
            foreach($lib as $library){
                require_once($library);
            }

            spl_autoload_register(function($className) {
                if ( substr($className , 0 , 5) == 'Cryo\\' ) {
                    self::loadCryoClass($className);
                    return;
                }
                $imported = false;
                foreach(self::$customAutoloaders as $loader) {
                    if ( $loader->canAutoload($className) ) {
                        $loader->import($className);
                        $imported = true;
                        break;
                    }
                }
                if ( !$imported ) {
                    throw new \Exception("CryoException - Cannot autoload cryo class {$className} - no valid autoloader can load this");
                }
            });

            $router = new \Cryo\Http\Router();
            $router->route(explode("?" , $_SERVER['REQUEST_URI'])[0] , $appConfig);
        }
        public static function addCustomAutoLoader(\Cryo\Core\AutoloaderInterface $autoloader) {
            self::$customAutoloaders[] = $autoloader;
        }

        private static function loadCryoClass($className){
            $path = 'CryoPHP/' . str_replace('\\' , '/' , substr($className , 5)) . '.php';

            if ( !file_exists($path) ) {
                throw new \Exception("CryoException - Cannot autoload cryo class {$className} - file doesn't exist");
            }
            require_once($path);
        }
    }

?>