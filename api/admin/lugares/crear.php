<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

// Solo admin
if (!function_exists('is_admin') || !is_admin()) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'msg'=>'Forbidden']); exit;
}

// Acepta JSON o form-data
$raw = file_get_contents('php://input');
$in  = $_POST ?: (json_decode($raw, true) ?? []);

$nombre    = trim($in['nombre']    ?? '');
$direccion = trim($in['direccion'] ?? '');
$ciudad    = trim($in['ciudad']    ?? '');
$pais      = trim($in['pais']      ?? '');
$capacidad = (int)($in['capacidad'] ?? 0);

// Mapeo robusto (acepta ambas claves)
$accesoDiscapacitados = isset($in['accesoDiscapacitados'])
  ? (int)$in['accesoDiscapacitados']
  : (int)($in['accesoDiscapacidad'] ?? 0);

$parking           = $in['parking']           ?? ($in['parkingInfo'] ?? '');
$transportePublico = $in['transportePublico'] ?? '';
$mapaUrl           = $in['mapaUrl']           ?? ($in['enlaceMapa'] ?? '');

if ($nombre === '' || $capacidad <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'Nombre y capacidad son obligatorios']); exit;
}

try {
  $db = (new Database())->getConnection();

  $sql = "INSERT INTO Lugar
          (nombre, direccion, ciudad, pais, capacidad,
           accesoDiscapacitados, parking, transportePublico, mapaUrl)
          VALUES
          (:nombre, :direccion, :ciudad, :pais, :capacidad,
           :acceso, :parking, :transp, :mapa)";

  $st = $db->prepare($sql);
  $st->execute([
    ':nombre'    => $nombre,
    ':direccion' => $direccion,
    ':ciudad'    => $ciudad,
    ':pais'      => $pais,
    ':capacidad' => $capacidad,
    ':acceso'    => $accesoDiscapacitados,
    ':parking'   => $parking,
    ':transp'    => $transportePublico,
    ':mapa'      => $mapaUrl,
  ]);

  echo json_encode([
    'ok' => true,
    'idLugar' => (int)$db->lastInsertId(),
    'capacidad' => $capacidad
  ]);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
