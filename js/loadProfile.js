async function carregarPerfilSidebar() {

    try {

        const res =
            await fetch("../../api/perfilApi.php", {
                cache: "no-store"
            });

        if (res.status === 401 || res.status === 403) {
            window.location.replace("../cadastrologin.html");
            return;
        }

        const data = await res.json();

        if (data.error) {
            if (data.error.toLowerCase().includes("autenticado")) {
                window.location.replace("../cadastrologin.html");
                return;
            }

            console.log(data.error);
            return;
        }

        /* =========================
           NOME
        ========================= */

        const nomeElements =
            document.querySelectorAll(".sidebarNome");

        nomeElements.forEach(el => {
            el.textContent = data.nome;
        });

        /* =========================
           AVATAR
        ========================= */

        const avatarElements =
            document.querySelectorAll(".sidebarAvatar");

        avatarElements.forEach(el => {

            const foto = data.foto_perfil
                ? "../../" + data.foto_perfil
                : "../../uploads/perfis/default.png";

            el.innerHTML = `
                <img
                    src="${foto}"
                    alt="Avatar"
                    style="
                        width:100%;
                        height:100%;
                        object-fit:cover;
                        border-radius:50%;
                    "
                >
            `;
        });

    } catch (err) {

        console.log(
            "Erro ao carregar perfil",
            err
        );
    }
}

carregarPerfilSidebar();
