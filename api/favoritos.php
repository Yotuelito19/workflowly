<?php
/**
 * API para gestión de favoritos - WorkFlowly
 * Maneja agregar, eliminar, verificar y listar eventos favoritos
 */

// Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Incluir configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar que el usuario esté logueado
if (!is_logged_in()) {
    json_error('Debes iniciar sesión para gestionar favoritos', 401);
}

// Obtener acción
$accion = isset($_GET['accion']) ? trim($_GET['accion']) : '';

// Conectar a la base de datos
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    error_log("Error de conexión DB en favoritos: " . $e->getMessage());
    json_error('Error de conexión a la base de datos', 500);
}

$idUsuario = (int)$_SESSION['user_id'];

// ============================================
// PROCESAR ACCIONES
// ============================================

try {
    switch ($accion) {
        
        // ============================================
        // LISTAR FAVORITOS
        // ============================================
        case 'listar':
            $query = "SELECT 
                        e.idEvento,
                        e.nombre,
                        e.descripcion,
                        e.tipo,
                        e.fechaInicio,
                        e.fechaFin,
                        e.ubicacion,
                        e.aforoTotal,
                        e.entradasDisponibles,
                        e.imagenPrincipal,
                        f.fechaAgregado,
                        MIN(te.precio) as precio_desde
                      FROM FavoritoEvento f
                      INNER JOIN Evento e ON f.idEvento = e.idEvento
                      LEFT JOIN TipoEntrada te ON e.idEvento = te.idEvento
                      INNER JOIN Estado est ON e.idEstadoEvento = est.idEstado
                      WHERE f.idUsuario = :idUsuario
                        AND est.nombre = 'Activo'
                        AND e.fechaFin > NOW()
                      GROUP BY e.idEvento
                      ORDER BY f.fechaAgregado DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            
            $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            json_success([
                'ok' => true,
                'favoritos' => $favoritos,
                'total' => count($favoritos)
            ]);
            break;
        
        // ============================================
        // AGREGAR FAVORITO
        // ============================================
        case 'agregar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_error('Método no permitido', 405);
            }
            
            $idEvento = isset($_POST['idEvento']) ? (int)$_POST['idEvento'] : 0;
            
            if ($idEvento <= 0) {
                json_error('ID de evento inválido');
            }
            
            // Verificar si el evento existe y está activo
            $queryEvento = "SELECT e.idEvento 
                           FROM Evento e
                           INNER JOIN Estado est ON e.idEstadoEvento = est.idEstado
                           WHERE e.idEvento = :idEvento 
                           AND est.nombre = 'Activo'";
            $stmtEvento = $db->prepare($queryEvento);
            $stmtEvento->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
            $stmtEvento->execute();
            
            if ($stmtEvento->rowCount() === 0) {
                json_error('Evento no encontrado o no disponible', 404);
            }
            
            // Verificar si ya existe en favoritos
            $queryCheck = "SELECT idFavoritoEvento FROM FavoritoEvento 
                          WHERE idUsuario = :idUsuario AND idEvento = :idEvento";
            $stmtCheck = $db->prepare($queryCheck);
            $stmtCheck->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmtCheck->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->rowCount() > 0) {
                json_success([
                    'ok' => true,
                    'mensaje' => 'El evento ya está en tus favoritos'
                ]);
            }
            
            // Agregar a favoritos
            $queryInsert = "INSERT INTO FavoritoEvento (idUsuario, idEvento, fechaAgregado) 
                           VALUES (:idUsuario, :idEvento, NOW())";
            $stmtInsert = $db->prepare($queryInsert);
            $stmtInsert->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmtInsert->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
            
            if ($stmtInsert->execute()) {
                json_success([
                    'ok' => true,
                    'mensaje' => 'Evento agregado a favoritos'
                ]);
            } else {
                json_error('Error al agregar a favoritos', 500);
            }
            break;
        
        // Eliminar favorito
        case 'eliminar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_error('Método no permitido', 405);
            }
            
            $idEvento = isset($_POST['idEvento']) ? (int)$_POST['idEvento'] : 0;
            
            if ($idEvento <= 0) {
                json_error('ID de evento inválido');
            }
            
            $query = "DELETE FROM FavoritoEvento 
                     WHERE idUsuario = :idUsuario AND idEvento = :idEvento";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    json_success([
                        'ok' => true,
                        'mensaje' => 'Evento eliminado de favoritos'
                    ]);
                } else {
                    json_success([
                        'ok' => true,
                        'mensaje' => 'El evento no estaba en favoritos'
                    ]);
                }
            } else {
                json_error('Error al eliminar de favoritos', 500);
            }
            break;
        
        // Verificar si es favorito
        case 'verificar':
            $idEvento = isset($_GET['idEvento']) ? (int)$_GET['idEvento'] : 0;
            
            if ($idEvento <= 0) {
                json_error('ID de evento inválido');
            }
            
            $query = "SELECT idFavoritoEvento FROM FavoritoEvento 
                     WHERE idUsuario = :idUsuario AND idEvento = :idEvento";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
            $stmt->execute();
            
            json_success([
                'ok' => true,
                'esFavorito' => $stmt->rowCount() > 0
            ]);
            break;
        
        // ============================================
        // ACCIÓN NO VÁLIDA
        // ============================================
        default:
            json_error('Acción no válida. Acciones disponibles: listar, agregar, eliminar, verificar');
            break;
    }
    
} catch (PDOException $e) {
    error_log("Error PDO en favoritos.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    json_error(
        ENVIRONMENT === 'development' 
            ? 'Error en la base de datos: ' . $e->getMessage()
            : 'Error en la base de datos',
        500
    );
    
} catch (Exception $e) {
    error_log("Error general en favoritos.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    json_error(
        ENVIRONMENT === 'development'
            ? 'Error al procesar la solicitud: ' . $e->getMessage()
            : 'Error al procesar la solicitud',
        500
    );
}
?>