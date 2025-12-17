<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('is_admin') || !is_admin()) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'msg'=>'Forbidden']); exit;
}

$raw = file_get_contents('php://input');
$in  = json_decode($raw, true) ?? [];

$idEvento = (int)($in['idEvento'] ?? 0);
$nombre   = trim($in['nombre'] ?? '');
$precio   = (float)($in['precio'] ?? 0);
$cant     = (int)($in['cantidadDisponible'] ?? 0);
$idTipo   = (int)($in['idTipoEntrada'] ?? 0);

if ($idEvento <= 0 || $nombre === '' || $precio < 0 || $cant < 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit;
}

try {
  $db = (new Database())->getConnection();

  if ($idTipo > 0) {
    $sql = "UPDATE TipoEntrada
               SET nombre=:n, precio=:p, cantidadDisponible=:c
             WHERE idTipoEntrada=:id AND idEvento=:e";
    $ok = $db->prepare($sql)->execute([
      ':n'=>$nombre, ':p'=>$precio, ':c'=>$cant, ':id'=>$idTipo, ':e'=>$idEvento
    ]);
  } else {
    $sql = "INSERT INTO TipoEntrada
              (idEvento, nombre, descripcion, precio, cantidadDisponible, fechaInicioVenta, fechaFinVenta)
            VALUES
              (:e, :n, '', :p, :c, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))";
    $ok = $db->prepare($sql)->execute([
      ':e'=>$idEvento, ':n'=>$nombre, ':p'=>$precio, ':c'=>$cant
    ]);
  }

  if (!$ok) { throw new Exception('DB error'); }
  
 // recalcular stock del evento, SIN superar el aforo
$db->prepare(
  "UPDATE Evento e
      JOIN (
        SELECT idEvento, COALESCE(SUM(cantidadDisponible),0) s
        FROM TipoEntrada
        WHERE idEvento = :e
      ) x ON x.idEvento = e.idEvento
     SET e.entradasDisponibles = LEAST(x.s, e.aforoTotal)
   WHERE e.idEvento = :e"
)->execute([':e' => $idEvento]);

  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
