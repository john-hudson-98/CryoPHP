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
                $authorizer = "";

                if ( isset($config['authorizer']) ) {
                    $class = $config['authorizer']['class'];
                    $authUrl = $config['authorizer']['authRedirect'];

                    $authorizer = "\t\t\t\$auth = new \\{$class}();\n\t\t\tif(!\$auth->isAuthorized()){ \n\t\t\t\tif ( !stristr(\$_SERVER['REQUEST_URI'] , '{$authUrl}')){ header('Location: {$authUrl}'); } }";
                }

                $classBuilder->addMethod($methodName , [] , "
                    {$authorizer}
                    if ( \$this->getLayout() ) {
                        \$this->getLayout()->setTheme('{$definition['meta']['theme']}');
                        \$this->getLayout()->setStructure('{$config['structure']}');    
                    }

                    include('{$config['handle']}');\n");
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