<?php
//======================================================
// CoDevPro Technology
// includes/nosotros_hero.php
//======================================================

require_once "./controladores/conexion.php";

/*=========================================
DATOS DE LA EMPRESA
=========================================*/

$sqlEmpresa = "SELECT
                    nombreEmpresa,
                    direccion,
                    celular,
                    email
                FROM usuario_acceso
                LIMIT 1";

$resEmpresa = mysqli_query($conexion, $sqlEmpresa);

$empresa = mysqli_fetch_assoc($resEmpresa);
?>

<section class="py-5 bg-primary text-white overflow-hidden">

    <div class="container">

        <div class="row align-items-center g-5">

            <!--=====================================
            TEXTO
            ======================================-->

            <div
                class="col-lg-6"
                data-aos="fade-right">

                <span class="badge bg-light text-primary px-3 py-2 fs-6">

                    <i class="bi bi-building"></i>

                    Sobre Nosotros

                </span>

                <h1 class="display-4 fw-bold mt-4">

                    <?= htmlspecialchars($empresa["nombreEmpresa"]); ?>

                </h1>

                <p class="lead mt-4">

                    Somos una empresa especializada en tecnología,
                    comprometida con brindar soluciones informáticas,
                    venta de equipos, accesorios, desarrollo de software,
                    redes, cableado estructurado y sistemas de videovigilancia
                    para hogares y empresas.

                </p>

                <p class="mt-4">

                    Nuestro objetivo es ofrecer productos de calidad,
                    atención personalizada y soporte técnico profesional,
                    garantizando siempre la satisfacción de nuestros clientes.

                </p>

                <div class="d-flex flex-wrap gap-3 mt-5">

                    <a
                        href="productos.php"
                        class="btn btn-light btn-lg">

                        <i class="bi bi-bag-fill"></i>

                        Ver productos

                    </a>

                    <a
                        href="contacto.php"
                        class="btn btn-outline-light btn-lg">

                        <i class="bi bi-envelope-fill"></i>

                        Contáctanos

                    </a>

                </div>

            </div>

            <!--=====================================
            IMAGEN
            ======================================-->

            <div
                class="col-lg-6 text-center"
                data-aos="fade-left">

                <img

                    src="assets/img/nosotros.png"

                    class="img-fluid"

                    style="max-height:500px;">

            </div>

        </div>

    </div>

</section>