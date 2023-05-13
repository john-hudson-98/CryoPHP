<?php

    namespace Cryo;

    class Boilerplate {
        /**
         * @description - Registers AutoLoader for 2 namespaces
         *              - App, Cryo, Plugin
         *              - App    - Application Specific
         *              - Cryo   - Cryo Specifc
         *              - Plugin - Plugins to stop cryo and app namespace getting polluted  
         */
        public static function registerAutoloader() {
            spl_autoload_register(function($className){
                self::autoloadClass($className);
            });
        }
        public static function autoloadClass($className){

            if ( class_exists($className , false) ) {
                return;
            }
            $codespace = explode("\\" , $className)[0];

            switch($codespace){
                case "App":
                    $lookPath = 'src/' . str_replace('App/' , '' , str_replace('\\' , '/' , $className)) . '.cryo.php';

                    if ( !file_exists($lookPath) ) {
                        throw new \Exception("Class File not found {$lookPath}, looking for class name: {$className}");
                    }

                    $cacheDir = 'var/cache/cryo/objects';
                    @mkdir($cacheDir , 0777 , true);

                    $cacheName = $cacheDir . '/' . sha1($lookPath) . '.php';

                    // if ( file_exists($cacheName) ) {
                    //     require_once($cacheName);
                    //     $meta = unserialize(file_get_contents(str_replace('.php' , '.meta.obj' , $cacheName)));
                    //     FrameworkUtils::registerClass($script->getNamespace() . '\\' . $script->getClassName() , $meta);
                    // }
                    
                    // parse Alternate PHP and convert it back to pure PHP

                    $file = file_get_contents($lookPath);
                    if ( substr($file , 0 , 5) == '<?php' ) {
                        $file = substr($file , 5 , strlen($file));
                    }
                    if ( substr($file , strlen($file) - 2 , 2) == '?>' ) {
                        $file = substr($file , 0 , strlen($file) - 2);
                    }
                    $tokenizer = new \Cryo\Framework\Tokenizer($file);
                    $transformer = new \Cryo\Framework\Transformer($tokenizer);
                    $scriptDefinition = $transformer->transform();
                    
                    $builder = new Framework\ClassBuilder($cacheName , $scriptDefinition);
                    $php = $builder->getSource();
                    FrameworkUtils::registerClass($scriptDefinition->getNamespace() . '\\' . $scriptDefinition->getClassName() , $scriptDefinition);
                    
                    file_put_contents($cacheName , $php);
                    file_put_contents(str_replace('.php' , '.meta.obj' , $cacheName) , serialize($scriptDefinition));

                    require_once($cacheName);
                    
                    // convert to pure PHP, and statically assign the script definition to selfClass.
                break;
                case "Cryo":
                case "Plugin":
                    $lookPath = str_replace('\\' , '/' , $className) . '.php';

                    if ( !file_exists($lookPath) ) {
                        throw new \Exception("Class File not found {$lookPath}, looking for class name: {$className}");
                    }
                    require_once($lookPath);
                break;
            }
        }
        public static function createAnnotation(Framework\Annotation $annotation , $relativeNamespace = null) {
            $cname = ($relativeNamespace ? $relativeNamespace : '') . str_replace('@' , '' , $annotation->getName());

            // cname is an interface, we need to create a 
            // class that implements the interface
            // then include this class.

            return Framework\AnnotationClassBuilder::fromInterface($cname , $annotation);
        }
    }

?>