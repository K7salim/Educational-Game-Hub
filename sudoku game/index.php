<?php
// Initialize session
session_start();
require_once 'db_connect.php';

// Simple user setup (no database dependency for user)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'User';
} else {
    // Redirect to login if no user session
    header('Location: login.php');
    exit();
}

// Define difficulty names and classes (based on your DB structure)
$difficulty_names = [
    1 => 'Easy',
    2 => 'Medium', 
    3 => 'Hard',
    4 => 'Expert'
];

$difficulty_classes = [
    1 => ['icon' => 'fas fa-leaf', 'color' => '#28a745'],
    2 => ['icon' => 'fas fa-fire', 'color' => '#ffc107'],
    3 => ['icon' => 'fas fa-bolt', 'color' => '#fd7e14'],
    4 => ['icon' => 'fas fa-crown', 'color' => '#dc3545']
];

// Get max levels from difficulties table
$max_levels = [];
try {
    $stmt = $pdo->prepare("SELECT id, max_level FROM difficulties ORDER BY id");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $max_levels[$row['id']] = $row['max_level'];
    }
} catch (PDOException $e) {
    error_log("Database error getting max levels: " . $e->getMessage());
    // Fallback values
    $max_levels = [1 => 30, 2 => 50, 3 => 100, 4 => 200];
}

// Initialize progress variables
$total_completed = 0;
$total_stars = 0;
$total_possible_levels = array_sum($max_levels);
$total_possible_stars = $total_possible_levels * 3;
$modes_played = 0;

// Get difficulty statistics
$difficulty_stats = [];
try {
    foreach ([1, 2, 3, 4] as $difficulty_id) {
        // Initialize default values
        $difficulty_stats[$difficulty_id] = [
            'name' => $difficulty_names[$difficulty_id],
            'completed' => 0,
            'max_levels' => $max_levels[$difficulty_id],
            'perfect_games' => 0,
            'avg_score' => 0,
            'best_time' => null,
            'speed_games' => 0,
            'stars' => 0,
            'max_stars' => $max_levels[$difficulty_id] * 3,
            'icon' => $difficulty_classes[$difficulty_id]['icon'],
            'color' => $difficulty_classes[$difficulty_id]['color']
        ];

        // Get completed levels and statistics for this difficulty
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT up.level_id) as completed_levels,
                COALESCE(AVG(up.best_score), 0) as avg_score,
                MIN(up.best_time) as best_time,
                COUNT(DISTINCT CASE WHEN up.best_time IS NOT NULL AND up.best_time <= 120 THEN up.level_id END) as speed_games,
                SUM(CASE 
                    WHEN up.best_score >= 1000 THEN 3
                    WHEN up.best_score >= 600 THEN 2
                    ELSE 1
                END) as total_stars
            FROM user_progress up
            JOIN levels l ON up.level_id = l.id
            WHERE up.user_id = ? AND l.difficulty_id = ? AND up.completed = 1
        ");
        $stmt->execute([$user_id, $difficulty_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stats && $stats['completed_levels'] > 0) {
            $difficulty_stats[$difficulty_id]['completed'] = (int)$stats['completed_levels'];
            $difficulty_stats[$difficulty_id]['avg_score'] = (int)$stats['avg_score'];
            $difficulty_stats[$difficulty_id]['best_time'] = $stats['best_time'];
            $difficulty_stats[$difficulty_id]['speed_games'] = (int)$stats['speed_games'];
            $difficulty_stats[$difficulty_id]['stars'] = (int)$stats['total_stars'];
            
            $total_completed += (int)$stats['completed_levels'];
            $total_stars += (int)$stats['total_stars'];
            $modes_played++;
        }

        // Get perfect games count (from recent_games table where mistakes = 0)
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT rg.level_id) as perfect_games
            FROM recent_games rg
            JOIN levels l ON rg.level_id = l.id
            WHERE rg.user_id = ? AND l.difficulty_id = ? AND rg.mistakes = 0 AND rg.completed = 1
        ");
        $stmt->execute([$user_id, $difficulty_id]);
        $perfect_result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($perfect_result) {
            $difficulty_stats[$difficulty_id]['perfect_games'] = (int)$perfect_result['perfect_games'];
        }
    }
} catch (PDOException $e) {
    error_log("Database error in difficulty stats: " . $e->getMessage());
    // Keep default values on error
}

$total_progress_percentage = $total_possible_levels > 0 ? ($total_completed / $total_possible_levels) * 100 : 0;

// Get user achievements with proper badge display
$achievements = [];
try {
    $stmt = $pdo->prepare("
        SELECT a.*, ua.earned_at,
        CASE 
            WHEN a.requirement_type = 'levels_completed' THEN 
                (SELECT COUNT(*) FROM user_progress WHERE user_id = ? AND completed = TRUE) >= a.requirement_value
            WHEN a.requirement_type = 'perfect_games' THEN 
                (SELECT COUNT(*) FROM recent_games WHERE user_id = ? AND mistakes = 0 AND completed = TRUE) >= a.requirement_value
            WHEN a.requirement_type = 'total_score' THEN 
                (SELECT COALESCE(SUM(score), 0) FROM leaderboard WHERE user_id = ?) >= a.requirement_value
            WHEN a.requirement_type = 'time_bonus' THEN 
                (SELECT COUNT(*) FROM recent_games WHERE user_id = ? AND time_taken <= a.requirement_value AND completed = TRUE) >= 1
            ELSE FALSE
        END as is_achieved
        FROM achievements a
        LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
        ORDER BY a.id
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
    $achievements = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error silently
}

// Get recent games with more details
$recent_games = [];
try {
    $stmt = $pdo->prepare("
        SELECT rg.*, l.level_number, d.name as difficulty_name,
        CASE 
            WHEN rg.mistakes = 0 THEN 'Perfect'
            WHEN rg.mistakes <= 2 THEN 'Good'
            ELSE 'Normal'
        END as performance
        FROM recent_games rg
        JOIN levels l ON rg.level_id = l.id
        JOIN difficulties d ON l.difficulty_id = d.id
        WHERE rg.user_id = ? AND rg.completed = TRUE
        ORDER BY rg.played_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_games = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error silently
}

// Get leaderboard data
$leaderboard = [];
try {
    $stmt = $pdo->prepare("
        SELECT u.username, SUM(l.score) as total_score, COUNT(DISTINCT l.level_id) as levels_completed
        FROM leaderboard l
        JOIN users u ON l.user_id = u.id
        GROUP BY u.id
        ORDER BY total_score DESC
        LIMIT 10
    ");
    $stmt->execute();
    $leaderboard = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error silently
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku Game - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            /* Enhanced Primary Color Palette */
            --primary-teal: #00d9ff;
            --primary-green: #10b981;
            --accent-cyan: #00fffc;
            --accent-green: #34d399;
            --electric-blue: #0ea5e9;
            
            /* Background & Surface Colors */
            --bg-dark: #0a0f1c;
            --bg-darker: #030712;
            --bg-gradient-start: #001a1a;
            --bg-gradient-mid: #003d3d;
            --bg-gradient-end: #006666;
            --surface-dark: #1a1f36;
            --surface-medium: #252b47;
            --surface-light: #2d3561;
            
            /* Glass Effect Colors */
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-highlight: rgba(0, 255, 252, 0.15);
            
            /* Text Colors */
            --text-primary: #ffffff;
            --text-secondary: #e2e8f0;
            --text-muted: #94a3b8;
            --text-accent: #00fffc;
            
            /* State Colors */
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --info: #3b82f6;
            
            /* Shadows & Effects */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-glow: 0 0 20px rgba(0, 255, 252, 0.4);
            --shadow-glow-strong: 0 0 40px rgba(0, 255, 252, 0.6);
            
            /* Transitions */
            --transition-fast: 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            
            /* Border Radius */
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 9999px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-mid) 50%, var(--bg-gradient-end) 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            font-weight: 400;
            letter-spacing: -0.01em;
        }

        /* Enhanced Animated Background */
        .sudoku-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-template-rows: repeat(12, 1fr);
            opacity: 0.08;
            z-index: -2;
            transform: perspective(2000px) rotateX(25deg) rotateY(-15deg) rotateZ(-8deg) scale(1.2);
            animation: gridFloat 20s ease-in-out infinite;
            background: 
                radial-gradient(circle at 20% 20%, rgba(0, 217, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 255, 252, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
            filter: blur(0.5px);
        }

        @keyframes gridFloat {
            0%, 100% { 
                transform: perspective(2000px) rotateX(25deg) rotateY(-15deg) rotateZ(-8deg) scale(1.2) translateY(0) translateX(0);
            }
            33% { 
                transform: perspective(2000px) rotateX(20deg) rotateY(-10deg) rotateZ(-5deg) scale(1.25) translateY(-30px) translateX(20px);
            }
            66% { 
                transform: perspective(2000px) rotateX(30deg) rotateY(-20deg) rotateZ(-12deg) scale(1.15) translateY(20px) translateX(-15px);
            }
        }

        .sudoku-cell {
            border: 1px solid rgba(0, 255, 252, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: min(1.8vw, 2rem);
            color: var(--accent-cyan);
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            text-shadow: 0 0 15px rgba(0, 255, 252, 0.4);
            animation: numberPulse 12s infinite;
            opacity: 0;
            backdrop-filter: blur(2px);
        }

        .sudoku-cell::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: 
                radial-gradient(circle at center, rgba(0, 255, 252, 0.15) 0%, rgba(0, 217, 255, 0.1) 30%, transparent 70%);
            animation: cellGlow 8s infinite ease-in-out;
            pointer-events: none;
        }

        .sudoku-cell::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 252, 0.3), transparent);
            animation: cellSweep 6s infinite;
            pointer-events: none;
        }

        @keyframes cellGlow {
            0%, 100% { 
                transform: translate(-50%, -50%) scale(0.5) rotate(0deg); 
                opacity: 0; 
            }
            50% { 
                transform: translate(-50%, -50%) scale(1.5) rotate(180deg); 
                opacity: 1; 
            }
        }

        @keyframes cellSweep {
            0% { left: -100%; opacity: 0; }
            50% { opacity: 1; }
            100% { left: 100%; opacity: 0; }
        }

        @keyframes numberPulse {
            0%, 90%, 100% {
                opacity: 0;
                transform: scale(0.8) rotateY(90deg);
                filter: blur(3px);
            }
            10%, 80% {
                opacity: 1;
                transform: scale(1) rotateY(0deg);
                filter: blur(0px);
            }
            45% {
                transform: scale(1.1) rotateY(0deg);
            }
        }

        /* Enhanced Container Styles */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.25rem;
            position: relative;
            z-index: 1;
        }

        /* Enhanced Header Design */
        header {
            background: linear-gradient(135deg, rgba(0, 217, 255, 0.06), rgba(0, 255, 252, 0.02));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-md);
            padding: 0.75rem 1.25rem;
            margin: 0.25rem 0 1rem 0;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-teal), var(--accent-cyan));
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            position: relative;
            animation: logoFloat 3s ease-in-out infinite;
            box-shadow: 0 0 15px rgba(0, 255, 252, 0.2);
        }

        .logo-info {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .logo-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, var(--accent-cyan), var(--primary-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .logo-info p {
            font-size: 0.8rem;
            margin: 0;
            color: var(--text-muted);
            line-height: 1.2;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-full);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-info span {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--error), #dc2626);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-full);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            border: none;
            cursor: pointer;
            opacity: 0.9;
        }

        .logout-btn:hover {
            opacity: 1;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .logout-btn i {
            font-size: 0.9rem;
        }

        /* Responsive adjustments for header */
        @media (max-width: 768px) {
            header {
                padding: 0.625rem 1rem;
            }

            .header-content {
                flex-direction: row;
                gap: 0.75rem;
            }

            .logo-section {
                gap: 0.5rem;
            }

            .logo-icon {
                width: 36px;
                height: 36px;
                font-size: 1.1rem;
            }

            .logo-info h1 {
                font-size: 1.25rem;
            }

            .logo-info p {
                font-size: 0.75rem;
            }

            .user-info {
                padding: 0.375rem 0.625rem;
            }

            .user-avatar {
                width: 28px;
                height: 28px;
                font-size: 0.8rem;
            }

            .user-info span {
                font-size: 0.8rem;
            }

            .logout-btn {
                padding: 0.375rem 0.625rem;
                font-size: 0.8rem;
            }
        }

        /* Enhanced Card System */
        .card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.2));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            margin: 0.75rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--glass-highlight);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(0, 255, 252, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 90% 80%, rgba(0, 217, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--accent-cyan), var(--primary-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-header h2 i {
            font-size: 1.1rem;
        }

        /* Enhanced Progress System */
        .progress-overview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin: 1rem 0;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.2));
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-md);
            padding: 0.875rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--accent-cyan);
            margin-bottom: 0.25rem;
            line-height: 1;
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Enhanced Progress Bar */
        .progress-container {
            margin: 0.75rem 0;
        }

        .progress-bar {
            background: rgba(0, 0, 0, 0.3);
            height: 6px;
            border-radius: var(--radius-full);
            overflow: hidden;
            margin: 0.5rem 0;
            position: relative;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-teal), var(--accent-cyan));
            border-radius: var(--radius-full);
            position: relative;
            transition: width 1s ease-out;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        /* Enhanced Game Modes Layout */
        .game-modes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 1.25rem;
            padding: 0.5rem;
        }

        .mode-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.2));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .mode-card.easy {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(0, 0, 0, 0.2));
            border-color: rgba(16, 185, 129, 0.1);
        }

        .mode-card.medium {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(0, 0, 0, 0.2));
            border-color: rgba(14, 165, 233, 0.1);
        }

        .mode-card.hard {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(0, 0, 0, 0.2));
            border-color: rgba(245, 158, 11, 0.1);
        }

        .mode-card.expert {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(0, 0, 0, 0.2));
            border-color: rgba(239, 68, 68, 0.1);
        }

        .mode-icon-container {
            width: 70px;
            height: 70px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.3s ease;
        }

        .mode-icon {
            font-size: 1.75rem;
            color: white;
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(5deg); }
        }

        .mode-card.easy .mode-icon-container {
            background: linear-gradient(135deg, #10b981, #34d399);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
        }

        .mode-card.medium .mode-icon-container {
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.3);
        }

        .mode-card.hard .mode-icon-container {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
        }

        .mode-card.expert .mode-icon-container {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
        }

        .mode-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            background: linear-gradient(135deg, var(--accent-cyan), var(--primary-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .mode-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
            margin: 0.25rem 0;
        }

        .mode-stat {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            padding: 0.5rem;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .mode-stat-number {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.125rem;
        }

        .mode-stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .play-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-teal), var(--accent-cyan));
            color: white;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            opacity: 0.9;
        }

        .play-btn:hover {
            opacity: 0.95;
            box-shadow: 0 4px 12px rgba(0, 255, 252, 0.15);
        }

        /* Enhanced Achievements */
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .achievement-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.1));
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-md);
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .achievement-card:hover {
            box-shadow: var(--shadow-md);
        }

        .achievement-card.unlocked {
            background: linear-gradient(135deg, rgba(0, 255, 252, 0.1), rgba(0, 217, 255, 0.05));
            border-color: rgba(0, 255, 252, 0.1);
        }

        .achievement-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: linear-gradient(135deg, var(--primary-teal), var(--accent-cyan));
            color: white;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 0 15px rgba(0, 255, 252, 0.2);
        }

        .achievement-card.unlocked .achievement-icon {
            animation: achievementPulse 2s infinite;
        }

        @keyframes achievementPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .achievement-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            line-height: 1.2;
        }

        .achievement-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.3;
            margin: 0;
        }

        .achievement-date {
            font-size: 0.7rem;
            color: var(--accent-cyan);
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-weight: 500;
        }

        /* Enhanced Leaderboard */
        .leaderboard-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.2));
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: 2rem;
        }

        .leaderboard-table th {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3));
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
            text-align: left;
        }

        .leaderboard-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .leaderboard-table tbody tr:hover {
            background: rgba(0, 255, 252, 0.05);
            transform: scale(1.02);
        }

        .rank {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--accent-cyan);
            font-family: 'JetBrains Mono', monospace;
        }

        /* Enhanced Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translate3d(0, -100%, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 40px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .progress-overview {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .progress-overview {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
            
            .stat-card {
                padding: 0.75rem;
            }
            
            .stat-number {
                font-size: 1.25rem;
            }
            
            .stat-label {
                font-size: 0.7rem;
            }
        }

        /* Leaderboard See All Button */
        .leaderboard-footer {
            text-align: center;
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .see-all-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-teal), var(--accent-cyan));
            color: white;
            border: none;
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .see-all-btn:hover {
            opacity: 0.95;
            box-shadow: 0 4px 12px rgba(0, 255, 252, 0.15);
        }

        .see-all-btn i {
            font-size: 0.9rem;
        }

        /* Recent Games Styles */
        .recent-games {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .recent-game-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-md);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .game-info {
            flex: 1;
        }

        .game-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .difficulty-badge {
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .difficulty-badge.easy { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .difficulty-badge.medium { background: rgba(14, 165, 233, 0.2); color: #0ea5e9; }
        .difficulty-badge.hard { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
        .difficulty-badge.expert { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

        .game-stats {
            display: flex;
            gap: 1rem;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .game-performance {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 600;
        }

        .game-performance.perfect { background: rgba(16, 185, 129, 0.2); color: #10b981; }
        .game-performance.good { background: rgba(14, 165, 233, 0.2); color: #0ea5e9; }
        .game-performance.normal { background: rgba(100, 116, 139, 0.2); color: #64748b; }

        /* Difficulty Statistics Styles */
        .difficulty-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .difficulty-stat-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-header i {
            font-size: 1.5rem;
        }

        .stat-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .stat-content {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .stat-value {
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Achievement Progress Styles */
        .achievement-progress {
            margin-top: 0.5rem;
            width: 100%;
        }

        .achievement-progress .progress-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .achievement-progress .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-teal), var(--accent-cyan));
            border-radius: var(--radius-full);
            transition: width 0.3s ease;
        }

        .achievement-progress .progress-text {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
            text-align: right;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-bg"></div>
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <div class="logo-section">
                        <div class="logo-icon">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <div class="logo-info">
                            <h1>Sudoku Master</h1>
                            <p>Challenge your mind with numbers</p>
                        </div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span>Welcome, <?php echo htmlspecialchars($user_name); ?>!</span>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Overall Progress Card -->
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> Overall Progress</h2>
                <div class="stars">
                    <?php for ($i = 0; $i < min(5, floor($total_stars / 20)); $i++): ?>
                        <i class="fas fa-star"></i>
                    <?php endfor; ?>
                    <?php if ($total_stars == 0): ?>
                        <span style="color: #666;">0 stars</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="total-progress" data-width="<?php echo $total_progress_percentage; ?>"></div>
                </div>
                <div class="progress-text">
                    <span>Levels Completed: <?php echo $total_completed; ?> of <?php echo $total_possible_levels; ?></span>
                    <span>Progress: <?php echo round($total_progress_percentage); ?>%</span>
                </div>
            </div>
            
            <div class="progress-overview">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_completed; ?></div>
                    <div class="stat-label">Levels Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_stars; ?></div>
                    <div class="stat-label">Stars Earned</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo round($total_progress_percentage); ?>%</div>
                    <div class="stat-label">Total Progress</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $modes_played; ?></div>
                    <div class="stat-label">Modes Played</div>
                </div>
            </div>
        </div>

        <!-- Game Modes Card -->
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h2><i class="fas fa-gamepad"></i> Game Modes</h2>
                <span style="color: #666; font-size: 16px;">Choose your challenge</span>
            </div>
            
            <div class="game-modes">
                <?php foreach ($difficulty_stats as $diff_id => $stats): ?>
                <div class="mode-card <?php echo strtolower($stats['name']); ?>">
                    <div class="mode-icon-container">
                        <i class="mode-icon <?php echo $stats['icon']; ?>"></i>
                    </div>
                    <h3 class="mode-title"><?php echo $stats['name']; ?></h3>
                    <p class="mode-subtitle"><?php echo $stats['max_levels']; ?> levels available</p>
                    
                    <div class="mode-stats">
                        <div class="mode-stat">
                            <div class="mode-stat-number"><?php echo $stats['completed']; ?>/<?php echo $stats['max_levels']; ?></div>
                            <div class="mode-stat-label">Completed</div>
                        </div>
                        <div class="mode-stat">
                            <div class="mode-stat-number"><?php echo $stats['stars']; ?>/<?php echo $stats['max_stars']; ?></div>
                            <div class="mode-stat-label">Stars</div>
                        </div>
                    </div>
                    
                    <a href="levels.php?difficulty=<?php echo $diff_id; ?>" class="play-btn">
                        <i class="fas fa-play"></i>
                        Start Playing
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Achievements Card -->
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h2><i class="fas fa-trophy"></i> Achievements</h2>
            </div>
            <div class="achievements-grid">
                <?php foreach ($achievements as $achievement): ?>
                <div class="achievement-card <?php echo $achievement['is_achieved'] ? 'unlocked' : 'locked'; ?>">
                    <div class="achievement-icon">
                        <?php if ($achievement['is_achieved']): ?>
                            <i class="fas fa-medal"></i>
                        <?php else: ?>
                            <i class="fas fa-lock"></i>
                        <?php endif; ?>
                    </div>
                    <div class="achievement-name"><?php echo htmlspecialchars($achievement['name']); ?></div>
                    <div class="achievement-desc"><?php echo htmlspecialchars($achievement['description']); ?></div>
                    <?php if ($achievement['is_achieved']): ?>
                    <div class="achievement-date">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo $achievement['earned_at'] ? date('M d, Y', strtotime($achievement['earned_at'])) : 'Just earned!'; ?>
                    </div>
                    <?php endif; ?>
                    <div class="achievement-progress">
                        <?php if ($achievement['requirement_type'] == 'levels_completed'): ?>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo min(100, ($total_completed / $achievement['requirement_value']) * 100); ?>%"></div>
                            </div>
                            <span class="progress-text"><?php echo $total_completed; ?>/<?php echo $achievement['requirement_value']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Leaderboard Card -->
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h2><i class="fas fa-crown"></i> Global Leaderboard</h2>
            </div>
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Player</th>
                        <th>Score</th>
                        <th>Levels</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $player): ?>
                    <tr>
                        <td class="rank">#<?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($player['username']); ?></td>
                        <td><?php echo number_format($player['total_score']); ?></td>
                        <td><?php echo $player['levels_completed']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="leaderboard-footer">
                <a href="leaderboard.php" class="see-all-btn">
                    <i class="fas fa-list"></i>
                    View Full Leaderboard
                </a>
            </div>
        </div>

        <!-- Add Recent Games Card -->
        <div class="card animate__animated animate__fadeIn">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Recent Games</h2>
            </div>
            <div class="recent-games">
                <?php foreach ($recent_games as $game): ?>
                <div class="recent-game-item">
                    <div class="game-info">
                        <div class="game-title">
                            <span class="difficulty-badge <?php echo strtolower($game['difficulty_name']); ?>">
                                <?php echo $game['difficulty_name']; ?>
                            </span>
                            Level <?php echo $game['level_number']; ?>
                        </div>
                        <div class="game-stats">
                            <div class="stat">
                                <i class="fas fa-star"></i>
                                <?php echo number_format($game['score']); ?> points
                            </div>
                            <div class="stat">
                                <i class="fas fa-clock"></i>
                                <?php echo floor($game['time_taken'] / 60); ?>m <?php echo $game['time_taken'] % 60; ?>s
                            </div>
                            <div class="stat">
                                <i class="fas fa-times-circle"></i>
                                <?php echo $game['mistakes']; ?> mistakes
                            </div>
                        </div>
                    </div>
                    <div class="game-performance <?php echo strtolower($game['performance']); ?>">
                        <?php echo $game['performance']; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
     
<div class="card animate__animated animate__fadeIn">
    <div class="card-header">
        <h2><i class="fas fa-chart-bar"></i> Difficulty Statistics</h2>
    </div>
    <div class="difficulty-stats-grid">
        <?php foreach ($difficulty_stats as $diff_id => $stats): ?>
        <div class="difficulty-stat-card <?php echo strtolower($stats['name']); ?>">
            <div class="stat-header">
                <i class="<?php echo $stats['icon']; ?>" style="color: <?php echo $stats['color']; ?>"></i>
                <h3><?php echo $stats['name']; ?></h3>
            </div>
            <div class="stat-content">
                <div class="stat-row">
                    <span class="stat-label">Completed Levels</span>
                    <span class="stat-value"><?php echo $stats['completed']; ?>/<?php echo $stats['max_levels']; ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Perfect Games</span>
                    <span class="stat-value"><?php echo $stats['perfect_games']; ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Average Score</span>
                    <span class="stat-value"><?php echo number_format($stats['avg_score']); ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Best Time</span>
                    <span class="stat-value">
                        <?php 
                        if ($stats['best_time']) {
                            $minutes = floor($stats['best_time'] / 60);
                            $seconds = $stats['best_time'] % 60;
                            echo $minutes . 'm ' . $seconds . 's';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Speed Games (&lt;2min)</span>
                    <span class="stat-value"><?php echo $stats['speed_games']; ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Completion Rate</span>
                    <span class="stat-value">
                        <?php 
                        $completion_rate = $stats['max_levels'] > 0 ? 
                            round(($stats['completed'] / $stats['max_levels']) * 100, 1) : 0;
                        echo $completion_rate . '%';
                        ?>
                    </span>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $completion_rate; ?>%; background-color: <?php echo $stats['color']; ?>"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

    <div class="sudoku-background" id="sudokuBackground"></div>

    <script>
        // Generate animated background grid
        function generateSudokuBackground() {
            const background = document.getElementById('sudokuBackground');
            const numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9];
            const gridSize = 9; // Standard Sudoku grid size
            
            // Create a 9x9 grid
            for (let row = 0; row < gridSize; row++) {
                for (let col = 0; col < gridSize; col++) {
                    const cell = document.createElement('div');
                    cell.className = 'sudoku-cell';
                    
                    // Add random number with animation delay
                    if (Math.random() > 0.3) { // 70% chance to show a number
                        const randomNumber = numbers[Math.floor(Math.random() * numbers.length)];
                        cell.textContent = randomNumber;
                        
                        // Stagger the animations based on position
                        const delay = (row * 0.2 + col * 0.1) % 12;
                        cell.style.animationDelay = `${delay}s`;
                        
                        // Vary the opacity based on position
                        const opacity = 0.2 + (Math.sin(row + col) * 0.3);
                        cell.style.opacity = opacity;
                        
                        // Add subtle rotation based on position
                        const rotation = (row + col) * 5;
                        cell.style.transform = `rotate(${rotation}deg)`;
                    }
                    
                    background.appendChild(cell);
                }
            }
        }

        // Enhanced button interactions
        function addButtonEffects() {
            const buttons = document.querySelectorAll('.play-btn, .logout-btn, .user-info');
            
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
                
                button.addEventListener('mousedown', function() {
                    this.style.transform = 'translateY(-1px) scale(0.98)';
                });
                
                button.addEventListener('mouseup', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                });
            });
        }

        // Animate statistics on scroll
        function animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const finalValue = target.textContent;
                        
                        // Animate number counting for numeric values
                        if (!isNaN(parseInt(finalValue))) {
                            let current = 0;
                            const increment = parseInt(finalValue) / 30;
                            const timer = setInterval(() => {
                                current += increment;
                                if (current >= parseInt(finalValue)) {
                                    target.textContent = finalValue;
                                    clearInterval(timer);
                                } else {
                                    target.textContent = Math.floor(current);
                                }
                            }, 50);
                        }
                        
                        observer.unobserve(target);
                    }
                });
            });
            
            statNumbers.forEach(stat => observer.observe(stat));
        }

        // Add floating animation to achievement cards
        function floatAchievements() {
            const achievements = document.querySelectorAll('.achievement-card');
            achievements.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.2}s`;
                card.style.animation = `fadeInUp 0.6s ease-out forwards, achievementFloat 6s ease-in-out infinite ${index * 0.5}s`;
            });
        }

        // Add CSS animation for Sudoku cells
        const sudokuKeyframes = `
            @keyframes sudokuPulse {
                0%, 100% {
                    opacity: 0.2;
                    transform: scale(0.8) rotate(0deg);
                    filter: blur(2px);
                }
                50% {
                    opacity: 0.8;
                    transform: scale(1.1) rotate(5deg);
                    filter: blur(0px);
                }
            }

            @keyframes sudokuFloat {
                0%, 100% {
                    transform: translateY(0) rotate(0deg);
                }
                50% {
                    transform: translateY(-10px) rotate(5deg);
                }
            }

            @keyframes sudokuGlow {
                0%, 100% {
                    box-shadow: 0 0 5px rgba(0, 255, 252, 0.2);
                }
                50% {
                    box-shadow: 0 0 20px rgba(0, 255, 252, 0.4);
                }
            }
        `;
        
        const style = document.createElement('style');
        style.textContent = sudokuKeyframes;
        document.head.appendChild(style);

        // Update the CSS for sudoku cells
        const cellStyle = document.createElement('style');
        cellStyle.textContent = `
            .sudoku-cell {
                animation: sudokuPulse 8s infinite, sudokuFloat 6s infinite, sudokuGlow 4s infinite;
                transition: all 0.3s ease;
            }
            
            .sudoku-cell:hover {
                transform: scale(1.2) rotate(10deg) !important;
                opacity: 1 !important;
                z-index: 1;
            }
        `;
        document.head.appendChild(cellStyle);

        // Initialize all effects
        document.addEventListener('DOMContentLoaded', function() {
            generateSudokuBackground();
            addButtonEffects();
            animateStats();
            floatAchievements();
            
            // Add staggered animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Add interactive hover effects to mode cards
            const modeCards = document.querySelectorAll('.mode-card');
            modeCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                    this.style.boxShadow = 'var(--shadow-glow-strong)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = 'var(--shadow-lg)';
                });
            });
        });

        // Add subtle parallax effect to background
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const background = document.getElementById('sudokuBackground');
            const rate = scrolled * -0.2;
            background.style.transform = `perspective(2000px) rotateX(25deg) rotateY(-15deg) rotateZ(-8deg) scale(1.2) translateY(${rate}px)`;
        });
    </script>
</body>
</html>