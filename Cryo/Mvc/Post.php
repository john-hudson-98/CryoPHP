<?php

    namespace Cryo\Mvc;

    interface Post{
        public function path();
        public function produces();
        public function consumes();
    }

?>