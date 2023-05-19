<?php

    namespace Cryo\Framework;

    require_once('Cryo/Framework/Ext/Yaml.php');

    class YamlClassBuilder {

        private $filePath;
        private $builder;

        public function __construct($path){
            $this->filePath = $path;
            @mkdir("var/cache/cryo/yamlclasses/" , 0755 , true);
        }
        public function isCached(){
            $cacheName = sha1($this->filePath);

            $exists = file_exists("var/cache/cryo/yamlclasses/" . $cacheName . ".php");
            $isLatest = $exists && filemtime($this->filePath) > filemtime("var/cache/cryo/yamlclasses/" . $cacheName . ".php");

            return $isLatest;
        }
        public function import(){
            $cacheName = sha1($this->filePath);
            require_once("var/cache/cryo/yamlclasses/" . $cacheName . ".php");
            $meta = unserialize(file_get_contents("var/cache/cryo/yamlclasses/" . $cacheName . ".meta"));
            \Cryo\FrameworkUtils::registerClass($meta->getNamespace() . '\\' . $meta->getClassName() , $meta);
        }
        public function build(){
            $struct = spyc_load(file_get_contents($this->filePath));
            $cacheName = sha1($this->filePath);
            switch($struct['type']) {
                case "Entity":
                    $this->builder = new YamlClass\Entity($struct);
                    $script = $this->builder->build();

                    file_put_contents("var/cache/cryo/yamlclasses/" . $cacheName . ".php" , $script);
                    file_put_contents("var/cache/cryo/yamlclasses/" . $cacheName . ".meta" , serialize($this->builder->getDefinition()));
                break;
                case "Repository":
                    $this->builder = new YamlClass\Repository($struct);
                    $script = $this->builder->build();
                    file_put_contents("var/cache/cryo/yamlclasses/" . $cacheName . ".php" , $script);
                    file_put_contents("var/cache/cryo/yamlclasses/" . $cacheName . ".meta" , serialize($this->builder->getDefinition()));
                break;
                case "Controller":
                    $this->builder = new YamlClass\Controller($struct , $this->filePath);
                    $this->builder->build();
                break;
                default:
                    die("Unknown class type: " . $struct['type']);
            }
        }
    }

?>