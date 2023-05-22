<?php

    namespace Cryo\YAC\Converter\Controller;

    class ApiController implements \Cryo\YAC\Converter\ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder{
            
            $classBuilder = new \Cryo\Core\Transform\ClassBuilder();
                        

            $classBuilder->addMethod("flagController" , [] , "\t\treturn true;\n" , 'bool' , true);

            
            $routes = [];
            
            foreach($definition['routes'] as $methodName => $config) {
                if ( !@$routes[$config['match']['path']] ) {
                    $routes[$config['match']['path']] = [];
                }
                $routes[$config['match']['path']][] = array(
                    'methods' => isset($config['match']['methods']) ? $config['match']['methods'] : '*' , 
                    'call' => $methodName
                );
                $arguments = [];
                if ( @$config['arguments'] ) {
                    foreach($config['arguments'] as $name => $cfg){
                        $arguments[] = (@$cfg['type'] ? $cfg['type'] . ' $' : '$') . $name . (@$cfg['default'] !== null ? ' = ' . @$cfg['default'] : '');
                    }
                }
                $query = @$config['query'];

                foreach(@$config['arguments'] ?? [] as $argName => $data){
                    $query = str_replace(':' . $argName , '{$' . $argName . '}' , $query);
                }

                $classBuilder->addMethod($methodName , $arguments , "\t\theader(\"Content-Type: application/json\");\n\t\t\$db = \\{$definition['adapter']}::Get();\n\t\t\t\$res = \$db->query(\"{$query}\"); if ( count(\$res) == 1 && count(array_keys(\$res[0])) == 1 ) { die(json_encode(\$res[0])); }else{ die(json_encode(\$res)); } \n" , 'array' , false);
            }
            $classBuilder->addMethod("GetRoutes" , [] ,  "\t\treturn json_decode('" . json_encode($routes) . "' , true);\n" , 'array' , true);
            
            return $classBuilder;
        
        }
        public function getDefinition() : array{
            return [];
        }
    }

?>