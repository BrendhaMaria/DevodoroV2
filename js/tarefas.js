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
function renderTasks(tasks) {
  const list = document.getElementById("taskList");
  list.innerHTML = "";

  tasks.forEach(task => {
    list.innerHTML += `
      <div class="task">
        <div>
          <strong>${task.titulo}</strong>
          <p>Prazo: ${task.prazo_entrega || "Sem prazo"}</p>
        </div>

        <span>
          <span class="badge">${task.estado}</span>
          <span class="badge">${task.prioridade}</span>

          <button class="btn-x" onclick="deleteTask(${task.id_tarefa})">
            X
          </button>
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
  const prazo = document.getElementById("prazo");

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
      prioridade: prioridade.value,
      prazo_entrega: prazo.value
    })
  });

  input.value = "";
  prazo.value = "";

  closeModal();
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
// ==========================
// ABRIR MODAL
// ==========================
function openModal() {
  document.getElementById("taskModal").classList.add("active");
}

// ==========================
// FECHAR MODAL
// ==========================
function closeModal() {
  document.getElementById("taskModal").classList.remove("active");
}

