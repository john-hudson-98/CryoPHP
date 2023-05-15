<?php

    namespace Cryo\Security;

    interface Authorizer {
        /**
         * @return {bool} - Is the client authorised to access this page?
         */
        public function authorize() : bool;

        public function hasPermission(string $permissionName) : bool;
    }

?>