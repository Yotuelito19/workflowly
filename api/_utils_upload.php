<?php
// Devuelve ruta relativa (p.ej. "uploads/archivo.jpg") o null si no hay archivo.
// Lanza RuntimeException con mensajes claros si hay error de subida.
function handle_image_upload(string $field, string $dir = 'uploads'): ?string {
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
    return null;
  }
  $f = $_FILES[$field];
  if (!is_dir($dir)) {
    if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
      throw new RuntimeException('No se pudo crear directorio de subidas.');
    }
  }
  if ($f['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Error al subir archivo (code '.$f['error'].').');
  }
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
  $mime = mime_content_type($f['tmp_name']);
  if (!isset($allowed[$mime])) throw new RuntimeException('Tipo de imagen no permitido.');
  if ($f['size'] > 5*1024*1024) throw new RuntimeException('Imagen > 5MB.');

  $ext = $allowed[$mime];
  $name = bin2hex(random_bytes(8)).'.'.$ext;
  $dest = rtrim($dir,'/').'/'.$name;

  if (!move_uploaded_file($f['tmp_name'], $dest)) {
    throw new RuntimeException('No se pudo mover el archivo subido.');
  }
  return $dest; // ruta pública que guardarás en BD
}
