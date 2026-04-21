<?php
// 1. INICIAR SESIÓN Y CONFIGURACIÓN
session_start();
require_once '../config.php';

// Seguridad: Si no hay sesión, al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ./login.php");
    exit();
}

// Mensaje de cancelación correcta de reserva si la hay

if (isset($_GET['cancelado'])): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        La reserva ha sido cancelada correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; 


// 2. CONEXIÓN A LA BASE DE DATOS
try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión técnica.");
}

// Variables para mensajes de feedback
$mensaje = "";
$tipo_mensaje = "";

// 3. LÓGICA: PROCESAR ACTUALIZACIÓN DE DATOS (Si el usuario envía el formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'actualizar_datos') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $id_usuario = $_SESSION['usuario_id'];

    try {
        $sql_update = "UPDATE usuario SET nombre = :nom, apellidos = :ape, telefono = :tel, direccion = :dir WHERE id_usuario = :id";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->execute([':nom' => $nombre, ':ape' => $apellidos, ':tel' => $telefono, ':dir' => $direccion, ':id' => $id_usuario]);
        
        $mensaje = "¡Datos actualizados correctamente!";
        $tipo_mensaje = "success";
        $_SESSION['nombre'] = $nombre; // Actualizamos el nombre en la barra de navegación
    } catch(PDOException $e) {
        $mensaje = "Error al actualizar.";
        $tipo_mensaje = "danger";
    }
}

// 4. CONSULTAS DE LECTURA (Para mostrar en las pestañas)
// Datos personales
$stmt_user = $conexion->prepare("SELECT * FROM usuario WHERE id_usuario = ?");
$stmt_user->execute([$_SESSION['usuario_id']]);
$datos_usuario = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Reservas de experiencias (JOIN para traer datos de la cata)
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


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Área Personal - Vinos Riverview</title>
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <link href="../css/perfil.css" rel="stylesheet">
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



                    <?php if (!isset($_SESSION['usuario_id'])): ?> <!-- Te redirige a login y despues de loguearte te redirige a la página desde la que accedes -->
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
                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador'): ?> <!-- ¿Existe la variable rol Y ADEMÁS es igual a 'administrador'?". Si -> te lleva al panel de administrador. No-> te lleva al menu de cliente. -->
                                    <li>
                                        <a class="dropdown-item fw-bold text-vino" href="../admin/panel.php">
                                            <i class="bi bi-speedometer2 me-2"></i> Panel de Control
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?> <!-- Abre el menu de usuario / cliente. -->
                                <li><a class="dropdown-item" href="./perfil.php">Mi Perfil</a></li>
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
                                <a class="nav-link active" aria-current="page" href="../index.php">Inicio</a>
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

    <main>

        <div class="container perfil-container ">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <h2 class="fw-light text-vino m-0">Mi Área Personal</h2>
                    </div>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show"><?php echo $mensaje; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <ul class="nav nav-tabs mb-4" id="perfilTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#datos">Mis Datos</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pedidos">Mis Pedidos</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#experiencias">Mis Reservas</button>
                        </li>
                    </ul>






                    <div class="tab-content" id="myTabContent">
                        
                        <div class="tab-pane fade show active" id="datos" role="tabpanel">
                            <div class="card border-0 shadow-sm p-4">
                                <form action="perfil.php" method="POST">
                                    <input type="hidden" name="accion" value="actualizar_datos">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Nombre</label>
                                            <input type="text" name="nombre" class="form-control" 
                                                value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Apellidos</label>
                                            <input type="text" name="apellidos" class="form-control" 
                                                value="<?php echo htmlspecialchars($datos_usuario['apellidos']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Email</label>
                                        <input type="email" class="form-control bg-light" 
                                            value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Teléfono</label>
                                        <input type="tel" name="telefono" class="form-control" 
                                            value="<?php echo htmlspecialchars($datos_usuario['telefono'] ?? ''); ?>">
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label text-muted small">Dirección de Envío</label>
                                        <textarea name="direccion" class="form-control" rows="2"><?php echo htmlspecialchars($datos_usuario['direccion'] ?? ''); ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-outline-vino text-uppercase px-4" style="letter-spacing: 2px;">
                                        Guardar Cambios
                                    </button>
                                </form>

                                <hr class="my-4"> 
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted small m-0">
                                            Si deseas darte de baja permanentemente. 
                                        </p>
                                    </div>
                                <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                                        ELIMINAR CUENTA
                                    </button>
                                </div>

                            </div>
                        </div>

                        <div class="tab-pane fade" id="pedidos" role="tabpanel">
        <div class="card border-0 shadow-sm p-4">
            <h4 class="mb-4 fw-light text-vino">Historial de Pedidos</h4>
            
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
                                            $clase_badge = 'bg-secondary'; // Gris por defecto
                                            if ($ped['estado'] == 'pendiente') $clase_badge = 'bg-warning text-dark';
                                            if ($ped['estado'] == 'enviado') $clase_badge = 'bg-success';
                                            if ($ped['estado'] == 'cancelado') $clase_badge = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $clase_badge; ?> text-uppercase px-3 py-2">
                                            <?php echo !empty($ped['estado']) ? htmlspecialchars($ped['estado']) : 'PENDIENTE'; ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($ped['forma_pago']); ?></td>
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
    </div>

                        <div class="tab-pane fade" id="experiencias">
        <div class="card border-0 shadow-sm p-4">
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
                                        <small class="text-muted"><i class="bi bi-people me-1"></i><?php echo $res['num_personas']; ?> plazas</small>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($res['fecha'])); ?></td>
                                    <td><?php echo $res['hora']; ?></td>
                                    <td><span class="badge bg-success text-uppercase"><?php echo $res['estado']; ?></span></td>
                                    <td class="fw-bold"><?php echo $res['precio']; ?>€</td>
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalConfirmarCancelacion" 
                                                data-id-visita="<?php echo $res['id_visita']; ?>"
                                                data-nombre-evento="<?php echo htmlspecialchars($res['nombre_evento']); ?>">
                                            <i class="bi bi-trash"></i> Cancelar
                                        </button>
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
    </div>


                    </div> 
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    
                    <div class="modal-header modal-header-vino">
                        <h5 class="modal-title modal-title-vino">
                            <i class="bi bi-shield-exclamation me-2"></i> Eliminar Cuenta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>¿Cancelar reserva?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p>Estás a punto de cancelar tu reserva para:</p>
                    <h5 id="nombreEventoCancel" class="fw-bold text-dark mb-4"></h5>
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
                        <i class="bi bi-bag-check-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h3 class="fw-light mb-3">¡Pedido realizado con éxito!</h3>
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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Miramos si la URL tiene "?pedido_exito=true"
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('pedido_exito')) {
            // Abrimos el modal de éxito
            var modalPedido = new bootstrap.Modal(document.getElementById('modalPedidoExito'));
            modalPedido.show();
            
            // Limpiamos la URL para que no vuelva a salir al recargar
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });

    // Añade esto dentro del "if (urlParams.has('pedido_exito')) {"
    const tabPedidos = document.querySelector('[data-bs-target="#pedidos"]');
    if (tabPedidos) {
        bootstrap.Tab.getInstance(tabPedidos)?.show() || new bootstrap.Tab(tabPedidos).show();
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalCancel = document.getElementById('modalConfirmarCancelacion');
        if (modalCancel) {
            modalCancel.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var idVisita = button.getAttribute('data-id-visita');
                var nombreEvento = button.getAttribute('data-nombre-evento');
                
                // Actualizamos el texto y el enlace del botón de borrado
                modalCancel.querySelector('#nombreEventoCancel').textContent = nombreEvento;
                modalCancel.querySelector('#btnConfirmarBorrado').href = 'cancelar_reserva.php?id_visita=' + idVisita;
            });
        }
    });


    document.addEventListener("DOMContentLoaded", function() {
        // 1. Mirar si la URL tiene un "ancla" (ej: #experiencias)
        const hash = window.location.hash;
        
        if (hash) {
            // Buscamos el botón de la pestaña que coincida con ese ID
            const targetTab = document.querySelector(`[data-bs-target="${hash}"]`);
            if (targetTab) {
                // Activamos la pestaña usando la función de Bootstrap
                const tabTrigger = new bootstrap.Tab(targetTab);
                tabTrigger.show();
            }
        }
    });

    </script>


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
                <p><i class="bi bi-envelope-fill me-2"></i> vinosriverview@outlook.com</p>
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