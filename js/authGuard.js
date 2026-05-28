(async function protegerPagina() {
  try {
    const res = await fetch("../../api/perfilApi.php", {
      cache: "no-store"
    });

    if (res.status === 401 || res.status === 403) {
      window.location.replace("../cadastrologin.html");
      return;
    }

    const data = await res.json().catch(() => null);

    if (!res.ok || !data || data.error) {
      window.location.replace("../cadastrologin.html");
    }
  } catch (err) {
    window.location.replace("../cadastrologin.html");
  }
})();
