<?php

// 1. Iniciar sesión
session_start();
require_once './config.php';

// Calcular total de productos para el recuento de productos en el icono del carrito
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}



try {
    // Intentamos conectar usando PDO (PHP Data Objects)
    // PDO es más seguro y permite usar sentencias preparadas
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    // Configuramos PDO para que nos avise si hay errores (Excepciones)
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    error_log("Error de conexión: " .$e->getMessage()); // Log secreto
    // Si falla la conexión, capturamos el error y paramos todo para no mostrar datos sensibles
    echo "Error de conexión. Inténtalo más tarde.";
    exit; // Para que no intente ejecutar el resto si falla la conexion.
}


// 3. Consulta de productos para los destacados (Limitado a 3)
$sql = "SELECT * FROM producto LIMIT :limite";
$sentencia = $conexion->prepare($sql);

$limite = 3; 
$sentencia->bindParam(':limite', $limite, PDO::PARAM_INT);

$sentencia->execute();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Vinos Riverview</title>
    
    <link href="./css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="./css/inicio.css" rel="stylesheet">
    
    
</head>
<body>

    <header>
        <nav class="navbar bg-white fixed-top">
            <div class="container-fluid position-relative">
                
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Abrir menú de navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="index.php">
                    <img src="./img/logo.png" alt="Vinos Riverview" height="102">
                </a>

                <div class="d-flex gap-3 align-items-center">
                    
                    <a href="#" class="text-dark" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#searchBar" 
                        aria-expanded="false" 
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' });">
                        <i class="bi bi-search icon-nav"></i>
                    </a>



                    <?php if (!isset($_SESSION['usuario_id'])): ?> <!-- Te redirige a login y despues de loguearte te redirige a la página desde la que accedes -->
                        <a href="./php/login.php?volver=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-dark">
                            <i class="bi bi-person icon-nav"></i>
                        </a>
                    <?php else: ?> <!-- (Si SÍ hay usuario): Muestras un menú desplegable con su nombre ($_SESSION['nombre']), enlace a "Mi Perfil" y "Cerrar Sesión". -->
                        <div class="dropdown">
                            <a href="#" class="text-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-fill icon-nav-user"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h6></li> <!-- recuperamos el nombre del usuario guardado en la sesión cuando hizo login. el caracter htmlspecialchars evita, ataques. Convierte caracteres especiales en entidades HTML -->
                                <li><hr class="dropdown-divider"></li>
                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador'): ?> <!-- ¿Existe la variable rol Y ADEMÁS es igual a 'administrador'?". Si -> te lleva al panel de administrador. No-> te lleva al menu de cliente. -->
                                    <li>
                                        <a class="dropdown-item fw-bold text-vino" href="./admin/panel.php">
                                            <i class="bi bi-speedometer2 me-2"></i> Panel de Control
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?> <!-- Abre el menu de usuario / cliente. -->
                                <li><a class="dropdown-item" href="./php/perfil.php">Mi Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="./php/logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <a href="./php/carrito.php" class="text-dark position-relative text-decoration-none">
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
                                <a class="nav-link active" aria-current="page" href="index.php">Inicio</a>
                            </li>
                            
                            <li class="nav-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link w-100" href="./php/tienda.php">Tienda</a>
                                    <a class="nav-link px-3" href="#menu-tienda" role="button" 
                                        data-bs-toggle="collapse" aria-expanded="false" aria-controls="menu-tienda">
                                        <i class="bi bi-chevron-down small"></i>
                                    </a>
                                </div>

                                <div class="collapse" id="menu-tienda">
                                    <ul class="nav flex-column ps-4 border-start ms-2 my-1 bg-light bg-opacity-25">
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="./php/tienda.php?categoria=vinos">Vinos</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="./php/tienda.php?categoria=quesos">Quesos</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="./php/tienda.php?categoria=embutidos">Embutidos</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="./php/experiencias.php">Experiencias / Catas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="./php/nosotros.php">Sobre Nosotros</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="./php/contacto.php">Contacto</a>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </nav>

        <div class="collapse bg-white shadow-sm buscador-superior" id="searchBar">
            <div class="container py-4"> <form action="./php/tienda.php" method="GET" class="d-flex justify-content-center align-items-center gap-2">
                    
                    <input type="text" name="q" class="form-control input-busqueda" placeholder="Buscar producto...">
                    
                    <button class="btn btn-lupa" type="submit">
                        <i class="bi bi-search"></i>
                    </button>

                </form>
            </div>
        </div>

    </header>

    <main>
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="container mt-4">
                
                <?php if ($_GET['mensaje'] == 'cuenta_eliminada'): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>¡Cuenta eliminada!</strong> Tu cuenta y todos tus datos han sido borrados correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>

                <?php elseif ($_GET['mensaje'] == 'error_eliminar'): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Error:</strong> No se pudo eliminar la cuenta. Por favor, contacta con soporte.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <section class="inicio d-flex align-items-center">
            <div class="container text-center">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h1 class="display-1 fw-light mb-4">Tradición y Sabor</h1>
                        <p class="lead mb-5">Descubre nuestra selección exclusiva de vinos, quesos y embutidos artesanales. El placer de la buena mesa, directo a tu casa.</p>
                        <a href="./php/tienda.php" class="btn btn-vino">VER CATÁLOGO</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-white">
            <div class="container">
                
                <header class="text-center mb-5">
                    <h2 class="display-6 fw-light text-vino">Nuestra Selección</h2>
                    <p class="text-muted small text-uppercase fw-bold-spacing">Favoritos del Sommelier</p>
                </header>

                <div class="row">
                    <?php while($fila = $sentencia->fetch(PDO::FETCH_ASSOC)) { 
                        $modalID = "modalProducto" . $fila['id_producto'];
                    ?>
                        <div class="col-md-4 mb-4">
                            
                            <article class="card product-card h-100 border-0">
                                <figure class="img-wrapper m-0"> <img src="./img/<?php echo $fila['imagen_url']; ?>" class="card-img-top" alt="Botella de <?php echo $fila['nombre']; ?>">
                                </figure>
                                
                                <div class="card-body text-center mt-3">
                                    <header> <h3 class="card-title fw-normal h5"><?php echo $fila['nombre']; ?></h3>
                                        <p class="fw-bold text-vino fs-5"><?php echo number_format($fila['precio_unidad'], 2, ',', '.'); ?>€</p>
                                    </header>
                                    
                                    <button type="button" class="btn btn-outline-vino btn-sm px-4 rounded-0" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#<?php echo $modalID; ?>">
                                        Ver Detalle
                                    </button>
                                </div>
                            </article>

                            <div class="modal fade" id="<?php echo $modalID; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-0 border-0">
            
            <div class="modal-header border-0">
                <h4 class="modal-title fw-light fs-5"><?php echo $fila['nombre']; ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                
                <article class="row align-items-center">
                    
                    <div class="col-md-6 mb-3 mb-md-0">
                        <figure class="m-0 text-center">
                            <img src="./img/<?php echo $fila['imagen_url']; ?>" class="img-fluid" alt="Vista detallada de <?php echo $fila['nombre']; ?>" style="max-height: 400px; width: 100%; object-fit: contain;">
                        </figure>
                    </div>

                    <section class="col-md-6">
                        <header>
                            <h3 class="fw-normal text-vino mb-2"><?php echo $fila['nombre']; ?></h3>
                            <p class="display-6 fw-bold mb-3"><?php echo number_format($fila['precio_unidad'], 2, ',', '.'); ?>€</p>
                        </header>
                        
                        <div class="descripcion mb-4">
                            <p class="text-muted">
                                <?php echo $fila['descripcion']; ?>
                            </p>
                            
                            <a href="./php/producto_detalle.php?id=<?php echo $fila['id_producto']; ?>" class="text-vino text-decoration-none small fw-bold">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Ver ficha completa del producto
                            </a>
                        </div>
                        
                        <footer class="d-grid gap-2 mt-4">
                            <a href="./php/carrito.php?add=<?php echo $fila['id_producto']; ?>" class="btn btn-vino btn-lg rounded-0">
                                AÑADIR AL CARRITO
                            </a>
    
                            <button type="button" class="btn btn-outline-secondary rounded-0" data-bs-dismiss="modal">
                                Seguir comprando
                            </button>
                        </footer>
                    </section>

                </article>

            </div>
        </div>
    </div>
</div>
                            </div>
                    <?php } ?>
                </div>
            </div>
        </section>

        <section class="py-5 section-promo">
            <div class="container">
                <div class="row align-items-center">
                    
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="promo-img-container">
                            <img src="./img/experienciaInicio.jpeg" alt="Experiencia de Cata en viñedos" class="img-fluid w-100 shadow-sm">
                        </div>
                    </div>

                    <div class="col-md-6 ps-md-5">
                        <h2 class="fw-light text-vino mb-3 h3">Vive la Experiencia Riverview</h2>
                        <p class="text-muted mb-4 fw-light text-promo">
                            No solo vendemos vino, creamos recuerdos. Ven a visitar nuestros viñedos al atardecer y descubre el proceso artesanal detrás de cada botella.
                        </p>
                        <a href="#" class="link-experiencia text-decoration-none text-uppercase small fw-bold text-vino">
                            Reservar visita guiada <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>

                </div>
            </div>
        </section>

    </main>

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
                <p><a href="index.php" class="footer-link">Inicio</a></p>
                <p><a href="./php/tienda.php" class="footer-link">Tienda</a></p>
                <p><a href="./php/experiencias.php" class="footer-link">Catas y Eventos</a></p>
                <p><a href="./php/nosotros.php" class="footer-link">Sobre Nosotros</a></p>
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
                <p class ="derechos">© 2025 <strong>Vinos Riverview</strong>. Todos los derechos reservados.</p>
            </div>

            <div class="col-md-5 col-lg-4">
                <div class="text-center text-md-end">
                    <ul class="rrss list-unstyled list-inline">
                        <li class="list-inline-item">
                            <a href="http://www.facebook.com" class="btn-floating btn-sm"><i class="bi bi-facebook"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="http://www.x.com" class="btn-floating btn-sm"><i class="bi bi-twitter-x"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="http://www.instagram.com" class="btn-floating btn-sm"><i class="bi bi-instagram"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
    
</footer>
    <div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content text-center p-4">
            <div class="mb-3">
                <i class="text-vino bi bi-check-circle display-1"></i>
            </div>
            <h3 class="fw-light mb-2">¡Producto añadido!</h3>
            <p class="text-muted mb-4">Ya tienes este producto en tu cesta.</p>
            
            <div class="d-grid gap-2">
                <a href="php/carrito.php" class="btn btn-vino">
                    IR A LA CESTA
                </a>
                
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Seguir comprando
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modales para añadir a la cesta o continuar comprando -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Miramos si la URL tiene el mensaje secreto "?modal_exito=true"
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('modal_exito')) {
            // Abrimos el modal
            var myModal = new bootstrap.Modal(document.getElementById('modalExito'));
            myModal.show();
            
            // Limpiamos la URL para que no salga otra vez al recargar
            const newUrl = window.location.pathname + window.location.search.replace(/[\?&]modal_exito=true/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, newUrl);
        }
    });
    
</script>
<script src="./css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>