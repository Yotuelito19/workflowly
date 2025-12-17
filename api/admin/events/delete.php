<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

/* === Blindaje JSON sin false positives === */
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();
set_error_handler(function ($severity, $message, $file, $line) {
  // Convierte avisos/errores en excepción
  throw new ErrorException($message, 0, $severity, $file, $line);
});
register_shutdown_function(function () {
  // Solo actúa si hubo un fatal real
  $err = error_get_last();
  if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode([
      'ok' => false,
      'error' => 'Fatal error',
      'detail' => $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']
    ], JSON_UNESCAPED_UNICODE);
  } else {
    // Entrega cualquier salida pendiente
    if (ob_get_length()) ob_end_flush();
  }
});

/* Rutas correctas */
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Evento.php';

/* Guard admin (sin redirecciones) */
if (!function_exists('is_admin') || !is_admin()) {
  if (ob_get_length()) ob_end_clean();
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $idEvento = (int)($_POST['idEvento'] ?? 0);
  if ($idEvento <= 0) throw new RuntimeException('idEvento inválido.');

  $database = new Database();
  $db = $database->getConnection();
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Borrado duro: las FKs con ON DELETE CASCADE harán el resto
  $stmt = $db->prepare("DELETE FROM Evento WHERE idEvento = :id");
  $stmt->bindParam(':id', $idEvento, PDO::PARAM_INT);
  $stmt->execute();

  // Respuesta JSON limpia
  if (ob_get_length()) ob_end_clean();
  echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
  exit;

} catch (Throwable $e) {
  if (ob_get_length()) ob_end_clean();
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}
