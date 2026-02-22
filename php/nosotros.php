<?php
// 1. INICIAR SESIÓN (Para que funcione el menú de usuario y carrito)
session_start();

// Calcular total de productos para el numerito del carrito
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - Vinos Riverview</title>
    
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <link href="../css/nosotros.css" rel="stylesheet"> 
    

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



                    <?php if (!isset($_SESSION['usuario_id'])): ?> <!-- Te redirige a login y despues de loguearte te redirige a la página desde la que accedes -->
                        <a href="./login.php?volver=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-dark">
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
                                        <a class="dropdown-item fw-bold text-vino" href="../admin/panel.php">
                                            <i class="bi bi-speedometer2 me-2"></i> Panel de Control
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?> <!-- Abre el menu de usuario / cliente. -->
                                <li><a class="dropdown-item" href="./perfil.php">Mi Perfil</a></li>
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
                                <a class="nav-link" href="./catas.php">Experiencias / Catas</a>
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

    <main>

        <section class="hero-section text-center">
            <div class="container">
                <h1 class="display-4 fw-light text-vino">Nuestra pasión, tu copa</h1>
                <p class="lead text-muted mx-auto">
                    En Vinos Riverview no solo vendemos vino; compartimos historias, tradición y el sabor auténtico de nuestra tierra.
                </p>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="row align-items-center g-5 mb-5">
                    <div class="col-md-6">
                        <img src="../img/bodega.jpg" alt="Nuestra Bodega" class="img-fluid rounded-3 shadow-sm">
                    </div>
                    <div class="col-md-6">
                        <h3 class="text-vino2 mb-4">De la viña a tu mesa</h3>
                        <p class="text-secondary">
                            Todo comenzó hace más de 30 años en los valles de La Rioja. Lo que empezó como un pequeño viñedo familiar se ha convertido hoy en Vinos Riverview, un punto de encuentro para los amantes del buen comer y el buen beber.
                        </p>
                        <p class="text-secondary">
                            Creemos que un buen vino nunca debe beberse solo. Por eso, hemos recorrido el país buscando los mejores acompañantes: quesos artesanales curados con paciencia y embutidos de la más alta calidad.
                        </p>
                        <p class="text-secondary">
                            Nuestro compromiso es sencillo: <em>Si no lo pondríamos en nuestra mesa, no lo vendemos en nuestra tienda.</em>
                        </p>
                        <div class="contenedor-imagen mt-4">
                            <img src="../img/firma.jpg"  class ="imagen img-fluid" alt="Firma Fundador" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 mb-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="h3 text-vino">Conoce al Equipo</h2>
                    <p class="text-muted">Las caras detrás de Riverview</p>
                </div>

                <div class="row g-4 justify-content-center">
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card border-0 text-center">
                            <img src="../img/fundador.jpg" class="card-img-top rounded-circle mx-auto mt-3 shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" alt="Fundador">
                            <div class="card-body">
                                <h5 class="card-title h6">Carlos Riverview</h5>
                                <p class="card-text text-muted small">Fundador & Sommelier</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card border-0 text-center">
                            <img src="../img/quesera.jpg" class="card-img-top rounded-circle mx-auto mt-3 shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" alt="Gerente">
                            <div class="card-body">
                                <h5 class="card-title h6">Elena García</h5>
                                <p class="card-text text-muted small">Maestra Quesera</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card border-0 text-center">
                            <img src="../img/logistica.jpg" class="card-img-top rounded-circle mx-auto mt-3 shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" alt="Logística">
                            <div class="card-body">
                                <h5 class="card-title h6">Javier López</h5>
                                <p class="card-text text-muted small">Jefe de Logística</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="seccion-iconos py-5">
            <div class="container text-center">
                <div class="row g-4">
                    
                    <div class="col-md-4">
                        <div class="icon-box rounded-circle shadow-sm">
                            <i class="bi bi-award"></i>
                        </div>
                        <h4 class="h5">Selección Premium</h4>
                        <p class="text-muted small">Cada botella y cada queso ha sido catado y seleccionado personalmente por nuestros expertos.</p>
                    </div>

                    <div class="col-md-4">
                        <div class="icon-box rounded-circle shadow-sm">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h4 class="h5">Envío Seguro</h4>
                        <p class="text-muted small">Embalaje especial anti-roturas y envíos refrigerados para los productos frescos.</p>
                    </div>

                    <div class="col-md-4">
                        <div class="icon-box rounded-circle shadow-sm">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h4 class="h5">Atención Cercana</h4>
                        <p class="text-muted small">¿Dudas con el maridaje? Escríbenos. Te aconsejamos como si fueras un amigo de la familia.</p>
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

<script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>