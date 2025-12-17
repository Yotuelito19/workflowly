<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('is_admin') || !is_admin()) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'msg'=>'Forbidden']); exit;
}

$idEvento = (int)($_POST['idEvento'] ?? 0);
$idTipo   = (int)($_POST['idTipoEntrada'] ?? 0);

if ($idEvento <= 0 || $idTipo <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit;
}

try {
  $db = (new Database())->getConnection();

  $st = $db->prepare("DELETE FROM TipoEntrada WHERE idTipoEntrada=:id AND idEvento=:e LIMIT 1");
  $st->execute([':id'=>$idTipo, ':e'=>$idEvento]);

  if ($st->rowCount() === 0) {
    echo json_encode(['ok'=>false,'msg'=>'Tipo no encontrado']); exit;
  }

  // No tocamos Evento; el trigger/constraints lo dejan consistente
  echo json_encode(['ok'=>true]);
} catch (PDOException $e) {
  // FK constraint
  if ($e->getCode() === '23000') {
    echo json_encode([
      'ok'=>false,
      'msg'=>'No se puede eliminar: hay ventas/entradas asociadas. Pon cantidad=0 o crea otro tipo.'
    ]);
    exit;
  }
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
