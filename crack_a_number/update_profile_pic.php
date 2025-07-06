<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['pic'])) {
    header("Location: profile.php");
    exit;
}

$userId = $_SESSION['user_id'];
$newPic = $_POST['pic'];

try {
    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    $stmt->execute([$newPic, $userId]);
    header("Location: profile.php");
    exit;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
