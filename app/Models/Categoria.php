<?php
namespace App\Models;

class Categoria
{
    private \PDO $db;
    private string $table = 'categorias';

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * ➕ Crear nueva categoría
     * Retorna: ['success' => bool, 'message' => string, 'id_categoria' => int|null]
     */
    public function crearCategoria(array $data): array
    {
        try {
            // Evita duplicados
            $stmt = $this->db->prepare("SELECT id_categoria FROM {$this->table} WHERE nombre = ?");
            $stmt->execute([$data['nombre']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'El nombre de la categoría ya existe.'];
            }

            // Inserta nueva categoría
            $sql = "INSERT INTO {$this->table} (nombre, descripcion, fecha_creacion)
                    VALUES (:nombre, :descripcion, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null
            ]);

            // ✅ Retornar el ID insertado
            $id = (int)$this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Categoría creada correctamente.',
                'id_categoria' => $id
            ];
        } catch (\PDOException $e) {
            $this->logError("PDOException al crear categoría: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al crear categoría: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 📋 Obtener todas las categorías
     */
    public function obtenerCategorias(): array
    {
        try {
            $sql = "SELECT id_categoria, nombre, descripcion, fecha_creacion
                    FROM {$this->table}
                    ORDER BY fecha_creacion DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->logError("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✏️ Editar categoría
     */
    public function editarCategoria(int $id, array $data): array
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

            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id_categoria = :id";
            $stmt = $this->db->prepare($sql);

            $data['id'] = $id;
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Categoría actualizada correctamente.'];
        } catch (\PDOException $e) {
            $this->logError("PDOException al editar categoría (ID {$id}): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al editar categoría (ID {$id}): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 🔎 Buscar categoría por ID
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_categoria = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            $this->logError("Error al buscar categoría por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ❌ Eliminar categoría
     */
    public function eliminarCategoria(int $id): array
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id_categoria = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Categoría eliminada correctamente.'];
        } catch (\PDOException $e) {
            $this->logError("Error SQL al eliminar categoría: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al eliminar categoría: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 📦 Contar categorías totales
     */
    public function contarCategorias(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            return (int)$this->db->query($sql)->fetchColumn();
        } catch (\Exception $e) {
            $this->logError("Error al contar categorías: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 🧾 Registrar errores
     */
    private function logError(string $message): void
    {
        $logPath = __DIR__ . '/../../logs/categoria_model_error.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
