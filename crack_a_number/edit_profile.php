<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get new values from the form
        $newName = $_POST['name'];
        $newGrade = $_POST['grade'];
        $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $newPic = $_POST['pic'] ?: 'assets/default-profile.png';

        $stmt = $pdo->prepare("UPDATE users SET name = ?, grade = ?, password = ?, profile_pic = ? WHERE id = ?");
        $stmt->execute([$newName, $newGrade, $newPassword, $newPic, $userId]);

        header("Location: profile.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT name, grade, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "User not found.";
        exit;
    }

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
  <title>Edit Profile</title>
  <style>
    body {
      background: #1e1e1e;
      color: #00ffcc;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 400px;
      margin: 2rem auto;
      padding: 2rem;
      background: #222;
      border-radius: 10px;
      box-shadow: 0 0 10px #00ffcc88;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: none;
      border-radius: 5px;
      background: #333;
      color: #fff;
    }
    button {
      padding: 10px 20px;
      background: #00ffcc;
      border: none;
      border-radius: 5px;
      color: #000;
      cursor: pointer;
    }
    .pic-options img {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      margin: 5px;
      border: 2px solid transparent;
      cursor: pointer;
    }
    .selected {
      border-color: #00ffcc;
      box-shadow: 0 0 8px #00ffcc;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Edit Profile</h2>
  <form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required />

    <label>Grade:</label>
    <input type="text" name="grade" value="<?= htmlspecialchars($user['grade']) ?>" required />

    <label>New Password:</label>
    <input type="password" name="password" placeholder="Enter new password" required />

    <label>Profile Picture:</label>
    <input type="hidden" name="pic" id="picInput" value="<?= htmlspecialchars($user['profile_pic']) ?>" />
    <div class="pic-options">
      <img src="assets/boy.png" onclick="selectPic(this, 'assets/boy.png')" />
      <img src="assets/girl.png" onclick="selectPic(this, 'assets/girl.png')" />
    </div>

    <button type="submit">Save Changes</button>
  </form>
</div>

<script>
  function selectPic(img, url) {
    document.getElementById('picInput').value = url;
    document.querySelectorAll('.pic-options img').forEach(i => i.classList.remove('selected'));
    img.classList.add('selected');
  }
</script>

</body>
</html>
