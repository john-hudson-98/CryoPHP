<?php

    namespace Cryo\YAC\Converter\Controller;

    class ReactApp implements \Cryo\YAC\Converter\ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder{
            
            $classBuilder = new \Cryo\Core\Transform\ClassBuilder();
            $classBuilder->setExtends('\\Cryo\\Framework\\Controller\\MvcController');

            $routes = [];
            $routes[$definition['route']['path']] = array(
                'methods' => ['GET', 'OPTIONS'] , 
                'call' => 'index'
            );

            $classBuilder->addMethod('index' , [] , "
                \Cryo\Http\ReactRouter::serveApp('{$definition['app']['local_url']}' , '{$definition['app']['name']}' , str_replace('{$definition['route']['path']}' , '{$definition['route']['mapsTo']}' , \$_SERVER['REQUEST_URI']));
            \n");

            $classBuilder->addMethod("flagController" , [] , "\t\treturn true;\n" , 'bool' , true);
            $classBuilder->addMethod("flagReactApp" , [] , "\t\treturn true;\n" , 'bool' , true);
            $classBuilder->addMethod("getDefinition" , [] , "\t\treturn json_decode('" . json_encode($definition) . "' , true);\n" , 'array' , true);
            $classBuilder->addMethod("GetRoutes" , [] ,  "\t\treturn json_decode('" . json_encode($routes) . "' , true);\n" , 'array' , true);
            return $classBuilder;
        
        }
        public function getDefinition() : array{
            return [];
        }
    }

?>