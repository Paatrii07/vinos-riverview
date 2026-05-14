<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

$mensaje_error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $precio = $_POST['precio'];
    $aforo = $_POST['aforo'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $nombre_foto = time() . "_" . basename($_FILES['foto']['name']);
        $ruta_temporal = $_FILES['foto']['tmp_name'];
        $destino = "../img/" . $nombre_foto;

        if (move_uploaded_file($ruta_temporal, $destino)) {
            try {
                $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = "INSERT INTO cata (nombre_evento, descripcion, fecha, hora, precio, aforo_maximo, imagen) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

                $conexion->prepare($sql)->execute([$nombre, $descripcion, $fecha, $hora, $precio, $aforo, $nombre_foto]);

                header("Location: panel.php?msg=creado");
                exit();
            } catch (PDOException $e) {
                error_log("Error al insertar experiencia: " . $e->getMessage());
                $mensaje_error = "No se pudo guardar la experiencia. Inténtalo de nuevo.";
            }
        } else {
            $mensaje_error = "No se pudo subir la imagen de la experiencia.";
        }
    } else {
        $mensaje_error = "Debes seleccionar una imagen válida.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Experiencia - Vinos Riverview</title>

    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
                    <h1 class="fw-light mb-4 h3">Añadir Nueva Experiencia</h1>

                    <label class="form-label">Nombre del evento</label>
                    <input type="text" name="nombre" class="form-control mb-3" placeholder="Ej: Cata de Tintos Maridados" required>

                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control mb-3" rows="3" placeholder="Describe la experiencia..." required></textarea>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control mb-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora</label>
                            <input type="time" name="hora" class="form-control mb-3" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Precio (€)</label>
                            <input type="number" step="0.01" name="precio" class="form-control mb-3" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aforo (Personas)</label>
                            <input type="number" name="aforo" class="form-control mb-3" value="0" min="1" required>
                        </div>
                    </div>

                    <label class="form-label">Imagen de la experiencia</label>
                    <input type="file" name="foto" class="form-control mb-3" accept="image/*" required>

                    <button type="submit" class="btn btn-vino-admin w-100">GUARDAR EN EL INVENTARIO</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer-admin">
        <div class="container text-center">
            <p class="mb-0 small text-muted">&copy; 2026 Vinos Riverview - Gestión de Experiencias</p>
        </div>
    </footer>
</body>
</html>
