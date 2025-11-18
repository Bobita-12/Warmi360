<section class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-[#5B4B8A]">🛍️ Gestión de Productos</h1>
        <button id="btnNuevoProducto" class="bg-[#9F7AEA] hover:bg-[#805AD5] text-white px-4 py-2 rounded-lg transition">
            + Nuevo producto
        </button>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <table class="min-w-full border-collapse">
            <thead class="bg-[#E9D8FD] text-[#4C3A78]">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Categoría</th>
                    <th class="px-4 py-2 text-left">Precio</th>
                    <th class="px-4 py-2 text-left">Stock</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaProductos" class="text-sm text-gray-700">
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $p): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2"><?= $p['id_producto'] ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($p['nombre']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></td>
                            <td class="px-4 py-2">S/<?= number_format($p['precio'], 2) ?></td>
                            <td class="px-4 py-2"><?= $p['stock'] ?></td>
                            <td class="px-4 py-2">
                                <?= $p['id_estado'] == 1
                                    ? "<span class='px-2 py-1 rounded bg-green-100 text-green-700 text-xs'>Activo</span>"
                                    : "<span class='px-2 py-1 rounded bg-red-100 text-red-700 text-xs'>Inactivo</span>" ?>
                            </td>
                            <td class="px-4 py-2 flex justify-center gap-2">
                                <button class="editarProducto text-blue-500 hover:text-blue-700" data-id="<?= $p['id_producto'] ?>">✏️</button>
                                <button class="eliminarProducto text-red-500 hover:text-red-700" data-id="<?= $p['id_producto'] ?>">🗑️</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-4 text-gray-400">No hay productos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- 🧩 MODAL -->
<div id="modalProducto" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 relative">
        <h2 id="tituloModal" class="text-xl font-semibold mb-4 text-[#5B4B8A]">Nuevo producto</h2>
        <form id="formProducto" class="space-y-3">
            <input type="hidden" name="id_producto" id="id_producto">

            <div>
                <label class="block text-sm font-medium text-gray-600">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="w-full border-gray-300 rounded-lg p-2 focus:ring-[#9F7AEA]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="w-full border-gray-300 rounded-lg p-2"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600">Precio (S/)</label>
                    <input type="number" step="0.01" name="precio" id="precio" class="w-full border-gray-300 rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Stock</label>
                    <input type="number" name="stock" id="stock" class="w-full border-gray-300 rounded-lg p-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600">Categoría</label>
                <select name="id_categoria" id="id_categoria" class="w-full border-gray-300 rounded-lg p-2">
                    <option value="">Seleccione una categoría</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" id="cancelarModal" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#9F7AEA] hover:bg-[#805AD5] text-white">Guardar</button>
            </div>
        </form>
    </div>
</div>
