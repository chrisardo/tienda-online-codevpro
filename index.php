<!--<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">-->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
//TOda esta parte es de index.php
//include 'includes/head.php'; 
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Index | <?= $empresa['nombreEmpresa']; ?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/hero.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/categorias.css">
    <link rel="stylesheet" href="css/card_producto.css">
    <link rel="stylesheet" href="css/ofertas_flash.css">
    <link rel="stylesheet" href="css/ofertas_productos.css">
    <link rel="stylesheet" href="css/marca.css">
    <link rel="stylesheet" href="css/ofertas.css">
    <link rel="stylesheet" href="css/ofertas_pagina.css">
    <!-- Bootstrap Icons -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- AOS -->

    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- SweetAlert -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="bg-light">
    <?php include 'includes/topbar.php'; ?>

    <?php include 'includes/navbar.php'; ?>


    <!-- Aquí comenzará el Slider -->
    <?php include 'includes/hero.php'; ?>

    <?php include 'includes/categorias.php'; ?>
    <?php include 'includes/card_producto.php'; ?>
    <?php include 'includes/ofertas.php'; ?>
    <?php //include 'includes/ofertas_productos.php'; 
    ?>
    <?php //include 'includes/servicios.php'; 
    ?>
    <?php include 'includes/marcas.php'; ?>
    <?php include 'includes/porque_elegirnos.php'; ?>
    <?php
    // Mostrar testimonios únicamente a visitantes
    if (!isset($_SESSION["idCliente"])) {
        include "includes/testimonios.php";
    }
    ?>

    <?php include 'includes/footer.php'; ?>
    <?php include "includes/modal_producto.php"; ?>
    <?php include "includes/carrito_offcanvas.php"; ?>
    <!--Todo esto pertenece a includes/scripts.php-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS -->

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <script>
        AOS.init({

            duration: 700,

            once: true

        });
    </script>
    <!--<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>-->
    <script src="js/ofertas.js"></script>
    <script src="js/tienda.js"></script>
    <!-- Tienda -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script src="js/notificaciones.js"></script>
    <script src="js/carrito.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>-->
</body>

</html>