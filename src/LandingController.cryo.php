<?php

    namespace App;

    @Controller
    class LandingController {

        @Get( path="/" , produces="text/html" )
        public function Homepage(){
            return file_get_contents("src/template/landing.html");
        }

    }

?>