const PROFILE_API_URL = "../../api/perfilApi.php";
const DEFAULT_PROFILE_AVATAR = "../../uploads/perfis/default.png";

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
    const res = await fetch(PROFILE_API_URL, {
      cache: "no-store"
    });

    if (res.status === 401 || res.status === 403) {
      window.location.replace("../cadastrologin.html");
      return;
    }

    const data = await res.json().catch(() => null);

    if (!res.ok || !data || data.error) {
      if (data && data.error && data.error.toLowerCase().includes("autenticado")) {
        window.location.replace("../cadastrologin.html");
        return;
      }

      console.log(data ? data.error : "Erro ao carregar perfil");
      return;
    }

    document.querySelectorAll(".sidebarNome").forEach((el) => {
      el.textContent = data.nome || "";
    });

    const foto = getProfileAvatarUrl(data.foto_perfil);

    document.querySelectorAll(".sidebarAvatar").forEach((el) => {
      renderSidebarAvatar(el, foto);
    });
  } catch (err) {
    console.log("Erro ao carregar perfil", err);
  }
}

carregarPerfilSidebar();
