<?php
session_start();

// SEGURIDAD
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Riverview</title>
    
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/panel.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">

    <nav class="admin-header d-flex justify-content-between align-items-center fixed-top">
        <div class="brand-text fw-light">
            Admin <strong>Riverview</strong>
        </div>
        
        <div>
            <span class="me-3 text-muted small d-none d-md-inline">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="../php/logout.php" class="logout-link rounded-0">
                Cerrar Sesión <i class="bi bi-x-lg ms-1"></i>
            </a>
        </div>
    </nav>

    <div style="margin-top: 70px;"></div>

    <div class="container-fluid main-layout flex-grow-1">
        <div class="row h-100">
            
            <div class="col-md-2 sidebar-menu d-flex flex-column">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a href="panel.php" class="nav-link active">
                            <i class="bi bi-house-door me-2"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link">
                            <i class="bi bi-bag me-2"></i> Productos
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link">
                            <i class="bi bi-cup-straw me-2"></i> Catas
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="#" class="nav-link">
                            <i class="bi bi-receipt me-2"></i> Ventas
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a href="#" class="nav-link text-muted">
                            <i class="bi bi-gear me-2"></i> Configuración
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-10 content-area d-flex flex-column">
                
                <div class="mb-5 border-bottom pb-2">
                    <h3 class="fw-light text-secondary">Resumen del día de hoy:</h3>
                    <p class="text-vino lead fw-normal"><?php echo date('d \d\e F, Y'); ?></p>
                </div>

                <div class="row mb-5">
                    <div class="col-md-4 mb-4">
                        <div class="card card-counter h-100 p-4">
                            <h6 class="text-secondary text-uppercase small mb-3">Pedidos Hoy</h6>
                            <div class="d-flex align-items-baseline">
                                <h1 class="display-3 fw-bold text-dark mb-0 me-3">3</h1>
                                <span class="text-warning small"><i class="bi bi-clock"></i> Pendientes</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 mb-4">
                        <div class="card card-counter h-100 p-4">
                            <h6 class="text-secondary text-uppercase small mb-3">Próxima Cata</h6>
                            <h4 class="fw-normal text-dark mb-1">Mañana, 11:00H</h4>
                            <div class="mt-3">
                                <p class="mb-1 small text-muted">Ocupación: 12/20 plazas</p>
                                <div class="progress" style="height: 3px;">
                                    <div class="progress-bar" style="background-color: #722F37; width: 60%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="text-muted fw-light mb-3">Accesos Rápidos:</h5>
                <div class="row mb-auto"> <div class="col-md-3 mb-3">
                        <button class="btn btn-minimal w-100 p-3 text-start">
                            [ + ] Añadir nuevo vino
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-minimal w-100 p-3 text-start">
                            [ + ] Añadir nueva cata
                        </button>
                    </div>
                </div>

                <footer class="mt-5 pt-4 text-center">
                    <hr style="opacity: 0.1; border-color: #722F37;">
                    <p class="text-muted small mb-0 pb-4">
                        &copy; 2025 <strong style="color: #722F37;">Vinos Riverview</strong>. 
                        Todos los derechos reservados.
                    </p>
                </footer>

            </div> </div>
    </div>

<script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>