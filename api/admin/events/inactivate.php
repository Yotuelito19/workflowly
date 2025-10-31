<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Evento.php';

if (!function_exists('is_admin') || !is_admin()) {
    json_error('Forbidden', 403);
}

try {
    $idEvento = (int)($_POST['idEvento'] ?? 0);
    if ($idEvento <= 0) {
        json_error('idEvento inválido');
    }

    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // conseguir id del estado "Inactivo"
    $stmt = $db->prepare("SELECT idEstado FROM Estado WHERE nombre = 'Inactivo' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        json_error("No existe el estado 'Inactivo' en la tabla Estado");
    }
    $idInactivo = (int)$row['idEstado'];

    // actualizar evento
    $stmt2 = $db->prepare("UPDATE Evento SET idEstadoEvento = :idEstado WHERE idEvento = :idEvento");
    $stmt2->bindParam(':idEstado', $idInactivo, PDO::PARAM_INT);
    $stmt2->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);
    $stmt2->execute();

    if ($stmt2->rowCount() < 1) {
        json_error('No se actualizó el evento (¿ID inexistente?)');
    }

    json_success(['ok' => true]);

} catch (Throwable $e) {
    json_error($e->getMessage(), 400);
}
