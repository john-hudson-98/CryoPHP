<?php

    namespace Cryo\Framework;

    class ScriptClassProperty {
        /** @param {String} visibility */
        private $visibility = 'public';
        /** @param {String} $name - the name of the property */
        private $name = '';
        /** @param {Boolean} $hasSetter - Generate a setter in transformation */
        private $hasSetter = false;
        /** @param {Boolean} $hasGetter - Generate a getter in transformation */
        private $hasGetter = false;
        /** @param {String} $type - What is the type this property needs to have */
        private $type = null;
        /** @param {String} $value - A string representation of the value of the variable */
        private $value = "";
        /** @param {Boolean} $isStatic - is this a static variable */
        private $isStatic = false;
        /** @param {Array} $annotations - A list of annotations assigned to this property */
        private $annotations = [];
        

        public function __construct($name , $value = "null" , $visibility = 'public' , $hasGetter = false , $hasSetter = false , $type = "" , $static = false) {
            $this->name = $name;
            $this->value = $value;
            $this->hasGetter = $hasGetter;
            $this->hasSetter = $hasSetter;
            $this->visibility = $visibility;
            $this->type = $type;
            $this->isStatic = $static;
        }
        public function setType(string $type) : ScriptClassProperty {
            $this->type = $type;
            return $this;
        }
        public function getName(){
            return $this->name;
        }
        /**
         * @param {Annotation} $annotation - the annotation to add.
         * @return {ScriptClassProperty} $self - self reference
         */
        public function addAnnotation(Annotation $annotation) : ScriptClassProperty {
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
        public function getSetterAndSetterSource(){
            $out = '';
            if ( $this->hasSetter ) {
                $out .= "\n\t\tpublic function set" . ucfirst(substr($this->name , 1)) . "(";
                    if ( $this->type == 'mixed' ) {
                        $out .= $this->name;
                    } else {
                        $out .= '' . $this->type . ' ' . $this->name;
                    }
                $out .= "){\n";
                $out .= "\t\t\t\$this->" . substr($this->name , 1) . " = " . $this->name . ";\n";
                $out .= "\t\t\treturn \$this;\n";

                $out .= "\t\t}\n";
            }
            if ( $this->hasGetter ) {
                $out .= "\n\t\tpublic function get" . ucfirst(substr($this->name , 1)) . "() : {$this->type} {\n";
                $out .= "\t\t\treturn \$this->" . substr($this->name , 1) . ";\n";

                $out .= "\t\t}\n";
            }
            return $out;
        }

        public function toSource(){
            $source = "";
            
            foreach($this->annotations as $idx => $annotation) {
                $source .= ($idx > 0 ? "\n" : "") . $annotation->toCommentSpec();
            }
            if ( $this->hasSetter ) {
                $source .= "\n\t\t/** @Setter **/";
            }
            if ( $this->hasGetter ) {
                $source .= "\n\t\t/** @Getter **/";
            }
            $source .= "\n\t\t" . $this->visibility . ($this->isStatic ? ' static ' : '') . " " . $this->name . "";

            if ( $this->value !== '' ) {
                $source .= " = {$this->value}";
            }
            $source .= ";";
            return $source;
        }
        public function getType(){
            return $this->type;
        }



        public static function fromTransformer($name , $def , $modifiers , $annotations , $type = 'mixed'){
            
            $visibility = 'public';
            $isStatic = false;
            $value = "";
            $hasGetter = false;
            $hasSetter = false;
            
            foreach($modifiers as $mod){
                switch($mod){
                    case "public":
                    case "private":
                    case "protected":
                        $visibility = $mod;
                    break;
                    case "static":
                        $isStatic = true;
                    break;
                }
            }
            $containsMeta = false;
            $reachedEndOfMeta = false;
            foreach($def as $idx => $token){
                if ( $token == '{' ) {
                    $containsMeta = true;
                    continue;
                }
                if ( $token == '}' && $containsMeta ) {
                    $reachedEndOfMeta = true;
                    continue;
                }
                if ( $containsMeta && !$reachedEndOfMeta ) {
                    if ( $token == 'get' ) {
                        $hasGetter = true;
                        continue;
                    } 
                    if ( $token == 'set' ) {
                        $hasSetter = true;
                        continue;
                    }
                }
                if ( $token == '=' ) {
                    for($i = $idx + 1;$i < count($def);$i++) {
                        if ( strlen($def[$i]) < 2 ) {
                            $value .= $def[$i];
                        } else {
                            $value .= " {$def[$i]}";
                        }
                    }
                }
            }
            
            $prop = new ScriptClassProperty($name , trim($value) , $visibility , $hasGetter , $hasSetter , $type , $isStatic);
            foreach($annotations as $annotation){
                $prop->annotations[] = $annotation;
            }
            return $prop;
        }
    }

?>