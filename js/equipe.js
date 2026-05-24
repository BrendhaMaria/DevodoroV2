const EQUIPES_API = "../../api/equipeApi.php";
const EQUIPE_FUNCIONARIO_API = "../../api/equipeFuncionarioApi.php";
const FUNCIONARIOS_DISPONIVEIS_API = "../../api/funcionariosDisponiveis.php";
const { requestJson, clearElement, createButton } = window.DevodoroApi;

let selectedTeam = null;

function createCard(text, onClick) {
  const card = document.createElement("div");
  card.className = "card";
  card.textContent = text;

  if (onClick) {
    card.tabIndex = 0;
    card.addEventListener("click", onClick);
    card.addEventListener("keydown", event => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        onClick();
      }
    });
  }

  return card;
}

function renderEmpty(container, text) {
  const message = document.createElement("p");
  message.textContent = text;
  container.appendChild(message);
}

function renderTeams(teams) {
  const list = document.getElementById("teamList");
  clearElement(list);

  if (!Array.isArray(teams) || teams.length === 0) {
    renderEmpty(list, "Nenhuma equipe cadastrada.");
    return;
  }

  teams.forEach(team => {
    list.appendChild(
      createCard(team.nome || "", () => selectTeam(team.id))
    );
  });
}

function renderMembers(members) {
  const list = document.getElementById("members");
  clearElement(list);

  if (!Array.isArray(members) || members.length === 0) {
    renderEmpty(list, "Nenhum membro nesta equipe.");
    return;
  }

  members.forEach(member => {
    const card = createCard(member.nome || "");
    card.appendChild(
      createButton("Remover", () => removeFromTeam(member.id_funcionario))
    );

    list.appendChild(card);
  });
}

function renderAvailableEmployees(employees) {
  const list = document.getElementById("availableEmployees");
  clearElement(list);

  if (!selectedTeam) {
    renderEmpty(list, "Selecione uma equipe.");
    return;
  }

  if (!Array.isArray(employees) || employees.length === 0) {
    renderEmpty(list, "Nenhum funcionario disponivel.");
    return;
  }

  employees.forEach(employee => {
    const card = createCard(employee.nome || "");
    card.appendChild(
      createButton("Adicionar", () => addToTeam(employee.id_funcionario))
    );

    list.appendChild(card);
  });
}

async function loadTeams() {
  try {
    const teams = await requestJson(EQUIPES_API);
    renderTeams(Array.isArray(teams) ? teams : []);
  } catch (err) {
    console.error(err);
    alert(err.message);
    renderTeams([]);
  }
}

async function selectTeam(id) {
  selectedTeam = id;

  await Promise.all([
    loadTeamMembers(),
    loadAvailableEmployees()
  ]);
}

async function loadTeamMembers() {
  const list = document.getElementById("members");

  if (!selectedTeam) {
    clearElement(list);
    renderEmpty(list, "Selecione uma equipe.");
    return;
  }

  try {
    const members = await requestJson(
      `${EQUIPE_FUNCIONARIO_API}?id_equipe=${encodeURIComponent(selectedTeam)}`
    );

    renderMembers(Array.isArray(members) ? members : []);
  } catch (err) {
    console.error(err);
    alert(err.message);
    renderMembers([]);
  }
}

async function loadAvailableEmployees() {
  if (!selectedTeam) {
    renderAvailableEmployees([]);
    return;
  }

  try {
    const employees = await requestJson(
      `${FUNCIONARIOS_DISPONIVEIS_API}?id_equipe=${encodeURIComponent(selectedTeam)}`
    );

    renderAvailableEmployees(Array.isArray(employees) ? employees : []);
  } catch (err) {
    console.error(err);
    alert(err.message);
    renderAvailableEmployees([]);
  }
}

async function createTeam() {
  const nome = prompt("Nome da equipe:");

  if (!nome || nome.trim() === "") return;

  try {
    await requestJson(EQUIPES_API, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ nome: nome.trim() })
    });

    await loadTeams();
  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}

async function addToTeam(idFuncionario) {
  if (!selectedTeam) return;

  try {
    await requestJson(EQUIPE_FUNCIONARIO_API, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        id_equipe: selectedTeam,
        id_funcionario: idFuncionario
      })
    });

    await Promise.all([
      loadTeamMembers(),
      loadAvailableEmployees()
    ]);
  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}

async function removeFromTeam(idFuncionario) {
  if (!selectedTeam) return;

  try {
    await requestJson(
      `${EQUIPE_FUNCIONARIO_API}?id_equipe=${encodeURIComponent(selectedTeam)}&id_funcionario=${encodeURIComponent(idFuncionario)}`,
      { method: "DELETE" }
    );

    await Promise.all([
      loadTeamMembers(),
      loadAvailableEmployees()
    ]);
  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  renderAvailableEmployees([]);
  loadTeams();
});
