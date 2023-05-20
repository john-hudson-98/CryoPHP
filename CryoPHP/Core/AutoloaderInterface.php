<?php

    namespace Cryo\Core;

    interface AutoloaderInterface {
        public function canAutoload(string $className) : bool;
        public function import(string $className);
    }

?>