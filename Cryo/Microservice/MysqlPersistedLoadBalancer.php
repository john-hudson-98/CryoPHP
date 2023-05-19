<?php

    namespace Cryo\Microservice;

    /**
     * I've built this rather quick, it has a very principle purpose
     * to assist load balancing. At some point I want to massively improve this.
     * Maybe even take in request IDs and store and update them, and delete them after
     * a timeout. 
     */

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