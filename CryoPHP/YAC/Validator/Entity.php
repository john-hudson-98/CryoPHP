<?php

    namespace Cryo\YAC\Validator;

    class Entity implements ValidatorInterface {

        private $definition;

        public function validate(array $definition) : bool {
            $this->definition = $definition;
            if ( !@$definition['meta']['primaryKey'] ) {
                throw new \Exception("YACValidationException - Entity musts have a meta property containing the child `primaryKey`");
            }
            if ( !@$definition['properties'] ) {
                throw new \Exception("YACValidationException - Entity doesn't have a properties property");
            }
            if ( count($definition['properties']) < 1 ) {
                throw new \Exception("YACValidationException - Entity must have at least 1 property");
            }
            foreach($definition['properties'] as $varName => $property){
                if ( !@$property['column'] ) {
                    throw new \Exception("YACValidationException - No `column` property found on property");
                }
                if ( !@$property['type'] ) {
                    throw new \Exception("YACValidationException - No `type` property found on property");
                }
            }
            return true;
        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>