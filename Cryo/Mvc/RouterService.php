<?php

    namespace Cryo\Mvc;

    class RouterService {

        public static function route(){

            self::loadApplication();
            $classes = self::getCryoUserClasses();

            $controllers = [];

            foreach($classes as $class){
                $def = \Cryo\FrameworkUtils::getClass($class);

                if ( $def->hasAnnotation('@Controller') ) {
                    $controllers[] = $def;
                }
            }
            $url = explode("?" , $_SERVER['REQUEST_URI'])[0]; // remove query string
            $routers = self::getApplicationRouters();
            //now we route
            foreach($controllers as $controller) {
                if ( $controller->hasAnnotation('@ReactApp') ) {
                    foreach($routers as $router){
                        if ( $router->canRoute($url , $controller) ) {
                            $resp = $router->route($url , $controller);
                            // it is possible a die could be called inside route.
                            return $resp;
                        }
                    }
                } else {
                    foreach($routers as $router) {
                        if ( $router->canRoute($url , $controller) ) {
                            $resp = $router->route($url , $controller);
                            // it is possible a die could be called inside route.
                            return $resp;
                        }
                    }
                    
                }

            }
            die(file_get_contents("Cryo/Defaults/404.html"));
        }
        private static function loadApplication(){
            $classes = self::getCryoFiles('src');

            foreach($classes as $class){
                $cname = str_replace('/' , '\\' , str_replace('.cryo.php' , '' , str_replace('src/' , 'App/' , $class)));

                \Cryo\Boilerplate::autoloadClass($cname);
            }
        }
        /**
         * @description - All Routers are SINGLETONS. They must have a static method called Get()
         */
        private static function getApplicationRouters() : array {
            $out = [];

            $routers = glob("Cryo/Mvc/Routers/*.php");

            foreach($routers as $router){
                if ( basename($router)[0] == 'I' ) {
                    continue; // interfaces in this directory start with I
                }
                $cname = str_replace('/' , '\\' , str_replace('.php' , '' , $router));

                $out[] = $cname::Get();
            }

            return $out;
        }
        public static function autowireDependencies($instance , $meta){
            // do nothing for now.
            // meta is the script definition

            foreach($meta->getProperties() as $property) {
                if ( $property->hasAnnotation('@Autowired') ) {
                    $target = $property->getType();

                    if ( $target[0] == '\\' ) {
                        $target = substr($target , 1);
                    }
                    
                    // load the class just in case it doesn't exist.
                    \Cryo\Boilerplate::autoloadClass($target);

                    if ( substr($target , 0 , 4) == 'Cryo' ) {

                        $target = '\\' . $target;

                        $autowired = new $target();

                        $obj = new \ReflectionObject($instance);
                        $propName = substr($property->getName() , 1);

                        if ( method_exists($autowired , "onAutowired") ) {
                            $autowired->onAutowired($property);
                        }

                        $prop = $obj->getProperty($propName);
                        $prop->setAccessible(true);
                        $prop->setValue($instance , $autowired);
                        $prop->setAccessible(false);
                        return;
                    }

                    $class = \Cryo\FrameworkUtils::getClass($target);
                    
                    if ( $class ) {

                        $autowired = null;
                        
                        if ( $class->hasAnnotation('@Repository') ) {
                            $repositoryBuilder = new \Cryo\MvcClassExtenders\Repository($target);

                            if ( $repositoryBuilder->exists() ) {
                                $autowired = $repositoryBuilder->import();
                            } else {
                                $autowired = $repositoryBuilder->buildRepositoryClass();
                            }
                        }

                        if ( $autowired ) {
                            $obj = new \ReflectionObject($instance);
                            $propName = substr($property->getName() , 1);

                            if ( method_exists($autowired , "onAutowired") ) {
                                $autowired->onAutowired($property);
                            }

                            $prop = $obj->getProperty($propName);
                            $prop->setAccessible(true);
                            $prop->setValue($instance , $autowired);
                            $prop->setAccessible(false);
                            
                        }

                    } else {
                        throw new \Exception("Cannot Autowire class {$target}");
                    }
                }
            }
            
        }
        private static function getCryoUserClasses() : array{
            $out = [];

            foreach(get_declared_classes() as $className){
                if ( substr($className , 0 , 4) == 'App\\' ) {
                    $out[] = $className;
                }
            }
            foreach(get_declared_interfaces() as $iface){
                if ( substr($iface , 0 , 4) == 'App\\' ) {
                    $out[] = $iface;
                }
            }
            return $out;
        }
        private static function getCryoFiles(string $dir) : array{
            $out = [];
            foreach(glob($dir . "/*") as $entry){

                if ( is_dir($entry) ) {
                    $a = self::getCryoFiles($entry);

                    $out = array_merge($out , $a);
                } else {
                    if ( stristr($entry , '.cryo.php') ) {
                        $out[] = $entry;
                    }
                }
            }
            return $out;
        }
    }

?>