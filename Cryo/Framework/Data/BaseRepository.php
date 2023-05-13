<?php

    namespace Cryo\Framework\Data;
    
    abstract class BaseRepository {

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

    } 

?>