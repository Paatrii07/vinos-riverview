<?php
session_start();
require_once '../config.php';

// 1. SEGURIDAD: Solo si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- LÓGICA DE BORRADO ---
    if (isset($_GET['borrar_prod'])) {
        $id = (int)$_GET['borrar_prod'];
        $conexion->prepare("DELETE FROM producto WHERE id_producto = ?")->execute([$id]);
        header("Location: panel.php?msg=borrado");
        exit();
    }

    if (isset($_GET['borrar_exp'])) {
        $id = (int)$_GET['borrar_exp'];
        $conexion->prepare("DELETE FROM cata WHERE id_visita = ?")->execute([$id]);
        header("Location: panel.php?msg=borrado");
        exit();
    }

    // --- CONSULTAS PARA ESTADÍSTICAS ---
    $total_ventas = $conexion->query("SELECT SUM(total_calculado) FROM pedido")->fetchColumn() ?: 0;
    $num_pedidos = $conexion->query("SELECT COUNT(*) FROM pedido")->fetchColumn();
    $num_usuarios = $conexion->query("SELECT COUNT(*) FROM usuario WHERE rol = 'cliente'")->fetchColumn();

    // --- CONSULTA DE TODOS LOS PRODUCTOS ---
    $prods = $conexion->query("SELECT * FROM producto")->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error en el panel: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Vinos Riverview</title>
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .bg-vino-admin { background-color: #640D14; color: white; }
        .card-stats { border: none; border-radius: 10px }
        .nav-tabs .nav-link.active { color: #640D14; font-weight: bold; border-bottom: 3px solid #640D14; }
        .nav-tabs .nav-link { color: #6c757d; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar bg-vino-admin shadow-sm mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1 text-white">ADMIN RIVERVIEW</span>
        <a href="../index.php" class="btn btn-outline-light btn-sm">Ver Web Pública</a>
    </div>
</nav>

<div class="container">
    <div class="row mb-4 g-3 text-center">
        <div class="col-md-4">
            <div class="card card-stats shadow-sm p-3">
                <h6 class="text-muted small">VENTAS</h6>
                <h2 class="fw-bold"><?php echo number_format($total_ventas, 2); ?>€</h2>
            </div>
        </div>
        <div class="col-md-4" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalDetallePedidos">
            <div class="card card-stats shadow-sm p-3 border-start border-success border-4">
                <h6 class="text-muted small">PEDIDOS (Ver todos)</h6>
                <h2 class="fw-bold"><?php echo $num_pedidos; ?></h2>
            </div>
        </div>
            <div class="col-md-4" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalDetalleClientes">
                <div class="card card-stats shadow-sm p-3 border-start border-warning border-4">
                    <h6 class="text-muted small">CLIENTES (Ver todos)</h6>
                    <h2 class="fw-bold"><?php echo $num_usuarios; ?></h2>
                </div>
            </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
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
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach($prods as $pr): 
                                if($pr['id_categoria'] == $id_cat):
                            ?>
                            <tr>
                                <td><img src="../img/<?php echo $pr['imagen_url']; ?>" width="40" class="rounded shadow-sm"></td>
                                <td><strong><?php echo htmlspecialchars($pr['nombre']); ?></strong></td>
                                <td><small class="text-muted"><?php echo mb_strimwidth(htmlspecialchars($pr['descripcion']), 0, 50, "..."); ?></small></td>
                                <td><?php echo number_format($pr['precio_unidad'], 2); ?>€</td>
                                <td class="text-end">
                                    <a href="editar_producto.php?id=<?php echo $pr['id_producto']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="panel.php?borrar_prod=<?php echo $pr['id_producto']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar producto?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Catas y Eventos</h5>
            <a href="nueva_experiencia.php" class="btn btn-success btn-sm">+ Nueva Experiencia</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Evento</th>
                        <th>Fecha</th>
                        <th>Aforo Máx.</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $exps = $conexion->query("SELECT * FROM cata")->fetchAll();
                    foreach($exps as $ex): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-vino"><?php echo htmlspecialchars($ex['nombre_evento']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($ex['fecha'])); ?></td>
                        <td><?php echo $ex['aforo_maximo']; ?> personas</td>
                        <td class="text-end pe-4">
                            <a href="editar_experiencia.php?id=<?php echo $ex['id_visita']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="panel.php?borrar_exp=<?php echo $ex['id_visita']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar experiencia?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para mostrar los datos de clientes -->
<div class="modal fade" id="modalDetalleClientes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-people me-2"></i>Listado de Clientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $clientes = $conexion->query("SELECT * FROM usuario WHERE rol = 'cliente'")->fetchAll();
                        foreach($clientes as $c): ?>
                        <tr>
                            <td class="ps-3"><?php echo htmlspecialchars($c['nombre'] . " " . $c['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($c['email']); ?></td>
                            <td><?php echo $c['telefono'] ?: '---'; ?></td>
                            <td class="small"><?php echo $c['direccion'] ?: 'No definida'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar los datos de pedido/ detalle de pedido -->
 <div class="modal fade" id="modalDetallePedidos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cart-check me-2"></i>Historial Completo de Pedidos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $todos_pedidos = $conexion->query("SELECT p.*, u.nombre FROM pedido p JOIN usuario u ON p.id_usuario = u.id_usuario ORDER BY p.fecha DESC")->fetchAll();
                        foreach($todos_pedidos as $tp): ?>
                        <tr>
                            <td class="ps-3">#<?php echo $tp['id_pedido']; ?></td>
                            <td><?php echo htmlspecialchars($tp['nombre']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($tp['fecha'])); ?></td>
                            <td class="fw-bold"><?php echo number_format($tp['total_calculado'], 2); ?>€</td>
                            <td><span class="badge bg-info text-dark"><?php echo $tp['estado']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>