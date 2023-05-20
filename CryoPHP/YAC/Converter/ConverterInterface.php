<?php

    namespace Cryo\YAC\Converter;

    interface ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder;
    }

?>