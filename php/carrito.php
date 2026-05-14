<?php
// 1. Iniciar sesión
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_title = 'Tu carrito - Vinos Riverview';
$page_css = 'carrito.css';

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error de conexión en carrito.php: " . $e->getMessage());
    exit("Error de conexión. Inténtelo más tarde.");
}

// =======================================================
// 3. LÓGICA DEL CARRITO
// =======================================================

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

$mensaje_stock = '';

// A) AÑADIR PRODUCTO
if (isset($_GET['add'])) {
    $id_producto = (int) $_GET['add'];
    $cantidad = isset($_GET['cantidad']) ? (int) $_GET['cantidad'] : 1;

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    $stmt_stock = $conexion->prepare("SELECT stock_actual FROM producto WHERE id_producto = :id_producto");
    $stmt_stock->execute([':id_producto' => $id_producto]);
    $producto_stock = $stmt_stock->fetch(PDO::FETCH_ASSOC);

    if ($producto_stock) {
        $stock_disponible = (int) $producto_stock['stock_actual'];
        $cantidad_actual_en_carrito = isset($_SESSION['carrito'][$id_producto]) ? (int) $_SESSION['carrito'][$id_producto] : 0;
        $nueva_cantidad_total = $cantidad_actual_en_carrito + $cantidad;

        if ($stock_disponible > 0) {
            if ($nueva_cantidad_total > $stock_disponible) {
                $_SESSION['carrito'][$id_producto] = $stock_disponible;
                $origen = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'tienda.php';
                $separador = (strpos($origen, '?') !== false) ? '&' : '?';
                header("Location: " . $origen . $separador . "stock_limitado=true");
                exit();
            } else {
                $_SESSION['carrito'][$id_producto] = $nueva_cantidad_total;
                $origen = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'tienda.php';
                $separador = (strpos($origen, '?') !== false) ? '&' : '?';
                header("Location: " . $origen . $separador . "modal_exito=true");
                exit();
            }
        } else {
            $origen = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'tienda.php';
            $separador = (strpos($origen, '?') !== false) ? '&' : '?';
            header("Location: " . $origen . $separador . "sin_stock=true");
            exit();
        }
    }

    header('Location: tienda.php');
    exit();
}

// B) BORRAR UN PRODUCTO
if (isset($_GET['borrar'])) {
    $id_producto = (int) $_GET['borrar'];
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

// D) RESTAR CANTIDAD
if (isset($_GET['restar'])) {
    $id_producto = (int) $_GET['restar'];

    if (isset($_SESSION['carrito'][$id_producto]) && $_SESSION['carrito'][$id_producto] > 1) {
        $_SESSION['carrito'][$id_producto]--;
    }

    header('Location: carrito.php');
    exit();
}

// E) SUMAR CANTIDAD
if (isset($_GET['sumar'])) {
    $id_producto = (int) $_GET['sumar'];

    if (isset($_SESSION['carrito'][$id_producto])) {
        $stmt_stock = $conexion->prepare("SELECT stock_actual FROM producto WHERE id_producto = :id_producto");
        $stmt_stock->execute([':id_producto' => $id_producto]);
        $producto_stock = $stmt_stock->fetch(PDO::FETCH_ASSOC);

        if ($producto_stock) {
            $stock_disponible = (int) $producto_stock['stock_actual'];
            $cantidad_actual = (int) $_SESSION['carrito'][$id_producto];

            if ($cantidad_actual < $stock_disponible) {
                $_SESSION['carrito'][$id_producto]++;
            } else {
                header('Location: carrito.php?stock_limitado=true');
                exit();
            }
        }
    }

    header('Location: carrito.php');
    exit();
}

// =======================================================
// 4. CONSULTA DATOS
// =======================================================
$productos_carrito = array();
$total_compra = 0;

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

    foreach ($productos_carrito as $producto) {
        $id_producto = $producto['id_producto'];
        $stock_disponible = (int) $producto['stock_actual'];

        if (isset($_SESSION['carrito'][$id_producto])) {
            if ($stock_disponible <= 0) {
                unset($_SESSION['carrito'][$id_producto]);
                $mensaje_stock = 'Uno o varios productos se han eliminado del carrito porque ya no tenían stock.';
            } elseif ($_SESSION['carrito'][$id_producto] > $stock_disponible) {
                $_SESSION['carrito'][$id_producto] = $stock_disponible;
                $mensaje_stock = 'Se ha ajustado la cantidad de algunos productos al stock disponible.';
            }
        }
    }

    if (!empty($_SESSION['carrito'])) {
        $ids = implode(',', array_keys($_SESSION['carrito']));
        $sql = "SELECT * FROM producto WHERE id_producto IN ($ids)";
        $stmt = $conexion->query($sql);
        $productos_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $productos_carrito = array();
    }
}

if (isset($_GET['stock_limitado'])) {
    $mensaje_stock = 'No puedes añadir más unidades de ese producto porque has alcanzado el stock disponible.';
}

if (isset($_GET['sin_stock'])) {
    $mensaje_stock = 'Este producto ya no tiene stock disponible.';
}
?>

<?php require_once '../includes/header.php'; ?>

<main class="container pb-5 carrito-main">
    <header class="mb-5">
        <h1 class="display-5 text-center text-vino fw-light">Tu Cesta de Compra</h1>
    </header>

    <?php if (!empty($mensaje_stock)): ?>
        <div class="alert alert-warning text-center mb-4" role="alert">
            <?php echo htmlspecialchars($mensaje_stock); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'pago'): ?>
        <div class="alert alert-danger text-center mb-4" role="alert">
            No se pudo procesar el pago simulado. Revisa los datos introducidos.
        </div>
    <?php endif; ?>

    <?php if (empty($productos_carrito)): ?>
        <section class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-vino"></i>
            <p class="lead mt-3 text-vino fw-bold">Tu carrito está vacío.</p>
            <a href="tienda.php" class="btn btn-vino btn-lg mt-3">IR A LA TIENDA</a>
        </section>

    <?php else: ?>
        <div class="row g-4 align-items-stretch">

            <section class="col-lg-8">
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
                                                    <img src="../img/<?php echo $producto['imagen_url']; ?>"
                                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                                         class="imagenes img-fluid rounded me-3">
                                                    <div>
                                                        <h2 class="h6 mb-0 text-vino"><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                                                        <small class="text-muted"><?php echo $cat_nombre; ?></small>
                                                        <div class="small text-muted mt-1">
                                                            Stock disponible: <?php echo (int) $producto['stock_actual']; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?php echo number_format($precio, 2); ?>€</td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm justify-content-center">
                                                    <a href="carrito.php?restar=<?php echo $id; ?>" class="btn btn-outline-secondary btn-cantidad">
                                                        <i class="bi bi-dash"></i>
                                                    </a>

                                                    <input type="text"
                                                           class="cuadro-cantidad form-control text-center border-secondary text-secondary bg-white"
                                                           value="<?php echo $cantidad; ?>"
                                                           readonly>

                                                    <?php if ($cantidad < (int) $producto['stock_actual']): ?>
                                                        <a href="carrito.php?sumar=<?php echo $id; ?>" class="btn btn-outline-secondary btn-cantidad">
                                                            <i class="bi bi-plus"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-outline-secondary btn-cantidad" disabled>
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center fw-bold"><?php echo number_format($subtotal_linea, 2); ?>€</td>
                                            <td class="text-end pe-4">
                                                <a href="carrito.php?borrar=<?php echo $id; ?>" class="text-danger" aria-label="Eliminar producto">
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
            </section>

            <aside class="col-lg-4 columna-resumen-ajuste">
                <div class="card border-0 shadow-sm">
                    <div class="card-header text-center py-3">
                        <h2 class="h5 mb-0">Resumen del Pedido</h2>
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

                        <div class="d-grid">
                            <?php if (isset($_SESSION['usuario_id'])): ?>
                                <button type="button" class="btn btn-vino btn-lg" data-bs-toggle="modal" data-bs-target="#modalPago">
                                    FINALIZAR COMPRA Y PAGAR
                                </button>
                            <?php else: ?>
                                <a href="./login.php?return_to=carrito.php" class="btn btn-warning">
                                    INICIA SESIÓN PARA PAGAR
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center text-muted small">
                    <i class="bi bi-shield-lock-fill me-1"></i> Pago 100% Seguro
                </div>
            </aside>
        </div>
    <?php endif; ?>
</main>

<div class="modal fade" id="modalVaciar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4 border-0 shadow">
            <div class="mb-3">
                <i class="bi bi-exclamation-circle text-danger display-1"></i>
            </div>
            <h2 class="h4 mb-2 text-vino">¿Vaciar cesta?</h2>
            <p class="text-muted mb-4">Se eliminarán todos los productos. Esta acción no se puede deshacer.</p>

            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <a href="carrito.php?vaciar=true" class="btn btn-danger px-4">Sí, vaciar</a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($productos_carrito) && isset($_SESSION['usuario_id'])): ?>
    <div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header modal-header-pago">
                    <h2 class="modal-title h5 text-vino mb-0">
                        <i class="bi bi-credit-card-2-front-fill me-2"></i>Pago seguro simulado
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form action="finalizar_pedido.php" method="POST" novalidate class="needs-validation" id="formPagoSimulado">
                    <div class="modal-body p-4">
                        <div class="alert alert-light border small mb-4">
                            <i class="bi bi-shield-lock-fill me-2 text-vino"></i>
                            Esta es una pasarela de pago simulada para el proyecto. No se almacenarán datos bancarios reales.
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="titular" class="form-label small text-muted">Titular de la tarjeta</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="titular"
                                    name="titular"
                                    placeholder="Nombre y apellidos"
                                    required>
                                <div class="invalid-feedback">
                                    Por favor, introduce el nombre del titular.
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="numero_tarjeta" class="form-label small text-muted">Número de tarjeta</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="numero_tarjeta"
                                    name="numero_tarjeta"
                                    placeholder="1234 5678 9012 3456"
                                    maxlength="19"
                                    required>
                                <div class="invalid-feedback">
                                    Introduce un número de tarjeta válido de 16 dígitos.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="caducidad" class="form-label small text-muted">Caducidad</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="caducidad"
                                    name="caducidad"
                                    placeholder="MM/AA"
                                    maxlength="5"
                                    required>
                                <div class="invalid-feedback">
                                    Introduce una fecha válida en formato MM/AA.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cvv" class="form-label small text-muted">CVV</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="cvv"
                                    name="cvv"
                                    placeholder="123"
                                    maxlength="4"
                                    required>
                                <div class="invalid-feedback">
                                    Introduce un CVV válido.
                                </div>
                            </div>
                        </div>

                        <div class="resumen-pago-simulado mt-4 p-3 rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span><?php echo number_format($total_compra, 2, ',', '.'); ?>€</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Envío</span>
                                <span class="text-success">Gratis</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-3 mt-3">
                                <span>Total a pagar</span>
                                <span class="text-vino"><?php echo number_format($total_compra, 2, ',', '.'); ?>€</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-vino px-4">
                            <i class="bi bi-lock-fill me-2"></i>Pagar ahora
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formPago = document.getElementById('formPagoSimulado');
    if (!formPago) return;

    const inputTarjeta = document.getElementById('numero_tarjeta');
    const inputCaducidad = document.getElementById('caducidad');
    const inputCvv = document.getElementById('cvv');

    inputTarjeta.addEventListener('input', function () {
        let valor = this.value.replace(/\D/g, '').substring(0, 16);
        valor = valor.replace(/(\d{4})(?=\d)/g, '$1 ');
        this.value = valor;
    });

    inputCaducidad.addEventListener('input', function () {
        let valor = this.value.replace(/\D/g, '').substring(0, 4);
        if (valor.length >= 3) {
            valor = valor.substring(0, 2) + '/' + valor.substring(2);
        }
        this.value = valor;
    });

    inputCvv.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').substring(0, 4);
    });

    formPago.addEventListener('submit', function (event) {
        const numeroTarjetaLimpio = inputTarjeta.value.replace(/\s/g, '');
        const regexCaducidad = /^(0[1-9]|1[0-2])\/\d{2}$/;
        const regexCvv = /^\d{3,4}$/;

        let valido = true;

        if (numeroTarjetaLimpio.length !== 16) {
            inputTarjeta.setCustomValidity('Número no válido');
            valido = false;
        } else {
            inputTarjeta.setCustomValidity('');
        }

        if (!regexCaducidad.test(inputCaducidad.value)) {
            inputCaducidad.setCustomValidity('Fecha no válida');
            valido = false;
        } else {
            inputCaducidad.setCustomValidity('');
        }

        if (!regexCvv.test(inputCvv.value)) {
            inputCvv.setCustomValidity('CVV no válido');
            valido = false;
        } else {
            inputCvv.setCustomValidity('');
        }

        if (!formPago.checkValidity() || !valido) {
            event.preventDefault();
            event.stopPropagation();
        }

        formPago.classList.add('was-validated');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
