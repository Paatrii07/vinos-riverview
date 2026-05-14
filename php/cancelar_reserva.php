<?php
session_start();
require_once '../config.php';

// Seguridad: Solo si hay sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Capturar el ID de la visita
$id_visita = isset($_GET['id_visita']) ? (int) $_GET['id_visita'] : 0;
$id_usuario = $_SESSION['usuario_id'];

if ($id_visita > 0) {
    try {
        $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DELETE FROM reserva WHERE id_visita = :id_vis AND id_usuario = :id_usu";
        $stmt = $conexion->prepare($sql);

        if ($stmt->execute([':id_vis' => $id_visita, ':id_usu' => $id_usuario])) {
            header("Location: perfil.php?cancelado=ok#experiencias");
            exit();
        } else {
            header("Location: perfil.php?error=cancelacion#experiencias");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Error al cancelar reserva: " . $e->getMessage());
        header("Location: perfil.php?error=cancelacion#experiencias");
        exit();
    }
} else {
    header("Location: perfil.php#experiencias");
    exit();
}
?>
