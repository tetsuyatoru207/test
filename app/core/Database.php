<?php

class Database {

    protected function connect() {
        try {
            $dsn = 'mysql:host=' . DB_HOST .
                   ';dbname=' . DB_NAME .
                   ';charset=utf8mb4';

            $dbh = new PDO($dsn, DB_USER, DB_PASS);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $dbh;
        } catch (PDOException $e) {
            die('Lỗi database: ' . $e->getMessage());
        }
    }
}
