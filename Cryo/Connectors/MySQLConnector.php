<?php

    namespace Cryo\Connectors;

    require_once(__DIR__ . '/../Framework/Ext/Yaml.php');

    /**
     * @description:
     * This is the primary database connector for the system, I will be adding the option
     * in the near future to allow other database engines such as Postgres or Dynamo
     * This class when connected, will create any tables defined in YAML files
     */
    /**
     * @singleton
     */
    class MySQLConnector implements IDatabaseConnector {

        /** @param MysqlDatabase */
        private $resource;

        /** @param {Singleton:self} */
        private static $self = null;

        private function __construct(){
            $this->connect();
        }
        public static function Get(){
            return self::$self ? self::$self : new MySQLConnector();
        }
        /**
         * @description - shorthand for real_escape_string
         */
        public function escape($escape){
            return $this->resource->real_escape_string($escape);
        }
        /**
         * @implements IDatabaseConnector::query
         */
        public function query(string $query , array $binds = []) : array{
            foreach($binds as $key => $val){
                if ( is_null($val) ) {
                    $query = str_replace(':' . $key . ':' , "NULL" , $query);
                } else {
                    $query = str_replace(':' . $key . ':' , "'{$this->resource->real_escape_string($val)}'" , $query);
                }
            }
            try{
                $resp = $this->resource->query($query);
            }catch(\Exception $e){
                throw new \Exception("MySQLConnector::Exception {$e->getMessage()}, your query was: {$query}");
            }

            if ( !$resp ) {
                throw new \Exception("MySQLConnector::Exception {$this->resource->error}, your query was: {$query}");
            }

            if ( @$resp->num_rows ) {
                $out = [];
                while($row = $resp->fetch_assoc()){
                    $out[] = $row;
                }
                return $out;
            }
            if ( $resp === true ) {
                return [];
            }
            //convert from object to associative array.
            return json_decode(json_encode($resp) , true);
        }
        public function connect(){
            $dot = new \Cryo\Parsers\DotEnv();

            if ( \Cryo\Stage::isDev() ) {
                $dot->load(".env.local");
            } else {
                $dot->load(".env.production");
            }
            if ( !$dot->get("mysql.host") ) {
                throw new \Exception("Missing environment variable in dotenv: mysql.host");
            }
            if ( !$dot->get("mysql.user") ) {
                throw new \Exception("Missing environment variable in dotenv: mysql.user");
            }
            if ( !$dot->get("mysql.schema") ) {
                throw new \Exception("Missing environment variable in dotenv: mysql.schema (A.K.A database)");
            }

            $this->resource = new \mysqli($dot->get("mysql.host") , $dot->get("mysql.user") , $dot->get("mysql.password"));

            $this->resource->query("CREATE DATABASE IF NOT EXISTS `{$dot->get("mysql.schema")}`");
            $this->resource->select_db($dot->get("mysql.schema"));

            $this->checkInstallation();
        }
        public function disconnect(){
            $this->resource->disconnect();
        }
        /**
         * Gets a collection of YAML installation files
         * and iterates them, parsing them and passing them
         * as arrays to the install function
         */
        public function checkInstallation(){
            @mkdir("var/cache/cryo/install" , 0755 , true);

            $toInstall = self::getYamlInstalls("src");

            foreach($toInstall as $installation){
                $doc = file_get_contents($installation);
                $this->install(\spyc_load($doc) , sha1($doc) , sha1($installation));
            }
        }
        /**
         * This will install the YAML files, then cache a marker file against them
         * When changes are made, the system will rerun the installers
         * This doesn't wipe existing tables, but can be used to add new tables
         * I'll be working on table update functionality to auto-add columns 
         */
        private function install($install , $sha1 , $cacheName){

            foreach($install['schema'] as $label => $table){
                $query = "CREATE TABLE IF NOT EXISTS {$table['table']} (\n";

                $i = 0;
                $primaryKey = null;
                foreach($table['fields'] as $field => $struct){
                    $query .= ($i > 0 ? ',' . PHP_EOL : '') . "\t" . $field . " {$struct['type']} " . (@$struct['null'] ? ' NULL ' : ' NOT NULL ') . (@$struct['default'] ? ' DEFAULT \'' . str_replace(['"' , "'"] , '' , $struct['default']) . '\' ' : '') . (@$struct['unique'] ? "UNIQUE " : '') . (@$struct['auto_increment'] ? 'AUTO_INCREMENT' : '');
                    if ( @$struct['primary'] ) {
                        $primaryKey = $field;
                    }
                    $i++;
                }
                if ( $primaryKey ) {
                    $query .= " , \n\tPRIMARY KEY(`{$primaryKey}`)";
                }
                if ( isset($table['foreign-keys']) ) {
                    foreach($table['foreign-keys'] as $fkName => $fk){
                        $query .= " ,\n\tFOREIGN KEY `fk_{$table['table']}_{$fkName}` (`{$fkName}`) REFERENCES {$fk['references']}";

                        if ( @$fk['delete'] ) {
                            $query .= " ON DELETE " . strtoupper($fk['delete']);
                        }
                        if ( @$fk['update'] ) {
                            $query .= " ON UPDATE " . strtoupper($fk['update']);
                        }
                    }
                }
                $query .= "\n)";
                $this->query($query);
            }
            
            file_put_contents('var/cache/cryo/install/' . $cacheName , $sha1);
        }
        /**
         * Just iterates the src directory getting a list of YAML files
         * where the Type == Install and Adapter is equal to this classes name
         */
        private static function getYamlInstalls(string $dir) : array{
            $out = [];
            foreach(glob($dir . "/*") as $entry){

                if ( is_dir($entry) ) {
                    $a = self::getYamlInstalls($entry);

                    $out = array_merge($out , $a);
                } else {
                    if ( stristr($entry , '.yaml') ) {
                        $file = file_get_contents($entry);
                        $type = explode("\n" , $file)[0];

                        if ( !stristr($type , 'type:') ) {
                            continue;
                        }

                        if ( !stristr($type , 'Install') ) {
                            continue;
                        }
                        if ( !stristr($file , '\Cryo\Connectors\MySQLConnector') ) {
                            continue;
                        }

                        $cacheName = sha1($entry);

                        if ( file_exists("var/cache/cryo/install/{$cacheName}") ) {
                            if ( file_get_contents("var/cache/cryo/install/{$cacheName}") == sha1($file) ) {
                                //no changes to make
                            } else {
                                $out[] = $entry;
                            }
                        } else {
                            $out[] = $entry;
                        }
                    }
                }
            }
            return $out;
        }
    }

?>