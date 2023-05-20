<?php

    namespace Cryo\Core\Transform;

    class ClassBuilder {
        private $namespace = '';
        private $className = '';
        private $extends = null;
        private $implements = null;
        private $methods = [];

        public function setNamespace(string $namespace) : ClassBuilder {
            $this->namespace = $namespace;
            return $this;
        }
        public function getNamespace() : string{
            return $this->namespace;
        }
        
        public function setName(string $name) : ClassBuilder {
            $this->className = $name;
            return $this;
        }
        public function getName() : string{
            return $this->className;
        }

        public function setExtends(string $extends) : ClassBuilder {
            $this->extends = $extends;
            return $this;
        }
        public function getExtends() : ?string{
            return $this->extends;
        }

        public function setImplements(string $implements) : ClassBuilder {
            $this->implements = $implements;
            return $this;
        }
        public function getImplements() : ?string{
            return $this->implements;
        }

        public function addMethod($methodName , $arguments = [] , $body , $returnType = null , $isStatic = false) {
            $this->methods[$methodName] = array(
                'body' => $body , 
                'type' => $returnType , 
                'static' => $isStatic , 
                'arguments' => $arguments
            );
            return $this;
        }
        
        public function toSource(){
            $out = '<?php' . PHP_EOL . PHP_EOL;

            $out .= "\tnamespace {$this->namespace};\n\n";
            $out .= "\tclass {$this->className} " . ($this->extends ? "extends {$this->extends} " : "") . ($this->implements ? "implements {$this->implements} " : "") . "{\n\n";
            foreach($this->methods as $name => $body) {
                $out .= "\t\tpublic " . ($body['static'] ? 'static ' : '') . "function {$name}(";

                if ( is_array($body['arguments']) ) {
                    foreach($body['arguments'] as $idx => $argument){
                        $out .= ($idx > 0 ? " , " : "") . $argument;
                    }
                }
                
                $out .= ") " . (isset($body['type']) ? " : {$body['type']}" : "") . " { \n";

                $out .= "\t" . $body['body'];

                $out .= "\t\t}\n\n";
            }
            $out .= "\n\t}\n";
            $out .= PHP_EOL . PHP_EOL . '?>';
            return $out;
        }
    }

?>