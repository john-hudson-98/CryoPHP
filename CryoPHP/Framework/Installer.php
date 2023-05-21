<?php

    namespace Cryo\Framework;

    class Installer {
        public final function __construct(){
            foreach(get_class_methods($this) as $method){
                if ( stristr($method , 'install_') ) {
                    $this->{$method}();
                }
            }
        }
    }

?>