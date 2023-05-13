<?php

    namespace Cryo\Framework\Data;

    class DataObject {
        
        private $_data = [];

        public function assign($data){
            foreach($data as $key => $value){
                $this->_data[$key] = $value;
            }
        }

    }

?>