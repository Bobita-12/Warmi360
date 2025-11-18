<?php
namespace App\Models;

class PedidoDetalle
{
    private \PDO $db;
    private string $table = 'pedido_detalles';

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * ➕ Agregar un detalle a un pedido
     */
    public function agregarDetalle(array $data): array
    {
        try {
            $sql = "INSERT INTO {$this->table}
                    (id_pedido, id_producto, cantidad, precio_unitario)
                    VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_pedido' => $data['id_pedido'],
                ':id_producto' => $data['id_producto'],
                ':cantidad' => $data['cantidad'],
                ':precio_unitario' => $data['precio_unitario']
            ]);

            return ['success' => true, 'message' => 'Detalle de pedido agregado correctamente.'];

        } catch (\PDOException $e) {
            $this->logError("Error SQL al agregar detalle: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al agregar detalle: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 📋 Obtener detalles por ID de pedido
     */
    public function obtenerPorPedido(int $id_pedido): array
    {
        try {
            $sql = "SELECT 
                        d.id_detalle,
                        d.id_pedido,
                        d.id_producto,
                        p.nombre AS producto,
                        d.cantidad,
                        d.precio_unitario,
                        (d.cantidad * d.precio_unitario) AS subtotal
                    FROM {$this->table} d
                    LEFT JOIN productos p ON d.id_producto = p.id_producto
                    WHERE d.id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $this->logError("Error al obtener detalles por pedido: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✏️ Editar un detalle (actualización parcial)
     */
    public function editarDetalle(int $id_detalle, array $data): array
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

            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id_detalle = :id";
            $stmt = $this->db->prepare($sql);

            $data['id'] = $id_detalle;
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Detalle actualizado correctamente.'];

        } catch (\PDOException $e) {
            $this->logError("Error SQL al editar detalle: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al editar detalle: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 🗑️ Eliminar un detalle
     */
    public function eliminarDetalle(int $id_detalle): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id_detalle = ?");
            return $stmt->execute([$id_detalle]);
        } catch (\Exception $e) {
            $this->logError("Error al eliminar detalle: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📊 Obtener total de un pedido sumando subtotales
     */
    public function calcularTotalPedido(int $id_pedido): float
    {
        try {
            $sql = "SELECT SUM(cantidad * precio_unitario) AS total FROM {$this->table} WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (float) ($result['total'] ?? 0);
        } catch (\Exception $e) {
            $this->logError("Error al calcular total de pedido: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 🧾 Registrar errores
     */
    private function logError(string $message): void
    {
        $logPath = __DIR__ . '/../../logs/pedido_detalle_model_error.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
