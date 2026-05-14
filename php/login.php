<?php
// 1. Iniciar sesión
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_title = 'Iniciar Sesión - Vinos Riverview';
$page_css = 'login.css';

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
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión. Inténtalo más tarde.");
}

$mensaje_error = "";

// 3. Procesar Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $sql = "SELECT id_usuario, nombre, contrasena, rol FROM usuario WHERE email = :email LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($pass, $usuario['contrasena'])) {
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        if ($_SESSION['rol'] === 'administrador') {
            header("Location: ../admin/panel.php");
        } elseif (isset($_GET['volver']) && !empty($_GET['volver'])) {
            header("Location: " . $_GET['volver']);
        } elseif (isset($_POST['return_to']) && !empty($_POST['return_to'])) {
            header("Location: " . $_POST['return_to']);
        } else {
            header("Location: ../index.php");
        }

        exit();
    } else {
        $mensaje_error = "El correo o la contraseña son incorrectos.";
    }
}

// Calcular total carrito para el header
$total_cesta = (isset($_SESSION['carrito'])) ? array_sum($_SESSION['carrito']) : 0;

$return_to = isset($_GET['return_to'])
    ? htmlspecialchars($_GET['return_to'])
    : (isset($_POST['return_to']) ? htmlspecialchars($_POST['return_to']) : '');

$accion_login = "login.php" . (isset($_GET['volver']) ? '?volver=' . urlencode($_GET['volver']) : '');
$link_registro = "./registro.php?return_to=" . urlencode(
    isset($_GET['return_to']) ? $_GET['return_to'] : (isset($_POST['return_to']) ? $_POST['return_to'] : '')
);
?>


<?php require_once '../includes/header.php'; ?>

    <main class="login-main">
        <section class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card card-login p-4 p-md-5 mb-3 bg-white">
                        <header class="mb-4">
                            <h1 class="text-center text-vino fw-light mb-0">Bienvenido</h1>
                        </header>

                        <?php if (!empty($mensaje_error)): ?>
                            <div class="alert alert-danger text-center"><?php echo $mensaje_error; ?></div>
                        <?php endif; ?>

                        <form action="<?php echo $accion_login; ?>" method="POST" novalidate class="needs-validation">
                            <input type="hidden" name="return_to" value="<?php echo $return_to; ?>">

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
                                <button type="submit" class="btn btn-vino text-uppercase btn-login-submit">Entrar</button>
                            </div>

                            <div class="text-center mt-4">
                                <span class="text-muted small">¿No tienes cuenta?</span>
                                <a href="<?php echo $link_registro; ?>" class="fw-bold text-decoration-none ms-1 enlace-registro-login">
                                    Regístrate aquí
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </section>
    </main>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
