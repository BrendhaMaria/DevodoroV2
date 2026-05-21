let timer;
let time = 25 * 60;
let isRunning = false;

let mode = "focus"; // focus | shortBreak | longBreak
let cycles = 0;

function updateDisplay() {
  let minutes = Math.floor(time / 60);
  let seconds = time % 60;

  document.getElementById("timer").innerText =
    `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

  document.getElementById("cycles").innerText = cycles;

  let modeText = {
    focus: "Foco",
    shortBreak: "Pausa curta",
    longBreak: "Pausa longa"
  };

  document.getElementById("mode").innerText = modeText[mode];
}

function startTimer() {
  if (isRunning) return;

  isRunning = true;

  timer = setInterval(() => {
    if (time > 0) {
      time--;
      updateDisplay();
    } else {
      nextMode();
    }
  }, 1000);
}

function pauseTimer() {
  clearInterval(timer);
  isRunning = false;
}

function resetTimer() {
  clearInterval(timer);
  isRunning = false;

  mode = "focus";
  time = 25 * 60;
  cycles = 0;

  updateDisplay();
}

function nextMode() {
  clearInterval(timer);
  isRunning = false;

  if (mode === "focus") {
    cycles++;

    if (cycles % 4 === 0) {
      mode = "longBreak";
      time = 15 * 60;
    } else {
      mode = "shortBreak";
      time = 5 * 60;
    }
  } else {
    mode = "focus";
    time = 25 * 60;
  }

  updateDisplay();
  startTimer(); // inicia automaticamente próximo ciclo

  alert(`Agora: ${document.getElementById("mode").innerText}`);
}

updateDisplay();