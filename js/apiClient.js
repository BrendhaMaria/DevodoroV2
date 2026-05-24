(function () {
  function getJsonError(data, fallback) {
    return data && data.error ? data.error : fallback;
  }

  async function requestJson(url, options = {}) {
    const requestOptions = {
      cache: "no-store",
      ...options
    };

    const res = await fetch(url, requestOptions);
    const data = await res.json().catch(() => null);

    if (!res.ok || (data && data.success === false)) {
      throw new Error(getJsonError(data, "Erro ao processar requisicao."));
    }

    return data;
  }

  function clearElement(element) {
    if (element) {
      element.textContent = "";
    }
  }

  function createButton(text, onClick, className = "") {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = text;

    if (className) {
      button.className = className;
    }

    button.addEventListener("click", onClick);

    return button;
  }

  window.DevodoroApi = {
    requestJson,
    clearElement,
    createButton
  };
})();
