<?php
// Initialize session
session_start();

// Simple user setup (no database dependency for user)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'User';
} else {
    $user_id = 1;
    $user_name = 'Guest';
}

// Get difficulty and level from URL
$difficulty_id = isset($_GET['difficulty']) ? (int)$_GET['difficulty'] : 1;
$level = isset($_GET['level']) ? (int)$_GET['level'] : 1;

// Get time limit from URL or session
$time_limit = isset($_GET['time_limit']) ? (int)$_GET['time_limit'] : 
              ($_SESSION['time_limits'][$difficulty_id] ?? 3); // Default to 3 minutes

// Get grid size from URL or session
$grid_size = isset($_GET['grid_size']) ? (int)$_GET['grid_size'] : 
             ($_SESSION['grid_sizes'][$difficulty_id] ?? 9);

// Validate difficulty
if (!in_array($difficulty_id, [1, 2, 3, 4])) {
    $difficulty_id = 1;
}

// Validate grid size
if (!in_array($grid_size, [4, 6, 9])) {
    $grid_size = 9;
}

// Simple difficulty setup (no database needed)
$difficulty_names = ['Easy', 'Medium', 'Hard', 'Expert'];
$difficulty = [
    'id' => $difficulty_id,
    'name' => $difficulty_names[min(max($difficulty_id - 1, 0), 3)]
];

// Initialize completed levels in session if not exists
if (!isset($_SESSION['completed_levels'])) {
    $_SESSION['completed_levels'] = [];
}

// Generate puzzle based on difficulty and level
function generatePuzzle($difficulty_id, $level, $grid_size) {
    // Base complete sudoku grid based on size
    $complete_grid = [];
    
    if ($grid_size === 4) {
        // 4x4 grid
        $complete_grid = [
            [1, 2, 3, 4],
            [3, 4, 1, 2],
            [2, 1, 4, 3],
            [4, 3, 2, 1]
        ];
    } elseif ($grid_size === 6) {
        // 6x6 grid
        $complete_grid = [
            [1, 2, 3, 4, 5, 6],
            [4, 5, 6, 1, 2, 3],
            [2, 3, 1, 5, 6, 4],
            [5, 6, 4, 2, 3, 1],
            [3, 1, 2, 6, 4, 5],
            [6, 4, 5, 3, 1, 2]
        ];
    } else {
        // 9x9 grid (original)
        $complete_grid = [
            [5,3,4,6,7,8,9,1,2],
            [6,7,2,1,9,5,3,4,8],
            [1,9,8,3,4,2,5,6,7],
            [8,5,9,7,6,1,4,2,3],
            [4,2,6,8,5,3,7,9,1],
            [7,1,3,9,2,4,8,5,6],
            [9,6,1,5,3,7,2,8,4],
            [2,8,7,4,1,9,6,3,5],
            [3,4,5,2,8,6,1,7,9]
        ];
    }
    
    // Shuffle the grid based on level for variation
    $seed = $difficulty_id * 1000 + $level;
    mt_srand($seed);
    
    // Determine how many cells to remove based on difficulty and grid size
    $cells_to_remove = [
        1 => $grid_size === 4 ? 8 + ($level % 3) : // Easy 4x4: 8-10 cells
             ($grid_size === 6 ? 20 + ($level % 5) : // Easy 6x6: 20-24 cells
             32 + ($level % 5)), // Easy 9x9: 32-36 cells
        2 => $grid_size === 4 ? 10 + ($level % 3) : // Medium 4x4: 10-12 cells
             ($grid_size === 6 ? 24 + ($level % 5) : // Medium 6x6: 24-28 cells
             37 + ($level % 8)), // Medium 9x9: 37-44 cells
        3 => 42 + ($level % 10), // Hard: 42-51 cells
        4 => 47 + ($level % 10)  // Expert: 47-56 cells
    ];
    
    $remove_count = $cells_to_remove[$difficulty_id];
    
    // Create puzzle by removing cells
    $puzzle = $complete_grid;
    $removed = 0;
    $attempts = 0;
    
    while ($removed < $remove_count && $attempts < 100) {
        $row = mt_rand(0, $grid_size - 1);
        $col = mt_rand(0, $grid_size - 1);
        
        if ($puzzle[$row][$col] != 0) {
            $puzzle[$row][$col] = 0;
            $removed++;
        }
        $attempts++;
    }
    
    return [
        'puzzle' => $puzzle,
        'solution' => $complete_grid
    ];
}

$game_data = generatePuzzle($difficulty_id, $level, $grid_size);
$puzzle = $game_data['puzzle'];
$solution = $game_data['solution'];

// Handle score saving via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_score'])) {
    require_once 'db_connect.php';
    
    $score_key = "{$difficulty_id}_{$level}";
    $time = (int)$_POST['time_taken'];
    $score = (int)$_POST['score'];
    
    try {
        // Get level ID from database using difficulty_id instead of name
        $stmt = $pdo->prepare("SELECT id FROM levels WHERE difficulty_id = ? AND level_number = ?");
        $stmt->execute([$difficulty_id, $level]);
        $level_data = $stmt->fetch();
        
        $level_id = null;
        
        if ($level_data) {
            $level_id = $level_data['id'];
        } else {
            // If level doesn't exist in database, create it
            $stmt = $pdo->prepare("
                INSERT INTO levels (difficulty_id, level_number, grid_size, time_limit, puzzle_data, solution_data)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $difficulty_id,
                $level,
                $grid_size,
                $time_limit * 60,
                json_encode($puzzle),
                json_encode($solution)
            ]);
            
            $level_id = $pdo->lastInsertId();
        }
        
        // Now save the progress
        $stmt = $pdo->prepare("
            INSERT INTO user_progress (user_id, level_id, completed, best_time, best_score, attempts)
            VALUES (?, ?, TRUE, ?, ?, 1)
            ON DUPLICATE KEY UPDATE 
                completed = TRUE,
                best_time = LEAST(best_time, ?),
                best_score = GREATEST(best_score, ?),
                attempts = attempts + 1
        ");
        $stmt->execute([$user_id, $level_id, $time, $score, $time, $score]);
        
        // Add to leaderboard
        $stmt = $pdo->prepare("
            INSERT INTO leaderboard (user_id, level_id, score, time_taken)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $level_id, $score, $time]);
        
        // Update session for immediate feedback
        $_SESSION['completed_levels'][$score_key] = true;
    
    echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku - <?php echo htmlspecialchars($difficulty['name']); ?> Level <?php echo $level; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            /* Primary Color Palette */
            --primary-color: #10b981;         /* Main Green */
            --dark-color: #065f46;            /* Dark Green */
            --light-color: #d1fae5;           /* Light Green */
            --accent-color: #34d399;          /* Medium Green */
            
            /* Neutral Colors */
            --black: #111827;
            --darker-black: #030712;
            --dark-gray: #4b5563;
            --light-gray: #f3f4f6;
            --white: #ffffff;
            
            /* State Colors */
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --success-color: var(--primary-color);
            
            /* Background System */
            --bg-primary: linear-gradient(135deg, var(--darker-black) 0%, var(--black) 50%, #1f2937 100%);
            --bg-card: rgba(255, 255, 255, 0.05);
            --bg-glass: rgba(255, 255, 255, 0.08);
            --bg-hover: rgba(16, 185, 129, 0.1);
            
            /* Border System */
            --border-primary: rgba(255, 255, 255, 0.1);
            --border-accent: rgba(16, 185, 129, 0.3);
            --border-light: rgba(255, 255, 255, 0.05);
            
            /* Text System */
            --text-primary: var(--white);
            --text-secondary: #e5e7eb;
            --text-muted: #9ca3af;
            --text-accent: var(--primary-color);
            
            /* Shadow System */
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 8px 25px rgba(0, 0, 0, 0.2);
            --shadow-glow: 0 0 20px rgba(16, 185, 129, 0.3);
            
            /* Spacing System */
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;
            
            /* Border Radius System */
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 50px;
            
            /* Transition System */
            --transition-fast: 0.15s ease;
            --transition-normal: 0.3s ease;
            --transition-slow: 0.5s ease;
        }

        body{
            background-color: #014742;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        header {
            background: black;
            padding: 20px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            color: #ffffff;
            border-radius: 10px;
            margin: 10px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-btn {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateX(-3px);
        }

        .back-btn[href*="dashboard"]:hover {
            transform: translateY(-3px);
        }

        .level-info h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
        }

        .header-right .user-info {
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

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .game-container {
            display: grid;
            grid-template-columns: minmax(320px, 2fr) 1fr;
            gap: 30px;
        }

        /* Sudoku Board */
        .sudoku-wrapper {
            background-color: #caeeed;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }

        .sudoku-wrapper:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .sudoku-board {
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            grid-template-rows: repeat(9, 1fr);
            gap: 0;
            width: 100%;
            max-width: 540px;
            margin: 0 auto;
            position: relative;
            aspect-ratio: 1/1;
            background-color: #cbd5e1;
            border: 2px solid #065f46;
            border-radius: 4px;
            overflow: hidden;
        }

        /* Cell styling */
        .cell {
            background-color: #ffffff;
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(1.2rem, 2.5vw, 1.8rem);
            font-weight: 600;
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #111827;
            border: 1px solid #cbd5e1;
        }

        /* 4x4 and 6x6 box borders */
        .cell[data-col="2"], .cell[data-col="4"] {
            border-left: 2px solid black;
        }

        .cell[data-row="2"], .cell[data-row="4"] {
            border-top: 2px solid black;
        }

        /* 9x9 specific borders */
        .sudoku-board[style*="grid-template-columns: repeat(9, 1fr)"] .cell[data-col="3"],
        .sudoku-board[style*="grid-template-columns: repeat(9, 1fr)"] .cell[data-col="6"] {
            border-left: 2px solid black;
        }

        .sudoku-board[style*="grid-template-columns: repeat(9, 1fr)"] .cell[data-row="3"],
        .sudoku-board[style*="grid-template-columns: repeat(9, 1fr)"] .cell[data-row="6"] {
            border-top: 2px solid black;
        }

        /* Make borders thicker for 9x9 */
        .sudoku-board[style*="grid-template-columns: repeat(9, 1fr)"] {
            border: 3px solid black;
        }

        .sudoku-board[style*="grid-template-columns: repeat(9, 1fr)"] .cell {
            border: 0.5px solid #666;
        }

        /* Fixed cells styling */
        .cell.fixed {
            color: #003b2a;
            font-weight: 700;
            background-color: #a3f0cc;
        }

        /* Selected cell styling */
        .cell.selected {
            background-color: #a7f3d0;
            box-shadow: inset 0 0 0 2px black;
            transform: scale(1.05);
            transition: all 0.2s ease;
        }

        .cell.error {
            color: #ef4444;
            animation: shake 0.5s;
        }

        .cell.hint {
            animation: pulse 1s;
            color: #34d399;
        }

        /* Same number highlighting */
        .cell.same-number {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .cell.selected.same-number {
            background-color: #fbbf24;
            color: #78350f;
            transform: scale(1.05);
        }

        /* Remove related cell highlighting styles */
        .cell.same-row, .cell.same-col, .cell.same-box, .cell.same-number {
            background-color: inherit;
        }

        /* Notes Grid */
        .notes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            font-size: 0.7em;
        }

        .note {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
        }

        /* Game Controls */
        .game-controls {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .control-panel {
            background-color: #caeeed;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .control-panel:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 10px;
        }

        .panel-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #111827;
        }

        .timer {
            font-size: 1.2rem;
            font-weight: 700;
            color: black;
        }

        .game-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: black;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-value i {
            font-size: 1.8rem;
        }

        .stat-value i.fa-times-circle {
            color: #ef4444;
        }

        .stat-value i.fa-lightbulb {
            color: #f59e0b;
        }

        .stat-value i.fa-star {
            color: #f59e0b;
        }

        .stat-value span {
            min-width: 1.5em;
            text-align: center;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
        }

        /* Numpad */
        .numpad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .num-btn {
            background-color: #f3f4f6;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: #111827;
            font-size: 1.5rem;
            font-weight: 600;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .num-btn:hover {
            background-color: #d1fae5;
            color: black;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Control Buttons */
        .control-btns {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .control-btn {
            background-color: #f3f4f6;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: #111827;
            font-size: 1rem;
            font-weight: 500;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .control-btn:hover {
            background-color: #d1fae5;
            color: black;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .control-btn.selected {
            background-color: #10b981;
            color: white;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.5);
        }

        #restart-btn {
            background-color: #fee2e2;
            color: #ef4444;
        }

        #restart-btn:hover {
            background-color: #fecaca;
            color: #dc2626;
        }

        #save-btn {
            background-color: #dbeafe;
            color: #3b82f6;
        }

        #save-btn:hover {
            background-color: #bfdbfe;
            color: #2563eb;
        }

        /* Best Scores */
        .best-scores {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .best-time, .best-score {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }

        /* Leaderboard */
        .leaderboard-list {
            list-style-type: none;
            margin-top: 10px;
            padding: 0;
        }

        .leaderboard-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 5px;
            background-color: #f3f4f6;
            border-radius: 8px;
            transition: all 0.2s ease;
            border: 1px solid #cbd5e1;
        }

        .leaderboard-item:hover {
            background-color: #d1fae5;
            transform: translateX(3px);
        }

        .leaderboard-rank {
            font-weight: 700;
            color: black;
            margin-right: 10px;
        }

        .leaderboard-name {
            flex: 1;
            color: #111827;
        }

        .leaderboard-score {
            font-weight: 600;
            color: black;
        }

        .leaderboard-item.empty {
            justify-content: center;
            color: #6b7280;
            opacity: 0.7;
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background-color: #caeeed;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(20px);
            transition: all 0.3s ease;
            border: 1px solid #cbd5e1;
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0);
        }

        .completion-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .completion-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: black;
            margin-bottom: 10px;
        }

        .star-rating {
            display: flex;
            justify-content: center;
            gap: 5px;
            font-size: 1.5rem;
            color: gold;
            margin-bottom: 20px;
        }

        .completion-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .modal-buttons {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .modal-btn {
            flex: 1;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .primary-btn {
            background-color: black;
            color: #ffffff;
        }

        .secondary-btn {
            background-color: #f3f4f6;
            color: #111827;
            border: 1px solid #cbd5e1;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Grid highlight effect */
        .cell:hover:not(.fixed) {
            background-color: #d1fae5;
            box-shadow: inset 0 0 0 1px black;
        }

        /* Animations */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }

        /* Responsive design */
        @media (max-width: 1024px) {
            .game-container {
                grid-template-columns: 1fr;
            }
            
            .sudoku-wrapper {
                margin-bottom: 20px;
            }
            
            .control-panel {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 600px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .sudoku-board {
                max-width: 100%;
            }
            
            .num-btn {
                font-size: 1.2rem;
                padding: 8px;
            }
            
            .control-btn {
                font-size: 0.9rem;
                padding: 10px;
            }
            
            .control-btn span {
                display: none;
            }
            
            .modal-content {
                padding: 20px;
            }
        }

        /* Success message */
        .success-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #10b981;
            color: white;
            padding: 20px;
            border-radius: 8px;
            font-weight: 600;
            z-index: 1001;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .success-message.show {
            opacity: 1;
        }

        /* Add these styles in the CSS section */
        .badge {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge-highlight {
            background: linear-gradient(45deg, rgba(255, 215, 0, 0.2), rgba(255, 193, 7, 0.2));
            color: #f57c00;
        }

        .control-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="header-left">
                <div class="header-buttons">
                    <a href="levels.php?difficulty=<?php echo $difficulty_id; ?>" class="back-btn" title="Back to Levels">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <a href="index.php" class="back-btn" title="Home">
                        <i class="fas fa-home"></i>
                    </a>
                </div>
                <div class="level-info">
                    <h1><?php echo htmlspecialchars($difficulty['name']); ?> - Level <?php echo $level; ?></h1>
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
    </header>

    <div class="main-container">
        <div class="game-container">
            <div class="sudoku-wrapper">
                <div class="sudoku-board" id="sudoku-board">
                    <!-- Sudoku cells will be generated by JavaScript -->
                </div>
            </div>

            <div class="game-controls">
                <!-- Timer and Stats Panel -->
                <div class="control-panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <i class="fas fa-clock"></i> Game Stats
                        </div>
                        <div class="timer" id="timer">00:00</div>
                    </div>
                    <div class="game-stats">
                        <div class="stat-item">
                            <div class="stat-value">
                                <i class="fas fa-times-circle"></i>
                                <span id="mistakes">0</span>/5
                            </div>
                            <div class="stat-label">Mistakes</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <i class="fas fa-lightbulb"></i>
                                <span id="hints-used">0</span>/5
                            </div>
                            <div class="stat-label">Hints</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                <i class="fas fa-star"></i>
                                <span id="score">0</span>
                            </div>
                            <div class="stat-label">Score</div>
                        </div>
                    </div>
                </div>

                <!-- Number Input Panel -->
                <div class="control-panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <i class="fas fa-keyboard"></i> Numbers
                        </div>
                    </div>
                    <div class="numpad">
                        <?php for ($i = 1; $i <= 9; $i++): ?>
                            <button class="num-btn" data-number="<?php echo $i; ?>"><?php echo $i; ?></button>
                        <?php endfor; ?>
                    </div>
                    <div class="control-btns">
                        <button class="control-btn" id="erase-btn">
                            <i class="fas fa-eraser"></i>
                            <span>Erase</span>
                        </button>
                        <button class="control-btn" id="hint-btn">
                            <i class="fas fa-lightbulb"></i>
                            <span>Hint</span>
                        </button>
                        <button class="control-btn" id="check-btn">
                            <i class="fas fa-check"></i>
                            <span>Check</span>
                        </button>
                        <button class="control-btn" id="notes-btn">
                            <i class="fas fa-pencil-alt"></i>
                            <span>Notes</span>
                        </button>
                        <button class="control-btn" id="restart-btn" onclick="restartLevel()">
                            <i class="fas fa-redo"></i>
                            <span>Restart</span>
                        </button>
                        <button class="control-btn" id="save-btn" onclick="saveProgress()">
                            <i class="fas fa-save"></i>
                            <span>Save</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Modal -->
    <div class="modal-overlay" id="completion-modal">
        <div class="modal-content">
            <div class="completion-header">
                <h2>Level Complete!</h2>
                <div class="star-rating" id="star-rating">
                    <!-- Stars will be added by JavaScript -->
                </div>
            </div>
            <div class="completion-stats">
                <div class="stat-item">
                    <div class="stat-value" id="final-time">--:--</div>
                    <div class="stat-label">Time</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="final-score">---</div>
                    <div class="stat-label">Score</div>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn secondary-btn" onclick="restartLevel()">
                    <i class="fas fa-redo"></i> Try Again </button>
                <button class="modal-btn primary-btn" onclick="nextLevel()">
                    <i class="fas fa-arrow-right"></i> Next Level
                </button>
            </div>
        </div>
    </div>

    <!-- Restart Confirmation Modal -->
    <div class="modal-overlay" id="restart-modal">
        <div class="modal-content">
            <div class="completion-header">
                <h2>Restart Level?</h2>
                <p>Are you sure you want to restart? All progress will be lost.</p>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn secondary-btn" onclick="closeRestartModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="modal-btn primary-btn" onclick="confirmRestart()">
                    <i class="fas fa-redo"></i> Restart
                </button>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <div class="success-message" id="success-message"></div>

    <script>
        // Game state variables
        let gameStarted = false;
        let startTime = null;
        let timerInterval = null;
        let selectedCell = null;
        let mistakes = 0;
        let hintsUsed = 0;
        let score = 0;
        let gameCompleted = false;
        let maxMistakes = 5; // Maximum allowed mistakes
        let notesMode = false; // Track if we're in notes mode
        let timeLimit = <?php echo $time_limit; ?> * 60; // Convert minutes to seconds
        let timeRemaining = timeLimit;
        let solvedCells = new Set(); // Track which cells have been solved

        // Puzzle data from PHP
        const initialPuzzle = <?php echo json_encode($puzzle); ?>;
        const solution = <?php echo json_encode($solution); ?>;
        const currentPuzzle = JSON.parse(JSON.stringify(initialPuzzle)); // Deep copy

        // Game difficulty and level
        const difficultyId = <?php echo $difficulty_id; ?>;
        const currentLevel = <?php echo $level; ?>;

        // Update game state variables
        let gridSize = <?php echo $grid_size; ?>;
        let maxNumber = gridSize;

        // Initialize the game
        document.addEventListener('DOMContentLoaded', function() {
            initializeBoard();
            setupEventListeners();
            updateUI();

            // Check for saved game
            const savedGame = localStorage.getItem('sudoku_save');
            if (savedGame) {
                if (confirm('Would you like to load your saved game?')) {
                    const gameState = JSON.parse(savedGame);
                    currentPuzzle = gameState.puzzle;
                    mistakes = gameState.mistakes;
                    hintsUsed = gameState.hintsUsed;
                    score = gameState.score;
                    
                    // Update the board with saved state
                    updateBoardFromSave();
                    
                    // Start timer from saved time
                    startTime = Date.now() - (gameState.time * 1000);
                    updateTimer();
                } else {
                    // Clear saved game if user chooses not to load it
                    localStorage.removeItem('sudoku_save');
                }
            }
        });

        function initializeBoard() {
            const board = document.getElementById('sudoku-board');
            board.innerHTML = '';
            
            // Update grid template based on size
            board.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;
            board.style.gridTemplateRows = `repeat(${gridSize}, 1fr)`;

            for (let row = 0; row < gridSize; row++) {
                for (let col = 0; col < gridSize; col++) {
                    const cell = document.createElement('div');
                    cell.className = 'cell';
                    cell.dataset.row = row;
                    cell.dataset.col = col;
                    cell.dataset.box = Math.floor(row / Math.sqrt(gridSize)) * Math.sqrt(gridSize) + 
                                     Math.floor(col / Math.sqrt(gridSize));

                    const value = initialPuzzle[row][col];
                    if (value !== 0) {
                        cell.textContent = value;
                        cell.classList.add('fixed');
                    } else {
                        cell.addEventListener('click', selectCell);
                    }

                    board.appendChild(cell);
                }
            }
        }

        function setupEventListeners() {
            // Number buttons
            document.querySelectorAll('.num-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const number = parseInt(this.dataset.number);
                    
                    // Remove previous number highlights
                    document.querySelectorAll('.cell').forEach(cell => {
                        cell.classList.remove('same-number');
                    });
                    
                    // Highlight cells with the same number
                    document.querySelectorAll('.cell').forEach(cell => {
                        if (getCurrentCellValue(cell) === number) {
                            cell.classList.add('same-number');
                        }
                    });
                    
                    if (selectedCell && !selectedCell.classList.contains('fixed')) {
                        if (notesMode) {
                            toggleNote(selectedCell, number);
                        } else {
                            placeNumber(selectedCell, number);
                        }
                    }
                });
            });

            // Control buttons
            document.getElementById('erase-btn').addEventListener('click', eraseCell);
            document.getElementById('hint-btn').addEventListener('click', giveHint);
            document.getElementById('check-btn').addEventListener('click', checkSolution);
            document.getElementById('notes-btn').addEventListener('click', toggleNotesMode);

            // Keyboard input
            document.addEventListener('keydown', handleKeyboard);

            // Add home button event listener
            document.getElementById('home-btn').addEventListener('click', goHome);
        }

        function selectCell(event) {
            if (gameCompleted) return;

            // Remove previous selection
            document.querySelectorAll('.cell').forEach(cell => {
                cell.classList.remove('selected', 'same-row', 'same-col', 'same-box', 'same-number');
            });

            selectedCell = event.target;
            selectedCell.classList.add('selected');

            // Start timer on first cell selection
            if (!gameStarted) {
                startGame();
            }
        }

        function startGame() {
            gameStarted = true;
            startTime = Date.now();
            timerInterval = setInterval(updateTimer, 1000);
        }

        function updateTimer() {
            if (!startTime) return;
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            timeRemaining = timeLimit - elapsed;
            
            if (timeRemaining <= 0) {
                timeRemaining = 0;
                gameOver('Time\'s up!');
            }
            
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function placeNumber(cell, number) {
            const row = parseInt(cell.dataset.row);
            const col = parseInt(cell.dataset.col);
            const cellKey = `${row}-${col}`;
            
            // Check for repetitions in row, column, and box
            if (hasRepetition(row, col, number)) {
                cell.classList.add('error');
                mistakes++;
                
                // Check if mistakes limit reached
                if (mistakes >= 5) {
                    gameOver('Too many mistakes!');
                    return;
                }
                
                score = Math.max(0, score - 50); // Deduct 50 points for each mistake
                updateUI();
                
                // Remove error class after animation
                setTimeout(() => {
                    cell.classList.remove('error');
                }, 500);
                return;
            }
            
            // Update visual
            cell.textContent = number;
            currentPuzzle[row][col] = number;

            // Check if placement is correct
            if (solution[row][col] === number) {
                cell.classList.remove('error');
                
                // Only award points if this cell hasn't been solved before
                if (!solvedCells.has(cellKey)) {
                    score += 50; // Add 50 points for each correct number
                    solvedCells.add(cellKey); // Mark this cell as solved
                    updateUI();
                }
                
                // Check if puzzle is complete
                if (isPuzzleComplete()) {
                    completeGame();
                }
            } else {
                // Wrong placement
                cell.classList.add('error');
                mistakes++;
                
                // Check if mistakes limit reached
                if (mistakes >= 5) {
                    gameOver('Too many mistakes!');
                    return;
                }
                
                score = Math.max(0, score - 50);
                updateUI();
                
                // Remove error class after animation
                setTimeout(() => {
                    cell.classList.remove('error');
                }, 500);
            }
        }

        function hasRepetition(row, col, number) {
            const boxSize = Math.sqrt(gridSize);
            
            // Check row
            for (let c = 0; c < gridSize; c++) {
                if (c !== col && currentPuzzle[row][c] === number) {
                    return true;
                }
            }

            // Check column
            for (let r = 0; r < gridSize; r++) {
                if (r !== row && currentPuzzle[r][col] === number) {
                    return true;
                }
            }

            // Check box
            const boxRow = Math.floor(row / boxSize) * boxSize;
            const boxCol = Math.floor(col / boxSize) * boxSize;
            for (let r = boxRow; r < boxRow + boxSize; r++) {
                for (let c = boxCol; c < boxCol + boxSize; c++) {
                    if (r !== row && c !== col && currentPuzzle[r][c] === number) {
                        return true;
                    }
                }
            }

            return false;
        }

        function eraseCell() {
            if (selectedCell && !selectedCell.classList.contains('fixed')) {
                const row = parseInt(selectedCell.dataset.row);
                const col = parseInt(selectedCell.dataset.col);
                
                selectedCell.textContent = '';
                currentPuzzle[row][col] = 0;
                selectedCell.classList.remove('error');
                
                // Clear notes if they exist
                if (selectedCell.notes) {
                    selectedCell.notes.clear();
                    updateNotesDisplay(selectedCell);
                }
            }
        }

        function giveHint() {
            if (!selectedCell || selectedCell.classList.contains('fixed')) {
                return;
            }

            // Check if hints are available
            if (hintsUsed >= 5) {
                showMessage('No hints remaining!', 'error');
                return;
            }

            const row = parseInt(selectedCell.dataset.row);
            const col = parseInt(selectedCell.dataset.col);
            const cellKey = `${row}-${col}`;
            const correctNumber = solution[row][col];

            selectedCell.textContent = correctNumber;
            currentPuzzle[row][col] = correctNumber;
            selectedCell.classList.add('hint');

            // Only add to solved cells if it wasn't solved before
            if (!solvedCells.has(cellKey)) {
                solvedCells.add(cellKey);
            }

            hintsUsed++;
            updateUI();

            // Update hint button state
            const hintBtn = document.getElementById('hint-btn');
            if (hintsUsed >= 5) {
                hintBtn.classList.add('disabled');
                hintBtn.style.opacity = '0.5';
                hintBtn.style.cursor = 'not-allowed';
            }

            // Remove hint class after animation
            setTimeout(() => {
                selectedCell.classList.remove('hint');
            }, 1000);

            if (isPuzzleComplete()) {
                completeGame();
            }
        }

        function checkSolution() {
            let hasErrors = false;
            
            document.querySelectorAll('.cell').forEach(cell => {
                if (!cell.classList.contains('fixed')) {
                    const row = parseInt(cell.dataset.row);
                    const col = parseInt(cell.dataset.col);
                    const currentValue = currentPuzzle[row][col];
                    
                    if (currentValue !== 0 && currentValue !== solution[row][col]) {
                        cell.classList.add('error');
                        hasErrors = true;
                        setTimeout(() => {
                            cell.classList.remove('error');
                        }, 2000);
                    }
                }
            });

            if (hasErrors) {
                showMessage('Some numbers are incorrect!', 'error');
            } else {
                showMessage('Looking good so far!', 'success');
            }
        }

        function isPuzzleComplete() {
            for (let row = 0; row < gridSize; row++) {
                for (let col = 0; col < gridSize; col++) {
                    if (currentPuzzle[row][col] === 0 || currentPuzzle[row][col] !== solution[row][col]) {
                        return false;
                    }
                }
            }
            return true;
        }

        function completeGame() {
            gameCompleted = true;
            clearInterval(timerInterval);
            
            const finalTime = Math.floor((Date.now() - startTime) / 1000);
            
            // Calculate final score
            const timeBonus = Math.max(0, 500 - finalTime); // Time bonus up to 500 points
            const mistakesPenalty = mistakes * 20; // Reduced penalty for mistakes
            const finalScore = Math.max(0, score + timeBonus - mistakesPenalty);

            // Update modal
            document.getElementById('final-time').textContent = formatTime(finalTime);
            document.getElementById('final-score').textContent = finalScore;
            
            // Add stars based on performance
            const stars = calculateStars(finalScore, mistakes, hintsUsed);
            displayStars(stars);
            
            // Save score
            saveScore(finalTime, finalScore);
            
            // Show completion modal
            document.getElementById('completion-modal').classList.add('active');
        }

        function calculateStars(score, mistakes, hints) {
            // 3 Stars: Perfect game (no mistakes, no hints) with good score
            if (mistakes === 0 && hints === 0 && score >= 1000) {
                return 3;
            }
            
            // 2 Stars: Good game (1-2 mistakes or 1-2 hints) with decent score
            if ((mistakes <= 2 || hints <= 2) && score >= 600) {
                return 2;
            }
            
            // 1 Star: Completed the game
            return 1;
        }

        function displayStars(count) {
            const starRating = document.getElementById('star-rating');
            starRating.innerHTML = '';
            
            // Add star rating explanation
            const explanation = document.createElement('div');
            explanation.className = 'star-explanation';
            explanation.style.textAlign = 'center';
            explanation.style.marginTop = '10px';
            explanation.style.color = '#666';
            
            switch(count) {
                case 3:
                    explanation.textContent = 'Perfect! No mistakes and no hints used.';
                    break;
                case 2:
                    explanation.textContent = 'Good! Few mistakes or hints used.';
                    break;
                case 1:
                    explanation.textContent = 'Completed! Keep practicing to improve.';
                    break;
            }
            
            // Add stars
            for (let i = 0; i < 3; i++) {
                const star = document.createElement('i');
                star.className = i < count ? 'fas fa-star' : 'far fa-star';
                star.style.color = i < count ? '#FFD700' : '#ccc';
                starRating.appendChild(star);
            }
            
            starRating.appendChild(explanation);
        }

        function saveScore(time, score) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `save_score=1&time_taken=${time}&score=${score}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update best scores display if this is a new record
                    updateBestScoresDisplay(time, score);
                    
                    // Redirect to levels page after a short delay
                    setTimeout(() => {
                        window.location.href = `levels.php?difficulty=${difficultyId}`;
                    }, 3000);
                }
            })
            .catch(error => console.error('Error saving score:', error));
        }

        function updateBestScoresDisplay(time, score) {
            const currentBestTimeText = document.getElementById('best-time-display').textContent;
            const currentBestScore = parseInt(document.getElementById('best-score-display').textContent) || 0;
            
            // Update best time if this is better
            if (currentBestTimeText === '--:--' || time < parseTime(currentBestTimeText)) {
                document.getElementById('best-time-display').textContent = formatTime(time);
            }
            
            // Update best score if this is better
            if (score > currentBestScore) {
                document.getElementById('best-score-display').textContent = score;
            }
        }

        function handleKeyboard(event) {
            if (gameCompleted) return;

            const key = event.key.toLowerCase();
            
            if (key >= '1' && key <= '9') {
                const number = parseInt(key);
                
                // Remove previous number highlights
                document.querySelectorAll('.cell').forEach(cell => {
                    cell.classList.remove('same-number');
                });
                
                // Highlight cells with the same number
                document.querySelectorAll('.cell').forEach(cell => {
                    if (getCurrentCellValue(cell) === number) {
                        cell.classList.add('same-number');
                    }
                });
                
                if (selectedCell && !selectedCell.classList.contains('fixed')) {
                    if (notesMode) {
                        toggleNote(selectedCell, number);
                    } else {
                        placeNumber(selectedCell, number);
                    }
                }
            } else if (key === 'delete' || key === 'backspace') {
                eraseCell();
            } else if (key === 'n') {
                toggleNotesMode();
            } else if (key === 'h') {
                giveHint();
            } else if (key === 'c') {
                checkSolution();
            } else if (key === 'e') {
                eraseCell();
            }
        }

        function getCurrentCellValue(cell) {
            const text = cell.textContent.trim();
            return text && !isNaN(text) ? parseInt(text) : 0;
        }

        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        function parseTime(timeString) {
            const [minutes, seconds] = timeString.split(':').map(Number);
            return minutes * 60 + seconds;
        }

        function updateUI() {
            document.getElementById('mistakes').textContent = mistakes;
            document.getElementById('hints-used').textContent = hintsUsed;
            document.getElementById('score').textContent = score;

            // Update hint button state
            const hintBtn = document.getElementById('hint-btn');
            if (hintsUsed >= 5) {
                hintBtn.classList.add('disabled');
                hintBtn.style.opacity = '0.5';
                hintBtn.style.cursor = 'not-allowed';
            } else {
                hintBtn.classList.remove('disabled');
                hintBtn.style.opacity = '1';
                hintBtn.style.cursor = 'pointer';
            }
        }

        function showMessage(text, type = 'success') {
            const message = document.getElementById('success-message');
            message.textContent = text;
            message.className = `success-message ${type}`;
            message.classList.add('show');
            
            setTimeout(() => {
                message.classList.remove('show');
            }, 2000);
        }

        function restartLevel() {
            document.getElementById('restart-modal').classList.add('active');
        }

        function closeRestartModal() {
            document.getElementById('restart-modal').classList.remove('active');
        }

        function confirmRestart() {
            // Clear solved cells tracking
            solvedCells.clear();
            location.reload();
        }

        function nextLevel() {
            const nextLevelNum = currentLevel + 1;
            window.location.href = `game.php?difficulty=${difficultyId}&level=${nextLevelNum}`;
        }

        function toggleNotesMode() {
            notesMode = !notesMode;
            const notesBtn = document.getElementById('notes-btn');
            notesBtn.classList.toggle('selected');
            showMessage(notesMode ? 'Notes Mode: ON' : 'Notes Mode: OFF');
        }

        function toggleNote(cell, number) {
            if (!cell.notes) {
                cell.notes = new Set();
            }
            
            if (cell.notes.has(number)) {
                cell.notes.delete(number);
            } else {
                cell.notes.add(number);
            }
            
            updateNotesDisplay(cell);
        }

        function updateNotesDisplay(cell) {
            // Clear existing notes
            cell.innerHTML = '';
            
            if (cell.notes && cell.notes.size > 0) {
                const notesGrid = document.createElement('div');
                notesGrid.className = 'notes-grid';
                
                // Create 3x3 grid for notes
                for (let i = 1; i <= 9; i++) {
                    const note = document.createElement('div');
                    note.className = 'note';
                    if (cell.notes.has(i)) {
                        note.textContent = i;
                    }
                    notesGrid.appendChild(note);
                }
                
                cell.appendChild(notesGrid);
            }
        }

        function gameOver(reason = 'Too many mistakes!') {
            gameCompleted = true;
            clearInterval(timerInterval);
            
            // Show game over modal
            const modal = document.getElementById('completion-modal');
            const modalContent = modal.querySelector('.modal-content');
            
            modalContent.innerHTML = `
                <div class="completion-header">
                    <h2>Game Over!</h2>
                    <p>${reason}</p>
                </div>
                <div class="completion-stats">
                    <div class="stat-item">
                        <div class="stat-value">${mistakes}</div>
                        <div class="stat-label">Mistakes</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${score}</div>
                        <div class="stat-label">Final Score</div>
                    </div>
                </div>
                <div class="modal-buttons">
                    <button class="modal-btn secondary-btn" onclick="restartLevel()">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                    <button class="modal-btn primary-btn" onclick="window.location.href='levels.php?difficulty=${difficultyId}'">
                        <i class="fas fa-home"></i> Back to Levels
                    </button>
                </div>
            `;
            
            modal.classList.add('active');
        }

        // Close modals when clicking outside
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Add tooltips to show keyboard shortcuts
        document.addEventListener('DOMContentLoaded', function() {
            // Add tooltips to control buttons
            document.getElementById('hint-btn').title = 'Hint (H)';
            document.getElementById('notes-btn').title = 'Notes (N)';
            document.getElementById('check-btn').title = 'Check (C)';
            document.getElementById('erase-btn').title = 'Erase (E)';
        });

        // Add this function to handle home button click
        function goHome() {
            // Save current progress before redirecting
            saveScore(Math.floor((Date.now() - startTime) / 1000), score);
            // Redirect to index.php
            window.location.href = 'index.php';
        }

        function saveProgress() {
            // Create a snapshot of the current game state
            const gameState = {
                puzzle: currentPuzzle,
                time: Math.floor((Date.now() - startTime) / 1000),
                mistakes: mistakes,
                hintsUsed: hintsUsed,
                score: score
            };

            // Save to localStorage
            localStorage.setItem('sudoku_save', JSON.stringify(gameState));
            showMessage('Progress saved!', 'success');
        }

        function updateBoardFromSave() {
            document.querySelectorAll('.cell').forEach(cell => {
                const row = parseInt(cell.dataset.row);
                const col = parseInt(cell.dataset.col);
                if (!cell.classList.contains('fixed')) {
                    cell.textContent = currentPuzzle[row][col] || '';
                }
            });
            updateUI();
        }
    </script>
</body>
</html>