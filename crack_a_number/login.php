<?php
session_start();
require 'config.php';

$grade = $_GET['grade'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
    $stmt->execute([$name]);
    $user = $stmt->fetch();

    // Make sure to hash passwords in real apps!
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_grade'] = $user['grade'];  // <- Change here to user_grade to match welcome.php
        header('Location: welcome.php');
        exit;
    } else {
        $_SESSION['error'] = 'Invalid name or password.';
        header("Location: index.php?grade=" . urlencode($grade));
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crack a Number - Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
  body {
    margin: 0;
    padding: 0;
    font-family: 'Baloo 2', cursive;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    color: #39ff14;
    position: relative;
    text-shadow: 0 0 5px #39ff14, 0 0 10px #39ff14, 0 0 20px #39ff14;
  }

  .floating-numbers {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
  }

  .floating-numbers span {
    position: absolute;
    color: rgba(229, 241, 229, 0.95);
    font-size: 2.5rem;
    font-weight: bold;
    animation: flyUp 12s linear infinite;
    user-select: none;
    opacity: 0;
  }

  @keyframes flyUp {
    0% {
      transform: translateY(100vh) scale(0.8) rotate(0deg);
      opacity: 0;
    }
    20% {
      opacity: 0.3;
    }
    80% {
      opacity: 0.3;
    }
    100% {
      transform: translateY(-120vh) scale(1.2) rotate(360deg);
      opacity: 0;
    }
  }

  .auth-container {
    background: #ffffff;
    border-radius: 25px;
    box-shadow: 0 12px 30px rgba(76, 175, 80, 0.3);
    padding: 3rem 1.5rem;
    width: 360px;
    text-align: center;
    position: relative;
    border: 3px dashed #4CAF50;
    box-sizing: border-box;
    z-index: 1;
    height: 570px;
  }

  .auth-container::before {
    content: "∑ √ π ∞ x² ÷ ±";
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    background: #388E3C;
    color: white;
    font-size: 1rem;
    padding: 0.4rem 1.5rem;
    border-radius: 25px;
    letter-spacing: 2px;
    user-select: none;
  }

  .main-title {
    font-size: 2.2rem;
    color: #388E3C;
    margin-bottom: -1rem;
    font-weight: bold;
  }

  h2 {
    font-size: 2rem;
    color: #1B5E20;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #A5D6A7;
    padding-bottom: 0.5rem;
  }

  form {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    margin-bottom: -2rem;
  }

  .input-wrapper,
  .button-wrapper {
    width: 100%;
    margin-bottom: 1.5rem;
    position: relative;
  }

  .input-wrapper input {
    width: 100%;
    padding: 1rem 1.2rem;
    padding-right: 2.5rem;
    font-size: 1.2rem;
    border: 2px solid #C8E6C9;
    border-radius: 18px;
    background: #F1F8E9;
    color: #2E7D32;
    box-sizing: border-box;
  }

  .toggle-eye {
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 1.2rem;
    color: #66BB6A;
    user-select: none;
  }

  input::placeholder {
    color: #A5D6A7;
  }

  input:focus {
    outline: none;
    border-color: #66BB6A;
    box-shadow: 0 0 6px #81C784;
    background: #E8F5E9;
  }

  button {
    width: 100%;
    padding: 1rem;
    font-size: 1.3rem;
    font-weight: bold;
    border: none;
    border-radius: 22px;
    color: white;
    background: linear-gradient(90deg, #43A047, #66BB6A);
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s;
    box-shadow: 0 8px 20px rgba(67,160,71,0.6);
  }

  button:hover {
    background: linear-gradient(90deg, #66BB6A, #43A047);
    transform: translateY(-2px);
  }

  .toggle-link {
    display: block;
    margin-top: 1.5rem;
    font-size: 1.05rem;
    color: #388E3C;
    cursor: pointer;
    transition: color 0.3s ease;
    text-decoration: none !important;
  }

  .toggle-link:hover,
  .toggle-link:focus {
    color: #1B5E20;
    text-shadow: 0 0 10px #66BB6A;
    transform: scale(1.05);
    outline: none;
  }

  @media (max-width: 400px) {
    .auth-container {
      width: 90vw;
      padding: 2rem 1.5rem;
    }
    h2 { font-size: 2rem; }
    input, button { font-size: 1rem; }
  }

  .back-button {
    position: fixed;
    top: 1rem;
    left: 1rem;
    padding: 0.5rem 1rem;
    background: #388E3C;
    color: white;
    font-weight: bold;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 4px 10px rgba(56, 142, 60, 0.7);
    transition: background 0.3s ease;
    z-index: 1000;
    font-family: 'Baloo 2', cursive;
    display: inline-flex;
    align-items: center;
  }

  .back-button:hover {
    background: #4CAF50;
  }
  </style>
</head>
<body>

<a href="http://educ.site/Collab_Game/landing%20page.php" class="back-button" aria-label="Back to Home">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle; margin-right: 8px;">
    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
  </svg>
  Back to Home
</a>

<!-- Floating math symbols background -->
<div class="floating-numbers" aria-hidden="true">
  <span style="left: 5%; animation-delay: 0s;">π</span>
  <span style="left: 15%; animation-delay: 2s;">∑</span>
  <span style="left: 25%; animation-delay: 4s;">√</span>
  <span style="left: 35%; animation-delay: 1s;">∞</span>
  <span style="left: 45%; animation-delay: 3s;">÷</span>
  <span style="left: 55%; animation-delay: 5s;">x²</span>
  <span style="left: 65%; animation-delay: 0.5s;">±</span>
  <span style="left: 75%; animation-delay: 2.5s;">3</span>
  <span style="left: 85%; animation-delay: 1.8s;">7</span>
  <span style="left: 95%; animation-delay: 3.5s;">9</span>
</div>

<main class="auth-container" role="main" aria-label="Login Form">
  <div class="main-title">Crack a Number</div>
  <h2 id="form-title">Login</h2>

  <form method="POST" action="" style="width: 100%;">
    <div class="input-wrapper">
      <input
        type="text"
        name="name"
        placeholder="Enter your Name"
        required
        minlength="3"
        maxlength="50"
        autocomplete="username"
      />
    </div>

    <div class="input-wrapper password-wrapper">
      <input
        type="password"
        name="password"
        id="password"
        placeholder="Password"
        required
        minlength="6"
        autocomplete="current-password"
      />
      <span
        class="toggle-eye"
        role="button"
        tabindex="0"
        aria-label="Toggle password visibility"
        onclick="togglePassword()"
        onkeydown="if(event.key==='Enter') togglePassword()"
        >👁️</span
      >
    </div>

    <div class="button-wrapper">
      <button type="submit">Login</button>
    </div>
  </form>

  <a class="toggle-link" href="register.php">Don't have an account? Register</a>
</main>

<script>
  function togglePassword() {
    const pwd = document.getElementById('password');
    if (pwd.type === 'password') {
      pwd.type = 'text';
    } else {
      pwd.type = 'password';
    }
  }
</script>

<?php
if (isset($_SESSION['error'])) {
    $msg = $_SESSION['error'];
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: " . json_encode($msg) . ",
            confirmButtonColor: '#4CAF50'
        });
    </script>";
    unset($_SESSION['error']);
}
?>
<br><br>
</body>
</html>
