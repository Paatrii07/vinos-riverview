<?php
// 1. Iniciar sesión
session_start();
require_once './config.php';

$base_url = '/vinos-riverview';
$page_title = 'Inicio - Vinos Riverview';
$page_css = 'inicio.css';

// Calcular total de productos para el recuento de productos en el icono del carrito
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    echo "Error de conexión. Inténtalo más tarde.";
    exit;
}

// 3. Consulta de productos para los destacados
$sql = "SELECT * FROM producto LIMIT :limite";
$sentencia = $conexion->prepare($sql);

$limite = 3;
$sentencia->bindParam(':limite', $limite, PDO::PARAM_INT);

$sentencia->execute();
?>


<?php require_once './includes/header.php'; ?>

    <main>
        <?php if (isset($_GET['mensaje'])): ?>
            <section class="container mt-4">
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
            </section>
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
                    <?php while ($fila = $sentencia->fetch(PDO::FETCH_ASSOC)) {
                        $modalID = "modalProducto" . $fila['id_producto'];
                    ?>
                        <div class="col-md-4 mb-4">
                            <article class="card product-card h-100 border-0">
                                <figure class="img-wrapper m-0">
                                    <img src="./img/<?php echo $fila['imagen_url']; ?>" class="card-img-top" alt="Botella de <?php echo htmlspecialchars($fila['nombre']); ?>">
                                </figure>

                                <div class="card-body text-center mt-3">
                                    <header>
                                        <h3 class="card-title fw-normal h5"><?php echo htmlspecialchars($fila['nombre']); ?></h3>
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
                                            <h4 class="modal-title fw-light fs-5"><?php echo htmlspecialchars($fila['nombre']); ?></h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>

                                        <div class="modal-body">
                                            <article class="row align-items-center">
                                                <div class="col-md-6 mb-3 mb-md-0">
                                                    <figure class="m-0 text-center">
                                                        <img src="./img/<?php echo $fila['imagen_url']; ?>" class="img-fluid modal-producto-img" alt="Vista detallada de <?php echo htmlspecialchars($fila['nombre']); ?>">
                                                    </figure>
                                                </div>

                                                <section class="col-md-6">
                                                    <header>
                                                        <h3 class="fw-normal text-vino mb-2"><?php echo htmlspecialchars($fila['nombre']); ?></h3>
                                                        <p class="display-6 fw-bold mb-3"><?php echo number_format($fila['precio_unidad'], 2, ',', '.'); ?>€</p>
                                                    </header>

                                                    <div class="descripcion mb-4">
                                                        <p class="text-muted">
                                                            <?php echo htmlspecialchars($fila['descripcion']); ?>
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
                        <a href="./php/experiencias.php" class="link-experiencia text-decoration-none text-uppercase small fw-bold text-vino">
                            Reservar visita guiada <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
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


<?php require_once './includes/footer.php'; ?>