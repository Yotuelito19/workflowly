<?php
session_start();
require_once "../config/database.php"; // Ajusta si tu archivo se llama diferente

// Verificar que el usuario esté logueado
if (!isset($_SESSION['idUsuario'])) {
    header("../views/login.php");
    exit();
}

$idUsuario = $_SESSION['idUsuario'];
$accion = $_GET['accion'] ?? null;

// DESACTIVAR CUENTA (estado = 2)
if ($accion === "desactivar") {

    $sql = "UPDATE usuario SET idEstadoUsuario = 2 WHERE idUsuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$idUsuario]);

    // Cerrar sesión
    session_destroy();

    header("../views/login.php?mensaje=cuenta_desactivada");
    exit();
}

// ELIMINAR CUENTA (estado = 3)
if ($accion === "eliminar") {

    $sql = "UPDATE usuario SET idEstadoUsuario = 3 WHERE idUsuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$idUsuario]);

    // Cerrar sesión
    session_destroy();

    header("../views/login.php?mensaje=cuenta_eliminada");
    exit();
}

// Si no hay acción válida
header(" ../views/account.php");
exit();
?>