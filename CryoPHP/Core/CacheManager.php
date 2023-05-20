<?php

    namespace Cryo\Core;

    class CacheManager {

        public function cacheExists($filename , $type = 'object') {
            $cacheName = sha1($filename) . ".php";
            return file_exists("var/cache/cryo/{$type}/{$cacheName}");
        }
        public function cacheValid($filename , $type = 'object') {
            $cacheName = sha1($filename) . ".php";
            return $this->cacheExists($filename , $type) && (filemtime($filename) < filemtime("var/cache/cryo/{$type}/{$cacheName}"));
        }
        public function saveCache($filename , $source , $type = 'object') {
            $cacheName = sha1($filename) . ".php";
            @mkdir("var/cache/cryo/{$type}");
            file_put_contents("var/cache/cryo/{$type}/{$cacheName}" , $source);
        }
    }

?>