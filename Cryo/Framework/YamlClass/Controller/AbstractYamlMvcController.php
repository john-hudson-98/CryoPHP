<?php

    namespace Cryo\Framework\YamlClass\Controller;

    abstract class AbstractYamlMvcController {

        private $layout;

        public final function __construct(){
            $this->layout = new \Cryo\Mvc\Layout();
            $this->layout->setTheme($this->getTheme());
        }
        public function getLayout(){
            return $this->layout;
        }
        public abstract function getTheme() : string;

    }

?>