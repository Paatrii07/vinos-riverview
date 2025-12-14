<?php


$servidor = "localhost";
$usuario = "root";
$password = "";
$basedatos = "vinos_riverview";

// Crear la conexión
$conexion = mysqli_connect($servidor, $usuario, $password, $basedatos);


if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");
?>