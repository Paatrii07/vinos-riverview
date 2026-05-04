<?php

session_start(); // Iniciamos la sesión para que PHP sepa quien es el usuario y que tiene en su carrito.
// =======================================================
// 1. CONEXIÓN A LA BASE DE DATOS (PDO)
// =======================================================
require_once '../config.php';
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

// Variables para guardar mensajes de feedback al usuario
$mensaje_error = "";
$mensaje_exito = "";

// =======================================================
// 2. PROCESAR EL FORMULARIO (BACKEND)
// =======================================================
// Solo entramos aquí si el usuario ha pulsado el botón "Registrarme" (Método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- PASO A: SANITIZACIÓN (LIMPIEZA) ---
    // Limpiamos los datos que vienen del formulario para evitar ataques XSS (scripts maliciosos).
    // trim() quita espacios en blanco al inicio y final.
    $nombre = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_SPECIAL_CHARS);
    $apellidos = filter_var(trim($_POST['apellidos']), FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL); // Filtro específico para emails
    $telefono = filter_var(trim($_POST['telefono']), FILTER_SANITIZE_SPECIAL_CHARS);
    $direccion = filter_var(trim($_POST['direccion']), FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Las contraseñas NO se sinitizan igual, se cogen tal cual para no romper caracteres especiales permitidos
    $pass_usuario = $_POST['password']; 
    $confirm_pass = $_POST['confirm_password'];

    // --- PASO B: VALIDACIÓN EN PHP (SEGURIDAD) ---
    // Aunque validemos en el HTML (Frontend), SIEMPRE hay que validar en PHP (Backend)
    // porque el HTML se puede trucar.

    // 1. Comprobar campos obligatorios
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($direccion) || empty($pass_usuario) || empty($confirm_pass)) {
        $mensaje_error = "Por favor, rellena todos los campos obligatorios (*).";
    }
    // 2. Comprobar que el email tiene formato de email real (@, ., etc)
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "El formato del correo electrónico no es válido.";
    }
    // 3. Comprobar que las dos contraseñas son idénticas
    elseif ($pass_usuario != $confirm_pass) {
        $mensaje_error = "Las contraseñas no coinciden.";
    } 
    else {
        // --- PASO C: COMPROBAR DUPLICADOS ---
        // Consultamos a la BBDD si el email ya existe.
        // Usamos sentencias preparadas (prepare + bindParam) para evitar INYECCIÓN SQL.
        $sql_check = "SELECT id_usuario FROM usuario WHERE email = :email";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bindParam(':email', $email); // Vinculamos el parámetro de forma segura
        $stmt_check->execute();

        // Si rowCount es mayor que 0, es que ya hay alguien con ese email
        if ($stmt_check->rowCount() > 0) {
            $mensaje_error = "Este correo electrónico ya está registrado.";
        } else {
            // --- PASO D: INSERTAR USUARIO ---
            
            // IMPORTANTE: Nunca guardamos la contraseña en texto plano.
            // La "hasheamos" (encriptamos) usando el algoritmo por defecto de PHP (Bcrypt actualmente).
            $pass_cifrada = password_hash($pass_usuario, PASSWORD_DEFAULT);

            // Preparamos la consulta de inserción
            $sql_insert = "INSERT INTO usuario (nombre, apellidos, email, telefono, direccion, contrasena, rol) 
                           VALUES (:nom, :ape, :email, :tel, :dir, :pass, 'cliente')";
            
            try {
                $stmt = $conexion->prepare($sql_insert);
                // Vinculamos cada variable a su hueco en la SQL
                $stmt->bindParam(':nom', $nombre);
                $stmt->bindParam(':ape', $apellidos);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':tel', $telefono);
                $stmt->bindParam(':dir', $direccion);
                $stmt->bindParam(':pass', $pass_cifrada); // Guardamos la cifrada, NO la original
                
                // Si se ejecuta correctamente...
                if ($stmt->execute()) {
    $mensaje_exito = "¡Cuenta creada con éxito! Redirigiendo...";
    
    // Si tenemos una página a la que volver, se la pasamos al login
    $url_retorno = !empty($_POST['return_to']) ? "?return_to=" . urlencode($_POST['return_to']) : "";
    header("refresh:2;url=login.php" . $url_retorno); 
}
            } catch(PDOException $e) {
                // Si falla la inserción (ej: error de BBDD), guardamos el error
                $mensaje_error = "Error al registrar: " . $e->getMessage();
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
    <link href="../css/registro.css" rel="stylesheet">
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

    <div class="bg-image"></div>

    <div class="container d-flex justify-content-center align-items-center min-vh-100 position-relative">
        
        <div class="card card-registro shadow-lg p-4">
            <div class="card-body">
                
                <div class="text-center mb-4">
                    <h3 class="fw-light text-uppercase" style="letter-spacing: 2px; color: #722F37;">Crear Cuenta</h3>
                    <p class="text-muted small">Los campos con <span class="text-danger">*</span> son obligatorios</p>
                </div>

                <?php if (!empty($mensaje_error)): ?>
                    <div class="alert alert-danger text-center py-2"><?php echo $mensaje_error; ?></div>
                <?php endif; ?>

                <?php if (!empty($mensaje_exito)): ?>
                    <div class="alert alert-success text-center py-2"><?php echo $mensaje_exito; ?></div>
                <?php endif; ?>

                <form action="registro.php" method="POST" novalidate class="needs-validation">
                    <input type="hidden" name="return_to" value="<?php echo isset($_GET['return_to']) ? htmlspecialchars($_GET['return_to']) : (isset($_POST['return_to']) ? htmlspecialchars($_POST['return_to']) : ''); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Nombre <span class="text-danger">*</span></label>
                            
                                <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" required>
                            
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" name="apellidos" class="form-control " placeholder="Tus apellidos" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Correo Electrónico <span class="text-danger">*</span></label>
                        <div class="input-group has-validation"> 
                            
                            <input type="email" name="email" class="form-control" 
                                placeholder="nombre@ejemplo.com" required id="emailInput">
                            
                            <div class="invalid-feedback">
                                Por favor, escribe un correo válido.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Teléfono</label>
                        <div class="input-group">
                            <input type="tel" name="telefono" class="form-control" placeholder="+34 600 000 000"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="right" 
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Lo usaremos solo para coordinar la entrega de tus pedidos.">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Dirección de Envío <span class="text-danger">*</span></label>
                        <div class="input-group has-validation">
                            <input type="text" name="direccion" class="form-control" 
                                placeholder="Calle, número, piso, ciudad..." required>
                            
                            <div class="invalid-feedback">
                                Por favor, introduce una dirección para tus pedidos.
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" placeholder="******" required
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-custom-class="custom-tooltip"
                                        data-bs-title="Recomendamos usar al menos 8 caracteres.">
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label small text-muted">Confirmar <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="******" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-vino text-uppercase" style="letter-spacing: 2px;">
                            Registrarme
                        </button>
                    </div>

                </form>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">¿Ya tienes cuenta?</p>
                    <a href="login.php" class="text-decoration-none fw-bold" style="color: #722F37;">Inicia Sesión aquí</a>
                </div>

            </div>
        </div>
    </div>

    <footer class="footer-riverview pt-5 pb-4 mt-5">
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <script>
        
    // Esperamos a que todo el HTML se cargue (DOM Content Loaded)
    document.addEventListener("DOMContentLoaded", function() {
        
        // 1. ACTIVAR TOOLTIPS DE BOOTSTRAP
        // Buscamos todos los elementos con data-bs-toggle="tooltip" y los inicializamos
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // 2. ACTIVAR VALIDACIÓN BONITA DE BOOTSTRAP
        // Buscamos todos los formularios que tengan la clase .needs-validation
        var forms = document.querySelectorAll('.needs-validation')

        // Para cada formulario encontrado...
        Array.prototype.slice.call(forms).forEach(function (form) {
            // Escuchamos cuando el usuario le da al botón "Submit" (Registrarme)
            form.addEventListener('submit', function (event) {
                // Si el formulario NO es válido (campos vacíos o email mal puesto)...
                if (!form.checkValidity()) {
                    event.preventDefault()  // Evitamos que se envíe al servidor
                    event.stopPropagation() // Paramos el evento
                }

                // Añadimos la clase 'was-validated' al formulario.
                // Esto hace que Bootstrap muestre los bordes rojos/verdes y los mensajes de error.
                form.classList.add('was-validated')
            }, false)
        })
    });
    </script>
</body>
</html>