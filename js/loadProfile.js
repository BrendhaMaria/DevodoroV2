async function carregarPerfilSidebar() {

    try {

        const res =
            await fetch("../../api/perfilApi.php");

        const data = await res.json();

        if (data.error) {
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