<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crack a Number - Forgot Password</title>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2&display=swap" rel="stylesheet" />
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

.math-symbol {
  position: absolute;
  font-size: 2.5rem;
  color: #39ff14;
  text-shadow: 0 0 8px #39ff14, 0 0 15px #39ff14, 0 0 20px #00ff00;
  animation: floatMath 10s linear infinite;
  user-select: none;
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
      .floating-numbers span {
  color: rgba(229, 241, 229, 0.95); /* from 0.15 to 0.4 */
}

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
    }

        .auth-container::before {
      content: "∑ √ π ∞ x² ÷ ±";
      position: absolute;
      top: -25px;
      left: 50%;
      transform: translateX(-50%);
      background:rgb(90, 153, 124);
      color: white;
      font-size: 1rem;
      padding: 0.4rem 1.5rem;
      border-radius: 25px;
      letter-spacing: 2px;
      user-select: none;
    }
 .main-title {
      font-size: 2.2rem;
      color:rgb(73, 80, 74);
      margin-bottom: -1rem;
      font-weight: bold;
    }

    h2 {
      font-size: 2rem;
       color:rgb(73, 80, 74);
      margin-bottom: 1.5rem;
      border-bottom: 2px solid #A5D6A7;
      padding-bottom: 0.5rem;
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
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
        font-weight: bold;
      border: 2px solid #C8E6C9;
      border-radius: 18px;
      background: #F1F8E9;
      color:rgb(202, 211, 211);
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
.auth-container {
  background: rgba(255, 255, 255, 0.08); /* glass effect */
  border-radius: 25px;
  box-shadow: 0 12px 30px rgb(55, 187, 125);
  padding: 3rem 1.5rem;
  width: 360px;
  text-align: center;
  position: relative;
  border: 2px solid rgb(90, 153, 124);
  box-sizing: border-box;
  z-index: 1;
  height: 570px;
  backdrop-filter: blur(10px);
  animation: pulseGlow 6s ease-in-out infinite;
}

@keyframes pulseGlow {
  0%, 100% {
    box-shadow: 0 12px 30px rgba(76, 175, 80, 0.3), 0 0 20pxrgb(82, 211, 132);
  }
  50% {
    box-shadow: 0 12px 30px rgba(76, 175, 80, 0.6), 0 0 30pxrgb(33, 199, 102);
  }
}



.input-wrapper input,
button {
  box-shadow: 0 0 10px rgba(57, 255, 20, 0.3);
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(5px);
}

.input-wrapper input:focus {
  box-shadow: 0 0 15px #81C784, 0 0 5px #39ff14;
}

button {
  background: linear-gradient(90deg, #43A047, #66BB6A);
  transition: background 0.3s ease, transform 0.2s, box-shadow 0.2s;
}

button:hover {
  background: linear-gradient(90deg, #66BB6A, #43A047);
  transform: translateY(-2px);
  box-shadow: 0 0 15px #39ff14, 0 0 25px #66FF66;
}
.toggle-link {
  display: block;
  margin-top: 1.5rem;
  font-size: clamp(0.95rem, 2.5vw, 1.05rem); /* Responsive font */
  color: rgb(27, 30, 37);
  cursor: pointer;
  text-decoration: none !important;
  font-weight: 600;
  position: relative;
  transition: all 0.3s ease;
  letter-spacing: 0.5px;
}

.toggle-link::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 2px;
  background-color: #66BB6A;
  transition: width 0.3s ease;
  border-radius: 1px;
}

 .toggle-link {
      display: block;
      margin-top: 1.5rem;
      font-size: 1.35rem;
      color:rgb(235, 243, 236);
      cursor: pointer;
      /* transition: color 0.3s ease; */
      text-decoration: none !important;
    }

    .toggle-link:hover,
    .toggle-link:focus {
      /* color: #1B5E20; */
      text-shadow: 0 0 10pxrgb(206, 214, 207);
      transform: scale(1.05);
      outline: none;
    }

  </style>
</head>
<body>
<a href="index.php" class="back-button">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle; margin-right: 8px;">
    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
  </svg>
  Back to login
</a>
  <!-- ✅ ONE background animation container only -->
  <div class="floating-numbers" aria-hidden="true">
    <span style="left: 5%; animation-delay: 0s;">1</span>
    <span style="left: 15%; animation-delay: 2s;">π</span>
    <span style="left: 25%; animation-delay: 4s;">3</span>
    <span style="left: 35%; animation-delay: 1s;">√</span>
    <span style="left: 45%; animation-delay: 3s;">8</span>
    <span style="left: 55%; animation-delay: 5s;">∑</span>
    <span style="left: 65%; animation-delay: 0.5s;">7</span>
    <span style="left: 75%; animation-delay: 2.5s;">∞</span>
    <span style="left: 85%; animation-delay: 1.8s;">x²</span>
    <span style="left: 95%; animation-delay: 3.5s;">÷</span>
  </div>
  

  <main class="auth-container" role="main" aria-label="Forgot Password Form">
       <audio id="myAudio" src="sound_effect/The Game Show Theme Music.mp3" preload="auto" loop></audio>


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

   <div id="countdown" style="font-size: 1.2rem; color:white; margin-bottom: 2rem;">
  Redirecting in 3:00
</div>
    <div class="main-title">Crack a Number</div>
    <h2>Forgot Password</h2>

    <form method="POST" action="process_forgot_password.php">
      <div class="input-wrapper">
        <input type="text" name="name" placeholder="Enter your Name" required minlength="3" maxlength="50" />
      </div>

      <div class="input-wrapper password-wrapper">
  <input type="password" name="password" id="password" placeholder="Enter your Password" required minlength="6" />
  <span class="toggle-eye" onclick="togglePassword()" id="toggleIcon">👁️</span>
</div>


      <div class="button-wrapper">
        <button type="submit">Reset Password</button>
      </div>
    </form>
  </main>


  <script>
    function togglePassword() {
      const field = document.getElementById("password");
      const type = field.getAttribute("type") === "password" ? "text" : "password";
      field.setAttribute("type", type);
    }
  </script>

  <script>
  const countdownElement = document.getElementById('countdown');
  let timeLeft = 180; // 180 seconds = 3 minutes

  function formatTime(seconds) {
    const min = Math.floor(seconds / 60);
    const sec = seconds % 60;
    return `${min}:${sec.toString().padStart(2, '0')}`;
  }

  const timer = setInterval(() => {
    timeLeft--;
    countdownElement.textContent = `Redirecting in ${formatTime(timeLeft)}`;

    if (timeLeft <= 0) {
      clearInterval(timer);
      window.location.href = 'index.php';
    }
  }, 1000);
</script>

<script>
  function togglePassword() {
    const passwordField = document.getElementById("password");
    const toggleIcon = document.getElementById("toggleIcon");

    if (passwordField.type === "password") {
      passwordField.type = "text";
      toggleIcon.textContent = "🙈"; // o pwede mong palitan ng 👁️‍🗨️ or 🔓
    } else {
      passwordField.type = "password";
      toggleIcon.textContent = "👁️";
    }
  }
</script>
</body>
</html>
