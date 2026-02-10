<?php
// =======================================================
// 1. CONEXIÓN A LA BASE DE DATOS (PDO)
// =======================================================

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}
// Definimos las credenciales de la base de datos
$url = 'mysql:dbname=vinos_riverview;host=localhost';
$user = 'root';
$pass_db = ""; 

try {
    // Intentamos conectar usando PDO (PHP Data Objects)
    // PDO es más seguro y permite usar sentencias preparadas
    $conexion = new PDO($url, $user, $pass_db);
    
    // Configuramos PDO para que nos avise si hay errores (Excepciones)
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Si falla la conexión, capturamos el error y paramos todo para no mostrar datos sensibles
    echo "Fallo la conexión: " . $e->getMessage();
    exit();
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
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($pass_usuario) || empty($confirm_pass)) {
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
                    // Esperamos 2 segundos y mandamos al usuario al Login
                    header("refresh:2;url=login.php"); 
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
                
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a class="navbar-brand position-absolute top-50 start-50 translate-middle" href="../index.php">
                    <img src="../img/logo.png" alt="Vinos Riverview" height="102">
                </a>

                <div class="d-flex gap-3 align-items-center">
                    <a href="#" class="text-dark" data-bs-toggle="collapse" data-bs-target="#searchBar">
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
                            <li class="nav-item"><a class="nav-link" href="./experiencias.php">Experiencias / Catas</a></li>
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
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Buscar...">
                        <button class="btn btn-vino" type="submit">BUSCAR</button>
                    </div>
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
                        <label class="form-label small text-muted">Dirección de Envío</label>
                        <div class="input-group">
                            <input type="text" name="direccion" class="form-control" placeholder="Calle, número, piso, ciudad...">
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
                <div class="col-md-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-vino-claro">Vinos Riverview</h5>
                    <p>Tradición, sabor y la mejor selección de nuestra tierra.</p>
                </div>
                <div class="col-md-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-vino-claro">Explorar</h5>
                    <p><a href="../index.php" class="footer-link">Inicio</a></p>
                    <p><a href="./tienda.php" class="footer-link">Tienda</a></p>
                </div>
                <div class="col-md-4 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-vino-claro">Contacto</h5>
                    <p><i class="bi bi-house-door-fill me-2"></i> Calle del Vino, 12, La Rioja</p>
                    <p><i class="bi bi-envelope-fill me-2"></i> info@vinosriverview.com</p>
                </div>
            </div>
            <hr class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <p>© 2025 <strong>Vinos Riverview</strong>. Todos los derechos reservados.</p>
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