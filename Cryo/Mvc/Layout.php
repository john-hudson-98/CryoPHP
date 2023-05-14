<?php

    namespace Cryo\Mvc;

    class Layout extends Block{

        public static $theme = null;
        public static $structure = null;

        public function setStructure($structure) : Layout{
            self::$structure = $structure;
            $this->setTemplate('page/' . $structure . '.phtml');
            return $this;
        }

        public function onAutowired($method){
            if ( $method->hasAnnotation('@Theme') ) {
                $theme = $method->getAnnotation('@Theme');

                self::$theme = $theme->getCleanValue('theme');
            }
        }
    }

?>