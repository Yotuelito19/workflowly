<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$idEvento = (int)($_POST['idEvento'] ?? 0);
$idTipo   = (int)($_POST['idTipoEntrada'] ?? 0);

if ($idEvento <= 0 || $idTipo <= 0) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }

$db = (new Database())->getConnection();

$db->prepare("DELETE FROM TipoEntrada WHERE idTipoEntrada=:id AND idEvento=:e")
   ->execute([':id'=>$idTipo, ':e'=>$idEvento]);

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
