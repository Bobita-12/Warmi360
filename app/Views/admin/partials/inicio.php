<div class="p-6 space-y-6">

  <!-- =================== TARJETAS DE TOTALES =================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Total Usuarias -->
    <div class="bg-gradient-to-br from-[#8d67b9] to-[#6b4b9c] rounded-2xl shadow-md p-6 text-center text-white">
      <h3 class="text-sm uppercase font-semibold opacity-80 mb-2">Total Usuarias</h3>
      <p class="text-3xl font-bold"><?= number_format($totalUsuarias ?? 0) ?></p>
    </div>

    <!-- Planes Activos -->
    <div class="bg-gradient-to-br from-[#d4a5f2] to-[#b689d9] rounded-2xl shadow-md p-6 text-center text-white">
      <h3 class="text-sm uppercase font-semibold opacity-80 mb-2">Planes Activos</h3>
      <p class="text-3xl font-bold"><?= number_format($planesActivos ?? 0) ?></p>
    </div>

    <!-- Ingresos del Mes -->
    <div class="bg-gradient-to-br from-[#9c89b8] to-[#7f68a3] rounded-2xl shadow-md p-6 text-center text-white">
      <h3 class="text-sm uppercase font-semibold opacity-80 mb-2">Ingresos (Mes)</h3>
      <p class="text-3xl font-bold">S/ <?= number_format($ingresosMes ?? 0, 2) ?></p>
    </div>
  </div>

  <!-- =================== ACCIONES + GRAFICO =================== -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Acciones rápidas -->
    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
      <h3 class="text-lg font-semibold text-[#4C3558] mb-4">Acciones Rápidas</h3>
      <div class="space-y-3">
        <a href="<?= $this->base_url ?>/admin/usuarias" class="quick-btn w-full">
          <i class="fa-solid fa-user-plus"></i> Añadir Usuaria
        </a>
        <a href="<?= $this->base_url ?>/admin/biblioteca" class="quick-btn w-full">
          <i class="fa-solid fa-book"></i> Añadir Artículo
        </a>
        <a href="<?= $this->base_url ?>/admin/eventos" class="quick-btn w-full">
          <i class="fa-solid fa-calendar-plus"></i> Añadir Evento
        </a>
      </div>
    </div>

    <!-- Nuevas usuarias -->
    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
      <h3 class="text-lg font-semibold text-[#4C3558] mb-4">Nuevas Usuarias (últimos 7 días)</h3>
      <canvas id="graficoUsuarias" height="120"></canvas>
    </div>
  </div>

  <!-- =================== ALERTAS Y ACTIVIDAD =================== -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Alertas -->
    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
      <h3 class="text-lg font-semibold text-[#4C3558] mb-4">⚠️ Alertas</h3>
      <ul class="space-y-2 text-sm text-gray-600">
        <li>✔️ Todos los sistemas funcionan correctamente.</li>
        <li>📅 Revisa los eventos próximos en el calendario.</li>
        <li>💳 Verifica los pagos recientes en pedidos.</li>
      </ul>
    </div>

    <!-- Actividad reciente -->
    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
      <h3 class="text-lg font-semibold text-[#4C3558] mb-4">🕒 Actividad Reciente</h3>
      <?php if (!empty($ultimaUsuaria)): ?>
        <div class="text-gray-700 leading-relaxed">
          <p><strong>Última usuaria registrada:</strong></p>
          <p><?= htmlspecialchars($ultimaUsuaria['nombres'] . ' ' . $ultimaUsuaria['apellidos']) ?></p>
          <p class="text-sm text-gray-500"><?= htmlspecialchars($ultimaUsuaria['correo']) ?></p>
          <p class="text-sm text-gray-400 mt-1">
            Registrada el <?= date('d/m/Y', strtotime($ultimaUsuaria['fecha_registro'])) ?>
          </p>
        </div>
      <?php else: ?>
        <p class="text-gray-500">No hay registros recientes.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- =================== ESTILOS =================== -->
<style>
  .quick-btn {
    background: linear-gradient(90deg, #8d67b9, #6b4b9c);
    color: white;
    padding: .75rem 1.25rem;
    border-radius: .75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    transition: transform .2s, box-shadow .2s;
  }

  .quick-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(107, 75, 156, 0.3);
  }
</style>

<!-- =================== SCRIPT DE GRÁFICO =================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const ctx = document.getElementById('graficoUsuarias');
  if (!ctx) return;

  const data = <?= json_encode($nuevasUsuarias ?? []) ?>;
  const labels = data.map(item => item.fecha.split('-').reverse().join('/'));
  const valores = data.map(item => item.cantidad);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Usuarias registradas',
        data: valores,
        borderRadius: 8,
        backgroundColor: 'rgba(141,103,185,0.9)',
        hoverBackgroundColor: 'rgba(107,75,156,1)',
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, ticks: { color: '#4C3558' } },
        x: { ticks: { color: '#4C3558' } }
      },
      plugins: {
        legend: { display: false },
        tooltip: { backgroundColor: '#6b4b9c', titleColor: '#fff', bodyColor: '#fff' }
      }
    }
  });
});
</script>
