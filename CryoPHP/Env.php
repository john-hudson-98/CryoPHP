<?php

    namespace Cryo;

    class Env {
        private static $instance = null;
        private $local = [];
        private $production = [];
      
        private function __construct() {
          // Load local environment variables
          $this->loadEnvFile('src/.env.local');
      
          // Load production environment variables
          $this->loadEnvFile('src/.env.production');
        }
      
        public static function GetInstance() {
          if (self::$instance === null) {
            self::$instance = new self();
          }
      
          return self::$instance;
        }
        public function get($key){
            if ( Stage::isDev() ) {
                return $this->getLocal($key);
            } else {
                return $this->getProduction($key);
            }
        }
        private function loadEnvFile($filePath) {
          if (file_exists($filePath)) {
            $envVariables = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      
            foreach ($envVariables as $line) {
              $line = trim($line);
      
              if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
      
                // Remove quotes from value if present
                $value = trim($value, "'\"");
      
                if ($filePath === 'src/.env.local') {
                  $this->local[$key] = $value;
                } elseif ($filePath === 'src/.env.production') {
                  $this->production[$key] = $value;
                }
              }
            }
          }
        }
      
        public function getLocal($key) {
          return $this->local[$key] ?? null;
        }
      
        public function getProduction($key) {
          return $this->production[$key] ?? null;
        }
      }
      

?>