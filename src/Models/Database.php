<?php

class Database {
    private static $pdo;

    public static function connect() {
        if (!self::$pdo) {
            try {
                self::$pdo = new PDO('sqlite:' . __DIR__ . '/../../database.sqlite');
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
