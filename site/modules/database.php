<?php

class Database {
    private $pdo;

    /**
     * Constructor for the Database class
     * @param string $path Path to the SQLite database file
     */
    public function __construct($path) {
        try {
            $this->pdo = new PDO("sqlite:$path");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute an SQL query
     * @param string $sql SQL query to execute
     * @return bool Success status
     */
    public function Execute($sql) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("SQL execution failed: " . $e->getMessage());
        }
    }

    /**
     * Execute an SQL query and return results as associative array
     * @param string $sql SQL query to execute
     * @return array Results as associative array
     */
    public function Fetch($sql) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("SQL fetch failed: " . $e->getMessage());
        }
    }

    /**
     * Create a new record in the specified table
     * @param string $table Table name
     * @param array $data Associative array of column names and values
     * @return int ID of the created record
     */
    public function Create($table, $data) {
        try {
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            die("Create operation failed: " . $e->getMessage());
        }
    }

    /**
     * Read a record from the specified table by ID
     * @param string $table Table name
     * @param int $id Record ID
     * @return array Record data as associative array
     */
    public function Read($table, $id) {
        try {
            $sql = "SELECT * FROM $table WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Read operation failed: " . $e->getMessage());
        }
    }

    /**
     * Update a record in the specified table by ID
     * @param string $table Table name
     * @param int $id Record ID
     * @param array $data Associative array of column names and values to update
     * @return bool Success status
     */
    public function Update($table, $id, $data) {
        try {
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = :$key";
            }
            $setString = implode(", ", $set);
            
            $sql = "UPDATE $table SET $setString WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(":id", $id);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Update operation failed: " . $e->getMessage());
        }
    }

    /**
     * Delete a record from the specified table by ID
     * @param string $table Table name
     * @param int $id Record ID
     * @return bool Success status
     */
    public function Delete($table, $id) {
        try {
            $sql = "DELETE FROM $table WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Delete operation failed: " . $e->getMessage());
        }
    }

    /**
     * Count records in the specified table
     * @param string $table Table name
     * @return int Number of records
     */
    public function Count($table) {
        try {
            $sql = "SELECT COUNT(*) as count FROM $table";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (PDOException $e) {
            die("Count operation failed: " . $e->getMessage());
        }
    }
}