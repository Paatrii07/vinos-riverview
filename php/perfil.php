<?php
// 1. INICIAR SESIÓN Y CONFIGURACIÓN
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_title = 'Mi Perfil - Vinos Riverview';
$page_css = 'perfil.css';

// Seguridad: Si no hay sesión, al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ./login.php");
    exit();
}

// 2. CONEXIÓN A LA BASE DE DATOS
try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión técnica.");
}

// Variables para mensajes de feedback
$mensaje = "";
$tipo_mensaje = "";
$mostrar_alerta_cancelacion = isset($_GET['cancelado']);

// 3. LÓGICA: PROCESAR ACTUALIZACIÓN DE DATOS
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'actualizar_datos') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $id_usuario = $_SESSION['usuario_id'];

    try {
        $sql_update = "UPDATE usuario SET nombre = :nom, apellidos = :ape, telefono = :tel, direccion = :dir WHERE id_usuario = :id";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->execute([
            ':nom' => $nombre,
            ':ape' => $apellidos,
            ':tel' => $telefono,
            ':dir' => $direccion,
            ':id'  => $id_usuario
        ]);

        $mensaje = "¡Datos actualizados correctamente!";
        $tipo_mensaje = "success";
        $_SESSION['nombre'] = $nombre;
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar.";
        $tipo_mensaje = "danger";
    }
}

// 4. CONSULTAS DE LECTURA
$stmt_user = $conexion->prepare("SELECT * FROM usuario WHERE id_usuario = ?");
$stmt_user->execute([$_SESSION['usuario_id']]);
$datos_usuario = $stmt_user->fetch(PDO::FETCH_ASSOC);

$sql_res = "SELECT r.*, c.nombre_evento, c.fecha, c.hora, c.precio
            FROM reserva r
            JOIN cata c ON r.id_visita = c.id_visita
            WHERE r.id_usuario = :id";
$stmt_res = $conexion->prepare($sql_res);
$stmt_res->execute(['id' => $_SESSION['usuario_id']]);
$mis_reservas = $stmt_res->fetchAll(PDO::FETCH_ASSOC);

// 5. CONSULTA DE PEDIDOS REALIZADOS
$sql_pedidos = "SELECT * FROM pedido WHERE id_usuario = :id ORDER BY fecha DESC";
$stmt_p = $conexion->prepare($sql_pedidos);
$stmt_p->execute([':id' => $_SESSION['usuario_id']]);
$mis_pedidos = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}
?>

<?php require_once '../includes/header.php'; ?>

    <main class="perfil-main">
        <section class="container perfil-container">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <header class="d-flex justify-content-between align-items-end mb-4">
                        <h1 class="fw-light text-vino m-0">Mi Área Personal</h1>
                    </header>

                    <?php if ($mostrar_alerta_cancelacion): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            La reserva ha sido cancelada correctamente.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                            <?php echo $mensaje; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>

                    <nav class="mb-4" aria-label="Secciones del perfil">
                        <ul class="nav nav-tabs" id="perfilTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab">
                                    Mis Datos
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pedidos" type="button" role="tab">
                                    Mis Pedidos
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#experiencias" type="button" role="tab">
                                    Mis Reservas
                                </button>
                            </li>
                        </ul>
                    </nav>

                    <div class="tab-content" id="myTabContent">

                        <section class="tab-pane fade show active" id="datos" role="tabpanel">
                            <div class="card border-0 shadow-sm p-4">
                                <form action="perfil.php" method="POST">
                                    <input type="hidden" name="accion" value="actualizar_datos">

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Nombre</label>
                                            <input
                                                type="text"
                                                name="nombre"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Apellidos</label>
                                            <input
                                                type="text"
                                                name="apellidos"
                                                class="form-control"
                                                value="<?php echo htmlspecialchars($datos_usuario['apellidos']); ?>"
                                                required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Email</label>
                                        <input
                                            type="email"
                                            class="form-control bg-light"
                                            value="<?php echo htmlspecialchars($datos_usuario['email']); ?>"
                                            readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Teléfono</label>
                                        <input
                                            type="tel"
                                            name="telefono"
                                            class="form-control"
                                            value="<?php echo htmlspecialchars($datos_usuario['telefono'] ?? ''); ?>">
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label text-muted small">Dirección de Envío</label>
                                        <textarea name="direccion" class="form-control" rows="2"><?php echo htmlspecialchars($datos_usuario['direccion'] ?? ''); ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-outline-vino text-uppercase px-4 btn-guardar-cambios">
                                        Guardar cambios
                                    </button>
                                </form>

                                <hr class="my-4">

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <p class="text-muted small m-0">
                                            Si deseas darte de baja permanentemente.
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger btn-sm fw-bold px-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar">
                                        ELIMINAR CUENTA
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section class="tab-pane fade" id="pedidos" role="tabpanel">
                            <div class="card border-0 shadow-sm p-4">
                                <h2 class="mb-4 fw-light text-vino h4">Historial de Pedidos</h2>

                                <?php if (count($mis_pedidos) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nº Pedido</th>
                                                    <th>Fecha</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                    <th>Método Pago</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mis_pedidos as $ped): ?>
                                                    <tr>
                                                        <td><span class="fw-bold text-vino">#<?php echo $ped['id_pedido']; ?></span></td>
                                                        <td><?php echo date('d/m/Y', strtotime($ped['fecha'])); ?></td>
                                                        <td class="fw-bold"><?php echo number_format($ped['total_calculado'], 2, ',', '.'); ?>€</td>
                                                        <td>
                                                            <?php
                                                                $clase_badge = 'bg-secondary';
                                                                if ($ped['estado'] == 'pendiente') $clase_badge = 'bg-warning text-dark';
                                                                if ($ped['estado'] == 'enviado') $clase_badge = 'bg-success';
                                                                if ($ped['estado'] == 'cancelado') $clase_badge = 'bg-danger';
                                                            ?>
                                                            <span class="badge <?php echo $clase_badge; ?> text-uppercase px-3 py-2">
                                                                <?php echo !empty($ped['estado']) ? htmlspecialchars($ped['estado']) : 'PENDIENTE'; ?>
                                                            </span>
                                                        </td>
                                                        <td class="small text-muted"><?php echo htmlspecialchars($ped['forma_pago']); ?></td>
                                                        <td>
                                                            <button
                                                                class="btn btn-sm btn-outline-vino btn-detalle"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalDetallePedido"
                                                                data-id="<?php echo $ped['id_pedido']; ?>">
                                                                <i class="bi bi-eye"></i> Ver Detalle
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-cart-x display-1 text-muted opacity-25"></i>
                                        <p class="mt-3 text-muted">Aún no has realizado ninguna compra.</p>
                                        <a href="tienda.php" class="btn btn-vino btn-sm mt-2">Ir a la tienda</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <section class="tab-pane fade" id="experiencias" role="tabpanel">
                            <div class="card border-0 shadow-sm p-4">
                                <h2 class="mb-4 fw-light text-vino h4">Mis Reservas</h2>

                                <?php if (count($mis_reservas) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Evento</th>
                                                    <th>Fecha</th>
                                                    <th>Hora</th>
                                                    <th>Estado</th>
                                                    <th>Precio</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mis_reservas as $res): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($res['nombre_evento']); ?></strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="bi bi-people me-1"></i><?php echo $res['num_personas']; ?> plazas
                                                            </small>
                                                        </td>
                                                        <td><?php echo date('d/m/Y', strtotime($res['fecha'])); ?></td>
                                                        <td><?php echo $res['hora']; ?></td>
                                                        <td>
                                                            <?php $clase_badge = ($res['estado'] == 'confirmada') ? 'bg-success' : 'bg-danger'; ?>
                                                            <span class="badge <?php echo $clase_badge; ?> text-uppercase">
                                                                <?php echo htmlspecialchars($res['estado']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="fw-bold"><?php echo $res['precio']; ?>€</td>
                                                        <td class="text-center">
                                                            <?php if ($res['estado'] !== 'cancelada'): ?>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalConfirmarCancelacion"
                                                                    data-id-visita="<?php echo $res['id_visita']; ?>"
                                                                    data-nombre-evento="<?php echo htmlspecialchars($res['nombre_evento']); ?>">
                                                                    <i class="bi bi-trash"></i> Cancelar
                                                                </button>
                                                            <?php else: ?>
                                                                <small class="text-muted">Sin acciones</small>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-4">No tienes reservas activas.</p>
                                    <div class="text-center">
                                        <a href="experiencias.php" class="btn btn-vino">Ver Catas</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header modal-header-vino">
                        <h2 class="modal-title modal-title-vino h5">
                            <i class="bi bi-shield-exclamation me-2"></i> Eliminar Cuenta
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body text-center p-4">
                        <p class="lead text-dark mb-3">¿Realmente deseas marcharte?</p>
                        <p class="text-muted small">
                            Esta acción borrará tus datos, tu historial de pedidos y tus reservas.
                            <strong>No se puede deshacer.</strong>
                        </p>
                    </div>

                    <div class="modal-footer justify-content-center border-0 pb-4 pt-0">
                        <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancelar</button>
                        <a href="borrarCuenta.php" class="btn btn-dark px-4">
                            Sí, eliminar definitivamente
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalConfirmarCancelacion" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h2 class="modal-title h5">
                            <i class="bi bi-exclamation-triangle me-2"></i>¿Cancelar reserva?
                        </h2>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p>Estás a punto de cancelar tu reserva para:</p>
                        <p id="nombreEventoCancel" class="fw-bold text-dark mb-4 modal-evento-cancelacion"></p>
                        <p class="text-muted small">Esta acción no se puede deshacer. ¿Deseas continuar?</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, mantener</button>
                        <a id="btnConfirmarBorrado" href="#" class="btn btn-danger px-4">Sí, cancelar reserva</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalPedidoExito" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg text-center p-4">
                    <div class="modal-body">
                        <div class="mb-4">
                            <i class="bi bi-bag-check-fill text-success icono-pedido-exito"></i>
                        </div>
                        <h2 class="fw-light mb-3 h3">¡Pedido realizado con éxito!</h2>
                        <p class="text-muted mb-4">
                            Gracias por confiar en <strong>Vinos Riverview</strong>.
                            Hemos recibido tu pedido y estamos preparándolo con mucho mimo.
                        </p>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-vino" data-bs-dismiss="modal">
                                VER MIS PEDIDOS
                            </button>
                            <a href="tienda.php" class="btn btn-outline-secondary">
                                SEGUIR COMPRANDO
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title text-vino h5">Detalles del Pedido #<span id="numPedidoModal"></span></h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table small mb-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cant.</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoDetallePedido">
                                    <!-- Aquí se cargará el contenido vía AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('pedido_exito')) {
                var modalPedido = new bootstrap.Modal(document.getElementById('modalPedidoExito'));
                modalPedido.show();

                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }

            const tabPedidos = document.querySelector('[data-bs-target="#pedidos"]');
            if (tabPedidos && urlParams.has('pedido_exito')) {
                bootstrap.Tab.getInstance(tabPedidos)?.show() || new bootstrap.Tab(tabPedidos).show();
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalCancel = document.getElementById('modalConfirmarCancelacion');
            if (modalCancel) {
                modalCancel.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var idVisita = button.getAttribute('data-id-visita');
                    var nombreEvento = button.getAttribute('data-nombre-evento');

                    modalCancel.querySelector('#nombreEventoCancel').textContent = nombreEvento;
                    modalCancel.querySelector('#btnConfirmarBorrado').href = 'cancelar_reserva.php?id_visita=' + idVisita;
                });
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            const hash = window.location.hash;

            if (hash) {
                const targetTab = document.querySelector(`[data-bs-target="${hash}"]`);
                if (targetTab) {
                    const tabTrigger = new bootstrap.Tab(targetTab);
                    tabTrigger.show();
                }
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modalDetalle = document.getElementById('modalDetallePedido');
            if (modalDetalle) {
                modalDetalle.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var idPedido = button.getAttribute('data-id');

                    document.getElementById('numPedidoModal').textContent = idPedido;

                    fetch('get_detalle_pedido.php?id=' + idPedido)
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('cuerpoDetallePedido').innerHTML = html;
                        });
                });
            }
        });
    </script>


<?php require_once '../includes/footer.php'; ?>
