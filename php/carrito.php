<?php
// 1. Iniciar sesión 
session_start();
require_once '../config.php';

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}



try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit();
}

// =======================================================
// 3. LÓGICA DEL CARRITO
// =======================================================

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// A) AÑADIR PRODUCTO (Lógica para mostrar Modal después)

if (isset($_GET['add'])) {
    $id_producto = (int)$_GET['add'];
    
    // 1. CAPTURAMOS LA CANTIDAD (Si no viene nada, usamos 1 por defecto)
    $cantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 1;
    
    // Seguridad: Que no añadan 0 o negativos
    if ($cantidad < 1) { $cantidad = 1; }

    // 2. SUMAMOS AL CARRITO
    if (isset($_SESSION['carrito'][$id_producto])) {
        // Si ya existe, le sumamos la nueva cantidad (ej: tenía 2 + 20 = 22)
        $_SESSION['carrito'][$id_producto] += $cantidad;
    } else {
        // Si no existe, lo creamos con esa cantidad
        $_SESSION['carrito'][$id_producto] = $cantidad;
    }
    
    // ... resto de la lógica de redirección que ya tenías ...
    $origen = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'tienda.php';
    $separador = (strpos($origen, '?') !== false) ? '&' : '?';
    header("Location: " . $origen . $separador . "modal_exito=true");
    exit();
}

// B) BORRAR UN PRODUCTO
if (isset($_GET['borrar'])) {
    $id_producto = (int)$_GET['borrar'];
    unset($_SESSION['carrito'][$id_producto]);
    header('Location: carrito.php');
    exit();
}

// C) VACIAR TODO
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header('Location: carrito.php');
    exit();
}

// D) RESTAR CANTIDAD (Minimo 1)
if (isset($_GET['restar'])) {
    $id_producto = (int)$_GET['restar'];
    
    // Solo restamos si existe y si la cantidad es mayor que 1
    if (isset($_SESSION['carrito'][$id_producto]) && $_SESSION['carrito'][$id_producto] > 1) {
        $_SESSION['carrito'][$id_producto]--;
    }
    
    header('Location: carrito.php');
    exit();
}

// E) SUMAR CANTIDAD
if (isset($_GET['sumar'])) {
    $id_producto = (int)$_GET['sumar'];
    
    if (isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]++;
    }
    
    header('Location: carrito.php');
    exit();
}

// =======================================================
// 4. CONSULTA DATOS
// =======================================================
$productos_carrito = array();
$total_compra = 0;

// Mapa para evitar Warning de categorías
$nombres_categorias = [
    1 => 'Vinos',
    2 => 'Quesos',
    3 => 'Embutidos'
];

if (!empty($_SESSION['carrito'])) {
    $ids = implode(',', array_keys($_SESSION['carrito']));
    $sql = "SELECT * FROM producto WHERE id_producto IN ($ids)";
    $stmt = $conexion->query($sql);
    $productos_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Carrito - Vinos Riverview</title>
    
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/carrito.css" rel="stylesheet"> 
 
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

                        

                    <?php else: ?> <!-- (Si SÍ hay usuario): Muestras un menú desplegable con su nombre ($_SESSION['nombre']), enlace a "Mi Perfil" y "Cerrar Sesión". -->
                        <div class="dropdown">
                            <a href="#" class="text-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-fill icon-nav-user"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h6></li> <!-- recuperamos el nombre del usuario guardado en la sesión cuando hizo login. el caracter htmlspecialchars evita, ataques. Convierte caracteres especiales en entidades HTML -->
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
                                <a class="nav-link active" aria-current="page" href="index.php">Inicio</a>
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


    <main class="container pb-5">
        
        <h1 class="display-5 text-center text-vino mb-5 fw-light">Tu Cesta de Compra</h1>

        <?php if (empty($productos_carrito)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-1 text-vino"></i>
                <p class="lead mt-3 text-vino fw-bold">Tu carrito está vacío.</p>
                <a href="tienda.php" class="btn btn-vino btn-lg mt-3">IR A LA TIENDA</a>
            </div>
        
        <?php else: ?>
            <div class="row g-4 align-items-stretch">
                
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Producto</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productos_carrito as $producto): 
                                            $id = $producto['id_producto'];
                                            $cantidad = $_SESSION['carrito'][$id];
                                            $precio = $producto['precio_unidad'];
                                            $subtotal_linea = $precio * $cantidad;
                                            $total_compra += $subtotal_linea;
                                            
                                            $cat_nombre = isset($nombres_categorias[$producto['id_categoria']]) 
                                                          ? $nombres_categorias[$producto['id_categoria']] 
                                                          : 'Bodega';
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <img src="../img/<?php echo $producto['imagen_url']; ?>" alt="Producto" 
                                                         class="imagenes img-fluid rounded me-3">
                                                    <div>
                                                        <h6 class="mb-0 text-vino"><?php echo $producto['nombre']; ?></h6>
                                                        <small class="text-muted"><?php echo $cat_nombre; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?php echo number_format($precio, 2); ?>€</td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm justify-content-center">
                                                    
                                                    <a href="carrito.php?restar=<?php echo $id; ?>" class="btn btn-outline-secondary">
                                                        <i class="bi bi-dash"></i>
                                                    </a>
                                                    
                                                    <input type="text" class="cuadro-cantidad form-control text-center border-secondary text-secondary bg-white" 
                                                        value="<?php echo $cantidad; ?>" 
                                                        readonly>
                                                    
                                                    <a href="carrito.php?sumar=<?php echo $id; ?>" class="btn btn-outline-secondary">
                                                        <i class="bi bi-plus"></i>
                                                    </a>
                                                    
                                                </div>
                                            </td>
                                            <td class="text-center fw-bold"><?php echo number_format($subtotal_linea, 2); ?>€</td>
                                            <td class="text-end pe-4">
                                                <a href="carrito.php?borrar=<?php echo $id; ?>" class="text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between py-3">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalVaciar">
                                <i class="bi bi-trash3-fill me-1"></i> Vaciar Carrito
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 columna-resumen-ajuste">
                    <div class="card border-0">
                        <div class="card-header text-center py-3">
                            <h5 class="mb-0">Resumen del Pedido</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item d-flex justify-content-between border-0 px-0">
                                    Subtotal <span><?php echo number_format($total_compra, 2); ?>€</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between border-0 px-0">
                                    Envío <span class="text-success">Gratis</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between border-top px-0 mb-3 pt-3">
                                    <strong>TOTAL</strong>
                                    <strong class="fs-4 text-vino"><?php echo number_format($total_compra, 2); ?>€</strong>
                                </li>
                            </ul>
                            <aside class="d-grid">
                                <?php if (isset($_SESSION['usuario_id'])): ?>
                                    <a href="finalizar_pedido.php" class="btn btn-vino btn-lg">FINALIZAR COMPRA Y PAGAR</a>
                                <?php else: ?>
                                    <a href="./login.php?return_to=carrito.php" class="btn btn-warning">
                                        INICIA SESIÓN PARA PAGAR
                                    </a>
                                <?php endif; ?>
                            </aside>
                        </div>
                    </div>
                    <div class="mt-4 text-center text-muted small">
                        <i class="bi bi-shield-lock-fill me-1"></i> Pago 100% Seguro
                    </div>
                </div>
            </div>
        <?php endif; ?>

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
                    <ul class="list-unstyled list-inline">
                        <li class="rrss list-inline-item">
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

    <div class="modal fade" id="modalVaciar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4 border-0 shadow">
                <div class="mb-3">
                    <i class="bi bi-exclamation-circle text-danger display-1"></i>
                </div>
                <h4 class="mb-2 text-vino">¿Vaciar cesta?</h4>
                <p class="text-muted mb-4">Se eliminarán todos los productos. Esta acción no se puede deshacer.</p>
                
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <a href="carrito.php?vaciar=true" class="btn btn-danger px-4">Sí, vaciar</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>