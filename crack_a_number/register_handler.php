<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $pass = $_POST['password'] ?? '';
    $grade = $_POST['grade'] ?? '';

    // Basic validation
    if (empty($name) || strlen($name) < 3 || strlen($name) > 50) {
        $_SESSION['error'] = 'Name must be between 3 and 50 characters.';
        header("Location: register.php");
        exit;
    }

    if (empty($pass) || strlen($pass) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters.';
        header("Location: register.php");
        exit;
    }

    if (!in_array($grade, ['4', '5', '6'])) {
        $_SESSION['error'] = 'Please select a valid grade level.';
        header("Location: register.php");
        exit;
    }

    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE name = ?");
        $stmt->execute([$name]);

        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Name already registered. Choose another.';
            header("Location: register.php");
            exit;
        }

        // Insert without hashing password
        $stmt = $pdo->prepare("INSERT INTO users (name, grade, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$name, $grade, $pass]);

        $_SESSION['success'] = 'You have successfully registered!';
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header("Location: register.php");
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}
