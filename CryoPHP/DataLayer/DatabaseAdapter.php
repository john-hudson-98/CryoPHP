<?php

    namespace Cryo\DataLayer;

    interface DatabaseAdapter {
        public function query(string $query , array $binds = []) : array;
        public function escape(string $value) : string;
    }

?>