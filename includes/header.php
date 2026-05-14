<?php
if (!isset($base_url)) {
    $base_url = '/vinos-riverview';
}

if (!isset($page_title)) {
    $page_title = 'Vinos Riverview';
}

if (!isset($page_css)) {
    $page_css = '';
}

if (!isset($total_cesta)) {
    $total_cesta = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>

    <link href="<?= $base_url ?>/css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <?php if (!empty($page_css)): ?>
        <link href="<?= $base_url ?>/css/<?= htmlspecialchars($page_css) ?>" rel="stylesheet">
    <?php endif; ?>

    <script src="<?= $base_url ?>/css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
</head>
<body>

<header class="site-header">
    <nav class="navbar bg-white fixed-top navbar-riverview">
        <div class="container-fluid position-relative">

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar"
                aria-label="Abrir menú de navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="<?= $base_url ?>/index.php">
                <img src="<?= $base_url ?>/img/logo.png" alt="Vinos Riverview" height="102">
            </a>

            <div class="d-flex gap-3 align-items-center">

                <a
                    href="#"
                    class="text-dark"
                    data-bs-toggle="collapse"
                    data-bs-target="#searchBar"
                    aria-expanded="false"
                    onclick="window.scrollTo({ top: 0, behavior: 'smooth' });">
                    <i class="bi bi-search icon-nav"></i>
                </a>

                <?php if (!isset($_SESSION['usuario_id'])): ?>
                    <a href="<?= $base_url ?>/php/login.php?volver=<?= urlencode($_SERVER['REQUEST_URI']); ?>" class="text-dark">
                        <i class="bi bi-person icon-nav"></i>
                    </a>
                <?php else: ?>
                    <div class="dropdown">
                        <a href="#" class="text-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-fill icon-nav-user"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <h6 class="dropdown-header">
                                    Hola, <?= htmlspecialchars($_SESSION['nombre']); ?>
                                </h6>
                            </li>
                            <li><hr class="dropdown-divider"></li>

                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
                                <li>
                                    <a class="dropdown-item fw-bold text-vino" href="<?= $base_url ?>/admin/panel.php">Panel de Control</a>
                                </li>
                            <?php elseif (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= $base_url ?>/php/perfil.php">Mi Perfil</a>
                                </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= $base_url ?>/php/logout.php">Cerrar Sesión</a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>

                <a href="<?= $base_url ?>/php/carrito.php" class="text-dark position-relative text-decoration-none">
                    <i class="bi bi-cart icon-nav"></i>
                    <?php if ($total_cesta > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-vino-carrito">
                            <?= $total_cesta; ?>
                            <span class="visually-hidden">productos</span>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <aside
                class="offcanvas offcanvas-start"
                tabindex="-1"
                id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h2 class="offcanvas-title h5" id="offcanvasNavbarLabel">Menú</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"></button>
                </div>

                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="<?= $base_url ?>/index.php">Inicio</a>
                        </li>

                        <li class="nav-item">
                            <div class="d-flex align-items-center justify-content-between">
                                <a class="nav-link w-100" href="<?= $base_url ?>/php/tienda.php">Tienda</a>
                                <a
                                    class="nav-link px-3"
                                    href="#menu-tienda"
                                    role="button"
                                    data-bs-toggle="collapse"
                                    aria-expanded="false"
                                    aria-controls="menu-tienda">
                                    <i class="bi bi-chevron-down small"></i>
                                </a>
                            </div>

                            <div class="collapse" id="menu-tienda">
                                <ul class="nav flex-column ps-4 border-start ms-2 my-1 bg-light bg-opacity-25">
                                    <li class="nav-item">
                                        <a class="nav-link py-1" href="<?= $base_url ?>/php/tienda.php?categoria=vinos">Vinos</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1" href="<?= $base_url ?>/php/tienda.php?categoria=quesos">Quesos</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1" href="<?= $base_url ?>/php/tienda.php?categoria=embutidos">Embutidos</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>/php/experiencias.php">Experiencias / Catas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>/php/nosotros.php">Sobre Nosotros</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>/php/contacto.php">Contacto</a>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </nav>

    <section class="collapse bg-white shadow-sm buscador-superior" id="searchBar" aria-label="Buscador de productos">
        <div class="container py-4">
            <form action="<?= $base_url ?>/php/tienda.php" method="GET" class="d-flex justify-content-center align-items-center gap-2">
                <input
                    type="text"
                    name="q"
                    class="form-control input-busqueda"
                    placeholder="Buscar producto...">

                <button class="btn btn-lupa" type="submit" aria-label="Buscar">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </section>
</header>
