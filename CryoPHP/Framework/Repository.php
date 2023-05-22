<?php

    namespace Cryo\Framework;

    class Repository {
        
        public function findAll($start = 0 , $limit = 20){
            return $this->getDbAdapter()->query("SELECT * FROM {$this->getTableName()} ORDER BY 1 DESC LIMIT {$start} , {$limit} ");
        }
        public function __call($method , $args){
            
            if ( stristr($method , 'findBy') ) {
                $varName = str_replace('findBy' , '' , $method);
                $attrName = strtolower(preg_replace('/(.)([A-Z])/' , '$1_$2' , $varName));

                $tableName = $this->getTableName();

                $query = "SELECT * FROM {$tableName} WHERE {$attrName} = ?";

                return $this->getDbAdapter()->query($query , array($attrName => $args[0]));
            }

            if ( $method == 'save' ) {
                //save entity

                $schema = [];
                $path = str_replace('\\' , '/' , str_replace('App\\' , 'src/' , get_class($args[0])));

                $match = glob($path . ".*");

                if ( count($match) < 1 ) {
                    throw new \Exception("Either Entity doesn't exist, or the path cannot be found");
                }
                $entity = \spyc_load_file($match[0]);

                $query = "INSERT INTO {$this->getTableName()} ( \n";

                $i = 0;
                foreach($entity['properties'] as $propertyName => $data){
                    $query .= ($i > 0 ? " , \n\t" : "\t") . $data['column'];
                    $i++;
                }

                $query .= "\n) VALUES (\n";
                $i = 0;
                foreach($entity['properties'] as $propertyName => $data){
                    $query .= ($i > 0 ? " , \n\t" : "\t") . '?';
                    $i++;
                }
                $query .= "\n)";

                $values = [];
                $reflection = new \ReflectionClass(get_class($args[0]));
                foreach($entity['properties'] as $propertyName => $data){
                    $values[] = $reflection->getProperty($propertyName)->getValue($args[0]);
                }

                $this->getDbAdapter()->query($query , $values);
                return $this;
            }


            throw new \Exception("Unknown Method: {$method} on " . get_class($this));

        }
    }

?>