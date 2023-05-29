<?php

    namespace Cryo\Http;

    class Router {

        private static $YAML_CLASSES = [];

        public function route($url , $config = null){
            $all = $this->getYamlFiles('src');
            $fallbackDefinition = null;

            foreach($all as $yaml){
                $definition = spyc_load_file($yaml);

                if ( @$definition['type'] !== 'Controller' ) {
                    continue;
                }

                self::$YAML_CLASSES[] = $definition;
                //why re read them?

                if ( @$definition['isFallback'] ) {
                    $fallbackDefinition = $definition;
                }

                if ( @$definition['isMultiTenant'] ) {
                    //route from a website level instead.
                    foreach((@$definition['websites'] ?? []) as $hostname => $config){
                        if ( $_SERVER['SERVER_NAME'] == $hostname ) {
                            if ( @$definition['isFallback'] ) {
                                //if is fallback, we will launch the router 
                                //further down the code.
                                continue 2;
                            } else {
                                if ( @$definition['route'] ) {
                                    $url = $definition['route'];

                                    if ( $this->urlMatches($url , explode("?" , $_SERVER['REQUEST_URI'])[0]) ) {
                                        $this->dispatchRouter($definition);
                                    }
                                } else {

                                    foreach($config as $method => $route){
                                        if ( $this->urlMatches($route['url'] , explode("?" , $_SERVER['REQUEST_URI'])[0]) ) {
                                            $this->dispatchRouter($definition);
                                            die();
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    //route from a single level
                }
            }
            if ( $fallbackDefinition ) {
                // load fallback
                $this->dispatchRouter($definition);
            }
        }
        private function dispatchRouter($definition){
            if ( @$definition['isMultiTenant'] ) {
                $server = $definition['websites'][$_SERVER['SERVER_NAME']];
                
                if ( @$definition['isFallback'] ) {
                    if ( @$definition['subType'] == 'React' ) {
                        $react = new ReactRouter();
                        $react->serveApp($server['url'] , $server['app_name'] , $_SERVER['REQUEST_URI']);
                        die();
                    } else {
                        // sort this when it gets to it
                    }
                } else {
                    foreach($server as $methodName => $route){
                        if ( $this->urlMatches($route['url'] , explode("?" , $_SERVER['REQUEST_URI'])[0]) ) {
                            //correct route
                            if ( @$route['response'] ) {
                                // shortcut code.
                                header("Content-Type: application/json");
                                die($this->executeShortcode(@$route['response']));
                            }
                        }
                    }
                }
            } else {
                //route on method.
                
            }
        }
        private function executeShortcode($response){

            $out = [];

            foreach($response as $key => $code){
                $parts = explode(":" , $code);

                if ( @$parts[0] == 'php' ) {
                    switch(@$parts[1]) {
                        case "session":
                            $operation = explode("(" , $parts[2]);
                            $argument = $operation[1][0] == "'" || $operation[1][0] == '"' ? substr($operation[1] , 1 , strlen($operation[1]) - 3) : $operation[1];
                            switch($operation[0]){
                                case "has":
                                    $out[$key] = isset($_SESSION[$argument]);
                                break;
                                case "get":
                                    $out[$key] = $_SESSION[$argument];
                                break;
                            }
                        break;
                    }
                }
            }
            return json_encode($out);
        }
        private function urlMatches($route , $url){
            if ( $url == $route ) {
                return true; //exact route
            }
            
            $path = explode("/" , $url);
            $routeItems = explode("/" , $route);
            // echo '<pre>';
            // var_dump($path , $routeItems);
            $invalid = false;
            if ( count($path) !== count($routeItems) ) {
                return false;
            }
            for($i = 0;$i < count($path);$i++){
                if ( isset($routeItems[$i]) ) {
                    if ( $routeItems[$i] == '*' ) {
                        continue;
                    } 
                    if ( @$routeItems[$i][0] == '{' ) {
                        continue;
                    }
                    if ( $routeItems[$i] == $path[$i] ) {
                        continue;
                    }
                    if ( $path[$i] !== $routeItems[$i] ) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            
            return true;
        }
        private function dispatch($path , $possibleRoutes , $controller) {
            $handled = false;
            foreach($possibleRoutes as $route){
                
                if ( method_exists($controller , 'flagReactApp') ) {
                    // is a react app?
                    $inst = new $controller();
                    $inst->index();
                } else {
                    if ( in_array($_SERVER['REQUEST_METHOD'] , $route['methods']) ) {
                        $handled = true;

                        $methodOnClass = $route['call'];

                        $inst = new $controller();
                        $inst->{$methodOnClass}();
                        exit();
                    }
                }
            }
            
        }
        public function getAppClasses() : array{
            $out = [];
            foreach(get_declared_classes() as $className){
                if ( substr($className , 0 , 4) == 'App\\' ) {
                    $out[] = $className;
                }
            }
            return $out;
        }
        private function getYamlFiles(string $dir) : array{
            $out = [];
            foreach(glob($dir . '/*') as $yaml){
                if ( is_dir($yaml) ) {
                    $out = array_merge($out , $this->getYamlFiles($yaml));
                } else {
                    if ( stristr($yaml , '.yaml') || stristr($yaml , '.yml') ) {
                        $out[] = $yaml;
                    }
                }
            }
            return $out;
        }
    }

?>