<?php

    namespace Cryo\YAC\Validator;

    interface ValidatorInterface {
        public function validate(array $definiton) : bool;
        public function getDefinition() : array;
    }

?>