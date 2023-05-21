<?php

  namespace Cryo\Core\SQLUtils;

  class BasicSQLTokenizer {
    private $tokens;
    private $table;
    private $operation;
    private $columns;
    private $values;

    public function __construct($query) {
      $this->tokens = $this->tokenize($query);
      $this->table = $this->extractTableName();
      $this->operation = $this->extractOperation();
      $this->columns = $this->extractColumns();
      $this->values = $this->extractValues();
    }

    private function tokenize($query) {
      // Tokenize the MySQL query
      $pattern = '/
        (?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|`[^`\\\\]*(?:\\\\.[^`\\\\]*)*`)(*SKIP)(*F)|
        (\()|(\))|([,\s])|("(?:[^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'(?:[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')?|(`(?:[^`\\\\]*(?:\\\\.[^`\\\\]*)*)`)?
      /x';

      preg_match_all($pattern, $query, $matches);

      return $matches[0];
    }

    private function extractTableName() {
      // Extract the table name from the tokens
      $fromIndex = array_search('FROM', $this->tokens) + 1;

      return $this->tokens[$fromIndex];
    }

    private function extractOperation() {
      // Extract the operation (SELECT, INSERT, UPDATE, DELETE) from the tokens
      return strtoupper($this->tokens[0]);
    }

    private function extractColumns() {
      // Extract columns from the tokens
      $columns = [];

      if ($this->operation === 'SELECT') {
        // Find the SELECT clause
        $selectIndex = array_search('SELECT', $this->tokens);
        $fromIndex = array_search('FROM', $this->tokens);

        // Extract columns between SELECT and FROM
        $columns = array_slice($this->tokens, $selectIndex + 1, $fromIndex - $selectIndex - 1);
      } elseif ($this->operation === 'INSERT') {
        // Find the columns enclosed in parentheses
        $columnsIndex = array_search('(', $this->tokens);

        if ($columnsIndex !== false) {
          $columns = array_slice($this->tokens, $columnsIndex + 1, -1);
        }
      } elseif ($this->operation === 'UPDATE') {
        // Find the SET clause
        $setIndex = array_search('SET', $this->tokens);

        // Extract columns after SET
        $columns = array_slice($this->tokens, $setIndex + 1);
      }

      // Remove any extraneous characters like commas and spaces
      $columns = array_filter($columns, function($column) {
        return !in_array($column, [',', ' ']);
      });

      return $columns;
    }

    private function extractValues() {
      // Extract values from the tokens
      $values = [];

      if ($this->operation === 'INSERT') {
        // Find the VALUES keyword
        $valuesIndex = array_search('VALUES', $this->tokens);

        if ($valuesIndex !== false) {
          $valuesTokens = array_slice($this->tokens, $valuesIndex + 1);
          $values = $this->extractMultipleInsertValues($valuesTokens);
        }
      } elseif ($this->operation === 'UPDATE') {
        // Find the WHERE clause
        $whereIndex = array_search('WHERE', $this->tokens);

        if ($whereIndex !== false) {
          $values = array_slice($this->tokens, 0, $whereIndex);
        }
      }

      // Remove any extraneous characters like commas and spaces
      $values = array_filter($values, function($value) {
        return !in_array($value, [',', ' ']);
      });

      return $values;
    }

    private function extractMultipleInsertValues($valuesTokens) {
      $values = [];

      $currentValue = [];
      $parenthesesCount = 0;

      foreach ($valuesTokens as $token) {
        if ($token === '(') {
          $parenthesesCount++;
        } elseif ($token === ')') {
          $parenthesesCount--;
          if ($parenthesesCount === 0) {
            $values[] = $currentValue;
            $currentValue = [];
          }
        }

        $currentValue[] = $token;
      }

      return $values;
    }

    public function getTableName() {
      return $this->table;
    }

    public function getOperation() {
      return $this->operation;
    }

    public function getColumns() {
      return $this->columns;
    }

    public function getValues() {
      return $this->values;
    }
  }



?>