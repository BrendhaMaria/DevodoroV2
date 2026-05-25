const PROFILE_API_URL = "../../api/perfilApi.php";
const DEFAULT_PROFILE_AVATAR = "../../uploads/perfis/default.png";
const devodoroProfileRequestJson = window.DevodoroApi.requestJson;

function getProfileAvatarUrl(path) {
  if (!path) {
    return DEFAULT_PROFILE_AVATAR;
  }

  if (/^(https?:)?\/\//i.test(path) || path.startsWith("data:")) {
    return path;
  }

  return "../../" + path.replace(/^(\.\.\/|\.\/|\/)+/, "");
}

function renderSidebarAvatar(container, avatarUrl) {
  container.textContent = "";

  const img = document.createElement("img");
  img.src = avatarUrl;
  img.alt = "Avatar";
  img.style.width = "100%";
  img.style.height = "100%";
  img.style.objectFit = "cover";
  img.style.borderRadius = "50%";

  container.appendChild(img);
}

async function carregarPerfilSidebar() {
  try {
    const data = await devodoroProfileRequestJson(PROFILE_API_URL);

    document.querySelectorAll(".sidebarNome").forEach((el) => {
      el.textContent = data.nome || "";
    });

    const foto = getProfileAvatarUrl(data.foto_perfil);

    document.querySelectorAll(".sidebarAvatar").forEach((el) => {
      renderSidebarAvatar(el, foto);
    });
  } catch (err) {
    if (err.message && err.message.toLowerCase().includes("autenticado")) {
      window.location.replace("../cadastrologin.html");
      return;
    }

    console.log("Erro ao carregar perfil", err);
  }
}

carregarPerfilSidebar();
