<?php

    namespace Cryo\Parsers;

    class DotEnv {
        private $data = [];
        public function load($path){
            $lines = explode("\n" , file_get_contents("src/" . $path));
            foreach($lines as $line){
                $this->data[explode("=" , $line)[0]] = str_replace('"' , '' , explode("=" , $line)[1]);
            }
        }
        public function get($key){
            return @$this->data[$key];
        }
    }

?>