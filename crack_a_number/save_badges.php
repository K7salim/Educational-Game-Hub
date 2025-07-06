<?php
session_start();
require 'config.php'; 

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// 2. Get and validate POST JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['grade'], $input['score'], $input['badges'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$grade = intval($input['grade']);
$score = intval($input['score']);
$badges = $input['badges']; // Expecting an array

// Convert badges array to comma-separated string
$badges_str = implode(',', array_map('trim', $badges));

// 3. Database connection (make sure this is correct in config.php)
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// 4. Check if there's already a record
$sql_check = "SELECT id FROM user_scores WHERE user_id = ? AND grade = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $grade);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // 5. Update existing record
    $sql_update = "UPDATE user_scores SET score = ?, badges = ?, updated_at = NOW() WHERE user_id = ? AND grade = ?";
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt_update->bind_param("isii", $score, $badges_str, $user_id, $grade);
    $success = $stmt_update->execute();
    if (!$success) {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt_update->error]);
        exit;
    }
    $stmt_update->close();
} else {
    // 6. Insert new record
    $sql_insert = "INSERT INTO user_scores (user_id, grade, score, badges, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt_insert->bind_param("iiis", $user_id, $grade, $score, $badges_str);
    $success = $stmt_insert->execute();
    if (!$success) {
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt_insert->error]);
        exit;
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();

echo json_encode(['success' => true, 'message' => 'Score and badges saved']);
