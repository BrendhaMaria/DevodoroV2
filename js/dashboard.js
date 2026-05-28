const TASKS_API_URL = "../../api/tarefasApi.php";
const dashboardRequestJson = window.DevodoroApi.requestJson;

let timer;
let time = 25 * 60;
let isRunning = false;

let mode = "focus"; // focus | shortBreak | longBreak
let cycles = 0;

function setCardValue(cardId, value) {
  const card = document.querySelector(`#${cardId} h2`);

  if (card) {
    card.textContent = value;
  }
}

function formatTaskDate(date) {
  if (!date) return "Sem prazo";

  const parsedDate = new Date(`${date}T00:00:00`);

  if (Number.isNaN(parsedDate.getTime())) {
    return "Sem prazo";
  }

  return parsedDate.toLocaleDateString("pt-BR");
}

function createBadge(text) {
  const badge = document.createElement("span");
  badge.className = "badge";
  badge.textContent = text;

  return badge;
}

function createRecentTask(task) {
  const item = document.createElement("div");
  item.className = "task";

  const content = document.createElement("div");

  const title = document.createElement("strong");
  title.textContent = task.titulo || "";

  const deadline = document.createElement("p");
  deadline.textContent = `Prazo: ${formatTaskDate(task.prazo_entrega)}`;

  content.appendChild(title);
  content.appendChild(deadline);

  const meta = document.createElement("span");
  meta.appendChild(createBadge(task.estado || ""));
  meta.appendChild(createBadge(task.prioridade || ""));

  item.appendChild(content);
  item.appendChild(meta);

  return item;
}

function renderRecentTasks(tasks) {
  const list = document.getElementById("recentTasks");

  if (!list) return;

  list.innerHTML = "";

  const recentTasks = Array.isArray(tasks) ? tasks.slice(0, 5) : [];

  if (recentTasks.length === 0) {
    const empty = document.createElement("p");
    empty.textContent = "Nenhuma tarefa cadastrada.";
    list.appendChild(empty);
    return;
  }

  recentTasks.forEach(task => {
    list.appendChild(createRecentTask(task));
  });
}

async function loadDashboardTasks() {
  try {
    const [summary, tasks] = await Promise.all([
      dashboardRequestJson(`${TASKS_API_URL}?resumo=1`),
      dashboardRequestJson(TASKS_API_URL)
    ]);

    setCardValue("total", summary.total || 0);
    setCardValue("concluidas", summary.concluidas || 0);
    setCardValue("emprogresso", summary.em_progresso || 0);
    setCardValue("atrasados", summary.atrasadas || 0);

    renderRecentTasks(tasks);
  } catch (err) {
    console.error(err);

    setCardValue("total", 0);
    setCardValue("concluidas", 0);
    setCardValue("emprogresso", 0);
    setCardValue("atrasados", 0);
    renderRecentTasks([]);
  }
}

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
document.addEventListener("DOMContentLoaded", loadDashboardTasks);
