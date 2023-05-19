<?php

    namespace Cryo\Framework\Data;

    /**
     * This is a primitive object, its used in a couple of places, mainly in Block & Layout
     * It simply stores,retrieves and checks for existence of properties. 
    */

    class DataObject {
        
        private $_data = [];

        public function assign($data){
            foreach($data as $key => $value){
                $this->_data[$key] = $value;
            }
        }
        public function get($key){
            return @$this->_data[$key];
        }
        /**
         * @param {String} $key - the key of the data to store
         * @param {mixed} $value - the data you want to store
         * @return {DataObject} $this - for chaining
         */
        public function set(string $key , $value) : DataObject{
            $this->_data[$key] = $value;
            return $this;
        }
        public function has(string $key) : bool{
            return isset($this->_data[$key]); // will return false if value is null too!
        }
    }

?>