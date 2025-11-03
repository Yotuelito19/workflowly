<?php
// api/admin/tipos/listar.php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('is_admin') || !is_admin()) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'msg'=>'Forbidden']); exit;
}

$idEvento = (int)($_GET['idEvento'] ?? 0);
if ($idEvento <= 0) { echo json_encode([]); exit; }

$db = (new Database())->getConnection();
$st = $db->prepare("SELECT idTipoEntrada, nombre, precio, cantidadDisponible
                      FROM TipoEntrada
                     WHERE idEvento = :e
                     ORDER BY idTipoEntrada");
$st->execute([':e' => $idEvento]);
echo json_encode($st->fetchAll(PDO::FETCH_ASSOC));
