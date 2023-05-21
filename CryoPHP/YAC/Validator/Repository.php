<?php

    namespace Cryo\YAC\Validator;

    class Repository implements ValidatorInterface {

        private $definition;

        public function validate(array $definition) : bool {
            $this->definition = $definition;
            if ( !@$definition['meta']['table'] ) {
                throw new \Exception("YACValidationException - Entity musts have a meta property containing the children `table`,`install` and `entity`");
            }
            if ( !@$definition['meta']['install'] ) {
                throw new \Exception("YACValidationException - Entity musts have a meta property containing the children `table`,`install` and `entity`");
            }
            if ( !@$definition['meta']['entity'] ) {
                throw new \Exception("YACValidationException - Entity musts have a meta property containing the children `table`,`install` and `entity`");
            }
           
            return true;
        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>