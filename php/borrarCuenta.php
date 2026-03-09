<?php
session_start();
require_once '../config.php';

// 1. Seguridad: Si no está logueado, fuera
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}


try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 3. Borrar el usuario
    // Usamos bindParam para máxima seguridad
    $id_usuario = $_SESSION['usuario_id'];
    
    $sql = "DELETE FROM usuario WHERE id_usuario = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // 4. Si se borra en BBDD, destruimos la sesión PHP
        session_destroy();
        
        // Redirigir a inicio con un parámetro para mostrar mensaje (opcional)
        header("Location: ../index.php?mensaje=cuenta_eliminada");
        exit();
    } else {
        echo "Error al eliminar la cuenta.";
    }

} catch(PDOException $e) {
    error_log("Error de conexión: " .$e->getMessage()); 
    echo "Error de conexión. Inténtalo más tarde.";
    exit; // Añadimos exit para que no intente ejecutar el resto si falla la conexión
}
?>