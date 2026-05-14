<?php
// 1. INICIAR SESIÓN
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_title = 'Experiencias - Vinos Riverview';
$page_css = 'experiencias.css';

// Calcular total de productos para la burbuja roja
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}

try {
    // Usamos las constantes que definimos en config.php
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión. Inténtelo más tarde.");
}

// 2. CONSULTAR EXPERIENCIAS
try {
    $query = "SELECT * FROM cata";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $experiencias = [];
}
?>


<?php require_once '../includes/header.php'; ?>

    <main class="container mb-5 experiencias-main">
        <section class="experiencias-hero text-center">
            <header class="row justify-content-center">
                <div class="col-12">
                    <h1 class="fw-light text-vino display-5">Nuestras Experiencias</h1>
                    <hr class="separador-vino mx-auto">
                </div>
            </header>
        </section>

        <section class="row g-4 justify-content-center" aria-label="Listado de experiencias">
            <?php if (empty($experiencias)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Próximamente nuevas experiencias disponibles.</p>
                </div>
            <?php else: ?>
                <?php foreach ($experiencias as $exp): ?>
                    <div class="col-md-6 col-lg-4 d-flex justify-content-center">
                        <article class="card border-1 shadow-sm card-experiencia px-0 w-100">
                            <div class="position-relative">
                                <img
                                    src="../img/<?php echo $exp['imagen']; ?>"
                                    onerror="this.src='../img/cata-fondo.jpg'"
                                    class="card-img-top"
                                    alt="<?php echo htmlspecialchars($exp['nombre_evento']); ?>">

                                <?php if ($exp['id_visita'] == 1): ?>
                                    <span class="badge bg-vino position-absolute top-0 end-0 m-3">Más Popular</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <h2 class="card-title h5 fw-bold text-vino">
                                    <?php echo htmlspecialchars($exp['nombre_evento']); ?>
                                </h2>

                                <p class="text-muted small mb-2">
                                    <i class="bi bi-calendar-event me-1"></i> <?php echo date('d/m/Y', strtotime($exp['fecha'])); ?> |
                                    <i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($exp['hora']); ?>
                                </p>

                                <p class="card-text flex-grow-1">
                                    <?php echo htmlspecialchars($exp['descripcion']); ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center mt-4 gap-3 flex-wrap">
                                    <p class="fs-4 fw-bold text-dark mb-0">
                                        <?php echo number_format($exp['precio'], 2); ?>€
                                        <small class="fs-6 text-muted">/ pers.</small>
                                    </p>

                                    <?php if (!isset($_SESSION['usuario_id'])): ?>
                                        <a href="login.php?volver=experiencias.php" class="btn btn-outline-vino">
                                            Reservar
                                        </a>
                                    <?php else: ?>
                                        <button
                                            type="button"
                                            class="btn btn-outline-vino"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalReserva"
                                            data-id="<?php echo $exp['id_visita']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($exp['nombre_evento']); ?>">
                                            Reservar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-reserva">
                <div class="modal-header border-bottom-vino">
                    <h2 class="modal-title fw-light text-uppercase tracking-wider modal-titulo-reserva h5" id="modalReservaLabel">
                        Confirmar Experiencia
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form action="reservar_proceso.php" method="POST">
                    <div class="modal-body p-4">
                        <p id="nombreCataModal" class="text-vino fw-bold mb-4 modal-nombre-cata"></p>

                        <input type="hidden" name="id_visita" id="idVisitaModal">

                        <div class="mb-4">
                            <label for="num_personas" class="form-label text-muted small text-uppercase fw-bold">Número de asistentes</label>
                            <div class="input-group custom-input-group">
                                <span class="input-group-text bg-light border-end-0 text-vino">
                                    <i class="bi bi-people-fill"></i>
                                </span>
                                <input
                                    type="number"
                                    name="num_personas"
                                    id="num_personas"
                                    class="form-control border-start-0 bg-light focus-vino"
                                    value="1"
                                    min="1"
                                    max="10"
                                    required>
                            </div>
                            <div class="form-text mt-2 small text-muted fst-italic">
                                * Máximo 10 personas por reserva online. Para grupos mayores, contáctenos.
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pb-4 justify-content-center">
                        <button type="button" class="btn btn-link text-muted text-decoration-none px-4" data-bs-dismiss="modal">Volver</button>
                        <button type="submit" class="btn btn-vino px-5 py-2 text-uppercase fw-bold btn-confirmar-reserva">
                            Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg text-center">
                <div class="modal-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success icono-exito-reserva"></i>
                    </div>
                    <h2 class="fw-bold text-vino h3">¡Reserva Confirmada!</h2>
                    <p class="text-muted">
                        Tu plaza para la experiencia ha sido registrada correctamente. Te esperamos para brindar juntos.
                    </p>

                    <div class="d-grid gap-2 mt-4">
                        <a href="perfil.php#experiencias" class="btn btn-vino">Ver mis reservas</a>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Continuar explorando</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalReserva = document.getElementById('modalReserva');
            if (modalReserva) {
                modalReserva.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var id = button.getAttribute('data-id');
                    var nombre = button.getAttribute('data-nombre');

                    var modalInputId = modalReserva.querySelector('#idVisitaModal');
                    var modalTextNombre = modalReserva.querySelector('#nombreCataModal');

                    modalInputId.value = id;
                    modalTextNombre.textContent = nombre;
                });
            }

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('reserva_exitosa')) {
                const modalElement = document.getElementById('modalExito');
                if (modalElement) {
                    const modalExito = new bootstrap.Modal(modalElement);
                    modalExito.show();

                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                }
            }
        });
    </script>

    

<?php require_once '../includes/footer.php'; ?>
