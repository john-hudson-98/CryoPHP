<?php

    namespace Cryo\YAC\Validator;

    class Install implements ValidatorInterface {
        private $definition;

        public function validate(array $definition) : bool {
            $this->definition = $definition;
            if ( !@$definition['Adapter'] ) {
                throw new \Exception("YACValidationException - Install has no adapter set, use `adapter` and set the value to a class that implements `\Cryo\DataLayer\DatabaseAdapter`");
            }
            if ( !@$definition['schema'] ) {
                throw new \Exception("YACValidationException - Install has no schema property, this must exist, and have children tables");
            }
            if ( count(array_keys(@$definition['schema'])) < 1 ) {
                throw new \Exception("YACValidationException - Install has no children tables");
            }
            foreach($definition['schema'] as $id => $table){
                if ( !@$table['table'] ) {
                    throw new \Exception("YACValidationException - Table Generation missing table name, specify using `table: MyTableName`");
                }
                if ( !@$table['fields'] ) {
                    throw new \Exception("YACValidationException - Table Generation missing fields");
                }
            }
           
            return true;
        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>