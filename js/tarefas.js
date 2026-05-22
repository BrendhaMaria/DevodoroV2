const API_URL = "../../api/tarefasApi.php";

function getTaskList() {
  return document.getElementById("taskList");
}

function getJsonError(data, fallback) {
  return data && data.error ? data.error : fallback;
}

async function requestJson(url, options = {}) {
  const res = await fetch(url, options);
  const data = await res.json().catch(() => null);

  if (!res.ok) {
    throw new Error(getJsonError(data, "Erro ao processar requisicao"));
  }

  return data;
}

function formatDate(date) {
  if (!date) return "Sem prazo";

  const d = new Date(`${date}T00:00:00`);

  if (Number.isNaN(d.getTime())) {
    return "Sem prazo";
  }

  return d.toLocaleDateString("pt-BR");
}

function createBadge(text) {
  const badge = document.createElement("span");
  badge.className = "badge";
  badge.textContent = text;

  return badge;
}

function createTaskElement(task) {
  const item = document.createElement("div");
  item.className = "task";

  const content = document.createElement("div");

  const title = document.createElement("strong");
  title.textContent = task.titulo || "";

  const deadline = document.createElement("p");
  deadline.textContent = `Prazo: ${formatDate(task.prazo_entrega)}`;

  content.appendChild(title);
  content.appendChild(deadline);

  const actions = document.createElement("span");
  actions.appendChild(createBadge(task.estado || ""));
  actions.appendChild(createBadge(task.prioridade || ""));

  const deleteButton = document.createElement("button");
  deleteButton.className = "btn-x";
  deleteButton.type = "button";
  deleteButton.textContent = "X";
  deleteButton.addEventListener("click", () => deleteTask(task.id_tarefa));

  actions.appendChild(deleteButton);

  item.appendChild(content);
  item.appendChild(actions);

  return item;
}

function renderTasks(tasks) {
  const list = getTaskList();

  if (!list) return;

  list.innerHTML = "";

  tasks.forEach(task => {
    list.appendChild(createTaskElement(task));
  });
}

async function loadTasks() {
  try {
    const tasks = await requestJson(API_URL);
    renderTasks(Array.isArray(tasks) ? tasks : []);
  } catch (err) {
    console.error(err);
    renderTasks([]);
    alert(err.message);
  }
}

async function addTask() {
  const input = document.getElementById("taskInput");
  const estado = document.getElementById("estado");
  const prioridade = document.getElementById("prioridade");
  const prazo = document.getElementById("prazo");

  const titulo = input.value.trim();

  if (!titulo) return;

  try {
    await requestJson(API_URL, {
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
  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}

async function deleteTask(id) {
  try {
    await requestJson(API_URL, {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ id_tarefa: id })
    });

    loadTasks();
  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}

function openModal() {
  document.getElementById("taskModal").classList.add("active");
}

function closeModal() {
  document.getElementById("taskModal").classList.remove("active");
}

document.addEventListener("DOMContentLoaded", loadTasks);
