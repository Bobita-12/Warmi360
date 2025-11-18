<?php
namespace App\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Exception;

class ProductosController
{
    public \PDO $db;
    public string $base_url;
    public Producto $productoModel;
    public Categoria $categoriaModel;

    public function __construct(\PDO $db, string $base_url)
    {
        $this->db = $db;
        $this->base_url = $base_url;
        $this->productoModel = new Producto($db);
        $this->categoriaModel = new Categoria($db);
    }

    // ======================================================
    // 📋 Vista principal de productos
    // ======================================================
    public function index(): void
    {
        try {
            $productos  = $this->productoModel->obtenerProductos();
            $categorias = $this->categoriaModel->obtenerCategorias();

            include __DIR__ . '/../Views/admin/partials/productos.php';
        } catch (Exception $e) {
            $this->logError("Error al cargar productos: " . $e->getMessage());
            echo "<div class='p-6 text-center text-red-600'>
                    ⚠️ Error al cargar los productos.
                  </div>";
        }
    }

    // ======================================================
    // 🧾 Listar productos (AJAX)
    // ======================================================
    public function listar_productos(): void
    {
        $this->jsonHeader();
        try {
            $productos = $this->productoModel->obtenerProductos();
            echo json_encode(['success' => true, 'data' => $productos]);
        } catch (Exception $e) {
            $this->logError("Error al listar productos: " . $e->getMessage());
            $this->jsonResponse(false, 'Error al obtener la lista de productos.');
        }
    }

    // ======================================================
    // ➕ Crear producto
    // ======================================================
    public function crear_producto(): void
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $data = [
                'nombre'       => trim($_POST['nombre'] ?? ''),
                'descripcion'  => trim($_POST['descripcion'] ?? ''),
                'precio'       => (float)($_POST['precio'] ?? 0),
                'stock'        => (int)($_POST['stock'] ?? 0),
                'id_categoria' => (int)($_POST['id_categoria'] ?? 0),
                'id_estado'    => 1,
                'imagen'       => $this->procesarImagen($_FILES['imagen'] ?? null)
            ];

            if (empty($data['nombre']) || $data['precio'] <= 0) {
                $this->jsonResponse(false, 'El nombre y el precio son obligatorios.');
                return;
            }

            $result = $this->productoModel->crearProducto($data);
            $this->jsonResponse($result['success'], $result['message']);
        } catch (Exception $e) {
            $this->logError("Error al crear producto: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno al crear el producto.');
        }
    }

    // ======================================================
    // ✏️ Editar producto
    // ======================================================
    public function editar_producto(): void
    {
        $this->jsonHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido.');
            return;
        }

        try {
            $id = (int)($_POST['id_producto'] ?? 0);
            if ($id <= 0) {
                $this->jsonResponse(false, 'ID de producto inválido.');
                return;
            }

            $data = [
                'nombre'       => trim($_POST['nombre'] ?? ''),
                'descripcion'  => trim($_POST['descripcion'] ?? ''),
                'precio'       => (float)($_POST['precio'] ?? 0),
                'stock'        => (int)($_POST['stock'] ?? 0),
                'id_categoria' => (int)($_POST['id_categoria'] ?? 0),
                'id_estado'    => (int)($_POST['id_estado'] ?? 1),
            ];

            // Si se envía una nueva imagen
            if (!empty($_FILES['imagen']['name'])) {
                $data['imagen'] = $this->procesarImagen($_FILES['imagen']);
            }

            $result = $this->productoModel->editarProducto($id, $data);
            $this->jsonResponse($result['success'], $result['message']);
        } catch (Exception $e) {
            $this->logError("Error al editar producto: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor.');
        }
    }

    // ======================================================
    // 🚫 Desactivar producto
    // ======================================================
    public function desactivar_producto(): void
    {
        $this->jsonHeader();

        try {
            $id = (int)($_POST['id_producto'] ?? 0);
            if ($id <= 0) {
                $this->jsonResponse(false, 'ID de producto no recibido.');
                return;
            }

            $success = $this->productoModel->desactivarProducto($id);
            $this->jsonResponse($success, $success ? 'Producto desactivado correctamente.' : 'No se pudo desactivar el producto.');
        } catch (Exception $e) {
            $this->logError("Error al desactivar producto: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor.');
        }
    }

    // ======================================================
    // ✅ Activar producto
    // ======================================================
    public function activar_producto(): void
    {
        $this->jsonHeader();

        try {
            $id = (int)($_POST['id_producto'] ?? 0);
            if ($id <= 0) {
                $this->jsonResponse(false, 'ID de producto no recibido.');
                return;
            }

            $success = $this->productoModel->activarProducto($id);
            $this->jsonResponse($success, $success ? 'Producto activado correctamente.' : 'No se pudo activar el producto.');
        } catch (Exception $e) {
            $this->logError("Error al activar producto: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno del servidor.');
        }
    }

    // ======================================================
    // 🗂️ Categorías - Listar y Crear
    // ======================================================
    public function listar_categorias(): void
    {
        $this->jsonHeader();

        try {
            $categorias = $this->categoriaModel->obtenerCategorias();
            $this->jsonResponse(true, 'Categorías obtenidas.', ['data' => $categorias]);
        } catch (Exception $e) {
            $this->logError("Error al listar categorías: " . $e->getMessage());
            $this->jsonResponse(false, 'Error al obtener las categorías.');
        }
    }

    public function crear_categoria(): void
    {
        $this->jsonHeader();

        try {
            $nombre = trim($_POST['nombre'] ?? '');
            if (empty($nombre)) {
                $this->jsonResponse(false, 'El nombre de la categoría es obligatorio.');
                return;
            }
            $result = $this->categoriaModel->crearCategoria(['nombre' => $nombre]);
            $this->jsonResponse($result['success'], $result['message']);
        } catch (Exception $e) {
            $this->logError("Error al crear categoría: " . $e->getMessage());
            $this->jsonResponse(false, 'Error interno al crear la categoría.');
        }
    }

   // ======================================================
// 🖼️ Procesar subida de imagen (corregido)
// ======================================================
private function procesarImagen(?array $file): ?string
{
    if (!$file || empty($file['name'])) {
        return null;
    }

    $uploadDir = __DIR__ . '/../../public/uploads/productos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . uniqid() . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $fileName; // ✅ Solo devolvemos el nombre, no la ruta
    }

    throw new Exception('No se pudo guardar la imagen del producto.');
}


    // ======================================================
    // 🔧 Helpers
    // ======================================================
    private function jsonHeader(): void
    {
        header('Content-Type: application/json; charset=utf-8');
    }

   private function jsonResponse(bool $success, string $message, array $extra = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit; // 🔥 Muy importante
}

    private function logError(string $message): void
    {
        $logPath = __DIR__ . '/../../logs/productos_controller.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
