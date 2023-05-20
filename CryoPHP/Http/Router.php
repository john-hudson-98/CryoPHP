<?php

    namespace Cryo\Http;

    class Router {

        public function route($url){
            $all = $this->getYamlFiles('src');
            
            $loader = new \Cryo\YAC\Loader();
            
            foreach($all as $definition) {
                $loader->load($definition);
            }
        }
        private function getYamlFiles(string $dir) : array{
            $out = [];
            foreach(glob($dir . '/*') as $yaml){
                if ( is_dir($yaml) ) {
                    $out = array_merge($out , $this->getYamlFiles($yaml));
                } else {
                    if ( stristr($yaml , '.yaml') || stristr($yaml , '.yml') ) {
                        $out[] = $yaml;
                    }
                }
            }
            return $out;
        }
    }

?>