<?php
//Toda esta parte es de tienda.php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

require_once "controladores/conexion.php";
$busquedaInicial = trim($_GET["q"] ?? "");
// Obtener datos de la empresa
$sqlEmpresa = "SELECT nombreEmpresa, imagen
               FROM usuario_acceso
               LIMIT 1";

$resultEmpresa = mysqli_query($conexion, $sqlEmpresa);
$empresa = mysqli_fetch_assoc($resultEmpresa);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tienda | <?= $empresa['nombreEmpresa']; ?></title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- AOS -->

    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- SweetAlert -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="bg-light">

    <!-- ==========================
TOPBAR
========================== -->

    <?php include 'includes/topbar.php'; ?>

    <!-- ==========================
NAVBAR
========================== -->

    <?php include 'includes/navbar.php'; ?>

    <!-- ==========================
BANNER
========================== -->

    <section class="bg-primary text-white py-5">

        <div class="container">

            <div class="row align-items-center">

                <div class="col-lg-8">

                    <h1 class="display-5 fw-bold">

                        Nuestra Tienda

                    </h1>

                    <p class="lead">

                        Encuentra laptops, computadoras, cámaras de seguridad,
                        componentes, accesorios y mucho más.

                    </p>

                </div>

                <div class="col-lg-4 text-center">

                    <i class="bi bi-cart4 display-1"></i>

                </div>

            </div>

        </div>

    </section>

    <!-- ==========================
FILTROS
========================== -->

    <?php include 'includes/filtros.php'; ?>

    <!-- ==========================
CATÁLOGO
========================== -->

    <div class="container py-5">

        <div class="row">

            <!-- Sidebar -->

            <div class="col-lg-3 mb-4">

                <?php include 'includes/sidebar_filtros.php'; ?>

            </div>

            <!-- Productos -->

            <div class="col-lg-9">

                <?php include 'includes/lista_productos.php'; ?>

            </div>

        </div>

    </div>
    <?php include "includes/modal_producto.php"; ?>
    <?php include "includes/carrito_offcanvas.php"; ?>
    <!-- ==========================
FOOTER
========================== -->

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS -->

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <script>
        AOS.init({

            duration: 700,

            once: true

        });
    </script>

    <!-- Tienda -->

    <script src="js/tienda.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/notificaciones.js"></script>
    <script src="js/carrito.js"></script>
</body>

</html>