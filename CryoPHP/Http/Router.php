<?php

    namespace Cryo\Http;

    class Router {

        public function route($url , $config = null){
            $all = $this->getYamlFiles('src');
            
            $cache = new \Cryo\Core\CacheManager();
            $loader = new \Cryo\YAC\Loader();
            
            foreach($all as $definition) {
                ## THIS CURRENTLY DOESN'T WORK CORRECTLY, IT DOESN'T SHOW WHATS THE MOST
                ## RECENT FILE, KEEPING IT LIKE THIS FOR DEVELOPMENT PURPOSES
                if ( $cache->cacheExistsNoType($definition) ) {
                    $cache->load($definition);   
                } else {
                    $loader->load($definition);
                    $cache->load($definition);   
                }
            }
            $controllers = [];
            foreach($this->getAppClasses() as $className) {
                if ( method_exists($className , 'flagController') ) {
                    $controllers[] = $className;
                }
            }

            foreach($controllers as $controller){
                $routes = $controller::GetRoutes();
                foreach($routes as $path => $possibleRoutes) {
                    if ( $this->urlMatches($path , $url) ) {
                        $this->dispatch($path , $possibleRoutes , $controller);
                        die();
                    }
                }
            }

            if ( $config ) {
                if ( isset($config['fallbackController']) ) {
                    $inst = new $config['fallbackController']();

                    $inst->{$config['fallbackMethod'] ?? "index"}();
                }
            }
            
        }
        private function urlMatches($route , $url){
            if ( $url == $route ) {
                return true; //exact route
            }
            if ( @preg_match($route , $url) ) {
                return true; //allow regular expression matching
            }
            $path = explode("/" , $url);
            $routeItems = explode("/" , $route);

            for($i = 0;$i < count($path);$i++){
                if ( isset($routeItems[$i]) ) {
                    if ( $routeItems[$i] == '*' ) {
                        continue;
                    } 
                    if ( $routeItems[$i] == $path[$i] ) {
                        continue;
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