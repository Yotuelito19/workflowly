<?php
/**
 * Configuración general de la aplicación WorkFlowly
 */

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores (solo en desarrollo)
define('ENVIRONMENT', 'development'); // cambiar a 'production' en producción

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Rutas del proyecto
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');

// URLs base
define('BASE_URL', 'http://localhost/workflowly');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Configuración de seguridad
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12);

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
ini_set('session.use_strict_mode', 1);

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Tiempo de expiración de sesión (30 minutos)
define('SESSION_TIMEOUT', 1800);

// Configuración de subida de archivos
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);

// Incluir autoload de clases
spl_autoload_register(function ($class_name) {
    $directories = [
        BASE_PATH . '/models/',
        BASE_PATH . '/controllers/',
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

/**
 * Función auxiliar para sanitizar entradas
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Función para verificar si el usuario está logueado
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Función para verificar si el usuario es organizador
 */
function is_organizer() {
    return is_logged_in() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Organizador';
}
/**
 * Función para verificar si el usuario es administrador
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin';
}


/**
 * Función para redirigir
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Función para generar código QR único
 */
function generate_qr_code() {
    return 'QR' . strtoupper(uniqid() . bin2hex(random_bytes(4)));
}

/**
 * Función para generar código de barras único
 */
function generate_barcode() {
    return 'BC' . date('Ymd') . strtoupper(bin2hex(random_bytes(6)));
}

/**
 * Función para formatear precio
 */
function format_price($price) {
    return number_format($price, 2, ',', '.') . ' €';
}

/**
 * Función para formatear fecha
 */
function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// ===================================================================
// === Helpers JSON globales y manejador de errores fatal-only ====
// ===================================================================

// Evitar doble instalación
if (!defined('JSON_HELPERS_INSTALLED')) {
    define('JSON_HELPERS_INSTALLED', true);

    /**
     * Devuelve respuesta JSON de éxito y termina ejecución
     */
    function json_success(array $payload, int $status = 200): void {
        if (ob_get_length()) ob_end_clean();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Devuelve respuesta JSON de error y termina ejecución
     */
    function json_error(string $msg, int $status = 400, array $extra = []): void {
        if (ob_get_length()) ob_end_clean();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => $msg] + $extra, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Manejador global para errores fatales (no interfiere con respuestas válidas)
     */
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    register_shutdown_function(function () {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            if (ob_get_length()) ob_end_clean();
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'error' => 'Fatal error',
                'detail' => $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            if (ob_get_length()) ob_end_flush();
        }
    });
}

