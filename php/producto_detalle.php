<?php
// 1. INICIAR SESIÓN
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_css = 'producto_detalle.css';

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

// Variable para controlar si encontramos el producto
$producto = null;
$mensaje_stock = '';

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. VALIDAR ID Y HACER LA CONSULTA COMPLETA
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int) $_GET['id'];

        $sql = "SELECT p.*, 
               v.graduacion, v.ano_cosecha, v.tipo_uva,
               q.tipo_leche, q.tiempo_curacion AS queso_curacion,
               e.tipo_carne, e.tiempo_curacion AS embutido_curacion
        FROM producto p 
        LEFT JOIN vino v ON p.id_producto = v.id_producto 
        LEFT JOIN queso q ON p.id_producto = q.id_producto
        LEFT JOIN embutido e ON p.id_producto = e.id_producto
        WHERE p.id_producto = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$producto) {
        header('Location: tienda.php');
        exit;
    }

    $page_title = $producto['nombre'] . ' - Vinos Riverview';

    if (isset($_GET['stock_limitado'])) {
        $mensaje_stock = 'No puedes añadir más unidades de las disponibles para este producto.';
    } elseif (isset($_GET['sin_stock'])) {
        $mensaje_stock = 'Este producto no tiene stock disponible en este momento.';
    }

} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    echo "Error de conexión. Inténtalo más tarde.";
    exit;
}
?>

<?php require_once '../includes/header.php'; ?>

    <main class="container-fluid mb-5 detalle-main">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none text-muted">Inicio</a></li>
                <li class="breadcrumb-item"><a href="./tienda.php" class="text-decoration-none text-muted">Tienda</a></li>
                <li class="breadcrumb-item active text-vino" aria-current="page"><?php echo htmlspecialchars($producto['nombre']); ?></li>
            </ol>
        </nav>

        <article class="row align-items-start">
            <div class="col-md-6">
                <div class="detalle-sticky">
                    <figure class="bg-light rounded-3 shadow-sm mt-5">
                        <div class="ratio ratio-1x1">
                            <img src="../img/<?php echo $producto['imagen_url']; ?>"
                                 class="object-fit-contain p-4"
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        </div>
                    </figure>
                </div>
            </div>

            <section class="col-md-6">
                <header class="mb-4">
                    <h1 class="display-4 fw-light text-vino"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                    <p class="text-muted text-uppercase small ls-2">
                        Ref: <?php echo htmlspecialchars($producto['id_producto']); ?> |
                        Categoría ID: <?php echo htmlspecialchars($producto['id_categoria']); ?>
                    </p>
                </header>

                <div class="precio-block mb-3">
                    <span class="display-5 fw-bold text-dark"><?php echo number_format($producto['precio_unidad'], 2, ',', '.'); ?>€</span>
                    <span class="text-muted fs-5 ms-2">/ unidad</span>
                </div>

                <div class="mb-4">
                    <?php if ((int)$producto['stock_actual'] > 5): ?>
                        <p class="small text-success fw-semibold mb-0">
                            <i class="bi bi-check-circle-fill me-1"></i> En stock
                        </p>
                    <?php elseif ((int)$producto['stock_actual'] > 0): ?>
                        <p class="small text-warning fw-semibold mb-0">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>
                            Quedan <?php echo (int)$producto['stock_actual']; ?> unidades
                        </p>
                    <?php else: ?>
                        <p class="small text-danger fw-semibold mb-0">
                            <i class="bi bi-x-circle-fill me-1"></i> Sin stock
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($mensaje_stock)): ?>
                    <div class="alert alert-warning" role="alert">
                        <?php echo htmlspecialchars($mensaje_stock); ?>
                    </div>
                <?php endif; ?>

                <div class="descripcion mb-5">
                    <h2 class="h5 text-vino border-bottom pb-2 mb-3">Descripción</h2>
                    <p class="text-secondary lead fs-6">
                        <?php echo htmlspecialchars($producto['descripcion']); ?>
                    </p>

                    <?php if (!empty($producto['tipo_uva'])): ?>
                        <div class="bg-light p-3 rounded-3 mt-4 border border-light-subtle">
                            <div class="row text-center text-muted">
                                <div class="col-4 border-end">
                                    <small class="d-block text-uppercase fw-bold ls-1 detalle-label">Variedad</small>
                                    <span class="text-dark fw-bold"><?php echo htmlspecialchars($producto['tipo_uva']); ?></span>
                                </div>
                                <div class="col-4 border-end">
                                    <small class="d-block text-uppercase fw-bold ls-1 detalle-label">Cosecha</small>
                                    <span class="text-dark fw-bold"><?php echo htmlspecialchars($producto['ano_cosecha']); ?></span>
                                </div>
                                <div class="col-4">
                                    <small class="d-block text-uppercase fw-bold ls-1 detalle-label">Alcohol</small>
                                    <span class="text-dark fw-bold"><?php echo htmlspecialchars($producto['graduacion']); ?>%</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($producto['tipo_leche'])): ?>
                        <div class="bg-light p-3 rounded-3 mt-4 border border-light-subtle">
                            <div class="row text-center text-muted justify-content-center">
                                <div class="col-5 border-end">
                                    <small class="d-block text-uppercase fw-bold ls-1 detalle-label">Tipo de Leche</small>
                                    <span class="text-dark fw-bold"><?php echo htmlspecialchars($producto['tipo_leche']); ?></span>
                                </div>
                                <div class="col-5">
                                    <small class="d-block text-uppercase fw-bold ls-1 detalle-label">Curación</small>
                                    <span class="text-dark fw-bold"><?php echo htmlspecialchars($producto['queso_curacion']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($producto['tipo_carne'])): ?>
                        <div class="bg-light p-3 rounded-3 mt-4 border border-light-subtle">
                            <div class="row text-center text-muted justify-content-center">
                                <div class="col-5 border-end">
                                    <small class="d-block text-uppercase fw-bold ls-1 detalle-label">Carne</small>
                                    <span class="text-dark fw-bold"><?php echo htmlspecialchars($producto['tipo_carne']); ?></span>
                                </div>
                                <div class="col-5">
                                    <small class="d-block text-uppercase fw-bold ls-1 detalle-label">Curación</small>
                                    <span class="text-dark fw-bold"><?php echo htmlspecialchars($producto['embutido_curacion']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted small mt-3">
                        Un producto de excelente calidad seleccionado por nuestros expertos sommeliers para garantizar la mejor experiencia en tu mesa.
                    </p>
                </div>

                <article class="compra-actions">
                    <?php if ((int)$producto['stock_actual'] > 0): ?>
                        <form action="./carrito.php" method="GET" class="d-flex gap-3">
                            <input type="hidden" name="add" value="<?php echo $producto['id_producto']; ?>">

                            <div class="input-group w-auto">
                                <span class="input-group-text bg-white border-end-0">Cant:</span>
                                <input
                                    type="number"
                                    name="cantidad"
                                    value="1"
                                    min="1"
                                    max="<?php echo (int)$producto['stock_actual']; ?>"
                                    class="form-control text-center border-start-0 input-cantidad-producto">
                            </div>

                            <button type="submit" class="btn btn-vino btn-lg flex-grow-1">
                                AÑADIR AL CARRITO
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="d-grid">
                            <button type="button" class="btn btn-secondary btn-lg" disabled>
                                PRODUCTO NO DISPONIBLE
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3 text-center">
                        <a href="./tienda.php" class="text-muted small text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Seguir comprando
                        </a>
                    </div>
                </article>
            </section>
        </article>
    </main>



    <div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <div class="mb-3">
                    <i class="text-vino bi bi-check-circle display-1"></i>
                </div>
                <h3 class="fw-light mb-2">¡Producto añadido!</h3>
                <p class="text-muted mb-4">Ya tienes este producto en tu cesta.</p>

                <div class="d-grid gap-2">
                    <a href="./carrito.php" class="btn btn-vino">
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
