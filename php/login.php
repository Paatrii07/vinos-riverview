<?php
// 1. Iniciar sesión
session_start();
require_once '../config.php';

// Si el usuario YA está logueado, lo sacamos del login directamente
if (isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador') {
        header("Location: ../admin/panel.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

// 2. Conexión a la base de datos
try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Error de conexión: " .$e->getMessage());
    die("Error de conexión. Inténtalo más tarde.");
}

$mensaje_error = "";

// 3. Procesar Login (Solo cuando se pulsa el botón 'Entrar')
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $sql = "SELECT id_usuario, nombre, contrasena, rol FROM usuario WHERE email = :email LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar Contraseña
    if ($usuario && password_verify($pass, $usuario['contrasena'])) {
        
        // Creamos la sesión
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        // REDIRECCIÓN INTELIGENTE
        if ($_SESSION['rol'] == 'administrador') {
            header("Location: ../admin/panel.php");
        } else {
            // Si venimos de experiencias.php, el valor estará en el input hidden 'return_to'
            if (isset($_POST['return_to']) && !empty($_POST['return_to'])) {
                header("Location: " . $_POST['return_to']);
            } else {
                header("Location: ../index.php");
            }
        }
        exit();
        
    } else {
        $mensaje_error = "El correo o la contraseña son incorrectos.";
    }
}

// Calcular total carrito para el header (opcional si lo usas en el nav)
$total_cesta = (isset($_SESSION['carrito'])) ? array_sum($_SESSION['carrito']) : 0;
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

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-login p-4 p-md-5 mb-3 bg-white">
                <h3 class="text-center text-vino mb-4 fw-light">Bienvenido</h3>

                <?php if(!empty($mensaje_error)): ?>
                    <div class="alert alert-danger text-center"><?php echo $mensaje_error; ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST" novalidate class="needs-validation">
                    <input type="hidden" name="return_to" value="<?php echo isset($_GET['return_to']) ? htmlspecialchars($_GET['return_to']) : (isset($_POST['return_to']) ? htmlspecialchars($_POST['return_to']) : ''); ?>">

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
                        <a href="./registro.php?return_to=<?php echo isset($_GET['return_to']) ? urlencode($_GET['return_to']) : (isset($_POST['return_to']) ? urlencode($_POST['return_to']) : ''); ?>" class="text-vino fw-bold text-decoration-none ms-1">Regístrate aquí</a>
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
                <p><i class="bi bi-envelope-fill me-2"></i> info@vinosriverview.com</p>
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
                    <ul class="rrss list-unstyled list-inline">
                        <li class="list-inline-item">
                            <a href="http://www.facebook.com" class="btn-floating btn-sm"><i class="bi bi-facebook"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="http://www.x.com" class="btn-floating btn-sm"><i class="bi bi-twitter-x"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="http://www.instagram.com" class="btn-floating btn-sm"><i class="bi bi-instagram"></i></a>
                        </li>
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