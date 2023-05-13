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
            $iface = \Cryo\FrameworkUtils::getClass(substr($this->interface , 1));
            
            $php = '<?php' . PHP_EOL . PHP_EOL;
            $php .= "\tnamespace " . substr($this->interface , 1) . ";\n\n";

            $php .= "\tclass Definition extends \\Cryo\\Framework\\Data\\BaseRepository implements {$this->interface} {\n";

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
                        

                    if ( $method->hasAnnotation('@ArrayItem') ) {
                        $targetItem = $method->getAnnotation('@ArrayItem')->getValue('class');

                        $php .= "\t\t\t\$out = [];\n";
                        $php .= "\t\t\tforeach(\$res as \$item){ \n";
                        $php .= "\t\t\t\t\$obj = new \\{$targetItem}();\n";
                        $php .= "\t\t\t\t\$obj->assign(\$item);\n";
                        $php .= "\t\t\t\t\$out[] = \$obj;\n";
                        $php .= "\t\t\t}\n";
                        $php .= "\t\t\treturn \$out;";
                    }

                    $php .= "\n\t\t}\n";
                }
            }

            $php .= "\t\tpublic function getTableName() : string { return " . $iface->getAnnotation('@Repository')->getValue('table') . "; }\n";
            $php .= "\n\t}";

            $php .= "\n\n?>";

            file_put_contents($this->targetFile , $php);

            require_once($this->targetFile);

            $cname = $this->interface . "\\Definition";

            return new $cname();
        }
    }

?>