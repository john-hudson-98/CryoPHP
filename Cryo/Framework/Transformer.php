<?php

    namespace Cryo\Framework;

    class Transformer {
        
        /**
         * @description - this class takes the array of tokens from the tokenizer 
         *              - and builds up a decision tree
         */
        
        private $tokens = [];

        public function __construct(Tokenizer $tokenizer){
            foreach($tokenizer->getTokens() as $token){
                if ( $token == ' ' ) {
                    continue;
                } 

                $this->tokens[] = $token;
            }
        }
        public function transform(){
            $script = new ScriptDefinition();

            $hitClassDefinition = false; // true when class token is found.
            $currentAnnotations = [];

            $currentModifiers = [];
            $currentType = 'mixed';

            for($i = 0;$i < count($this->tokens);$i++){
                $current = $this->tokens[$i];

                if ( $current == 'namespace' ) {
                    $script->setNamespace($this->tokens[$i + 1]);
                    $i++;
                    continue;
                }
                if ( $current == 'class' ) {
                    $hitClassDefinition = true;
                    $script->setClassType('class');
                    $script->setClassName($this->tokens[$i + 1]);
                    $i++;
                    continue;
                }
                if ( $current == 'interface' ) {
                    $hitClassDefinition = true;
                    $script->setClassType('interface');
                    $script->setClassName($this->tokens[$i + 1]);
                    $i++;
                    continue;
                }
                if ( $current == 'extends' ) {
                    if ( $script->getClassType() == 'interface' ) {
                        $implements = $this->getImplementations($i + 1);
                        $implementations = $implements['implementations'];
                        $script->setImplements($implementations);
                        $i += $implements['jump'];
                        continue;
                    }
                    $script->setExtends($this->tokens[$i + 1]);
                    $i++;
                }
                if ( $current == 'implements' ) {

                    if ( $script->getClassType() == 'interface' ) {
                        throw new \Exception("Interface cannot implement another interface");
                    }
                    $implements = $this->getImplementations($i + 1);
                    $implementations = $implements['implementations'];
                    $script->setImplements($implementations);
                    $i += $implements['jump'];
                    continue;
                }
                if ( $current[0] == '@' ) {
                    //is annotation.
                    $annotation = $this->getAnnotation($i);
                    $instance = Annotation::fromTokens($annotation);

                    if ( !$hitClassDefinition ) {
                        $script->addAnnotation($instance);
                        $i += count($annotation);
                        continue;
                    }
                    //add to a list of annotations, and the next declared member will take ownership
                    $currentAnnotations[] = $instance;
                    $i += count($annotation);
                    continue;
                }
                if ( in_array($current , ['private' , 'public' , 'protected' , 'static' , 'abstract']) ) {
                    $currentModifiers[] = $current;
                    continue;
                }
                if ( count($currentModifiers) > 0 ) {
                    if ( $current[0] !== '$' ) {
                        $currentType = $current;
                    }
                }
                if ( $current[0] == '$' ) {
                    //is a property.
                    $property = $this->grabProperty($i);
                    $prop = ScriptClassProperty::fromTransformer($current , $property , $currentModifiers , $currentAnnotations , $currentType);
                    $currentType = 'mixed';
                    $currentModifiers = [];
                    $currentAnnotations = [];

                    $script->addProperty($prop);

                    $i += count($property);
                    continue;
                }
                if ( $current == 'function' ) {
                    $method = $this->grabFunction($i);
                    $methodInstance = ScriptClassMethod::fromTokens($method , $currentModifiers , $currentAnnotations);
                    $currentModifiers = [];
                    $currentAnnotations = [];
                    
                    $script->addMethod($methodInstance);

                    $i += count($method);
                    continue;
                }
            }
            return $script;
        }
        private function getLastOccurenceOf(int $startPos , array $breakOn){
            for($i = $startPos;$i > 0;$i--){
                if ( in_array($this->tokens[$i] , $breakOn) ) {
                    return $i - 1;
                }
            }
        }
        private function grabFunction($startPos){
            $hitFirstClosingBracket = false;
            $openBrackets = 0;

            for($i = $startPos;$i < count($this->tokens);$i++){
                $curr = $this->tokens[$i];

                if ( $curr == ')' ) {
                    if ( !$hitFirstClosingBracket ) {
                        $hitFirstClosingBracket = true;
                        continue;
                    }
                }
                if ( $hitFirstClosingBracket && $curr == ';' && $openBrackets == 0 ) {
                    $i++;
                    break;
                }
                if ( $curr == '{' ) {
                    $openBrackets++;
                    continue;
                }
                if ( $curr == '}' ) {
                    $openBrackets--;
                    if ( $hitFirstClosingBracket && $openBrackets == 0 ) {
                        $i++;
                        break;
                    }
                    continue;
                }
            }
            return $this->getRange($startPos, $i);
        }
        private function grabProperty($start){
            $property = [];
            $openBrackets = 0;
            for($i = $start;$i < count($this->tokens);$i++){
                $current = $this->tokens[$i];

                if ( $current == '{' ) {
                    $openBrackets++;
                    $property[] = $current;
                    continue;
                } 
                if ( $current == '}' ) {
                    $openBrackets--;
                    $property[] = $current;
                    continue;
                }
                if ( ($current == ';' || $current == ',') && $openBrackets == 0 ) {
                    $property[] = $current;
                    return $property;
                }
                $property[] = $current;
                
            }
            return null;
        }
        private function getAnnotation($start){

            if ( $this->getNextChar($start + 1) !== '(' ) {
                return [$this->tokens[$start]];
            }
            $body = [];
            $openParenthesis = 0;
            for($i = $start;$i < count($this->tokens);$i++){
                $token = $this->tokens[$i];

                if ( $token == '(' ) {
                    $openParenthesis++;
                    $body[] = $token;
                    continue;
                }
                if ( $token == ')' ) {
                    $openParenthesis--;
                    $body[] = $token;
                    if ( $openParenthesis == 0 ) {
                        return $body;
                    }
                    continue;
                }
                $body[] = $token;
            }
        }
        private function getNextChar($startPosition){
            for($i = $startPosition;$i < count($this->tokens);$i++){
                $token = $this->tokens[$i];

                if ( $token == ' ' ) {
                    continue;
                }
                return $token;
            }
        }
        private function getImplementations($startPosition) {
            $range = $this->getRange($startPosition , $this->getNext($startPosition , '{'));

            $implementations = [];

            foreach($range as $token){
                if ( $token == ',' ) {
                    continue;
                }
                if ( $token == ' ' ) {
                    continue;
                }
                
                $implementations[] = $token;
            }
            return array(
                'implementations' => $implementations , 
                'jump' => count($range)
            );
        }
        private function getRange($start , $end){
            $out = [];
            for($i = $start;$i < $end;$i++){
                $out[] = $this->tokens[$i];
            }
            return $out;
        }
        private function getNext($pos , $matchCase){
            for($i = $pos;$i < count($this->tokens);$i++){
                if ( $this->tokens[$i] == $matchCase ) {
                    return $i;
                }
            }
            return $i;
        }
    }

?>