<?php

    namespace Cryo\Mvc\Routers;

    class MvcRouter implements IRouter {

        /**
         * @description - Allows a controller that uses a Layout
         *              - to be routed. 
         */

        private static $self;

        private function __construct(){
            self::$self = $this;
        }
        
        public static function Get() : MvcRouter {
            return (self::$self ? self::$self : new MvcRouter());
        }

        public function canRoute(string $url , $controller) : bool {
            if ( !$controller->hasAnnotation('@Controller') ) {
                return false;
            }
            if ( $controller->hasAnnotation('@ReactApp') ) {
                return false;
            }
            foreach($controller->getMethods() as $method){
                if ( $method->hasAnnotation('@Route') ) {
                    $route = $method->getAnnotation('@Route');

                    if ( $route->hasValue("allow") ) {
                        $reqMethods = explode("," , $route->getCleanValue("allow"));
                        if ( !in_array(strtoupper($_SERVER['REQUEST_METHOD']) , $reqMethods ) ) {
                            return false;
                        }
                    }

                    $matching = $this->matchesRoute($route->getCleanValue('path') );
                    if ( $matching['match'] ) {
                        return true;
                    }
                }
            }
            return false;
        }
        public function route(string $url , $controller) {
            
            // now we need to check for attributes 
            // i want to allow a layout renderer, this will enable
            // minimal code with a full page layout.
            /**
             * TARGET USAGE
             * @Autowired
             * @Theme( theme="default" )
             * private LayoutRenderer layout; 
             */

            $className = "\\" . $controller->getNamespace() . '\\' . $controller->getClassName();

            $inst = new $className();

            foreach($controller->getMethods() as $method) {
                if ( $method->hasAnnotation('@Route') ) {

                    $route = $method->getAnnotation('@Route');

                    $matching = $this->matchesRoute($route->getCleanValue('path') );

                    if ( $matching['match'] ) {
                        // already matched, but we need path variables.

                        if ( $controller->hasAnnotation('@Protected') ) {
                            if ( !self::isAuthorized($controller->getAnnotation('@Protected')) ) {
                                header("Location: " . $controller->getAnnotation('@Protected')->getCleanValue('loginUrl'));
                            }
                        }

                        $_SERVER['PATH_VARIABLES'] = $matching['variables'];

                        $cname = '\\' . $controller->getNamespace() . '\\' . $controller->getClassName();

                        $inst = new $cname();

                        \Cryo\Mvc\RouterService::autowireDependencies($inst , $controller);

                        if ( $method->hasAnnotation('@Layout') ) {
                            $this->applyLayoutAnnotation($inst , $method->getAnnotation('@Layout') , $controller);
                        }

                        $methodName = $method->getName();

                        $resp = $inst->{$methodName}();

                        return $resp;
                        
                    }

                }
            }

            die();
        }
        private function isAuthorized($annotation){
            
        }
        private function applyLayoutAnnotation($instance , $annotation , $controller){
            foreach($controller->getProperties() as $property){

                if ( $property->getType() == '\Cryo\Mvc\Layout' ) {
                    
                    $propName = substr($property->getName() , 1);

                    $obj = new \ReflectionObject($instance);

                    $prop = $obj->getProperty($propName);
                    $prop->setAccessible(true);
                    $prop->getValue($instance)->setStructure($annotation->getCleanValue("structure"));
                    $prop->setAccessible(false);

                }

            }
        }
        /**
         * @description - breaks the url & route path down and compares them.
         */
        private function matchesRoute($path){
            $url = explode("?" , $_SERVER['REQUEST_URI'])[0];

            $pathItems = explode("/" , $path);
            $urlItems  = explode("/" , $url );

            $resp = array(
                'match' => false , 
                'variables' => []
            );

            if ( count($pathItems) !== count($urlItems) ) {
                return $resp;
            }
            for($i = 0;$i < count($pathItems);$i++){
                $path = $pathItems[$i];
                $url = $urlItems[$i];

                if ( $path == '*' ) {
                    // allows wildcarding
                    continue;
                }
                if ( @$path[0] == '{' ) {
                    //variable
                    $varName = str_replace(['{' , '}'] , '' , $path);
                    $resp['variables'][$varName] = is_integer($url) ? intval($url) : $url;
                    continue;
                }
                if ( $path !== $url ) {
                    return $resp;
                }
            }
            $resp['match'] = true;
            return $resp;
        }
    }

?>