<?php

    namespace Cryo\Microservice;

    /**
     * I've built this rather quick, it has a very principle purpose
     * to assist load balancing. At some point I want to massively improve this.
     * Maybe even take in request IDs and store and update them, and delete them after
     * a timeout. 
     */

    class MysqlPersistedLoadBalancer {
        private $db;
        public function setDb(\Cryo\DataLayer\DatabaseAdapter $adapter){
            $this->db = $adapter;
        }
        public function getLeastActiveServer(){
            $db = $this->db;

            return $db->query("SELECT * FROM loadbalancer_servers ORDER BY max_requests - current_requests DESC LIMIT 1")[0];
        }
        public function addRequest($server){
            $db = $this->db;

            $db->query("UPDATE loadbalancer_servers SET current_requests = current_requests + 1 WHERE server_ip = '{$db->escape($server['server_ip'])}'");
        }
        public function finishRequest($server){
            $db = $this->db;

            $db->query("UPDATE loadbalancer_servers SET current_requests = current_requests - 1 WHERE server_ip = '{$db->escape($server['server_ip'])}'");
        }
    }

?>