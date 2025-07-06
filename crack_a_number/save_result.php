<?php

session_start();

require 'config.php';

header('Content-Type: application/json');



// 1. Check session

if (!isset($_SESSION['user_id'])) {

    http_response_code(403);

    echo json_encode(['error' => 'Not logged in']);

    exit;

}



// 2. Validate input

$required = ['grade', 'level', 'correct_count', 'wrong_count', 'times_up_count'];

foreach ($required as $key) {

    if (!isset($_POST[$key])) {

        http_response_code(400);

        echo json_encode(['error' => "Missing field: $key"]);

        exit;

    }

}



// 3. Get inputs

$user_id       = $_SESSION['user_id'];

$grade         = (int)$_POST['grade'];

$level         = trim($_POST['level']);

$correct_count = (int)$_POST['correct_count'];

$wrong_count   = (int)$_POST['wrong_count'];

$times_up      = (int)$_POST['times_up_count'];



$total    = max(1, $correct_count + $wrong_count + $times_up);

$accuracy = ($correct_count / $total) * 100;



// 4. Generate UUID for session

function generateUUIDv4() {

    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

        mt_rand(0, 0xffff), mt_rand(0, 0xffff),

        mt_rand(0, 0xffff),

        mt_rand(0, 0x0fff) | 0x4000,

        mt_rand(0, 0x3fff) | 0x8000,

        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)

    );

}

$session_id = generateUUIDv4();



// 5. Badge logic

$badge = '';

if (in_array($grade, [4,5,6]) && in_array($level, ['beginner', 'advance', 'expert'])) {
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
            $badge = 'fast_learner_advanced';
        } elseif ($accuracy >= 80) {
            $badge = 'moderate_mover_advanced';
        } else {
            $badge = 'slow_learner_advanced';
        }
    } elseif ($level === 'expert') {
        // Expert special cases for wrong count (mali)
        if ($wrong_count === 0) {
            $badge = 'Blazing_Pro_expert';
        } elseif (in_array($wrong_count, [1,2,3])) {
            $badge = 'Tactical_Operator_expert';
        } else {
            // Fallback based on accuracy if mali > 3
            if ($accuracy >= 90) {
                $badge = 'fast_learner_expert';
            } elseif ($accuracy >= 80) {
                $badge = 'moderate_mover_expert';
            } else {
                $badge = 'slow_learner_expert';
            }
        }
    }
}






// 6. Insert into user_badges

try {

$badgeImage = strtolower($badge) . '.jpg';

$stmt = $pdo->prepare("
    INSERT INTO user_badges 
        (user_id, grade, level, badge, badge_image, correct_count, wrong_count, times_up_count, accuracy, earned_at)
    VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
");

$stmt->execute([
    $user_id, $grade, $level, $badge, $badgeImage,
    $correct_count, $wrong_count, $times_up, round($accuracy, 2)
]);




    echo json_encode([

        'success' => true,

        'session_id' => $session_id,

        'badge' => $badge,

        'accuracy' => round($accuracy, 2),

        'correct' => $correct_count,

        'wrong' => $wrong_count,

        'times_up' => $times_up

    ]);

} catch (PDOException $e) {

    error_log("Insert error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode(['error' => 'Database insert failed']);

}

