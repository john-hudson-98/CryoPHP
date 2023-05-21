<?php

    namespace Cryo\Http;

    class LoadBalancer {
        public function dispatch($servers , $persistenceType = 'live'){

            if ( $persistenceType == 'mysql' ) {

                $this->persistServers($servers);

                $lb = $this->getBestServer($servers , $persistenceType);
                $server = $lb->getLeastActiveServer();
                $lb->addRequest($server);
                $forwardReq = new \Cryo\Microservice\ForwardEndpoint();
                $resp = ($forwardReq->forwardRequest($server['server_ip'] . $_SERVER['REQUEST_URI']));
                $lb->finishRequest($server);
                die($resp);
            }
        }
        private function persistServers($servers){
            $db = $this->GetDbAdapter();

            foreach($servers as $server){
                try{
                    $db->insert("loadbalancer_servers" , array(
                        'server_ip' => $server['domain'] , 
                        'max_requests' => $server['maxConcurrentRequests']
                    ));
                }catch(\Exception $e){
                    //already exists!
                }
            }
        }
        private function getBestServer($list , $type = 'live') {
            if ( $type == 'mysql' ) {
                $db = $this->GetDbAdapter();

                $db->query("CREATE TABLE IF NOT EXISTS loadbalancer_servers (
                    server_ip VARCHAR(255) NOT NULL UNIQUE , 
                    max_requests INT(11) NOT NULL DEFAULT '0' , 
                    current_requests INT(11) NOT NULL DEFAULT '0' , 
                    PRIMARY KEY(server_ip)
                )");
                $lb = new \Cryo\Microservice\MysqlPersistedLoadBalancer();
                $lb->setDb($db);

                return $lb;
            }
        }

    }

?>