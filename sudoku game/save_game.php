<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['level_id']) || !isset($data['game_state'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    // Check if there's an existing saved game
    $stmt = $pdo->prepare("SELECT id FROM saved_games WHERE user_id = ? AND level_id = ?");
    $stmt->execute([$_SESSION['user_id'], $data['level_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing save
        $stmt = $pdo->prepare("
            UPDATE saved_games 
            SET game_state = ?, time_remaining = ?, saved_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([
            json_encode($data['game_state']),
            $data['game_state']['timeLeft'],
            $existing['id']
        ]);
    } else {
        // Create new save
        $stmt = $pdo->prepare("
            INSERT INTO saved_games (user_id, level_id, game_state, time_remaining) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $data['level_id'],
            json_encode($data['game_state']),
            $data['game_state']['timeLeft']
        ]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 