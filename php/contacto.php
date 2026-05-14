<?php
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_title = 'Contacto - Vinos Riverview';
$page_css = 'contacto.css';

$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

$mensaje_enviado = false;
$error_envio = false;
$texto_error = '';

// 1. IMPORTAR LAS CLASES DE PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. CARGAR LOS ARCHIVOS
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// 3. LÓGICA SI SE ENVÍA EL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars(strip_tags($_POST['nombre'] ?? ''));
    $email_usuario = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $asunto = htmlspecialchars(strip_tags($_POST['asunto'] ?? ''));
    $mensaje_usuario = htmlspecialchars(strip_tags($_POST['mensaje'] ?? ''));

    if (!filter_var($email_usuario, FILTER_VALIDATE_EMAIL)) {
        $error_envio = true;
        $texto_error = 'Por favor, introduce un correo electrónico válido.';
    } else {
        $mail = new PHPMailer(true);

        try {
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

            $mail->setFrom(EMAIL_TIENDA, 'Vinos Riverview');
            $mail->addAddress(EMAIL_TIENDA, 'Administrador');
            $mail->addBCC($email_usuario, $nombre);
            $mail->addReplyTo($email_usuario, $nombre);

            $mail->isHTML(true);
            $mail->Subject = 'Consulta Web - ' . $asunto;
            $mail->Body = "
                <div style='font-family: Arial; border: 1px solid #722F37; padding: 20px;'>
                    <h2 style='color: #722F37;'>Nueva consulta de: $nombre</h2>
                    <p><strong>Email del cliente:</strong> $email_usuario</p>
                    <p><strong>Motivo:</strong> $asunto</p>
                    <hr>
                    <p><strong>Mensaje:</strong></p>
                    <p>" . nl2br($mensaje_usuario) . "</p>
                    <br>
                    <p style='font-size: 0.8em; color: gray;'>Este es un mensaje de prueba para el proyecto Vinos Riverview.</p>
                </div>
            ";

            if ($mail->send()) {
                $mensaje_enviado = true;
            }
        } catch (Exception $e) {
            $error_envio = true;
            $texto_error = 'No se pudo enviar el mensaje. Revisa los datos e inténtalo de nuevo.';
            error_log("Error PHPMailer contacto.php: " . $mail->ErrorInfo);
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<main>
    <section class="hero-contacto text-center mb-5 shadow-sm">
        <div class="container">
            <h1 class="display-3 fw-light mb-3">Estamos aquí para ti</h1>
            <p class="lead mx-auto hero-contacto-texto">
                Ya sea para una recomendación de maridaje, dudas sobre tu pedido o visitas a la bodega, escríbenos.
            </p>
        </div>
    </section>

    <section class="container mb-4 pb-0">
        <div class="row g-5">
            <section class="col-lg-5">
                <div class="pe-lg-4">
                    <h2 class="h2 text-vino mb-4 fw-light">Hablemos</h2>
                    <p class="text-muted mb-5">
                        Detrás de cada botella hay una historia. Cuéntanos la tuya o pregúntanos cualquier duda.
                    </p>

                    <article class="contacto-info-box d-flex align-items-center mb-4">
                        <div class="contacto-icon shadow-sm me-4"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <h3 class="h6 fw-bold text-dark mb-1">Visítanos</h3>
                            <p class="text-muted mb-0">Calle del Vino, 12<br>26001 Logroño, La Rioja</p>
                        </div>
                    </article>

                    <article class="contacto-info-box d-flex align-items-center mb-4">
                        <div class="contacto-icon shadow-sm me-4"><i class="bi bi-telephone"></i></div>
                        <div>
                            <h3 class="h6 fw-bold text-dark mb-1">Llámanos</h3>
                            <p class="text-muted mb-0">+34 912 345 678<br><span class="small">L-V: 9:00 - 18:00</span></p>
                        </div>
                    </article>

                    <article class="contacto-info-box d-flex align-items-center mb-4">
                        <div class="contacto-icon shadow-sm me-4"><i class="bi bi-envelope"></i></div>
                        <div>
                            <h3 class="h6 fw-bold text-dark mb-1">Escríbenos</h3>
                            <p class="text-muted mb-0">vinosriverview@outlook.com</p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="col-lg-7">
                <div class="card card-contacto h-100">
                    <div class="card-body p-4 p-md-5">

                        <?php if ($mensaje_enviado): ?>
                            <div class="alert alert-riverview alert-riverview-success d-flex align-items-center gap-3 mb-4" role="alert">
                                <i class="bi bi-check-circle-fill alert-riverview-icon"></i>
                                <div>
                                    <strong class="d-block mb-1">¡Mensaje enviado con éxito!</strong>
                                    Hemos recibido tu consulta correctamente.
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_envio): ?>
                            <div class="alert alert-riverview alert-riverview-error d-flex align-items-center gap-3 mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill alert-riverview-icon"></i>
                                <div>
                                    <strong class="d-block mb-1">Hubo un problema.</strong>
                                    <?php echo htmlspecialchars($texto_error); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h2 class="h4 text-vino mb-4 fw-bold">Envíanos un mensaje</h2>

                        <form action="contacto.php" method="POST" novalidate class="needs-validation">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="nombre"
                                            name="nombre"
                                            placeholder="Tu nombre"
                                            value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
                                            required
                                        >
                                        <label for="nombre" class="text-muted">Nombre completo</label>
                                        <div class="invalid-feedback">
                                            Por favor, introduce tu nombre.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input
                                            type="email"
                                            class="form-control"
                                            id="email"
                                            name="email"
                                            placeholder="nombre@ejemplo.com"
                                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                            required
                                        >
                                        <label for="email" class="text-muted">Correo electrónico</label>
                                        <div class="invalid-feedback">
                                            Por favor, introduce un correo electrónico válido.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <select class="form-select" id="asunto" name="asunto" aria-label="Asunto">
                                            <option value="Duda sobre un vino" <?php echo (($_POST['asunto'] ?? 'Duda sobre un vino') === 'Duda sobre un vino') ? 'selected' : ''; ?>>Duda sobre un vino / producto</option>
                                            <option value="Problema con mi pedido" <?php echo (($_POST['asunto'] ?? '') === 'Problema con mi pedido') ? 'selected' : ''; ?>>Problema con mi pedido</option>
                                            <option value="Eventos y catas" <?php echo (($_POST['asunto'] ?? '') === 'Eventos y catas') ? 'selected' : ''; ?>>Información sobre eventos y catas</option>
                                            <option value="Otro" <?php echo (($_POST['asunto'] ?? '') === 'Otro') ? 'selected' : ''; ?>>Otro motivo</option>
                                        </select>
                                        <label for="asunto" class="text-muted">¿Sobre qué quieres hablarnos?</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea
                                            class="form-control textarea-contacto"
                                            id="mensaje"
                                            name="mensaje"
                                            placeholder="Escribe aquí tu mensaje"
                                            required
                                        ><?php echo htmlspecialchars($_POST['mensaje'] ?? ''); ?></textarea>
                                        <label for="mensaje" class="text-muted">Escribe aquí tu mensaje...</label>
                                        <div class="invalid-feedback">
                                            Por favor, escribe tu mensaje.
                                        </div>
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
            </section>
        </div>
    </section>

    <section class="container mb-5 mt-2">
        <div class="card card-mapa border-0 overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5">
                        <h2 class="h3 text-vino fw-bold mb-3">Dónde encontrarnos</h2>
                        <p class="text-muted mb-3">
                            Ven a visitarnos y descubre nuestra selección de vinos en persona.
                        </p>
                        <p class="mb-1"><strong>Dirección:</strong> Calle del Vino, 12, 26001 Logroño, La Rioja - 26001</p>
                        <p class="mb-0"><strong>Horario:</strong> Lunes a Viernes, de 9:00 a 18:00</p>
                    </div>

                    <div class="col-lg-7">
                        <div class="mapa-contenedor rounded-4 overflow-hidden shadow-sm">
                            <iframe
                                src="https://maps.google.com/maps?q=Logro%C3%B1o%2C%20La%20Rioja&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.needs-validation');

    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
