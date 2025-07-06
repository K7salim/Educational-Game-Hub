<?php
session_start();
require 'config.php'; // PDO connection

// Check if user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['user_grade'], $_SESSION['user_name'])) {
    header("Location: login.php");
    exit;
}

// Get session data
$user_id = $_SESSION['user_id'];
$grade = $_SESSION['user_grade'];
$name = $_SESSION['user_name'];

// Fetch profile_pic
try {
    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Default profile picture if none set
    $profilePic = $user['profile_pic'] ?? 'assets/default-profile.png';
} catch (PDOException $e) {
    $profilePic = 'assets/default-profile.png';
}

// Badge colors by difficulty level
$levelColors = [
    'easy' => '#28a745',
    'medium' => '#ffc107',
    'hard' => '#dc3545',
];

// Fetch user badges with accuracy
try {
    $stmt = $pdo->prepare("SELECT grade, level, badge, earned_at, correct_count, wrong_count, times_up_count, accuracy FROM user_badges WHERE user_id = ? ORDER BY earned_at DESC");
    $stmt->execute([$user_id]);
    $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $badges = [];
}

// Determine current level
$level = $badges[0]['level'] ?? 'easy';  // Fallback to 'easy'
$levelColor = $levelColors[$level] ?? '#28a745';
?>



<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8" />

<meta name="viewport" content="width=device-width, initial-scale=1" />

<title>Badges - Crack a Number</title>

<style>

  /* Your CSS styles here (unchanged) */

  html, body { margin: 0; padding: 0; }

  * { box-sizing: border-box; }

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

  .menu { position: relative; display: inline-block; }

  .user-info {

    font-size: 1rem;

    font-weight: 700;

    font-family: 'Orbitron', sans-serif;

    color: #00ffcc;

    display: flex;

    align-items: center;

    gap: 0.5rem;

  }

  .gear {

    cursor: pointer;

    transition: transform 0.3s ease;

    stroke: #00ffcc;

    margin-left: 1rem;

  }

  .gear:hover { transform: rotate(90deg); }

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

  .dropdown a:hover { background-color: #003333; }

  .show { display: block; }

  .container {

    max-width: 800px;

    width: 100%;

    display: flex;

    flex-direction: column;

    gap: 2rem;

    margin-top: 2rem;

  }

  .card {

    background: rgba(10, 10, 10, 0.85);

    border-radius: 15px;

    padding: 2rem 2.5rem;

    box-shadow:

      0 0 15px #00ffccaa,

      inset 0 0 12px #00ffcc77;

    border: 1.5px solid #00ffcc;

  }

  ul.badge-list {

    list-style: none;

    padding: 0;

    margin: 0;

  }

  ul.badge-list li {

    display: flex;

    align-items: flex-start;

    gap: 1rem;

    margin-bottom: 1.5rem;

    cursor: default;

    color: #00ffcccc;

  }

  ul.badge-list li img {

    width: 80px;

    height: 80px;

    border-radius: 50px;

    box-shadow: 0 0 10px #00ffccaa;

    object-fit: contain;

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

    margin: 0.2rem 0;

  }

  /* Base header style */

  .main-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 10px 20px;

    box-sizing: border-box;

    background-color: #f8f9fa;

    width: 100%;

  }

  /* Header title and menu */

  .header-title {

    font-size: 1.5rem;

    font-weight: bold;

  }

  .menu {

    display: flex;

    flex-direction: column;

    align-items: flex-end;

  }

  /* Dropdown menu styling */

  .dropdown {

    display: none;

    position: absolute;

    right: 20px;

    top: 60px;

    background-color: white;

    border: 1px solid #ccc;

    padding: 10px;

  }

  .menu .gear {

    cursor: pointer;

  }

  /* Media query for mobile */

  @media (max-width: 480px) {

    .main-header {

      padding-left: 0;

      padding-right: 0;

      width: 100vw;

    }

    .menu {

      padding-right: 10px;

    }

    .header-title {

      padding-left: 10px;

    }

  }

</style>

</head>

<body>



<header class="main-header">
  <div class="header-title">Crack a Number</div>

  <div class="menu">

    <div class="user-info" style="display: flex; align-items: center; gap: 12px;">
     
      <!-- User Info Text -->
      <p style="margin: 0;">
        Cracker: <span style="color:#007BFF;"><?= htmlspecialchars($name) ?></span>
        <!-- (Grade <span style="color:#28a745;"><?= htmlspecialchars($grade) ?></span>) -->
      </p>
       <!-- Profile Picture -->
      <img 
        src="<?= htmlspecialchars($profilePic) ?>" 
        alt="Profile Picture" 
        class="profile-pic" 
        style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #00ffcc;"
        onclick="openModal()" 
      />

      <!-- Gear Icon -->
      <!-- <svg
        class="gear"
        onclick="toggleDropdown()"
        viewBox="0 0 100 100"
        width="30"
        height="30"
        fill="none"
        stroke="#00ffcc"
        stroke-width="6"
        stroke-linejoin="round"
        stroke-linecap="round"
        aria-label="Settings menu"
        role="button"
        tabindex="0"
      >
        <circle cx="50" cy="50" r="15" />
        <path d="M80 50h10M10 50h10M50 80v10M50 10v10M68 68l7 7M25 25l7 7M68 32l7-7M25 75l7-7" />
      </svg>
    </div> -->

    <!-- Dropdown Menu -->
    <div id="dropdownMenu" class="dropdown" style="display: none;">
      <a href="welcome.php">Home</a>
      <a href="#" onclick="openModal()">Profile</a>
      <a href="crackers.php">Crackers</a>
      <a href="logout.php">Logout</a>
    </div>

  </div>
</header>



 <style>

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

    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);

    display: none;

    flex-direction: column;

    min-width: 150px;

    z-index: 1000;

    border: 1px solid #00ffcc;

  }



  .dropdown.show {

    display: flex;

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









.animated-badge img {

  animation: twistBounce 2s ease-in-out infinite;

  transform-origin: center;

  width: 150px;

  display: block;

  margin: 0 auto;

}



@keyframes twistBounce {

  0% {

    transform: rotate(0deg) scale(1);

  }

  20% {

    transform: rotate(20deg) scale(1.2);

  }

  40% {

    transform: rotate(-15deg) scale(1);

  }

  60% {

    transform: rotate(10deg) scale(1.1);

  }

  80% {

    transform: rotate(-5deg) scale(1);

  }

  100% {

    transform: rotate(0deg) scale(1);

  }

}





</style>







</header>

<script>



function toggleDropdown() {

  const dropdown = document.getElementById('dropdownMenu');

  dropdown.classList.toggle('show');

}



window.onclick = function(event) {

  const dropdown = document.getElementById('dropdownMenu');

  if (!event.target.closest('.menu')) {

    dropdown.classList.remove('show');

  }

}





</script>



    <audio id="myAudio" src="sound_effect/Gamemath2.mp3" preload="auto" loop></audio>





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

  <div class="card">

    <h2>Your Badges</h2>

    <ul class="badge-list">

      <?php if (empty($badges)): ?>

        <li>No badges earned yet.</li>

      <?php else: ?>

        <?php foreach ($badges as $badge): ?>

          <li>

<div class="animated-badge">

  <img src="badges/<?= htmlspecialchars($badge['badge']) ?>.jpg" alt="<?= htmlspecialchars($badge['badge']) ?>">

</div>

            <div class="badge-details">

              <strong><?= ucwords(str_replace('_', ' ', htmlspecialchars($badge['badge']))) ?></strong>

              <p>Grade: <?= htmlspecialchars($badge['grade']) ?> &nbsp;|&nbsp; Level: <?= ucfirst(htmlspecialchars($badge['level'])) ?></p>

              <p>Accuracy Average: <?= isset($badge['accuracy']) ? number_format($badge['accuracy'], 2) : 'N/A' ?>%</p>

              <p>Correct: <?= (int)$badge['correct_count'] ?> &nbsp;&nbsp; Wrong: <?= (int)$badge['wrong_count'] ?> &nbsp;&nbsp; Times Up: <?= (int)$badge['times_up_count'] ?></p>

              <p><small>Earned on: <?= date('M d, Y', strtotime($badge['earned_at'])) ?></small></p>

            </div>

          </li>

          <hr>

        <?php endforeach; ?>

      <?php endif; ?>

    </ul>

  </div>

  <div style="width: 100%; max-width: 700px; margin: 1rem auto 0; text-align: left;">

    <a href="welcome.php" 

       style="

         display: inline-block;

         padding: 0.5rem 1rem;

         background: #00ffcc;

         color: #1c1c1c;

         font-weight: bold;

         border-radius: 8px;

         text-decoration: none;

         box-shadow: 0 0 8px #00ffccaa;

         transition: background 0.3s ease;

       "

       onmouseover="this.style.background='#00c4b4'"

       onmouseout="this.style.background='#00ffcc'"

    >← Back to Home</a>

  </div>

</div>



<script>

  function toggleDropdown() {

    const dropdown = document.getElementById('dropdownMenu');

    dropdown.classList.toggle('show');

  }

  window.onclick = function(event) {

    const dropdown = document.getElementById('dropdownMenu');

    if (!event.target.closest('.menu')) {

      dropdown.classList.remove('show');

    }

  }









  const img = document.querySelector('.animated-badge img');

img.classList.remove('twist');

void img.offsetWidth; // trigger reflow

img.classList.add('twist');



</script>



</body>

</html>

