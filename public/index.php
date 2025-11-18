<?php
// ======================================================
// ✅ AUTOLOAD DE COMPOSER (mantener primero)
// ======================================================
require_once __DIR__ . '/../vendor/autoload.php';

// ======================================================
// ✅ INICIO DE SESIÓN GLOBAL
// ======================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ======================================================
// ✅ CONFIGURACIÓN / BASE DE DATOS
// ======================================================
require_once __DIR__ . '/../config/database.php';

// ======================================================
// ✅ CONTROLADORES (requiere sólo si no usas autoload de namespaces)
// Si tus controladores se cargan vía PSR-4 con composer, puedes quitar estos require_once.
// ======================================================
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/UsuariasController.php';
require_once __DIR__ . '/../app/Controllers/ProductosController.php';

// ======================================================
// ✅ IMPORTS (namespaced classes)
// ======================================================
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\UsuariasController;
use App\Controllers\ProductosController;

// ======================================================
// ✅ CONEXIÓN A LA BASE DE DATOS
// ======================================================
$db = Database::connect();
if (!$db) {
    http_response_code(500);
    die("<h2 style='color:red;text-align:center;margin-top:2rem;'>❌ Error: No se pudo conectar a la base de datos.</h2>");
}

// ======================================================
// ✅ PARÁMETROS DE RUTA (ahora base_url dinámico)
// ======================================================
$view       = $_GET['view']       ?? 'main';
$section    = $_GET['section']    ?? null;
$action     = $_GET['action']     ?? null;

// Detectar protocolo y host para producir base_url correcto en nube
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // ruta relativa al public
$base_url = $protocol . '://' . $host . $scriptPath;

// Si quieres forzar un subdirectorio específico (opcional):
// $base_url .= '/Warmi360-Refactor/public';

// ======================================================
// ✅ RUTAS DE HEADER Y FOOTER (usar rutas absolutas del proyecto)
// ======================================================
$headerMain  = __DIR__ . '/../app/Views/shared/header-main.php';
$headerUser  = __DIR__ . '/../app/Views/shared/usuaria-header.php';
$footer      = __DIR__ . '/../app/Views/shared/footer.php';

// ======================================================
// ⚙️ CONTROLADORES (instancias)
// ======================================================
// Pasamos $db y $base_url si tus controladores lo requieren
$usuariasController  = new UsuariasController($db, $base_url);
$productosController = new ProductosController($db, $base_url);

// ======================================================
// ✅ BLOQUE DE PETICIONES AJAX USUARIAS Y PRODUCTOS
// ======================================================
if ($action) {
    header('Content-Type: application/json');

    // Nota: aquí validas que sólo rol 3 (admin) pueda ejecutar acciones AJAX.
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
        exit;
    }

    switch ($action) {
        // 👩‍🦰 USUARIAS
        case 'crear_usuaria':        $usuariasController->crear_usuaria(); break;
        case 'editar_usuaria':       $usuariasController->editar_usuaria(); break;
        case 'actualizar_usuaria':   $usuariasController->actualizar_usuaria(); break;
        case 'desactivar_usuaria':   $usuariasController->desactivar_usuaria(); break;
        case 'activar_usuaria':      $usuariasController->activar_usuaria(); break;
        case 'exportar_usuarias':    $usuariasController->exportar_usuarias(); break;

        // 🛍️ PRODUCTOS
        case 'listar_productos':     $productosController->listar_productos(); break;
        case 'crear_producto':       $productosController->crear_producto(); break;
        case 'editar_producto':      $productosController->editar_producto(); break;
        case 'activar_producto':     $productosController->activar_producto(); break;
        case 'desactivar_producto':  $productosController->desactivar_producto(); break;

        // 🏷️ CATEGORÍAS
        case 'listar_categorias':    $productosController->listar_categorias(); break;
        case 'crear_categoria':      $productosController->crear_categoria(); break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }
    exit;
}

// ======================================================
// ✅ ENRUTADOR PRINCIPAL
// ======================================================
switch ($view) {
    // 🌸 PÁGINAS PÚBLICAS
    case 'main':
        if (file_exists($headerMain)) include $headerMain;
        include __DIR__ . '/../app/Views/main/index.php';
        if (file_exists($footer)) include $footer;
        break;

    case 'tienda':
    case 'biblioteca':
    case 'eventos':
    case 'descargar':
    case 'politicas':
    case 'buzon':
        if (file_exists($headerMain)) include $headerMain;
        $page = __DIR__ . "/../app/Views/main/{$view}.php";
        if (file_exists($page)) {
            include $page;
        } else {
            echo "<main class='pt-24 text-center text-xl text-text-dark'>Vista no encontrada</main>";
        }
        if (file_exists($footer)) include $footer;
        break;

    // 🔐 AUTENTICACIÓN
    case 'login':
        include __DIR__ . '/../app/Views/auth/login.php';
        break;

    case 'register':
        include __DIR__ . '/../app/Views/auth/register.php';
        break;

    case 'procesar-login':
        (new AuthController())->login();
        break;

    case 'registrar':
        (new AuthController())->registrarUsuaria();
        break;

    case 'validar-dni':
        (new AuthController())->validarDNI();
        break;

    // 👩‍🦰 DASHBOARD USUARIA
    case 'usuaria':
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
            header("Location: $base_url/?view=login");
            exit;
        }
        if (file_exists($headerUser)) include $headerUser;
        include __DIR__ . '/../app/Views/user/dashboard.php';
        if (file_exists($footer)) include $footer;
        break;

    // 🧠 PANEL ADMINISTRATIVO
    case 'admin':
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
            header("Location: $base_url/?view=login");
            exit;
        }

        $controller = new AdminController($db, $base_url);

        ob_start();
        switch ($section) {
            case 'usuarias':
                $usuariasController->index();
                break;
            case 'productos':
                $productosController->index();
                break;
            case 'planes':
            case 'eventos':
            case 'biblioteca':
            case 'inicio':
                $controller->loadSection($section);
                break;
            default:
                $controller->loadSection('inicio');
                break;
        }
        $content = ob_get_clean();

        // Layout admin que usa $content
        include __DIR__ . '/../app/Views/layouts/admin-layout.php';
        break;

    // 🚪 CIERRE DE SESIÓN
    case 'logout':
        session_unset();
        session_destroy();
        header("Location: $base_url/?view=login");
        exit;

    // 🚫 ERROR 404
    default:
        http_response_code(404);
        if (file_exists($headerMain)) include $headerMain;
        echo "<main class='pt-24 text-center text-xl text-text-dark'>
                <h1>404 - Página no encontrada</h1>
              </main>";
        if (file_exists($footer)) include $footer;
        break;
}
