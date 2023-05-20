<?php

    namespace Cryo\YAC\Converter\Controller;

    class MVCController implements \Cryo\YAC\Converter\ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder{
            
            $classBuilder = new \Cryo\Core\Transform\ClassBuilder();
            $classBuilder->setExtends('\\Cryo\\Framework\\Controller\\MvcController');

            $routes = [];
            foreach($definition['routes'] as $methodName => $config){

                if ( !isset($routes[$config['match']['path']]) ) {
                    $routes[$config['match']['path']] = [];
                }
                
                $routes[$config['match']['path']][] = array(
                    'methods' => isset($config['match']['methods']) ? $config['match']['methods'] : '*' , 
                    'call' => $methodName
                );

                $classBuilder->addMethod($methodName , [] , "\t\tinclude('{$config['handle']}');\n");
            }
            $classBuilder->addMethod("flagController" , [] , "\t\treturn true;\n" , 'bool' , true);
            $classBuilder->addMethod("GetRoutes" , [] ,  "\t\treturn json_decode('" . json_encode($routes) . "' , true);\n" , 'array' , true);

            return $classBuilder;
        
        }
        public function getDefinition() : array{
            return [];
        }
    }

?>