

<?php
/**
 * API - para métodos de pago
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$userId = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'listar';

try {
    switch ($accion) {
        case 'listar':
            listarMetodos($db, $userId);
            break;
        case 'agregar':
            agregarMetodo($db, $userId);
            break;
        case 'eliminar':
            eliminarMetodo($db, $userId);
            break;
        case 'predeterminado':
            setPredeterminado($db, $userId);
            break;
        default:
            echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

function listarMetodos($db, $userId) {
    $query = "SELECT * FROM MetodoPago WHERE idUsuario = ? ORDER BY esPredeterminado DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    
    $metodos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Extraer últimos 4 dígitos del token
        $ultimos = substr($row['tokenReferencia'] ?? '0000', -4);
        
        $metodos[] = [
            'id' => $row['idMetodoPago'],
            'tipo' => $row['tipo'] === 'Tarjeta' ? 'Visa' : $row['tipo'],
            'numero' => '**** **** **** ' . $ultimos,
            'titular' => $row['nombreTitular'] ?? 'Usuario',
            'expiracion' => $row['fechaExpiracion'] ? date('m/y', strtotime($row['fechaExpiracion'])) : '12/25',
            'predeterminado' => (bool)$row['esPredeterminado']
        ];
    }
    
    echo json_encode(['ok' => true, 'metodos' => $metodos]);
}

function agregarMetodo($db, $userId) {
    $tipo = $_POST['tipo'] ?? 'Tarjeta';
    $numero = preg_replace('/\s+/', '', $_POST['numero'] ?? '0000');
    $titular = $_POST['titular'] ?? 'Usuario';
    $expiracion = $_POST['expiracion'] ?? '12/25';
    $predeterminado = isset($_POST['predeterminado']) ? 1 : 0;
    
    // Extraer últimos 4 dígitos
    $ultimos4 = substr($numero, -4);
    
    // Convertir fecha MM/YY a DATE
    list($mes, $ano) = explode('/', $expiracion);
    $fechaExp = '20' . $ano . '-' . $mes . '-01';
    
    // Si es predeterminado, quitar el flag de los demás
    if ($predeterminado) {
        $update = "UPDATE MetodoPago SET esPredeterminado = 0 WHERE idUsuario = ?";
        $stmt = $db->prepare($update);
        $stmt->execute([$userId]);
    }
    
    $query = "INSERT INTO MetodoPago (idUsuario, tipo, tokenReferencia, nombreTitular, fechaExpiracion, esPredeterminado) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId, $tipo, $ultimos4, $titular, $fechaExp, $predeterminado]);
    
    echo json_encode(['ok' => true, 'mensaje' => 'Método agregado', 'id' => $db->lastInsertId()]);
}

function eliminarMetodo($db, $userId) {
    $id = $_POST['idMetodo'] ?? 0;
    
    $query = "DELETE FROM MetodoPago WHERE idMetodoPago = ? AND idUsuario = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id, $userId]);
    
    echo json_encode(['ok' => true, 'mensaje' => 'Método eliminado']);
}

function setPredeterminado($db, $userId) {
    $id = $_POST['idMetodo'] ?? 0;
    
    // Quitar predeterminado de todos
    $update1 = "UPDATE MetodoPago SET esPredeterminado = 0 WHERE idUsuario = ?";
    $stmt1 = $db->prepare($update1);
    $stmt1->execute([$userId]);
    
    // Establecer el nuevo
    $update2 = "UPDATE MetodoPago SET esPredeterminado = 1 WHERE idMetodoPago = ? AND idUsuario = ?";
    $stmt2 = $db->prepare($update2);
    $stmt2->execute([$id, $userId]);
    
    echo json_encode(['ok' => true, 'mensaje' => 'Predeterminado actualizado']);
}
?>