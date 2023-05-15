<?php

    namespace Cryo\MvcClassExtenders;

    class Repository {
        private $interface = null;
        private $targetFile = "";
        public function __construct($targetClass){
            @mkdir("var/cache/cryo/repository" , 0777 , true);
            $this->interface = $targetClass;
            $this->targetFile = 'var/cache/cryo/repository/' . str_replace('\\' , '_' , $targetClass) . '.php';
        }
        public function exists() {
            return false; //until its built
            //return file_exists($this->targetFile);
        }
        public function import(){
            require_once($this->targetFile);
            $cname = $this->interface . "\\Definition";

            return new $cname();
        }
        public function buildRepositoryClass(){

            \Cryo\Boilerplate::autoloadClass($this->interface);

            $iface = \Cryo\FrameworkUtils::getClass($this->interface);
            
            $php = '<?php' . PHP_EOL . PHP_EOL;
            $php .= "\tnamespace " . $this->interface. ";\n\n";

            $php .= "\tclass Definition extends \\Cryo\\Framework\\Data\\BaseRepository implements \\{$this->interface} {\n";
            
            $php .= "\tpublic function install(){\n";
            
            if ( $iface->getAnnotation('@Repository')->hasValue('install') ) {
                $php .= "\t\t\$this->getDatabaseAdapter()->query(file_get_contents('src/" . $iface->getAnnotation('@Repository')->getCleanValue("install") . "'));\n";
            } else {
                throw new \Exception("RepositoryException - @Repository annotation missing install attribute");
            }

            $php .= "\t}\n";
            
            foreach($iface->getMethods() as $method){
                if ( $method->hasAnnotation('@Query') ) {
                    $php .= "\t\tpublic function {$method->getName()}(";

                    foreach($method->getArguments() as $idx => $argument) {
                        $php .= ($idx > 0 ? " , " : "") . $argument->toSource();
                    }
                    if ( $method->getReturnType() !== 'mixed' ) {
                        $php .= ") : {$method->getReturnType()} {\n";
                    } else {
                        $php .= ") : array {\n";
                    }

                    //method body here.

                    $php .= "\t\t\t\$db = \$this->getDatabaseAdapter();\n";
                    $php .= "\t\t\t\$res = \$db->query({$method->getAnnotation('@Query')->getValue('value')});\n";
                    
                    if ( $method->getReturnType() == 'int' ) {
                        //returns an int.

                        $php .= "\t\t\treturn isset(\$res[0]) ? array_values(\$res[0])[0] : 0;\n";
                    } else {

                        if ( $method->hasAnnotation('@ArrayItem') ) {
                            $targetItem = $method->getAnnotation('@ArrayItem')->getValue('class');

                            $php .= "\t\t\t\$out = [];\n";
                            $php .= "\t\t\tforeach(\$res as \$item){ \n";
                            $php .= "\t\t\t\t\$obj = new \\{$targetItem}();\n";
                            $php .= "\t\t\t\t\$obj->assign(\$item);\n";
                            $php .= "\t\t\t\t\$out[] = \$obj;\n";
                            $php .= "\t\t\t}\n";
                            $php .= "\t\t\treturn \$out;";
                        } else if ( $method->hasAnnotation('@Modifying') ) {
                            //means this is an update or statement
                        } else {
                            throw new \Exception("RepositoryException - custom method must have either @ArrayItem( class={MODEL_NAME} ) or @Modifying");
                        }
                    }

                    $php .= "\n\t\t}\n";
                }
            }

            $php .= "\t\tpublic function getTableName() : string { return " . $iface->getAnnotation('@Repository')->getValue('table') . "; }\n";
            $php .= "\n\t}";

            $php .= "\n\n?>";

            file_put_contents($this->targetFile , $php);

            require_once($this->targetFile);

            $cname = '\\' . $this->interface . "\\Definition";

            return new $cname();
        }
    }

?>