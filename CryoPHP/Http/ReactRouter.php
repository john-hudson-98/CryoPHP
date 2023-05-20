<?php

    namespace Cryo\Http;

    /**
     * This class was built in the prototying stage, its meant to route
     * web requests to either a local react http server such as create-react-app
     * Its a little bit hacky but its stable and allows all the same functionality
     * as requesting it from a browser, this helps with CORS, use inline with EndpointForward
     * or any controllers you've set up.
     */

    class ReactRouter {
        /**
         * @param {String} $localUrl - usually http://localhost:3000, url to the react app in dev mode
         * @param {String} $appName - Directly maps to public/reactapps/{$appName} - this is for a built app only
         * @param {String} $url - the URL that is sent to the controller.
         */
        public static function serveApp($localUrl ,  $appName , $url = "") : void {
            
            $pageName = ucfirst($appName); // this can be overwritten by the react app.
            
            if ( \Cryo\Stage::isDev() && !file_exists("public/reactapps/" . $appName) ) {

                // if the app folder exists, i.e its already built, this 
                // statement will never be reached. Instead it will serve
                // the app instead.
                self::serveLocal($localUrl , $url);

            } else {
                
                // if the app exists, load the app
                if ( file_exists("public/reactapps/" . $appName) ) {
                    //load local.

                    self::servePublic("public/reactapps/" . $appName , $pageName);
                }
                //will fall back to 404
            }
        }
        private static function servePublic($dir , $pageName){

            $manifest = json_decode(file_get_contents($dir . '/asset-manifest.json') , true);
            
            //checks if the URL contains static, meaning its referencing
            //static content.
            // works out the mime type, 
            // then serves it.

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

                $tpl = file_get_contents("Cryo/Http/React/page.html");

                foreach($replaces as $find => $replace){
                    $tpl = str_replace('{' . $find . '}' , $replace , $tpl);
                }
                die($tpl);
            }
        }
        private static function serveLocal($localUrl , $url){

            //creates a web request and 
            //requests content as if a browser
            //did.
            $ch = curl_init();
            curl_setopt_array($ch , array(
                CURLOPT_RETURNTRANSFER => 1 , 
                CURLOPT_FOLLOWLOCATION => 1 , 
                CURLOPT_URL => $localUrl . $url , 
                CURLOPT_HEADER => 1
            ));
            // die($localUrl . $url);
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