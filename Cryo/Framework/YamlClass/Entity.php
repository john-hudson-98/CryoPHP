<?php

    namespace Cryo\Framework\YamlClass;

    /**
     * This takes an Entity YAML file and converts it to PHP
     * There is nothing fancy about these classes, except a
     * series of setters and getters and private properties
     * that have assigned columns.
     */

    class Entity {
        /**
         * @note - this needs to create a class in a similar
         *       - way the autoloader does, I need a .meta class and a code class
        */
        private $namespace = '';
        private $className = '';

        private $private = [];
        private $protected = [];
        private $public = [];

        private $classDefinition;

        public function __construct($definition){
            
            $this->namespace = $definition['namespace'];
            $this->className = $definition['class'];

            $def = new \Cryo\Framework\ScriptDefinition();
            $def->setNamespace($definition['namespace']);
            $def->setClassType('class');
            $def->setClassName($this->className);
            $def->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@Entity']));
            

            $this->classDefinition = $def;

            foreach(['private', 'public' , 'protected'] as $v){
                foreach((@$definition['properties'][$v] ?? []) as $i => $idx) {
                    $prop = $definition['properties'][$v][$i];

                    $label = array_keys($prop)[0];

                    $property = new \Cryo\Framework\ScriptClassProperty($label);
                    if ( $definition['meta']['primaryKey'] == $label ) {
                        $property->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@Id']));    
                    }
                    $property->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@Column' , '(' ,  'name' , '=' , '"' . $prop[$label]['column'] . '"' , ')']));
                    $def->addProperty($property);
                    $this->{$v}[$label] = $prop[$label];
                }
            }
            
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

            $out .= "\tclass {$this->className} {\n\n";

            foreach($this->private as $field => $private){
                $out .= "\t\tprivate \${$field};\n";
            }
            foreach($this->public as $field => $private){
                $out .= "\t\tpublic \${$field};\n";
            }
            foreach($this->protected as $field => $private){
                $out .= "\t\tprotected \${$field};\n";
            }
            

            $out .= "\n";
            foreach($this->private as $field => $private){
                if ( in_array('set' , $private['def']) ) {
                    $out .= "\t\tpublic function set" . ucfirst($field) . "({$private['type']} \$val) : {$this->className} { \$this->{$field} = \$val; return \$this; }\n";
                }
                if ( in_array('get' , $private['def']) ) {
                    $out .= "\t\tpublic function get" . ucfirst($field) . "() : ?{$private['type']} { return \$this->{$field}; }\n";
                }
            }
            foreach($this->protected as $field => $private){
                if ( in_array('set' , $private['def']) ) {
                    $out .= "\t\tpublic function set" . ucfirst($field) . "({$private['type']} \$val) : {$this->className} { \$this->{$field} = \$val; return \$this; }\n";
                }
                if ( in_array('get' , $private['def']) ) {
                    $out .= "\t\tpublic function get" . ucfirst($field) . "() : ?{$private['type']} { return \$this->{$field}; }\n";
                }
            }
            foreach($this->public as $field => $private){
                if ( in_array('set' , $private['def']) ) {
                    $out .= "\t\tpublic function set" . ucfirst($field) . "({$private['type']} \$val) : {$this->className} { \$this->{$field} = \$val; return \$this; }\n";
                }
                if ( in_array('get' , $private['def']) ) {
                    $out .= "\t\tpublic function get" . ucfirst($field) . "() : ?{$private['type']} { return \$this->{$field}; }\n";
                }
            }

            $out .= "\n\t}\n";

            $out .= PHP_EOL . '?>';

            return $out;
        }
    }

?>