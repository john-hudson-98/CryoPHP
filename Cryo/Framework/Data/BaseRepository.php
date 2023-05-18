<?php

    namespace Cryo\Framework\Data;
    
    abstract class BaseRepository {

        public function __construct(){
            $this->install();
        }
        public abstract function install();
        public abstract function getTableName() : string; //made abstract to make sure interface is autowired only.

        public function getDatabaseAdapter() : \Cryo\Connectors\IDatabaseConnector {
            //figure out the implementation on this.
            $dot = new \Cryo\Parsers\DotEnv();

            if ( $_SERVER['SERVER_NAME'] == 'localhost' ) {
                $dot->load(".env.local");
            } else {
                $dot->load(".env.production");
            }
            $connector = "\Cryo\Connectors\MySQLConnector";
            if ( $dot->get("cryo.repositoryschema") ) {
                $connector = $dot->get("cryo.repositoryschema");
            }

            return $connector::Get();

        }
        public function save($entity){
            $type = get_class($entity);

            $entityDef = \Cryo\FrameworkUtils::getClass($type);

            if ( !$entityDef->hasAnnotation('@Entity') ) {
                throw new \Exception("You cannot save an object that isn't marked with @Entity");
            }
            $repositoryBase = str_replace('\\Definition' , '' , get_class($this));

            $repository = \Cryo\FrameworkUtils::getClass($repositoryBase);

            $repoAnnotation = $repository->getAnnotation('@Repository');

            if ( substr($repoAnnotation->getCleanValue('entity'), 1) !== get_class($entity) ) {
                throw new \Exception("Repository " . $repositoryBase . ' cannot save entity ' . $type . ', the @Repository Annotation is set to ' . $repoAnnotation->getCleanValue('entity'));
            }
            //can now save.

            $fields = [];
            $idColumn = null;
            $reflectionObj = new \ReflectionObject($entity);
            foreach($entityDef->getProperties() as $property){
                $reflectionProperty = $reflectionObj->getProperty(substr($property->getName() , 1));

                if ( $reflectionProperty->isProtected() || $reflectionProperty->isPrivate() ) {
                    $reflectionProperty->setAccessible(true);
                }
                $column = $property->getAnnotation('@Column');
                if ( $property->hasAnnotation('@Id') ) {
                    
                    if ( !$column ) {
                        throw new \Exception("Repository Exception: Fields marked with @Id need to have a @Column attribute, like @Column( name=\"COL_NAME\" ) ");
                    }
                    $idColumn = $column->getCleanValue('name');
                    
                }
                $fields[$column->getCleanValue('name')] = $reflectionProperty->getValue($entity);
                if ( $reflectionProperty->isProtected() || $reflectionProperty->isPrivate() ) {
                    $reflectionProperty->setAccessible(false);
                }
            }
            $tableName = $repoAnnotation->getCleanValue('table');

            $query = "INSERT INTO {$tableName} ( ";

            $i = 0;
            foreach($fields as $field => $value) {
                $query .= ($i > 0 ? " , " : "") . " `{$field}` ";

                $i++;
            }
            $query .= " ) VALUES ( ";
            $i = 0;
            foreach($fields as $field => $value) {
                $query .= ($i > 0 ? " , " : "") . " :{$field}: ";

                $i++;
            }
            $query .= " ) ON DUPLICATE KEY UPDATE ";
            $i = 0;
            foreach($fields as $field => $value) {
                $query .= ($i > 0 ? " , " : "") . " `{$field}` = VALUES(`{$field}`) ";
                $i++;
            }
            $this->getDatabaseAdapter()->query($query , $fields);
        }
        public function __call($method , $args){
            if ( substr($method , 0 , strlen("findBy")) == "findBy" ) {
                //find by attribute name.
                //check if is a column in the table. 
                $self = \Cryo\FrameworkUtils::getClass(str_replace('\\Definition' , '' , get_class($this)));

                $repo = $self->getAnnotation('@Repository');

                $entity = $repo->getCleanValue('entity');
                $cname = $entity;

                \Cryo\Boilerplate::autoloadClass($entity[0] == '\\' ? substr($entity , 1) : $entity);
                $entName = $entity;
                $entity = \Cryo\FrameworkUtils::getClass($entity[0] == '\\' ? substr($entity , 1) : $entity);

                $fields = [];

                foreach($entity->getProperties() as $property){
                    if ( $property->hasAnnotation('@Column') ) {
                        $fields[] = $property->getAnnotation('@Column')->getCleanValue('name');
                    }
                }
                
                $fieldExists = false;
                foreach($fields as $field){
                    if ( stristr($method , $field) ) {
                        //call by this field.
                        $fieldExists = true;
                    }
                }

                if ( !$fieldExists ) {
                    throw new \Exception("Unknown method, class " . get_class($this) . " contains no member called " . $method . ", Reason: No such field exists ");
                }
                $column = $this->getColumnName($method , 'findBy');
                $table = $repo->getCleanValue('table');

                $q = "SELECT * FROM {$table} WHERE {$column} = :value: LIMIT 1";

                $resp = $this->getDatabaseAdapter()->query($q , array('value' => $args[0]));
                
                if ( count($resp) < 1 ) {
                    return null;
                }
                $data = $resp[0];

                $inst = new $cname();
                $refl = new \ReflectionObject($inst);
                foreach($data as $key => $value){

                    $propName = $key;
                    
                    foreach($entity->getProperties() as $prop){
                        if ( $prop->hasAnnotation('@Column') ) {
                            if ( $prop->getAnnotation('@Column')->getCleanValue("name") == str_replace('"' , '' , $key) ) {
                                $propName = $propName[0] == '$' ? substr($prop->getName() , 1) : $prop->getName();
                                
                            } 
                        }
                    }
                    
                    if ( property_exists($inst , $propName) ) {
                        $property = new \ReflectionProperty($inst , $propName);

                        if ( $property->isPrivate() || $property->isProtected() ) {
                            $property->setAccessible(true);
                            $property->setValue($inst , $value);
                            $property->setAccessible(false);
                        } else {
                            $inst->{$key} = $value;
                        }
                    }
                }
                return $inst;
                
                // return;
            }
            throw new \Exception("Unknown method, class " . get_class($this) . " contains no member called " . $method);
        }
        private function getColumnName(string $method , string $remove){
            // Define a regular expression to match the pattern "findBy<FieldName>"
            $pattern = "/^{$remove}([A-Z][a-z0-9]*)$/";
            
            // Use preg_match to see if the method_name matches the pattern
            $matches = [];
            $result = preg_match($pattern, $method, $matches);
            
            if ($result === 1) {
                // If the method_name matches the pattern, extract the field name and convert it to snake_case
                $field_name = $matches[1];
                $snake_case_name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $field_name));
                
                // Return the converted method name
                return $snake_case_name;
            } else {
                // If the method_name doesn't match the pattern, return null or throw an exception
                return null;
            }
        }
    } 

?>