<?php
// 1. INICIAR SESIÓN Y CONEXIÓN
session_start();
$url_db = 'mysql:dbname=vinos_riverview;host=localhost';
$user_db = 'root';
$pass_db = "";

// Variable para controlar si encontramos el producto
$producto = null;

try {
    $conexion = new PDO($url_db, $user_db, $pass_db);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. VALIDAR ID
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = $_GET['id'];
        
        // Consultamos el producto específico
        $sql = "SELECT * FROM producto WHERE id_producto = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Si no hay ID o no existe el producto, redirigimos a la tienda
    if (!$producto) {
        header('Location: tienda.php');
        exit;
    }

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $producto['nombre']; ?> - Vinos Riverview</title>
    
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
    <link href="../css/producto_detalle.css" rel="stylesheet">
</head>
<body>

    <header>
        <nav class="navbar bg-white fixed-top">
            <div class="container-fluid position-relative">
                
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Abrir menú de navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="index.php">
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
                        <a href="./php/login.php" class="text-dark">
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
                        <i class="bi bi-cart icon-nav"></i>
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

    <main class="container mb-5" style="margin-top: 140px;">
        
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none text-muted">Inicio</a></li>
                <li class="breadcrumb-item"><a href="tienda.php" class="text-decoration-none text-muted">Tienda</a></li>
                <li class="breadcrumb-item active text-vino" aria-current="page"><?php echo $producto['nombre']; ?></li>
            </ol>
        </nav>

        <article class="row align-items-center g-5">
            
            <div class="col-md-6">
                <figure class="text-center p-5 bg-light rounded-3 shadow-sm m-0">
                    <img src="../img/<?php echo $producto['imagen_url']; ?>" 
                        class="img-fluid" 
                        alt="<?php echo $producto['nombre']; ?>" 
                        style="max-height: 500px; object-fit: contain;">
                </figure>
            </div>

            <section class="col-md-6">
                <header class="mb-4">
                    <h1 class="display-4 fw-light text-vino"><?php echo $producto['nombre']; ?></h1>
                    <p class="text-muted text-uppercase small ls-2">Ref: <?php echo $producto['id_producto']; ?> | Categoría ID: <?php echo $producto['id_categoria']; ?></p>
                </header>

                <div class="precio-block mb-4">
                    <span class="display-5 fw-bold text-dark"><?php echo number_format($producto['precio_unidad'], 2, ',', '.'); ?>€</span>
                    <span class="text-muted fs-5 ms-2">/ unidad</span>
                </div>

                <div class="descripcion mb-5">
                    <h2 class="h5 text-vino border-bottom pb-2 mb-3">Descripción</h2>
                    <p class="text-secondary lead fs-6">
                        <?php echo $producto['descripcion']; ?>
                    </p>
                    <p class="text-muted small">
                        Un producto de excelente calidad seleccionado por nuestros expertos sommeliers para garantizar la mejor experiencia en tu mesa.
                    </p>
                </div>

                <article class="compra-actions">
                    <form action="../carrito.php" method="GET" class="d-flex gap-3">
                        <input type="hidden" name="add" value="<?php echo $producto['id_producto']; ?>">
                        
                        <div class="input-group w-auto">
                            <span class="input-group-text bg-white border-end-0">Cant:</span>
                            <input type="number" name="cantidad" value="1" min="1" class="form-control text-center border-start-0" style="width: 70px;">
                        </div>

                        <button type="submit" class="btn btn-vino btn-lg flex-grow-1">
                            AÑADIR AL CARRITO
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <a href="tienda.php" class="text-muted small text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Seguir comprando
                        </a>
                    </div>
                </article>
            </section>

        </article>

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
</body>
</html>
