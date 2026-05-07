<?php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'berlin');
define('DB_PASS', 'Berlin@123');  // 👈 put your MySQL password here
define('DB_NAME', 'integrated_billing_system');

function getDB()
{
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($conn->connect_error) {
            die('Database connection failed: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}