<?php

    namespace Cryo\YAC;

    class Loader {

        public function load($yamlFile){
            $validator = new Validator();
            $validator->validate($yamlFile);

            //an exception would be thrown if YAML is not valid YAC
            
        }

    }

?>