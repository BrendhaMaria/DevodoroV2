(function () {
  const API_PERFIL = "../../api/perfilApi.php";
  const API_EMPRESA = "../../api/empresa-info.php";
  const AVATAR_PADRAO = "../../uploads/perfis/default.png";
  const MAX_AVATAR_SIZE = 5 * 1024 * 1024;
  const { requestJson } = window.DevodoroApi;

  const elements = {
    nome: document.getElementById("nameInput"),
    email: document.getElementById("emailInput"),
    avatarInput: document.getElementById("avatarInput"),
    previewAvatar: document.getElementById("previewAvatar"),
    salvarPerfil: document.getElementById("saveProfile"),
    codigoCard: document.getElementById("empresaCodigoCard"),
    codigoEmpresa: document.getElementById("codigoEmpresa"),
    mostrarCodigo: document.getElementById("mostrarCodigoBtn"),
    copiarCodigo: document.getElementById("copiarCodigoBtn")
  };

  function normalizarAvatar(caminho) {
    if (!caminho) {
      return AVATAR_PADRAO;
    }

    if (/^(https?:)?\/\//i.test(caminho) || caminho.startsWith("data:")) {
      return caminho;
    }

    return "../../" + caminho.replace(/^(\.\.\/|\.\/|\/)+/, "");
  }

  function atualizarCamposPerfil(perfil) {
    elements.nome.value = perfil.nome || "";
    elements.email.value = perfil.email || "";
    elements.previewAvatar.src = normalizarAvatar(perfil.foto_perfil);
  }

  async function carregarPerfil() {
    const perfil = await requestJson(API_PERFIL);
    atualizarCamposPerfil(perfil);
  }

  async function carregarEmpresa() {
    if (!elements.codigoCard) {
      return;
    }

    try {
      const empresa = await requestJson(API_EMPRESA);
      elements.codigoEmpresa.value = empresa.codigo_acesso || "";
    } catch (err) {
      console.log("Erro ao carregar empresa", err);
      elements.codigoCard.style.display = "none";
    }
  }

  function prepararPreviewAvatar() {
    const file = elements.avatarInput.files[0];

    if (!file) {
      return;
    }

    if (!["image/png", "image/jpeg"].includes(file.type)) {
      alert("Use uma imagem PNG ou JPG.");
      elements.avatarInput.value = "";
      return;
    }

    if (file.size > MAX_AVATAR_SIZE) {
      alert("Imagem muito grande. Maximo 5MB.");
      elements.avatarInput.value = "";
      return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {
      elements.previewAvatar.src = event.target.result;
    };

    reader.readAsDataURL(file);
  }

  async function salvarPerfil() {
    const nome = elements.nome.value.trim();
    const email = elements.email.value.trim();
    const foto = elements.avatarInput.files[0];

    if (!nome) {
      alert("Nome obrigatorio.");
      elements.nome.focus();
      return;
    }

    if (!email) {
      alert("Email obrigatorio.");
      elements.email.focus();
      return;
    }

    const formData = new FormData();
    formData.append("nome", nome);
    formData.append("email", email);

    if (foto) {
      formData.append("foto", foto);
    }

    elements.salvarPerfil.disabled = true;

    try {
      const data = await requestJson(API_PERFIL, {
        method: "POST",
        body: formData
      });

      if (data.perfil) {
        atualizarCamposPerfil(data.perfil);
      } else {
        await carregarPerfil();
      }

      elements.avatarInput.value = "";
      carregarPerfilSidebar();
      alert(data.message || "Perfil atualizado.");
    } catch (err) {
      alert(err.message || "Erro ao salvar perfil.");
    } finally {
      elements.salvarPerfil.disabled = false;
    }
  }

  function alternarCodigo() {
    if (elements.codigoEmpresa.type === "password") {
      elements.codigoEmpresa.type = "text";
      elements.mostrarCodigo.textContent = "Ocultar Codigo";
      return;
    }

    elements.codigoEmpresa.type = "password";
    elements.mostrarCodigo.textContent = "Mostrar Codigo";
  }

  async function copiarCodigo() {
    const codigo = elements.codigoEmpresa.value;

    if (!codigo) {
      alert("Codigo indisponivel.");
      return;
    }

    try {
      await navigator.clipboard.writeText(codigo);
    } catch (err) {
      elements.codigoEmpresa.select();
      document.execCommand("copy");
    }

    alert("Codigo copiado.");
  }

  function iniciarEventos() {
    elements.avatarInput.addEventListener("change", prepararPreviewAvatar);
    elements.salvarPerfil.addEventListener("click", salvarPerfil);
    elements.mostrarCodigo.addEventListener("click", alternarCodigo);
    elements.copiarCodigo.addEventListener("click", copiarCodigo);
  }

  async function iniciar() {
    iniciarEventos();

    try {
      await carregarPerfil();
    } catch (err) {
      console.log("Erro ao carregar perfil", err);
      alert(err.message || "Erro ao carregar perfil.");
    }

    carregarEmpresa();
  }

  iniciar();
})();
