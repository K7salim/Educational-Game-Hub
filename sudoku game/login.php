<?php
session_start();
require_once 'db_connect.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $action = $_POST['action'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        if ($action === 'login') {
            // Login
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                
                // Initialize user data if not exists
                if (!isset($_SESSION['user_data'])) {
                    $_SESSION['user_data'] = [
                        'easy_progress' => 1,
                        'medium_progress' => 1,
                        'hard_progress' => 1,
                        'expert_progress' => 1,
                        'completed_levels' => [],
                        'recent_games' => [],
                        'badges' => [],
                        'daily_challenges' => [],
                        'total_score' => 0,
                        'games_played' => 0,
                        'perfect_games' => 0
                    ];
                }
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else if ($action === 'register') {
            // Registration
            if (strlen($username) < 3) {
                $error = 'Username must be at least 3 characters long';
            } else if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long';
            } else {
                try {
                    // Check if username already exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = 'Username already exists';
                    } else {
                        // Create new user
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                        $stmt->execute([$username, $hashed_password]);
                        
                        $success = 'Registration successful! You can now login.';
                    }
                } catch (PDOException $e) {
                    $error = 'Registration failed. Please try again later.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sudoku Master</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #10b981;
            --accent-green: #34d399;
            --dark-green: #065f46;
            --light-green: #d1fae5;
            --dark-gray: #1f2937;
            --darker-gray: #111827;
            --card-highlight: #374151;
            --light-gray: #f3f4f6;
            --white: #ffffff;
            --error-color: #ef4444;
            --success-color: #10b981;
            --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-strong: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --glow: 0 0 30px rgba(16, 185, 129, 0.4);
            --glow-strong: 0 0 50px rgba(16, 185, 129, 0.6);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background */
        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            grid-template-rows: repeat(9, 1fr);
            transform: perspective(2000px) rotateX(25deg) rotateY(-15deg) rotateZ(-8deg) scale(1.2);
            animation: gridFloat 20s ease-in-out infinite;
        }

        .sudoku-cell {
            border: 1px solid rgba(16, 185, 129, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: min(1.8vw, 2rem);
            color: var(--primary-green);
            font-family: 'Poppins', monospace;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            text-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
            animation: sudokuPulse 8s infinite, sudokuFloat 6s infinite, sudokuGlow 4s infinite;
            transition: all 0.3s ease;
        }

        .sudoku-cell::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle at center, rgba(16, 185, 129, 0.15) 0%, rgba(52, 211, 153, 0.1) 30%, transparent 70%);
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
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.3), transparent);
            animation: cellSweep 6s infinite;
            pointer-events: none;
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
                box-shadow: 0 0 5px rgba(16, 185, 129, 0.2);
            }
            50% {
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            }
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

        /* Main Container */
        .auth-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
            z-index: 10;
            position: relative;
        }

        .auth-card {
            background: #ffffff;
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: var(--shadow-strong);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .auth-header {
            background: linear-gradient(135deg, var(--darker-gray) 0%, var(--dark-gray) 100%);
            color: var(--white);
            padding: 20px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .auth-header .logo-icon {
            font-size: 2.5em;
            margin-bottom: 5px;
            color: var(--primary-green);
            text-shadow: var(--glow);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .auth-header h1 {
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .auth-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1em;
            font-weight: 300;
        }

        /* Tabs */
        .auth-tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .auth-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 1em;
            position: relative;
            z-index: 2;
        }

        .auth-tab.active {
            color: var(--primary-green);
            background: var(--white);
            box-shadow: 0 -5px 15px rgba(16, 185, 129, 0.2);
        }

        .auth-tab:hover:not(.active) {
            background: rgba(16, 185, 129, 0.05);
        }

        /* Forms */
        .auth-form {
            padding: 25px 40px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
            max-width: 100%;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 0.95em;
            transition: all 0.3s ease;
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: var(--dark-gray);
            font-size: 1.1em;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 50px;
            border: 2px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95em;
            font-weight: 400;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
            background: var(--white);
        }

        .form-control:focus + .input-icon,
        .form-control:not(:placeholder-shown) + .input-icon {
            color: var(--primary-green);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            color: var(--dark-gray);
            cursor: pointer;
            font-size: 1.1em;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--primary-green);
        }

        /* Button */
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--accent-green) 100%);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin: 20px 30px;
            font-size: 0.95em;
            font-weight: 500;
            animation: slideInDown 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        .alert-success {
            background: linear-gradient(135deg, var(--light-green) 0%, #a7f3d0 100%);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .password-requirements {
            font-size: 0.85em;
            color: rgba(31, 41, 55, 0.7);
            margin-top: 8px;
            padding-left: 50px;
            animation: fadeIn 0.3s ease;
        }

        /* Loading Animation */
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .auth-container {
                padding: 10px;
            }
            
            .auth-form {
                padding: 30px 20px;
            }
            
            .auth-header {
                padding: 25px 15px;
            }
            
            .auth-header .logo-icon {
                font-size: 2.5em;
            }
            
            .auth-header h1 {
                font-size: 1.8em;
            }
        }

        /* Micro-interactions */
        .form-group:hover label {
            color: var(--dark-gray);
        }

        .auth-tab::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--primary-green);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .auth-tab.active::after {
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="background-animation" id="sudokuBackground"></div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-icon">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <h1>Sudoku Master</h1>
                <p>Challenge your mind, master the grid</p>
            </div>

            <!-- PHP Alert Messages would go here -->
            <!-- Example alerts for demonstration -->
            <!--
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Invalid username or password
            </div>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Registration successful! You can now login.
            </div>
            -->

            <div class="auth-tabs">
                <div class="auth-tab active" onclick="switchTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </div>
                <div class="auth-tab" onclick="switchTab('register')">
                    <i class="fas fa-user-plus"></i> Register
                </div>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="auth-form" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-container">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-container">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-lock input-icon"></i>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <div class="loading"></div>
                    <i class="fas fa-sign-in-alt"></i> Login to Continue
                </button>
            </form>

            <!-- Register Form -->
            <form id="registerForm" class="auth-form" style="display: none;" method="POST">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="registerUsername">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <div class="input-container">
                        <input type="text" 
                               class="form-control" 
                               id="registerUsername" 
                               name="username" 
                               placeholder="Choose a username"
                               required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="registerPassword">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="input-container">
                        <input type="password" 
                               class="form-control" 
                               id="registerPassword" 
                               name="password" 
                               placeholder="Create a strong password"
                               required>
                        <i class="fas fa-lock input-icon"></i>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('registerPassword', this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <div class="loading"></div>
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
        </div>
    </div>

    <script>
        // Tab switching with enhanced animations
        function switchTab(tab) {
            // Update tab appearance
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            const activeTab = document.querySelector(`.auth-tab:${tab === 'login' ? 'first-child' : 'last-child'}`);
            activeTab.classList.add('active');
            
            // Hide current form with fade out
            const currentForm = document.querySelector('.auth-form:not([style*="display: none"])');
            if (currentForm) {
                currentForm.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    currentForm.style.display = 'none';
                    // Show new form with fade in
                    const newForm = document.getElementById(tab + 'Form');
                    newForm.style.display = 'block';
                    newForm.style.animation = 'fadeIn 0.5s ease-out';
                }, 300);
            }
        }

        // Password visibility toggle
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === 'password';
            
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'fas fa-eye-slash password-toggle' : 'fas fa-eye password-toggle';
            
            // Add a small animation
            icon.style.transform = 'scale(0.8)';
            setTimeout(() => {
                icon.style.transform = 'scale(1)';
            }, 150);
        }

        // Enhanced form submission with loading state
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = this.querySelector('.btn-primary');
                const loading = button.querySelector('.loading');
                const icon = button.querySelector('i:not(.loading)');
                
                // Show loading state
                loading.style.display = 'inline-block';
                icon.style.display = 'none';
                button.disabled = true;
                button.style.transform = 'translateY(-1px)';
            });
        });

        // Input focus animations
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.querySelector('label').style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.parentElement.querySelector('label').style.transform = 'translateY(0)';
            });
        });

        // Add fade out animation keyframe
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-20px); }
            }
        `;
        document.head.appendChild(style);

        // Generate Sudoku background
        function generateSudokuBackground() {
            const background = document.getElementById('sudokuBackground');
            const numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9];
            const gridSize = 9;
            
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

        // Initialize background on page load
        document.addEventListener('DOMContentLoaded', function() {
            generateSudokuBackground();
            
            // Add subtle parallax effect to background
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const background = document.getElementById('sudokuBackground');
                const rate = scrolled * -0.2;
                background.style.transform = `perspective(2000px) rotateX(25deg) rotateY(-15deg) rotateZ(-8deg) scale(1.2) translateY(${rate}px)`;
            });
        });
    </script>
</body>
</html>