<?php

    namespace Cryo\YAC\Converter\Controller;

    class EndpointForward implements \Cryo\YAC\Converter\ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder{
            
            $classBuilder = new \Cryo\Core\Transform\ClassBuilder();

            $routes = [];
            foreach($definition['endpoints'] as $methodName => $config){

                if ( !isset($routes[$config['route']['path']]) ) {
                    $routes[$config['route']['path']] = [];
                }
                
                $routes[$config['route']['path']][] = array(
                    'methods' => isset($config['route']['methods']) ? $config['route']['methods'] : '*' , 
                    'call' => $methodName
                );
                $authorizer = "";

                if ( isset($config['authorizer']) ) {
                    $class = $config['authorizer']['class'];
                    $authUrl = $config['authorizer']['authRedirect'];

                    $authorizer = "\t\t\t\$auth = new \\{$class}();\n\t\t\tif(!\$auth->isAuthorized()){ \n\t\t\t\tif ( !stristr(\$_SERVER['REQUEST_URI'] , '{$authUrl}')){ header('Location: {$authUrl}'); } }";
                }

                $classBuilder->addMethod($methodName , [] , "
                    {$authorizer}
                    \$ref = new \Cryo\Microservice\ForwardEndpoint();
                    return \$ref->forwardRequest('{$config['endpoint']['url']}' , " . (@$config['endpoint']['remove'] ? "'{$config['endpoint']['remove']}'" : 'null') . ");

                    \n");
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