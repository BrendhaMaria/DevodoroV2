// URL da API (ajusta se necessário)
const API_URL = "../../api/tarefasApi.php";

// ==========================
// CARREGAR TAREFAS
// ==========================
async function loadTasks() {
  const res = await fetch(API_URL);
  const tasks = await res.json();

  renderTasks(tasks);
}

// ==========================
// RENDERIZAR NA TELA
// ==========================
function renderTasks(tasks) {
  const list = document.getElementById("taskList");
  list.innerHTML = "";

  tasks.forEach(task => {
    list.innerHTML += `
      <div class="task">
        ${task.titulo}
        <span>
          <span class="badge">${task.estado}</span>
          <span class="badge">${task.prioridade}</span>
          <button class="btn-x" onclick="deleteTask(${task.id_tarefa})">X</button>
        </span>
      </div>
    `;
  });
}

document.addEventListener("DOMContentLoaded", () => {
  loadTasks();
});

// ==========================
// CRIAR TAREFA
// ==========================
async function addTask() {
  const input = document.getElementById("taskInput");
  const estado = document.getElementById("estado");
  const prioridade = document.getElementById("prioridade");

  console.log({ input, estado, prioridade });

  if (!input || !estado || !prioridade) {
    console.error("Elementos não encontrados no DOM");
    return;
  }

  const titulo = input.value;

  if (!titulo) return;

  await fetch(API_URL, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      titulo,
      estado: estado.value,
      prioridade: prioridade.value
    })
  });

  input.value = "";
}

// ==========================
// DELETAR TAREFA
// ==========================
async function deleteTask(id) {
  await fetch(API_URL, {
    method: "DELETE",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({ id_tarefa: id })
  });

  loadTasks();
}

// ==========================
// INICIAR
// ==========================
loadTasks();

