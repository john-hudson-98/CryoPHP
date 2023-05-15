<?php

    namespace Cryo\Mvc\Annotations;

    interface RequestBody{

    }

    \Cryo\Framework\Annotation::addModifier('RequestBody' , function($initialValue , $attributes , $argType){
        if ( $_SERVER['CONTENT_TYPE'] == 'application/json' ) {
            $_POST = json_decode(file_get_contents("php://input") , true);
        }
        $var = $_POST;

        if ( $attributes['postItem'] ) {
            $var = $_POST[str_replace('"' , '' , $attributes['postItem'])];
        }

        if ( stristr($argType , 'App\\') || stristr($argType , 'Plugin\\') ) {
            \Cryo\Boilerplate::autoloadClass($argType);
            $class = \Cryo\FrameworkUtils::getClass($argType);
            
            $inst = new $argType();

            $refl = new \ReflectionObject($inst);
            foreach($var as $key => $value){
                if ( property_exists($inst , $key) ) {
                    $property = new \ReflectionProperty($inst , $key);

                    if ( $property->isPrivate() || $property->isProtected() ) {
                        $property->setAccessible(true);
                        $property->setValue($inst , $value);
                        $property->setAccessible(false);
                    } else {
                        $inst->{$key} = $value;
                    }
                }
            }
            return $inst;
        }
        return $var;
    });

?>