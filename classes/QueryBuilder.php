<?php
// ============================================================
//  classes/QueryBuilder.php
//
//  A fluent interface for building SQL queries without
//  writing raw SQL strings in every page.
//
//  Usage example:
//    $qb = new QueryBuilder($conn);
//    $rows = $qb->table('subjects')->select()->where('user_id', 1)->get();
// ============================================================

class QueryBuilder {
    private $conn;
    private $table;
    private $sql    = "";
    private $params = [];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Set the target table and reset state for a fresh query
    public function table($table) {
        $this->table  = $table;
        $this->sql    = "";
        $this->params = [];
        return $this;
    }

    // Build a SELECT statement
    public function select($columns = "*") {
        $this->sql = "SELECT $columns FROM {$this->table}";
        return $this;
    }

    // Append a WHERE clause
    public function where($column, $value) {
        // Check if WHERE already exists to support chaining
        if (strpos($this->sql, 'WHERE') === false) {
            $this->sql .= " WHERE $column = :$column";
        } else {
            $this->sql .= " AND $column = :$column";
        }
        $this->params[":$column"] = $value;
        return $this;
    }

    // ORDER BY clause
    public function orderBy($column, $direction = 'ASC') {
        $this->sql .= " ORDER BY $column $direction";
        return $this;
    }

    // Execute and return all matching rows
    public function get() {
        $stmt = $this->conn->prepare($this->sql);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Execute and return only the first matching row
    public function first() {
        $this->sql .= " LIMIT 1";
        $stmt = $this->conn->prepare($this->sql);
        $stmt->execute($this->params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Build and execute an INSERT statement
    public function insert(array $data) {
        $columns      = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $this->sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        $stmt = $this->conn->prepare($this->sql);
        return $stmt->execute($data);
    }

    // Build and execute an UPDATE statement
    public function update(array $data, $id) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :$key";
        }
        $set = implode(", ", $setParts);

        $this->sql = "UPDATE {$this->table} SET $set WHERE id = :id";
        $data['id'] = $id;

        $stmt = $this->conn->prepare($this->sql);
        return $stmt->execute($data);
    }

    // Build and execute a DELETE statement
    public function delete($id) {
        $this->sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($this->sql);
        return $stmt->execute(['id' => $id]);
    }

    // Return the last inserted ID
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
