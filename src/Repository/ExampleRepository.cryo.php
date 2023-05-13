<?php

    namespace App\Repository;

    @Repository
    interface ExampleRepository {
        @Query( value="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES" )
        @ArrayItem( class=App\Model\MysqlTable )
        public function getAllTables();

        @Query( value="SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES" )
        public function getCount() : int;
    }

?>