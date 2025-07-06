<?php
// Game Hub Landing Page
$page_title = "Game Hub - Choose Your Challenge";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e8f8f5 0%, #d5f4e6 50%, #a7f3d0 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInDown 1s ease-out;
        }

        .header h1 {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #047857, #0d9488, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            font-weight: 800;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .header p {
            font-size: 1.3rem;
            color: #065f46;
            font-weight: 500;
            opacity: 0.9;
        }

        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            margin-bottom: 60px;
        }

        .game-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .game-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .game-card:hover::before {
            left: 100%;
        }

        .game-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
            border-color: #14b8a6;
        }

        .game-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, #10b981, #14b8a6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
        }

        .game-card:hover .game-icon {
            transform: rotate(5deg) scale(1.1);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.4);
        }

        .game-card h3 {
            font-size: 1.8rem;
            color: #047857;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .game-card p {
            color: #065f46;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 25px;
            opacity: 0.8;
        }

        .play-btn {
            background: linear-gradient(135deg, #10b981, #14b8a6);
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
        }

        .play-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669, #0d9488);
        }

        .stats-section {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #047857;
            display: block;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #065f46;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(2) { animation-delay: -2s; }
        .shape:nth-child(3) { animation-delay: -4s; }

        .shape1 {
            top: 20%;
            left: 10%;
            width: 60px;
            height: 60px;
            background: #10b981;
            border-radius: 12px;
            transform: rotate(45deg);
        }

        .shape2 {
            top: 60%;
            right: 15%;
            width: 80px;
            height: 80px;
            background: #14b8a6;
            border-radius: 50%;
        }

        .shape3 {
            bottom: 20%;
            left: 20%;
            width: 40px;
            height: 40px;
            background: #0d9488;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .game-card {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
        }

        .game-card:nth-child(1) { animation-delay: 0.2s; }
        .game-card:nth-child(2) { animation-delay: 0.4s; }
        .game-card:nth-child(3) { animation-delay: 0.6s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }
            
            .games-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .game-card {
                padding: 30px 20px;
            }
            
            .stats-section {
                padding: 30px 20px;
            }
        }

        .footer {
            text-align: center;
            margin-top: 60px;
            color: #065f46;
            opacity: 0.7;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    


    
    
    <div class="floating-shapes">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
    </div>

    <div class="container">
        <header class="header">
            <h1>Game Hub</h1>
            <p>Challenge your mind with our collection of brain games</p>
        </header>

        <div class="games-grid">
            <!-- Sudoku Game Card -->
            <div class="game-card" onclick="window.location.href='sudoku.php'">
                <div class="game-icon">
                    <i class="fas fa-th"></i>
                </div>
                <h3>Sudoku Puzzle</h3>
                <p>Test your logical thinking with classic number puzzles. Fill the 9x9 grid with digits so that each row, column, and 3x3 section contains all numbers from 1-9.</p>
                <a href="sudoku game/login.php" class="play-btn">
                    <i class="fas fa-play"></i> Play Sudoku
                </a>
            </div>

            <!-- Crack a Number Game Card -->
            <div class="game-card" onclick="window.location.href='crack-number.php'">
                <div class="game-icon">
                    <i class="fas fa-unlock"></i>
                </div>
                <h3>Crack a Number</h3>
                <p>Put your deduction skills to the test! Guess the secret number using logic and strategy. Each guess gives you clues to crack the code.</p>
                <a href="./crack_a_number/index.php" class="play-btn">
                    <i class="fas fa-play"></i> Start Cracking
                </a>
            </div>

            <!-- Speed Typing Game Card -->
            <div class="game-card" onclick="window.location.href='speed-typing.php'">
                <div class="game-icon">
                    <i class="fas fa-keyboard"></i>
                </div>
                <h3>Speed Typing Test</h3>
                <p>How fast can you type? Improve your typing speed and accuracy with our interactive typing challenges. Track your WPM and beat your records!</p>
                <a href="speed-typing.php" class="play-btn">
                    <i class="fas fa-play"></i> Start Typing
                </a>
            </div>
        </div>

        <div class="stats-section">
            <h2 style="color: #047857; margin-bottom: 10px; font-size: 2rem;">Game Statistics</h2>
            <p style="color: #065f46; margin-bottom: 20px;">Track your progress across all games</p>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">3</span>
                    <span class="stat-label">Games Available</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">∞</span>
                    <span class="stat-label">Challenges</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Brain Power</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Available</span>
                </div>
            </div>
        </div>
        <br>
        <div class="download-buttons" style="text-align: center; margin-bottom: 60px;">
    <a href="andriod/gamehub.apk" class="download-btn android-btn" download>
        <i class="fab fa-android" style="margin-right: 10px;"></i> Download Android Widget
    </a>
    <a href="windows/GameHub.exe" class="download-btn windows-btn" download>
        <i class="fab fa-windows" style="margin-right: 10px;"></i> Download Desktop for Windows
    </a>
</div>

<style>
    .download-buttons {
        display: flex;
        justify-content: center;
        gap: 30px;
        flex-wrap: wrap;
    }

    .download-btn {
        display: inline-flex;
        align-items: center;
        padding: 15px 30px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        text-decoration: none;
        color: white;
        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
        cursor: pointer;
        user-select: none;
    }

    .android-btn {
        background: linear-gradient(135deg, #3DDC84, #30B16A);
        box-shadow: 0 8px 16px rgba(61, 220, 132, 0.5);
    }

    .android-btn:hover {
        background: linear-gradient(135deg, #2CAE65, #249753);
        box-shadow: 0 12px 24px rgba(44, 174, 101, 0.6);
        transform: translateY(-3px);
    }

    .windows-btn {
        background: linear-gradient(135deg, #0078D7, #005A9E);
        box-shadow: 0 8px 16px rgba(0, 120, 215, 0.5);
    }

    .windows-btn:hover {
        background: linear-gradient(135deg, #005A9E, #004578);
        box-shadow: 0 12px 24px rgba(0, 90, 158, 0.6);
        transform: translateY(-3px);
    }

    .download-btn i {
        font-size: 1.5rem;
    }
</style>


        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Game Hub. Challenge your mind, improve your skills.</p>
        </footer>
    </div>

    <script>
        // Add smooth scrolling and enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add click sound effect simulation
            const gameCards = document.querySelectorAll('.game-card');
            
            gameCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-15px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
                
                // Prevent double navigation
                const playBtn = card.querySelector('.play-btn');
                playBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            // Add some dynamic elements
            const stats = document.querySelectorAll('.stat-number');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            entry.target.style.transform = 'scale(1)';
                        }, 200);
                    }
                });
            });

            stats.forEach(stat => {
                observer.observe(stat);
            });
        });

        // Add particle effect on hover
        function createParticle(e) {
            const particle = document.createElement('div');
            particle.style.position = 'absolute';
            particle.style.left = e.clientX + 'px';
            particle.style.top = e.clientY + 'px';
            particle.style.width = '4px';
            particle.style.height = '4px';
            particle.style.background = '#10b981';
            particle.style.borderRadius = '50%';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '1000';
            particle.style.animation = 'particleFloat 1s ease-out forwards';
            
            document.body.appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 1000);
        }

        // Add CSS for particle animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes particleFloat {
                0% {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
                100% {
                    opacity: 0;
                    transform: translateY(-50px) scale(0);
                }
            }
        `;
        document.head.appendChild(style);

        // Add particle effect to game cards
        document.querySelectorAll('.game-card').forEach(card => {
            card.addEventListener('mouseenter', createParticle);
        });
    </script>
</body>
</html>