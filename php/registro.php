
<?php

// 1. Incluimos la conexión 
$url = 'mysql:dbname=vinos_riverview;host=localhost';
$user = 'root';
$pass = "";

try {
    $conexion = new PDO($url, $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Fallo la conexión: " . $e->getMessage();
}

$mensaje_error = "";
$mensaje_exito = "";

// 2. Si el formulario se ha enviado...
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recogemos los datos y limpiamos espacios
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);   
    $direccion = trim($_POST['direccion']); 
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];

    // Validaciones básicas
    if ($pass !== $pass_confirm) {
        $mensaje_error = "Las contraseñas no coinciden.";
    } else {
        // 3. ENCRIPTAR LA CONTRASEÑA (Seguridad obligatoria)
        // Nunca guardamos la contraseña tal cual, la convertimos en un hash
        $password_segura = password_hash($pass, PASSWORD_BCRYPT);

        try {
            // 4. PREPARAR LA CONSULTA (PDO)
            // Fíjate que usamos tus nombres de columna: id_usuario, contrasena...
            $sql = "INSERT INTO usuario (nombre, apellidos, email, telefono, direccion, contrasena, rol) VALUES (:nom, :ape, :email, :tel, :dir, :pass, 'cliente')";
            
            $stmt = $conexion->prepare($sql);
            
            // 5. VINCULAR PARÁMETROS (bindParam)
            $stmt->bindParam(':nom', $nombre);
            $stmt->bindParam(':ape', $apellidos);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':tel', $telefono);   
            $stmt->bindParam(':dir', $direccion);
            $stmt->bindParam(':pass', $password_segura);

            // 6. EJECUTAR
            if ($stmt->execute()) {
                $mensaje_exito = "¡Registro completado! <a href='login.php'>Inicia sesión aquí</a>";
            }

        } catch(PDOException $e) {
            // Si el error contiene "Duplicate entry", es que el email ya existe
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $mensaje_error = "Ese correo electrónico ya está registrado.";
            } else {
                $mensaje_error = "Error en el registro: " . $e->getMessage();
            }
        }
    }
}
    
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Vinos Riverview</title>
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <link href = "../css/registro.css" rel="stylesheet">
    

</head>

<body>
    <!-- Menú -->

<header>
    <nav class="navbar bg-white fixed-top">
        <div class="container-fluid position-relative">
            
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
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
                    <i class="bi bi-search" style="font-size: 1.5rem;"></i>
                </a>

                <a href="./login.php" class="text-dark">
                    <i class="bi bi-person" style="font-size: 1.5rem;"></i>
                </a>

                <a href="./carrito.php" class="text-dark">
                    <i class="bi bi-cart" style="font-size: 1.5rem;"></i>
                </a>
            </div>
        
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="../index.php">Inicio</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Tienda</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Vinos</a></li>
                                <li><a class="dropdown-item" href="#">Quesos</a></li>
                                <li><a class="dropdown-item" href="#">Embutidos</a></li>
                            </ul>
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
            </div>
        </div>
    </nav>

    <div class="collapse bg-white shadow-sm buscador-superior" id="searchBar">
        <div class="container py-3">
            <form action="tienda.php" method="GET" class="d-flex justify-content-center">
                <div class="input-group w-50">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Buscar vino, queso..." style="box-shadow: none;">
                    <button class="btn btn-vino" type="submit">BUSCAR</button>
                </div>
            </form>
        </div>
    </div>

</header>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card card-registro p-4 p-md-5 bg-white">
                    <h3 class="text-center text-vino mb-4 fw-light">Crear Cuenta</h3>

                    <?php if(!empty($mensaje_error)): ?>
                        <div class="alert alert-danger text-center"><?php echo $mensaje_error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($mensaje_exito)): ?>
                        <div class="alert alert-success text-center"><?php echo $mensaje_exito; ?></div>
                    <?php else: ?>

                    <form action="registro.php" method="POST">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                        
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Teléfono</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Dirección Completa</label>
                            <input type="text" name="direccion" class="form-control" placeholder="Calle, número, piso...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>


                        <div class="mb-3">
                            <label class="form-label text-muted small">Contraseña</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small">Confirmar Contraseña</label>
                            <input type="password" name="password_confirm" class="form-control" required minlength="6">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-vino text-uppercase" style="letter-spacing: 2px;">
                                Registrarse
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <span class="text-muted small">¿Ya tienes cuenta?</span>
                            <a href="./login.php" class="text-vino fw-bold text-decoration-none ms-1">Iniciar Sesión</a>
                        </div>
                    </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

<!-- Footer -->

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
                <p>© 2025 <strong>Vinos Riverview</strong>. Todos los derechos reservados.</p>
            </div>

            <div class="col-md-5 col-lg-4">
                <div class="text-center text-md-end">
                    <ul class="list-unstyled list-inline">
                        <li class="list-inline-item">
                            <a href="#" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-facebook"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-twitter-x"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="btn-floating btn-sm" style="font-size: 23px;"><i class="bi bi-instagram"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</footer>
    
</body>
</html>
</html>