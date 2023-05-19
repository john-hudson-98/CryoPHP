<?php

    namespace Cryo\Framework;

    /**
     * This is what breaks the CryoPHP code down into tokens 
     * This class could definitely do with some improvement
     * but it works so I'm not going to attempt to improve it
     * yet.
     */

    class Tokenizer {
        private $input;
        private $position;

        private $tokens = [];

        public function __construct(string $input) {
            $this->input = $input;
            $this->position = 0;
            $this->tokenize();
        }
        public function getTokens() : array{
            return $this->tokens;
        }
        private function getNextChar() {
            if ($this->position >= strlen($this->input)) {
                return false;
            }

            $char = $this->input[$this->position];
            $this->position++;

            return $char;
        }

        public function tokenize() {
            $tokens = [];
            $in_token = false;
            $token = '';

            while (($char = $this->getNextChar()) !== false) {
                if ($char == '@') {
                    if ($in_token) {
                        $tokens[] = $token;
                    }
                    $in_token = true;
                    $token = $char;
                } elseif ($char == '$') {
                    if ($in_token) {
                        $tokens[] = $token;
                    }
                    $in_token = true;
                    $token = $char;
                } elseif ($char == '"' || $char == "'") {
                    if ($in_token) {
                        $tokens[] = $token;
                    }
                    $in_token = true;
                    $token = $char;

                    while (($next_char = $this->getNextChar()) !== false) {
                        $token .= $next_char;

                        if ($next_char == $char) {
                            break;
                        }
                    }
                    $tokens[] = $token;
                    $in_token = false;
                    $token = '';
                } elseif (ctype_space($char) || in_array($char, [';', '(', ')', '[', ']', '+', '-', '*', '/', '%', '>', '<', '=', '!', '&', '|' , '{' , '}' , ','])) {
                    if ($in_token) {
                        $tokens[] = $token;
                    }
                    $tokens[] = $char;
                    $in_token = false;
                    $token = '';
                } else {
                    $in_token = true;
                    $token .= $char;
                }
            }

            if ($in_token) {
                $tokens[] = $token;
            }

            $this->tokens = $tokens;
        }
    }

?>
