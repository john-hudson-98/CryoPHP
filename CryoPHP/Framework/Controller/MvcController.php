<?php

    namespace Cryo\Framework\Controller;

    class MvcController {
        private $layout;

        public function __construct(){
            $this->layout = new \Cryo\Framework\Layout();
            $this->_init();
        }
        public function _init(){

        }
        public function getLayout() : ?\Cryo\Framework\Layout {
            return $this->layout;
        }
    }

?>