(function () {
  function getJsonError(data, fallback) {
    return data && data.error ? data.error : fallback;
  }

  function mergeHeaders(baseHeaders, customHeaders) {
    const headers = new Headers(baseHeaders);

    new Headers(customHeaders || {}).forEach((value, key) => {
      headers.set(key, value);
    });

    return headers;
  }

  async function requestJson(url, options = {}) {
    const requestOptions = {
      cache: "no-store",
      credentials: "same-origin",
      ...options,
      headers: mergeHeaders({
        Accept: "application/json"
      }, options.headers)
    };

    const res = await fetch(url, requestOptions);
    const raw = await res.text();
    let data = null;

    if (raw.trim() !== "") {
      try {
        data = JSON.parse(raw);
      } catch (err) {
        console.error("Resposta nao JSON da API", {
          url,
          status: res.status,
          body: raw.slice(0, 500)
        });

        throw new Error("A API retornou uma resposta invalida.");
      }
    }

    if (!res.ok || (data && data.success === false)) {
      throw new Error(getJsonError(data, "Erro ao processar requisicao."));
    }

    return data;
  }

  function clearElement(element) {
    if (element) {
      element.replaceChildren();
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
