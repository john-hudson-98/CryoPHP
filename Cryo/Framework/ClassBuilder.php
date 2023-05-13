<?php

    namespace Cryo\Framework;

    /**
     * @description - This class converts the structured object back down 
     *              - into PHP, then stores it in a cache dir. If no modifications
     *              - to the script have been made, hit the cached version , 
     *              - this removes the overhead that comes from this framework.
     */

    class ClassBuilder {
        private $fileName = '';
        private $source = '';
        public function __construct(string $filename , ScriptDefinition $scriptObj) {
            
            $formatter = new \Cryo\Framework\Formatting\PHPrettify();
            $this->fileName = $filename;
            $this->source = '<?php ' . PHP_EOL . PHP_EOL . $formatter->prettify($scriptObj->toSource()) . PHP_EOL . '?>';
            
        }
        public function getSource(){
            return $this->source;
        }
    }

?>