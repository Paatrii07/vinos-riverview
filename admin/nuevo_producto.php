<?php
session_start();
require_once '../config.php';

// SEGURIDAD: Solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $cat = $_POST['categoria'];

    $nombre_foto = time() . "_" . $_FILES['foto']['name']; 
    $ruta_temporal = $_FILES['foto']['tmp_name'];
    $destino = "../img/" . $nombre_foto;

    if (move_uploaded_file($ruta_temporal, $destino)) {
        $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $sql = "INSERT INTO producto (nombre, precio_unidad, imagen_url, id_categoria) VALUES (?, ?, ?, ?)";
        $conexion->prepare($sql)->execute([$nombre, $precio, $nombre_foto, $cat]);
        header("Location: panel.php?msg=creado");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <title>Nuevo Producto - Vinos Riverview</title>
    <style>
        .btn-vino-admin { background-color: #640D14; color: white; }
        .btn-vino-admin:hover { background-color: #4a0a0f; color: white; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="panel.php" class="btn btn-outline-secondary btn-sm">Volver al Panel</a>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm border-0">
                <h3 class="fw-light mb-4">Añadir Nuevo Producto</h3>
                
                <label class="small text-muted">Nombre del producto</label>
                <input type="text" name="nombre" class="form-control mb-3" placeholder="Ej: Vino Tinto Reserva" required>
                
                <label class="small text-muted">Precio (€)</label>
                <input type="number" step="0.01" name="precio" class="form-control mb-3" placeholder="0.00" required>
                
                <label class="small text-muted">Categoría</label>
                <select name="categoria" class="form-control mb-3">
                    <option value="1">Vino</option>
                    <option value="2">Queso</option>
                    <option value="3">Embutido</option>
                </select>
                
                <label class="small text-muted">Imagen del producto</label>
                <input type="file" name="foto" class="form-control mb-3" accept="image/*" required>
                
                <button type="submit" class="btn btn-vino-admin w-100">GUARDAR EN EL INVENTARIO</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>