<?php
namespace App\Models;

class Producto
{
    private \PDO $db;
    private string $table = 'productos';

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * ➕ Crear nuevo producto
     */
    public function crearProducto(array $data): array
    {
        try {
            // Evita duplicados por nombre
            $stmt = $this->db->prepare("SELECT id_producto FROM {$this->table} WHERE nombre = ?");
            $stmt->execute([$data['nombre']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'El producto ya existe.'];
            }

            $sql = "INSERT INTO {$this->table}
                    (nombre, descripcion, precio, imagen, stock, id_categoria, id_estado, fecha_creacion)
                    VALUES (:nombre, :descripcion, :precio, :imagen, :stock, :id_categoria, :id_estado, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
                ':precio' => $data['precio'],
                ':imagen' => $data['imagen'] ?? null,
                ':stock' => $data['stock'] ?? 0,
                ':id_categoria' => $data['id_categoria'] ?? null,
                ':id_estado' => $data['id_estado'] ?? 1 // 1 = activo
            ]);

            return ['success' => true, 'message' => 'Producto registrado correctamente.'];

        } catch (\PDOException $e) {
            $this->logError("Error SQL al crear producto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos.'];
        } catch (\Exception $e) {
            $this->logError("Error general al crear producto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general.'];
        }
    }

    /**
     * 📋 Obtener todos los productos (con nombre de categoría)
     */
    public function obtenerProductos(): array
    {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.nombre,
                        p.descripcion,
                        p.precio,
                        p.imagen,
                        p.stock,
                        c.nombre AS categoria,
                        p.id_estado,
                        p.fecha_creacion
                    FROM {$this->table} p
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                    ORDER BY p.fecha_creacion DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $this->logError("Error al obtener productos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 🔎 Buscar producto por ID
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_producto = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            $this->logError("Error al buscar producto por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✏️ Editar producto (actualización parcial)
     */
    public function editarProducto(int $id, array $data): array
    {
        try {
            $data = array_filter($data, fn($v) => $v !== null && $v !== '');

            if (empty($data)) {
                return ['success' => false, 'message' => 'No hay datos para actualizar.'];
            }

            $campos = [];
            foreach ($data as $key => $value) {
                $campos[] = "{$key} = :{$key}";
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id_producto = :id";
            $stmt = $this->db->prepare($sql);

            $data['id'] = $id;
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Producto actualizado correctamente.'];

        } catch (\PDOException $e) {
            $this->logError("Error SQL al editar producto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al editar producto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 🚫 Desactivar producto
     */
    public function desactivarProducto(int $id): bool
    {
        return $this->editarProducto($id, ['id_estado' => 2])['success'];
    }

    /**
     * ✅ Activar producto
     */
    public function activarProducto(int $id): bool
    {
        return $this->editarProducto($id, ['id_estado' => 1])['success'];
    }

    /**
     * 📦 Actualizar stock
     */
    public function actualizarStock(int $id, int $cantidad): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET stock = :cantidad WHERE id_producto = :id");
            return $stmt->execute([':cantidad' => $cantidad, ':id' => $id]);
        } catch (\Exception $e) {
            $this->logError("Error al actualizar stock: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📊 Contar productos activos
     */
    public function contarProductosActivos(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id_estado = 1";
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (\Exception $e) {
            $this->logError("Error al contar productos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 💾 Exportar productos a CSV
     */
    public function exportarCSV(): void
    {
        try {
            $productos = $this->obtenerProductos();
            if (empty($productos)) {
                echo "No hay productos para exportar.";
                return;
            }

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=productos.csv');

            $salida = fopen('php://output', 'w');
            fputcsv($salida, ['ID', 'Nombre', 'Descripción', 'Precio', 'Stock', 'Categoría', 'Estado', 'Fecha creación']);

            foreach ($productos as $p) {
                fputcsv($salida, [
                    $p['id_producto'],
                    $p['nombre'],
                    $p['descripcion'],
                    $p['precio'],
                    $p['stock'],
                    $p['categoria'] ?? 'Sin categoría',
                    $p['id_estado'] == 1 ? 'Activo' : 'Inactivo',
                    $p['fecha_creacion']
                ]);
            }

            fclose($salida);
            exit;

        } catch (\Exception $e) {
            $this->logError("Error al exportar productos CSV: " . $e->getMessage());
            echo "Error al generar CSV.";
        }
    }

    /**
     * 🧾 Registrar errores
     */
    private function logError(string $message): void
    {
        $logPath = __DIR__ . '/../../logs/producto_model_error.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
