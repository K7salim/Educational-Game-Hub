<?php
// config.php
$host = 'localhost';
$db   = 'crack_a_number';  // Make sure this matches your database name
$user = 'root2';            // Default for XAMPP
$pass = '5r.wFo@c]OQV';                // Default for XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    // THIS must be a PDO object
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}
