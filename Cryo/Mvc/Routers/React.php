<?php

    namespace Cryo\Mvc\Routers;

    class React implements IRouter {

        /**
         * @description - Allows for use of React Micro Apps.
         *              - I'll probably add support for VueJS
         *              - , Angular , Ember etc.. at some point
         *              
         *              - This allows use with create-react-app 
         *              - by setting the local_url value to the 
         *              - web address (usually localhost:3000)
         *              - and also allows for built applications
         */

        private static $self;

        private function __construct(){
            self::$self = $this;
        }
        
        public static function Get() : React {
            return (self::$self ? self::$self : new React());
        }

        public function canRoute(string $url , $controller) : bool {
            if ( !$controller->hasAnnotation('@ReactRoute') ) {
                return false; // you need to specify a react route
            }
            $route = $controller->getAnnotation('@ReactRoute');
            $value = $route->getCleanValue('value');
            switch($route->getCleanValue('match_type')) {
                case "starts_with":
                    if ( substr($url , 0 , strlen( $value )) == $value ) {
                        return true;
                    }
                    return false;
                case "equals":
                    return $url == $value || stristr($url . 'static' , $value);
                default:
                    return false;
            }
        }
        public function route(string $url , $controller) {
            $route = $controller->getAnnotation('@ReactRoute');
            $value = $route->getCleanValue('value');
            $mapTo = $route->getCleanValue('mapsTo');

            //we can map.
            $app = $controller->getAnnotation('@ReactApp');
            $name = $app->getCleanValue('app_name');
            $loc_url = $app->getCleanValue('local_url');

            $url = explode("?" , $_SERVER['REQUEST_URI'])[0];

            if ( $mapTo ) {
                $url = str_replace($value , $mapTo , $url);
            }

            \Cryo\Addons\React::serveApp($loc_url , $name , $url);
            die();
        }
    }

?>