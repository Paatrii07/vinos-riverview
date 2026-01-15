<?php
// 1. Iniciar sesión (Obligatorio para que funcionen los menús inteligentes)
session_start();

// 2. Conexión a Base de Datos (Manual, como la tienes configurada)
$url = 'mysql:dbname=vinos_riverview;host=localhost';
$user = 'root';
$pass = "";

try {
    $conexion = new PDO($url, $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Fallo la conexión: " . $e->getMessage();
}

// 3. Consulta de productos para los articulos destacados
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
    <link href="./css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="./css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <link href="./css/inicio.css" rel="stylesheet">
    <title>Inicio - Vinos Riverview</title>
</head>
<body>
        <header>
    <nav class="navbar bg-white fixed-top">
        <div class="container-fluid position-relative">
            
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
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
                    <i class="bi bi-search" style="font-size: 1.5rem;"></i>
                </a>

                <?php if (!isset($_SESSION['usuario_id'])): ?>
                    
                    <a href="./php/login.php" class="text-dark">
                        <i class="bi bi-person" style="font-size: 1.5rem;"></i>
                    </a>

                <?php else: ?>
                    
                    <div class="dropdown">
                        <a href="#" class="text-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-fill" style="font-size: 1.5rem; color: #722F37;"></i>
                        </a>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><h6 class="dropdown-header">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador'): ?>
                                <li>
                                    <a class="dropdown-item fw-bold text-vino" href="./admin/panel.php">
                                        <i class="bi bi-speedometer2 me-2"></i> Panel de Control
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="./php/perfil.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <li><a class="dropdown-item text-danger" href="./php/logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>

                <?php endif; ?>
                <a href="carrito.php" class="text-dark">
                    <i class="bi bi-cart" style="font-size: 1.5rem;"></i>
                </a>
            </div>
        
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php">Inicio</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Tienda</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Vinos</a></li>
                                <li><a class="dropdown-item" href="#">Quesos</a></li>
                                <li><a class="dropdown-item" href="#">Embutidos</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Experiencias / Catas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Sobre Nosotros</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Contacto</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="collapse bg-white shadow-sm buscador-superior" id="searchBar">
        <div class="container py-3">
            <form action="tienda.php" method="GET" class="d-flex justify-content-center">
                <div class="input-group w-50">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Buscar vino, queso..." style="box-shadow: none;">
                    <button class="btn btn-vino" type="submit">BUSCAR</button>
                </div>
            </form>
        </div>
    </div>

</header>

<section class="inicio d-flex align-items-center">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-1 fw-light mb-4">Tradición y Sabor</h1>
                <p class="lead mb-5">Descubre nuestra selección exclusiva de vinos, quesos y embutidos artesanales. El placer de la buena mesa, directo a tu casa.</p>
                <a href="tienda.php" class="btn btn-vino">VER CATÁLOGO</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        
        <div class="text-center mb-5">
            <h2 class="display-6 fw-light text-vino">Nuestra Selección</h2>
            <p class="text-muted small text-uppercase" style="letter-spacing: 2px;">Favoritos del Sommelier</p>
        </div>

        <div class="row">
            
            <?php 
            // BUCLE PDO: Mientras haya productos en la sentencia...
            while($fila = $sentencia->fetch(PDO::FETCH_ASSOC)) { 
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100 border-0">
                        <div class="img-wrapper">
                            <img src="./img/<?php echo $fila['imagen_url']; ?>" class="card-img-top" alt="<?php echo $fila['nombre']; ?>">
                        </div>
                        
                        <div class="card-body text-center mt-3">
                            <h5 class="card-title fw-normal"><?php echo $fila['nombre']; ?></h5>
                            <p class="fw-bold text-vino fs-5"><?php echo $fila['precio_unidad']; ?>€</p>
                            <a href="#" class="btn btn-outline-vino btn-sm px-4 rounded-0">Ver Detalle</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
    </div>
</section>

<section class="py-5 section-promo" style="background-color: #F9F7F2;">
    <div class="container">
        <div class="row align-items-center">
            
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="promo-img-container">
                    <img src="./img/experienciaInicio.jpeg" alt="Experiencia de Cata" class="img-fluid w-100 shadow-sm">
                </div>
            </div>

            <div class="col-md-6 ps-md-5">
                <h3 class="fw-light text-vino mb-3">Vive la Experiencia Riverview</h3>
                <p class="text-muted mb-4 fw-light" style="font-size: 1.1rem;">
                    No solo vendemos vino, creamos recuerdos. Ven a visitar nuestros viñedos al atardecer y descubre el proceso artesanal detrás de cada botella.
                </p>
                <a href="#" class="link-experiencia text-decoration-none text-uppercase small fw-bold text-vino">
                    Reservar visita guiada <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>

        </div>
    </div>
</section>

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
                <p><a href="tienda.php" class="footer-link">Tienda</a></p>
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