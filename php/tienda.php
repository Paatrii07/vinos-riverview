<?php
// 1. INICIAR SESIÓN Y CONEXIÓN
session_start();
$url_db = 'mysql:dbname=vinos_riverview;host=localhost';
$user_db = 'root';
$pass_db = "";

try {
    $conexion = new PDO($url_db, $user_db, $pass_db);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 2. LÓGICA DE FILTRADO
$mapa_categorias = [
    'vinos'     => 1,
    'quesos'    => 2,
    'embutidos' => 3
];

$categoria_url = isset($_GET['categoria']) ? $_GET['categoria'] : null;
$busqueda      = isset($_GET['q']) ? $_GET['q'] : null;
$id_categoria  = null;

$sql = "SELECT * FROM producto WHERE 1=1"; 

if ($categoria_url && array_key_exists($categoria_url, $mapa_categorias)) {
    $id_categoria = $mapa_categorias[$categoria_url];
    $sql .= " AND id_categoria = :id_cat";
}

if ($busqueda) {
    $sql .= " AND (nombre LIKE :busqueda OR descripcion LIKE :busqueda)";
}

$stmt = $conexion->prepare($sql);

if ($id_categoria) {
    $stmt->bindParam(':id_cat', $id_categoria, PDO::PARAM_INT);
}

if ($busqueda) {
    $param_busqueda = "%" . $busqueda . "%"; 
    $stmt->bindParam(':busqueda', $param_busqueda, PDO::PARAM_STR);
}

$stmt->execute();
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
                        <a href="./login.php" class="text-dark">
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
                                <li><a class="dropdown-item" href="./perfil.php">Mi Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="./logout.php">Cerrar Sesión</a></li>
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
                                <a class="nav-link active" aria-current="page" href="../index.php">Inicio</a>
                            </li>
                            
                            <li class="nav-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link w-100" href="tienda.php">Tienda</a>
                                    <a class="nav-link px-3" href="#menu-tienda" role="button" 
                                        data-bs-toggle="collapse" aria-expanded="false" aria-controls="menu-tienda">
                                        <i class="bi bi-chevron-down small"></i>
                                    </a>
                                </div>

                                <div class="collapse" id="menu-tienda">
                                    <ul class="nav flex-column ps-4 border-start ms-2 my-1 bg-light bg-opacity-25">
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="../php/tienda.php?categoria=vinos">Vinos</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="../php/tienda.php?categoria=quesos">Quesos</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-1" href="../php/tienda.php?categoria=embutidos">Embutidos</a>
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
            <div class="container py-4"> <form action="./tienda.php" method="GET" class="d-flex justify-content-center align-items-center gap-2">
                    
                    <input type="text" name="q" class="form-control input-busqueda" placeholder="Buscar producto...">
                    
                    <button class="btn btn-lupa" type="submit">
                        <i class="bi bi-search"></i>
                    </button>

                </form>
            </div>
        </div>

    </header>

    <main class="container mb-5" style="margin-top: 140px;">
        <div class="row">
            
            <aside class="col-lg-3 mb-4">
                <div class="p-4 bg-white shadow-sm rounded">
                    <h2 class="h5 mb-4 text-vino border-bottom pb-2">CATEGORÍAS</h2>
                    
                    <nav> 
                        <a href="tienda.php" class="sidebar-link <?php echo (!$categoria_url) ? 'active' : ''; ?>">
                            <i class="bi bi-grid-fill me-2"></i> Todos los productos
                        </a>
                        
                        <a href="tienda.php?categoria=vinos" class="sidebar-link <?php echo ($categoria_url == 'vinos') ? 'active' : ''; ?>">
                            <i class="bi bi-droplet-fill me-2"></i> Vinos
                        </a>
                        
                        <a href="tienda.php?categoria=quesos" class="sidebar-link <?php echo ($categoria_url == 'quesos') ? 'active' : ''; ?>">
                            <i class="bi bi-circle-fill me-2"></i> Quesos
                        </a>
                        
                        <a href="tienda.php?categoria=embutidos" class="sidebar-link <?php echo ($categoria_url == 'embutidos') ? 'active' : ''; ?>">
                            <i class="bi bi-hexagon-fill me-2"></i> Embutidos
                        </a>
                    </nav>
                </div>
            </aside>

            <div class="col-lg-9">
                <header class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2 fw-light">
                        <?php 
                            if($busqueda) echo 'Resultados para "' . htmlspecialchars($busqueda) . '"';
                            elseif($categoria_url) echo ucfirst($categoria_url);
                            else echo "Catálogo Completo";
                        ?>
                    </h1>
                    <span class="text-muted"><?php echo $stmt->rowCount(); ?> productos</span>
                </header>

                <div class="row g-4">
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($prod = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="col-md-6 col-lg-4">
                                
                                <article class="card h-100 border-0 shadow-sm product-card">
                                    <div class="position-relative img-wrapper">
                                        <a href="producto_detalle.php?id=<?php echo $prod['id_producto']; ?>">
                                            <img src="../img/<?php echo $prod['imagen_url']; ?>" class="card-img-top" alt="Imagen de <?php echo $prod['nombre']; ?>">
                                        </a>
                                    </div>
                                    
                                    <div class="card-body text-center">
                                        <h3 class="card-title fw-normal h5"><?php echo $prod['nombre']; ?></h3>
                                        
                                        <p class="text-muted small">
                                            <?php echo substr($prod['descripcion'], 0, 50) . '...'; ?>
                                        </p>
                                        
                                        <p class="precio-grande my-2">
                                            <?php echo number_format($prod['precio_unidad'], 2, ',', '.'); ?>€
                                        </p>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="producto_detalle.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-outline-vino btn-sm">
                                                Ver Detalle
                                            </a>
                                            <button class="btn btn-vino btn-sm">Añadir</button>
                                        </div>
                                    </div>
                                </article>

                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <h3 class="text-muted fw-light">No encontramos productos con ese filtro.</h3>
                            <a href="tienda.php" class="btn btn-vino mt-3">Ver todos</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
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
