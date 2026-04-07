let tasks = JSON.parse(localStorage.getItem("tasks")) || [];

/* NAVEGAÇÃO */
function loadPage(page) {
  document.querySelectorAll(".nav a").forEach(a => a.classList.remove("active"));
  event.target.classList.add("active");

  if (page === "dashboard") dashboard();
  if (page === "tarefas") tarefas();
  if (page === "equipe") equipe();
  if (page === "config") config();
}

/* DASHBOARD */
function dashboard() {
  let total = tasks.length;
  let done = tasks.filter(t => t.status === "done").length;

  document.getElementById("app").innerHTML = `
    <div class="header">
      <div class="title">Dashboard</div>
      <div class="actions">
        <input class="search" placeholder="Buscar tarefas...">
        <button class="btn" onclick="tarefas()">+ Nova Tarefa</button>
      </div>
    </div>

    <div class="cards">
      <div class="card"><p>Total</p><h2>${total}</h2></div>
      <div class="card"><p>Concluídas</p><h2>${done}</h2></div>
      <div class="card"><p>Em progresso</p><h2>${total - done}</h2></div>
      <div class="card"><p>Atrasadas</p><h2>0</h2></div>
    </div>

    <div class="task-list">
      ${tasks.map(t => `
        <div class="task">
          ${t.text}
          <span class="badge ${t.status}">${t.status}</span>
        </div>
      `).join("")}
    </div>
  `;
}

/* TAREFAS */
function tarefas() {
  document.getElementById("app").innerHTML = `
    <div class="header">
      <div class="title">Tarefas</div>
      <button class="btn" onclick="addTask()">+ Nova</button>
    </div>

    <input id="taskInput" placeholder="Nome da tarefa" type="text">
    <select id="status">
      <option value="high">Alta</option>
      <option value="medium">Média</option>
      <option value="done">Concluída</option>
    </select>

    <div class="task-list">
      ${tasks.map((t,i) => `
        <div class="task">
          ${t.text}
          <span>
            <span class="badge ${t.status}">${t.status}</span>
            <button onclick="removeTask(${i})">X</button>
          </span>
        </div>
      `).join("")}
    </div>
  `;
}

function addTask() {
  let text = document.getElementById("taskInput").value;
  let status = document.getElementById("status").value;

  if (!text) return;

  tasks.push({ text, status });
  localStorage.setItem("tasks", JSON.stringify(tasks));
  tarefas();
}

function removeTask(i) {
  tasks.splice(i,1);
  localStorage.setItem("tasks", JSON.stringify(tasks));
  tarefas();
}

/* EQUIPE */
function equipe() {
  document.getElementById("app").innerHTML = `
    <div class="header">
      <div class="title">Equipe</div>
      <button class="btn">Adicionar</button>
    </div>

    <div class="card">Nenhum membro ainda</div>
  `;
}

/* CONFIG */
function config() {
  document.getElementById("app").innerHTML = `
    <div class="header">
      <div class="title">Configurações</div>
    </div>

    <div class="card">
      <input placeholder="Nome">
      <input placeholder="Email">
      <button class="btn">Salvar</button>
    </div>
  `;
}

/* INICIAL */
dashboard();