<?php

    namespace App\Controller;

    @Controller
    class ExampleController {

        @Autowired
        private \App\Repository\ExampleRepository $exampleRepository;

        @Get( path="/example/tables" , produces="application/json" )
        public function getTables(){
            echo '<pre>';
            var_dump($this->exampleRepository);
        }

    }

?>