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
          <span class="badge">${task.status}</span>
          <span class="badge">${task.prioridade}</span>
          <button class="btn-x" onclick="deleteTask(${task.id_tarefa})">X</button>
        </span>
      </div>
    `;
  });
}

// ==========================
// CRIAR TAREFA
// ==========================
async function addTask() {
  const input = document.getElementById("taskInput");
  const status = document.getElementById("status");
  const prioridade = document.getElementById("prioridade");

  const titulo = input.value;

  if (!titulo) return;

  await fetch(API_URL, {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      titulo: titulo,
      status: status.value,
      prioridade: prioridade.value
    })
  });

  input.value = "";
  loadTasks();
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