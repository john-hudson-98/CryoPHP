<?php

    namespace Cryo\Framework\Formatting;

    class PHPrettify {

        public function prettify(string $source) : string{
            $lines = explode("\n" , $source);
            $out = [];
            foreach($lines as $idx => $line) {
                if ( trim($line) == '' || trim($line) == ' ' ) {
                    continue;
                }
                $out[] = $this->performModifications($line , @$lines[$idx - 1] , substr_count($lines[$idx - 1] , "\t"));
            }
            return "\n" . $this->fixDoubleColons($this->fixArrows(implode("\n" , $out))) . "\n";
        }
        private function performModifications($line , $previousLine , $previousLineTabs = 0) {
            if ( 
                substr(trim($previousLine) , -1) == '{'  ||
                substr(trim($previousLine) , -1) == ':'
            ) {
                if ( substr(trim($previousLine) , -1) == ':' ) {
                    return str_repeat("\t" , substr_count($previousLine , "\t") + 2) . trim($line);    
                }
                return str_repeat("\t" , substr_count($previousLine , "\t") + 1) . trim($line);
            } else {
                return $line;
            }
        }
        private function fixArrows($str){
            //this is bad, but for now it works. Improve later
            $arrArrow = str_replace("= >" , "=>" , $str);
            $arrCall = str_replace('- >' , '->' , $arrArrow);
            return $arrCall;
        }
        private function fixDoubleColons($code){
            return str_replace(';;' , ';' , $code);
        }
    }

?>