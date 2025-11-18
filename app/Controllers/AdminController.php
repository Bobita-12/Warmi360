<?php
namespace App\Controllers;

use PDO;
use Exception;
use App\Controllers\UsuariasController;
use App\Controllers\ProductosController;
use App\Models\User;

class AdminController
{
    private PDO $db;
    private string $base_url;

    public function __construct(PDO $db, string $base_url = '/Warmi360-Refactor/public')
    {
        $this->db = $db;
        $this->base_url = $base_url;
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // ======================================================
    // ✅ Verificación de acceso
    // ======================================================
    private function verificarAcceso(): void
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
            header("Location: {$this->base_url}/?view=login");
            exit;
        }
    }

    // ======================================================
    // ✅ Dashboard principal con datos reales + simulados
    // ======================================================
    public function dashboard(): void
    {
        $this->verificarAcceso();

        try {
            $userModel = new User($this->db);

            // 🔹 Datos reales
            $totalUsuarias  = $userModel->contarUsuarias();
            $ultimaUsuaria  = $userModel->ultimaUsuaria();
            $nuevasUsuarias = $userModel->nuevasUsuariasSemana();

            // 🔹 Datos simulados (por ahora)
            $promocionesActivas = 3;
            $ultimosPedidos = [
                ['id' => 101, 'cliente' => 'María López', 'total' => 125.50, 'fecha' => '2025-11-04'],
                ['id' => 102, 'cliente' => 'Juana Pérez', 'total' => 89.90, 'fecha' => '2025-11-05'],
                ['id' => 103, 'cliente' => 'Lucía Fernández', 'total' => 140.00, 'fecha' => '2025-11-06'],
            ];

            $planesActivos = 0;
            $ingresosMes   = 0;
        } catch (Exception $e) {
            $this->logError("Error en dashboard(): " . $e->getMessage());
            $totalUsuarias = 0;
            $planesActivos = 0;
            $ingresosMes   = 0;
            $ultimaUsuaria = null;
            $nuevasUsuarias = [];
            $promocionesActivas = 0;
            $ultimosPedidos = [];
        }

        include __DIR__ . '/../Views/admin/partials/inicio.php';
    }

    // ======================================================
    // ✅ Carga dinámica de secciones
    // ======================================================
    public function loadSection(string $section = ''): void
    {
        $this->verificarAcceso();
        $safe = basename($section);

        try {
            switch ($safe) {
                case 'inicio':
                    $this->dashboard();
                    break;

                case 'usuarias':
                    $controller = new UsuariasController($this->db, $this->base_url);
                    $controller->index();
                    break;

                case 'productos':
                    // ✅ Nuevo: delega todo al controlador de productos
                    $productosController = new ProductosController($this->db, $this->base_url);
                    $productosController->index();
                    break;

                case 'planes':
                case 'eventos':
                case 'biblioteca':
                    $this->cargarSeccionEstatico($safe);
                    break;

                default:
                    echo "<div class='p-6 text-center text-gray-500'>
                            ⚠️ Sección no válida o no disponible.
                          </div>";
                    break;
            }
        } catch (Exception $e) {
            $this->logError("Error al cargar sección '{$safe}': " . $e->getMessage());
            echo "<div class='p-6 text-center text-red-600'>
                    ⚠️ Error interno al cargar la sección: " . htmlspecialchars($e->getMessage()) . "
                  </div>";
        }
    }

    // ======================================================
    // 📂 Cargar secciones estáticas (planes, eventos, biblioteca)
    // ======================================================
    private function cargarSeccionEstatico(string $nombre): void
    {
        $ruta = __DIR__ . "/../Views/admin/partials/{$nombre}.php";
        if (file_exists($ruta)) {
            include $ruta;
        } else {
            echo "<div class='p-6 text-center text-red-600'>
                    ⚠️ Sección no encontrada: " . htmlspecialchars($nombre) . "
                  </div>";
        }
    }

    // ======================================================
    // 🪵 Logger de errores
    // ======================================================
    private function logError(string $msg): void
    {
        $logPath = __DIR__ . '/../../logs/admin_controller.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
