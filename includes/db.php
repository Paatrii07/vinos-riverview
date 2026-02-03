<?php
$url = 'mysql:dbname=vinos_riverview;host=localhost';
$user = 'root';
$pass = "";

try {
    $conexion = new PDO($url, $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->exec("SET CHARACTER SET utf8");
} catch(PDOException $e) {
    echo "Fallo la conexión: " . $e->getMessage();
    die();
}
?>