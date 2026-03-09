<?php
// 1. Iniciar sesión
session_start();
require_once '../config.php';

// Si el usuario ya está logueado...
if (isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador') {
        header("Location: ../admin/panel.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}


try {
    // Intentamos conectar usando PDO (PHP Data Objects)
    // PDO es más seguro y permite usar sentencias preparadas
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    // Configuramos PDO para que nos avise si hay errores (Excepciones)
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    error_log("Error de conexión: " .$e->getMessage()); // Log secreto
    // Si falla la conexión, capturamos el error y paramos todo para no mostrar datos sensibles
    echo "Error de conexión. Inténtalo más tarde.";
    exit; // Para que no intente ejecutar el resto si falla la conexion.
}


$mensaje_error = "";

// 2. Procesar Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // 3. Buscar usuario
    $sql = "SELECT id_usuario, nombre, contrasena, rol FROM usuario WHERE email = :email LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Verificar Contraseña
    if ($usuario && password_verify($pass, $usuario['contrasena'])) {
        
        // --- AQUÍ EMPIEZA LA MAGIA DE LA SESIÓN ---
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        // --- ESTO ES LO QUE TE FALTABA CAMBIAR ---
        if ($_SESSION['rol'] == 'administrador') {
            header("Location: ../admin/panel.php");
        } else {
            // Verificamos si hay una petición de "volver" (del input hidden)
            if (isset($_POST['volver']) && !empty($_POST['volver'])) {
                header("Location: " . $_POST['volver']);
            } else {
                // Si no hay nada, al inicio por defecto
                header("Location: ../index.php");
            }
        }
        exit();
        // ------------------------------------------
        
    } else {
        $mensaje_error = "El correo o la contraseña son incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Vinos Riverview</title>
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <link href="../css/login.css" rel="stylesheet">
</head>
<body>
    
<header>
    <nav class="navbar bg-white fixed-top">
        <div class="container-fluid position-relative">
            
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="../index.php">
                <img src="../img/logo.png" alt="Vinos Riverview" height="102">
            </a>

            <div class="d-flex gap-3 align-items-center">
                <a href="#" class="text-dark" data-bs-toggle="collapse" data-bs-target="#searchBar" onclick="window.scrollTo({ top: 0, behavior: 'smooth' });">
                    <i class="bi bi-search" style="font-size: 1.5rem;"></i>
                </a>
                <a href="./login.php" class="text-dark">
                    <i class="bi bi-person" style="font-size: 1.5rem;"></i>
                </a>
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
        
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item"><a class="nav-link" href="../index.php">Inicio</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Tienda</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Vinos</a></li>
                                <li><a class="dropdown-item" href="#">Quesos</a></li>
                                <li><a class="dropdown-item" href="#">Embutidos</a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="./experiencias.php">Experiencias</a></li>
                        <li class="nav-item"><a class="nav-link" href="./nosotros.php">Sobre Nosotros</a></li>
                        <li class="nav-item"><a class="nav-link" href="./contacto.php">Contacto</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="collapse bg-white shadow-sm buscador-superior" id="searchBar">
        <div class="container py-3">
            <form action="tienda.php" method="GET" class="d-flex justify-content-center">
                <div class="input-group w-50">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Buscar..." style="box-shadow: none;">
                    <button class="btn btn-vino" type="submit">BUSCAR</button>
                </div>
            </form>
        </div>
    </div>
</header>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-login p-4 p-md-5 mb-3 bg-white">
                <h3 class="text-center text-vino mb-4 fw-light">Bienvenido</h3>

                <?php if(!empty($mensaje_error)): ?>
                    <div class="alert alert-danger text-center"><?php echo $mensaje_error; ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST" novalidate class="needs-validation">
                    
                    <input type="hidden" name="volver" value="<?php echo isset($_GET['volver']) ? htmlspecialchars($_GET['volver']) : (isset($_POST['volver']) ? htmlspecialchars($_POST['volver']) : ''); ?>">

                    <div class="mb-3">
                        <label class="form-label text-muted small">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" required>
                        <div class="invalid-feedback">Por favor, introduce tu correo.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                        <div class="invalid-feedback">Por favor, introduce tu contraseña.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-vino text-uppercase" style="letter-spacing: 2px;">Entrar</button>
                    </div>

                    <div class="text-center mt-4">
                        <span class="text-muted small">¿No tienes cuenta?</span>
                        <a href="./registro.php" class="text-vino fw-bold text-decoration-none ms-1">Regístrate aquí</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
    
<footer class="footer-riverview pt-5 pb-4">
    <div class="container text-center text-md-start">
        <div class="row text-center text-md-start">
            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold text-vino-claro">Vinos Riverview</h5>
                <p>Tradición, sabor y la mejor selección de nuestra tierra.</p>
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
                <p class ="derechos">© 2025 <strong>Vinos Riverview</strong>. Todos los derechos reservados.</p>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="text-center text-md-end">
                    <ul class="list-unstyled list-inline">
                        <li class="list-inline-item"><a href="#" class="btn-floating btn-sm fs-4"><i class="bi bi-facebook"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="btn-floating btn-sm fs-4"><i class="bi bi-twitter-x"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="btn-floating btn-sm fs-4"><i class="bi bi-instagram"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    });
</script>
    
</body>
</html>