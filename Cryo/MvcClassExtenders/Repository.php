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

            $php .= "\tclass Definition implements {$this->interface} {\n";

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

                    $php .= "\n\t\t}\n";
                }
            }

            $php .= "\n\t}";

            $php .= "\n\n?>";

            file_put_contents($this->targetFile , $php);

            require_once($this->targetFile);

            $cname = $this->interface . "\\Definition";

            return new $cname();
        }
    }

?>