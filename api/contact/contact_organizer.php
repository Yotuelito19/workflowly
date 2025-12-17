<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

function fail(string $msg, int $code = 400) {
  http_response_code($code);
  echo json_encode(['ok'=>false,'msg'=>$msg]); exit;
}

// ---- Entrada del formulario ----
$nombre     = trim($_POST['nombre']      ?? '');
$apellidos  = trim($_POST['apellidos']   ?? '');
$emailUser  = trim($_POST['email']       ?? '');
$numPedido  = trim($_POST['num_pedido']  ?? '');
$desc       = trim($_POST['descripcion'] ?? '');
$eventoId   = (int)($_POST['evento_id']  ?? 0);
$orgEmailIn = trim($_POST['organizer_email'] ?? '');

if ($nombre==='' || $apellidos==='' || $emailUser==='' || $desc==='' || $eventoId===0) fail('Faltan campos obligatorios.');
if (!filter_var($emailUser, FILTER_VALIDATE_EMAIL)) fail('El correo del usuario no es válido.');
if (mb_strlen($desc) < 10) fail('La descripción es demasiado corta.');

// ---- Resolución de email del organizador desde BD ----
try {
  $db  = (new Database())->getConnection();
  $sql = "
    SELECT 
      e.nombre AS evento_nombre,
      uo.email AS email_organizador,
      ue.email AS email_propietario
    FROM `evento` e
    LEFT JOIN `organizador` o ON o.idOrganizador = e.idOrganizador
    LEFT JOIN `usuario` uo    ON uo.idUsuario    = o.idUsuario
    LEFT JOIN `usuario` ue    ON ue.idUsuario    = e.idUsuario
    WHERE e.idEvento = :id
    LIMIT 1";
  $st = $db->prepare($sql);
  $st->execute([':id'=>$eventoId]);
  $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
  $eventoNombre   = $row['evento_nombre']   ?? ('Evento #'.$eventoId);
  $organizerEmail = $row['email_organizador'] ?: ($row['email_propietario'] ?? '');
  if ($organizerEmail === '' && $orgEmailIn !== '') $organizerEmail = $orgEmailIn;
} catch (Throwable $e) {
  fail('Error al consultar el organizador.', 500);
}

// ---- Datos de remitente y CC (coincidir sendmail.ini) ----
$from     = defined('MAIL_FROM')      ? MAIL_FROM      : 'soporteworkflowly@gmail.com';
$fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'WorkFlowly';
$cc       = defined('CONTACT_CC_EMAIL') ? CONTACT_CC_EMAIL : 'soporteworkflowly@gmail.com';

// Si no hay correo de organizador, enviamos a soporte (sin CC duplicado)
$toPrimary = $organizerEmail !== '' ? $organizerEmail : $cc;
$ccHeader  = ($organizerEmail !== '' && filter_var($cc, FILTER_VALIDATE_EMAIL)) ? "Cc: {$cc}\r\n" : '';

// ---- Mensajes ----
$subjectOrg = "[WorkFlowly] Consulta de usuario · {$eventoNombre}";
$bodyOrg    = "Has recibido un mensaje de un cliente desde la ficha del evento.\n\n"
            . "Evento: {$eventoNombre} (ID: {$eventoId})\n"
            . "Nombre: {$nombre}\n"
            . "Apellidos: {$apellidos}\n"
            . "Correo del cliente: {$emailUser}\n"
            . "Nº de pedido: " . ($numPedido !== '' ? $numPedido : '(no indicado)') . "\n\n"
            . "Descripción:\n{$desc}\n";

$subjectUser = "Hemos recibido tu consulta · {$eventoNombre}";
$bodyUser    = "Hola {$nombre},\n\n"
             . "Hemos recibido tu consulta sobre \"{$eventoNombre}\" y te responderemos por email lo antes posible.\n\n"
             . "Resumen enviado:\n{$desc}\n\n"
             . "Nº de pedido: " . ($numPedido !== '' ? $numPedido : '(no indicado)') . "\n\n"
             . "— Equipo WorkFlowly";

// ---- Headers (usar \r\n en Windows) ----
$headersOrg  = "From: {$fromName} <{$from}>\r\n";
$headersOrg .= $ccHeader;
$headersOrg .= "Reply-To: {$nombre} {$apellidos} <{$emailUser}>\r\n";
$headersOrg .= "MIME-Version: 1.0\r\n";
$headersOrg .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headersOrg .= "X-Mailer: PHP/".phpversion()."\r\n";

$headersUser  = "From: {$fromName} <{$from}>\r\n";
$headersUser .= "MIME-Version: 1.0\r\n";
$headersUser .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headersUser .= "X-Mailer: PHP/".phpversion()."\r\n";

// ---- Envío ----
$sentPrimary = @mail($toPrimary, $subjectOrg, $bodyOrg, $headersOrg);
$sentUser    = @mail($emailUser, $subjectUser, $bodyUser, $headersUser);

echo json_encode(($sentPrimary && $sentUser) ? ['ok'=>true]
                                             : ['ok'=>false,'msg'=>'No se pudo enviar el correo.']);
