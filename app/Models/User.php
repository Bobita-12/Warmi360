<?php
namespace App\Models;

class User
{
    private \PDO $db;
    private string $table = 'usuarios';

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * ➕ Crear nueva usuaria
     */
    public function crearUsuaria(array $data): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id_usuario FROM {$this->table} WHERE correo = ? OR dni = ?");
            $stmt->execute([$data['correo'], $data['dni']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'El correo o DNI ya está registrado.'];
            }

            $sql = "INSERT INTO {$this->table}
                    (dni, nombres, apellidos, correo, telefono, sexo, pin_hash, id_role, id_estado, fecha_registro)
                    VALUES (:dni, :nombres, :apellidos, :correo, :telefono, :sexo, :pin_hash, :id_role, :id_estado, NOW())";

            $stmt = $this->db->prepare($sql);
            $params = [
                ':dni' => $data['dni'],
                ':nombres' => $data['nombres'],
                ':apellidos' => $data['apellidos'],
                ':correo' => $data['correo'],
                ':telefono' => $data['telefono'],
                ':sexo' => $data['sexo'],
                ':pin_hash' => $data['pin_hash'],
                ':id_role' => $data['id_role'] ?? 1,
                ':id_estado' => $data['id_estado'] ?? 1 // 1 = activo
            ];
            $stmt->execute($params);

            return ['success' => true, 'message' => 'Usuaria registrada correctamente.'];

        } catch (\PDOException $e) {
            $this->logError("PDOException al registrar usuaria: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al registrar usuaria: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 📋 Obtener todas las usuarias
     */
    public function obtenerUsuarias(): array
    {
        try {
            $sql = "SELECT 
                        id_usuario, dni, nombres, apellidos, correo, telefono, sexo, id_estado, fecha_registro
                    FROM {$this->table}
                    WHERE id_role = 1
                    ORDER BY fecha_registro DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->logError("Error al obtener usuarias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✏️ Editar usuaria (actualización parcial)
     */
    public function editarUsuaria(int $id, array $data): array
    {
        try {
            // Elimina campos vacíos o nulos
            $data = array_filter($data, fn($v) => $v !== null && $v !== '');

            if (empty($data)) {
                return ['success' => false, 'message' => 'No hay datos para actualizar.'];
            }

            // Construcción dinámica del SQL
            $campos = [];
            foreach ($data as $key => $value) {
                $campos[] = "{$key} = :{$key}";
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $campos) . " WHERE id_usuario = :id";
            $stmt = $this->db->prepare($sql);

            $data['id'] = $id;
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Datos de usuaria actualizados correctamente.'];

        } catch (\PDOException $e) {
            $this->logError("PDOException al editar usuaria (ID {$id}): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()];
        } catch (\Exception $e) {
            $this->logError("Error general al editar usuaria (ID {$id}): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error general: ' . $e->getMessage()];
        }
    }

    /**
     * 💾 Exportar usuarias directamente a CSV (salida)
     */
    public function exportarCSV(): void
    {
        try {
            $usuarias = $this->obtenerUsuarias();
            if (empty($usuarias)) {
                echo "No hay registros de usuarias para exportar.";
                return;
            }

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=usuarias.csv');

            $salida = fopen('php://output', 'w');
            fputcsv($salida, ['ID', 'DNI', 'Nombres', 'Apellidos', 'Correo', 'Teléfono', 'Sexo', 'Estado', 'Fecha Registro']);

            foreach ($usuarias as $u) {
                fputcsv($salida, [
                    $u['id_usuario'],
                    $u['dni'],
                    $u['nombres'],
                    $u['apellidos'],
                    $u['correo'],
                    $u['telefono'],
                    $u['sexo'],
                    $u['id_estado'] == 1 ? 'Activo' : ($u['id_estado'] == 2 ? 'Desactivado' : 'Otro'),
                    $u['fecha_registro']
                ]);
            }

            fclose($salida);
            exit;

        } catch (\Exception $e) {
            $this->logError("Error al exportar CSV: " . $e->getMessage());
            echo "Error al generar el archivo CSV.";
        }
    }

    /**
     * 🔎 Buscar usuaria por correo
     */
    public function findByCorreo(string $correo): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE correo = ?");
            $stmt->execute([$correo]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            $this->logError("Error al buscar usuaria por correo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 🔎 Buscar usuaria por ID
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_usuario = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            $this->logError("Error al buscar usuaria por ID: " . $e->getMessage());
            return null;
        }
    }

    // =============================================================
    // 📊 MÉTODOS PARA DASHBOARD
    // =============================================================

    public function contarUsuarias(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id_role = 1";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    public function ultimaUsuaria(): ?array
    {
        $sql = "SELECT nombres, apellidos, correo, fecha_registro
                FROM {$this->table}
                WHERE id_role = 1
                ORDER BY fecha_registro DESC
                LIMIT 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function nuevasUsuariasSemana(): array
    {
        $sql = "
            SELECT DATE(fecha_registro) AS fecha, COUNT(*) AS cantidad
            FROM {$this->table}
            WHERE id_role = 1
              AND fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(fecha_registro)
            ORDER BY fecha ASC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $r) $map[$r['fecha']] = (int)$r['cantidad'];

        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = ['fecha' => $fecha, 'cantidad' => $map[$fecha] ?? 0];
        }

        return $result;
    }

    /**
     * 🚫 Desactivar usuaria (usa editarUsuaria internamente)
     */
    public function desactivarUsuaria(int $id_usuario): bool
    {
        $resultado = $this->editarUsuaria($id_usuario, ['id_estado' => 2]);
        return $resultado['success'];
    }

    /**
     * ✅ Activar usuaria (usa editarUsuaria internamente)
     */
    public function activarUsuaria(int $id_usuario): bool
    {
        $resultado = $this->editarUsuaria($id_usuario, ['id_estado' => 1]);
        return $resultado['success'];
    }

    /**
     * 🧾 Registrar errores
     */
    private function logError(string $message): void
    {
        $logPath = __DIR__ . '/../../logs/user_model_error.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
