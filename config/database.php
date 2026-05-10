<?php
// config/database.php

class Database {
    private $host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
    private $port = 4000;
    private $user = '23q29KSmPmgSA4v.root';
    private $pass = 'rTy66yom37kCjduu';
    private $db   = 'simosoba2';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_SSL_CA       => true,
            ];
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $exception) {
            die("Koneksi Database Gagal: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>
