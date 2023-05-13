<?php

    namespace Cryo\Mvc;

    interface GetParam {
        public function name();
    }

    \Cryo\Framework\Annotation::addModifier('GetParam' , function($value , $attributes){
        $name = substr($attributes['name'] , 1 , strlen($attributes['name']) - 2);
        return $_GET[$name];
    })

?>