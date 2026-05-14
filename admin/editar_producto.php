<?php
session_start();
require_once '../config.php';

// SEGURIDAD: Solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

// VALIDAR ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id = (int) $_GET['id'];
$mensaje_error = "";

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cargar producto actual
    $stmt = $conexion->prepare("SELECT * FROM producto WHERE id_producto = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        header("Location: panel.php");
        exit();
    }

    // Procesar actualización
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $cat = $_POST['categoria'];

        $foto = $producto['imagen_url'];

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0 && !empty($_FILES['foto']['name'])) {
            $nombre_foto = time() . "_" . basename($_FILES['foto']['name']);
            $ruta_temporal = $_FILES['foto']['tmp_name'];
            $destino = "../img/" . $nombre_foto;

            if (move_uploaded_file($ruta_temporal, $destino)) {
                $foto = $nombre_foto;
            } else {
                $mensaje_error = "No se pudo subir la nueva imagen del producto.";
            }
        }

        if (empty($mensaje_error)) {
            $sql = "UPDATE producto SET
                        nombre = ?,
                        descripcion = ?,
                        precio_unidad = ?,
                        imagen_url = ?,
                        id_categoria = ?,
                        stock_actual = ?
                    WHERE id_producto = ?";

            $conexion->prepare($sql)->execute([$nombre, $descripcion, $precio, $foto, $cat, $stock, $id]);

            header("Location: panel.php?msg=actualizado");
            exit();
        }
    }
} catch (PDOException $e) {
    error_log("Error al editar producto: " . $e->getMessage());
    $mensaje_error = "No se pudo actualizar el producto. Inténtalo de nuevo.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Vinos Riverview</title>

    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="../css/añadir_servicio.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <main class="container mt-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="panel.php" class="btn btn-outline-secondary btn-sm">Volver al Panel</a>
                </div>

                <?php if (!empty($mensaje_error)): ?>
                    <div class="alert alert-danger shadow-sm border-0">
                        <?php echo htmlspecialchars($mensaje_error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="card p-4 card-formulario shadow-sm border-0">
                    <h1 class="fw-light mb-4 h3">Editar Producto: <?php echo htmlspecialchars($producto['nombre']); ?></h1>

                    <label class="form-label">Nombre del producto</label>
                    <input type="text" name="nombre" class="form-control mb-3" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>

                    <label class="form-label">Descripción detallada</label>
                    <textarea name="descripcion" class="form-control mb-3" rows="3" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Precio (€)</label>
                            <input type="number" step="0.01" name="precio" class="form-control mb-3" value="<?php echo htmlspecialchars($producto['precio_unidad']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Actual</label>
                            <input type="number" name="stock" class="form-control mb-3" value="<?php echo htmlspecialchars($producto['stock_actual']); ?>" min="0" required>
                        </div>
                    </div>

                    <label class="form-label">Categoría</label>
                    <select name="categoria" class="form-control mb-3">
                        <option value="1" <?php echo ($producto['id_categoria'] == 1) ? 'selected' : ''; ?>>Vino</option>
                        <option value="2" <?php echo ($producto['id_categoria'] == 2) ? 'selected' : ''; ?>>Queso</option>
                        <option value="3" <?php echo ($producto['id_categoria'] == 3) ? 'selected' : ''; ?>>Embutido</option>
                    </select>

                    <div class="mb-3">
                        <label class="form-label">Imagen actual</label>
                        <div class="d-flex align-items-center gap-3 p-3 border rounded bg-white bloque-imagen-admin">
                            <img src="../img/<?php echo !empty($producto['imagen_url']) ? htmlspecialchars($producto['imagen_url']) : 'placeholder.jpg'; ?>" class="preview-admin-img rounded shadow-sm" alt="Imagen actual del producto">
                            <div class="flex-grow-1">
                                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                                <div class="form-text small text-muted">Sube una nueva imagen solo si deseas reemplazar la actual.</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-vino-admin w-100">ACTUALIZAR EN EL INVENTARIO</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer-admin mt-5">
        <div class="container-fluid text-center">
            <p class="mb-0 small text-muted">&copy; 2026 Vinos Riverview - Gestión de Inventario</p>
        </div>
    </footer>
</body>
</html>
