<?php

    namespace Cryo\Mvc;

    interface ReactApp {
        public function app_name();
        public function local_url(); // i.e http://localhost:3000/
        public function asset_manifest(); // points to asset manifest
    }

?>