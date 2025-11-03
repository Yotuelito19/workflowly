<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

set_error_handler(function ($severity, $message, $file, $line) {
  throw new ErrorException($message, 0, $severity, $file, $line);
});

/* SOLO actúa si hubo un fatal real; no toques respuestas válidas */
register_shutdown_function(function () {
  $err = error_get_last();
  if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode([
      'ok'    => false,
      'error' => 'Fatal error',
      'detail'=> $err['message'].' @ '.$err['file'].':'.$err['line']
    ], JSON_UNESCAPED_UNICODE);
  } else {
    if (ob_get_length()) ob_end_flush(); // entrega lo que haya (tu JSON)
  }
});


require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Evento.php';
require_once __DIR__ . '/../../_utils_upload.php';

if (!function_exists('is_admin') || !is_admin()) {
  if (ob_get_length()) ob_end_clean();
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'Forbidden'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $idEvento = (int)($_POST['idEvento'] ?? 0);
  if ($idEvento<=0) throw new RuntimeException('idEvento inválido.');

  $database = new Database();
  $db = $database->getConnection();
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $eventoModel = new Evento($db);

  $actual = $eventoModel->obtenerPorId($idEvento);
  if (!$actual) throw new RuntimeException('Evento no encontrado.');

  $normDate = function (?string $s) {
    $s = trim((string)$s);
    if ($s==='') return '';
    $s = str_replace('T',' ',$s);
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/',$s)) $s.=':00';
    return $s;
  };

  $imagen = handle_image_upload('imagen');
  if ($imagen === null) $imagen = $actual['imagenPrincipal'];

  // 👇 NUEVO: pueden venir vacíos
  $idLugar       = isset($_POST['idLugar']) && $_POST['idLugar'] !== '' ? (int)$_POST['idLugar'] : null;
  $idOrganizador = isset($_POST['idOrganizador']) && $_POST['idOrganizador'] !== '' ? (int)$_POST['idOrganizador'] : null;

  $data = [
    'idEvento'            => $idEvento,
    'nombre'              => sanitize_input($_POST['nombre'] ?? $actual['nombre']),
    'descripcion'         => sanitize_input($_POST['descripcion'] ?? $actual['descripcion']),
    'tipo'                => sanitize_input($_POST['tipo'] ?? $actual['tipo']),
    'fechaInicio'         => $normDate($_POST['fechaInicio'] ?? $actual['fechaInicio']),
    'fechaFin'            => $normDate($_POST['fechaFin'] ?? $actual['fechaFin']),
    'ubicacion'           => sanitize_input($_POST['ubicacion'] ?? $actual['ubicacion']),
    'aforoTotal'          => (int)($_POST['aforoTotal'] ?? $actual['aforoTotal']),
    'entradasDisponibles' => (int)($_POST['entradasDisponibles'] ?? $actual['entradasDisponibles']),
    'imagenPrincipal'     => $imagen,
    'idEstadoEvento'      => (int)($_POST['idEstadoEvento'] ?? $actual['idEstadoEvento']),
    // 🔽🔽 NUEVO 🔽🔽
    'idLugar'             => $idLugar,
    'idOrganizador'       => $idOrganizador,
  ];

  // Validaciones
  if (!$data['nombre'] || !$data['tipo'] || !$data['fechaInicio'] || !$data['fechaFin'] || !$data['ubicacion']) {
    throw new RuntimeException('Faltan datos obligatorios.');
  }
  if (strtotime($data['fechaFin']) <= strtotime($data['fechaInicio'])) {
    throw new RuntimeException('La fecha de fin debe ser posterior al inicio.');
  }
  if ($data['entradasDisponibles'] < 0) throw new RuntimeException('Stock negativo no permitido.');
  if ($data['aforoTotal'] <= 0) throw new RuntimeException('Aforo debe ser mayor a 0.');
  if ($data['entradasDisponibles'] > $data['aforoTotal']) {
    throw new RuntimeException('Stock no puede superar el aforo.');
  }

  if (!$eventoModel->actualizar($data)) throw new RuntimeException('No se pudo actualizar.');

  if (ob_get_length()) ob_end_clean();
  echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
  exit;

} catch (Throwable $e) {
  if (ob_get_length()) ob_end_clean();
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}
// Eliminar tipos de entrada anteriores
$del = $db->prepare("DELETE FROM TipoEntrada WHERE idEvento = :idEvento");
$del->execute([':idEvento' => $idEvento]);

// Insertar los nuevos (si vienen)
if (!empty($_POST['tickets'])) {
  $tickets = json_decode($_POST['tickets'], true) ?: [];
  if ($tickets) {
    $ins = $db->prepare(
      "INSERT INTO TipoEntrada
       (idEvento, nombre, descripcion, precio, cantidadDisponible, fechaInicioVenta, fechaFinVenta)
       VALUES (:idEvento, :nombre, '', :precio, :cantidad, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))"
    );
    foreach ($tickets as $t) {
      if (empty($t['nombre'])) continue;
      $ins->execute([
        ':idEvento' => $idEvento,
        ':nombre'   => $t['nombre'],
        ':precio'   => (float)($t['precio'] ?? 0),
        ':cantidad' => (int)($t['cantidad'] ?? 0),
      ]);
    }
  }
}
