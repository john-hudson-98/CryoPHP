<?php

    namespace Cryo\Framework;

    class ScriptDefinition {

        /** @param {String} $namespace - the namespace this class resides in */
        private $namespace = '';

        /** @param {String} $type - [class,interface] */
        private $type = 'class';
        
        /** @param {String} $className - name of the class */
        private $className = '';
        
        /** @param {String} $extends - the class it extends (if any)  */
        private $extends = '';
        
        /** @param {Array} $implements - a list of interfaces this class implements */
        private $implements = [];

        /** @param {Array} $annotations - a list of annotations the class has */
        private $annotations = [];

        /** @param {Array} $methods - the methods the class has */
        private $methods = [];

        /** @param {Array} $properties - the list of properties the class has */
        private $properties = [];

        /**
         * @param {String} $type - is it a class or interface?
         * @return { ScriptDefinition } - self - for chaining
         */
        public function setClassType(string $type) : ScriptDefinition {
            $this->type = $type;
            return $this;
        }
        /**
         * @return {String} $type - the type of object
         */
        public function getClassType() : string {
            return $this->type;
        }
        /**
         * @param {String} $ns - Sets the classes namespace
         * @return {ScriptDefinition} - self - for chaining
         */
        public function setNamespace(string $ns) : ScriptDefinition {
            $this->namespace = $ns;
            return $this;
        }
        /**
         * @return {String} namespace - the namespace of the class
         */
        public function getNamespace() : string{
            return $this->namespace;
        }

        /**
         * @param {String} $cname - sets the name of the class
         * @return {ScriptDefinition} - self - for chaining
         */
        public function setClassName(string $cname) : ScriptDefinition {
            $this->className = $cname;
            return $this;
        }
        /**
         * @return {String} $className - the name of the class 
         */
        public function getClassName() : string{
            return $this->className;
        }
        /**
         * @param {String} $extends - the class this extends
         * @return {ScriptDefinition} - self - for chaining
         */
        public function setExtends(string $extends) : ScriptDefinition {
            $this->extends = $extends;
            return $this;
        }
        /**
         * @return {String} $extends - the class this one extends
         */
        public function getExtends() : string{
            return $this->extends;
        }

        /**
         * @param {Array} $implements - a list of interfaces the class implements
         * @return {ScriptDefinition} - self - for chaining
         */
        public function setImplements(array $implements) : ScriptDefinition {
            $this->implements = $implements;
            return $this;
        }
        /**
         * @return {Array} $implements - gets the interfaces the class extends
         */
        public function getImplements() : array{
            return $this->implements;
        }
        /**
         * @param {Annotation} $annotation - the annotation to add.
         * @return {ScriptDefinition} $self - self reference
         */
        public function addAnnotation(Annotation $annotation) : ScriptDefinition {
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
        /**
         * @param {ScriptClassProperty} $property - add a property to the current script
         * @return {ScriptDefinition} $self - for chaining
         */
        public function addProperty(ScriptClassProperty $property) : ScriptDefinition {
            $this->properties[] = $property;
            return $this;
        }
        /**
         * @return {Array} $properties - a list of properties belonging to this class
         */
        public function getProperties() : array {
            return $this->properties;
        }
        /**
         * @param {ScriptClassMethod} $method - the method to add
         * @return {ScriptDefinition} $self - for chaining
         */
        public function addMethod(ScriptClassMethod $method) : ScriptDefinition {
            $this->methods[] = $method;
            return $this;
        }
        /**
         * @return {Array} $methods - get a list of methods
         */
        public function getMethods() : array {
            return $this->methods;
        }
        public function toSource(){
            $out = '' . PHP_EOL . PHP_EOL . "\tnamespace {$this->getNamespace()};\n\n";

            $out .= "\t{$this->getClassType()} {$this->getClassName()} ";

            if ( $this->getClassType() == 'interface' ) {
                $out .= 'extends ';
                foreach($this->getImplements() as $idx => $implement) {
                    $out .= ($idx > 0 ? " , " : "") . "{$implement} ";
                }
            } else {
                if ( $this->getExtends() !== '' ) {
                    $out .= "extends {$this->getExtends()} ";
                }
                if ( count($this->getImplements()) > 0 ) {
                    $out .= "implements " . implode(" , " , $this->getImplements());
                }
            }
            $out .= "{\n";

            foreach($this->getProperties() as $property) {
                $out .= "\n";
                $out .= "\t\t/** @param { \\{$property->getType()} } **/\n";
                $out .= "\t\t{$property->toSource()}\n";
                
                $out .= "\n";
            }

            foreach($this->getMethods() as $method){
                $out .= $method->toSource();
            }
            

            // add setters and getters
            foreach($this->getProperties() as $property){
                $out .= $property->getSetterAndSetterSource();
            }

            $out .= "\n\t}\n";

            $out .=  PHP_EOL . '';
            return $out;
        }
    }

?>