<?php
session_start();
require_once '../config.php';

$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

$mensaje_enviado = false;
$error_envio = false;

// 1. IMPORTAR LAS CLASES DE PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. CARGAR LOS ARCHIVOS
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// 3. LÓGICA SI SE ENVÍA EL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nombre = htmlspecialchars(strip_tags($_POST['nombre']));
    $email_usuario = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $asunto = htmlspecialchars(strip_tags($_POST['asunto']));
    $mensaje_usuario = htmlspecialchars(strip_tags($_POST['mensaje'])); 
    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURACIÓN MAILTRAP USANDO LAS CONSTANTES DE CONFIG.PHP ---
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Port       = SMTP_PORT; 
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // --- UN SOLO ENVÍO PARA TODOS (Evita error de "Too many emails") ---
        $mail->setFrom(EMAIL_TIENDA, 'Vinos Riverview');
        
        // Destinatario principal (Tú)
        $mail->addAddress(EMAIL_TIENDA, 'Administrador'); 
        
        // Cliente en Copia Oculta (BCC)
        $mail->addBCC($email_usuario, $nombre); 

        $mail->addReplyTo($email_usuario, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'Consulta Web - ' . $asunto;
        $mail->Body    = "
            <div style='font-family: Arial; border: 1px solid #722F37; padding: 20px;'>
                <h2 style='color: #722F37;'>Nueva consulta de: $nombre</h2>
                <p><strong>Email del cliente:</strong> $email_usuario</p>
                <p><strong>Motivo:</strong> $asunto</p>
                <hr>
                <p><strong>Mensaje:</strong></p>
                <p>".nl2br($mensaje_usuario)."</p>
                <br>
                <p style='font-size: 0.8em; color: gray;'>Este es un mensaje de prueba para el proyecto Vinos Riverview.</p>
            </div>
        ";

        // Cambiamos el envío para que active la caja verde si funciona
        if($mail->send()) {
            $mensaje_enviado = true;
        }

    } catch (Exception $e) {
        $error_envio = true;
        // Solo para depuración si falla:
        echo "Error: {$mail->ErrorInfo}"; 
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Vinos Riverview</title>
    
    <link href="../css/bootstrap-5.3.8-dist/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../css/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
    <link href="../css/tienda.css" rel="stylesheet"> 
    <link href="../css/contacto.css" rel="stylesheet"> 
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

                    <?php if (!isset($_SESSION['usuario_id'])): ?>
                        <a href="./login.php?volver=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-dark">
                            <i class="bi bi-person icon-nav"></i>
                        </a>
                    <?php else: ?>
                        <div class="dropdown">
                            <a href="#" class="text-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-fill icon-nav-user"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h6></li>
                                <li><hr class="dropdown-divider"></li>
                               <?php if ($_SESSION['rol'] === 'administrador'): ?>
                                        <li><a class="dropdown-item fw-bold text-vino" href="../admin/panel.php">Panel de Control</a></li>

                                    <?php elseif ($_SESSION['rol'] === 'cliente'): ?>
                                        <li><a class="dropdown-item" href="./perfil.php">Mi Perfil</a></li>
                                    <?php endif; ?>
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
        <section class="hero-contacto text-center mb-5 shadow-sm">
            <div class="container">
                <h1 class="display-3 fw-light mb-3">Estamos aquí para ti</h1>
                <p class="lead mx-auto" style="max-width: 600px; opacity: 0.9;">
                    Ya sea para una recomendación de maridaje, dudas sobre tu pedido o visitas a la bodega, escríbenos.
                </p>
            </div>
        </section>

        <section class="container mb-5 pb-5">
            <div class="row g-5">
                <div class="col-lg-5">
                    <div class="pe-lg-4">
                        <h3 class="h2 text-vino mb-4 fw-light">Hablemos</h3>
                        <p class="text-muted mb-5">Detrás de cada botella hay una historia. Cuéntanos la tuya o pregúntanos cualquier duda.</p>
                        
                        <div class="contacto-info-box d-flex align-items-center mb-4">
                            <div class="contacto-icon shadow-sm me-4"><i class="bi bi-geo-alt"></i></div>
                            <div>
                                <h5 class="h6 fw-bold text-dark mb-1">Visítanos</h5>
                                <p class="text-muted mb-0">Calle del Vino, 12<br>26001 Logroño, La Rioja</p>
                            </div>
                        </div>

                        <div class="contacto-info-box d-flex align-items-center mb-4">
                            <div class="contacto-icon shadow-sm me-4"><i class="bi bi-telephone"></i></div>
                            <div>
                                <h5 class="h6 fw-bold text-dark mb-1">Llámanos</h5>
                                <p class="text-muted mb-0">+34 912 345 678<br><span class="small">L-V: 9:00 - 18:00</span></p>
                            </div>
                        </div>

                        <div class="contacto-info-box d-flex align-items-center mb-4">
                            <div class="contacto-icon shadow-sm me-4"><i class="bi bi-envelope"></i></div>
                            <div>
                                <h5 class="h6 fw-bold text-dark mb-1">Escríbenos</h5>
                                <p class="text-muted mb-0">vinosriverview@outlook.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card card-contacto h-100">
                        <div class="card-body p-4 p-md-5">
                            <?php if ($mensaje_enviado): ?>
                                <div class="alert alert-success d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" role="alert">
                                    <i class="bi bi-check-circle-fill me-3 fs-3 text-success"></i>
                                    <div>
                                        <strong class="d-block mb-1">¡Mensaje enviado con éxito!</strong> 
                                        Revisa tu bandeja de Mailtrap, ahí tienes los correos.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($error_envio): ?>
                                <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-3 fs-3 text-danger"></i>
                                    <div>
                                        <strong class="d-block mb-1">Hubo un problema.</strong> 
                                        No se pudo enviar el mensaje. Revisa tu conexión.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <h3 class="h4 text-vino mb-4 fw-bold">Envíanos un mensaje</h3>
                            <form action="contacto.php" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Tu nombre" required>
                                            <label for="nombre" class="text-muted">Nombre completo</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" required>
                                            <label for="email" class="text-muted">Correo electrónico</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <select class="form-select" id="asunto" name="asunto" aria-label="Asunto">
                                                <option value="Duda sobre un vino" selected>Duda sobre un vino / producto</option>
                                                <option value="Problema con mi pedido">Problema con mi pedido</option>
                                                <option value="Eventos y catas">Información sobre eventos y catas</option>
                                                <option value="Otro">Otro motivo</option>
                                            </select>
                                            <label for="asunto" class="text-muted">¿Sobre qué quieres hablarnos?</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="mensaje" name="mensaje" placeholder="Escribe aquí tu mensaje" style="height: 150px" required></textarea>
                                            <label for="mensaje" class="text-muted">Escribe aquí tu mensaje...</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-vino btn-lg w-100 py-3 rounded-3 shadow-sm">
                                            <i class="bi bi-send-fill me-2"></i> ENVIAR MENSAJE
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
</body>
</html>