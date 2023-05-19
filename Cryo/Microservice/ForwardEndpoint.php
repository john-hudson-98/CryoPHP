<?php

    namespace Cryo\Microservice;

    /**
     * This class gets forwards a web request to a 
     * specified endpoint, used to help connect microservices
     * and reduce the need for CORS headers being placed 
     * allover a codebase, drastically reducing its quality
     */

    class ForwardEndpoint
    {
        public function forwardRequest($url , $remove = null , $timeout = 2)
        {
            // Get the current request headers
            $headers = getallheaders();

            // Get the request method
            $method = $_SERVER['REQUEST_METHOD'];

            if ( $remove ) {
                $url .= '/' . str_replace($remove , '' , $_SERVER['REQUEST_URI']);
            }

            $url = str_replace('//' , '/' , $url);
            $url = str_replace(':/' , '://' , $url);

            // die($url);
            // Initialize cURL options
            $curlOptions = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => $timeout , 
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'] , 
                CURLOPT_HTTPHEADER => $this->prepareHeaders($headers),
                CURLOPT_HEADERFUNCTION => function ($curl, $header) {
                    $this->handleHeaderLine($header);
                    return strlen($header);
                }
            ];

            // Handle POST request
            if ($method === 'POST') {
                // Get the request body if available
                $body = file_get_contents('php://input');
                $curlOptions[CURLOPT_POSTFIELDS] = $body;
            }

            // Create a new cURL resource
            $curl = curl_init();

            // Set the cURL options
            curl_setopt_array($curl, $curlOptions);

            // Execute the cURL request
            $response = curl_exec($curl);

            // Close the cURL resource
            curl_close($curl);

            return $response;
        }

        private function prepareHeaders($headers)
        {
            $preparedHeaders = [];
            foreach ($headers as $name => $value) {
                $preparedHeaders[] = $name . ': ' . $value;
            }
            return $preparedHeaders;
        }

        private function handleHeaderLine($headerLine)
        {
            $headerParts = explode(':', $headerLine, 2);
            if (count($headerParts) === 2) {
                $name = trim($headerParts[0]);
                $value = trim($headerParts[1]);
                header("$name: $value");
            }
        }
    }
