<?php
require 'config.php';

function showCrackers($data, $gradeLevel, $defaultAvatar) {
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Rank</th>';
    echo '<th>Crackers</th>';
    echo '<th>Name</th>';
    echo '<th>Level</th>';
    echo '<th>Badge</th>';
    echo '<th>Earned At</th>';
    echo '<th>Accuracy</th>';
    echo '<th>Correct</th>';
    echo '<th>Wrong</th>';
    echo '<th>Times Up</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $rank = 1;
    foreach ($data as $row) {
        if ((int)$row['grade'] === $gradeLevel) {
            $avatar = !empty($row['profile_pic']) ? htmlspecialchars($row['profile_pic']) : $defaultAvatar;

            $badgeImg = '';
            if (!empty($row['badge_image'])) {
                $badgeImg = 'badges/' . htmlspecialchars($row['badge_image']);
            }

            echo '<tr>';
            echo '<td data-label="Rank">' . $rank . '</td>';
            echo '<td data-label="Profile"><img src="' . $avatar . '" alt="Profile Pic" class="profile-pic"></td>';
            echo '<td data-label="Name">' . htmlspecialchars($row['name'] ?? 'Unknown') . '</td>';
            echo '<td data-label="Level">' . htmlspecialchars($row['level']) . '</td>';
            echo '<td data-label="Badge">';
            if ($badgeImg) {
echo '<img src="' . $badgeImg . '" class="badge-img modal-trigger" data-img="' . $badgeImg . '">';


            }
            echo '</td>';
            echo '<td data-label="Earned At">' . htmlspecialchars($row['earned_at']) . '</td>';
            echo '<td data-label="Accuracy">' . number_format($row['accuracy'], 2) . '%</td>';
            echo '<td data-label="Correct">' . (int)$row['correct_count'] . '</td>';
            echo '<td data-label="Wrong">' . (int)$row['wrong_count'] . '</td>';
            echo '<td data-label="Times Up">' . (int)$row['times_up_count'] . '</td>';
            echo '</tr>';
            $rank++;
        }
    }

    echo '</tbody></table>';
}

$defaultAvatar = 'assets/default-profile.png';

try {
    $stmt = $pdo->query("
        SELECT 
            b.id,
            b.session_id,
            b.user_id,
            b.grade,
            b.level,
            b.badge_image,
            b.earned_at,
            b.correct_count,
            b.wrong_count,
            b.times_up_count,
            b.accuracy,
            u.name,
            u.profile_pic
        FROM user_badges b
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY b.grade ASC, b.accuracy DESC
    ");
    $badgeRows = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
    $badgeRows = [];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Crackers Leaderboard</title>
<style>
.badge-img {
    width: 45px;
    height: 50px;
    object-fit: contain;
    transition: transform 0.3s ease;
    border-radius: 8px;
     cursor: pointer;
}

.zoom-hover:hover {
    transform: scale(1.5);
    z-index: 10;
    position: relative;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    border: 2px solid #fff;
}
.zoom-interact:hover,
.zoom-interact:active {
    transform: scale(1.5);
    z-index: 10;
    position: relative;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    border: 2px solid #fff;
}
.zoomed {
    transform: scale(1.5) !important;
    z-index: 10;
    position: relative;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    border: 2px solid #fff;
}
.badge-img:hover {
    transform: scale(1.1);
}
/* Modal Styles */
.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.8);
  justify-content: center;
  align-items: center;
}

.modal-backdrop {
  position: relative;
}

#modalImage {
  max-width: 90%;
  max-height: 80vh;
  border-radius: 10px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.modal-content {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}

.modal-content img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
}

  /* Page body */
  body {
    background: linear-gradient(to right, #1c1c1c, #2c5364);
    color: #00ffcc;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  h2 {
    text-align: center;
    font-size: 2.4rem;
    margin-bottom: 1.5rem;
    letter-spacing: 1px;
  }

  /* Tabs */
.tabs {
  position: sticky;
  top: 0;
  z-index: 1000;
  background-color: #000; 
  padding: 1rem 0;
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.tabs button {
  background: transparent;
  border: 2px solid #00ffcc;
  color: #00ffcc;
  padding: 0.75rem 1.8rem;
  cursor: pointer;
  font-size: 1.1rem;
  border-radius: 12px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.tabs button.active,
.tabs button:hover {
  background-color: #00ffcc;
  color: #000;
}

  /* Tab content */
  .tab-content {
    display: none;
  }

  .tab-content.active {
    display: block;
  }

  /* Table styling */
  table {
    width: 100%;
    border-collapse: collapse;
    background-color: rgba(5, 5, 5, 0.8);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 10px #00ffccaa;
  }

  thead tr {
    background-color: #004d4d;
  }

  thead th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #00ffcc;
  }

  tbody tr {
    border-bottom: 1px solid #003333;
    transition: background-color 0.25s ease;
  }

  tbody tr:hover {
    background-color: #006666;
  }

  tbody td {
    padding: 10px 15px;
    vertical-align: middle;
  }

  /* Profile image */
  .profile-pic {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #00ffcc;
    box-shadow: 0 0 5px #00ffcc;
  }

  /* Back link */
  a.back-link {
    margin: 2rem auto;
    color: #00ffcc;
    text-decoration: none;
    font-size: 1.3rem;
    border: 2px solid #00ffcc;
    padding: 0.65rem 1.3rem;
    border-radius: 12px;
    display: inline-block;
    transition: background-color 0.3s ease, color 0.3s ease;
  }

  a.back-link:hover {
    background: #00ffcc;
    color: #000;
  }

  .badge-img {
  width: 40px;
  height: 40px;
  vertical-align: middle;
  margin-right: 8px;
  border-radius: 6px;
  box-shadow: 0 0 6px #00ffccaa;
  object-fit: contain;
}
/* Card container */
.card {
  background: rgba(10, 10, 10, 0.9);
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 0 20px #00ffccaa;
  max-width: 1500px;
  width: 90%;
  margin: 3rem auto;
}

/* Responsive for small screens */
@media (max-width: 480px) {
  .card {
    padding: 1rem;
    margin: 1rem;
    width: 95%;
    box-shadow: 0 0 15px #00ffcc99;
  }

  h2 {
    font-size: 1.6rem;
  }

  /* Tabs stacked vertically */
  .tabs {
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }

  .tabs button {
    padding: 0.6rem 1rem;
    font-size: 1rem;
  }

  /* Make table scrollable horizontally */
  table {
    display: block;
    width: 100%;
    overflow-x: auto;
    white-space: nowrap;
    border-radius: 12px;
    box-shadow: 0 0 10px #00ffccaa;
  }

  thead, tbody, tr, th, td {
    display: block;
  }

  thead tr {
    position: absolute;
    top: -9999px;
    left: -9999px;
  }

  tbody tr {
    margin-bottom: 1rem;
    background-color: rgba(5, 5, 5, 0.85);
    padding: 1rem;
    border-radius: 12px;
  }

  tbody td {
    border: none;
    position: relative;
    padding-left: 50%;
    text-align: left;
  }

  tbody td::before {
    content: attr(data-label);
    position: absolute;
    left: 15px;
    font-weight: 600;
    color: #00ffcc;
  }

  /* Smaller images */
  .profile-pic {
    width: 40px;
    height: 40px;
  }

  .badge-img {
    width: 30px;
    height: 30px;
  }
}

</style>
</head>
<body>
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


<div class="modal" id="imageModal">
    <div class="modal-content">
        <img src="" id="modalImage" alt="Badge">
        <span id="closeModal" style="position: absolute; top: 20px; right: 30px; font-size: 32px; color: white; cursor: pointer;">&times;</span>
    </div>
</div>


<div class="card">
  <h2>Top Crackers Leaderboard</h2>

  <div class="tabs">
    <button class="active" onclick="showTab('grade4', this)">Grade 4</button>
    <button onclick="showTab('grade5', this)">Grade 5</button>
    <button onclick="showTab('grade6', this)">Grade 6</button>
  </div>

  <div id="grade4" class="tab-content active">
    <?php showCrackers($badgeRows, 4, $defaultAvatar); ?>
  </div>

  <div id="grade5" class="tab-content">
    <?php showCrackers($badgeRows, 5, $defaultAvatar); ?>
  </div>

  <div id="grade6" class="tab-content">
    <?php showCrackers($badgeRows, 6, $defaultAvatar); ?>
  </div>
</div>

<a href="welcome.php" class="back-link">← Back </a>

<script>
function showTab(tabId, btn) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.getElementById(tabId).classList.add('active');

  document.querySelectorAll('.tabs button').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const badges = document.querySelectorAll('.zoom-interact');
    badges.forEach(function(badge) {
        badge.addEventListener('click', function () {
            badge.classList.toggle('zoomed');
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const closeModal = document.getElementById('closeModal');

    // Show modal when badge is clicked
    document.querySelectorAll('.modal-trigger').forEach(img => {
        img.addEventListener('click', () => {
            modalImg.src = img.getAttribute('data-img');
            modal.style.display = 'flex';
        });
    });

    // Close modal when clicking outside the image
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // ✅ Close modal when clicking the "×" icon
    closeModal.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Optional: ESC key support
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            modal.style.display = 'none';
        }
    });
});
</script>




</body>
</html>