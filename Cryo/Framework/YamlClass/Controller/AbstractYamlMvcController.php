<?php

    namespace Cryo\Framework\YamlClass\Controller;

    /**
     * I've spent a lot of time developing YAML -> PHP transpilation
     * This is a base class that allows the YAML Controllers to exist
     * by providing access to the layout library, this needs to be added
     * pretty much straight away, thats why its developed as a class 
     * and not autowired.
     */

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