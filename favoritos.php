<?php
//=========================================================
// CoDevPro Technology
// favoritos.php
//=========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
require_once "controladores/conexion.php";
/*=========================================
DATOS EMPRESA
=========================================*/

$sqlEmpresa = "SELECT nombreEmpresa, imagen
FROM usuario_acceso
LIMIT 1";

$resEmpresa = mysqli_query($conexion, $sqlEmpresa);

$empresa = mysqli_fetch_assoc($resEmpresa);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis pedidos | <?= $empresa['nombreEmpresa']; ?></title>

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
    <?php
    include 'includes/topbar.php';
    include 'includes/navbar.php';
    ?>

    <section class="py-5 bg-light">

        <div class="container">

            <!--=====================================
        CABECERA
        ======================================-->

            <div class="bg-white rounded-4 shadow-sm p-5 mb-4">

                <div class="row align-items-center">

                    <div class="col-lg-8">

                        <h1 class="fw-bold mb-3">

                            <i class="bi bi-heart-fill text-danger"></i>

                            Mis Favoritos

                        </h1>

                        <p class="text-muted mb-0">

                            Aquí encontrarás todos los productos que has guardado para comprarlos más adelante.

                        </p>

                    </div>

                    <div class="col-lg-4 text-center">

                        <i class="bi bi-heart-fill text-danger"
                            style="font-size:90px;"></i>

                    </div>

                </div>

            </div>

            <!--=====================================
        FILTROS
        ======================================-->

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-lg-6">

                            <input
                                type="text"
                                id="buscarFavorito"
                                class="form-control"
                                placeholder="Buscar producto...">

                        </div>

                        <div class="col-lg-3">

                            <select
                                id="ordenFavorito"
                                class="form-select">

                                <option value="recientes">

                                    Más recientes

                                </option>

                                <option value="precio_asc">

                                    Precio menor

                                </option>

                                <option value="precio_desc">

                                    Precio mayor

                                </option>

                                <option value="nombre_asc">

                                    Nombre A-Z

                                </option>

                                <option value="nombre_desc">

                                    Nombre Z-A

                                </option>

                            </select>

                        </div>

                        <div class="col-lg-3">

                            <button
                                class="btn btn-primary w-100"
                                id="btnBuscarFavoritos">

                                <i class="bi bi-search"></i>

                                Buscar

                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <!--=====================================
        PRODUCTOS FAVORITOS
        ======================================-->

            <div
                class="row"
                id="contenedorFavoritos">

                <div class="col-12 text-center py-5">

                    <div
                        class="spinner-border text-primary"
                        role="status">

                    </div>

                    <p class="mt-3">

                        Cargando favoritos...

                    </p>

                </div>

            </div>

            <!--=====================================
        PAGINACIÓN
        ======================================-->

            <div
                class="d-flex justify-content-center mt-5">

                <nav>

                    <ul
                        class="pagination"
                        id="paginacionFavoritos">

                    </ul>

                </nav>

            </div>

        </div>

    </section>

    <?php
    include 'includes/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <?php include "includes/modal_producto.php"; ?>

    <?php include "includes/carrito_offcanvas.php"; ?>

    <script src="js/carrito.js"></script>

    <script src="js/favoritos.js"></script>

    <script src="js/tienda.js"></script>
    <script src="js/notificaciones.js"></script>
</body>

</html>