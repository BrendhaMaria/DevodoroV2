const API_URL = "../../api/tarefasApi.php";
const EQUIPES_API_URL = "../../api/equipeApi.php";
const FUNCIONARIOS_API_URL = "../../api/funcionariosApi.php";
const apiClient = window.DevodoroApi;

if (!apiClient) {
  throw new Error("DevodoroApi nao foi carregado antes de tarefas.js.");
}

const tarefasRequestJson = apiClient.requestJson;

let equipesDisponiveis = [];
let funcionariosDisponiveis = [];
let editingTaskId = null;

function getTaskList() {
  return document.getElementById("taskList");
}

function formatDate(date) {
  if (!date) return "Sem prazo";

  const d = new Date(`${date}T00:00:00`);

  if (Number.isNaN(d.getTime())) {
    return "Sem prazo";
  }

  return d.toLocaleDateString("pt-BR");
}

function selectedIds(selectId) {
  const select = document.getElementById(selectId);

  if (!select) return [];

  return Array.from(select.selectedOptions).map(option => Number(option.value));
}

function fillMultiSelect(selectId, items, idField) {
  const select = document.getElementById(selectId);

  if (!select) return;

  select.innerHTML = "";

  items.forEach(item => {
    const option = document.createElement("option");
    option.value = item[idField];
    option.textContent = item.nome;
    select.appendChild(option);
  });
}

function setSelectedOptions(selectId, ids) {
  const select = document.getElementById(selectId);
  const selected = new Set((ids || []).map(id => Number(id)));

  if (!select) return;

  Array.from(select.options).forEach(option => {
    option.selected = selected.has(Number(option.value));
  });
}

function createBadge(text) {
  const badge = document.createElement("span");
  badge.className = "badge";
  badge.textContent = text;

  return badge;
}

function createMetaLine(label, items) {
  const line = document.createElement("p");
  const names = Array.isArray(items) ? items.map(item => item.nome).filter(Boolean) : [];
  line.textContent = `${label}: ${names.length > 0 ? names.join(", ") : "Nenhum"}`;

  return line;
}

function createTaskElement(task) {
  const item = document.createElement("div");
  item.className = "task";

  const content = document.createElement("div");
  content.className = "task-content";

  const title = document.createElement("strong");
  title.textContent = task.titulo || "";

  const deadline = document.createElement("p");
  deadline.textContent = `Prazo: ${formatDate(task.prazo_entrega)}`;

  content.appendChild(title);
  content.appendChild(deadline);
  content.appendChild(createMetaLine("Equipes", task.equipes));
  content.appendChild(createMetaLine("Funcionarios", task.funcionarios));

  const actions = document.createElement("span");
  actions.className = "task-actions";
  actions.appendChild(createBadge(task.estado || ""));
  actions.appendChild(createBadge(task.prioridade || ""));

  const editButton = document.createElement("button");
  editButton.className = "btn-link";
  editButton.type = "button";
  editButton.textContent = "Vinculos";
  editButton.addEventListener("click", () => openAssignmentModal(task));

  const deleteButton = document.createElement("button");
  deleteButton.className = "btn-x";
  deleteButton.type = "button";
  deleteButton.textContent = "X";
  deleteButton.addEventListener("click", () => deleteTask(task.id_tarefa));

  actions.appendChild(editButton);
  actions.appendChild(deleteButton);

  item.appendChild(content);
  item.appendChild(actions);

  return item;
}

function renderTasks(tasks) {
  const list = getTaskList();

  if (!list) return;

  list.innerHTML = "";

  if (!Array.isArray(tasks) || tasks.length === 0) {
    const empty = document.createElement("p");
    empty.textContent = "Nenhuma tarefa cadastrada.";
    list.appendChild(empty);
    return;
  }

  tasks.forEach(task => {
    list.appendChild(createTaskElement(task));
  });
}

async function loadTaskOptions() {
  const [equipes, funcionarios] = await Promise.all([
    tarefasRequestJson(EQUIPES_API_URL),
    tarefasRequestJson(FUNCIONARIOS_API_URL)
  ]);

  equipesDisponiveis = Array.isArray(equipes) ? equipes : [];
  funcionariosDisponiveis = Array.isArray(funcionarios) ? funcionarios : [];

  fillMultiSelect("equipesTarefa", equipesDisponiveis, "id");
  fillMultiSelect("funcionariosTarefa", funcionariosDisponiveis, "id_funcionario");
}

async function loadTasks() {
  try {
    const tasks = await tarefasRequestJson(API_URL);
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

  if (!input || !estado || !prioridade || !prazo) {
    console.error("Formulario de tarefa incompleto.");
    return;
  }

  const titulo = input.value.trim();

  if (!titulo) return;

  try {
    await tarefasRequestJson(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        titulo,
        estado: estado.value,
        prioridade: prioridade.value,
        prazo_entrega: prazo.value,
        id_equipes: selectedIds("equipesTarefa"),
        id_funcionarios: selectedIds("funcionariosTarefa")
      })
    });

    input.value = "";
    prazo.value = "";
    setSelectedOptions("equipesTarefa", []);
    setSelectedOptions("funcionariosTarefa", []);

    closeModal();
    await loadTasks();
  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}

async function updateTaskAssignments() {
  try {
    await tarefasRequestJson(API_URL, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        acao: "vinculos",
        id_tarefa: editingTaskId,
        id_equipes: selectedIds("equipesTarefa"),
        id_funcionarios: selectedIds("funcionariosTarefa")
      })
    });

    closeModal();
    await loadTasks();
  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}

function saveTask() {
  if (editingTaskId) {
    updateTaskAssignments();
    return;
  }

  addTask();
}

async function deleteTask(id) {
  try {
    await tarefasRequestJson(API_URL, {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ id_tarefa: id })
    });

    await loadTasks();
  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}

function resetModalMode() {
  editingTaskId = null;
  const title = document.getElementById("taskModalTitle");
  const saveButton = document.getElementById("taskModalSave");
  const input = document.getElementById("taskInput");
  const prioridade = document.getElementById("prioridade");
  const estado = document.getElementById("estado");
  const prazo = document.getElementById("prazo");

  if (title) title.textContent = "Nova Tarefa";
  if (saveButton) saveButton.textContent = "Criar tarefa";
  if (input) input.readOnly = false;
  if (prioridade) prioridade.disabled = false;
  if (estado) estado.disabled = false;
  if (prazo) prazo.disabled = false;
}

async function openModal() {
  try {
    if (equipesDisponiveis.length === 0 && funcionariosDisponiveis.length === 0) {
      await loadTaskOptions();
    }
  } catch (err) {
    console.error(err);
    alert(err.message);
  }

  resetModalMode();
  const input = document.getElementById("taskInput");
  const prazo = document.getElementById("prazo");
  const modal = document.getElementById("taskModal");

  if (!input || !prazo || !modal) {
    console.error("Modal de tarefa nao encontrado.");
    return;
  }

  input.value = "";
  prazo.value = "";
  setSelectedOptions("equipesTarefa", []);
  setSelectedOptions("funcionariosTarefa", []);
  modal.classList.add("active");
}

async function openAssignmentModal(task) {
  try {
    if (equipesDisponiveis.length === 0 && funcionariosDisponiveis.length === 0) {
      await loadTaskOptions();
    }
  } catch (err) {
    console.error(err);
    alert(err.message);
    return;
  }

  editingTaskId = task.id_tarefa;
  const title = document.getElementById("taskModalTitle");
  const saveButton = document.getElementById("taskModalSave");
  const input = document.getElementById("taskInput");
  const prioridade = document.getElementById("prioridade");
  const estado = document.getElementById("estado");
  const prazo = document.getElementById("prazo");
  const modal = document.getElementById("taskModal");

  if (!title || !saveButton || !input || !prioridade || !estado || !prazo || !modal) {
    console.error("Modal de tarefa incompleto.");
    return;
  }

  title.textContent = "Vinculos da tarefa";
  saveButton.textContent = "Salvar vinculos";
  input.value = task.titulo || "";
  input.readOnly = true;
  prioridade.value = task.prioridade || "MEDIA";
  prioridade.disabled = true;
  estado.value = task.estado || "PENDENTE";
  estado.disabled = true;
  prazo.value = task.prazo_entrega || "";
  prazo.disabled = true;

  setSelectedOptions("equipesTarefa", (task.equipes || []).map(equipe => equipe.id));
  setSelectedOptions(
    "funcionariosTarefa",
    (task.funcionarios || []).map(funcionario => funcionario.id_funcionario)
  );

  modal.classList.add("active");
}

function closeModal() {
  const modal = document.getElementById("taskModal");

  if (modal) {
    modal.classList.remove("active");
  }

  resetModalMode();
}

function bindTaskEvents() {
  document.getElementById("openTaskModal")?.addEventListener("click", openModal);
  document.getElementById("closeTaskModal")?.addEventListener("click", closeModal);
  document.getElementById("cancelTaskModal")?.addEventListener("click", closeModal);
  document.getElementById("taskModalSave")?.addEventListener("click", saveTask);
}

window.openModal = openModal;
window.closeModal = closeModal;
window.saveTask = saveTask;

document.addEventListener("DOMContentLoaded", () => {
  bindTaskEvents();
  loadTaskOptions().catch(err => console.error(err));
  loadTasks();
});
