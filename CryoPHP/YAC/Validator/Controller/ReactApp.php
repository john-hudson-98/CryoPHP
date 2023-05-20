<?php

    namespace Cryo\YAC\Validator\Controller;

    class ReactApp implements \Cryo\YAC\Validator\ValidatorInterface {

        private $definition;

        public function validate(array $definition) : bool {
            $this->definition = $definition;
            
            if ( !isset($definition['app']) ) {
                throw new \Exception("YACValidationException - Definition must have an 'app' property, see the wiki online for more information");
            }
            if ( !isset($definition['app']['local_url']) || !isset($definition['app']['name']) ) {
                throw new \Exception("YACValidationException - Property 'app' must have 'local_url' and 'name', local URL points to the react server which is usually 'http://localhost:3000', see wiki for more information on Github");
            }
            if ( !isset($definition['route']) ) {
                throw new \Exception("YACValidationException - No route supplied so the router cannot map the request to this endpoint");
            }
            if ( !isset($definition['route']['path']) ) {
                throw new \Exception("YACValidationException - property 'route' requires a 'path' property, if you want this as the homepage use path: /");
            }
            if ( !isset($definition['route']['match_type']) ) {
                throw new \Exception("YACValidationException - property 'route' requires a 'match_type' property, example match_type: equals");
            }
            if ( !isset($definition['route']['mapsTo']) ) {
                throw new \Exception("YACValidationException - property 'route' requires a 'mapsTo' property, this is how it routes to the local react server");
            }
            return true;
        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>