<?php

    namespace Cryo\Framework;

    /**
     * Another feature developed early in the system, 
     * this takes the Annotations interface and extends it 
     * based on values passed.
     */

    class AnnotationClassBuilder {
        public static function fromInterface(string $className , Annotation $annot){
            $php = '<?php ' . PHP_EOL . PHP_EOL;
            $tmpfile = sha1(serialize($annot));


            $ifaceName = 'Annot' . $tmpfile;
            $exportname = $className . '\\' . $ifaceName;

            
            if ( file_exists("var/cache/cryo/objects/" . $tmpfile . ".php") ) {
                require_once("var/cache/cryo/objects/{$tmpfile}.php");
                $inst = new $exportname(); 
                return $inst;
            }

            $php .= "\tnamespace " . substr($className , 1) . ";\n\n";

            $php .= "\tclass {$ifaceName} implements {$className} {\n";

            $targetInterface = new \ReflectionClass($className);

            $methods = $targetInterface->getMethods();

            foreach($methods as $name){
                $name = $name->name;
                $php .= "\t\tpublic function {$name}(){ return " . ($annot->getValue($name) ? $annot->getValue($name) : 'null') . "; }\n";
            }
            
            $php .= "\n\t}\n";

            $php .= PHP_EOL . '?>';
            
            file_put_contents("var/cache/cryo/objects/" . $tmpfile . ".php" , $php);
            require_once("var/cache/cryo/objects/" . $tmpfile . ".php");
            

            $inst = new $exportname();
            
            return $inst;
        } 
    }

?>