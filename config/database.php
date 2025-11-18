<?php
class Database {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo === null) {

            $envPath = __DIR__ . '/../.env';

            // ✅ Verificar que el archivo exista
            if (!file_exists($envPath)) {
                self::logError('Archivo .env no encontrado en: ' . $envPath);
                die("<h2 style='color:red;text-align:center;'>❌ No se encontró el archivo .env</h2>");
            }

            // ✅ Leer contenido crudo del archivo
            $envRaw = file_get_contents($envPath);
            if (trim($envRaw) === '') {
                self::logError('.env vacío o ilegible');
                die("<h2 style='color:red;text-align:center;'>❌ El archivo .env está vacío o ilegible</h2>");
            }

            // ✅ Intentar parsear
            $env = @parse_ini_string($envRaw);
            if ($env === false) {
                self::logError('Error al parsear .env, revisa el formato.');
                die("<h2 style='color:red;text-align:center;'>⚠️ Error al leer .env — revisa el formato</h2>");
            }

            // ✅ Leer variables
            $host = $env['DB_HOST'] ?? null;
            $port = $env['DB_PORT'] ?? '3306';
            $dbname = $env['DB_NAME'] ?? null;
            $user = $env['DB_USER'] ?? null;
            $pass = $env['DB_PASS'] ?? '';

            if (!$host || !$dbname || !$user) {
                self::logError('Faltan variables obligatorias en .env');
                die("<h2 style='color:red;text-align:center;'>⚠️ Faltan variables DB_ en el archivo .env</h2>");
            }

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                $msg = "❌ Error de conexión MySQL: " . $e->getMessage();
                self::logError($msg);
                die("<h2 style='color:red;text-align:center;'>$msg</h2>");
            }
        }

        return self::$pdo;
    }

    private static function logError($msg) {
        $logDir = __DIR__ . '/../app/logs';
        if (!is_dir($logDir)) mkdir($logDir, 0777, true);
        $logFile = $logDir . '/db_error.log';
        $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
        file_put_contents($logFile, $line, FILE_APPEND);
    }
}
