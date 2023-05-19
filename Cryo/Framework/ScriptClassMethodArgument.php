<?php

    namespace Cryo\Framework;

    /**
     * This holds meta information about an argument for a class method
     */

    class ScriptClassMethodArgument {

        /** @param {String} $argumentName - the name of the argument */
        private $argumentName = '';

        /** @param {String} $datatype - of what type is this argument */
        private $datatype = '';

        /** @param {Array} $annotations - a list of assigned annotations */
        private $annotations = [];

        /** @param {String} $defaultValue - whats the function default */
        private $defaultValue = '';

        public function toSource(){
            return "{$this->argumentName}" . ($this->defaultValue !== '' ? " = {$this->defaultValue}" : (count($this->annotations) > 0 ? " = null" : ""));
        }
        public function getName(){
            return $this->argumentName;
        }
        public function getType() : string{
            return $this->datatype;
        }

        /**
         * @param {Annotation} $annotation - the annotation to add.
         * @return {ScriptClassMethodArgument} $self - self reference
         */
        public function addAnnotation(Annotation $annotation) : ScriptClassMethodArgument {
            $this->annotations[] = $annotation;
            return $this;
        }
        /**
         * @param {Array} $annotations - list of annotations the current class has
         */
        public function getAnnotations() : array{
            return $this->annotations;
        }
        /**
         * @param {String} $name - the name of the annotation to check for
         * @return {bool} - does this class have that annotation
         */
        public function hasAnnotation(string $name) : bool {
            foreach($this->annotations as $annotation) {
                if ( $annotation->getName() == $name ) {
                    return true;
                }
            }
            return false;
        }
        /**
         * @param {String} $name - the name of the annotation to check for
         * @return {Annotation} - return class
         */
        public function getAnnotation(string $name) : ?Annotation {
            foreach($this->annotations as $annotation) {
                if ( $annotation->getName() == $name ) {
                    return $annotation;
                }
            }
            return null;
        }

        public static function fromTokens($tokens){
            // echo '=============<br>';
            // var_dump($tokens);
            // echo '=============<br>';#

            $inst = new ScriptClassMethodArgument();

            $annotations = [];
            $currAnnot = [];

            $startOfJustVar = -1;
            for($i = 0;$i < count($tokens);$i++) {
                $curr = $tokens[$i];
                
                if ( count($currAnnot) < 1 ) {
                    if ( $curr[0] == '@' ) {
                        $currAnnot[] = $curr;
                        continue;
                    }
                } else {
                    if ( $curr[0] == '@' ) {
                        //new annotation.
                        $annotations[] = $currAnnot;
                        $currAnnot = [$curr];
                    } else if ( $curr[0] == '(' ) {
                        // go to the end.
                        $end = self::getEndOfAnnotation($tokens , $i);
                        $range = self::range($tokens , $i - 1 , $end);
                        $annotations[] = $range;
                        $i = $end - 1;
                    } else {
                        $annotations[] = $currAnnot;
                        $currAnnot = [];
                    }
                }
                if ( stristr($curr , '\\') && $curr[0] !== '"' && $curr[0] == "'" ) {
                    $inst->datatype = $curr;
                    continue;
                }
                if ( @$tokens[$i + 1][0] == '$' ) {
                    if ( 
                        $curr == '(' || 
                        $curr == ')'
                    ) {
                        continue;
                    }
                    //chance of this being a type.
                    if ( $curr[0] !== '@' ) {
                        $inst->datatype = $curr;
                        continue;
                    }
                }
                if ( $curr[0] == '$' ) {
                    $startOfJustVar = $i;
                    $inst->argumentName = $curr;
                    break;
                }
            }
            foreach($annotations as $annot){
                $a = Annotation::fromTokens($annot);

                $inst->annotations[] = $a;
            }
            if ( $inst->argumentName == '' ) {
                foreach($tokens as $token){
                    if ( $token[0] == '$' ) {
                        $inst->argumentName = $token;
                    }
                }
            }
            for($i = $startOfJustVar;$i < count($tokens);$i++){
                if ( isset($tokens[$i + 1]) && $tokens[$i + 1] == '=' ) {
                    $inst->defaultValue = $tokens[$i + 2];
                }
            }
            return $inst;
        }
        private static function range($arr , $start , $end){
            $out = [];

            for($i = $start;$i < $end;$i++){
                $out[] = $arr[$i];
            }
            return $out;
        }
        private static function getEndOfAnnotation($tokens , $start){
            $openParenthesis = 0;
            for($i = $start;$i < count($tokens);$i++){
                if ( $tokens[$i] == '(' ) {
                    $openParenthesis++;
                } else if ( $tokens[$i] == ')' ) {
                    $openParenthesis--;
                    if ( $openParenthesis == 0 ) {
                        return $i + 1;
                    }
                }
            }
        }
    }

?>