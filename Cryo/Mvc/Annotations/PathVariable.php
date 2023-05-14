<?php

    namespace Cryo\Mvc\Annotations;

    interface PathVariable {
        public function name();
    }

    \Cryo\Framework\Annotation::addModifier('PathVariable' , function($initialValue , $attributes){
        $name = str_replace('"' , '' , @$attributes['name']);

        return $_SERVER['PATH_VARIABLES'][$name];
    });

?>