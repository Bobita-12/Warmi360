<?php
namespace App\Controllers;

use App\Models\User;

class UsuariasController
{
    private \PDO $db;
    private string $base_url;
    private User $userModel;

    public function __construct(\PDO $db, string $base_url)
    {
        $this->db = $db;
        $this->base_url = $base_url;
        $this->userModel = new User($db);
    }

    // ======================================================
    // 📋 Mostrar listado de usuarias (Vista principal)
    // ======================================================
    public function index(): void
    {
        try {
            $totalUsuarias = $this->userModel->contarUsuarias();
            $ultimaUsuaria = $this->userModel->ultimaUsuaria();
            $nuevasSemana  = $this->userModel->nuevasUsuariasSemana();
            $usuarias      = $this->userModel->obtenerUsuarias();

            include __DIR__ . '/../Views/admin/partials/usuarias.php';
        } catch (\Throwable $e) {
            $this->logError("Error al cargar index de usuarias: " . $e->getMessage());
            http_response_code(500);
            echo "<p style='color:red;text-align:center;'>Error al cargar las usuarias.</p>";
        }
    }

    // ======================================================
    // ➕ Crear nueva usuaria (AJAX)
    // ======================================================
    public function crear_usuaria(): void
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            return;
        }

        try {
            $data = [
                'dni'       => trim($_POST['dni'] ?? ''),
                'nombres'   => trim($_POST['nombres'] ?? ''),
                'apellidos' => trim($_POST['apellidos'] ?? ''),
                'correo'    => trim($_POST['correo'] ?? ''),
                'telefono'  => trim($_POST['telefono'] ?? ''),
                'sexo'      => trim($_POST['sexo'] ?? ''),
                'pin_hash'  => password_hash($_POST['pin'] ?? '1234', PASSWORD_BCRYPT),
                'id_role'   => 1,
                'id_estado' => 1
            ];

            // Validación mínima
            if (empty($data['correo']) || empty($data['dni']) || empty($data['nombres'])) {
                $this->jsonResponse(false, 'Campos obligatorios faltantes (DNI, nombre o correo).');
                return;
            }

            $result = $this->userModel->crearUsuaria($data);
            $this->jsonResponse($result['success'], $result['message']);
        } catch (\Throwable $e) {
            $this->logError("Error al crear usuaria: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }

    // ======================================================
    // ✏️ Editar datos de una usuaria (AJAX)
    // ======================================================
    public function editar_usuaria(): void
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            return;
        }

        try {
            $id = (int)($_POST['id_usuario'] ?? 0);
            if ($id <= 0) {
                $this->jsonResponse(false, 'ID de usuaria no recibido');
                return;
            }

            $data = [
                'nombres'   => trim($_POST['nombres'] ?? ''),
                'apellidos' => trim($_POST['apellidos'] ?? ''),
                'correo'    => trim($_POST['correo'] ?? ''),
                'telefono'  => trim($_POST['telefono'] ?? ''),
                'sexo'      => trim($_POST['sexo'] ?? ''),
                'id_estado' => $_POST['id_estado'] ?? null
            ];

            $result = $this->userModel->editarUsuaria($id, $data);
            $this->jsonResponse($result['success'], $result['message']);
        } catch (\Throwable $e) {
            $this->logError("Error al editar usuaria: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }

    // ======================================================
    // 🚫 Desactivar usuaria (AJAX)
    // ======================================================
    public function desactivar_usuaria(): void
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            return;
        }

        try {
            $id = (int)($_POST['id_usuario'] ?? 0);
            if ($id <= 0) {
                $this->jsonResponse(false, 'ID no recibido');
                return;
            }

            $success = $this->userModel->desactivarUsuaria($id);
            $this->jsonResponse($success, $success ? 'Usuaria desactivada correctamente.' : 'No se pudo desactivar la usuaria.');
        } catch (\Throwable $e) {
            $this->logError("Error al desactivar usuaria: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }

    // ======================================================
    // ✅ Activar usuaria (AJAX)
    // ======================================================
    public function activar_usuaria(): void
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            return;
        }

        try {
            $id = (int)($_POST['id_usuario'] ?? 0);
            if ($id <= 0) {
                $this->jsonResponse(false, 'ID no recibido');
                return;
            }

            $success = $this->userModel->activarUsuaria($id);
            $this->jsonResponse($success, $success ? 'Usuaria activada correctamente.' : 'No se pudo activar la usuaria.');
        } catch (\Throwable $e) {
            $this->logError("Error al activar usuaria: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }

    // ======================================================
    // 📤 Exportar CSV
    // ======================================================
    public function exportar_usuarias(): void
    {
        try {
            $this->userModel->exportarCSV();
        } catch (\Throwable $e) {
            $this->logError("Error al exportar CSV: " . $e->getMessage());
            echo "Error al exportar CSV.";
        }
    }

    // ======================================================
    // 🧾 Helpers privados
    // ======================================================
    private function jsonHeader(): void
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    private function jsonResponse(bool $success, string $message, array $extra = []): void
    {
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    }

    private function logError(string $message): void
    {
        $logPath = __DIR__ . '/../../logs/usuarias_controller.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
