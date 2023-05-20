<?php

    namespace Cryo\YAC;

    class Validator {
        private $definition;
        public function validate(string $yaml) {

            if ( !stristr($yaml , PHP_EOL) ) {
                throw new \Exception("YACValidatorException - Invalid template, less than 1 lines of instructions");
            }
            $topLine = substr($yaml , 0 , strpos($yaml , PHP_EOL));

            $definition = spyc_load($yaml);
            unset($yaml);
            if ( !stristr($topLine , 'type:') ) {
                throw new \Exception("YACValidatorException - Valid YAC files need to have type as the top line");
            }
            if ( !isset( $definition['type'] ) ) {
                throw new \Exception("YACValidatorException - No type added to the YAC Document");
            }
            $validator = "\\Cryo\YAC\\Validator\\" . $definition['type'];

            if ( !class_exists($validator , true) ) {
                throw new \Exception("YACValidatorException - Unknown Entity Type: {$definition['type']}");
            }

            $inst = new $validator();

            if ( !$inst->validate($definition) ) {
                throw new \Exception("YACValidatorException - Couldn't validate YAML template");
            }
            $this->definition = $inst->getDefinition();
        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>