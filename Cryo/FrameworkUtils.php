<?php

    namespace Cryo;

    class FrameworkUtils {
        private static $CLASS_REGISTRY = [];

        public static function registerClass(string $className , Framework\ScriptDefinition $definition){
            self::$CLASS_REGISTRY[$className] = $definition;
        }
        public static function getClass(string $className){
            return self::$CLASS_REGISTRY[$className];
        }
    }

?>