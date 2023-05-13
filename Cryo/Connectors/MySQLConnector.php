<?php

    namespace Cryo\Connectors;

    class MySQLConnector implements IDatabaseConnector {

        private $resource;

        private static $self = null;

        private function __construct(){
            $this->connect();
        }
        public static function Get(){
            return self::$self ? self::$self : new MySQLConnector();
        }
        public function query(string $query , array $binds = []) : array{
            foreach($binds as $key => $val){
                $query = str_replace(':' . $key . ':' , "'{$this->resource->real_escape_string($val)}'" , $query);
            }
            $resp = $this->resource->query($query);

            if ( @$resp->num_rows ) {
                $out = [];
                while($row = $resp->fetch_assoc()){
                    $out[] = $row;
                }
                return $out;
            }
            return json_decode(json_encode($resp) , true);
        }
        public function connect(){
            $dot = new \Cryo\Parsers\DotEnv();

            if ( $_SERVER['SERVER_NAME'] == 'localhost' ) {
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

            $this->resource = new \mysqli($dot->get("mysql.host") , $dot->get("mysql.user") , $dot->get("mysql.password") , $dot->get("mysql.schema"));

            
        }
        public function disconnect(){
            $this->resource->disconnect();
        }
    }

?>