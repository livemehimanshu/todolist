<?php
define('DB_SERVER', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', 'your_password'); // CHANGE THIS
define('DB_NAME', 'todo_db'); 

try {
    $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection ERROR: " . $e->getMessage());
}
?>
