document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = "http://localhost/Warmi360-Refactor/public/index.php";
  const modal = document.getElementById("modalUsuaria");
  const btnNueva = document.getElementById("btnNuevaUsuaria");
  const cancelarModal = document.getElementById("cancelarModal");
  const form = document.getElementById("formUsuaria");
  const tituloModal = document.getElementById("tituloModal");

  // 👉 Abrir modal nueva usuaria
  btnNueva?.addEventListener("click", () => {
    form.reset();
    form.id_usuario.value = "";
    tituloModal.textContent = "Registrar Nueva Usuaria";
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  });

  // 👉 Cerrar modal
  cancelarModal?.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  });

  // 👉 Guardar usuaria (crear/editar)
  form?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const id = formData.get("id_usuario");
    const action = id ? "editar_usuaria" : "crear_usuaria";

    try {
      const response = await fetch(`${baseUrl}?action=${action}`, {
        method: "POST",
        body: formData,
      });

      const texto = await response.text();
      console.log("🧩 [Guardar] Respuesta cruda del servidor:", texto);

      let data;
      try {
        data = JSON.parse(texto);
      } catch (e) {
        console.error("❌ [Guardar] No se pudo parsear JSON:", e, texto);
        alert("El servidor no devolvió JSON válido (ver consola).");
        return;
      }

      if (data.success) {
        alert(data.message || "Operación exitosa ✅");
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        setTimeout(() => location.reload(), 700);
      } else {
        alert(data.message || "Ocurrió un error ❌");
      }
    } catch (error) {
      console.error("❌ [Guardar] Error de conexión:", error);
      alert("Error de conexión con el servidor ❌");
    }
  });

  // 👉 Editar usuaria
  document.querySelectorAll(".btnEditarUsuaria").forEach((btn) => {
    btn.addEventListener("click", () => {
      const usuaria = JSON.parse(btn.getAttribute("data-usuaria"));
      for (const campo in usuaria) {
        if (form.elements[campo]) form.elements[campo].value = usuaria[campo];
      }
      tituloModal.textContent = "Editar Usuaria";
      modal.classList.remove("hidden");
      modal.classList.add("flex");
    });
  });

  // 👉 Desactivar usuaria
  document.querySelectorAll(".btnDesactivarUsuaria").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.getAttribute("data-id");
      if (!id) return alert("ID no recibido ❌");
      const confirmar = confirm("¿Deseas desactivar esta usuaria?");
      if (!confirmar) return;

      const formData = new FormData();
      formData.append("id_usuario", id);

      try {
        const response = await fetch(`${baseUrl}?action=desactivar_usuaria`, {
          method: "POST",
          body: formData,
        });

        const texto = await response.text();
        console.log("🧩 [Desactivar] Respuesta cruda del servidor:", texto);

        let data;
        try {
          data = JSON.parse(texto);
        } catch (e) {
          console.error("❌ [Desactivar] No se pudo parsear JSON:", e, texto);
          alert("El servidor no devolvió JSON válido (ver consola).");
          return;
        }

        if (data.success) {
          alert(data.message || "Usuaria desactivada ✅");
          setTimeout(() => location.reload(), 700);
        } else {
          alert(data.message || "Error al desactivar usuaria ❌");
        }
      } catch (error) {
        console.error("❌ [Desactivar] Error de conexión:", error);
        alert("Error de conexión con el servidor ❌");
      }
    });
  });

  // 👉 Activar usuaria
  document.querySelectorAll(".btnActivarUsuaria").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = btn.getAttribute("data-id");
      if (!id) return alert("ID no recibido ❌");
      const confirmar = confirm("¿Deseas activar esta usuaria?");
      if (!confirmar) return;

      const formData = new FormData();
      formData.append("id_usuario", id);

      try {
        const response = await fetch(`${baseUrl}?action=activar_usuaria`, {
          method: "POST",
          body: formData,
        });

        const texto = await response.text();
        console.log("🧩 [Activar] Respuesta cruda del servidor:", texto);

        let data;
        try {
          data = JSON.parse(texto);
        } catch (e) {
          console.error("❌ [Activar] No se pudo parsear JSON:", e, texto);
          alert("El servidor no devolvió JSON válido (ver consola).");
          return;
        }

        if (data.success) {
          alert(data.message || "Usuaria activada ✅");
          setTimeout(() => location.reload(), 700);
        } else {
          alert(data.message || "Error al activar usuaria ❌");
        }
      } catch (error) {
        console.error("❌ [Activar] Error de conexión:", error);
        alert("Error de conexión con el servidor ❌");
      }
    });
  });

  // 👉 Buscador
  const buscador = document.getElementById("buscador");
  buscador?.addEventListener("keyup", () => {
    const filtro = buscador.value.toLowerCase();
    document.querySelectorAll("#tablaUsuarias tbody tr").forEach((fila) => {
      const texto = fila.textContent.toLowerCase();
      fila.style.display = texto.includes(filtro) ? "" : "none";
    });
  });
});
