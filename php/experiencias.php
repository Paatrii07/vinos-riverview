<?php
// 1. INICIAR SESIÓN 
session_start();
require_once '../config.php';

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

try {
    // Usamos las constantes que definimos en config.php
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage()); // Log secreto
    die("Error de conexión. Inténtelo más tarde."); // Mensaje genérico para el usuario
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online - Vinos Riverview</title>
    
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
    <link href="../css/tienda.css" rel="stylesheet">
</head>
<body>

    <header>
        <nav class="navbar bg-white fixed-top">
            <div class="container-fluid position-relative">
                
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Abrir menú de navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="../index.php">
                    <img src="../img/logo.png" alt="Vinos Riverview" height="102">
                </a>

                <div class="d-flex gap-3 align-items-center">
                    
                    <a href="#" class="text-dark" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#searchBar" 
                        aria-expanded="false" 
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' });">
                        <i class="bi bi-search icon-nav"></i>
                    </a>

                    <?php if (!isset($_SESSION['usuario_id'])): ?>
                        <a href="./login.php?volver=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-dark">
                            <i class="bi bi-person icon-nav"></i>
                        </a>
                    <?php else: ?>
                        <div class="dropdown">
                            <a href="#" class="text-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-fill icon-nav-user"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h6></li>
                                <li><hr class="dropdown-divider"></li>
                               <?php if ($_SESSION['rol'] === 'administrador'): ?>
                                        <li><a class="dropdown-item fw-bold text-vino" href="../admin/panel.php">Panel de Control</a></li>

                                    <?php elseif ($_SESSION['rol'] === 'cliente'): ?>
                                        <li><a class="dropdown-item" href="./perfil.php">Mi Perfil</a></li>
                                    <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="./logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <a href="./carrito.php" class="text-dark position-relative text-decoration-none">
                        <i class="bi bi-cart icon-nav" style="font-size: 1.5rem;"></i>
                        <?php if ($total_cesta > 0): ?>
                           <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-vino-carrito">
                                <?php echo $total_cesta; ?>
                                <span class="visually-hidden">productos</span>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            
                <aside class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"></button>
                    </div>
                    
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="../index.php">Inicio</a>
                            </li>
                            
                            <li class="nav-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link w-100" href="./tienda.php">Tienda</a>
                                    <a class="nav-link px-3" href="#menu-tienda" role="button" 
                                        data-bs-toggle="collapse" aria-expanded="false" aria-controls="menu-tienda">
                                        <i class="bi bi-chevron-down small"></i>
                                    </a>
                                </div>

                                <div class="collapse" id="menu-tienda">
                                    <ul class="nav flex-column ps-4 border-start ms-2 my-1 bg-light bg-opacity-25">
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="./tienda.php?categoria=vinos">Vinos</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="./tienda.php?categoria=quesos">Quesos</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="./tienda.php?categoria=embutidos">Embutidos</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="./experiencias.php">Experiencias / Catas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="./nosotros.php">Sobre Nosotros</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="./contacto.php">Contacto</a>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </nav>

        <div class="collapse bg-white shadow-sm buscador-superior" id="searchBar">
            <div class="container py-4"> <form action="./tienda.php" method="GET" class="d-flex justify-content-center align-items-center gap-2">
                    
                    <input type="text" name="q" class="form-control input-busqueda" placeholder="Buscar producto...">
                    
                    <button class="btn btn-lupa" type="submit">
                        <i class="bi bi-search"></i>
                    </button>

                </form>
            </div>
        </div>

    </header>


<?php
// 1. Consultar las experiencias de la base de datos
try {
    $query = "SELECT * FROM cata";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $experiencias = []; // Si falla, evitamos que la página se rompa
}
?>
<div class="container mb-5">
    <div class="row text-center">
        <div class="col-12">
            <h2 class="fw-light text-vino display-5">Nuestras Experiencias</h2>
            <hr class="mx-auto" style="width: 50px; border-top: 2px solid #722F37;">
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <?php if (empty($experiencias)): ?>
            <div class="col-12 text-center">
                <p class="text-muted">Próximamente nuevas experiencias disponibles.</p>
            </div>
        <?php else: ?>
            <?php foreach ($experiencias as $exp): ?>
                <div class="col-md-6 col-lg-4 d-flex justify-content-center">
                    <div class="card border-1 shadow-sm card-experiencia px-0 w-100">
                        <div class="position-relative">
                            <img src="../img/<?php echo $exp['imagen']; ?>" 
                                 onerror="this.src='../img/cata-fondo.jpg'" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($exp['nombre_evento']); ?>">
                            
                            <?php if($exp['id_visita'] == 1): ?>
                                <span class="badge bg-vino position-absolute top-0 end-0 m-3">Más Popular</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold text-vino"><?php echo htmlspecialchars($exp['nombre_evento']); ?></h5>
                            
                            <p class="text-muted small mb-2">
                                <i class="bi bi-calendar-event me-1"></i> <?php echo date('d/m/Y', strtotime($exp['fecha'])); ?> | 
                                <i class="bi bi-clock me-1"></i> <?php echo $exp['hora']; ?>
                            </p>
                            
                            <p class="card-text flex-grow-1">
                                <?php echo htmlspecialchars($exp['descripcion']); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <span class="fs-4 fw-bold text-dark">
                                    <?php echo number_format($exp['precio'], 2); ?>€ 
                                    <small class="fs-6 text-muted">/ pers.</small>
                                </span>

                                <?php if (!isset($_SESSION['usuario_id'])): ?>
                                        <a href="login.php?volver=experiencias.php" class="btn btn-outline-vino">
                                            Reservar
                                        </a>
                                <?php else: ?>
                                <button type="button" 
                                        class="btn btn-outline-vino" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalReserva" 
                                        data-id="<?php echo $exp['id_visita']; ?>" 
                                        data-nombre="<?php echo htmlspecialchars($exp['nombre_evento']); ?>">
                                    Reservar
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-bottom-vino">
                <h5 class="modal-title fw-light text-uppercase tracking-wider" id="modalReservaLabel" style="letter-spacing: 2px;">
                    Confirmar Experiencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="reservar_proceso.php" method="POST">
                <div class="modal-body p-4">
                    <h4 id="nombreCataModal" class="text-vino fw-bold mb-4"></h4>
                    
                    <input type="hidden" name="id_visita" id="idVisitaModal">
                    
                    <div class="mb-4">
                        <label for="num_personas" class="form-label text-muted small text-uppercase fw-bold">Número de asistentes</label>
                        <div class="input-group custom-input-group">
                            <span class="input-group-text bg-light border-end-0 text-vino">
                                <i class="bi bi-people-fill"></i>
                            </span>
                            <input type="number" name="num_personas" id="num_personas" 
                                   class="form-control border-start-0 bg-light focus-vino" 
                                   value="1" min="1" max="10" required>
                        </div>
                        <div class="form-text mt-2 small text-muted italic">
                            * Máximo 10 personas por reserva online. Para grupos mayores, contáctenos.
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pb-4 justify-content-center">
                    <button type="button" class="btn btn-link text-muted text-decoration-none px-4" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-vino px-5 py-2 text-uppercase fw-bold" style="letter-spacing: 1px;">
                        Confirmar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-center">
            <div class="modal-body p-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h3 class="fw-bold text-vino">¡Reserva Confirmada!</h3>
                <p class="text-muted">Tu plaza para la experiencia ha sido registrada correctamente. Te esperamos para brindar juntos.</p>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="perfil.php#experiencias" class="btn btn-vino">Ver mis reservas</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Continuar explorando</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- LÓGICA 1: RELLENAR EL MODAL DE RESERVA ---
    var modalReserva = document.getElementById('modalReserva');
    if (modalReserva) {
        modalReserva.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var nombre = button.getAttribute('data-nombre');
            
            var modalInputId = modalReserva.querySelector('#idVisitaModal');
            var modalTextNombre = modalReserva.querySelector('#nombreCataModal');
            
            modalInputId.value = id;
            modalTextNombre.textContent = nombre;
        });
    }

    // --- LÓGICA 2: LANZAR EL MODAL DE ÉXITO SI LA URL TIENE EL PARÁMETRO ---
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('reserva_exitosa')) {
        const modalElement = document.getElementById('modalExito');
        if (modalElement) {
            const modalExito = new bootstrap.Modal(modalElement);
            modalExito.show();
            
            // Limpiamos la URL sin recargar para que el mensaje no salga otra vez si pulsan F5
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
        }
    }
});
</script>


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
                <p><a href="./tienda.php" class="footer-link">Tienda</a></p>
                <p><a href="./experiencias.php" class="footer-link">Catas y Eventos</a></p>
                <p><a href="./nosotros.php" class="footer-link">Sobre Nosotros</a></p>
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
                            <a href="http://www.facebook.com" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-facebook"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="http://www.x.com" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-twitter-x"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="http://www.instagram.com" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-instagram"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</footer>