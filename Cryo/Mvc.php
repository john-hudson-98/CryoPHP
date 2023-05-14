<?php

    namespace Cryo;

    use Mvc\Controller;

    class Mvc {
        // loads all the .cryo.php files in the src directory
        // and listens routes the current web request to 
        // the correct controller

        public static function Application(){
            
            foreach(glob("Cryo/Mvc/*.php") as $bean){
                require_once($bean);
            }

            $files = self::getCryoFiles('src');

            foreach($files as $file){
                $cname = str_replace('/' , '\\' , 
                    str_replace(
                        'src/' , 
                        'App/' , 
                        str_replace('.cryo.php' , '' , $file)
                    )
                );

                Boilerplate::autoloadClass($cname);
            }
            // load application classes.
            
            $classes = self::getCryoUserClasses();

            $controllers = [];

            foreach($classes as $class){
                $def = \Cryo\FrameworkUtils::getClass($class);

                if ( $def->hasAnnotation('@Controller') ) {
                    $controllers[] = $def;
                }
            }
            foreach($controllers as $controller) {

                if ( $controller->hasAnnotation('@ReactApp') ) {

                    // figure out react micro app routing pattern
                }
                
                foreach($controller->getMethods() as $method) {

                    if ( $_SERVER['REQUEST_METHOD'] == 'GET' && $method->hasAnnotation('@Get') ) {
                        
                        //has the annotation, now we just check the annotation value.
                        $className = '\\' . $controller->getNamespace() . '\\' . $controller->getClassName();

                        $inst = new $className(); //controllers have no constructors

                        self::autowireDependencies($inst , $controller);

                        $call = $method->getName();

                        $get = Boilerplate::createAnnotation($method->getAnnotation('@Get') , '\\Cryo\Mvc\\');

                        if ( self::pathMatches($get->path() , $_SERVER['REQUEST_URI']) ) {
                            $headers = array();
                            $headers['Content-Type'] = $get->produces();

                            // go ahead and call the function, but first we need to get the arguments
                            $resp = $inst->{$call}();

                            foreach($headers as $header => $value){
                                header("{$header}: {$value}");
                            }

                            if ( is_array($resp) ) {
                                die(json_encode($resp));
                            } 
                            if ( is_object($resp) ) {
                                if ( method_exists($resp , 'serialize') ) {
                                    die(json_encode($resp->serialize()));
                                }
                                die(json_encode($resp));
                            }
                            die(strval($resp));
                        }
                    } else if ( $_SERVER['REQUEST_METHOD'] == 'POST' && $method->hasAnnotation('@Post') ) {
                        //has the annotation, now we just check the annotation value.
                        $className = '\\' . $controller->getNamespace() . '\\' . $controller->getClassName();

                        $inst = new $className(); //controllers have no constructors

                        self::autowireDependencies($inst , $controller);

                        $call = $method->getName();

                        $post = Boilerplate::createAnnotation($method->getAnnotation('@Post') , '\\Cryo\Mvc\\');

                        if ( $post->consumes() !== $_SERVER['CONTENT_TYPE'] ) {
                            die(json_encode(array('error' => 'Invalid Post Type, expected ' . $post->consumes())));
                        }

                        if ( self::pathMatches($post->path() , $_SERVER['REQUEST_URI']) ) {
                            $headers = array();
                            $headers['Content-Type'] = $get->produces();

                            // go ahead and call the function, but first we need to get the arguments
                            $resp = $inst->{$call}();

                            foreach($headers as $header => $value){
                                header("{$header}: {$value}");
                            }

                            if ( is_array($resp) ) {
                                die(json_encode($resp));
                            } 
                            if ( is_object($resp) ) {
                                if ( method_exists($resp , 'serialize') ) {
                                    die(json_encode($resp->serialize()));
                                }
                                die(json_encode($resp));
                            }
                            die(strval($resp));
                        }
                    }

                }
            }
            $dotenv = new \Cryo\Parsers\DotEnv();
            if ( $_SERVER['SERVER_NAME'] == 'localhost' ) {
                $dotenv->load(".env.local");
            } else {
                $dotenv->load(".env.production");
            }
            if ( $dotenv->get('react.fallback_app') ) {
                $handled = false;
                $app = null;
                foreach($controllers as $controller){
                    if ( $controller->hasAnnotation('@ReactApp') ) {
                        $app = str_replace('"' , '' , $controller->getAnnotation('@ReactApp')->getValue('app_name'));
                        if ($app  == $dotenv->get('react.fallback_app') ) {
                            $local_url = $controller->getAnnotation('@ReactApp')->getValue('local_url');
                            $handled = true;
                            \Cryo\Addons\React::serveApp($local_url , $app);
                        } 
                    }
                }
                if ( !$handled ) {
                    throw new \Exception("React app may exist, but the controller for it doesn't " . $dotenv->get('react.fallback_app'));
                }
            } else {
                include_once('Cryo/Defaults/404.html');
            }
        }
        private static function pathMatches($stored , $current){
            $current = str_replace(['///' , '//'] , '/' , str_replace('?' . $_SERVER['QUERY_STRING'] , '' , $current));
            
            if ( $current == $stored ) {
                return true;
            }
            if ( $current . "/" == $stored ) {
                return true;
            }
            if ( $current == $stored . "/" ) {
                return true;
            }
            if ( @preg_match($stored , $current) ) {
                return true;
            }
            $matchUrl = preg_quote($stored, '/');
    
            // Replace * with regex pattern for any character except newline
            $matchUrl = str_replace('\*', '.*', $matchUrl);
            
            // Add regex delimiters
            $regex = '/^' . $matchUrl . '$/';
            
            // Test if requestUrl matches regex
            return preg_match($regex, $current);
            // return true;
        }
        private static function autowireDependencies($instance , $meta){
            // do nothing for now.
            // meta is the script definition

            foreach($meta->getProperties() as $property) {
                if ( $property->hasAnnotation('@Autowired') ) {
                    $target = $property->getType();
                    
                    // load the class just in case it doesn't exist.
                    Boilerplate::autoloadClass($target);

                    $class = FrameworkUtils::getClass(substr($target , 1));
                    
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