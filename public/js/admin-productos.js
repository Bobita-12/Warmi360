document.addEventListener("DOMContentLoaded", () => {
  // 🧩 Referencias a elementos
  const btnNuevoProducto = document.querySelector("#btnNuevoProducto");
  const modalProducto = document.querySelector("#modalProducto");
  const formProducto = document.querySelector("#formProducto");
  const cerrarModalProducto = document.querySelector("#cerrarModalProducto");

  const btnNuevaCategoria = document.querySelector("#btnNuevaCategoria");
  const modalCategoria = document.querySelector("#modalCategoria");
  const formCategoria = document.querySelector("#formCategoria");
  const cerrarModalCategoria = document.querySelector("#cerrarModalCategoria");

  const listaProductos = document.querySelector("#listaProductos");
  const listaCategorias = document.querySelector("#listaCategorias");

  // 🔗 URL base global
  const baseUrl = window.baseUrl || "http://localhost/Warmi360-Refactor/public";

  // 🔢 Variables globales
  let productosActuales = [];
  let categoriasActuales = [];
  let productoEditando = null; // 🧠 Nuevo: para controlar el modo edición

  // 🧠 Función segura para obtener JSON
  async function fetchJSON(url, options = {}) {
    const response = await fetch(url, options);
    const contentType = response.headers.get("content-type") || "";
    if (contentType.includes("application/json")) {
      return await response.json();
    } else {
      const text = await response.text();
      console.warn("⚠️ Respuesta no JSON:", text);
      throw new Error("Respuesta inesperada del servidor");
    }
  }

  // 🔹 Render dinámico de productos
  function renderProductos(productos = []) {
    if (!listaProductos) return;

    if (productos.length === 0) {
      listaProductos.innerHTML = `<p class="text-gray-500 text-center">No hay productos registrados.</p>`;
      return;
    }

    listaProductos.innerHTML = productos
      .map(
        (p) => `
        <div class="bg-purple-50 rounded-xl p-4 shadow-sm">
          ${
            p.imagen
              ? `<img src="${baseUrl}${p.imagen.startsWith('/') ? p.imagen : '/uploads/productos/' + p.imagen}" 
                  alt="${p.nombre}" 
                  class="w-full h-40 object-cover rounded-lg mb-3">`
              : `<div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-400 rounded-lg mb-3">
                  Sin imagen
                 </div>`
          }
          <h4 class="font-semibold text-gray-800">${p.nombre ?? "Sin nombre"}</h4>
          <p class="text-sm text-gray-600">Stock: ${p.stock ?? 0}</p>
          <p class="font-medium text-purple-700">S/ ${parseFloat(p.precio ?? 0).toFixed(2)}</p>
          <div class="flex gap-2 mt-3">
            <button class="bg-purple-600 hover:bg-purple-700 text-white p-2 rounded-lg text-sm btnEditarProducto" data-id="${p.id_producto ?? ""}">
              <i class="fas fa-edit"></i>
            </button>
            <button class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg text-sm btnEliminarProducto" data-id="${p.id_producto ?? ""}">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>
      `
      )
      .join("");

    // ✅ Reasignar eventos después del render
    asignarEventosProductos();
  }

  // 🔹 Render dinámico de categorías
  function renderCategorias(categorias = []) {
    if (!listaCategorias) return;

    if (categorias.length === 0) {
      listaCategorias.innerHTML = `<p class="text-gray-500 text-center">No hay categorías registradas.</p>`;
      return;
    }

    listaCategorias.innerHTML = categorias
      .map(
        (c) => `
        <div class="bg-purple-50 rounded-xl p-3 flex justify-between items-center">
          <div>
            <span class="font-semibold text-gray-700 block">${c.nombre ?? "Sin nombre"}</span>
            ${c.descripcion ? `<span class="text-xs text-gray-500">${c.descripcion}</span>` : ""}
          </div>
          <button class="bg-red-500 hover:bg-red-600 text-white p-1.5 rounded-lg text-sm btnEliminarCategoria" data-id="${c.id_categoria ?? ""}">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      `
      )
      .join("");
  }

  // 🔹 Actualizar estadísticas dinámicamente
  function actualizarEstadisticas(productos, categorias) {
    const totalProductos = document.querySelector("#totalProductos");
    const totalCategorias = document.querySelector("#totalCategorias");
    const totalBajoStock = document.querySelector("#totalBajoStock");

    if (totalProductos) totalProductos.textContent = productos.length;
    if (totalCategorias) totalCategorias.textContent = categorias.length;

    const bajoStock = productos.filter((p) => Number(p.stock) <= 10).length;
    if (totalBajoStock) {
      totalBajoStock.textContent = `${bajoStock} producto${bajoStock !== 1 ? "s" : ""}`;
    }
  }

  // 🔹 Asignar eventos a botones dinámicos
  function asignarEventosProductos() {
    // ✏️ EDITAR
    document.querySelectorAll(".btnEditarProducto").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        const id = e.currentTarget.dataset.id;
        const producto = productosActuales.find((p) => p.id_producto == id);

        if (!producto) {
          alert("❌ Producto no encontrado.");
          return;
        }

        productoEditando = producto;

        // 🧠 Rellenar campos
        formProducto.querySelector('[name="nombre"]').value = producto.nombre || "";
        formProducto.querySelector('[name="precio"]').value = producto.precio || "";
        formProducto.querySelector('[name="stock"]').value = producto.stock || "";
        formProducto.querySelector('[name="id_categoria"]').value = producto.id_categoria || "";

        const descInput = formProducto.querySelector('[name="descripcion"]');
        if (descInput) descInput.value = producto.descripcion || "";

        modalProducto.classList.remove("hidden");
      });
    });

    // 🗑️ ELIMINAR
    document.querySelectorAll(".btnEliminarProducto").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        const id = e.currentTarget.dataset.id;
        if (confirm("¿Seguro que deseas eliminar este producto?")) {
          const formData = new FormData();
          formData.append("id_producto", id);

          try {
            const res = await fetchJSON(`${baseUrl}/index.php?action=desactivar_producto`, {
              method: "POST",
              body: formData,
            });

            if (res.success) {
              alert("🗑️ Producto eliminado correctamente");
              cargarProductos();
            } else {
              alert("⚠️ No se pudo eliminar el producto");
            }
          } catch (err) {
            console.error("❌ Error eliminando producto:", err);
          }
        }
      });
    });
  }

  // 🔹 Cargar productos
  async function cargarProductos() {
    try {
      const data = await fetchJSON(`${baseUrl}/index.php?action=listar_productos`);
      if (data.success && Array.isArray(data.data)) {
        productosActuales = data.data;
        renderProductos(productosActuales);
        actualizarEstadisticas(productosActuales, categoriasActuales);
      } else {
        console.warn("⚠️ Datos inesperados:", data);
      }
    } catch (err) {
      console.error("❌ Error cargando productos:", err);
    }
  }

  // 🔹 Cargar categorías
  async function cargarCategorias() {
    try {
      const data = await fetchJSON(`${baseUrl}/index.php?action=listar_categorias`);
      if (data.success && Array.isArray(data.data)) {
        categoriasActuales = data.data;
        renderCategorias(categoriasActuales);
        actualizarEstadisticas(productosActuales, categoriasActuales);
      } else {
        console.warn("⚠️ Datos inesperados:", data);
      }
    } catch (err) {
      console.error("❌ Error cargando categorías:", err);
    }
  }

  // 🟣 Crear o editar producto
  formProducto?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(formProducto);

    let action = "crear_producto";
    if (productoEditando) {
      formData.append("id_producto", productoEditando.id_producto);
      action = "editar_producto";
    }

    try {
      const data = await fetchJSON(`${baseUrl}/index.php?action=${action}`, {
        method: "POST",
        body: formData,
      });

      if (data.success) {
        alert(`✅ Producto ${productoEditando ? "actualizado" : "creado"} correctamente`);
        modalProducto.classList.add("hidden");
        formProducto.reset();
        productoEditando = null;
        await cargarProductos();
      } else {
        alert("⚠️ Error al guardar producto");
      }
    } catch (err) {
      console.error("❌ Error al guardar producto:", err);
    }
  });

  // 🟣 Crear categoría
  formCategoria?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(formCategoria);

    try {
      const data = await fetchJSON(`${baseUrl}/index.php?action=crear_categoria`, {
        method: "POST",
        body: formData,
      });

      if (data.success) {
        alert("✅ Categoría creada correctamente");
        modalCategoria.classList.add("hidden");
        formCategoria.reset();
        await cargarCategorias();
      } else {
        alert("⚠️ Error al crear categoría");
      }
    } catch (err) {
      console.error("❌ Error al crear categoría:", err);
    }
  });

  // 🪟 Mostrar/Ocultar modales
  btnNuevoProducto?.addEventListener("click", () => {
    productoEditando = null;
    formProducto.reset();
    modalProducto.classList.remove("hidden");
  });

  cerrarModalProducto?.addEventListener("click", () => {
    modalProducto.classList.add("hidden");
    formProducto.reset();
    productoEditando = null;
  });

  btnNuevaCategoria?.addEventListener("click", () => modalCategoria.classList.remove("hidden"));
  cerrarModalCategoria?.addEventListener("click", () => modalCategoria.classList.add("hidden"));

  // 🚀 Cargar datos iniciales
  cargarProductos();
  cargarCategorias();
});
