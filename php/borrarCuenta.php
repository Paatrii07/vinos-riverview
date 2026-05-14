<?php
session_start();
require_once '../config.php';

// Seguridad: Si no está logueado, fuera
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_usuario = $_SESSION['usuario_id'];

    $sql = "DELETE FROM usuario WHERE id_usuario = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);

    if ($stmt->execute()) {
        session_unset();
        session_destroy();

        header("Location: ../index.php?mensaje=cuenta_eliminada");
        exit();
    } else {
        header("Location: ../index.php?mensaje=error_eliminar");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error al borrar cuenta: " . $e->getMessage());
    header("Location: ../index.php?mensaje=error_eliminar");
    exit();
}
?>
