<?php

    namespace Cryo\YAC;

    class Loader {

        public function load($yamlFile){
            $validator = new Validator();
            $validator->validate(file_get_contents($yamlFile));
            $definition = $validator->getDefinition();
            //an exception would be thrown if YAML is not valid YAC
            $converterClass = '\\Cryo\\YAC\\Converter\\Controller\\' . $definition['subType'];
            $fullClassName = str_replace('/' , '\\' , str_replace('src/' , 'App\\' , str_replace(['.yaml' , '.yml'] , '' , $yamlFile)));
            $className = basename($fullClassName);
            $namespace = str_replace('\\' . $className , '' , $fullClassName);
            
            $converter = new $converterClass();
            $builder = $converter->convert($definition);
            $builder->setNamespace($namespace);
            $builder->setName($className);
            
            $cache = new \Cryo\Core\CacheManager();

            $cache->saveCache($yamlFile , $builder->toSource() , 'Controller');

            die();
        }

    }

?>