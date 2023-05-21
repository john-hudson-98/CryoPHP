<?php

    namespace Cryo\YAC\Converter\Controller;

    class LoadBalancer implements \Cryo\YAC\Converter\ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder{
            
            $classBuilder = new \Cryo\Core\Transform\ClassBuilder();

            $classBuilder->setExtends('\Cryo\Http\LoadBalancer');
            $classBuilder->addMethod("index" , [] , "
            \$servers = json_decode('" . json_encode($definition['servers']) . "' , true);\n
            \$this->dispatch(\$servers , '{$definition['persistenceType']}');
            \n");
            
            $classBuilder->addMethod("flagLoadBalancer" , [] , "\t\treturn true;\n" , 'bool' , true);
            $classBuilder->addMethod("flagController" , [] , "\t\treturn true;\n" , 'bool' , true);
            $classBuilder->addMethod("GetRoutes" , [] ,  "\t\treturn [];\n" , 'array' , true);
            if ( $definition['databaseProvider'] ) {
                $classBuilder->addMethod("GetDbAdapter" , [] , "\t\treturn \\{$definition['databaseProvider']}::Get();\n" , '\Cryo\DataLayer\DatabaseAdapter' , false);
            }

            return $classBuilder;
        
        }
        public function getDefinition() : array{
            return [];
        }
    }

?>