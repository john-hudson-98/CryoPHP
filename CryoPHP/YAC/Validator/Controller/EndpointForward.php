<?php

    namespace Cryo\YAC\Validator\Controller;

    class EndpointForward implements \Cryo\YAC\Validator\ValidatorInterface {

        private $definition;

        public function validate(array $definition) : bool {
            $this->definition = $definition;
            if ( !isset($definition['endpoints']) || count(array_keys($definition['endpoints'])) < 1 ) {
                throw new \Exception("YACValidationException - MVC Controller must have at least 1 endpoint, create the property 'endpoints' to add an endpoint");
            }
            foreach($definition['endpoints'] as $methodName => $config) {
                if ( stristr($methodName , '-') ) {
                    throw new \Exception("YACValidationException - Endpoint {$methodName} contains illegal characters, {$methodName} must be alphanumeric + underscores");
                }
                $endpoint = @$config['endpoint'];
                if ( !$endpoint ) {
                    throw new \Exception("YACValidationException - No Endpoint Property, each endpoint needs an endpoint property, see the wiki for an example of this class");
                }
                
                return true;
            }

        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>