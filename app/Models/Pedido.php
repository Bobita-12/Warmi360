<?php
namespace App\Models;

class Pedido
{
    private \PDO $db;
    private string $table = 'pedidos';

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * ➕ Crear nuevo pedido
     */
    public function crearPedido(array $data): array
    {
        try {
            $sql = "INSERT INTO {$this->table}
                    (id_usuario, total, metodo_pago, id_estado, fecha)
                    VALUES (:id_usuario, :total, :metodo_pago, :id_estado, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $data['id_usuario'] ?? null,
                ':total' => $data['total'],
                ':metodo_pago' => $data['metodo_pago'] ?? 'tarjeta',
                ':id_estado' => $data['id_estado'] ?? 3 // 3 = pendiente
            ]);

            $id_pedido = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Pedido registrado correctamente.',
                'id_pedido' => $id_pedido
            ];

        } catch (\PDOException $e) {
            $this->logError("Error SQL al crear pedido: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al crear pedido: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 📋 Obtener todos los pedidos (con nombre de usuaria)
     */
    public function obtenerPedidos(): array
    {
        try {
            $sql = "SELECT 
                        p.id_pedido,
                        CONCAT(u.nombres, ' ', u.apellidos) AS usuaria,
                        p.total,
                        p.metodo_pago,
                        p.id_estado,
                        p.fecha
                    FROM {$this->table} p
                    LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                    ORDER BY p.fecha DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $this->logError("Error al obtener pedidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 🔎 Buscar pedido por ID
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_pedido = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            $this->logError("Error al buscar pedido por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✏️ Actualizar pedido (actualización parcial)
     */
    public function editarPedido(int $id, array $data): array
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

            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id_pedido = :id";
            $stmt = $this->db->prepare($sql);

            $data['id'] = $id;
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Pedido actualizado correctamente.'];

        } catch (\PDOException $e) {
            $this->logError("Error SQL al editar pedido: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al editar pedido: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 📦 Obtener detalles del pedido
     */
    public function obtenerDetalles(int $id_pedido): array
    {
        try {
            $sql = "SELECT 
                        d.id_detalle,
                        d.id_producto,
                        p.nombre AS producto,
                        d.cantidad,
                        d.precio_unitario
                    FROM pedido_detalles d
                    LEFT JOIN productos p ON d.id_producto = p.id_producto
                    WHERE d.id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pedido]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $this->logError("Error al obtener detalles del pedido: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ Cambiar estado de pedido
     */
    public function cambiarEstado(int $id_pedido, int $nuevoEstado): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET id_estado = :estado WHERE id_pedido = :id");
            return $stmt->execute([':estado' => $nuevoEstado, ':id' => $id_pedido]);
        } catch (\Exception $e) {
            $this->logError("Error al cambiar estado del pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📊 Contar pedidos totales
     */
    public function contarPedidos(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (\Exception $e) {
            $this->logError("Error al contar pedidos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 💾 Exportar pedidos a CSV
     */
    public function exportarCSV(): void
    {
        try {
            $pedidos = $this->obtenerPedidos();
            if (empty($pedidos)) {
                echo "No hay pedidos para exportar.";
                return;
            }

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=pedidos.csv');

            $salida = fopen('php://output', 'w');
            fputcsv($salida, ['ID', 'Usuaria', 'Total', 'Método Pago', 'Estado', 'Fecha']);

            foreach ($pedidos as $p) {
                fputcsv($salida, [
                    $p['id_pedido'],
                    $p['usuaria'] ?? 'No asignada',
                    $p['total'],
                    ucfirst($p['metodo_pago']),
                    $p['id_estado'],
                    $p['fecha']
                ]);
            }

            fclose($salida);
            exit;

        } catch (\Exception $e) {
            $this->logError("Error al exportar pedidos CSV: " . $e->getMessage());
            echo "Error al generar CSV.";
        }
    }

    /**
     * 🧾 Registrar errores
     */
    private function logError(string $message): void
    {
        $logPath = __DIR__ . '/../../logs/pedido_model_error.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
