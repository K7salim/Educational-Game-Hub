<?php
// Initialize session
session_start();
require_once 'db_connect.php';

// Simple user setup (no database dependency for user)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'User';
} else {
    $user_id = 1;
    $user_name = 'Guest';
}

// Check difficulty
$difficulty_id = 1; // Default to Easy
if (isset($_GET['difficulty']) && in_array((int)$_GET['difficulty'], [1, 2, 3, 4])) {
    $difficulty_id = (int)$_GET['difficulty'];
}

// Simple difficulty setup (no database needed)
$difficulty_names = ['Easy', 'Medium', 'Hard', 'Expert'];
$difficulty = [
    'id' => $difficulty_id,
    'name' => $difficulty_names[min(max($difficulty_id - 1, 0), 3)]
];

// Define max levels for each difficulty
$max_levels = [
    1 => 30,  // Easy
    2 => 50,  // Medium
    3 => 100, // Hard
    4 => 200  // Expert
];

// Define time limits for each difficulty
$time_limits = [
    1 => [3, 6, 8, 10],   // Easy: 3min, 6min, 8min, 10min
    2 => [3, 6, 8, 10],   // Medium: 3min, 6min, 8min, 10min
    3 => [5, 8, 10, 12],  // Hard: 5min, 8min, 10min, 12min
    4 => [5, 8, 10, 15]   // Expert: 5min, 8min, 10min, 15min
];

// Define grid sizes for each difficulty
$grid_sizes = [
    1 => [4, 6, 9],    // Easy: 4x4, 6x6, 9x9
    2 => [4, 6, 9],    // Medium: 4x4, 6x6, 9x9
    3 => [9],          // Hard: only 9x9
    4 => [9]           // Expert: only 9x9
];

// Get completed levels and best scores from database
$completed_levels = [];
$best_scores = [];

try {
    // Get completed levels
    $stmt = $pdo->prepare("
        SELECT l.level_number, up.best_time, up.best_score, up.attempts
        FROM user_progress up
        JOIN levels l ON up.level_id = l.id
        WHERE up.user_id = ? AND l.difficulty_id = ? AND up.completed = TRUE
    ");
    $stmt->execute([$user_id, $difficulty_id]);
    $progress = $stmt->fetchAll();
    
    foreach ($progress as $p) {
        $completed_levels[$p['level_number']] = true;
        $best_scores[$p['level_number']] = [
            'time' => $p['best_time'],
            'score' => $p['best_score'],
            'attempts' => $p['attempts']
        ];
    }
} catch (PDOException $e) {
    // If database error, fall back to session data
    $completed_levels = $_SESSION['completed_levels'] ?? [];
    $best_scores = $_SESSION['best_scores'] ?? [];
}

// Get current time limit from session or default to first option
$current_time_limit = $_SESSION['time_limits'][$difficulty_id] ?? $time_limits[$difficulty_id][0];

// Get current grid size from session or default to 9x9
$current_grid_size = $_SESSION['grid_sizes'][$difficulty_id] ?? 9;

// Get completed levels for current difficulty
$level_stars = [];
$highest_unlocked = 0;

foreach ($completed_levels as $level_num => $completed) {
    $highest_unlocked = max($highest_unlocked, (int)$level_num);
    
    // Get stars for completed level
    $score = $best_scores[$level_num]['score'] ?? 0;
    if ($score >= 1000) {
        $level_stars[$level_num] = 3;
    } elseif ($score >= 600) {
        $level_stars[$level_num] = 2;
    } else {
        $level_stars[$level_num] = 1;
    }
}

// Always allow access to the next level after the highest completed
$next_unlocked = $highest_unlocked + 1;

// If no levels are completed, start from level 1
if ($highest_unlocked === 0) {
    $next_unlocked = 1;
}

// Ensure next_unlocked doesn't exceed max levels
$next_unlocked = min($next_unlocked, $max_levels[$difficulty_id]);

$total_stars = array_sum($level_stars);
$current_max_levels = $max_levels[$difficulty_id];
$max_possible_stars = $current_max_levels * 3;
$progress_percentage = $max_possible_stars > 0 ? ($total_stars / $max_possible_stars) * 100 : 0;

// Difficulty styling
$difficulty_classes = [
    1 => ['class' => 'easy', 'icon' => 'fas fa-child', 'color' => '#4CAF50'],
    2 => ['class' => 'medium', 'icon' => 'fas fa-user', 'color' => '#FFC107'],
    3 => ['class' => 'hard', 'icon' => 'fas fa-user-graduate', 'color' => '#F44336'],
    4 => ['class' => 'expert', 'icon' => 'fas fa-brain', 'color' => '#3F51B5']
];

$current_difficulty = $difficulty_classes[$difficulty_id];

// Helper functions
function getLevelState($level_num, $completed_levels, $next_unlocked) {
    if (isset($completed_levels[$level_num])) {
        return 'completed';
    } elseif ($level_num <= $next_unlocked) {
        return 'unlocked';
    } else {
        return 'locked';
    }
}

function getStarsHtml($stars) {
    $html = '';
    for ($i = 1; $i <= 3; $i++) {
        if ($i <= $stars) {
            $html .= '<i class="fas fa-star"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    return $html;
}

function getLevelLink($level_num, $difficulty_id, $state) {
    if ($state === 'locked') {
        return 'javascript:void(0)';
    }
    return "game.php?difficulty={$difficulty_id}&level={$level_num}";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku - <?php echo htmlspecialchars($difficulty['name']); ?> Levels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #10b981;         /* #00ff00 */
            --dark-color: #065f46;            /* Dark #00ff00 */
            --light-color: #d1fae5;           /* Light #00ff00 */
            --accent-color: #34d399;          /* Medium #00ff00 */
            --black: #111827;                 
            --darker-black: #030712;
            --dark-gray: #4b5563;
            --light-gray: #f3f4f6;
            --white: #ffffff;
            --error-color: #ef4444;
            
            /* Cell colors */
            --cell-bg: #ffffff;               /* White cell background */
            --cell-border: #cbd5e1;           /* Light gray cell border */
            --text-dark: #111827;             /* Dark text */
            --text-light: #6b7280;            /* Light text */
            --fixed-cell-color: var(--dark-color);
            --fixed-cell-bg: #ecfdf5;         /* Very light #00ff00 background */
            --selected-cell-bg: #a7f3d0;      /* Light #00ff00 highlight */
            
            /* Difficulty level colors */
            --easy-color: #34d399;
            --medium-color: #10b981;
            --hard-color: #059669;
            --expert-color: #047857;
            
            /* Background */
            --bg-gradient-start: #f0fdf4;
            --bg-gradient-end: #dcfce7;
            --card-bg: #ffffff;
            --card-highlight: #f8fafc;
            --shadow-soft: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 8px 20px rgba(0, 0, 0, 0.15);
            --glow: 0 0 15px rgba(16, 185, 129, 0.5);
            
            /* Border radius */
            --border-radius: 8px;
            --border-radius-lg: 12px;
            --transition-normal: all 0.3s ease;
            --transition-fast: all 0.2s ease;
        }

        /* Base Styles */
        * {
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #014742;
            color: #111827;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Header Styles */
        header {
            border-radius: 10px;
            background: black;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            color: #ffffff;
            margin: 10px;
        }

        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 20%);
            z-index: 1;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
            padding: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
        }

        .back-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #003929;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-left: -30px;
        }

        .back-btn:hover {
            background: #007b58;
            transform: scale(1.1);
        }

        .back-btn i {
            font-size: 35px;
        }

        .difficulty-header {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .difficulty-icon {
            font-size: 2.5em;
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .difficulty-info h1 {
            font-size: 28px;
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            background: rgb(0 255 245 / 10%);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            margin-left: -50px;
            border-radius: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            border: 2px solid #00fffc;
        }

        /* Difficulty Tab Styles */
        .difficulty-tabs {
            display: flex;
            gap: 10px;
            margin: 20px 10px;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: #99fffe;
        }

        .tab {
            flex: 1;
            padding: 16px 24px;
            text-align: center;
            background: #002f37;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border: 2px solid transparent;
            text-decoration: none;
            min-height: 60px;
            font-size: 18px;
        }

        .tab i {
            font-size: 28px;
            transition: all 0.2s ease;
        }

        /* Different colors for each difficulty */
        .tab[href*='difficulty=1'] {
            color: #00ff00;
        }

        .tab[href*='difficulty=2'] {
            color: #017cff;
        }

        .tab[href*='difficulty=3'] {
            color: orange;
        }

        .tab[href*='difficulty=4'] {
            color: red;
        }

        /* Hover states */
        .tab[href*='difficulty=1']:hover {
            background: #00ff00;
            color: black;
        }

        .tab[href*='difficulty=2']:hover {
            background: #017cff;
            color: black;
        }

        .tab[href*='difficulty=3']:hover {
            background: orange;
            color: black;
        }

        .tab[href*='difficulty=4']:hover {
            background: red;
            color: black;
        }

        /* Active states */
        .tab[href*='difficulty=1'].active {
            background: #00ff00;
            color: black;
        }

        .tab[href*='difficulty=2'].active {
            background: #017cff;
            color: black;
        }

        .tab[href*='difficulty=3'].active {
            background: orange;
            color: black;
        }

        .tab[href*='difficulty=4'].active {
            background: red;
            color: black;
        }

        /* Card Styles */
        .card {
            background: #c5fffe;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin: 10px;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-header h3 i {
            color: #10b981;
        }

        /* Progress Bar */
        .progress-container {
            margin-bottom: 15px;
        }

        .progress-bar {
            background: rgba(0, 0, 0, 0.05);
            height: 10px;
            border-radius: 10px;
            overflow: hidden;
            margin: 15px 0;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            width: 0;
            border-radius: 10px;
            position: relative;
            transition: width 1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                        transparent, 
                        rgba(255, 255, 255, 0.2), 
                        transparent);
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Level Grid */
        .level-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 12px;
            padding: 5px 0;
            position: relative;
        }

        .level-card {
            aspect-ratio: 1/1;
            border-radius: 8px;
            border: 1px solid black;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
            background: #f8fafc;
        }

        .level-completed {
            background: linear-gradient(145deg, var(--card-highlight), rgba(16, 185, 129, 0.1));
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.1);
        }

        .level-unlocked {
            background: #f8fafc;
            border: 2px solid #10b981;
        }

        .level-locked {
            background: rgba(0, 0, 0, 0.05);
            opacity: 0.9;
            cursor: not-allowed;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .level-locked::before {
            content: '\f023';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            font-size: 32px;
            color: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .level-locked .level-number {
            position: absolute;
            font-size: 16px;
            color: rgba(0, 0, 0, 0.5);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            margin: 0;
        }

        .level-card:hover:not(.level-locked) {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .level-number {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .level-stars {
            color: #FFC107;
            font-size: 14px;
            position: relative;
            z-index: 1;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .level-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .difficulty-header {
                flex-direction: column;
            }
            
            .tab {
                font-size: 14px;
            }
            
            .tab i {
                font-size: 18px;
            }
        }

        @media (max-width: 480px) {
            .level-grid {
                grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            }
            
            .level-number {
                font-size: 18px;
            }
            
            .level-stars {
                font-size: 12px;
            }
            
            .user-info {
                padding: 5px 10px;
            }
            
            .user-avatar {
                width: 30px;
                height: 30px;
            }
            
            .user-info span {
                font-size: 14px;
            }
            
            .difficulty-icon {
                width: 45px;
                height: 45px;
                font-size: 1.8em;
            }
            
            .difficulty-info h1 {
                font-size: 22px;
            }
        }

        /* Time Limit Selector Styles */
        .time-limit-selector {
            background: #002f37;
            padding: 15px;
            border-radius: 8px;
            margin: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .time-limit-selector h3 {
            color: white;
            margin: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .time-limit-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .time-option {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .time-option:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .time-option.selected {
            background: #00ff00;
            color: black;
            border-color: white;
        }

        /* Grid Size Selector Styles */
        .grid-size-selector {
            background: #002f37;
            padding: 15px;
            border-radius: 8px;
            margin: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .grid-size-selector h3 {
            color: white;
            margin: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .grid-size-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .grid-option {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .grid-option:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .grid-option.selected {
            background: #00ff00;
            color: black;
            border-color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <a href="index.php" class="back-btn" title="Back to Home">
                        <i class="fas fa-home"></i>
                    </a>
                    <div class="difficulty-header">
                        <div class="difficulty-icon">
                            <i class="<?php echo $current_difficulty['icon']; ?>"></i>
                        </div>
                        <div class="difficulty-info">
                            <h1><?php echo htmlspecialchars($difficulty['name']); ?> Mode</h1>
                        </div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="difficulty-tabs">
            <a href="levels.php?difficulty=1" class="tab easy <?php echo $difficulty_id == 1 ? 'active' : ''; ?>">
                <i class="fas fa-child"></i>
                Easy
            </a>
            <a href="levels.php?difficulty=2" class="tab medium <?php echo $difficulty_id == 2 ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                Medium
            </a>
            <a href="levels.php?difficulty=3" class="tab hard <?php echo $difficulty_id == 3 ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                Hard
            </a>
            <a href="levels.php?difficulty=4" class="tab expert <?php echo $difficulty_id == 4 ? 'active' : ''; ?>">
                <i class="fas fa-brain"></i>
                Expert
            </a>
        </div>
        
        <div class="time-limit-selector">
            <h3><i class="fas fa-clock"></i> Time Limit</h3>
            <div class="time-limit-options">
                <?php foreach ($time_limits[$difficulty_id] as $limit): ?>
                    <div class="time-option <?php echo $limit === $current_time_limit ? 'selected' : ''; ?>"
                         onclick="setTimeLimit(<?php echo $limit; ?>)">
                        <?php echo $limit; ?> min
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (in_array($difficulty_id, [1, 2])): ?>
        <div class="grid-size-selector">
            <h3><i class="fas fa-th"></i> Grid Size</h3>
            <div class="grid-size-options">
                <?php foreach ($grid_sizes[$difficulty_id] as $size): ?>
                    <div class="grid-option <?php echo $size === $current_grid_size ? 'selected' : ''; ?>"
                         onclick="setGridSize(<?php echo $size; ?>)">
                        <?php echo $size; ?>x<?php echo $size; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-trophy"></i> Progress</h3>
                <div class="badge badge-highlight">
                    <i class="fas fa-star"></i> <?php echo $total_stars; ?> stars
                </div>
            </div>
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
                </div>
                <div class="progress-stats">
                    <span><?php echo count($completed_levels); ?> of <?php echo $current_max_levels; ?> levels completed</span>
                    <span><?php echo round($progress_percentage); ?>%</span>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-layer-group"></i> Levels</h3>
                <div class="badge">
                    <?php echo $next_unlocked; ?> unlocked
                </div>
            </div>
            
            <?php 
            $levels_per_page = 40;
            $total_pages = ceil($current_max_levels / $levels_per_page);
            $need_pagination = ($difficulty_id > 1 && $total_pages > 1);
            ?>
            
            <?php if ($need_pagination): ?>
                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <div class="level-page <?php echo ($page === 1) ? 'active' : ''; ?>" data-page="<?php echo $page; ?>">
                        <div class="level-grid">
                            <?php 
                            $start_level = (($page - 1) * $levels_per_page) + 1;
                            $end_level = min($page * $levels_per_page, $current_max_levels);
                            
                            for ($i = $start_level; $i <= $end_level; $i++): 
                                $level_state = getLevelState($i, $completed_levels, $next_unlocked);
                                $level_stars_count = $level_stars[$i] ?? 0;
                            ?>
                                <div class="level-card level-<?php echo $level_state; ?>" 
                                     data-level="<?php echo $i; ?>"
                                     <?php if ($level_state !== 'locked'): ?>
                                     onclick="window.location.href='<?php echo getLevelLink($i, $difficulty_id, $level_state); ?>'"
                                     <?php endif; ?>>
                                    <div class="level-number"><?php echo $i; ?></div>
                                    <?php if ($level_state === 'completed'): ?>
                                        <div class="level-stars">
                                            <?php echo getStarsHtml($level_stars_count); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endfor; ?>
                
                <div class="level-navigation">
                    <div class="level-nav-btn prev-btn disabled" onclick="changeLevelPage(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <div class="level-nav-page">Page <span id="current-page">1</span> of <?php echo $total_pages; ?></div>
                    <div class="level-nav-btn next-btn" onclick="changeLevelPage(1)">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            <?php else: ?>
                <div class="level-grid">
                    <?php for ($i = 1; $i <= $current_max_levels; $i++): ?>
                        <?php $level_state = getLevelState($i, $completed_levels, $next_unlocked); ?>
                        <?php $level_stars_count = $level_stars[$i] ?? 0; ?>
                        
                        <div class="level-card level-<?php echo $level_state; ?>" 
                             data-level="<?php echo $i; ?>"
                             <?php if ($level_state !== 'locked'): ?>
                             onclick="window.location.href='<?php echo getLevelLink($i, $difficulty_id, $level_state); ?>'"
                             <?php endif; ?>>
                            <div class="level-number"><?php echo $i; ?></div>
                            <?php if ($level_state === 'completed'): ?>
                                <div class="level-stars">
                                    <?php echo getStarsHtml($level_stars_count); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Level pagination
            window.currentLevelPage = 1;
            window.totalLevelPages = <?php echo $need_pagination ? $total_pages : 1; ?>;
            
            window.changeLevelPage = function(direction) {
                const newPage = window.currentLevelPage + direction;
                
                if (newPage < 1 || newPage > window.totalLevelPages) {
                    return;
                }
                
                document.querySelector(`.level-page[data-page="${window.currentLevelPage}"]`).classList.remove('active');
                window.currentLevelPage = newPage;
                document.querySelector(`.level-page[data-page="${window.currentLevelPage}"]`).classList.add('active');
                document.getElementById('current-page').textContent = window.currentLevelPage;
                
                const prevBtn = document.querySelector('.prev-btn');
                const nextBtn = document.querySelector('.next-btn');
                
                prevBtn.classList.toggle('disabled', window.currentLevelPage === 1);
                nextBtn.classList.toggle('disabled', window.currentLevelPage === window.totalLevelPages);
            };
            
            // Animate progress bar
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                setTimeout(() => {
                    progressFill.style.width = '<?php echo $progress_percentage; ?>%';
                }, 300);
            }
            
            // Locked level feedback
            const lockedLevels = document.querySelectorAll('.level-locked');
            lockedLevels.forEach(level => {
                level.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.style.animation = 'shake 0.5s ease-in-out';
                    setTimeout(() => {
                        this.style.animation = '';
                    }, 500);
                });
            });
        });

        function setTimeLimit(minutes) {
            // Update selected state
            document.querySelectorAll('.time-option').forEach(option => {
                option.classList.remove('selected');
            });
            event.target.classList.add('selected');

            // Save to session via AJAX
            fetch('set_time_limit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `difficulty=<?php echo $difficulty_id; ?>&minutes=${minutes}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update all level links to include the time limit
                    document.querySelectorAll('.level-card').forEach(card => {
                        const link = card.getAttribute('href');
                        if (link && link !== 'javascript:void(0)') {
                            card.setAttribute('href', link + `&time_limit=${minutes}`);
                        }
                    });
                }
            });
        }

        function setGridSize(size) {
            // Update selected state
            document.querySelectorAll('.grid-option').forEach(option => {
                option.classList.remove('selected');
            });
            event.target.classList.add('selected');

            // Save to session via AJAX
            fetch('set_grid_size.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `difficulty=<?php echo $difficulty_id; ?>&size=${size}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update all level links to include the grid size
                    document.querySelectorAll('.level-card').forEach(card => {
                        const link = card.getAttribute('href');
                        if (link && link !== 'javascript:void(0)') {
                            card.setAttribute('href', link + `&grid_size=${size}`);
                        }
                    });
                }
            });
        }
    </script>
    
    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</body>
</html>