<?php
if (!isset($base_url)) {
    $base_url = '/vinos-riverview';
}
?>

<footer class="footer-riverview pt-5 pb-4">
    <div class="container text-center text-md-start">
        <div class="row text-center text-md-start">

            <section class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h2 class="text-uppercase mb-4 fw-bold text-vino-claro footer-title">Vinos Riverview</h2>
                <p>
                    Tradición, sabor y la mejor selección de nuestra tierra.
                    Llevamos la excelencia de la bodega directamente a tu mesa.
                </p>
            </section>

            <nav class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3" aria-label="Enlaces del sitio">
                <h2 class="text-uppercase mb-4 fw-bold text-vino-claro footer-title">Explorar</h2>
                <p><a href="<?= $base_url ?>/index.php" class="footer-link">Inicio</a></p>
                <p><a href="<?= $base_url ?>/php/tienda.php" class="footer-link">Tienda</a></p>
                <p><a href="<?= $base_url ?>/php/experiencias.php" class="footer-link">Catas y Eventos</a></p>
                <p><a href="<?= $base_url ?>/php/nosotros.php" class="footer-link">Sobre Nosotros</a></p>
            </nav>

            <section class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                <h2 class="text-uppercase mb-4 fw-bold text-vino-claro footer-title">Contacto</h2>
                <p><i class="bi bi-house-door-fill me-2"></i> Calle del Vino, 12, La Rioja</p>
                <p><i class="bi bi-envelope-fill me-2"></i> info@vinosriverview.com</p>
                <p><i class="bi bi-telephone-fill me-2"></i> +34 912 345 678</p>
            </section>

        </div>

        <hr class="mb-4">

        <div class="row align-items-center">
            <div class="col-md-7 col-lg-8">
                <p>© 2025 <strong>Vinos Riverview</strong>. Todos los derechos reservados.</p>
            </div>

            <div class="col-md-5 col-lg-4">
                <div class="text-center text-md-end">
                    <ul class="list-unstyled list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="http://www.facebook.com" class="btn-floating btn-sm" aria-label="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="http://www.x.com" class="btn-floating btn-sm" aria-label="X">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="http://www.instagram.com" class="btn-floating btn-sm" aria-label="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
