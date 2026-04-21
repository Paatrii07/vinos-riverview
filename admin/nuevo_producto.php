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
    $descripcion = $_POST['descripcion']; // Capturamos descripción
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];             // Capturamos stock
    $cat = $_POST['categoria'];

    $nombre_foto = time() . "_" . $_FILES['foto']['name']; 
    $ruta_temporal = $_FILES['foto']['tmp_name'];
    $destino = "../img/" . $nombre_foto;

    if (move_uploaded_file($ruta_temporal, $destino)) {
        try {
            $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Añadimos descripcion y stock_actual a la consulta
            $sql = "INSERT INTO producto (nombre, descripcion, precio_unidad, imagen_url, id_categoria, stock_actual) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $conexion->prepare($sql)->execute([$nombre, $descripcion, $precio, $nombre_foto, $cat, $stock]);
            
            header("Location: panel.php?msg=creado");
            exit();
        } catch(PDOException $e) {
            die("Error al insertar: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <title>Nuevo Producto - Vinos Riverview</title>
    <link href="../css/añadir_servicio.css" rel="stylesheet">

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
                
                <label class="form-label">Nombre del producto</label>
                <input type="text" name="nombre" class="form-control mb-3" placeholder="Ej: Vino Tinto Reserva" required>
                
                <label class="form-label">Descripción detallada</label>
                <textarea name="descripcion" class="form-control mb-3" rows="3" placeholder="Notas de cata, origen..." required></textarea>
                
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Precio (€)</label>
                        <input type="number" step="0.01" name="precio" class="form-control mb-3" placeholder="0.00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stock Inicial</label>
                        <input type="number" name="stock" class="form-control mb-3" value="0" min="0" required>
                    </div>
                </div>
                
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-control mb-3">
                    <option value="1">Vino</option>
                    <option value="2">Queso</option>
                    <option value="3">Embutido</option>
                </select>
                
                <label class="form-label">Imagen del producto</label>
                <input type="file" name="foto" class="form-control mb-3" accept="image/*" required>
                
                <button type="submit" class="btn btn-vino-admin w-100">GUARDAR EN EL INVENTARIO</button>
            </form>
        </div>
    </div>
</div>
<footer class="footer-admin mt-5">
    <div class="container-fluid text-center">
        <p class="mb-0 small text-muted">&copy; 2026 Vinos Riverview - Gestión de Inventario</p>
    </div>
</footer>
</body>
</html>