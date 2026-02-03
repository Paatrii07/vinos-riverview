<?php
session_start();

// 1. Seguridad: Si no está logueado, fuera
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

// 2. Conexión BBDD
$url = 'mysql:dbname=vinos_riverview;host=localhost';
$user = 'root';
$pass_db = ""; 

try {
    $conexion = new PDO($url, $user, $pass_db);
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
    echo "Error de conexión: " . $e->getMessage();
}
?>