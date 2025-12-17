<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Evento.php';
require_once __DIR__ . '/../../_utils_upload.php';

/* === Guard de admin === */
if (!function_exists('is_admin') || !is_admin()) {
  json_error('Forbidden', 403);
}

/* === Helpers locales === */
$normDate = function (?string $s): string {
  $s = trim((string)$s);
  if ($s === '') return '';
  $s = str_replace('T', ' ', $s);
  if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $s)) $s .= ':00';
  return $s;
};

try {
  /* === Conexión y modelo === */
  $database = new Database();
  $db = $database->getConnection();
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $eventoModel = new Evento($db);

  /* === Datos del POST === */
  $nombre   = sanitize_input($_POST['nombre'] ?? '');
  $tipo     = sanitize_input($_POST['tipo'] ?? '');
  $desc     = sanitize_input($_POST['descripcion'] ?? '');
  $inicio   = $normDate($_POST['fechaInicio'] ?? '');
  $fin      = $normDate($_POST['fechaFin'] ?? '');
  $ubic     = sanitize_input($_POST['ubicacion'] ?? '');
  $aforo    = (int)($_POST['aforoTotal'] ?? 0);
  $stock    = (int)($_POST['entradasDisponibles'] ?? 0);
  $estadoId = (int)($_POST['idEstadoEvento'] ?? 0);

  $idLugar = !empty($_POST['idLugar']) ? (int)$_POST['idLugar'] : null;
  if ($idLugar) {
  $stL = $db->prepare("SELECT nombre, ciudad FROM Lugar WHERE idLugar=:id");
  $stL->execute([':id'=>$idLugar]);
  if ($r = $stL->fetch(PDO::FETCH_ASSOC)) {
    $ubic = trim($r['nombre'] . (!empty($r['ciudad']) ? ' (' . $r['ciudad'] . ')' : ''));
  }
}
  $idOrganizador  = !empty($_POST['idOrganizador']) ? (int)$_POST['idOrganizador'] : null;

  if (!$nombre || !$tipo || !$inicio || !$fin || $aforo <= 0 || $stock < 0) {
    json_error('Faltan datos obligatorios.');
  }
  if (strtotime($fin) <= strtotime($inicio)) {
    json_error('La fecha de fin debe ser posterior al inicio.');
  }
  if ($stock > $aforo) {
    json_error('Las entradas disponibles no pueden superar el aforo total.');
  }

  /* === Imagen === */
  try {
    $imagen = handle_image_upload('imagen'); // null si no suben
  } catch (RuntimeException $e) {
    $imagen = null;
  }
  if ($imagen === null) {
    $imagen = 'uploads/placeholder-event.jpg';
  }

  /* === Usuario creador (FK) === */
  $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
  if ($userId <= 0) {
    json_error('Usuario de sesión no válido (user_id no está en $_SESSION).');
  }

  /* === Estado (FK) === */
  if ($estadoId <= 0) {
    json_error('Debes seleccionar un estado válido.');
  }

  /* === Setear en el modelo === */
  $eventoModel->idUsuario           = $userId;
  $eventoModel->nombre              = $nombre;
  $eventoModel->descripcion         = $desc;
  $eventoModel->tipo                = $tipo;
  $eventoModel->fechaInicio         = $inicio;
  $eventoModel->fechaFin            = $fin;
  $eventoModel->ubicacion           = $ubic;
  $eventoModel->aforoTotal          = $aforo;
  $eventoModel->entradasDisponibles = $stock;
  $eventoModel->imagenPrincipal     = $imagen;
  $eventoModel->idEstadoEvento      = $estadoId;
  $eventoModel->idLugar             = $idLugar;
  $eventoModel->idOrganizador       = $idOrganizador;

  /* === Insert === */
  if (!$eventoModel->crear()) {
    json_error('No se pudo crear el evento (modelo devolvió false).');
  }
  $idInserted = $eventoModel->idEvento ?? (int)$db->lastInsertId();
  json_success(['ok' => true, 'idEvento' => $idInserted]);

} catch (Throwable $e) {
  json_error($e->getMessage(), 400);
}
// Guardar tipos de entrada
if (!empty($_POST['tickets'])) {
  $tickets = json_decode($_POST['tickets'], true) ?: [];
  if ($tickets) {
    $ins = $db->prepare(
      "INSERT INTO TipoEntrada
         (idEvento, nombre, descripcion, precio, cantidadDisponible, fechaInicioVenta, fechaFinVenta)
       VALUES
         (:e, :n, '', :p, :c, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))"
    );
    foreach ($tickets as $t) {
      if (empty($t['nombre'])) continue;
      $ins->execute([
        ':e' => $idInserted,
        ':n' => $t['nombre'],
        ':p' => (float)($t['precio'] ?? 0),
        ':c' => (int)($t['cantidad'] ?? 0),
      ]);
    }
    // recalcular stock
    $db->prepare(
      "UPDATE Evento e
          JOIN (SELECT idEvento, COALESCE(SUM(cantidadDisponible),0) s FROM TipoEntrada WHERE idEvento=:e) x
            ON x.idEvento = e.idEvento
         SET e.entradasDisponibles = x.s
       WHERE e.idEvento=:e"
    )->execute([':e'=>$idInserted]);
  }
}

json_success(['ok' => true, 'idEvento' => $idInserted]);