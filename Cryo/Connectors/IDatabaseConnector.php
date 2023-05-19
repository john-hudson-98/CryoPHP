<?php
    
    namespace Cryo\Connectors;

    /**
     * Basic Database Connection Interface - When adding a database adapter
     * Make sure it implements this interface. Some databases are NoSQL, 
     * Will need to come up with a solution for this. Even if it means
     * writing a SQL parser and passing a structured context to 
     * the DB adapter, I want to add support for services such as 
     * AWS DynamoDB
     */

    interface IDatabaseConnector {
        /**
         * @info - Binds are primarily for SQL adapters, 
         *       - However, this can be used for the 
         *       - NoSQL stuff
         */
        public function query(string $query , array $binds = []) : array; // must return array
        public function connect(); // should throw exception on error
        public function disconnect(); // stop hanging connections.
    }

?>