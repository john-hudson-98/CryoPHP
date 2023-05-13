<?php

    namespace Cryo\Framework;

    class ScriptClassMethod {
        /** @param {String} $visibility - [public|protected|private] */
        private $visibility = 'public';

        /** @param {String} $name - the name of the method */
        private $name = '';

        /** @param {Array} $annotations - the list of annotations assigned to this method */
        private $annotations = [];

        /** @param {Array} $arguments - What arguments does this take, arguments can have annotations too */
        private $arguments = [];

        /** @param {String} $body - the body of the method. */
        private $body = '';

        /** @param {Boolean} $isAbstract - is the method abstract */
        private $isAbstract = false;

        /** @param {Boolean} $isStatic - is this method static */
        private $isStatic = false;

        /** @param {String} $returnType - what does this return? */
        private $returnType = 'mixed';

        /** @param {bool} $isInterface - does this method belong to an interface */
        private $isInterface = false;

        /**
         * @param {Annotation} $annotation - the annotation to add.
         * @return {ScriptClassMethod} $self - self reference
         */
        public function addAnnotation(Annotation $annotation) : ScriptClassMethod {
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
         * @return {String} $name - name of the method
         */
        public function getName() : string{
            return $this->name;
        }

        public function toSource(){

            $out = '';

            foreach($this->annotations as $annotation) {
                $out .= "\t\t" . $annotation->toCommentSpec() . "\n";
            }
            if ( $this->returnType !== 'mixed' ) {
                $out .= "\t\t/** @return {{$this->returnType}} **/\n";
            }
            $out .= "\t\t{$this->visibility} function {$this->name} (";

            foreach($this->arguments as $idx => $arg) {
                $out .= ($idx > 0 ? " , " : "") . $arg->toSource();
            }

            $out .= ") " . ($this->returnType !== 'mixed' ? ': ' . $this->returnType : '');
            
            if ( !$this->isAbstract && !$this->isInterface ) {
                $out .= " { \n";
            } else {
                $out .= ";\n";
            }
            
            foreach($this->arguments as $argument) {
                $doneAnnots = [];
                foreach($argument->getAnnotations() as $annotation){
                    if ( !in_array($annotation->getName() , $doneAnnots) ) {
                        $cname = str_replace('@' , '' , $annotation->getName());
                        $out .= "\t\t\t{$argument->getName()} = \Cryo\Framework\Annotation::apply('{$cname}' , '" . json_encode($annotation->getAttributes()) . "' , {$argument->getName()});\n";
                        $doneAnnots[] = $annotation->getName();
                    }
                }
            }
            if ( $this->isAbstract || $this->isInterface ) {
                
            } else {
                $out .= "\t\t\t{\n";
                
                $out .= str_replace("\n" , "\n\t\t\t" , $this->body);

                $out .= "\n\t\t\t}\n";
                $out .= "\n\t\t}\n";
            }
            return str_replace('* /' , '*/' , 
                    str_replace(
                        '/ * *' , 
                        '/**' , 
                        str_replace(
                            '/ /' ,
                             '//' , 
                             $out
                        )
                    )
            );
        }

        
        public static function fromTokens($tokens , $modifiers , $annotations){
            $inst = new ScriptClassMethod();

            $hitEndOfArguments = false;
            $openParenthesis = 0;
            $args = [];
            $currentArg = [];
            $lastToken = $tokens[count($tokens) - 1];
            $isInterface = false;
            if ( $lastToken == ';' ) {
                //is interface
                $isInterface = true;
            }
            for($i = 2;$i < count($tokens);$i++){
                $current = $tokens[$i];

                if ( $current == '(' ) {
                    if ( $openParenthesis > 0 ) {
                        $currentArg[] = $current;
                    }
                    $openParenthesis++;
                    continue;
                }
                if ( $current == ')' ) {
                    
                    if ( $openParenthesis > 1 ) {
                        $currentArg[] = $current;
                    }
                    $openParenthesis--;
                    
                    if ( !$hitEndOfArguments && $openParenthesis == 0 ) {
                        $hitEndOfArguments = true;
                        $i++;
                        break;
                    }
                    continue;
                }
                if ( $current == ',' ) {
                    $args[] = $currentArg;
                    $currentArg = [];
                    continue;
                }
                $currentArg[] = $current;
            }
            $args[] = $currentArg;
            
            foreach($args as $argument){
                $_argument = ScriptClassMethodArgument::fromTokens($argument);
                $inst->arguments[] = $_argument;
            }

            $inst->name = $tokens[1];

            foreach($modifiers as $mod){
                switch($mod){
                    case "public":
                    case "private":
                    case "protected":
                        $inst->visibility = $mod;
                    break;
                    case "static":
                        $inst->isStatic = true;
                    break;
                    case "abstract":
                        $inst->isAbstract = true;
                    break;
                }
            }
            $inst->annotations = $annotations;
            $inst->isInterface = $isInterface;
            if ( !$isInterface ) {
                $grab = self::extractBody($tokens);
                $inst->body = implode(" " , $grab['body']);
                $inst->returnType = $grab['type'];
            }

            return $inst;
        }
        private static function extractBody($tokens){
            $hitClosingParenthesis = false;
            $hitFirstBracket = false;
            $openParenthesis = 0;
            $openBrackets = 0;
            $startIndex = -1;
            $endIndex = count($tokens);
            $returnType = 'mixed';
            for($i = 0;$i < count($tokens);$i++){
                $curr = $tokens[$i];

                if ( $curr == ':' && !$hitFirstBracket ) {
                    $returnType = $tokens[$i + 1];
                    $i++;
                    continue;
                }
                if ( $curr == ')' ) {
                    $openParenthesis--;

                    if ( !$hitClosingParenthesis && $openParenthesis == 0 ) {
                        $hitClosingParenthesis = true;
                    }
                } else if ( $curr == '(' ) {
                    $openParenthesis++;
                } else if ( $curr == '{' ) {
                    if ( $hitClosingParenthesis && !$hitFirstBracket ) {
                        $hitFirstBracket = true;
                        $startIndex = $i + 1;
                    }
                    $openBrackets++;
                } else if ( $curr == '}' ) {
                    $openBrackets--;
                    if ( $hitFirstBracket && $openBrackets == 0 ) {
                        $endIndex = $i;
                        break;
                    }
                }
                
                
            }
            
            $body = [];
            for($i = $startIndex;$i < $endIndex;$i++){
                $body[] = $tokens[$i];
            }
            return array('body' => $body , 'type' => $returnType);
        }
    }

?>