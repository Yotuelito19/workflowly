<?php
/**
 * API - Registro de usuario
 */

require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$nombre = isset($_POST['nombre']) ? sanitize_input($_POST['nombre']) : '';
$apellidos = isset($_POST['apellidos']) ? sanitize_input($_POST['apellidos']) : '';
$email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? sanitize_input($_POST['telefono']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
$tipoUsuario = isset($_POST['tipoUsuario']) ? sanitize_input($_POST['tipoUsuario']) : 'Comprador';

// Validaciones
if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit();
}

if ($password !== $password_confirm) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $usuario = new Usuario($db);

    // Verificar si el email ya existe
    if ($usuario->emailExists($email)) {
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        exit();
    }

    // Obtener ID del estado "Activo"
    $queryEstado = "SELECT idEstado FROM Estado WHERE nombre = 'Activo' AND tipoEntidad = 'General' LIMIT 1";
    $stmtEstado = $db->prepare($queryEstado);
    $stmtEstado->execute();
    $idEstadoActivo = $stmtEstado->fetch(PDO::FETCH_ASSOC)['idEstado'];

    // Crear usuario
    $usuario->nombre = $nombre;
    $usuario->apellidos = $apellidos;
    $usuario->email = $email;
    $usuario->telefono = $telefono;
    $usuario->contraseña = $password;
    $usuario->tipoUsuario = $tipoUsuario;
    $usuario->idEstadoUsuario = $idEstadoActivo;

    if ($usuario->registrar()) {
        echo json_encode([
            'success' => true,
            'message' => 'Registro exitoso. Por favor, inicia sesión.',
            'redirect' => BASE_URL . '/views/login.php?registered=1'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario']);
    }

} catch (Exception $e) {
    error_log("Error en registro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar el registro']);
}
?>
