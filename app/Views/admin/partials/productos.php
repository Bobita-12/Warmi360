<?php
// ======================================================
// 🧩 Parcial: Gestión de Productos
// ======================================================

// Aseguramos que $productos y $categorias estén definidos
$productos = $productos ?? [];
$categorias = $categorias ?? [];
?>

<div class="p-6">
  <!-- 🔹 Título principal -->
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-semibold text-gray-800">Gestión de Artículos (Tienda)</h2>
    <button id="btnNuevoProducto"
      class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg shadow-md transition">
      + Nuevo Artículo
    </button>
  </div>

  <!-- 📊 Estadísticas -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4 text-center">
      <h3 class="text-gray-500 text-sm">TOTAL PRODUCTOS</h3>
      <p class="text-2xl font-bold text-purple-700"><?= count($productos) ?></p>
    </div>

    <div class="bg-white rounded-xl shadow p-4 text-center">
      <h3 class="text-gray-500 text-sm">CATEGORÍAS</h3>
      <p class="text-xl font-bold text-gray-800"><?= count($categorias) ?></p>
    </div>

    <div class="bg-white rounded-xl shadow p-4 text-center">
      <h3 class="text-gray-500 text-sm">BAJO STOCK</h3>
      <?php $bajoStock = array_filter($productos, fn($p) => isset($p['stock']) && $p['stock'] <= 10); ?>
      <p class="text-xl font-bold text-red-500">
        <?= count($bajoStock) ?> producto<?= count($bajoStock) !== 1 ? 's' : '' ?>
      </p>
    </div>
  </div>

  <!-- 🛍️ Inventario de Artículos -->
  <section class="bg-white rounded-2xl shadow p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Inventario de Artículos</h3>

    <div id="listaProductos" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php if (!empty($productos)): ?>
        <?php foreach ($productos as $producto): ?>
          <div class="bg-purple-50 rounded-xl p-4 shadow-sm">
            <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($producto['nombre'] ?? 'Sin nombre') ?></h4>
            <p class="text-sm text-gray-600">Stock: <?= htmlspecialchars($producto['stock'] ?? 0) ?></p>
            <p class="font-medium text-purple-700">S/ <?= number_format($producto['precio'] ?? 0, 2) ?></p>
            <div class="flex gap-2 mt-3">
              <button
                class="bg-purple-600 hover:bg-purple-700 text-white p-2 rounded-lg text-sm btnEditarProducto"
                data-id="<?= $producto['id_producto'] ?? '' ?>">
                <i class="fas fa-edit"></i>
              </button>
              <button
                class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg text-sm btnEliminarProducto"
                data-id="<?= $producto['id_producto'] ?? '' ?>">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-gray-500 text-center">No hay productos registrados.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- 🧾 Gestión de Promociones y Categorías -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- 💸 Promociones (simulado) -->
    <section class="bg-white rounded-2xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Gestión de Promociones</h3>

      <div class="bg-purple-50 rounded-xl p-4 flex justify-between items-center mb-3">
        <div>
          <p class="font-semibold">WARM10</p>
          <p class="text-sm text-gray-600">10% de descuento</p>
        </div>
        <div class="flex gap-2">
          <button class="bg-purple-600 hover:bg-purple-700 text-white p-2 rounded-lg text-sm">
            <i class="fas fa-edit"></i>
          </button>
          <button class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg text-sm">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </div>

      <button class="w-full bg-purple-200 hover:bg-purple-300 text-purple-800 font-semibold py-2 rounded-lg mt-3">
        + Nuevo Código
      </button>
    </section>

    <!-- 🗂️ Categorías -->
    <section class="bg-white rounded-2xl shadow p-6">
      <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Gestión de Categorías</h3>

      <div id="listaCategorias" class="grid grid-cols-2 gap-3">
        <?php if (!empty($categorias)): ?>
          <?php foreach ($categorias as $cat): ?>
            <div class="bg-purple-50 rounded-xl p-3 flex justify-between items-center">
              <div>
                <span class="font-semibold text-gray-700 block"><?= htmlspecialchars($cat['nombre'] ?? 'Sin nombre') ?></span>
                <?php if (!empty($cat['descripcion'])): ?>
                  <span class="text-xs text-gray-500"><?= htmlspecialchars($cat['descripcion']) ?></span>
                <?php endif; ?>
              </div>
              <button
                class="bg-red-500 hover:bg-red-600 text-white p-1.5 rounded-lg text-sm btnEliminarCategoria"
                data-id="<?= $cat['id_categoria'] ?? '' ?>">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-gray-500 text-center">No hay categorías registradas.</p>
        <?php endif; ?>
      </div>

      <button id="btnNuevaCategoria"
        class="w-full bg-purple-200 hover:bg-purple-300 text-purple-800 font-semibold py-2 rounded-lg mt-3">
        + Nueva Categoría
      </button>
    </section>
  </div>

  <!-- 📦 Últimos pedidos (simulado) -->
  <section class="bg-white rounded-2xl shadow p-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Últimos Pedidos</h3>
    <div class="flex justify-between items-center bg-purple-50 rounded-xl p-4">
      <div>
        <p class="font-semibold text-gray-700">Pedido #00124</p>
        <p class="text-sm text-gray-600">Ana García (1x Collar)</p>
      </div>
      <span class="text-green-600 font-medium">Pagado</span>
    </div>
  </section>
</div>

<!-- 🪟 Modal Nuevo Producto -->
<div id="modalProducto" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 animate-fade-in">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Nuevo Producto</h3>
    <form id="formProducto" class="space-y-4" enctype="multipart/form-data">
      <input type="text" name="nombre" placeholder="Nombre del producto" class="w-full border rounded-lg p-2" required>
      <input type="number" name="precio" placeholder="Precio (S/)" class="w-full border rounded-lg p-2" step="0.01" required>
      <input type="number" name="stock" placeholder="Stock" class="w-full border rounded-lg p-2" required>

      <select name="id_categoria" class="w-full border rounded-lg p-2" required>
        <option value="">Seleccionar categoría</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= htmlspecialchars($cat['id_categoria']) ?>">
            <?= htmlspecialchars($cat['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <input type="file" name="imagen" class="w-full border rounded-lg p-2">

      <div class="flex justify-end gap-3 pt-3">
        <button type="button" id="cerrarModalProducto"
          class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">Cancelar</button>
        <button type="submit"
          class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- 🪟 Modal Nueva Categoría -->
<div id="modalCategoria" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 animate-fade-in">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Nueva Categoría</h3>
    <form id="formCategoria" class="space-y-4">
      <input type="text" name="nombre" placeholder="Nombre de la categoría"
        class="w-full border rounded-lg p-2" required>
      <textarea name="descripcion" placeholder="Descripción (opcional)"
        class="w-full border rounded-lg p-2" rows="3"></textarea>

      <div class="flex justify-end gap-3 pt-3">
        <button type="button" id="cerrarModalCategoria"
          class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
          Cancelar
        </button>
        <button type="submit"
          class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
          Guardar
        </button>
      </div>
    </form>
  </div>
</div>
