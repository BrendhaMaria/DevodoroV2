(function () {
  const THEME_KEY = "theme";
  const DARK_THEME = "dark";
  const LIGHT_THEME = "light";
  const btn = document.getElementById("escuro");

  function aplicarTema(theme) {
    const dark = theme === DARK_THEME;

    document.documentElement.classList.toggle("dark", dark);
    document.body.classList.toggle("dark", dark);
    localStorage.setItem(THEME_KEY, dark ? DARK_THEME : LIGHT_THEME);
  }

  aplicarTema(localStorage.getItem(THEME_KEY) === DARK_THEME ? DARK_THEME : LIGHT_THEME);

  if (btn) {
    btn.addEventListener("click", () => {
      const temaAtual = document.documentElement.classList.contains("dark")
        ? DARK_THEME
        : LIGHT_THEME;

      aplicarTema(temaAtual === DARK_THEME ? LIGHT_THEME : DARK_THEME);
    });
  }
})();
