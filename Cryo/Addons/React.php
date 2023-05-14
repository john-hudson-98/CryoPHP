<?php

    namespace Cryo\Addons;

    class React {
        public static function serveApp($localUrl ,  $appName , $url = "") {
            $pageName = ucfirst($appName);
            if ( $_SERVER['SERVER_NAME'] == 'localhost' && !file_exists("public/reactapps/" . $appName) ) {
                self::serveLocal($localUrl , $url);
            } else {
                if ( file_exists("public/reactapps/" . $appName) ) {
                    //load local.

                    self::servePublic("public/reactapps/" . $appName , $pageName);
                } else {
                    echo 'Unknown Environment: ' . $_SERVER['SERVER_NAME'];
                }
            }
        }
        private static function servePublic($dir , $pageName){
            // echo 'Serving from ' . $dir;
            $manifest = json_decode(file_get_contents($dir . '/asset-manifest.json') , true);
            
            if ( stristr($_SERVER['REQUEST_URI'] , 'static') ) {
                // return static asset
                $ext = substr($_SERVER['REQUEST_URI'] , strrpos($_SERVER['REQUEST_URI'] , '.') + 1);

                if ( $ext == 'css' || $ext == 'js' ) {
                    header("Content-Type: text/" . str_replace('js' , 'javascript' , $ext));
                } else {
                    header("Content-Type: image/{$ext}");
                }
                readfile($dir . $_SERVER['REQUEST_URI']);
                die();
            } else {
                $replaces = array(
                    'JSBUNDLE' => $manifest['entrypoints'][1] , 
                    'CSSBUNDLE' => $manifest['entrypoints'][0] , 
                    'PAGE_TITLE' => $pageName 
                );

                $tpl = file_get_contents("Cryo/Addons/ReactPage.html");

                foreach($replaces as $find => $replace){
                    $tpl = str_replace('{' . $find . '}' , $replace , $tpl);
                }
                die($tpl);
            }
        }
        private static function serveLocal($localUrl , $url){
            $ch = curl_init();
            curl_setopt_array($ch , array(
                CURLOPT_RETURNTRANSFER => 1 , 
                CURLOPT_FOLLOWLOCATION => 1 , 
                CURLOPT_URL => $localUrl . $url , 
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