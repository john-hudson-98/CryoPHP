<?php

    namespace Cryo\YAC;

    class Validator {

        public function validate(string $yaml) {

            $topLine = substr($yaml , 0 , strpos($yaml , PHP_EOL));

            $definition = spyc_load($yaml);
            unset($yaml);
            if ( !stristr($topLine , 'type:') ) {
                throw new \Exception("YACValidatorException - Valid YAC files need to have type as the top line");
            }
            if ( !isset( $definition['type'] ) ) {
                throw new \Exception("YACValidatorException - No type added to the YAC Document");
            }
            
        }

    }

?>