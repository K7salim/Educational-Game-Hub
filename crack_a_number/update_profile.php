<?php 
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$name = $_POST['name'];
$grade = $_POST['grade'];
$password = $_POST['password'];
$selectedAvatar = $_POST['profile_pic'];

$profilePic = $selectedAvatar; // Default

// Handle uploaded image
if (isset($_FILES['upload_image']) && $_FILES['upload_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = basename($_FILES['upload_image']['name']);
    $targetFile = $uploadDir . uniqid() . '_' . $filename;

    if (move_uploaded_file($_FILES['upload_image']['tmp_name'], $targetFile)) {
        $profilePic = $targetFile; // Override only if upload succeeded
    }
}

try {
    // Prepare query dynamically if password is filled
    $query = "UPDATE users SET name = ?, grade = ?, profile_pic = ?";
    $params = [$name, $grade, $profilePic];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query .= ", password = ?";
        $params[] = $hashedPassword;
    }

    $query .= " WHERE id = ?";
    $params[] = $userId;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    header("Location: profile.php");
    exit;
} catch (PDOException $e) {
    echo "Update failed: " . $e->getMessage();
}
?>
