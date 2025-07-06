<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT name, grade, password, created_at, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "User not found.";
        exit;
    }

    $name = $user['name'];
    $grade = $user['grade'];
    $createdAt = $user['created_at'];
    $profilePic = $user['profile_pic'] ?: 'assets/default-profile.png';
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Your Profile</title>
   <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #1e1e1e;
      color: #00ffcc;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    header {
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #00ffcc;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }
    .profile-menu {
      display: flex;
      align-items: center;
      gap: 10px;
      position: relative;
    }
    .profile-menu img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 2px solid #00ffcc;
      cursor: pointer;
    }
    .dropdown {
      display: none;
      position: absolute;
      top: 60px;
      right: 0;
      background-color: #1a1a1a;
      border: 1px solid #00ffcc;
      border-radius: 8px;
      z-index: 100;
    }
    .dropdown a {
      display: block;
      padding: 12px 18px;
      text-decoration: none;
      color: #00ffcc;
    }
    .dropdown a:hover {
      background-color: #003333;
    }
    .gear {
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    .gear:hover {
      transform: rotate(90deg);
    }
    .profile-container {
      max-width: 600px;
      margin: 2rem auto;
      background: #222;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 20px #00ffcc88;
      text-align: center;
    }
    .profile-pic {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 3px solid #00ffcc;
      object-fit: cover;
      cursor: pointer;
    }
    .badge-list {
      margin-top: 2rem;
      text-align: left;
    }
    .badge-item {
      background: #111;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1rem;
    }
    #profileModal {
      display: none;
      position: fixed;
      z-index: 9999;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      overflow: auto;
      max-height: 90vh;  
    }
    .modal-content {
      background-color: #222;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #00ffcc;
      width: 300px;
      margin: 10% auto;
      text-align: center;
    }
    .modal-content .pic-options img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin: 10px;
      border: 2px solid transparent;
      cursor: pointer;
      transition: transform 0.2s ease, border 0.2s ease;
    }
    .modal-content .pic-options img:hover {
      transform: scale(1.05);
      border: 2px solid #00ffcc88;
    }
    .modal-content .pic-options img.selected {
      border: 2px solid #00ffcc;
      box-shadow: 0 0 10px #00ffcc;
    }
    @media (max-width: 480px) {
      .gear { width: 24px; height: 24px; }
    }
    .pic-wrapper {
      position: relative;
      display: inline-block;
      cursor: pointer;
    }
    .edit-icon {
      position: absolute;
      bottom: 0;
      right: 0;
      background: #00ffcc;
      color: #000;
      border-radius: 50%;
      padding: 4px;
      font-size: 14px;
      transform: translate(-25%, -25%);
      box-shadow: 0 0 5px #00ffcc;
    }

    .form-input {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 6px;
  border: 1px solid #00ffcc;
  background-color: #111;
  color: #00ffcc;
  outline: none;
  font-size: 14px;
  transition: border 0.3s ease;
}
.form-input:focus {
  border-color: #00ffaa;
  box-shadow: 0 0 5px #00ffaa88;
}

.btn-primary, .btn-secondary {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s ease, box-shadow 0.3s ease;
}

.btn-primary {
  background-color: #00ffcc;
  color: #000;
  margin-right: 10px;
}
.btn-primary:hover {
  background-color: #00ffaa;
  box-shadow: 0 0 10px #00ffaa88;
}

.btn-secondary {
  background-color: #333;
  color: #00ffcc;
}
.btn-secondary:hover {
  background-color: #222;
  box-shadow: 0 0 10px #00ffcc55;
}
.form-group {
  margin-bottom: 20px;
  width: 100%;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 6px;
  font-size: 14px;
  color: #00ffcc;
  font-weight: 600;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #00ffcc;
  background-color: #111;
  color: #00ffcc;
  border-radius: 6px;
  font-size: 14px;
  outline: none;
  transition: border 0.3s ease;
}
.form-input:focus {
  border-color: #00ffaa;
  box-shadow: 0 0 8px #00ffaa88;
}

.password-container {
  position: relative;
}

.password-input {
  padding-right: 40px;
}

.eye-icon {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  cursor: pointer;
  color: #00ffcc;
  font-size: 18px;
  user-select: none;
}

.button-group {
  text-align: center;
  margin-top: 20px;
}

.btn-primary, .btn-secondary {
  padding: 10px 18px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  font-size: 14px;
  transition: all 0.3s ease;
}

.btn-primary {
  background-color: #00ffcc;
  color: #000;
  margin-right: 10px;
}
.btn-primary:hover {
  background-color: #00ffaa;
  box-shadow: 0 0 10px #00ffaa88;
}

.btn-secondary {
  background-color: #333;
  color: #00ffcc;
}
.btn-secondary:hover {
  background-color: #222;
  box-shadow: 0 0 10px #00ffcc55;
}


  </style>
</head>
<body>

<header>

  <div class="header-title">Crack a Number</div>

  <div class="profile-menu">

    <span>Cracker: <?= htmlspecialchars($name) ?></span>

    <img src="<?= htmlspecialchars($user['profile_pic'] ?: 'assets/default-profile.png') ?>" onclick="openModal()" alt="Profile Picture" />

    <svg class="gear" onclick="toggleDropdown()" viewBox="0 0 100 100" width="30" height="30"

      fill="none" stroke="#00ffcc" stroke-width="6" stroke-linejoin="round"

      stroke-linecap="round">

      <circle cx="50" cy="50" r="15" />

      <path d="M80 50h10M10 50h10M50 80v10M50 10v10M68 68l7 7M25 25l7 7M68 32l7-7M25 75l7-7" />

    </svg>

    <div class="dropdown" id="dropdownMenu">

      <a href="welcome.php">Home</a>

      <a href="badges.php">Badges</a>

      <a href="crackers.php">Crackers</a>

      <a href="logout.php">Logout</a>

    </div>

  </div>

</header>
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


<div class="profile-container">
  <div class="pic-wrapper" onclick="openModal()">
    <img class="profile-pic" src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" /> <a href="edit_profile.php" > </a>
    <div class="edit-icon">✏️</div>
  </div>

  <h2><?= htmlspecialchars($name) ?></h2>
  <p>Grade: <?= htmlspecialchars($grade) ?></p>
  <p>Member since: <?= htmlspecialchars(date("F j, Y", strtotime($createdAt))) ?></p>

  <div class="user-info">
    <h3>User Information</h3>
    <div class="badge-item">
      <strong>Name:</strong> <?= htmlspecialchars($name) ?><br>
      <strong>Grade:</strong> <?= htmlspecialchars($grade) ?><br>
      <strong>Joined:</strong> <?= htmlspecialchars(date("F j, Y", strtotime($createdAt))) ?><br>
      <strong>Password:</strong> <?= htmlspecialchars($user['password']) ?>
      <!-- Or mask like: str_repeat('*', strlen($user['password'])) -->
    </div>
  </div>
</div>


<!-- Modal -->
<div id="profileModal" style="display: none;">
  <div class="modal-content">
    <h3>Edit Profile</h3>
    <form action="update_profile.php" method="post" enctype="multipart/form-data" style="width: 100%; max-width: 350px; margin: 0 auto; text-align: center; font-family: 'Segoe UI', sans-serif;">
      
      <!-- Profile Picture Preview & Picker -->
      <div style="margin-bottom: 20px;">
        <img id="previewPic" 
             src="<?= htmlspecialchars($profilePic) ?>" 
             style="width: 100px; height: 100px; border-radius: 50%; border: 3px solid #00ffcc; box-shadow: 0 0 10px #00ffcc;" />
        <input type="hidden" name="profile_pic" id="picInput" />
        
        <div class="pic-options" style="margin-top: 10px;">
          <img src="assets/boy.png" alt="Boy" onclick="choosePic(this, 'assets/boy.png')" />
          <img src="assets/girl.png" alt="Girl" onclick="choosePic(this, 'assets/girl.png')" />
        </div>
      </div>

      <!-- Take or Upload Photo -->
      <div style="margin-top: 15px;">
        <label for="uploadImage" style="display: block; font-weight: bold; margin-bottom: 5px;">📷 Upload or Take a Photo</label>
        <input type="file"
               id="uploadImage"
               name="upload_image"
               accept="image/*"
               capture="user"
               onchange="previewUploadedImage(event)"
               style="display: block; margin: 0 auto;" />
      </div>

      <!-- Input Fields -->
      <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" id="name"
               value="<?= htmlspecialchars($name) ?>"
               placeholder="Enter your name"
               required class="form-input" />
      </div>

      <div class="form-group">
        <label for="grade">Grade</label>
        <input type="text" name="grade" id="grade"
               value="<?= htmlspecialchars($grade) ?>"
               placeholder="Enter your grade"
               required class="form-input" />
      </div>

      <div class="form-group password-wrapper">
        <label for="passwordInput">New Password</label>
        <div class="password-container">
          <input type="password" name="password" id="passwordInput"
                 placeholder="Enter new password"
                 class="form-input password-input" />
          <span class="eye-icon" onclick="togglePassword()">👁️</span>
        </div>
      </div>

      <!-- Buttons -->
      <div class="button-group">
        <button type="submit" class="btn-primary">💾 Save</button>
        <button type="button" onclick="closeModal()" class="btn-secondary">❌ Cancel</button>
      </div>
    </form>
  </div>
</div>





<script>
  function choosePic(element, url) {
    document.getElementById("picInput").value = url;
    document.getElementById("previewPic").src = url;

    document.querySelectorAll('.pic-options img').forEach(img => img.classList.remove('selected'));
    element.classList.add('selected');
  }

  function previewUploadedImage(event) {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function (e) {
        document.getElementById('previewPic').src = e.target.result;
        document.getElementById('picInput').value = ''; // clear chosen avatar if any
      };
      reader.readAsDataURL(file);
    }
  }

  function togglePassword() {
    const pw = document.getElementById("passwordInput");
    pw.type = pw.type === "password" ? "text" : "password";
  }

  function toggleDropdown() {
    const dropdown = document.getElementById("dropdownMenu");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
  }

  function openModal() {
    document.getElementById("profileModal").style.display = "block";
  }

  function closeModal() {
    document.getElementById("profileModal").style.display = "none";
  }

  // Close dropdown when clicking outside
  window.onclick = function(event) {
    const dropdown = document.getElementById("dropdownMenu");
    if (!event.target.closest(".profile-menu")) {
      dropdown.style.display = "none";
    }
  };
</script>


<script>
  function toggleDropdown() {
    const dropdown = document.getElementById("dropdownMenu");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
  }

  window.onclick = function(event) {
    const dropdown = document.getElementById("dropdownMenu");
    if (!event.target.closest(".profile-menu")) {
      dropdown.style.display = "none";
    }
  };

  function openModal() {
    document.getElementById("profileModal").style.display = "block";
  }

  function closeModal() {
    document.getElementById("profileModal").style.display = "none";
  }

  function choosePic(element, url) {
    document.getElementById("picInput").value = url;
    document.getElementById("previewPic").src = url;
    document.querySelectorAll('.pic-options img').forEach(img => img.classList.remove('selected'));
    element.classList.add('selected');
  }
</script>
<script>
  function togglePassword() {
    const pw = document.getElementById("passwordInput");
    pw.type = pw.type === "password" ? "text" : "password";
  }
</script>



</body>
</html>
