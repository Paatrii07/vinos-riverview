<?php
session_start();
require_once '../config.php';

// 1. SEGURIDAD: Solo si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- LÓGICA DE ACTUALIZAR ESTADO DE PEDIDO ---
    if (isset($_POST['actualizar_estado'])) {
        $id_pedido = (int) $_POST['id_pedido'];
        $nuevo_estado = $_POST['nuevo_estado'];

        $stmt = $conexion->prepare("UPDATE pedido SET estado = ? WHERE id_pedido = ?");
        $stmt->execute([$nuevo_estado, $id_pedido]);

        header("Location: panel.php?msg=estado_actualizado");
        exit();
    }

    // --- LÓGICA DE ACTUALIZAR ESTADO DE RESERVA ---
    if (isset($_POST['actualizar_reserva'])) {
        $id_res = (int) $_POST['id_reserva_edit'];
        $nuevo_estado = $_POST['nuevo_estado_reserva'];

        $stmt = $conexion->prepare("UPDATE reserva SET estado = ? WHERE id_reserva = ?");
        $stmt->execute([$nuevo_estado, $id_res]);

        header("Location: panel.php?msg=actualizado");
        exit();
    }

    // --- LÓGICA DE BORRADO DE PRODUCTOS ---
    if (isset($_GET['borrar_prod'])) {
        $id = (int) $_GET['borrar_prod'];
        $conexion->prepare("DELETE FROM producto WHERE id_producto = ?")->execute([$id]);
        header("Location: panel.php?msg=borrado");
        exit();
    }

    // --- LÓGICA DE BORRADO DE RESERVAS ---
    if (isset($_GET['borrar_reserva'])) {
        $id = (int) $_GET['borrar_reserva'];
        $conexion->prepare("DELETE FROM reserva WHERE id_reserva = ?")->execute([$id]);
        header("Location: panel.php?msg=borrado");
        exit();
    }

    // --- LÓGICA DE BORRADO DE EXPERIENCIAS ---
    if (isset($_GET['borrar_exp'])) {
        $id = (int) $_GET['borrar_exp'];
        $conexion->prepare("DELETE FROM cata WHERE id_visita = ?")->execute([$id]);
        header("Location: panel.php?msg=borrado");
        exit();
    }

    // --- CONSULTAS PARA ESTADÍSTICAS Y TABLAS ---
    $total_ventas = $conexion->query("SELECT SUM(total_calculado) FROM pedido")->fetchColumn() ?: 0;
    $num_pedidos = $conexion->query("SELECT COUNT(*) FROM pedido")->fetchColumn();
    $num_usuarios = $conexion->query("SELECT COUNT(*) FROM usuario WHERE rol = 'cliente'")->fetchColumn();
    $num_reservas = $conexion->query("SELECT COUNT(*) FROM reserva")->fetchColumn();

    $todos_pedidos = $conexion->query("
        SELECT p.*, u.nombre, u.apellidos, u.direccion, u.telefono, p.id_usuario
        FROM pedido p
        JOIN usuario u ON p.id_usuario = u.id_usuario
        ORDER BY p.fecha DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $reservas = $conexion->query("
        SELECT r.*, u.nombre, u.apellidos, c.nombre_evento
        FROM reserva r
        JOIN usuario u ON r.id_usuario = u.id_usuario
        JOIN cata c ON r.id_visita = c.id_visita
        ORDER BY r.fecha_reserva DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $prods = $conexion->query("SELECT * FROM producto")->fetchAll(PDO::FETCH_ASSOC);
    $exps = $conexion->query("SELECT * FROM cata")->fetchAll(PDO::FETCH_ASSOC);
    $clientes = $conexion->query("SELECT * FROM usuario WHERE rol = 'cliente'")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error en panel admin: " . $e->getMessage());
    die("Error interno del panel.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Vinos Riverview</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/panel.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar bg-vino-admin shadow-sm mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-white fw-light admin-brand">
                ADMIN <span class="fw-bold">RIVERVIEW</span>
            </span>

            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-light btn-sm px-3">
                    <i class="bi bi-eye me-1"></i> Web Pública
                </a>

                <button type="button" class="btn btn-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalLogout">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </button>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'creado'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> ¡Excelente! El nuevo registro se ha añadido al inventario.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($_GET['msg'] == 'borrado'): ?>
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-trash-fill me-2"></i> Registro eliminado correctamente del sistema.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($_GET['msg'] == 'actualizado'): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-pencil-square me-2"></i> Los cambios se han guardado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($_GET['msg'] == 'estado_actualizado'): ?>
                <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                    <i class="bi bi-check-circle me-2"></i> Estado del pedido actualizado con éxito.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <main class="container flex-grow-1">
        <div class="row mb-4 g-3 text-center">

            <div class="col-md-4 panel-clickable" data-bs-toggle="modal" data-bs-target="#modalDetalleReservas">
                <div class="card card-stats shadow-sm p-4 border-start border-riverview-vino border-5">
                    <h6 class="text-muted small text-uppercase fw-bold">Gestión de reservas</h6>
                    <h2 class="fw-bold text-vino-panel"><?php echo $num_reservas; ?></h2>
                    <small class="text-muted fst-italic">Gestionar reservas</small>
                </div>
            </div>

            <div class="col-md-4 panel-clickable" data-bs-toggle="modal" data-bs-target="#modalDetallePedidos">
                <div class="card card-stats shadow-sm p-4 border-start border-riverview-vino border-5">
                    <h6 class="text-muted small text-uppercase fw-bold">Gestión de pedidos</h6>
                    <h2 class="fw-bold text-vino-panel"><?php echo $num_pedidos; ?></h2>
                    <small class="text-muted fst-italic">Gestionar pedidos</small>
                </div>
            </div>

            <div class="col-md-4 panel-clickable" data-bs-toggle="modal" data-bs-target="#modalDetalleClientes">
                <div class="card card-stats shadow-sm p-4 border-start border-riverview-suave border-5">
                    <h6 class="text-muted small text-uppercase fw-bold">Cartera de Clientes</h6>
                    <h2 class="fw-bold text-vino-panel"><?php echo $num_usuarios; ?></h2>
                    <small class="text-muted fst-italic">Ver clientes</small>
                </div>
            </div>
        </div>

        <section class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Inventario de Productos</h5>
                <a href="./nuevo_producto.php" class="btn btn-success btn-sm">+ Añadir Producto</a>
            </div>

            <ul class="nav nav-tabs px-4 pt-2" id="adminTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-vinos">Vinos</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-quesos">Quesos</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-embutidos">Embutidos</button>
                </li>
            </ul>

            <div class="tab-content p-3">
                <?php
                $cats_info = [1 => 'tab-vinos', 2 => 'tab-quesos', 3 => 'tab-embutidos'];
                foreach ($cats_info as $id_cat => $tab_id):
                ?>
                    <div class="tab-pane fade <?php echo ($id_cat == 1) ? 'show active' : ''; ?>" id="<?php echo $tab_id; ?>">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Imagen</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prods as $pr): ?>
                                        <?php if ($pr['id_categoria'] == $id_cat): ?>
                                            <tr>
                                                <td><img src="../img/<?php echo $pr['imagen_url']; ?>" width="40" class="rounded shadow-sm" alt="<?php echo htmlspecialchars($pr['nombre']); ?>"></td>
                                                <td><strong><?php echo htmlspecialchars($pr['nombre']); ?></strong></td>
                                                <td><small class="text-muted"><?php echo mb_strimwidth(htmlspecialchars($pr['descripcion']), 0, 40, "..."); ?></small></td>
                                                <td><?php echo number_format($pr['precio_unidad'], 2); ?>€</td>
                                                <td>
                                                    <?php if ($pr['stock_actual'] <= 5): ?>
                                                        <span class="badge bg-danger">Bajo: <?php echo $pr['stock_actual']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo $pr['stock_actual']; ?> uds</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="editar_producto.php?id=<?php echo $pr['id_producto']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                    <a href="#" class="btn btn-sm btn-outline-danger"
                                                       onclick="prepararBorrado('panel.php?borrar_prod=<?php echo $pr['id_producto']; ?>', 'Eliminar Producto', '¿Seguro que quieres eliminar <?php echo htmlspecialchars($pr['nombre']); ?>?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Catas y Eventos</h5>
                <a href="nueva_experiencia.php" class="btn btn-success btn-sm">+ Nueva Experiencia</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Evento / Descripción</th>
                            <th>Fecha y Hora</th>
                            <th>Precio</th>
                            <th>Aforo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exps as $ex): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-vino-panel"><?php echo htmlspecialchars($ex['nombre_evento']); ?></div>
                                    <small class="text-muted">ID Visita: #<?php echo $ex['id_visita']; ?></small>
                                </td>
                                <td>
                                    <div class="small"><?php echo date('d/m/Y', strtotime($ex['fecha'])); ?></div>
                                </td>
                                <td class="fw-bold"><?php echo number_format($ex['precio'], 2); ?>€</td>
                                <td><?php echo $ex['aforo_maximo']; ?> pers.</td>
                                <td class="text-end pe-4">
                                    <a href="editar_experiencia.php?id=<?php echo $ex['id_visita']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="#" class="btn btn-sm btn-outline-danger"
                                       onclick="prepararBorrado('panel.php?borrar_exp=<?php echo $ex['id_visita']; ?>', 'Eliminar Experiencia', '¿Seguro?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal fade" id="modalDetalleClientes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title fw-light"><i class="bi bi-people me-2"></i> CARTERA DE CLIENTES</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nombre</th>
                                    <th>Email</th>
                                    <th class="pe-4">Dirección</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $c): ?>
                                    <tr>
                                        <td class="ps-4"><strong><?php echo htmlspecialchars($c['nombre'] . " " . $c['apellidos']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                                        <td class="pe-4 small text-muted"><?php echo !empty($c['direccion']) ? htmlspecialchars($c['direccion']) : 'Sin dirección'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetallePedidos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title fw-light"><i class="bi bi-cart-check me-2"></i> GESTIÓN DE PEDIDOS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 text-vino-panel">ID / Fecha</th>
                                    <th class="text-vino-panel">Cliente / Contacto</th>
                                    <th class="text-vino-panel">Dirección de Envío</th>
                                    <th class="text-vino-panel">Total</th>
                                    <th class="text-vino-panel">Estado</th>
                                    <th class="text-vino-panel text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todos_pedidos as $tp): ?>
                                    <?php
                                    $ped_col = 'secondary';
                                    if ($tp['estado'] == 'enviado') $ped_col = 'success';
                                    if ($tp['estado'] == 'cancelado') $ped_col = 'danger';
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-vino-panel">#<?php echo $tp['id_pedido']; ?></div>
                                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($tp['fecha'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($tp['nombre'] . " " . $tp['apellidos']); ?></div>

                                            <div class="small text-muted">
                                                <i class="bi bi-telephone me-1 text-vino-panel"></i>
                                                <?php echo htmlspecialchars($tp['telefono'] ?? 'Sin teléfono'); ?>
                                            </div>

                                            <?php
                                            $usuario_pedido = $tp['id_usuario'] ?? null;
                                            $usuario_sesion = $_SESSION['id_usuario'] ?? null;
                                            if ($usuario_pedido && $usuario_sesion && $usuario_pedido == $usuario_sesion):
                                            ?>
                                                <span class="badge bg-light text-vino-panel border mt-1 badge-admin-test">ADMIN TEST</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1 text-vino-panel"></i>
                                                <?php echo htmlspecialchars($tp['direccion'] ?? 'Recogida en tienda'); ?>
                                            </small>
                                        </td>
                                        <td class="fw-bold"><?php echo number_format($tp['total_calculado'], 2); ?>€</td>
                                        <td>
                                            <form method="POST" class="d-flex gap-2">
                                                <input type="hidden" name="id_pedido" value="<?php echo $tp['id_pedido']; ?>">
                                                <select name="nuevo_estado" class="form-select form-select-sm fw-bold border-<?php echo $ped_col; ?> text-<?php echo $ped_col; ?> select-estado-admin">
                                                    <option value="pendiente" <?php echo ($tp['estado'] == 'pendiente') ? 'selected' : ''; ?>>⏳ Pendiente</option>
                                                    <option value="enviado" <?php echo ($tp['estado'] == 'enviado') ? 'selected' : ''; ?>>📦 Enviado</option>
                                                    <option value="cancelado" <?php echo ($tp['estado'] == 'cancelado') ? 'selected' : ''; ?>>❌ Cancelado</option>
                                                </select>
                                                <button type="submit" name="actualizar_estado" class="btn btn-sm btn-<?php echo $ped_col; ?>">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-vino btn-ver-detalle-admin" data-id="<?php echo $tp['id_pedido']; ?>">
                                                <i class="bi bi-eye"></i> Detalle
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalleReservas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header modal-header-admin">
                    <h5 class="modal-title fw-light"><i class="bi bi-calendar-check me-2"></i> GESTIÓN DE RESERVAS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Evento / Experiencia</th>
                                    <th class="text-center">ID Reserva</th>
                                    <th>Cliente</th>
                                    <th>Fecha Reserva</th>
                                    <th>Estado</th>
                                    <th class="pe-4 text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $res): ?>
                                    <?php $color_borde = ($res['estado'] == 'confirmada') ? 'success' : 'danger'; ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-vino-panel"><?php echo htmlspecialchars($res['nombre_evento']); ?></div>
                                            <small class="text-muted fst-italic">Ref. Visita: #<?php echo $res['id_visita']; ?></small>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-dark px-3 py-2 badge-codigo-reserva">
                                                #<?php echo $res['id_reserva']; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($res['nombre'] . " " . $res['apellidos']); ?></div>
                                        </td>

                                        <td><?php echo date('d/m/Y', strtotime($res['fecha_reserva'])); ?></td>

                                        <td>
                                            <form method="POST" class="d-flex gap-1">
                                                <input type="hidden" name="id_reserva_edit" value="<?php echo $res['id_reserva']; ?>">

                                                <select name="nuevo_estado_reserva" class="form-select form-select-sm fw-bold border-<?php echo $color_borde; ?> text-<?php echo $color_borde; ?> select-estado-admin">
                                                    <option value="confirmada" <?php echo ($res['estado'] == 'confirmada') ? 'selected' : ''; ?>>🟢 Confirmada</option>
                                                    <option value="cancelada" <?php echo ($res['estado'] == 'cancelada') ? 'selected' : ''; ?>>🔴 Cancelada</option>
                                                </select>

                                                <button type="submit" name="actualizar_reserva" class="btn btn-sm btn-<?php echo $color_borde; ?>">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </form>
                                        </td>

                                        <td class="pe-4 text-end">
                                            <a href="#" class="btn btn-sm btn-outline-danger"
                                               onclick="prepararBorrado('panel.php?borrar_reserva=<?php echo $res['id_reserva']; ?>', 'Eliminar Reserva', '¿Seguro que quieres borrar la reserva #<?php echo $res['id_reserva']; ?>?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAdminDetalleProductos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-light">Contenido del Pedido #<span id="adminIdPedidoText"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th class="text-end">P. Unitario</th>
                            </tr>
                        </thead>
                        <tbody id="adminCuerpoDetalle">
                            <!-- Aquí carga el AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLogout" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-redondeado-admin">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-light text-vino-admin"><i class="bi bi-shield-lock me-2"></i> SEGURIDAD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="lead mb-0">¿Deseas cerrar tu sesión de administrador?</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancelar</button>
                    <a href="../php/logout.php" class="btn btn-admin-logout px-4 shadow-sm">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmarBorrado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-redondeado-admin">
                <div class="modal-header border-0 pt-4 px-4 justify-content-center">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center icono-alerta-borrado">
                        <i class="bi bi-exclamation-triangle text-danger icono-alerta-borrado-i"></i>
                    </div>
                </div>
                <div class="modal-body text-center px-4">
                    <h4 class="fw-bold text-dark" id="tituloBorrar">¿Estás seguro?</h4>
                    <p class="text-muted" id="mensajeBorrar">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="botonConfirmarBorrar" class="btn btn-admin-logout px-4 shadow-sm">Eliminar ahora</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer-admin mt-auto py-3 bg-white border-top">
        <div class="container-fluid px-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 small">&copy; 2026 <span class="text-vino-panel">Vinos Riverview</span> - Gestión Interna</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <span class="small text-muted">Sesión: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
                </div>
            </div>
        </div>
    </footer>

    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <script>
        if (window.location.search.includes('msg=')) {
            setTimeout(function() {
                const url = new URL(window.location);
                url.searchParams.delete('msg');
                window.history.replaceState({}, document.title, url);
                const alert = document.querySelector('.alert');
                if (alert) {
                    new bootstrap.Alert(alert).close();
                }
            }, 3000);
        }

        function prepararBorrado(url, titulo, mensaje) {
            document.getElementById('tituloBorrar').innerText = titulo;
            document.getElementById('mensajeBorrar').innerText = mensaje;
            document.getElementById('botonConfirmarBorrar').setAttribute('href', url);
            var miModal = new bootstrap.Modal(document.getElementById('modalConfirmarBorrado'));
            miModal.show();
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.btn-ver-detalle-admin').forEach(boton => {
                boton.addEventListener('click', function() {
                    const idPedido = this.getAttribute('data-id');
                    document.getElementById('adminIdPedidoText').textContent = idPedido;

                    fetch('../php/get_detalle_pedido.php?id=' + idPedido)
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('adminCuerpoDetalle').innerHTML = html;
                            var modalHijo = new bootstrap.Modal(document.getElementById('modalAdminDetalleProductos'));
                            modalHijo.show();
                        });
                });
            });
        });
    </script>
</body>
</html>
