<?php
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_title = 'Registro - Vinos Riverview';
$page_css = 'registro.css';

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    echo "Error de conexión. Inténtalo más tarde.";
    exit;
}

// Variables para guardar mensajes de feedback al usuario
$mensaje_error = "";
$mensaje_exito = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_SPECIAL_CHARS);
    $apellidos = filter_var(trim($_POST['apellidos']), FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $telefono = filter_var(trim($_POST['telefono']), FILTER_SANITIZE_SPECIAL_CHARS);
    $direccion = filter_var(trim($_POST['direccion']), FILTER_SANITIZE_SPECIAL_CHARS);

    $pass_usuario = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if (empty($nombre) || empty($apellidos) || empty($email) || empty($direccion) || empty($pass_usuario) || empty($confirm_pass)) {
        $mensaje_error = "Por favor, rellena todos los campos obligatorios (*).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "El formato del correo electrónico no es válido.";
    } elseif ($pass_usuario != $confirm_pass) {
        $mensaje_error = "Las contraseñas no coinciden.";
    } else {
        $sql_check = "SELECT id_usuario FROM usuario WHERE email = :email";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            $mensaje_error = "Este correo electrónico ya está registrado.";
        } else {
            $pass_cifrada = password_hash($pass_usuario, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO usuario (nombre, apellidos, email, telefono, direccion, contrasena, rol)
                           VALUES (:nom, :ape, :email, :tel, :dir, :pass, 'cliente')";

            try {
                $stmt = $conexion->prepare($sql_insert);
                $stmt->bindParam(':nom', $nombre);
                $stmt->bindParam(':ape', $apellidos);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':tel', $telefono);
                $stmt->bindParam(':dir', $direccion);
                $stmt->bindParam(':pass', $pass_cifrada);

                if ($stmt->execute()) {
                    $mensaje_exito = "¡Cuenta creada con éxito! Redirigiendo...";

                    $url_retorno = !empty($_POST['return_to']) ? "?return_to=" . urlencode($_POST['return_to']) : "";
                    header("refresh:2;url=login.php" . $url_retorno);
                }
            } catch (PDOException $e) {
                $mensaje_error = "Error al registrar: " . $e->getMessage();
            }
        }
    }
}

$return_to = isset($_GET['return_to'])
    ? htmlspecialchars($_GET['return_to'])
    : (isset($_POST['return_to']) ? htmlspecialchars($_POST['return_to']) : '');
?>


<?php require_once '../includes/header.php'; ?>

    <main class="registro-main">
        <div class="bg-image" aria-hidden="true"></div>

        <section class="container d-flex justify-content-center align-items-center min-vh-100 position-relative">
            <div class="card card-registro shadow-lg p-4">
                <div class="card-body">

                    <header class="text-center mb-4">
                        <h1 class="fw-light text-uppercase titulo-registro">Crear Cuenta</h1>
                        <p class="text-muted small">Los campos con <span class="text-danger">*</span> son obligatorios</p>
                    </header>

                    <?php if (!empty($mensaje_error)): ?>
                        <div class="alert alert-danger text-center py-2"><?php echo $mensaje_error; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($mensaje_exito)): ?>
                        <div class="alert alert-success text-center py-2"><?php echo $mensaje_exito; ?></div>
                    <?php endif; ?>

                    <form action="registro.php" method="POST" novalidate class="needs-validation">
                        <input type="hidden" name="return_to" value="<?php echo $return_to; ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" class="form-control" placeholder="Tus apellidos" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Correo Electrónico <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="nombre@ejemplo.com"
                                    required
                                    id="emailInput">
                                <div class="invalid-feedback">
                                    Por favor, escribe un correo válido.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Teléfono</label>
                            <div class="input-group">
                                <input
                                    type="tel"
                                    name="telefono"
                                    class="form-control"
                                    placeholder="+34 600 000 000"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Lo usaremos solo para coordinar la entrega de tus pedidos.">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Dirección de Envío <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <input
                                    type="text"
                                    name="direccion"
                                    class="form-control"
                                    placeholder="Calle, número, piso, ciudad..."
                                    required>
                                <div class="invalid-feedback">
                                    Por favor, introduce una dirección para tus pedidos.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted">Contraseña <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="******"
                                        required
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
                            <button type="submit" class="btn btn-vino text-uppercase btn-registro-submit">
                                Registrarme
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="small text-muted mb-0">¿Ya tienes cuenta?</p>
                        <a href="login.php" class="text-decoration-none fw-bold enlace-login-registro">
                            Inicia Sesión aquí
                        </a>
                    </div>

                </div>
            </div>
        </section>
    </main>



    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            var forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
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
