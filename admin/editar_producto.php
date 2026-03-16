<?php
session_start();
require_once '../config.php';

// 1. SEGURIDAD: Solo si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

// 2. VALIDAR QUE VIENE UN ID
if (!isset($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id = (int)$_GET['id'];

try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cargar datos actuales del producto
    $stmt = $conexion->prepare("SELECT * FROM producto WHERE id_producto = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el producto no existe en la base de datos
    if (!$p) { 
        header("Location: panel.php?error=no_existe"); 
        exit(); 
    }

    // 3. PROCESAR EL FORMULARIO AL PULSAR EL BOTÓN
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = $_POST['nombre'];
        $desc = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $cat = $_POST['categoria'];
        
        // Lógica de la foto: si no suben una nueva, mantenemos la que hay
        $foto = $p['imagen_url'];
        if (!empty($_FILES['foto']['name'])) {
            $foto = time() . "_" . $_FILES['foto']['name'];
            move_uploaded_file($_FILES['foto']['tmp_name'], "../img/" . $foto);
        }

        // --- IMPORTANTE: Verifica que estos nombres coincidan con tu tabla 'producto' ---
        // Según tus capturas: id_producto, nombre, descripcion, precio_unidad, imagen_url, id_categoria, stock_actual
        $sql = "UPDATE producto SET 
                nombre = ?, 
                descripcion = ?, 
                precio_unidad = ?, 
                stock_actual = ?, 
                id_categoria = ?, 
                imagen_url = ? 
                WHERE id_producto = ?";
        
        $stmt_update = $conexion->prepare($sql);
        $stmt_update->execute([$nombre, $desc, $precio, $stock, $cat, $foto, $id]);
        
        header("Location: panel.php?msg=actualizado");
        exit();
    }
} catch(PDOException $e) {
    // Si hay un error, esto evitará la página en blanco y te dirá qué falla
    die("Error en la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - Admin</title>
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .btn-vino-admin { background-color: #640D14; color: white; }
        .btn-vino-admin:hover { background-color: #4a0a0f; color: white; }
        .text-vino { color: #640D14; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="panel.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver al Panel
                </a>
            </div>

            <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm border-0">
                <h3 class="fw-light mb-4 border-bottom pb-2">Editar: <span class="text-vino"><?php echo htmlspecialchars($p['nombre']); ?></span></h3>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nombre del Producto</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($p['nombre']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="5" required><?php echo htmlspecialchars($p['descripcion']); ?></textarea>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Precio (€)</label>
                        <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $p['precio_unidad']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo $p['stock_actual']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Categoría</label>
                        <select name="categoria" class="form-control">
                            <option value="1" <?php echo ($p['id_categoria'] == 1) ? 'selected' : ''; ?>>Vinos</option>
                            <option value="2" <?php echo ($p['id_categoria'] == 2) ? 'selected' : ''; ?>>Quesos</option>
                            <option value="3" <?php echo ($p['id_categoria'] == 3) ? 'selected' : ''; ?>>Embutidos</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Imagen Actual</label>
                    <div class="d-flex align-items-center gap-3 p-2 border rounded bg-white">
                        <img src="../img/<?php echo $p['imagen_url']; ?>" width="80" class="rounded">
                        <div class="flex-grow-1">
                            <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                            <div class="form-text small">Sube un archivo solo si quieres cambiar la imagen.</div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-vino-admin w-100 py-2 text-uppercase fw-bold" style="letter-spacing: 1px;">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>
</div>

<script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>