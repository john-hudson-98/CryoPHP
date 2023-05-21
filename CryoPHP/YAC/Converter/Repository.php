<?php

    namespace Cryo\YAC\Converter;

    class Repository implements ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder{
            
            $classBuilder = new \Cryo\Core\Transform\ClassBuilder();

            $classBuilder->addMethod('getPrimaryKey' , [] , "\t\treturn \\{$definition['meta']['entity']}::getPrimaryKey();\n" , 'string' , false);
            $classBuilder->addMethod('getTableName' , [] , "\t\treturn '{$definition['meta']['table']}';\n" , 'string' , false);
            $classBuilder->setExtends('\\Cryo\\Framework\\Repository');

            $classBuilder->addMethod('getDbAdapter' , [] , "\t\treturn \\{$definition['meta']['adapter']}::Get(); \n" , '\\Cryo\DataLayer\\DatabaseAdapter' , false);

            if ( @$definition['custom'] ) {
                foreach($definition['custom'] as $methodName => $props){
                    $query = $props['query'];
                    $argNames = [];

                    if ( @$props['arguments'] ) {
                        foreach($props['arguments'] as $arg => $meta){
                            $argNames[] = '$' . $arg;
                            switch($meta['type']) {
                                case "string":
                                    $query = str_replace("\${$arg}" , "'{\$this->getDbAdapter()->escape(\${$arg})}'" , $query);
                                break;
                                case "int":
                                    $query = str_replace("\${$arg}" , "{\$this->getDbAdapter()->escape(\${$arg})}" , $query);
                                break;
                            }
                        }
                    }
                    
                    $classBuilder->addMethod($methodName , $argNames , "\t\treturn \$this->getDbAdapter()->query(\"{$query}\");\n" , '?array' , false);
                }
            }

            return $classBuilder;
        
        }
        public function getDefinition() : array{
            return [];
        }
    }

?>