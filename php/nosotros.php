<?php
// 1. INICIAR SESIÓN (Para que funcione el menú de usuario y carrito)
session_start();
require_once '../config.php';

$base_url = '/vinos-riverview';
$page_title = 'Sobre Nosotros - Vinos Riverview';
$page_css = 'nosotros.css';

// Calcular total de productos para el numerito del carrito
$total_cesta = 0;
if (isset($_SESSION['carrito'])) {
    $total_cesta = array_sum($_SESSION['carrito']);
}
?>


<?php require_once '../includes/header.php'; ?>

    <main>
        <section class="hero-section text-center">
            <div class="container">
                <h1 class="display-4 fw-light titulo-hero-nosotros">Nuestra pasión, tu copa</h1>
                <p class="lead text-muted mx-auto">
                    En Vinos Riverview no solo vendemos vino; compartimos historias, tradición y el sabor auténtico de nuestra tierra.
                </p>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="row align-items-center g-5 mb-5">
                    <div class="col-md-6">
                        <img src="../img/bodega.jpg" alt="Nuestra Bodega" class="img-fluid rounded-3 shadow-sm">
                    </div>
                    <div class="col-md-6">
                        <h2 class="text-vino2 mb-4">De la viña a tu mesa</h2>
                        <p class="text-secondary texto-justificado">
                            Todo comenzó hace más de 30 años en los valles de La Rioja. Lo que empezó como un pequeño viñedo familiar se ha convertido hoy en Vinos Riverview, un punto de encuentro para los amantes del buen comer y el buen beber.
                        </p>
                        <p class="text-secondary texto-justificado">
                            Creemos que un buen vino nunca debe beberse solo. Por eso, hemos recorrido el país buscando los mejores acompañantes: quesos artesanales curados con paciencia y embutidos de la más alta calidad.
                        </p>
                        <p class="text-secondary texto-justificado">
                            Nuestro compromiso es sencillo: <em>Si no lo pondríamos en nuestra mesa, no lo vendemos en nuestra tienda.</em>
                        </p>
                        <div class="contenedor-imagen mt-4">
                            <img src="../img/firma.jpg" class="imagen-firma img-fluid" alt="Firma Fundador">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 mb-5">
            <div class="container">
                <header class="text-center mb-5">
                    <h2 class="h3 text-vino">Conoce al Equipo</h2>
                    <p class="text-muted">Las caras detrás de Riverview</p>
                </header>

                <div class="row g-4 justify-content-center">
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <article class="card border-0 text-center">
                            <img src="../img/fundador.jpg" class="card-img-top rounded-circle mx-auto mt-3 shadow-sm foto-equipo" alt="Fundador">
                            <div class="card-body">
                                <h3 class="card-title h6">Carlos Riverview</h3>
                                <p class="card-text text-muted small">Fundador & Sommelier</p>
                            </div>
                        </article>
                    </div>

                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <article class="card border-0 text-center">
                            <img src="../img/quesera.jpg" class="card-img-top rounded-circle mx-auto mt-3 shadow-sm foto-equipo" alt="Gerente">
                            <div class="card-body">
                                <h3 class="card-title h6">Elena García</h3>
                                <p class="card-text text-muted small">Maestra Quesera</p>
                            </div>
                        </article>
                    </div>

                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <article class="card border-0 text-center">
                            <img src="../img/logistica.jpg" class="card-img-top rounded-circle mx-auto mt-3 shadow-sm foto-equipo" alt="Logística">
                            <div class="card-body">
                                <h3 class="card-title h6">Javier López</h3>
                                <p class="card-text text-muted small">Jefe de Logística</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="seccion-iconos py-5">
            <div class="container text-center">
                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="icon-box rounded-circle shadow-sm">
                            <i class="bi bi-award"></i>
                        </div>
                        <h2 class="h5">Selección Premium</h2>
                        <p class="text-muted small">Cada botella y cada queso ha sido catado y seleccionado personalmente por nuestros expertos.</p>
                    </div>

                    <div class="col-md-4">
                        <div class="icon-box rounded-circle shadow-sm">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h2 class="h5">Envío Seguro</h2>
                        <p class="text-muted small">Embalaje especial anti-roturas y envíos refrigerados para los productos frescos.</p>
                    </div>

                    <div class="col-md-4">
                        <div class="icon-box rounded-circle shadow-sm">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h2 class="h5">Atención Cercana</h2>
                        <p class="text-muted small">¿Dudas con el maridaje? Escríbenos. Te aconsejamos como si fueras un amigo de la familia.</p>
                    </div>

                </div>
            </div>
        </section>
    </main>

    
<?php require_once '../includes/footer.php'; ?>