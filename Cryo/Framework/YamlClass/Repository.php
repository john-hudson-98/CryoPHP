<?php

    namespace Cryo\Framework\YamlClass;

    class Repository {
        /**
         * @note - this needs to create a class in a similar
         *       - way the autoloader does, I need a .meta class and a code class
        */
        private $namespace = '';
        private $className = '';

        private $classDefinition;

        public function __construct($definition){
            
            $this->namespace = $definition['namespace'];
            $this->className = $definition['class'];

            $def = new \Cryo\Framework\ScriptDefinition();
            $def->setNamespace($definition['namespace']);
            $def->setClassType('interface');
            $def->setClassName($this->className);
            $def->addAnnotation(\Cryo\Framework\Annotation::fromTokens(
                [
                    '@Repository' , '(' , 
                    'table' , '=' , '' . $definition['meta']['table'] . ''  , ',' ,  
                    'install' , '=' , '' . $definition['meta']['install'] . '' , ',' , 
                    'entity' , '=' , '' . $definition['meta']['entity'] . '' ,
                    ')'
                ]
            ));            

            $this->classDefinition = $def;

            foreach($definition['custom'] as $methodName => $method) {
                $annotations = [];

                if ( $method['query'] ) {
                    $annotations[] = \Cryo\Framework\Annotation::fromTokens([
                        '@Query' , 
                        '(' , 
                        'value' , 
                        '=' , 
                        '"' . $method['query'] . '"' , 
                        ')'
                    ]);
                }

                $method = \Cryo\Framework\ScriptClassMethod::fromTokens([
                    'function' , 
                    $methodName , 
                    '(' , 
                    ')' , 
                    ':' , 
                    $method['return'] ,
                    ' ; '
                ] , ['public'] , $annotations);
                $method->setIsInterface(true);
                $this->classDefinition->addMethod($method);
            }
            \Cryo\FrameworkUtils::registerClass($this->getFullName() , $this->getDefinition());
        }
        public function getFullName(){
            return $this->namespace . '\\' . $this->className;
        }
        public function getDefinition(){
            return $this->classDefinition;
        }
        public function build(){
            $out = '<?php' . PHP_EOL . PHP_EOL;

            $out .= "\tnamespace {$this->namespace};\n\n";

            $out .= "\tinterface {$this->className} {\n\n";

            foreach($this->classDefinition->getMethods() as $method){
                $out .= "\t\t{$method->toSource()}\n";
            }
            
            $out .= "\n\t}\n";

            $out .= PHP_EOL . '?>';

            return $out;
        }
    }

?>