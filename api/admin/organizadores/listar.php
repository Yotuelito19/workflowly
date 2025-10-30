<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

$database = new Database();
$db = $database->getConnection();

/*
   Solo los que están en tabla Organizador
   (si no tienes la tabla, este archivo fallará; en ese caso lo adaptamos a Usuario)
*/
$sql = "SELECT o.idOrganizador, u.nombre
        FROM Organizador o
        JOIN Usuario u ON o.idUsuario = u.idUsuario
        ORDER BY u.nombre";

$stmt = $db->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
