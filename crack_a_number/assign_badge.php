<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

// Check user session
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Validate POST inputs
if (!isset($_POST['grade'], $_POST['level'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$user_id = $_SESSION['user_id'];
$grade   = (int)$_POST['grade'];
$level   = trim($_POST['level']);

try {
    // Fetch counts
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total,
            SUM(correct) AS correct_count,
            SUM(CASE WHEN correct = 0 AND time_taken > 0 THEN 1 ELSE 0 END) AS wrong_count,
            SUM(CASE WHEN time_taken = 0 THEN 1 ELSE 0 END) AS times_up_count,
            AVG(time_taken) AS avg_time
        FROM user_results
        WHERE user_id = ? AND grade = ? AND level = ?
    ");
    $stmt->execute([$user_id, $grade, $level]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $total         = $data['total'] ?: 1;
    $correct_count = $data['correct_count'] ?: 0;
    $wrong_count   = $data['wrong_count'] ?: 0;
    $times_up      = $data['times_up_count'] ?: 0;
    $avg_time      = $data['avg_time'] ?: 0;
    $accuracy      = ($correct_count / $total) * 100;

// Badge determination logic
$badge = '';

if (in_array($grade, [4, 5, 6]) && in_array($level, ['beginner', 'advance', 'expert'])) {
    if ($level === 'beginner') {
        if ($accuracy >= 90) {
            $badge = 'fast_learner_beginner';
        } elseif ($accuracy >= 80) {
            $badge = 'moderate_mover_beginner';
        } else {
            $badge = 'slow_learner_beginner';
        }
    } elseif ($level === 'advance') {
        if ($accuracy >= 90) {
            $badge = 'Swift_Improver_advanced';
        } elseif ($accuracy >= 80) {
            $badge = 'Consistent_Performer_advanced';
        } else {
            $badge = 'careful_strategist_advanced';
        }
    } elseif ($level === 'expert') {
        if ($accuracy >= 90) {
            $badge = 'Blazing_Pro_expert';
        } elseif ($accuracy >= 80) {
            $badge = 'Precision_Master_expert';
        } else {
            $badge = 'Strategic_Thinker_expert';
        }
    }
}


    // Save badge + counts to user_badges
    $stmt = $pdo->prepare("
        INSERT INTO user_badges 
            (user_id, grade, level, badge, correct_count, wrong_count, times_up_count, earned_at)
        VALUES 
            (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE 
            badge = VALUES(badge),
            correct_count = VALUES(correct_count),
            wrong_count = VALUES(wrong_count),
            times_up_count = VALUES(times_up_count),
            earned_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([
        $user_id, $grade, $level, $badge,
        $correct_count, $wrong_count, $times_up
    ]);

    echo json_encode([
        'badge' => $badge,
        'accuracy' => round($accuracy, 2),
        'correct' => $correct_count,
        'wrong' => $wrong_count,
        'times_up' => $times_up,
        'avg_time' => round($avg_time, 2)
    ]);

} catch (PDOException $e) {
    error_log("Badge Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
