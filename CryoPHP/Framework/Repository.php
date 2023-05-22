<?php

    namespace Cryo\Framework;

    class Repository {
        

        public function __call($method , $args){
            
            if ( stristr($method , 'findBy') ) {
                $varName = str_replace('findBy' , '' , $method);
                $attrName = strtolower(preg_replace('/(.)([A-Z])/' , '$1_$2' , $varName));

                $tableName = $this->getTableName();

                $query = "SELECT * FROM {$tableName} WHERE {$attrName} = ?";

                return $this->getDbAdapter()->query($query , array($attrName => $args[0]));
            }

        }
    }

?>