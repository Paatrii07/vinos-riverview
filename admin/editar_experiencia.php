<?php
session_start();
require_once '../config.php';

// SEGURIDAD: Solo si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

// VALIDAR QUE VIENE UN ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id = (int) $_GET['id'];
$mensaje_error = "";

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cargar datos actuales de la experiencia
    $stmt = $conexion->prepare("SELECT * FROM cata WHERE id_visita = ?");
    $stmt->execute([$id]);
    $ex = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ex) {
        header("Location: panel.php");
        exit();
    }

    // Procesar actualización
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre_evento'];
        $desc = $_POST['descripcion'];
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $precio = $_POST['precio'];
        $aforo = $_POST['aforo'];

        $foto = $ex['imagen'];

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0 && !empty($_FILES['foto']['name'])) {
            $nombre_foto = time() . "_" . basename($_FILES['foto']['name']);
            $ruta_temporal = $_FILES['foto']['tmp_name'];
            $destino = "../img/" . $nombre_foto;

            if (move_uploaded_file($ruta_temporal, $destino)) {
                $foto = $nombre_foto;
            } else {
                $mensaje_error = "No se pudo subir la nueva imagen.";
            }
        }

        if (empty($mensaje_error)) {
            $sql = "UPDATE cata SET 
                        nombre_evento = ?, 
                        descripcion = ?, 
                        fecha = ?, 
                        hora = ?, 
                        precio = ?, 
                        aforo_maximo = ?,
                        imagen = ?
                    WHERE id_visita = ?";

            $conexion->prepare($sql)->execute([$nombre, $desc, $fecha, $hora, $precio, $aforo, $foto, $id]);

            header("Location: panel.php?msg=actualizado");
            exit();
        }
    }
} catch (PDOException $e) {
    error_log("Error al editar experiencia: " . $e->getMessage());
    $mensaje_error = "Se produjo un error al actualizar la experiencia.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Experiencia - Vinos Riverview</title>

    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/añadir_servicio.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <main class="container mt-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="mb-3">
                    <a href="panel.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Cancelar y Volver
                    </a>
                </div>

                <?php if (!empty($mensaje_error)): ?>
                    <div class="alert alert-danger shadow-sm border-0">
                        <?php echo htmlspecialchars($mensaje_error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="card shadow-sm border-0 p-4 card-formulario">
                    <h1 class="fw-light mb-4 border-bottom pb-2 h3">
                        Editar Experiencia: <?php echo htmlspecialchars($ex['nombre_evento']); ?>
                    </h1>

                    <div class="mb-3">
                        <label class="form-label">Nombre del Evento</label>
                        <input type="text" name="nombre_evento" class="form-control" value="<?php echo htmlspecialchars($ex['nombre_evento']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($ex['descripcion']); ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($ex['fecha']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora</label>
                            <input type="time" name="hora" class="form-control" value="<?php echo htmlspecialchars($ex['hora']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Precio por Persona (€)</label>
                            <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo htmlspecialchars($ex['precio']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aforo Máximo</label>
                            <input type="number" name="aforo" class="form-control" value="<?php echo htmlspecialchars($ex['aforo_maximo']); ?>" min="1" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Imagen de la Experiencia</label>
                        <div class="d-flex align-items-center gap-3 p-3 border rounded bg-white bloque-imagen-admin">
                            <img src="../img/<?php echo !empty($ex['imagen']) ? htmlspecialchars($ex['imagen']) : 'placeholder.jpg'; ?>" class="preview-admin-img rounded shadow-sm" alt="Imagen actual de la experiencia">
                            <div class="flex-grow-1">
                                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                                <div class="form-text small text-muted">Selecciona una nueva foto solo si quieres cambiar la actual.</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-vino-admin w-100 py-2 fw-bold">
                        ACTUALIZAR DATOS DE LA CATA
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer-admin mt-5">
        <div class="container-fluid text-center">
            <p class="mb-0 small text-muted">&copy; 2026 Vinos Riverview - Gestión de Experiencias</p>
        </div>
    </footer>
</body>
</html>
