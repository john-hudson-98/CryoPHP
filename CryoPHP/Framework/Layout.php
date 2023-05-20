<?php

    namespace Cryo\Framework;

    class Layout extends Block{

        public static $theme = null;
        public static $structure = null;

        public function setStructure($structure) : Layout{
            self::$structure = $structure;
            $this->loadLayout();
            $this->setTemplate('page/' . $structure . '.phtml');
            return $this;
        }
        public function setTheme($theme) : Layout{
            self::$theme = $theme;
            return $this;
        }
        public function loadLayout(){
            if ( !file_exists("src/theme/" . self::$theme . "/layout/" . self::$structure . ".json") ) {
                throw new \Exception("Layout file: " . "src/theme/" . self::$theme . "/layout/" . self::$structure . ".json" . " does not exist");
            }
            $list = json_decode(file_get_contents("src/theme/" . self::$theme . "/layout/" . self::$structure . ".json") , true);

            $this->buildTree($list , $this);
        }
        private function buildTree($children , $parent){
            foreach($children as $child){
                $block = new Block();
                $block->setId($child['id']);
                $block->setTemplate($child['template']);
                $parent->addChild($block);

                if ( isset($child['children']) ) {
                    $this->buildTree($child['children'] , $block);
                }
            }
        }
        public function onAutowired($method){
            if ( $method->hasAnnotation('@Theme') ) {
                $theme = $method->getAnnotation('@Theme');

                self::$theme = $theme->getCleanValue('theme');
            }
        }
    }

?>