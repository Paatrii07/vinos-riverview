<?php
session_start();
require_once '../config.php';

// 1. CONEXIÓN A LA BASE DE DATOS
try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 2. SEGURIDAD: Si no está logueado, al login
if (!isset($_SESSION['usuario_id'])) {
    // Si viene de un formulario POST, lo mandamos a experiencias para que vuelva a empezar tras el login
    header("Location: login.php?error=debes_loguearte");
    exit();
}

// 3. CAPTURAR DATOS DEL FORMULARIO (POST)
// Cambiamos $_GET por $_POST porque el modal usa un <form method="POST">
$id_usuario = $_SESSION['usuario_id'];
$id_visita = isset($_POST['id_visita']) ? $_POST['id_visita'] : null;
$num_personas = isset($_POST['num_personas']) ? $_POST['num_personas'] : 1;
$fecha_reserva = date('Y-m-d'); 
$estado = 'confirmada';

// Si no hay ID de visita, lo echamos atrás
if (!$id_visita) {
    header("Location: experiencias.php");
    exit();
}

// 4. COMPROBAR SI YA EXISTE LA RESERVA
$check = $conexion->prepare("SELECT * FROM reserva WHERE id_usuario = ? AND id_visita = ?");
$check->execute([$id_usuario, $id_visita]);

if ($check->rowCount() > 0) {
    header("Location: perfil.php?msg=ya_reservado#experiencias");
    exit();
}

// 5. INSERTAR LA RESERVA REAL
try {
    $sql = "INSERT INTO reserva (id_usuario, id_visita, fecha_reserva, num_personas, estado) 
            VALUES (:user, :visita, :fecha, :cantidad, :estado)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':user', $id_usuario);
    $stmt->bindParam(':visita', $id_visita);
    $stmt->bindParam(':fecha', $fecha_reserva);
    $stmt->bindParam(':cantidad', $num_personas); // Aquí usamos el dato del modal
    $stmt->bindParam(':estado', $estado);
    
    if ($stmt->execute()) {
    // Volvemos a experiencias con un parámetro de éxito
    header("Location: experiencias.php?reserva_exitosa=1");
    exit();
}
} catch(PDOException $e) {
    echo "Error crítico: " . $e->getMessage();
}