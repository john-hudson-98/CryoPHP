<?php

    namespace Cryo\DataLayer;

    class MySQLAdapter implements DatabaseAdapter{
    private static $instance = null;
    private $connection;

    private function __construct() {
        $env = \Cryo\Env::GetInstance();

        $this->connection = new \mysqli($env->get('mysql.host'), $env->get('mysql.user'), $env->get('mysql.password'));
        $this->connection->query("CREATE DATABASE IF NOT EXISTS `{$env->get('mysql.schema')}`");
        $this->connection->select_db($env->get('mysql.schema'));

        if ($this->connection->connect_error) {
        throw new \Exception('Failed to connect to MySQL: ' . $this->connection->connect_error);
        }
    }

    public static function Get() {
        if (self::$instance === null) {
        self::$instance = new self();
        }

        return self::$instance;
    }
    public function escape(string $str) : string{
        return $this->connection->real_escape_string($str);
    }
    public function query(string $query, array $params = []) : array {
        // Prepare the query
        $statement = $this->connection->prepare($query);

        if ($statement === false) {
        throw new \Exception('Failed to prepare query: ' . $this->connection->error);
        }

        // Bind parameters if provided
        if (!empty($params)) {
        $types = '';
        $values = [];

        foreach ($params as $param) {
            $types .= $this->getParamType($param);
            $values[] = $param;
        }

        $statement->bind_param($types, ...$values);
        }

        // Execute the query
        $result = $statement->execute();

        if ($result === false) {
        throw new \Exception('Failed to execute query: ' . $statement->error);
        }

        // Retrieve the result set if applicable
        $resultSet = null;

        if ($statement->result_metadata()) {
        $resultSet = $statement->get_result();
        }

        // Close the statement
        $statement->close();
        $out = [];
        
        if ( @$resultSet->num_rows ) {
            while($o = $resultSet->fetch_assoc()) {
                $out[] = $o;
            }
            return $out;
        }

        return [];
    }

    private function getParamType($param) {
        if (is_int($param)) {
        return 'i';
        } elseif (is_float($param)) {
        return 'd';
        } elseif (is_string($param)) {
        return 's';
        } else {
        return 'b';
        }
    }

    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));

        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        return $this->query($query, array_values($data));
    }

    public function update($table, $data, $where) {
        $setStatements = [];

        foreach ($data as $column => $value) {
        $setStatements[] = "$column = ?";
        }

        $setClause = implode(',', $setStatements);

        $query = "UPDATE $table SET $setClause WHERE $where";

        return $this->query($query, array_values($data));
    }

    public function delete($table, $where) {
        $whereStatements = [];

        foreach ($where as $column => $value) {
        $whereStatements[] = "$column = ?";
        }

        $whereClause = implode(' AND ', $whereStatements);

        $query = "DELETE FROM $table WHERE $whereClause";

        return $this->query($query, array_values($where));
    }
    }


?>