<body>
<header>
    <nav class="navbar bg-white fixed-top">
        <div class="container-fluid position-relative">
            
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="index.php">
                <img src="./img/logo.png" alt="Vinos Riverview" height="102">
            </a>

            <div class="d-flex gap-3 align-items-center">
                <a href="#" class="text-dark" data-bs-toggle="collapse" data-bs-target="#searchBar"><i class="bi bi-search"></i></a>

                <?php if (!isset($_SESSION['usuario_id'])): ?>
                    <a href="<?php echo $ruta; ?>php/login.php" class="text-dark"><i class="bi bi-person"></i></a>
                <?php else: ?>
                    <div class="dropdown">
                        <a href="#" class="text-dark dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-fill"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><h6 class="dropdown-header">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h6></li>
                            <li><a class="dropdown-item" href="<?php echo $ruta; ?>php/perfil.php">Mi Perfil</a></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $ruta; ?>php/logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <a href="<?php echo $ruta; ?>carrito.php" class="text-dark"><i class="bi bi-cart"></i></a>
            </div>
        
            <div class="offcanvas offcanvas-start" id="offcanvasNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <div class="d-flex align-items-center justify-content-between">
                                <a class="nav-link w-100" href="<?php echo $ruta; ?>../php/tienda.php">Tienda</a>
                                <a class="nav-link px-3" href="#menu-tienda" role="button" data-bs-toggle="collapse"><i class="bi bi-chevron-down small"></i></a>
                            </div>
                            <div class="collapse" id="menu-tienda">
                                <ul class="nav flex-column ps-4 border-start ms-2 my-1 bg-light bg-opacity-25">
                                    <li><a class="nav-link py-1" href="<?php echo $ruta; ?>php/tienda.php?categoria=vinos">Vinos</a></li>
                                    <li><a class="nav-link py-1" href="<?php echo $ruta; ?>php/tienda.php?categoria=quesos">Quesos</a></li>
                                    <li><a class="nav-link py-1" href="<?php echo $ruta; ?>php/tienda.php?categoria=embutidos">Embutidos</a></li>
                                </ul>
                            </div>
                        </li> 
                        <li class="nav-item"><a class="nav-link" href="#">Experiencias / Catas</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Sobre Nosotros</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Contacto</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="collapse bg-white shadow-sm buscador-superior" id="searchBar">
        <div class="container py-3">
            <form action="<?php echo $ruta; ?>php/tienda.php" method="GET" class="d-flex justify-content-center">
                <div class="input-group w-50">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Buscar vino, queso...">
                    <button class="btn btn-vino" type="submit">BUSCAR</button>
                </div>
            </form>
        </div>
    </div>
</header>