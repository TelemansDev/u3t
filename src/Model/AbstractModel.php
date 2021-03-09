<?php
declare(strict_types=1);

namespace App;
use PDO;
use PDOException;

class AbstractModel {
    protected PDO $conn;
    
    public function __construct(array $conf) {
        try {
            $this->createConnection($conf);
        } catch (PDOException $e) {
            echo 'DB connection error';
        }
    }

    private function createConnection($conf): void {
        $dsn = "mysql:dbname={$conf['dbname']}; host={$conf['host']}";
        $this->conn = new PDO($dsn, $conf['user'], $conf['pass']);
    }
}