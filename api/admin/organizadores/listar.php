<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';

$database = new Database();
$db = $database->getConnection();


$sql = "SELECT o.idOrganizador, u.nombre, u.apellidos
        FROM Organizador o
        JOIN Usuario u ON o.idUsuario = u.idUsuario
        ORDER BY u.nombre, u.apellidos";


$stmt = $db->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
