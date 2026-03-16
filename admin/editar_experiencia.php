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

    // Cargar datos actuales de la cata
    $stmt = $conexion->prepare("SELECT * FROM cata WHERE id_visita = ?");
    $stmt->execute([$id]);
    $ex = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ex) { 
        header("Location: panel.php"); 
        exit(); 
    }

    // 3. PROCESAR EL FORMULARIO AL GUARDAR
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = $_POST['nombre_evento'];
        $desc = $_POST['descripcion'];
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $precio = $_POST['precio'];
        $aforo = $_POST['aforo'];

        // Lógica de la imagen (igual que en productos)
        $foto = $ex['imagen']; // Mantenemos la foto actual por defecto
        
        if (!empty($_FILES['foto']['name'])) {
            $nombre_foto = time() . "_" . $_FILES['foto']['name'];
            $ruta_temporal = $_FILES['foto']['tmp_name'];
            $destino = "../img/" . $nombre_foto;
            
            if (move_uploaded_file($ruta_temporal, $destino)) {
                $foto = $nombre_foto; // Actualizamos con el nuevo nombre
            }
        }

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
} catch(PDOException $e) {
    die("Error técnico: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Experiencia - Vinos Riverview</title>
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .btn-vino-admin { background-color: #640D14; color: white; }
        .btn-vino-admin:hover { background-color: #4a0a0f; color: white; }
    </style>
</head>
<body class="bg-light py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="panel.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Cancelar y Volver
                </a>
            </div>

            <form method="POST" enctype="multipart/form-data" class="card shadow-sm border-0 p-4">
                <h3 class="fw-light mb-4 border-bottom pb-2">Editar Experiencia: <?php echo htmlspecialchars($ex['nombre_evento']); ?></h3>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nombre del Evento</label>
                    <input type="text" name="nombre_evento" class="form-control" value="<?php echo htmlspecialchars($ex['nombre_evento']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($ex['descripcion']); ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?php echo $ex['fecha']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Hora</label>
                        <input type="time" name="hora" class="form-control" value="<?php echo $ex['hora']; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Precio por Persona (€)</label>
                        <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $ex['precio']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Aforo Máximo</label>
                        <input type="number" name="aforo" class="form-control" value="<?php echo $ex['aforo_maximo']; ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Imagen de la Experiencia</label>
                    <div class="d-flex align-items-center gap-3 p-3 border rounded bg-white">
                        <img src="../img/<?php echo $ex['imagen'] ?: 'placeholder.jpg'; ?>" width="100" class="rounded shadow-sm">
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
</div>

</body>
</html>