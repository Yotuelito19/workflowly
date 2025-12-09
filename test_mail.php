<?php
$to = 'soporteworkflowly@gmail.com';
$subject = 'Prueba XAMPP sendmail';
$body = "Hola!\nEsto es una prueba.";
$headers  = "From: WorkFlowly <soporteworkflowly@gmail.com>\r\n";
$headers .= "Reply-To: soporteworkflowly@gmail.com\r\n";

echo mail($to, $subject, $body, $headers) ? 'OK' : 'FAIL';
