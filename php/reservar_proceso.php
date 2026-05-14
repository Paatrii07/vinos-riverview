<?php
session_start();
require_once '../config.php';

// Seguridad: Si no está logueado, al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php?error=debes_loguearte");
    exit();
}

// Capturar datos del formulario
$id_usuario = $_SESSION['usuario_id'];
$id_visita = isset($_POST['id_visita']) ? (int) $_POST['id_visita'] : 0;
$num_personas = isset($_POST['num_personas']) ? (int) $_POST['num_personas'] : 1;
$fecha_reserva = date('Y-m-d');
$estado = 'confirmada';

// Validación básica
if ($id_visita <= 0 || $num_personas <= 0) {
    header("Location: experiencias.php?error=datos");
    exit();
}

// Conexión a la base de datos
try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Comprobar si ya existe la reserva
    $check = $conexion->prepare("SELECT id_reserva FROM reserva WHERE id_usuario = ? AND id_visita = ?");
    $check->execute([$id_usuario, $id_visita]);

    if ($check->fetch()) {
        header("Location: perfil.php?msg=ya_reservado#experiencias");
        exit();
    }

    // Insertar reserva
    $sql = "INSERT INTO reserva (id_usuario, id_visita, fecha_reserva, num_personas, estado) 
            VALUES (:user, :visita, :fecha, :cantidad, :estado)";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':user', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':visita', $id_visita, PDO::PARAM_INT);
    $stmt->bindParam(':fecha', $fecha_reserva);
    $stmt->bindParam(':cantidad', $num_personas, PDO::PARAM_INT);
    $stmt->bindParam(':estado', $estado);

    if ($stmt->execute()) {
        header("Location: experiencias.php?reserva_exitosa=1");
        exit();
    } else {
        header("Location: experiencias.php?error=reserva");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error al procesar reserva: " . $e->getMessage());
    header("Location: experiencias.php?error=reserva");
    exit();
}
?>
