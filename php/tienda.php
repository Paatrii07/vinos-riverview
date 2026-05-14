<?php
// 1. INICIAR SESIÓN
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_title = 'Tienda Online - Vinos Riverview';
$page_css = 'tienda.css';

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

try {
    // Usamos las constantes que definimos en config.php
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión. Inténtelo más tarde.");
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
$total_productos = $stmt->rowCount();
?>

<?php require_once '../includes/header.php'; ?>

    <main class="container mb-5 tienda-main">
        <div class="row">

            <aside class="col-lg-3 mb-4" aria-label="Filtros de categorías">
                <div class="menu-lateral p-4 bg-white shadow-sm rounded sticky-top">
                    <h2 class="h5 mb-4 text-vino border-bottom pb-2">Categorías</h2>

                    <nav class="sidebar-categorias">
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

            <section class="col-lg-9" aria-label="Listado de productos">
                <header class="d-flex justify-content-between align-items-center mb-4 mt-3 tienda-encabezado">
                    <h1 class="h2 fw-light mb-0">
                        <?php
                            if ($busqueda) {
                                echo 'Resultados para "' . htmlspecialchars($busqueda) . '"';
                            } elseif ($categoria_url) {
                                echo ucfirst($categoria_url);
                            } else {
                                echo "Catálogo Completo";
                            }
                        ?>
                    </h1>
                    <p class="text-muted mb-0"><?php echo $total_productos; ?> productos</p>
                </header>

                <div class="row g-4">
                    <?php if ($total_productos > 0): ?>
                        <?php while ($prod = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="col-md-6 col-lg-4">
                                <article class="card h-100 border-0 shadow-sm product-card">
                                    <div class="position-relative img-wrapper text-center border-bottom bg-white">
                                        <a href="producto_detalle.php?id=<?php echo $prod['id_producto']; ?>" class="d-block py-3">
                                            <img
                                                src="../img/<?php echo $prod['imagen_url']; ?>"
                                                alt="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                                class="img-fluid product-image">
                                        </a>
                                    </div>

                                    <div class="card-body d-flex flex-column p-4">
                                        <h2 class="card-title h5 text-vino text-center titulo-fijo">
                                            <?php echo htmlspecialchars($prod['nombre']); ?>
                                        </h2>

                                        <p class="text-muted small text-center mb-3 desc-fija">
                                            <?php echo htmlspecialchars($prod['descripcion']); ?>
                                        </p>

                                        <div class="mt-auto w-100 border-top pt-3 text-center">
                                            <p class="fw-bold fs-4 mb-2 text-dark">
                                                <?php echo number_format($prod['precio_unidad'], 2, ',', '.'); ?>€
                                            </p>

                                            <?php if ((int)$prod['stock_actual'] > 5): ?>
                                                <p class="small text-success fw-semibold mb-3">
                                                    <i class="bi bi-check-circle-fill me-1"></i> En stock
                                                </p>
                                            <?php elseif ((int)$prod['stock_actual'] > 0): ?>
                                                <p class="small text-warning fw-semibold mb-3">
                                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                                    Quedan <?php echo (int)$prod['stock_actual']; ?> unidades
                                                </p>
                                            <?php else: ?>
                                                <p class="small text-danger fw-semibold mb-3">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Sin stock
                                                </p>
                                            <?php endif; ?>

                                            <div class="d-grid gap-2">
                                                <a href="producto_detalle.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-outline-vino btn-sm">
                                                    Ver detalle
                                                </a>

                                                <?php if ((int)$prod['stock_actual'] > 0): ?>
                                                    <a href="carrito.php?add=<?php echo $prod['id_producto']; ?>" class="btn btn-vino btn-sm rounded-0">
                                                        <i class="bi bi-cart-plus me-1"></i> Añadir
                                                    </a>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-secondary btn-sm rounded-0" disabled>
                                                        <i class="bi bi-slash-circle me-1"></i> No disponible
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <h2 class="text-muted fw-light">No encontramos productos con ese filtro.</h2>
                            <a href="tienda.php" class="btn btn-vino mt-3">Ver todos</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </main>



    <div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <div class="mb-3">
                    <i class="text-vino bi bi-check-circle display-1"></i>
                </div>

                <h2 class="fw-light mb-2">¡Producto añadido!</h2>
                <p class="text-muted mb-4">Ya tienes este producto en tu cesta.</p>

                <div class="d-grid gap-2">
                    <a href="carrito.php" class="btn btn-vino">
                        IR A LA CESTA
                    </a>

                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Seguir comprando
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('modal_exito')) {
                var myModal = new bootstrap.Modal(document.getElementById('modalExito'));
                myModal.show();

                const newUrl = window.location.pathname + window.location.search.replace(/[\?&]modal_exito=true/, '').replace(/^&/, '?');
                window.history.replaceState({}, document.title, newUrl);
            }
        });
    </script>

<?php require_once '../includes/footer.php'; ?>
