<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Crack a Number - Register</title>
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
    /* Auth Container */
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
      height: 680px;
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
    /* Form Styles */
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
      margin-bottom: 1rem;
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
      color:rgb(233, 129, 69);
      box-sizing: border-box;
    }

   
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 1rem 1.2rem;
      font-size: 1.2rem;
      border: 2px solid #C8E6C9;
      border-radius: 18px;
      background: #F1F8E9;
      margin-bottom: 1.5rem;
      box-shadow: inset 1px 1px 4px rgba(0,0,0,0.05);
      color:rgb(198, 216, 199);
      box-sizing: border-box;
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

    .toggle-eye {
      position: absolute;
      top: 41%;
      right: 1rem;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 1.2rem;
      color: #66BB6A;
      user-select: none;
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
      /* color: #388E3C; */
      cursor: pointer;
      /* transition: color 0.3s ease; */
      text-decoration: none !important;
    }

    .toggle-link:hover,
    .toggle-link:focus {
      /* color: #1B5E20; */
      text-shadow: 0 0 10px #66BB6A;
      transform: scale(1.05);
      outline: none;
    }


    /* Math Background */
    .math-bg {
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      overflow: hidden;
      z-index: 0;
    }

    .math-symbol {
      position: absolute;
      font-size: 2.5rem;
      color: rgba(76, 175, 80, 0.1);
      animation: floatMath 10s linear infinite;
      user-select: none;
    }

    @keyframes floatMath {
      0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
      50% { opacity: 0.3; }
      100% { transform: translateY(-120vh) rotate(360deg); opacity: 0; }
    }

    .math-symbol:nth-child(1) { left: 10%; animation-delay: 0s; }
    .math-symbol:nth-child(2) { left: 25%; animation-delay: 2s; }
    .math-symbol:nth-child(3) { left: 40%; animation-delay: 4s; }
    .math-symbol:nth-child(4) { left: 55%; animation-delay: 1s; }
    .math-symbol:nth-child(5) { left: 70%; animation-delay: 3s; }
    .math-symbol:nth-child(6) { left: 85%; animation-delay: 5s; }

    /* Modal Overlay */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .modal-overlay.active {
      display: flex;
    }

.modal {
  background: linear-gradient(145deg, #e6f4ea, #ffffff);
  border: 1px solid rgba(76, 175, 80, 0.2);
  border-radius: 20px;
  padding: 2.5rem;
  width: 80%;
  max-width: 480px;
  box-shadow:
    0 10px 30px rgba(76, 175, 80, 0.35),
    0 0 15px rgba(56, 142, 60, 0.2),
    inset 0 0 12px rgba(255, 255, 255, 0.4);
  position: relative;
  text-align: center;
  animation: fadeIn 0.4s ease-in-out, glowBorder 3s ease-in-out infinite alternate;
  backdrop-filter: blur(6px);
  color: #1b5e20;
  font-family: 'Baloo 2', cursive;
}

/* Animation for subtle border glow effect */
@keyframes glowBorder {
  0% {
    box-shadow:
      0 10px 30px rgba(76, 175, 80, 0.35),
      0 0 15px rgba(56, 142, 60, 0.2),
      inset 0 0 8px rgba(255, 255, 255, 0.2);
  }
  100% {
    box-shadow:
      0 15px 40px rgba(76, 175, 80, 0.45),
      0 0 20px rgba(56, 142, 60, 0.3),
      inset 0 0 15px rgba(255, 255, 255, 0.4);
  }
}

/* Optional fade-in */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}


    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .modal .main-title {
      font-size: 2rem;
      font-weight: bold;
      color: #2e7d32;
      margin-bottom: 0.5rem;
    }

    .modal .subtitle {
      font-size: 1.2rem;
      color: #4caf50;
      margin-bottom: 2rem;
    }

    .modal-close {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: transparent;
      border: none;
      font-size: 1.5rem;
      font-weight: bold;
      color: #999;
      cursor: pointer;
    }

    .modal-close:hover {
      color: #000;
    }

    /* Grade Options */
    .grade-options {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 1rem;
    }

    .grade-button {
      padding: 1rem 2rem;
      font-size: 1.2rem;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      color: white;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      position: relative;
      min-width: 120px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .grade-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .grade4 { background: linear-gradient(to right, #f57c00, #ffb300); }
    .grade5 { background: linear-gradient(to right, #43a047, #66bb6a); }
    .grade6 { background: linear-gradient(to right, #1e88e5, #42a5f5); }

    .tooltip {
      position: absolute;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      background: #333;
      color: #fff;
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 0.8rem;
      white-space: nowrap;
      opacity: 0;
      pointer-events: none;
      transition: all 0.2s ease;
    }

    .grade-button:hover .tooltip {
      opacity: 1;
      transform: translateX(-50%) translateY(-5px);
    }

    @media (max-width: 480px) {
      .auth-container { width: 90vw; padding: 2rem 1.5rem; }
      h2 { font-size: 2rem; }
      input, button { font-size: 1rem; }
      .grade-options { flex-direction: column; gap: 1rem; }
    }



  .grade-options {
  display: flex;
  flex-direction: row;
  justify-content: center;
  gap: 2rem;
  flex-wrap: nowrap;
}

.grade-button {
  min-width: 110px;
  padding: 1rem 2rem;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: bold;
  color: #fff;
  cursor: pointer;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 1.5);
  transition: transform 0.2s, box-shadow 0.3s;
}

.grade-button:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}
.modal-close {
  position: absolute;
  top: 13rem;
  right: 1rem;
  background: transparent;
  border: none;
  font-size: 2rem;
  font-weight: bold;
  color: orange;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  transition: color 0.2s ease;
  min-width: 100px;
  width: 95%;
  padding: 1rem 2rem;
}

.modal-close:hover,
.modal-close:focus {
  color: #e74c3c; /* red on hover/focus */
  outline: none;
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

  <main class="auth-container" role="main" aria-label="Registration Form">
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

    <div class="main-title">Crack a Number</div>
    <h2 id="form-title">Register</h2>

    <form method="POST" action="register_handler.php" novalidate>
      <div class="input-wrapper">
        <input type="text" name="name" placeholder="Enter your Name" required minlength="3" maxlength="50" autocomplete="name" />
      </div>
   <div class="input-wrapper password-wrapper">
  <input type="password" name="password" id="password" placeholder="Enter your Password" required minlength="6" />
  <span class="toggle-eye" onclick="togglePassword()" id="toggleIcon">👁️</span>
</div>

      <div class="button-wrapper">
        <button type="button" id="openGradeModal">Choose Grade-Level</button>
      </div>
      <div class="button-wrapper">
        <button type="submit">Register</button>
      </div>
      <input type="hidden" name="grade" id="grade_level" />
    </form>

    <a class="toggle-link" href="index.php">Already have an account? Login </a>
  
      
  </main>

  <!-- Grade Selection Modal -->
<!-- Grade Selection Modal -->
<div class="modal-overlay" id="gradeModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalDesc">
  <div class="modal">
    <!-- <button class="modal-close" id="closeModal" aria-label="Close modal">&times;</button> -->

    <div class="main-title" id="modalTitle">Select Your Grade</div>
    <div class="subtitle" id="modalDesc"></div>

    <form id="gradeForm" class="grade-options">
      <button class="grade-button grade4" type="button" data-grade="4">Grade 4 <span class="tooltip">Are you Grade 4?</span></button>
      <button class="grade-button grade5" type="button" data-grade="5">Grade 5 <span class="tooltip">Are you Grade 5?</span></button>
      <button class="grade-button grade6" type="button" data-grade="6">Grade 6 <span class="tooltip">Are you Grade 6?</span></button>
    </form>
    <br><br><br>
    <button class="modal-close" id="closeModal" aria-label="Close modal">Cancel</button>
    <br><br>
  </div>
</div>


  </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  <?php if (isset($_SESSION['success'])): ?>
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: <?= json_encode($_SESSION['success']) ?>,
      timer: 3000,
      timerProgressBar: true,
      showConfirmButton: false
    });
  <?php unset($_SESSION['success']); endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: <?= json_encode($_SESSION['error']) ?>,
      timer: 4000,
      timerProgressBar: true,
      showConfirmButton: false
    });
  <?php unset($_SESSION['error']); endif; ?>
});
</script>


  <script>
    function togglePassword() {
      const field = document.getElementById("password");
      const type = field.getAttribute("type") === "password" ? "text" : "password";
      field.setAttribute("type", type);
    }

    const gradeModal = document.getElementById('gradeModal');
    const openGradeModalBtn = document.getElementById('openGradeModal');
    const closeModalBtn = document.getElementById('closeModal');
    const gradeLevelInput = document.getElementById('grade_level');

    openGradeModalBtn.addEventListener('click', () => {
      gradeModal.classList.add('active');
      gradeModal.querySelector('button[data-grade]').focus();
    });

    closeModalBtn.addEventListener('click', () => {
      gradeModal.classList.remove('active');
      openGradeModalBtn.focus();
    });

    gradeModal.addEventListener('click', (e) => {
      if (e.target === gradeModal) {
        gradeModal.classList.remove('active');
        openGradeModalBtn.focus();
      }
    });

    document.getElementById('gradeForm').addEventListener('click', (e) => {
      if (e.target.closest('button[data-grade]')) {
        const grade = e.target.closest('button[data-grade]').getAttribute('data-grade');
        gradeLevelInput.value = grade;
        openGradeModalBtn.textContent = `Grade ${grade} Selected`;
        gradeModal.classList.remove('active');
        openGradeModalBtn.focus();
      }
    });
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
