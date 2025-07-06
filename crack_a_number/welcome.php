<?php
session_start();
require 'config.php'; // Make sure this includes your $pdo connection

// Check if user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_grade'])) {
    header('Location: login.php');
    exit;
}

// Assign session variables
$userId = $_SESSION['user_id'];
$name = $_SESSION['user_name'];
$grade = $_SESSION['user_grade'];

try {
    // Fetch user profile picture
    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit;
    }

    // Use fallback image if no profile_pic is set
    $profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'assets/default-profile.png';
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<!-- Google Font (optional gaming font) -->
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<title>Crack a Number Challenge</title>
<style>
  html, body {
  margin: 0;
  padding: 0;
}

  * {
    box-sizing: border-box;
  }
  body {
    margin: 0; padding: 2rem;
    font-family: 'arial', cursive, sans-serif;
    background: linear-gradient(135deg, #1c1c1c, #2c5364);
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
    color: #39ff14;
  }

  /* Main container */
  .container {
    max-width: 800px;
    width: 112%;
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  /* Card styling */
  .card {
    background: rgba(10, 10, 10, 0.85);
    border-radius: 15px;
    padding: 2rem 2.5rem;
    box-shadow:
      0 0 15px #00ffccaa,
      inset 0 0 12px #00ffcc77;
    border: 1.5px solid #00ffcc;
    transition: box-shadow 0.3s ease;
  }
  .card:hover {
    box-shadow:
      0 0 25px #00ffccee,
      inset 0 0 20px #00ffccbb;
  }

  /* Split layout inside card */
  .split {
    display: flex;
    gap: 2rem;
    align-items: center;
  }

  .split > div {
    flex: 1;
  }
.card1 {
  width: 100%;            /* full width of parent */
  max-width: 1100px;      /* max width to prevent too wide */
  margin: 0 auto;         /* center horizontally */
  background: rgba(10, 10, 10, 0.85);
  border-radius: 15px;
  padding: 2rem 4rem;     /* equal left and right padding */
  box-shadow:
    0 0 15px #00ffccaa,
    inset 0 0 12px #00ffcc77;
  border: 1.5px solid #00ffcc;
  transition: box-shadow 0.3s ease;
  box-sizing: border-box; /* include padding in width */
}

.user-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.5rem;
  font-weight: 700;
  font-family: 'Orbitron', sans-serif;
  color: #00ffcc;
  padding: 1rem 4rem;  /* bigger horizontal padding */
  background: rgba(0, 255, 204, 0.1);
  border-radius: 12px;
  box-shadow: inset 0 0 10px #00ffccaa;
  width: 100%;        /* full width of card1 */
  max-width: none;    /* no max-width */
  box-sizing: border-box; /* include padding in width */
}


/* Glow effect still applies on small screens */
.card1:hover {
  box-shadow:
    0 0 25px #00ffccee,
    inset 0 0 20px #00ffccbb;
  transition: box-shadow 0.3s ease-in-out;
}

/* Ensure responsiveness */
.user-info {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
}

/* Name stays aligned and readable */
.user-info .name {
  flex: 1 1 60%;
  text-align: left;
  font-size: clamp(1rem, 2.5vw, 1.2rem);
  text-shadow: 0 0 6px #00ffccaa;
  word-break: break-word;
}

/* Grade adjusts nicely on smaller screens */
.user-info .grade {
  flex: 1 1 30%;
  text-align: right;
  font-size: clamp(1.2rem, 5vw, 2rem);
  color: #39ff14;
  text-shadow: 0 0 10px #39ff14cc;
  font-weight: 900;
}

/* Optional: Ensure card doesn't overflow screen */
@media (max-width: 400px) {
  .card1 {
    padding: 1rem;
    border-radius: 16px;
  }

  .user-info {
    flex-direction: column;
    align-items: flex-start;
  }

  .user-info .grade {
    text-align: left;
    margin-top: 0.3rem;
  }
}


  /* Headings */
  h1, h2 {
    margin-top: 0;
  }

  h1 {
     font-family: 'arial', cursive, sans-serif;
    font-size: 2rem;
    text-align: center;
    color: #39ff14;
  }

  h2 {
    font-family: 'Orbitron', sans-serif;
    font-size: 1.8rem;
    margin-bottom: 1rem;
    color: #00ffcc;
    text-shadow: 0 0 6px #00ffccaa;
  }

  /* Difficulty Buttons */
  .difficulty-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .btn-level {
    background: linear-gradient(145deg, #111, #333);
    border-radius: 20px;
    border: 2px solid transparent;
    padding: 1rem 1.5rem;
    font-weight: 700;
    color: white;
    font-family: 'Orbitron', sans-serif;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    flex: 1 1 130px;
    text-align: center;
    user-select: none;
  }

  .btn-level.beginner {
    background: linear-gradient(145deg, #1a73e8, #0d47a1);
    border-color: #0a3c8a;
  }

  .btn-level.advance {
    background: linear-gradient(145deg, #e67e22, #b45508);
    border-color: #8f4603;
  }

  .btn-level.expert {
    background: linear-gradient(145deg, #e74c3c, #b71c1c);
    border-color: #8a1717;
  }

  .btn-level:hover,
  .btn-level:focus-visible {
    transform: translateZ(10px) scale(1.1);
    border-color: #39ff14;
    box-shadow: 0 0 12px #39ff14cc;
    outline-offset: 3px;
    outline: 2px solid #39ff14cc;
    /* outline-radius: 20px; */
    outline-style: solid;
  }

  /* Badges */
  ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  li {
    font-size: 1.2rem;
    margin: 0.7rem 0;
    display: flex;
    align-items: center;
    cursor: default;
    color: #00ffcccc;
  }

  .badge-icon {
    font-size: 2rem;
    margin-right: 1rem;
    animation: pulse 1.5s infinite ease-in-out;
  }

  @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
  }

  .badge-content {
    border-top: 1px solid #00ffcc44;
    padding-top: 1rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #a0fffccc;
    text-align: center;
  }

  .badge-content h3 {
    font-weight: 700;
    font-size: 1.4rem;
    margin-bottom: 0.5rem;
    color: #00ffcc;
    position: relative;
  }

  .badge-content h3::after {
    content: "";
    position: absolute;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #00ffcc, #00cccc);
    left: 50%;
    bottom: -6px;
    transform: translateX(-50%);
    border-radius: 2px;
    animation: underlineGlow 2s infinite alternate ease-in-out;
  }

  @keyframes underlineGlow {
    0% {
      box-shadow: 0 0 5px #00ffcc, 0 0 15px #00cccc;
    }
    100% {
      box-shadow: 0 0 15px #00ffcc, 0 0 25px #00cccc;
    }
  }

  .badge-content p {
    font-size: 1rem;
    line-height: 1.4;
    max-width: 450px;
    margin: 0 auto;
    color: #88ffeeaa;
  }

  /* Logout link */
  .logout {
    display: block;
    text-align: center;
    margin-top: 1rem;
    color: #ff4f4f;
    font-weight: 900;
    text-decoration: none;
    font-family: 'Orbitron', sans-serif;
    transition: color 0.3s ease;
    user-select: none;
  }

  .logout:hover,
  .logout:focus-visible {
    color: #ff9999;
    outline-offset: 3px;
    outline: 2px solid #ff9999aa;
    border-radius: 10px;
    outline-style: solid;
  }

  /* Responsive */
  @media (max-width: 700px) {
    .split {
      flex-direction: column;
    }
    .left-side {
      border-right: none;
      padding-right: 0;
      padding-bottom: 1.5rem;
    }
    .right-side {
      padding-left: 0;
    }
    .user-info {
      flex-direction: column;
      gap: 0.5rem;
      font-size: 1.2rem;
      text-align: center;
    }
    .user-info .grade {
      font-size: 1.5rem;
    }
  }

  .difficulty-section {
  perspective: 1000px; /* para may 3D perspective */
}

.btn-level {
  /* Remove old shadows and add 3D base */
  background: linear-gradient(145deg, #222, #111);
  border: none;
  padding: 1rem 1.8rem;
  font-weight: 800;
  color: white;
  font-family: 'Orbitron', sans-serif;
  cursor: pointer;
  border-radius: 15px;
  box-shadow:
    0 5px 15px rgba(0, 255, 204, 0.6),
    inset 0 0 10px rgba(0, 255, 204, 0.4);
  transition:
    transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
    box-shadow 0.3s ease;
  flex: 1 1 130px;
  text-align: center;
  user-select: none;
  transform-style: preserve-3d;
  position: relative;
}

/* Differentiate colors per difficulty */
.btn-level.beginner {
  background: linear-gradient(145deg, #0d47a1, #1a73e8);
  box-shadow:
    0 8px 20px rgba(26, 115, 232, 0.6),
    inset 0 0 15px rgba(26, 115, 232, 0.6);
}

.btn-level.advance {
  background: linear-gradient(145deg, #b45508, #e67e22);
  box-shadow:
    0 8px 20px rgba(230, 126, 34, 0.7),
    inset 0 0 15px rgba(230, 126, 34, 0.6);
}

.btn-level.expert {
  background: linear-gradient(145deg, #b71c1c, #e74c3c);
  box-shadow:
    0 8px 20px rgba(231, 76, 60, 0.7),
    inset 0 0 15px rgba(231, 76, 60, 0.6);
}

/* 3D hover effect */
.btn-level:hover,
.btn-level:focus-visible {
  transform: translateZ(20px) scale(1.1) rotateX(10deg) rotateY(5deg);
  box-shadow:
    0 15px 30px rgba(57, 255, 20, 0.9),
    inset 0 0 25px rgba(57, 255, 20, 0.8);
  border-color: transparent;
  outline: none;
}

/* Press effect on click */
.btn-level:active {
  transform: translateZ(10px) scale(1.05) rotateX(5deg) rotateY(2deg);
  box-shadow:
    0 8px 15px rgba(57, 255, 20, 0.7),
    inset 0 0 10px rgba(57, 255, 20, 0.6);
}
header {
  width: 100%;
  background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
  color: #00ffcc;
  padding: 20px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
  position: sticky;
  top: 0;
  z-index: 100;
  font-family: 'Orbitron', sans-serif;
}


.header-title {
  font-size: 2rem;
  letter-spacing: 1px;
  text-shadow: 1px 1px 5px black;
}

.menu {
  position: relative;
  display: inline-block;
}

.gear {
  cursor: pointer;
  transition: transform 0.3s ease;
  stroke: #00ffcc;
}

.gear:hover {
  transform: rotate(90deg);
}

.dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 60px;
  background-color: #1a1a1a;
  box-shadow: 0 8px 16px rgba(0,0,0,0.6);
  min-width: 180px;
  z-index: 1000;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #00ffcc;
}

.dropdown a {
  color: #00ffcc;
  padding: 12px 18px;
  text-decoration: none;
  display: block;
  transition: background 0.2s;
}

.dropdown a:hover {
  background-color: #003333;
}

.show {
  display: block;
}

#background-fly {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  overflow: hidden;
  z-index: -1;
}

.fly-item {
  position: absolute;
  font-size: 20px;
  color: #00ffcc;
  opacity: 0.8;
  font-weight: bold;
  animation: flyUp 10s linear infinite;
  font-family: 'Orbitron', sans-serif;
  text-shadow: 0 0 5px #00ffcc;
}

@keyframes flyUp {
  0% {
    transform: translateY(100vh) scale(1);
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
  100% {
    transform: translateY(-20vh) scale(1.5);
    opacity: 0;
  }
}


.badge-list li {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.badge-details {
  max-width: 500px;
}

.badge-details strong {
  font-size: 1.3rem;
  color: #00ffcc;
  display: block;
  margin-bottom: 0.3rem;
}

.badge-details p {
  font-size: 1rem;
  color: #a0ffee;
  line-height: 1.4;
  margin: 0;
}



header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 15px;
  background-color: #222;
  color: white;
  position: relative;
  font-family: Arial, sans-serif;
}

.header-title {
  font-size: 1.5rem;
  font-weight: bold;
}

.menu {
  position: relative;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1rem;
  cursor: default;
  user-select: none;
}

.menu svg.gear {
  cursor: pointer;
  fill: none;
  stroke: white;
  stroke-width: 6;
  stroke-linejoin: round;
  stroke-linecap: round;
  flex-shrink: 0;
}

.dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background-color: #333;
  border-radius: 5px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.3);
  display: none;
  flex-direction: column;
  min-width: 150px;
  z-index: 1000;
}

.dropdown a {
  color: white;
  padding: 10px 15px;
  text-decoration: none;
  font-size: 1rem;
  transition: background-color 0.2s ease;
}

.dropdown a:hover,
.dropdown a:focus {
  background-color: #555;
  outline: none;
}

/* Show dropdown when active */
.dropdown.show {
  display: flex;
}
/* Responsive tweaks for small screens */
@media (max-width: 480px) {
header {
  position: sticky;
  top: 0;
  z-index: 1000;
  width: 130%; /* Use 100%, not 120% */
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  background-color: #f8f8f8;
  border-bottom: 1px solid #ccc;
  box-sizing: border-box;
}


  .header-title {
    font-size: 1.2em;
    margin: 0;
  }

  .menu {
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .dropdown {
    min-width: 120px;
    right: 0;
  }
}




    
</style>
</head>
<body>
<header>
  <div class="header-title">Crack a Number</div>
  
  <div class="menu">
    <!-- Profile Picture + Username -->
  
    <span style="margin-right: 15px;">Cracker: <?= htmlspecialchars($name) ?></span>
      <img 
      src="<?= htmlspecialchars($user['profile_pic'] ?: 'assets/default-profile.png') ?>" 
      alt="Profile Picture" 
      onclick="openModal()" 
      style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #00ffcc; margin-right: 10px; vertical-align: middle; cursor: pointer;" 
    />


    <!-- Gear Icon -->
    <svg class="gear" onclick="toggleDropdown()" viewBox="0 0 100 100" width="40" height="40"
      fill="none" stroke="#00ffcc" stroke-width="6" stroke-linejoin="round" stroke-linecap="round"
      aria-label="Settings menu" role="button" tabindex="0">
      <circle cx="50" cy="50" r="15"/>
      <path d="M80 50h10M10 50h10M50 80v10M50 10v10M68 68l7 7M25 25l7 7M68 32l7-7M25 75l7-7"/>
    </svg>

    <!-- Dropdown Menu -->
    <div id="dropdownMenu" class="dropdown">
      <a href="profile.php">Profile</a>
      <a href="badges.php">Badges</a>
      <a href="crackers.php">Crackers</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</header>



  <div id="background-fly"></div>
<br>


     <audio id="myAudio" src="sound_effect/GameMath.mp3" preload="auto" loop></audio>


<script>
  window.addEventListener('DOMContentLoaded', () => {
    const audio = document.getElementById('myAudio');

    // Try to play after a slight delay to improve compatibility
    setTimeout(() => {
      audio.play().catch(error => {
        console.warn('Autoplay blocked. Waiting for user interaction...');

        // Add an event listener to play on first user interaction
        function playOnInteraction() {
          audio.play();
          window.removeEventListener('click', playOnInteraction);
          window.removeEventListener('keydown', playOnInteraction);
        }

        window.addEventListener('click', playOnInteraction);
        window.addEventListener('keydown', playOnInteraction);
      });
    }, 500);
  });
</script>

<div class="container">
 
<section class="card1">
  <div class="user-info">
    <!-- <div class="name">User, <?= htmlspecialchars($name) ?></div> -->
    <div class="grade">Grade <?= htmlspecialchars($grade) ?></div>
  </div>
</section>



 <section class="card">
    <h1>Crack a Number Challenge</h1>
  </section>


  <section class="card">
    <h2>Choose Your Difficulty</h2>
    <div class="split">
      <div class="left-side">
        <p>Pick a level that matches your skill and start challenging yourself!</p>
        <ul>
          <li>Beginner - For those just starting out</li>
          <li>Advance - Ready for more complexity</li>
          <li>Expert - Test your mastery</li>
        </ul>
      </div>
      <div class="right-side difficulty-buttons">
       <button class="btn-level beginner" onclick="goToGame('beginner')">Beginner</button>
<button class="btn-level advance" onclick="goToGame('advance')">Advance</button>
<button class="btn-level expert" onclick="goToGame('expert')">Expert</button>

      </div>
    </div>
  </section>

  <section class="card">
    <h2>Badges</h2>
    <div class="split">
      <div class="right-side">


      <div class="left-side">
        <ul>
       <ul class="badge-list">
  <li>
    <span class="badge-icon">
<style>
  .slow_learner_beginner,
  .moderate_mover_advanced,
  .fast_learner_expert {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    transition: transform 0.3s ease;
    cursor: pointer; /* pointer cursor para ma-clickable */
  }

  .slow_learner_beginner.zoomed,
  .moderate_mover_advanced.zoomed,
  .fast_learner_expert.zoomed {
    transform: scale(2); /* zoom 2x */
    z-index: 1000; /* naka-foreground kapag zoomed */
    position: relative;
  }
</style>


<img class="slow_learner_beginner - default" src="badges/slow_learner_beginner - default.jpg" alt="Beginner Badge" />

    </span>
    <div class="badge-details">
      <strong>Beginner Badge</strong>
      <p>Earned after completing your first quiz. A great start to your learning journey!</p>
    </div>
  </li>

  <li>
    <span class="badge-icon"><style>
  .moderate_mover_advanced{
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
  }
</style>

<img class="moderate_mover_advanced - default" src="badges/moderate_mover_advanced - default.jpg" alt="Beginner Badge" />
</span>
    <div class="badge-details">
      <strong>Advanced Badge</strong>
      <p>Awarded for maintaining consistent scores above 80%. You're leveling up!</p>
    </div>
  </li>

  <li>
    <span class="badge-icon"><style>
  .fast_learner_expert{
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
  }
</style>

<img class="fast_learner_expert - default" src="badges/fast_learner_expert - default.jpg" alt="Beginner Badge" />
</span>
    <div class="badge-details">
      <strong>Expert Badge</strong>
      <p>Achieved by getting a perfect score on a quiz. True mastery unlocked!</p>
    </div>
  </li>
</ul>

        </ul>
      </div>
      <div class="right-side badge-content">
        <h3>About Badges</h3>
        <p>Earn badges by completing challenges at each difficulty level. Show off your progress and unlock exclusive content!</p>
      </div>
    </div>
  </section>

  <section class="card" style="text-align: center;">
    <a href="logout.php" class="logout">Logout</a>
  </section>

</div>


<script>
  // You need these variables set, maybe from PHP
  const userName = <?= json_encode($name) ?>;
  const userGrade = <?= json_encode($grade) ?>;

  function goToGame(level) {
    // Redirect to game.php with grade and level as GET params
    window.location.href = `game.php?grade=${encodeURIComponent(userGrade)}&level=${encodeURIComponent(level)}`;
  }
</script>

<script>
  function toggleDropdown() {
    document.getElementById("dropdownMenu").classList.toggle("show");
  }

  window.onclick = function(event) {
    if (!event.target.closest('.menu')) {
      document.getElementById("dropdownMenu").classList.remove("show");
    }
  }
</script>
<script>
  const chars = [...Array(20).keys()].map(n => (n + 1).toString())
    .concat(['+', '-', '×', '÷']);

  function createFlyingItem() {
    const item = document.createElement('div');
    item.className = 'fly-item';
    item.textContent = chars[Math.floor(Math.random() * chars.length)];

    item.style.left = Math.random() * 100 + 'vw';
    item.style.fontSize = (Math.random() * 20 + 14) + 'px';
    item.style.animationDuration = (Math.random() * 10 + 5) + 's';

    document.getElementById('background-fly').appendChild(item);

    setTimeout(() => {
      item.remove();
    }, 15000);
  }

  setInterval(createFlyingItem, 400); // Add new item every 400ms
</script>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
<script>
  const badges = document.querySelectorAll('img.slow_learner_beginner, img.moderate_mover_advanced, img.fast_learner_expert');

  badges.forEach(img => {
    img.addEventListener('click', () => {
      img.classList.toggle('zoomed');
    });
  });
</script>
</body>
</html>
