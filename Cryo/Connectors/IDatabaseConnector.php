<?php
    
    namespace Cryo\Connectors;

    interface IDatabaseConnector {
        public function query(string $query , array $binds = []) : array; // must return array
        public function connect(); // should throw exception on error
        public function disconnect(); // stop hanging connections.
    }

?>