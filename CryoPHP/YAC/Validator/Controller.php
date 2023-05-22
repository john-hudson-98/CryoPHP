<?php

    namespace Cryo\YAC\Validator;

    class Controller implements ValidatorInterface {

        private $definition;

        public function validate(array $definition) : bool {
            $allowedSubTypes = ["MVCController" , "ReactApp" , "EndpointForward" , "LoadBalancer" , "ApiController"];

            if ( !in_array($definition['subType'] , $allowedSubTypes) ) {
                throw new \Exception("Unknown Sub Controller Type: {$definition['subType']}");
            }

            $cname = "\\" . get_class($this) . "\\" . $definition['subType'];
            
            $this->definition = $definition;

            $subValidator = new $cname();

            $subValidator->validate($definition); // will throw exception on fail

            return true;
        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>