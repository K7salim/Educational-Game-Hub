<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    $_SESSION['user_id'] = 1;

    $_SESSION['user_name'] = 'Juan';

    $_SESSION['user_grade'] = 5;

}



$name = $_SESSION['user_name'];

$grade = (int)$_SESSION['user_grade'];

$level = isset($_GET['level']) ? htmlspecialchars($_GET['level']) : 'beginner';

$levelnum = isset($_GET['levelnum']) ? intval($_GET['levelnum']) : 1;



$levelColors = [

    'beginner' => '#007bff',

    'advance' => '#fd7e14',

    'expert' => '#dc3545'

];

$levelColor = $levelColors[$level] ?? '#007bff';



// Generate question for initial render

function generateQuestion() {

    $a = rand(1, 10);

    $b = rand(1, 10);

    $ops = ['+', '-', '×', '÷'];

    $op = $ops[array_rand($ops)];

    return "$a $op $b";

}



$question = generateQuestion();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8" />

<meta name="viewport" content="width=device-width, initial-scale=1" />

<title>Crack a Number - Game</title>

<link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>


<style>

  /* --- General Body + Font --- */

body {

  margin: 0;

  padding: 0;

  font-family: 'Orbitron', sans-serif;

  background: radial-gradient(circle at center, #1a1a1a, #0f2027);

  color: #00ffcc;

  overflow-x: hidden;

  user-select: none;

}



header {

  width: 100%;

  background: linear-gradient(to right, #0f2027, #203a43, #2c5364);

  color: #00ffcc;

  padding: 16px 20px;

  display: flex;

  justify-content: space-between;

  align-items: center;

  flex-wrap: wrap;

  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);

  position: sticky;

  top: 0;

  z-index: 100;

  box-sizing: border-box;

}



.header-title {

  font-size: 1.8rem;

  font-weight: bold;

  text-shadow: 1px 1px 5px black;

  letter-spacing: 1px;

}



.user-info span {

  margin-left: 16px;

  font-weight: 600;

}



.level-badge {

  margin-left: 16px;

  padding: 4px 12px;

  border-radius: 20px;

  font-weight: 700;

  color: #001f1f;

  background-color: #00ffcc;

}



/* Container */

.container {

  max-width: 900px;

  margin: 40px auto 80px;

  padding: 30px;

  background: rgba(0, 0, 0, 0.85);

  border-radius: 16px;

  box-shadow: 0 0 20px #00ffccaa;

  position: relative;

  z-index: 10;

}



/* Timer */

.timer {

  font-size: 1rem;

  text-align: right;

  margin-bottom: 20px;

  color: #ffcc00;

  font-weight: bold;

  user-select: none;

}



/* Question */

.question {

  font-size: 1.9rem;

  margin: 20px 0;

  display: flex;

  align-items: center;

  justify-content: center;

  gap: 12px;

  flex-wrap: wrap;

}



.question label {

  font-weight: 700;

  white-space: nowrap;

}



/* Input */

.question input[type="text"],input[type="number"] {

  padding: 12px 16px;

  font-size: 1.2rem;

  width: 150px;

  border: 2px solid #00ffcc;

  border-radius: 10px;

  background: #002b2b;

  color: #00ffcc;

  text-align: center;

  transition: border-color 0.3s ease, box-shadow 0.3s ease;

}



.question input[type="text"]:focus {

  outline: none;

  border-color: #00ddb3;

  box-shadow: 0 0 8px #00ddb3;

}



/* Buttons */

.btn {

  padding: 14px 32px;

  background: linear-gradient(135deg, #00ffcc, #00ddb3);

  color: #001f1f;

  font-weight: 700;

  font-size: 1.3rem;

  border: none;

  border-radius: 14px;

  cursor: pointer;

  transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;

  user-select: none;

  box-shadow: 0 4px 8px rgba(0, 255, 204, 0.5);

  display: inline-block;

}



.btn:hover {

  background: linear-gradient(135deg, #00ddb3, #00ffcc);

  transform: scale(1.07);

  box-shadow: 0 6px 14px rgba(0, 221, 179, 0.8);

}



.exit-btn {

  margin-top: 20px;

  background-color: #ff4444;

  color: #fff;

  border: none;

  border-radius: 12px;

  font-weight: bold;

  font-size: 1.1rem;

  cursor: pointer;

  transition: background 0.3s, transform 0.2s, width 0.3s;

  padding: 10px 20px;

  width: 25%;

  user-select: none;

  display: inline-block;

}



.exit-btn:hover {

  background-color: #ff0000;

  transform: scale(1.05);

  width: 30%;

}



/* Responsive for Mobile */

@media (max-width: 480px) {

  header {

    flex-direction: column;

    align-items: center;

    padding: 12px 16px;

  }



  .header-title {

    font-size: 1.4rem;

    width: 100%;

    text-align: center;

  }



  .user-info {

    width: 100%;

    display: flex;

    flex-wrap: wrap;

    justify-content: center;

    gap: 10px;

    margin-top: 8px;

  }



  .user-info span {
      font-size: 1.4rem;

    margin-left: 0;

    text-align: center;

    flex: 1 1 45%;

  }



  .level-badge {

    flex: 1 1 100%;

    margin-left: 0;

    margin-top: 6px;

  }



  .container {

    width: 95% !important;

    margin: 20px auto !important;

    padding: 20px !important;

    max-width: none !important;

    box-sizing: border-box;

  }



  .question {

    flex-direction: column;

    gap: 16px;

  }



  .question input[type="text"] {

    width: 100%;

    max-width: 250px;

  }



  .btn, .exit-btn {

    width: 100% !important;

    max-width: 320px;

  }

}



</style>

</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>







<header>

  <div class="header-title">Crack a Number</div>

  <div class="user-info">

    <span>User: <strong style="color:#0d6efd"><?= htmlspecialchars($name) ?></strong></span>

    <span>Grade: <strong style="color:#198754"><?= htmlspecialchars($grade) ?></strong></span>

    <span class="level-badge" style="background-color: <?= $levelColor ?>"><?= htmlspecialchars($level) ?></span>

  </div>

</header>



<main class="container">





     <audio id="myAudio" src="sound_effect/Tense music from game show - Sound Effect (HD).mp3" preload="auto" loop></audio>


<script>
  let isPaused = false;
let pausedTimeLeft = 0;

function togglePause() {
  const audio = document.getElementById('myAudio');
  const pauseBtn = document.getElementById('pauseBtn');

  if (!isPaused) {
    // Pause the game
    clearInterval(countdown);
    pausedTimeLeft = timeLeft;
    audio.pause();
    document.getElementById('answer').disabled = true;
    pauseBtn.textContent = '▶️ Resume';
    isPaused = true;
  } else {
    // Resume the game
    timeLeft = pausedTimeLeft;
    timerEl.textContent = `Time Left: ${timeLeft}s`;
    countdown = setInterval(() => {
      timeLeft--;
      timerEl.textContent = `Time Left: ${timeLeft}s`;
      if (timeLeft <= 0) {
        clearInterval(countdown);
        const q = document.getElementById('question').value;
        const result = evaluateAnswer(q, "");
        totalTimesUp++;
        sessionStorage.setItem('totalTimesUp', totalTimesUp);
        Swal.fire({
          icon: 'error',
          title: 'Time is up!',
          html: `Correct answer: <strong>${result.correctAnswer}</strong>`,
          timer: 3000,
          showConfirmButton: false
        }).then(() => {
          proceedToNextLevel();
        });
      }
    }, 1000);

    audio.play();
    document.getElementById('answer').disabled = false;
    pauseBtn.textContent = '⏸️ Pause';
    isPaused = false;
  }
}

</script>


<script>
  window.addEventListener('DOMContentLoaded', () => {
    const audio = document.getElementById('myAudio');

    const levelnum = <?= $levelnum ?>;
    if (levelnum === 1) {
      let level = '<?= $level ?>';
      let instructionText = '';

      switch (level) {
        case 'beginner':
          instructionText = 'Solve basic arithmetic problems. You have more time and simpler questions.';
          break;
        case 'advance':
          instructionText = 'Expect more challenging math problems including fractions and decimals.';
          break;
        case 'expert':
          instructionText = 'This level includes square roots, fractions, and time pressure. Stay sharp!';
          break;
      }

      Swal.fire({
        title: `Welcome to ${level.charAt(0).toUpperCase() + level.slice(1)} Level`,
        html: `
          <p style="font-size:1rem; text-align:left;">
            ${instructionText}<br><br>
            🔢 Solve the question within the time limit.<br>
            ✅ Correct answers increase your score.<br>
            ❌ Wrong or no answers will reduce your score.<br><br>
            Good luck!
          </p>
        `,
        icon: 'info',
        confirmButtonText: 'Start Game',
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then((result) => {
        if (result.isConfirmed) {
          // Start audio playback after confirmation
          setTimeout(() => {
            audio.play().catch(error => {
              console.warn('Autoplay blocked. Waiting for user interaction...');
              function playOnInteraction() {
                audio.play();
                window.removeEventListener('click', playOnInteraction);
                window.removeEventListener('keydown', playOnInteraction);
              }
              window.addEventListener('click', playOnInteraction);
              window.addEventListener('keydown', playOnInteraction);
            });
          }, 300);

          // Start the game after audio setup
          renderQuestion();
        }
      });
    } else {
      // For levels other than 1, start the question immediately
      renderQuestion();
    }
  });
  
</script>




<p style="text-align:center; font-weight: 600; font-size: 1.1rem;">Level <?= $levelnum ?></p>

<div class="timer" id="timer">Time Left: --</div>

<div id="question-container">
  <div class="question">
    <label for="answer"> <?= htmlspecialchars($question) ?> = </label>

    <!-- Input field with filter -->
<style>
  input[type="number"] {
    appearance: textfield;
    -moz-appearance: textfield;
    -webkit-appearance: none;
  }

  input[type="number"]::-webkit-inner-spin-button,
  input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
</style>

<input
  type="number"
  id="answer"
  autocomplete="off"
  inputmode="numeric"
  oninput="filterSymbols(this)"
/>





    <input type="hidden" id="question" value="<?= htmlspecialchars($question) ?>" />
    <button class="btn" onclick="submitAnswer()">Submit</button>
  </div>
</div>

<div style="text-align:center; margin-top: 30px;">
  <button class="exit-btn" onclick="exitGame()">Exit</button>
</div>

</main>

<div style="text-align:center; margin-top: 20px;">
  <button class="btn" id="pauseBtn" onclick="togglePause()">⏸️ Pause</button>
</div>

<script>



const grade = <?= $grade ?>;

let level = '<?= $level ?>';

let levelnum = <?= $levelnum ?>;



const timerEl = document.getElementById('timer');

let timeLeft;

let countdown;

let questionStartTime;



// Load running totals from sessionStorage

let totalCorrect = parseInt(sessionStorage.getItem('totalCorrect')) || 0;

let totalWrong = parseInt(sessionStorage.getItem('totalWrong')) || 0;

let totalTimesUp = parseInt(sessionStorage.getItem('totalTimesUp')) || 0;



function getTimeLimit(grade, level) {

  if (grade === 4 || grade === 5) {

    if (level === 'beginner') return 30;

    if (level === 'advance') return 25;

    if (level === 'expert') return 20;

  } else if (grade === 6) {

    if (level === 'beginner') return 20;

    if (level === 'advance') return 15;

    if (level === 'expert') return 10;

  }

  return 30;

}



function getMaxLevel(grade, level) {

  return (level === 'beginner') ? 30 : (level === 'advance') ? 60 : 120;

}



function getRandomFraction() {

  const numerator = Math.floor(Math.random() * 9) + 1;

  const denominator = Math.floor(Math.random() * 9) + 1;

  return `${numerator}/${denominator}`;

}



function getRandomDecimal() {

  return (Math.random() * 10).toFixed(1);

}



function getRandomPerfectSquare() {

  const squares = [1,4,9,16,25,36,49,64,81,100];

  return squares[Math.floor(Math.random() * squares.length)];

}



function generateQuestion() {

  const ops = ['+', '-', '×', '÷'];

  const op = ops[Math.floor(Math.random() * ops.length)];

  let a, b;



  if (grade === 4) {

    const useFraction = Math.random() < 0.5;

    a = useFraction ? getRandomFraction() : Math.floor(Math.random() * 10) + 1;

    b = useFraction ? getRandomFraction() : Math.floor(Math.random() * 10) + 1;

  } else if (grade === 5) {

    const useFraction = Math.random() < 0.4;

    const useDecimal = !useFraction && Math.random() < 0.5;

    if (useFraction) {

      a = getRandomFraction(); b = getRandomFraction();

    } else if (useDecimal) {

      a = getRandomDecimal(); b = getRandomDecimal();

    } else {

      a = Math.floor(Math.random() * 10) + 1;

      b = Math.floor(Math.random() * 10) + 1;

    }

  } else if (grade === 6) {

    const useSquareRoot = Math.random() < 0.5;

    const useFraction = !useSquareRoot && Math.random() < 0.5;

    if (useSquareRoot) {

      a = `√${getRandomPerfectSquare()}`; b = `√${getRandomPerfectSquare()}`;

    } else if (useFraction) {

      a = getRandomFraction(); b = getRandomFraction();

    } else {

      a = Math.floor(Math.random() * 10) + 1;

      b = Math.floor(Math.random() * 10) + 1;

    }

  }

  return `${a} ${op} ${b}`;

}



function evaluateAnswer(question, userAnswer) {

  function fractionToDecimal(frac) {

    const parts = frac.split('/');

    return parts.length === 2 ? parseFloat(parts[0]) / parseFloat(parts[1]) : NaN;

  }



  function sqrtToDecimal(sqrtStr) {

    return Math.sqrt(parseFloat(sqrtStr.replace('√', '')));

  }



  let expr = question

    .replace(/\b\d+\/\d+\b/g, m => fractionToDecimal(m))

    .replace(/√\d+/g, m => sqrtToDecimal(m))

    .replace(/×/g, '*').replace(/÷/g, '/');



  try {

    let correctAnswer = Function('"use strict";return (' + expr + ')')();

    correctAnswer = Math.round(correctAnswer * 100) / 100;

    userAnswer = parseFloat(userAnswer);

    return { correct: userAnswer === correctAnswer, correctAnswer };

  } catch {

    return { correct: false, correctAnswer: null };

  }

}



function renderQuestion() {

  const q = generateQuestion();

  document.getElementById('question-container').innerHTML = `

    <div class="question">

      <label for="answer"> ${q} = </label>

      <input type="number" id="answer" autocomplete="off" />

      <input type="hidden" id="question" value="${q}" />

      <button class="btn" onclick="submitAnswer()">Submit</button>

    </div>

  `;

  startTimer();

  document.getElementById('answer').focus();

}



async function submitAnswer() {

  clearInterval(countdown);

  const q = document.getElementById('question').value;

  const userAns = document.getElementById('answer').value.trim();



  if (!userAns) {

    Swal.fire('Oops', 'Please enter your answer before submitting.', 'warning');

    startTimer();

    return;

  }



  const result = evaluateAnswer(q, userAns);



  if (result.correct) {

    totalCorrect++;

  } else {

    totalWrong++;

  }



  // Save progress

  sessionStorage.setItem('totalCorrect', totalCorrect);

  sessionStorage.setItem('totalWrong', totalWrong);

  sessionStorage.setItem('totalTimesUp', totalTimesUp);



  Swal.fire({

    icon: result.correct ? 'success' : 'error',

    title: result.correct ? 'Correct!' : 'Wrong!',

    html: result.correct ? '' : `Correct answer: <strong>${result.correctAnswer}</strong>`,

    timer: 2000,

    showConfirmButton: false

  }).then(() => {

    proceedToNextLevel();

  });

}



function startTimer() {

  questionStartTime = Date.now();

  timeLeft = getTimeLimit(grade, level);

  timerEl.textContent = `Time Left: ${timeLeft}s`;

  countdown = setInterval(() => {

    timeLeft--;

    timerEl.textContent = `Time Left: ${timeLeft}s`;

    if (timeLeft <= 0) {

      clearInterval(countdown);

      const q = document.getElementById('question').value;

      const result = evaluateAnswer(q, "");



      totalTimesUp++;

      sessionStorage.setItem('totalTimesUp', totalTimesUp);



      Swal.fire({

        icon: 'error',

        title: 'Time is up!',

        html: `Correct answer: <strong>${result.correctAnswer}</strong>`,

        timer: 3000,

        showConfirmButton: false

      }).then(() => {

        proceedToNextLevel();

      });

    }

  }, 1000);

}



async function proceedToNextLevel() {
  const max = getMaxLevel(grade, level);
  
  if (levelnum < max) {
    window.location.href = `game.php?level=${encodeURIComponent(level)}&levelnum=${levelnum + 1}`;
  } else {
    try {
      const res = await fetch('save_result.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          grade: grade,
          level: level,
          correct_count: totalCorrect,
          wrong_count: totalWrong,
          times_up_count: totalTimesUp
        })
      });

      const data = await res.json();

      // Clear session storage
      sessionStorage.removeItem('totalCorrect');
      sessionStorage.removeItem('totalWrong');
      sessionStorage.removeItem('totalTimesUp');

      let badgeImg = `badges/${data.badge || 'default'}.jpg`;

      // Calculate score percentage
      let totalAnswered = totalCorrect + totalWrong + totalTimesUp;
      let scorePercent = totalAnswered > 0 ? (totalCorrect / totalAnswered) * 100 : 0;

      // Show confetti if high score (>=80%)
      if(scorePercent >= 80) {
        confetti({
          particleCount: 150,
          spread: 70,
          origin: { y: 0.6 }
        });

        Swal.fire({
          icon: 'success',
          title: `🎉 Amazing! 🎉`,
          html: `
            You finished level <strong>${level}</strong> for Grade <strong>${grade}</strong> with a high score of <strong>${scorePercent.toFixed(1)}%</strong>!<br><br>
            <img src="${badgeImg}" alt="Badge" style="width:150px;">
          `,
          confirmButtonText: 'Back to Menu'
        }).then(() => {
          window.location.href = 'welcome.php';
        });
      }
      // Show thumbs down if low score (<40%)
      else if(scorePercent < 40) {
        Swal.fire({
          icon: 'error',
          title: '😞 Keep Trying!',
          html: `
            You finished level <strong>${level}</strong> for Grade <strong>${grade}</strong> with a score of <strong>${scorePercent.toFixed(1)}%</strong>.<br><br>
            Don't give up! Practice makes perfect.
          `,
          confirmButtonText: 'Back to Menu'
        }).then(() => {
          window.location.href = 'welcome.php';
        });
      }
      // Otherwise, normal message
      else {
        Swal.fire({
          icon: 'success',
          title: `Good job!`,
          html: `
            You finished level <strong>${level}</strong> for Grade <strong>${grade}</strong> with a score of <strong>${scorePercent.toFixed(1)}%</strong>.<br><br>
            <img src="${badgeImg}" alt="Badge" style="width:150px;">
          `,
          confirmButtonText: 'Back to Menu'
        }).then(() => {
          window.location.href = 'welcome.php';
        });
      }

    } catch (error) {
      console.error('Error saving final result:', error);
      Swal.fire('Error', 'Something went wrong.', 'error');
    }
  }
}



function exitGame() {

  Swal.fire({

    title: 'Exit game?',

    icon: 'warning',

    showCancelButton: true,

    confirmButtonText: 'Yes',

    cancelButtonText: 'No',

  }).then(result => {

    if (result.isConfirmed) {

      window.location.href = 'welcome.php';

    }

  });

}



// Start game

//window.onload = renderQuestion;

</script>



<script>
function filterSymbols(input) {
  input.value = input.value.replace(/[A-Za-z]/g, ''); // Allow numbers and symbols only
}

</script>





</body>

</html>







<!-- <script>

  const grade = <?//= $grade ?>;

let level = '<?//= $level ?>';

let levelnum = <?//= $levelnum ?>;



const timerEl = document.getElementById('timer');

let timeLeft;

let countdown;

let questionStartTime;



// Time limits per grade and level

function getTimeLimit(grade, level) {

  if (grade === 4 || grade === 5) {

    if (level === 'beginner') return 30;

    if (level === 'advance') return 25;

    if (level === 'expert') return 20;

  } else if (grade === 6) {

    if (level === 'beginner') return 20;

    if (level === 'advance') return 15;

    if (level === 'expert') return 10;

  }

  return 30;

}



// Max level per grade and level

function getMaxLevel(grade, level) {

  if (grade === 4 || grade === 5) {

    if (level === 'beginner') return 30;

    if (level === 'advance') return 60;

    if (level === 'expert') return 120;

  } else if (grade === 6) {

    if (level === 'beginner') return 30;

    if (level === 'advance') return 60;

    if (level === 'expert') return 120;

  }

  return 30;

}



function generateQuestion() {

  const a = Math.floor(Math.random() * 10) + 1;

  const b = Math.floor(Math.random() * 10) + 1;

  const ops = ['+', '-', '×', '÷'];

  const op = ops[Math.floor(Math.random() * ops.length)];

  return `${a} ${op} ${b}`;

}



function renderQuestion() {

  const q = generateQuestion();

  const container = document.getElementById('question-container');

  container.innerHTML = `

    <div class="question">

      <label for="answer"> ${q} = </label>

      <input type="text" id="answer" autocomplete="off" aria-label="Answer input" />

      <input type="hidden" id="question" value="${q}" />

      <button class="btn" onclick="submitAnswer()">Submit</button>

    </div>

  `;

  startTimer();

  document.getElementById('answer').focus();

}



function evaluateAnswer(question, userAnswer) {

  let expression = question.replace(/×/g, '*').replace(/÷/g, '/');

  try {

    let correctAnswer = Function('"use strict";return (' + expression + ')')();

    if (Number.isFinite(correctAnswer)) {

      correctAnswer = Math.round(correctAnswer * 100) / 100;

      userAnswer = parseFloat(userAnswer);

      return { correct: userAnswer === correctAnswer, correctAnswer };

    }

    return { correct: false, correctAnswer: null };

  } catch {

    return { correct: false, correctAnswer: null };

  }

}



async function submitAnswer() {

  clearInterval(countdown);

  const q = document.getElementById('question').value;

  const userAns = document.getElementById('answer').value.trim();



  if (!userAns) {

    Swal.fire('Oops', 'Please enter your answer before submitting.', 'warning');

    startTimer();

    return;

  }



  const timeTaken = Math.floor((Date.now() - questionStartTime) / 1000);

  const result = evaluateAnswer(q, userAns);



  try {

    await fetch('save_result.php', {

      method: 'POST',

      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },

      body: new URLSearchParams({

        grade: grade,

        level: level,

        levelnum: levelnum,

        correct: result.correct,

        time_taken: timeTaken

      })

    });

  } catch (error) {

    console.error('Error saving result:', error);

  }



  if (result.correct) {

    Swal.fire({

      icon: 'success',

      title: 'Correct!',

      showConfirmButton: false,

      timer: 1400

    }).then(() => {

      proceedToNextLevel();

    });

  } else {

    Swal.fire({

      icon: 'error',

      title: 'Wrong!',

      html: `The correct answer is <strong>${result.correctAnswer}</strong>`,

      timer: 3000,

      timerProgressBar: true,

      showConfirmButton: false,

    }).then(() => {

      proceedToNextLevel();

    });

  }

}



function startTimer() {

  questionStartTime = Date.now();

  timeLeft = getTimeLimit(grade, level);

  timerEl.textContent = `Time Left: ${timeLeft}s`;

  countdown = setInterval(async () => {

    timeLeft--;

    timerEl.textContent = `Time Left: ${timeLeft}s`;

    if (timeLeft <= 0) {

      clearInterval(countdown);

      const q = document.getElementById('question').value;

      const timeTaken = getTimeLimit(grade, level);

      const result = evaluateAnswer(q, "");



      try {

        await fetch('save_result.php', {

          method: 'POST',

          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },

          body: new URLSearchParams({

            grade: grade,

            level: level,

            levelnum: levelnum,

            correct: false,

            time_taken: timeTaken

          })

        });

      } catch (error) {

        console.error('Error saving result:', error);

      }



      Swal.fire({

        icon: 'error',

        title: 'Time is up!',

        html: `The correct answer is <strong>${result.correctAnswer}</strong>`,

        timer: 3000,

        timerProgressBar: true,

        showConfirmButton: false,

      }).then(() => {

        proceedToNextLevel();

      });

    }

  }, 1000);

}



async function proceedToNextLevel() {

  const max = getMaxLevel(grade, level);

  if (levelnum < max) {

    window.location.href = `game.php?level=${encodeURIComponent(level)}&levelnum=${levelnum + 1}`;

  } else {

    try {

      const res = await fetch('assign_badge.php', {

        method: 'POST',

        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },

        body: new URLSearchParams({

          grade: grade,

          level: level

        })

      });

      const data = await res.json();



      let badgeImgSrc = 'badges/slow_learner_beginner - default.jpg';

      if (data.badge === 'moderate_mover_advanced') badgeImgSrc = 'badges/moderate_mover_advanced - default.jpg';

      else if (data.badge === 'fast_learner_expert') badgeImgSrc = 'badges/fast_learner_expert - default.jpg';



      Swal.fire({

        icon: 'success',

        title: `Congrats! You finished level ${level} for grade ${grade}!`,

        html: `<img src="${badgeImgSrc}" alt="Badge" style="width:150px; margin-top:15px;">`,

        confirmButtonText: 'Back to Menu',

        allowOutsideClick: false

      }).then(() => {

        window.location.href = 'welcome.php';

      });

    } catch (error) {

      console.error('Error assigning badge:', error);

      window.location.href = 'index.php';

    }

  }

}



function exitGame() {

  Swal.fire({

    title: 'Are you sure you want to exit?',

    icon: 'warning',

    showCancelButton: true,

    confirmButtonText: 'Yes, exit',

    cancelButtonText: 'Cancel',

  }).then((result) => {

    if (result.isConfirmed) {

      window.location.href = 'welcome.php';

    }

  });

}



// Initialize

window.onload = () => {

  renderQuestion();

};





</script> -->