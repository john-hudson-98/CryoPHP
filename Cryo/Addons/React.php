<?php

    namespace Cryo\Addons;

    class React {
        public static function serveApp($localUrl , $assetManifest) {
            if ( $_SERVER['SERVER_NAME'] == 'localhost' ) {
                self::serveLocal(str_replace('"' , '' , $localUrl));
            } else {
                echo 'Unknown env: ' . $_SERVER['SERVER_NAME'];
            }
        }
        private static function serveLocal($localUrl){
            $ch = curl_init();
            curl_setopt_array($ch , array(
                CURLOPT_RETURNTRANSFER => 1 , 
                CURLOPT_FOLLOWLOCATION => 1 , 
                CURLOPT_URL => $localUrl . $_SERVER['REQUEST_URI'] , 
                CURLOPT_HEADER => 1
            ));
            // echo $localUrl . $_SERVER['REQUEST_URI'];
            $response = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);

            foreach(explode("\n" , $header) as $line){
                header($line);
            }
            $body = substr($response, $header_size);
            // var_dump($body , $localUrl . '?' . $_SERVER['REQUEST_URI']);
            echo ($body);
            die();
        }
    }

?>