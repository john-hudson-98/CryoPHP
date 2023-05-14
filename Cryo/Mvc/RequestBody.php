<?php

    namespace Cryo\Mvc;

    interface RequestBody{

    }

    \Cryo\Framework\Annotation::addModifier('RequestBody' , function($value , $attributes){
        if ( $_SERVER['CONTENT_TYPE'] == 'application/json' ) {
            return json_decode(file_get_contents("php://input") , true);
        }
        return $_POST;
    })

?>