<?php
session_start();
require_once '../config.php';

// 1. SEGURIDAD: Solo si hay sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// 2. CAPTURAR EL ID DE LA VISITA (que viene de la URL)
$id_visita = isset($_GET['id_visita']) ? $_GET['id_visita'] : null;
$id_usuario = $_SESSION['usuario_id'];

if ($id_visita) {
    try {
        $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 3. BORRADO SEGURO: Filtramos por visita Y por el usuario logueado
        $sql = "DELETE FROM reserva WHERE id_visita = :id_vis AND id_usuario = :id_usu";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt->execute([':id_vis' => $id_visita, ':id_usu' => $id_usuario])) {
            // Volvemos al perfil con el ancla de experiencias
            header("Location: perfil.php?cancelado=ok#experiencias");
            exit();
        }
    } catch(PDOException $e) {
        die("Error técnico al cancelar: " . $e->getMessage());
    }
} else {
    header("Location: perfil.php");
}