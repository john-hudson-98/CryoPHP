<?php

    namespace Cryo\Mvc\Routers;

    interface IRouter {

        public function canRoute(string $url , $annotation) : bool;
        public function route(string $url , $annotation); //may or may not return anything

    }

?>