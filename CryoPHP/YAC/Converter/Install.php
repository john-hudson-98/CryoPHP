<?php

    namespace Cryo\YAC\Converter;

    class Install implements ConverterInterface {
        public function convert(array $definition) : \Cryo\Core\Transform\ClassBuilder{
            
            $classBuilder = new \Cryo\Core\Transform\ClassBuilder();
            $classBuilder->setExtends('\\Cryo\\Framework\\Installer');

            $classBuilder->addMethod('getDbAdapter' , [] , "\t\treturn \\{$definition['Adapter']}::Get(); \n" , '\\Cryo\DataLayer\\DatabaseAdapter' , false);

            foreach($definition['schema'] as $id => $table){

                $methodBody = "\t\t\$this->getDbAdapter()->query(\"CREATE TABLE IF NOT EXISTS `{$table['table']}` (";

                $i = 0;
                $primary = '';
                foreach($table['fields'] as $fieldName => $def) {
                    $methodBody .= ($i > 0 ? " , " : "") . "\n\t\t\t\t{$this->getColumnSql($fieldName , $def)}";
                    if ( @$def['primary'] ) {
                        $primary = $fieldName;
                    }
                    $i++;
                }
                if ( $primary ) {
                    $methodBody .= ",\n\t\t\t\tPRIMARY KEY(`{$primary}`) ";
                }

                if ( @$table['foreign-keys'] ) {
                    foreach($table['foreign-keys'] as $fkId => $fk){
                        $methodBody .= ",\n\t\t\t\tFOREIGN KEY `fk_" . str_replace('-' , '_' , $id) . "_{$fkId}_ref_" . explode("(" , $fk['references'])[0] . "` (`{$fkId}`)";
                        $methodBody .= " REFERENCES {$fk['references']} ";
                        if ( @$fk['delete'] ) {
                            $methodBody .= "ON DELETE {$fk['delete']} ";
                        }
                        if ( @$fk['update'] ) {
                            $methodBody .= "ON UPDATE " . $fk['update'];
                        }
                    }
                }

                $methodBody .= "\n\t\t\t)\");\n";

                $classBuilder->addMethod('install_' . $table['table'] , [] , $methodBody , 'void' , false);
                $classBuilder->addMethod('flagInstaller' , [] , '' , 'void' , true);

            }

            return $classBuilder;
        
        }
        private function getColumnSql($field , $colData){
            $out = $field . " " . $colData['type'] . " " . (@$colData['null'] ? 'NULL':'NOT NULL') . ' ' . (@$colData['auto_increment'] ? 'AUTO_INCREMENT' : '') . ' ' . (@$colData['unique'] ? 'UNIQUE' : '');
            return str_replace('  ' , ' ' , $out);
        }
        public function getDefinition() : array{
            return [];
        }
    }

?>