<?php

    namespace Cryo\Framework;

    /**
     * Annotations are a great way to add descriptions to code, they are also referred to as decorators
     * In PHP natively these aren't available. However I've added functionality to allow you to read the
     * annotations values, you can access these by getting the current property, method or class.
     */

    class Annotation {
        private $name = '';
        private $attributes = [];
        public static function fromTokens(array $tokens){
            $annot = new Annotation();
            $annot->name = $tokens[0];

            if ( count($tokens) > 1 ) {
                // iterate parts
                $currentKey = null;
                for($i = 2;$i < count($tokens) - 1;$i++) {
                    if ( is_null($currentKey) ) {
                        $currentKey = $tokens[$i];
                        continue;
                    } else {
                        $annot->attributes[$currentKey] = $tokens[$i + 1];
                        $currentKey = null;
                    }
                    
                    $i += 2;
                }
            }
            return $annot;
        }
        public function getName(){
            return $this->name;
        }
        public function getAttributes(){
            return $this->attributes;
        }
        public function getValue($key){
            return @$this->attributes[$key];
        }
        public function hasValue($key){
            return isset($this->attributes[$key]);
        }
        public function getCleanValue($key){
            return str_replace('"' , '' , $this->attributes[$key]);
        }
        public function toCommentSpec(){
            return "/** {$this->name}(" . str_replace('*/' , '*\/' , implode(" , " , $this->attributes)) . ") **/";
        }
        /**
         * This applies an annotation to a variable
         * Some annotations are purely lexical and provide instructions
         * to the interpreter, others have actual functionality and provide real purpose 
         * to your code. 
         */
        public static function apply($annotationName , $attributes , $initialValue , $type = ''){
            if ( @self::$Modifiers[$annotationName] ) {
                $initialValue = self::$Modifiers[$annotationName]($initialValue , json_decode($attributes , true) , $type);
                return $initialValue;
            }
            return $initialValue;
        }
        private static $Modifiers = [];

        public static function addModifier($modifier , \Closure $callable){
            self::$Modifiers[$modifier] = $callable;
        }
    }

?>