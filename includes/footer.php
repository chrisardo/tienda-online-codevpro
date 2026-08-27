<?php
$clienteLogueado = isset($_SESSION["idCliente"]) && $_SESSION["idCliente"] > 0;

?>
<footer class="bg-dark text-white pt-5 pb-3">

    <div class="container">

        <div class="row">

            <!-- Empresa -->

            <div class="col-lg-4 mb-4">

                <h3 class="fw-bold text-primary">

                    <span class="text-primary">

                        CODEVPRO

                    </span>

                    <span class="text-success">

                        TECHNOLOGY

                    </span>

                </h3>

                <p class="text-light">

                    Especialistas en venta de productos tecnológicos,
                    desarrollo de software y soluciones informáticas.

                </p>

                <p>

                    <i class="bi bi-geo-alt-fill text-warning"></i>

                    Monitor Huáscar #811

                </p>

                <p>

                    <i class="bi bi-whatsapp text-success"></i>

                    943 239 039

                </p>

                <p>

                    <i class="bi bi-envelope-fill text-danger"></i>

                    ventas@codevpro.com

                </p>

            </div>

            <!-- Enlaces -->

            <div class="col-lg-2 col-md-6 mb-4">

                <h5 class="fw-bold">

                    Empresa

                </h5>

                <ul class="nav flex-column">

                    <li class="nav-item">

                        <a href="index.php"
                            class="nav-link text-light p-0">

                            Inicio

                        </a>

                    </li>
                    <li class="nav-item">

                        <a href="tienda.php"
                            class="nav-link text-light p-0">

                            Tienda

                        </a>

                    </li>
                    <li class="nav-item">

                        <a href="ofertas.php"
                            class="nav-link text-light p-0">

                            Ofertas

                        </a>

                    </li>
                    <?php if (!$clienteLogueado) { ?>
                        <li class="nav-item">

                            <a href="nosotros.php"
                                class="nav-link text-light p-0">
                                Nosotros
                            </a>

                        </li>
                    <?php } else { ?>
                        <li class="nav-item">

                            <a href="favoritos.php"
                                class="nav-link text-light p-0">
                                favoritos
                            </a>

                        </li>
                    <?php } ?>






                </ul>

            </div>

            <!-- Atención -->

            <div class="col-lg-3 col-md-6 mb-4">

                <h5 class="fw-bold">

                    Atención

                </h5>

                <ul class="nav flex-column">

                    <li class="nav-item">

                        <span class="text-light">

                            Lunes - Viernes

                        </span>

                    </li>

                    <li class="nav-item">

                        <span class="text-secondary">

                            09:00 AM - 08:00 PM

                        </span>

                    </li>

                    <li class="nav-item mt-2">

                        <span class="text-light">

                            Sábado

                        </span>

                    </li>

                    <li class="nav-item">

                        <span class="text-secondary">

                            09:00 AM - 06:00 PM

                        </span>

                    </li>

                </ul>

            </div>

            <!-- Newsletter -->

            <div class="col-lg-3">

                <h5 class="fw-bold">

                    Recibe nuestras ofertas

                </h5>

                <p class="text-secondary">

                    Suscríbete y recibe promociones exclusivas.

                </p>

                <form>

                    <div class="input-group mb-3">

                        <input
                            type="email"
                            class="form-control"
                            placeholder="Correo electrónico">

                        <button
                            class="btn btn-primary"
                            type="submit">

                            Suscribirme

                        </button>

                    </div>

                </form>

                <div class="mt-4">

                    <a href="#" class="btn btn-outline-light me-2">

                        <i class="bi bi-facebook"></i>

                    </a>

                    <a href="#" class="btn btn-outline-light me-2">

                        <i class="bi bi-instagram"></i>

                    </a>

                    <a href="#" class="btn btn-outline-light me-2">

                        <i class="bi bi-tiktok"></i>

                    </a>

                    <a href="#" class="btn btn-outline-light">

                        <i class="bi bi-youtube"></i>

                    </a>

                </div>

            </div>

        </div>

        <hr class="border-secondary">

        <div class="row align-items-center">

            <div class="col-md-6">

                <p class="mb-0">

                    © <?= date('Y'); ?>

                    CoDevPro Technology

                    Todos los derechos reservados.

                </p>

            </div>

            <div class="col-md-6 text-md-end mt-3 mt-md-0">

                <!--<img
                    src="assets/pagos/yape.png"
                    height="35"
                    class="me-2">

                <img
                    src="assets/pagos/plin.png"
                    height="35"
                    class="me-2">

                <img
                    src="assets/pagos/visa.png"
                    height="35"
                    class="me-2">

                <img
                    src="assets/pagos/mastercard.png"
                    height="35">-->

            </div>

        </div>

    </div>

</footer>