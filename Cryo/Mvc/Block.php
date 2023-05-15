<?php

    namespace Cryo\Mvc;

    class Block extends \Cryo\Framework\Data\DataObject {
        
        private $parent = null;
        private $children = [];

        public function setTemplate($tpl) {
            return $this->set('template' , $tpl);
        }
        public function getTemplate(){
            return $this->get('template');
        }
        public function getParent(){
            return $this->parent;
        }
        public function addChild(Block $child){
            $child->parent = $this;
            $this->children[] = $child;
            return $this;
        }
        public function getChild($id) : ?Block{
            foreach($this->children as $child){
                if ( $child->getId() == $id ) {
                    return $child;
                }
                $csearch = $child->getChild($id);
                if ( $csearch ) {
                    return $csearch;
                }
            }
            return null;
        }
        public function render(){
            
            $tplDir;
            
            if ( substr($this->getTemplate() , 0 , strlen("shared://")) == "shared://" ) {
                $tplDir = str_replace('shared://' , 'src/theme/shared/' , $this->getTemplate());    
            } else {
                $tplDir = 'src/theme/' . Layout::$theme . '/template/' . $this->getTemplate();
            }
            if ( !file_exists($tplDir) ) {
                echo 'Missing theme file: ' . $tplDir;
                return;
            }
            include($tplDir);
        }
        public function getId(){
            return $this->get("id");
        }
        public function setId($id){
            $this->set("id" , $id);
            return $this;
        }
    
    }

?>