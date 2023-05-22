<?php

    namespace Cryo\YAC\Converter;

    class Entity implements ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder{
            
            $classBuilder = new \Cryo\Core\Transform\ClassBuilder();

            $classBuilder->addMethod('getPrimaryKey' , [] , "\t\treturn '{$definition['meta']['primaryKey']}';\n" , 'string' , true);

            foreach($definition['properties'] as $name => $conf){
                $classBuilder->addProperty($name , $conf['type']);
                if ( in_array('set' , @$conf['def']) ) {
                    $classBuilder->addMethod('set' . ucfirst($name) , ['$value'] , "\t\t\$this->{$name} = \$value;\n" , 'void' , false);
                }
                if ( in_array('get' , @$conf['def']) ) {
                    $classBuilder->addMethod('get' . ucfirst($name) , [] , "\t\treturn \$this->{$name};\n" , '?' . $conf['type'] , false);
                }
            }

            return $classBuilder;
        
        }
        public function getDefinition() : array{
            return [];
        }
    }

?>