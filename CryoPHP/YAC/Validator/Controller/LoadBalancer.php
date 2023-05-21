<?php

    namespace Cryo\YAC\Validator\Controller;

    class LoadBalancer implements \Cryo\YAC\Validator\ValidatorInterface {

        private $definition;

        public function validate(array $definition) : bool {
            $this->definition = $definition;
            if ( !isset($definition['servers']) || count(array_keys($definition['servers'])) < 1 ) {
                throw new \Exception("YACValidationException - MVC Controller must have at least 1 endpoint, create the property 'endpoints' to add an endpoint");
            }
            foreach($definition['servers'] as $methodName => $config) {
                if ( stristr($methodName , '-') ) {
                    throw new \Exception("YACValidationException - Endpoint {$methodName} contains illegal characters, {$methodName} must be alphanumeric + underscores");
                }
                $domain = @$config['domain'];
                if ( !$domain ) {
                    throw new \Exception("YACValidationException - No Domain Property, each server needs a domain property, see the wiki for an example of this class");
                }
                if ( !stristr($domain , 'http://') && !stristr($domain  , 'https://') ) {
                    throw new \Exception("YACValidationException - Domain must contain http:// or https://");
                }
                if ( !@$config['maxConcurrentRequests'] ) {
                    throw new \Exception("YACValidationException - Each Domain needs a maxConcurrentRequests value to limit the requests sent to each individual server");
                }
                
                return true;
            }

        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>