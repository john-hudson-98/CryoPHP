<?php

    namespace Cryo\Microservice;

    class MysqlPersistedLoadBalancer {
        public function getLeastActiveServer(){
            $db = \Cryo\Connectors\MySQLConnector::Get();

            return $db->query("SELECT * FROM loadbalancer_servers ORDER BY max_requests - current_requests DESC LIMIT 1")[0];
        }
        public function addRequest($server){
            $db = \Cryo\Connectors\MySQLConnector::Get();

            $db->query("UPDATE loadbalancer_servers SET current_requests = current_requests + 1 WHERE server_ip = '{$db->escape($server['server_ip'])}'");
        }
        public function finishRequest($server){
            $db = \Cryo\Connectors\MySQLConnector::Get();

            $db->query("UPDATE loadbalancer_servers SET current_requests = current_requests - 1 WHERE server_ip = '{$db->escape($server['server_ip'])}'");
        }
    }

?>