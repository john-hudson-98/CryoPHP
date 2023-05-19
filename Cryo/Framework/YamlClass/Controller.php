<?php

    namespace Cryo\Framework\YamlClass;

    class Controller {
        private $filePath;
        private $def;

        public function __construct($definition , $filePath){
            $this->filePath = $filePath;
            $this->def = $definition;
        }
        public function build(){
            //TODO: Move this to build()
            //   &: Add Cache Mechanism

            $filePath = $this->filePath;
            $definition = $this->def;

            $className = str_replace(['.yaml' , '.yml'] , '' , basename($filePath));
            $namespace = str_replace('/' , '\\' , str_replace('src/' , 'App/' , str_replace(['.yaml' , '.yml' , '/' . $className] , '' , $filePath)));
            $classDefinition = new \Cryo\Framework\ScriptDefinition();
            $classDefinition->setNamespace($namespace);
            $classDefinition->setClassName($className);

            if ( @$definition['autowire'] ) {
                foreach($definition['autowire'] as $varName => $className){
                    $property = new \Cryo\Framework\ScriptClassProperty('$' . $varName);
                    $property->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@Autowired']));
                    $property->setType($className);
                    $classDefinition->addProperty($property);
                }
            }

            switch($definition['subType']) {
                case "MVCController":
                    $classDefinition->setExtends('\Cryo\Framework\YamlClass\Controller\AbstractYamlMvcController');
                    foreach($definition['routes'] as $methodName => $config){
                        $method = new \Cryo\Framework\ScriptClassMethod();
                        $method->rename($methodName);
                        $method->setVisibility('public');
                        $method->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@Route' , '(' , 'path' , '=' , '"' . $config['match']['path'] . '"' , ',' , 'allow' , '=' , '"' . implode("," , $config['match']['methods']) . '"' , ')']));
                        if ( !isset($config['blocks']) ) {
                            $method->setBody("\t\t\t\t\$this->getLayout()->setStructure('{$config['structure']}');\n\tinclude '{$config['handle']}';\n");
                        } else {
                            $body = "\t\t\t\t\$this->getLayout()->setStructure('{$config['structure']}');\n";
                            foreach($config['blocks'] as $blockId => $block) {
                                $body .= "\t\$this->getLayout()->getChild('{$blockId}')->setTemplate('{$block['template']}');\n";
                            }
                            $body .= "\tinclude '{$config['handle']}';\n";
                            $method->setBody($body);
                        }
                        $classDefinition->addMethod($method);
                        $theme = new \Cryo\Framework\ScriptClassMethod();
                        $theme->setVisibility('public');
                        $theme->rename('getTheme');
                        $theme->setReturnType('string');
                        $theme->setBody("\t\treturn '" . $definition['meta']['theme'] . "';\n");
                        $classDefinition->addMethod($theme);
                    }
                break;
                case "ReactApp":

                    $classDefinition->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@ReactApp' , '(' , 'app_name' , '=' , '"' . $definition['app']['name'] . '"' , ',' , 'local_url' , '=' , '"' . $definition['app']['local_url'] . '"' , ')']));
                    $classDefinition->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@ReactRoute' , '(' , 'match_type' , '=' , '"' . $definition['route']['match_type'] . '"' , ',' , 'value' , '=' , '"' . $definition['route']['path'] . '"' , ',' , 'mapsTo' , '=' , '"' . $definition['route']['mapsTo'] . '"' , ')']));
                   
                break;
                case "EndpointForward":
                    
                    if ( !$definition['endpoints'] ) {
                        throw new \Exception("No Endpoints Specified");
                    }

                    foreach($definition['endpoints'] as $methodName => $endpoint) {
                        $method = new \Cryo\Framework\ScriptClassMethod($methodName);
                        $method->rename($methodName);

                        $method->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@Route' , '(' , 'path' , '=' , '"' . $endpoint['route']['path'] . '"' , ',' , 'allow' , '=' , '"' . implode("," , $endpoint['route']['methods']) . '"' , ')']));
                        $method->setBody("\t\t\t\t\t\$req = new \Cryo\Microservice\ForwardEndpoint();\n\techo \$req->forwardRequest('{$endpoint['endpoint']['url']}' , '{$endpoint['endpoint']['remove']}');\n");
                        $classDefinition->addMethod($method);
                    }

                break;
            }

            


            $classDefinition->addAnnotation(\Cryo\Framework\Annotation::fromTokens(['@Controller']));

            $cacheName = sha1($filePath);

            file_put_contents("var/cache/cryo/yamlclasses/{$cacheName}.php" , '<?php' . PHP_EOL . PHP_EOL . $classDefinition->toSource() . PHP_EOL . PHP_EOL . '?>');
            file_put_contents("var/cache/cryo/yamlclasses/{$cacheName}.meta" , serialize($classDefinition));
        }
    }

?>