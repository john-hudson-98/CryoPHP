<?php

    namespace Cryo\YAC\Validator\Controller;

    class MVCController implements \Cryo\YAC\Validator\ValidatorInterface {

        private $definition;

        public function validate(array $definition) : bool {
            $this->definition = $definition;
            if ( !isset($definition['meta']) ) {
                throw new \Exception("YACValidationException - MVC Controller must have a 'meta' property, with a 'theme' property as a child as this is a block based layout controller");
            }
            if ( !isset($definition['routes']) || count(array_keys($definition['routes'])) < 1 ) {
                throw new \Exception("YACValidationException - MVC Controller must have at least 1 route, create the property 'routes' to add a route");
            }
            foreach($definition['routes'] as $methodName => $config) {
                if ( stristr($methodName , '-') ) {
                    throw new \Exception("YACValidationException - Route {$methodName} contains illegal characters, {$methodName} must be alphanumeric + underscores");
                }
                if ( !isset($config['match']) ) {
                    throw new \Exception("YACValidationException - Route {$methodName} is invalid, it has no match property, it cannot route without this property");
                }
                if ( !isset($config['match']['path']) ) {
                    throw new \Exception("YACValidationException - Route {$methodName} is invalid, it has a match property, but it cannot route due to a missing path");
                }
                if ( !isset($config['handle']) ) {
                    throw new \Exception("YACValidationException - Route {$methodName} is invalid, it has no handle so cannot produce an output");
                }
                if ( !file_exists($config['handle']) ) {
                    throw new \Exception("YACValidationException - Route {$methodName} is invalid, the handler file doesn't exist");
                }
                if ( !isset($config['structure']) ) {
                    throw new \Exception("YACValidationException - Route {$methodName} is invalid, a valid structure must be assigned as MVCController is a block based, layout powered Controller");
                }
                $layoutFile = "src/theme/" . $definition['meta']['theme'] . "/layout/" . $config['structure'] . '.json';
                $tplFile = "src/theme/{$definition['meta']['theme']}/template/page/{$config['structure']}.phtml";
                if ( !file_exists($layoutFile) ) {
                    throw new \Exception("YACValidationException - Route {$methodName} is invalid, the structure file doesn't exist, hint: the file location is {$layoutFile}");
                }
                if ( !file_exists($tplFile) ) {
                    throw new \Exception("YACValidationException - Route {$methodName} is invalid, the template doesn't exist, hint: the file location is {$tplFile}");
                }
                return true;
            }

        }
        public function getDefinition() : array{
            return $this->definition;
        }
    }

?>