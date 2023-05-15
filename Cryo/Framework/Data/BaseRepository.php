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
    } 

?>