<?php
// app/Views/admin/partials/usuarias.php
global $base_url;
?>

<div class="bg-white rounded-3xl shadow-lg w-full p-8">

  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-semibold text-purple-900">Gestión de Usuarias</h1>

    <div class="flex items-center gap-3">
      <button id="btnNuevaUsuaria"
              class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-full shadow-md text-sm font-medium flex items-center gap-2">
        ➕ Nueva Usuaria
      </button>

      <form action="index.php?action=exportar_usuarias" method="POST" class="inline">
        <button type="submit"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full shadow-md text-sm font-medium">
          📄 Exportar CSV
        </button>
      </form>
    </div>
  </div>

  <!-- Buscador -->
  <div class="mb-6">
    <input type="text" id="buscador" placeholder="Buscar por nombre, DNI o correo..."
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400" />
  </div>

  <!-- Tabla -->
  <div class="overflow-x-auto">
    <table class="min-w-full text-left border-collapse" id="tablaUsuarias">
      <thead>
        <tr class="bg-purple-200 text-purple-900 text-sm uppercase">
          <th class="py-3 px-4">#</th>
          <th class="py-3 px-4">DNI</th>
          <th class="py-3 px-4">Nombres y Apellidos</th>
          <th class="py-3 px-4">Correo</th>
          <th class="py-3 px-4">Teléfono</th>
          <th class="py-3 px-4">Sexo</th>
          <th class="py-3 px-4">Estado</th>
          <th class="py-3 px-4 text-center">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200" id="bodyUsuarias">
        <?php if (!empty($usuarias)): ?>
          <?php foreach ($usuarias as $u): ?>
            <tr class="hover:bg-purple-50 filaUsuaria">
              <td class="py-3 px-4 font-semibold text-gray-700"><?= htmlspecialchars($u['id_usuario']) ?></td>
              <td class="py-3 px-4"><?= htmlspecialchars($u['dni']) ?></td>
              <td class="py-3 px-4"><?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?></td>
              <td class="py-3 px-4"><?= htmlspecialchars($u['correo']) ?></td>
              <td class="py-3 px-4"><?= htmlspecialchars($u['telefono']) ?></td>
              <td class="py-3 px-4"><?= htmlspecialchars($u['sexo']) ?></td>
              <td class="py-3 px-4">
                <?php if ((int)$u['id_estado'] === 1): ?>
                  <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">Activo</span>
                <?php else: ?>
                  <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold">Inactivo</span>
                <?php endif; ?>
              </td>
              <td class="py-3 px-4 text-center flex gap-2 justify-center">
                <button type="button"
                        class="bg-purple-200 hover:bg-purple-300 text-purple-800 p-2 rounded-full btnEditarUsuaria"
                        data-usuaria='<?= htmlspecialchars(json_encode($u), ENT_QUOTES, "UTF-8") ?>'>
                  ✏️
                </button>

                <?php if ((int)$u['id_estado'] === 1): ?>
                  <button type="button"
                          class="bg-red-200 hover:bg-red-300 text-red-800 p-2 rounded-full btnDesactivarUsuaria"
                          data-id="<?= htmlspecialchars($u['id_usuario']) ?>"
                          data-accion="desactivar">🗑️</button>
                <?php else: ?>
                  <button type="button"
                          class="bg-green-200 hover:bg-green-300 text-green-800 p-2 rounded-full btnActivarUsuaria"
                          data-id="<?= htmlspecialchars($u['id_usuario']) ?>"
                          data-accion="activar">✅</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center py-4 text-gray-500">No hay usuarias registradas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Métricas -->
  <div class="grid md:grid-cols-2 gap-6 mt-8">
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
      <h2 class="text-lg font-semibold text-purple-900 mb-3">Métricas de Usuarias</h2>

      <?php
        $total = isset($totalUsuarias) ? (int)$totalUsuarias : count($usuarias ?? []);
        $activas = array_sum(array_map(fn($x) => (int)$x['id_estado'] === 1 ? 1 : 0, $usuarias ?? []));
        $inactivas = max(0, $total - $activas);
        $porcActivas = $total > 0 ? round(($activas / $total) * 100) : 0;
      ?>

      <div class="w-full bg-purple-100 h-3 rounded-full mb-3">
        <div class="bg-purple-500 h-3 rounded-full" style="width: <?= $porcActivas ?>%;"></div>
      </div>
      <p class="text-sm text-gray-600">
        <?= $porcActivas ?>% Activas (<?= $activas ?>) vs <?= 100 - $porcActivas ?>% Inactivas (<?= $inactivas ?>)
      </p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-center items-center">
      <?php if (!empty($ultimaUsuaria)): ?>
        <div class="text-center">
          <p class="text-sm text-gray-500 mb-1"><strong>Última usuaria registrada</strong></p>
          <p class="font-medium"><?= htmlspecialchars($ultimaUsuaria['nombres'] . ' ' . $ultimaUsuaria['apellidos']) ?></p>
          <p class="text-sm text-gray-500"><?= htmlspecialchars($ultimaUsuaria['correo']) ?></p>
          <p class="text-xs text-gray-400 mt-2">
            Registrada el <?= date('d/m/Y', strtotime($ultimaUsuaria['fecha_registro'])) ?>
          </p>
        </div>
      <?php else: ?>
        <p class="text-gray-500">No hay registros recientes.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="modalUsuaria" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-lg">
    <h2 id="tituloModal" class="text-xl font-semibold text-purple-900 mb-4">Registrar Nueva Usuaria</h2>

    <form id="formUsuaria" class="space-y-3">
      <input type="hidden" name="id_usuario" id="id_usuario">

      <label class="block text-sm font-semibold text-gray-700">DNI</label>
      <input name="dni" id="dni" placeholder="DNI" class="w-full border p-2 rounded-lg">

      <label class="block text-sm font-semibold text-gray-700">Nombres</label>
      <input name="nombres" id="nombres" placeholder="Nombres" class="w-full border p-2 rounded-lg">

      <label class="block text-sm font-semibold text-gray-700">Apellidos</label>
      <input name="apellidos" id="apellidos" placeholder="Apellidos" class="w-full border p-2 rounded-lg">

      <label class="block text-sm font-semibold text-gray-700">Correo</label>
      <input name="correo" id="correo" type="email" placeholder="Correo" class="w-full border p-2 rounded-lg">

      <label class="block text-sm font-semibold text-gray-700">Teléfono</label>
      <input name="telefono" id="telefono" placeholder="Teléfono" class="w-full border p-2 rounded-lg">

      <label class="block text-sm font-semibold text-gray-700">Sexo</label>
      <select name="sexo" id="sexo" class="w-full border p-2 rounded-lg">
        <option value="Femenino">Femenino</option>
        <option value="Masculino">Masculino</option>
        <option value="Otro">Otro</option>
      </select>

      <label class="block text-sm font-semibold text-gray-700">PIN de acceso</label>
      <input name="pin" id="pin" type="password" placeholder="PIN de acceso" class="w-full border p-2 rounded-lg">

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" id="cancelarModal" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Cancelar</button>
        <button type="submit" id="guardarUsuaria" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Alertas -->
<div id="alerta" class="fixed top-5 right-5 hidden px-4 py-3 rounded-lg shadow-lg text-white font-medium"></div>
