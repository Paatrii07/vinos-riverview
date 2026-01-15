<?php
// 1. INICIAR SESIÓN Y SEGURIDAD
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ./login.php");
    exit();
}

// 2. CONEXIÓN BBDD
$url = 'mysql:dbname=vinos_riverview;host=localhost';
$user = 'root';
$pass = "";

try {
    $conexion = new PDO($url, $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Fallo la conexión: " . $e->getMessage();
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// 3. PROCESAR ACTUALIZACIÓN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'actualizar_datos') {
    
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $id_usuario = $_SESSION['usuario_id'];

    try {
        $sql_update = "UPDATE usuario 
                       SET nombre = :nom, apellidos = :ape, telefono = :tel, direccion = :dir 
                       WHERE id_usuario = :id";
        
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bindParam(':nom', $nombre);
        $stmt_update->bindParam(':ape', $apellidos);
        $stmt_update->bindParam(':tel', $telefono);
        $stmt_update->bindParam(':dir', $direccion);
        $stmt_update->bindParam(':id', $id_usuario);
        
        if ($stmt_update->execute()) {
            $mensaje = "¡Tus datos se han actualizado correctamente!";
            $tipo_mensaje = "success";
            $_SESSION['nombre'] = $nombre;
        }
    } catch(PDOException $e) {
        $mensaje = "Error al actualizar: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// 4. LEER DATOS
$sql_leer = "SELECT * FROM usuario WHERE id_usuario = :id";
$stmt_leer = $conexion->prepare($sql_leer);
$stmt_leer->bindParam(':id', $_SESSION['usuario_id']);
$stmt_leer->execute();
$datos_usuario = $stmt_leer->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Área Personal - Vinos Riverview</title>
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <link href="../css/perfil.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar bg-white fixed-top shadow-sm">
        <div class="container-fluid position-relative">
            
            <div class="d-flex align-items-center">
                <a href="../index.php" class="text-secondary text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i> Volver a la Tienda
                </a>
            </div>

            <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="../index.php">
                <img src="../img/logo.png" alt="Vinos Riverview" height="102">
            </a>

            <div class="d-flex gap-3 align-items-center">
                <span class="text-muted d-none d-md-block">
                    Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </span>
                
                <a href="logout.php" class="btn btn-outline-vino btn-sm rounded-0 text-uppercase" style="letter-spacing: 1px; font-size: 0.8rem;">
                    Cerrar Sesión
                </a>
            </div>
            
        </div>
    </nav>

    <div class="container pb-5 perfil-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <h2 class="fw-light text-vino m-0">Mi Área Personal</h2>
                    <span class="text-muted small d-md-none">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                </div>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab">
                            <i class="bi bi-person-gear me-2"></i>Mis Datos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button" role="tab">
                            <i class="bi bi-box-seam me-2"></i>Mis Pedidos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center" id="experiencias-tab" data-bs-toggle="tab" data-bs-target="#experiencias" type="button" role="tab">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi me-2" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M2.5.5A.5.5 0 0 1 3 0h10a.5.5 0 0 1 .5.5v1.378c0 1.497-.514 2.93-1.435 4.127l-.003.004-.003.004C11.005 7.418 9.525 8.473 8.5 9.151V13.5h2a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1h2V9.151c-1.025-.678-2.505-1.733-3.56-3.137l-.003-.003-.003-.004C3.014 4.809 2.5 3.376 2.5 1.878V.5Zm1 0v1.378c0 1.218.419 2.413 1.197 3.422.787 1.02 1.952 1.865 2.803 2.478V3.5a.5.5 0 0 1 1 0v4.278c.851-.613 2.016-1.458 2.803-2.478.778-1.009 1.197-2.204 1.197-3.422V.5h-9Z"/>
                            </svg>
                            Mis Experiencias
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    
                    <div class="tab-pane fade show active" id="datos" role="tabpanel">
                        <div class="card border-0 shadow-sm p-4">
                            <form action="perfil.php" method="POST">
                                <input type="hidden" name="accion" value="actualizar_datos">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Nombre</label>
                                        <input type="text" name="nombre" class="form-control" 
                                               value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Apellidos</label>
                                        <input type="text" name="apellidos" class="form-control" 
                                               value="<?php echo htmlspecialchars($datos_usuario['apellidos']); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted small">Email</label>
                                    <input type="email" class="form-control bg-light" 
                                           value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted small">Teléfono</label>
                                    <input type="tel" name="telefono" class="form-control" 
                                           value="<?php echo htmlspecialchars($datos_usuario['telefono'] ?? ''); ?>">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-muted small">Dirección de Envío</label>
                                    <textarea name="direccion" class="form-control" rows="2"><?php echo htmlspecialchars($datos_usuario['direccion'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-outline-vino text-uppercase px-4" style="letter-spacing: 2px;">
                                    Guardar Cambios
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pedidos" role="tabpanel">
                        <div class="card border-0 shadow-sm p-4">
                            <div class="alert alert-info small mb-3">
                                <i class="bi bi-info-circle me-2"></i> Historial de pedidos próximamente disponible.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nº Pedido</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">#RV-2025-001</td>
                                            <td>14/01/2026</td>
                                            <td><span class="badge bg-success">Entregado</span></td>
                                            <td>45.50€</td>
                                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary">Ver</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="experiencias" role="tabpanel">
                        <div class="card border-0 shadow-sm p-4">
                            <h5 class="fw-light mb-4">Próximas Reservas</h5>
                            <div class="card mb-3 border border-light bg-light">
                                <div class="row g-0 align-items-center">
                                    <div class="col-md-2 text-center p-3">
                                        <div class="bg-white rounded p-2 shadow-sm">
                                            <h3 class="m-0 text-vino fw-bold">25</h3>
                                            <small class="text-uppercase text-muted">Ene</small>
                                        </div>
                                    </div>
                                    <div class="col-md-8 p-3">
                                        <h5 class="card-title mb-1">Cata de Vinos Premium</h5>
                                        <p class="card-text text-muted small mb-0">
                                            <i class="bi bi-clock me-1"></i> 18:00H <br>
                                            <i class="bi bi-geo-alt me-1"></i> Bodega Principal
                                        </p>
                                    </div>
                                    <div class="col-md-2 p-3 text-end">
                                        <span class="badge bg-primary">Confirmada</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> </div>
        </div>
    </div>

    <footer class="footer-riverview pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-vino-claro">Vinos Riverview</h5>
                    <p>
                        Tradición, sabor y la mejor selección de nuestra tierra. 
                        Llevamos la excelencia de la bodega directamente a tu mesa.
                    </p>
                </div>

                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-vino-claro">Explorar</h5>
                    <p><a href="../index.php" class="footer-link">Inicio</a></p>
                    <p><a href="../tienda.php" class="footer-link">Tienda</a></p>
                    <p><a href="#" class="footer-link">Catas y Eventos</a></p>
                    <p><a href="#" class="footer-link">Sobre Nosotros</a></p>
                </div>

                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-vino-claro">Contacto</h5>
                    <p><i class="bi bi-house-door-fill me-2"></i> Calle del Vino, 12, La Rioja</p>
                    <p><i class="bi bi-envelope-fill me-2"></i> info@vinosriverview.com</p>
                    <p><i class="bi bi-telephone-fill me-2"></i> +34 912 345 678</p>
                </div>
                
            </div>

            <hr class="mb-4">

            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8">
                    <p>© 2025 <strong>Vinos Riverview</strong>. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="text-center text-md-end">
                        <ul class="list-unstyled list-inline">
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-facebook"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-twitter-x"></i></a>
                            </li>
                            <li class="list-inline-item">
                                <a href="#" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-instagram"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>