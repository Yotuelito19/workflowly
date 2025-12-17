<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// solo admin
if (!function_exists('is_admin') || !is_admin()) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'msg' => 'Forbidden']);
  exit;
}

// leer JSON
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true) ?? [];

$idEvento    = (int)($in['idEvento'] ?? 0);
$nombre      = trim($in['nombre'] ?? '');
$descripcion = trim($in['descripcion'] ?? '');
$precio      = (float)($in['precio'] ?? 0);
$cantidad    = (int)($in['cantidadDisponible'] ?? 0);

if ($idEvento <= 0 || $nombre === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
  exit;
}

try {
  $db = (new Database())->getConnection();

  // 1) sacar aforoTotal y la suma actual de tipos
  $st = $db->prepare("
    SELECT e.aforoTotal,
           COALESCE(SUM(t.cantidadDisponible), 0) AS usados
    FROM Evento e
    LEFT JOIN TipoEntrada t ON t.idEvento = e.idEvento
    WHERE e.idEvento = :e
    GROUP BY e.idEvento, e.aforoTotal
  ");
  $st->execute([':e' => $idEvento]);
  $row = $st->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    throw new Exception('Evento no encontrado');
  }

  $aforo  = (int)$row['aforoTotal'];
  $usados = (int)$row['usados'];
  $libres = max(0, $aforo - $usados); // lo que queda disponible

  // 2) si la cantidad que pide el admin es mayor que el hueco, la recortamos
  $cantidadFinal = min($cantidad, $libres);

  // 3) insert con descripcion
  $sql = "INSERT INTO TipoEntrada
            (idEvento, nombre, descripcion, precio, cantidadDisponible, fechaInicioVenta, fechaFinVenta)
          VALUES
            (:e, :n, :d, :p, :c, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))";
  $stmt = $db->prepare($sql);
  $stmt->execute([
    ':e' => $idEvento,
    ':n' => $nombre,
    ':d' => $descripcion,
    ':p' => $precio,
    ':c' => $cantidadFinal,
  ]);

  echo json_encode([
    'ok'               => true,
    'idTipoEntrada'    => (int)$db->lastInsertId(),
    'cantidadGuardada' => $cantidadFinal,
    'aforoTotal'       => $aforo,
    'usadosAntes'      => $usados,
    'libresAntes'      => $libres,
    'descripcion'      => $descripcion,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
