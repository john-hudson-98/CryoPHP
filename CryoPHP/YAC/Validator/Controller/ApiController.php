<?php

    namespace Cryo\YAC\Validator\Controller;

    class ApiController implements \Cryo\YAC\Validator\ValidatorInterface {

        private $definition;

        public function validate(array $definition) : bool {
            $this->definition = $definition;
            if ( !@$definition['adapter'] ) {
                throw new \Exception("YACValidationException - No Adapter on ApiController");
            }
            foreach($definition['routes'] as $methodName => $config){
                if ( !@$config['match'] ) {
                    throw new \Exception("YACValidationException - No Routes on {$methodName}");
                }
                if ( !@$config['query'] ) {
                    throw new \Exception("YACValidationException - No Query on endpoint {$methodName}");
                }
            }
            
            return true;
        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>