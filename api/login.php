<?php
/**
 * API - Login de usuario
 */

require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email y contraseña son obligatorios']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $usuario = new Usuario($db);
    $result = $usuario->login($email, $password);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'redirect' => BASE_URL . '/index.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }

} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el login'
    ]);
}
?>
