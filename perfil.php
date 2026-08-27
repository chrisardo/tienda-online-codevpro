<?php
//======================================================
// CoDevPro Technology
// perfil.php
//======================================================

session_start();

if (!isset($_SESSION["idCliente"])) {

    header("Location: login.php");

    exit;
}
require_once "controladores/conexion.php";

$idCliente = intval($_SESSION["idCliente"]);
$sqlCliente = "

SELECT

c.*,

p.nombre AS pais,
d.nombre AS departamento,
pr.nombre AS provincia,
di.nombre AS distrito

FROM clientes c

LEFT JOIN pais p
ON c.id_pais = p.id_pais

LEFT JOIN departamento d
ON c.id_departamento = d.id_departamento

LEFT JOIN provincia pr
ON c.id_provincia = pr.id_provincia

LEFT JOIN distrito di
ON c.id_distrito = di.id_distrito

WHERE c.idCliente='$idCliente'

LIMIT 1

";

$resultadoCliente = mysqli_query($conexion, $sqlCliente);

$cliente = mysqli_fetch_assoc($resultadoCliente);

if (!$cliente) {

    session_destroy();

    header("Location: login.php");

    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Mi Perfil | CoDevPro Technology</title>
    <link rel="stylesheet" href="css/perfil.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/hero.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- AOS -->

    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- SweetAlert -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

    <!--=====================================
    NAVBAR
    ======================================-->

    <?php include "includes/navbar.php"; ?>

    <!--=====================================
    CONTENIDO
    ======================================-->

    <div class="container py-2">

        <!--=====================================
        CABECERA DEL CLIENTE
        ======================================-->
        <div class="card border-0 shadow-sm mb-2">

            <div class="card-body">

                <div class="row align-items-center">

                    <!-- Foto -->

                    <div class="col-lg-2 text-center">
                        <?php

                        if (!empty($cliente["imagen"])) {

                            $foto = "data:image/jpeg;base64," .
                                base64_encode($cliente["imagen"]);
                        } else {

                            $foto = "assets/img/user.png";
                        }

                        ?>
                        <img

                            id="fotoCabecera"

                            src="<?= $foto ?>"

                            class="rounded-circle border shadow"

                            style="width:130px;
                            height:130px;
                            object-fit:cover;">

                    </div>

                    <!-- Información -->

                    <div class="col-lg-7 mt-4 mt-lg-0">

                        <div class="d-flex align-items-center flex-wrap gap-2">

                            <h2
                                class="fw-bold mb-0"
                                id="nombreCabecera">

                                <?= htmlspecialchars($cliente["nombre"]); ?>
                            </h2>

                            <span class="badge bg-success">

                                <?php

                                if (!empty($cliente["dni_o_ruc"])) {

                                ?>

                                    <span class="badge bg-success">

                                        <i class="bi bi-patch-check-fill"></i>

                                        Cliente verificado

                                    </span>

                                <?php

                                } else {

                                ?>

                                    <span class="badge bg-warning">

                                        <i class="bi bi-exclamation-circle"></i>

                                        Completa tu perfil

                                    </span>

                                <?php

                                }

                                ?>

                            </span>

                        </div>
                        <div class="row g-3">

                            <div class="col-md-6">

                                <div class="small text-muted">

                                    <i class="bi bi-envelope"></i>

                                    Correo electrónico

                                </div>

                                <div
                                    class="fw-semibold"
                                    id="correoCabecera">

                                    <?= htmlspecialchars($cliente["email"]); ?>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="small text-muted">

                                    <i class="bi bi-phone"></i>

                                    Celular

                                </div>

                                <div
                                    class="fw-semibold"
                                    id="celularCabecera">

                                    <?= htmlspecialchars($cliente["celular"]); ?>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="small text-muted">

                                    <i class="bi bi-geo-alt">Dirección</i>
                                    <div
                                        class="fw-semibold"
                                        id="direccionCabecera">
                                        <?=

                                        htmlspecialchars(

                                            $cliente["direccion"] . ", " .
                                                $cliente["distrito"] . ", " .
                                                $cliente["provincia"] . ", " .
                                                $cliente["departamento"] . ", " .
                                                $cliente["pais"]

                                        );

                                        ?>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="small text-muted">

                                    <i class="bi bi-calendar-event"></i>

                                    Cliente desde

                                </div>

                                <div class="fw-semibold">

                                    <?= date("d F Y", strtotime($cliente["fecha_registro"])); ?>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!--=====================================
        LAYOUT PRINCIPAL
        ======================================-->

        <div class="row mt-2 g-2">

            <!--=====================================
            MENÚ LATERAL
            ======================================-->

            <div class="col-lg-3">

                <div class="card border-0 shadow-sm sticky-top" style="top:90px;">

                    <!--=========================================
                    MENÚ
                    ==========================================-->

                    <div class="list-group list-group-flush">

                        <a
                            href="#"
                            class="list-group-item list-group-item-action border-0 active menuPerfil"
                            data-vista="vistaDashboard">

                            <i class="bi bi-speedometer2 me-2"></i>

                            Dashboard

                        </a>

                        <a
                            href="#"
                            class="list-group-item list-group-item-action border-0 menuPerfil"
                            data-vista="vistaPerfil">

                            <i class="bi bi-person-fill me-2"></i>

                            Mi información

                        </a>
                        <a
                            href="#"
                            class="list-group-item list-group-item-action border-0 menuPerfil"
                            data-vista="vistaSeguridad">

                            <i class="bi bi-shield-lock-fill me-2"></i>

                            Seguridad

                        </a>

                        <a
                            href="#"
                            class="list-group-item list-group-item-action border-0 menuPerfil"
                            data-vista="vistaPreferencias">

                            <i class="bi bi-sliders me-2"></i>

                            Preferencias

                        </a>

                    </div>

                    <!--=========================================
                    ACCESOS RÁPIDOS
                    ==========================================-->

                    <div class="card-body border-top">

                        <h6 class="text-uppercase text-muted mb-3">

                            Accesos rápidos

                        </h6>

                        <div class="d-grid gap-2">

                            <a
                                href="mis_pedidos.php"
                                class="btn btn-outline-primary btn-sm">

                                <i class="bi bi-bag-check-fill"></i>

                                Mis pedidos

                            </a>

                            <a
                                href="favoritos.php"
                                class="btn btn-outline-danger btn-sm">

                                <i class="bi bi-heart-fill"></i>

                                Favoritos

                            </a>

                            <a
                                href="tienda.php"
                                class="btn btn-outline-success btn-sm">

                                <i class="bi bi-cart-fill"></i>

                                Seguir comprando

                            </a>

                        </div>

                    </div>

                    <!--=========================================
                    CERRAR SESIÓN
                    ==========================================-->

                    <div class="card-footer bg-white">

                        <div class="d-grid">

                            <a
                                href="logout.php"
                                class="btn btn-danger">

                                <i class="bi bi-box-arrow-right"></i>

                                Cerrar sesión

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================
            CONTENIDO DEL PERFIL
            ======================================-->

            <div class="col-lg-9">

                <div
                    class="card border-0 shadow-sm">

                    <div
                        class="card-body p-3">

                        <!--=====================================
                        DASHBOARD
                        ======================================-->

                        <div
                            id="vistaDashboard"
                            class="perfil-vista activa">

                            <?php include "includes/perfil/dashboard.php"; ?>

                        </div>


                        <!--=====================================
                        INFORMACIÓN PERSONAL
                        ======================================-->

                        <div
                            id="vistaPerfil"
                            class="perfil-vista">

                            <?php include "includes/perfil/informacion_personal.php"; ?>

                        </div>
                        <!--=====================================
                        SEGURIDAD
                        ======================================-->

                        <div
                            id="vistaSeguridad"
                            class="perfil-vista">

                            <?php include "includes/perfil/seguridad.php"; ?>

                        </div>


                        <!--=====================================
                        PREFERENCIAS
                        ======================================-->

                        <div
                            id="vistaPreferencias"
                            class="perfil-vista">

                            <?php include "includes/perfil/preferencias.php"; ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!--=====================================
    MODALES
    ======================================-->

    <?php

    if (file_exists("includes/perfil/modal_editar_perfil.php")) {

        include "includes/perfil/modal_editar_perfil.php";
    }

    if (file_exists("includes/perfil/modal_direccion.php")) {

        include "includes/perfil/modal_direccion.php";
    }

    ?>
    <?php include "includes/modal_producto.php"; ?>
    <?php include "includes/carrito_offcanvas.php"; ?>

    <!--=====================================
    FOOTER
    ======================================-->

    <?php include "includes/footer.php"; ?>

    <!--=====================================
    SCRIPTS
    ======================================-->

    <script src="js/perfil.js"></script>
    <script src="js/notificaciones.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Tienda -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="js/carrito.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>-->

    <!-- AOS -->
    <script src="js/ofertas.js"></script>
    <script src="js/tienda.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    
    <script>
        AOS.init({

            duration: 700,

            once: true

        });
    </script>
    <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>-->
</body>

</html>