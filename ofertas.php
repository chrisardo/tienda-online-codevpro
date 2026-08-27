<?php
//====================================================
// CoDevPro Technology
// ofertas.php - Página principal de ofertas
//====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";
require_once "controladores/obtener_filtros.php";
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
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

    <title>Ofertas | <?= $empresa['nombreEmpresa']; ?></title>

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
include "includes/navbar.php";
?>



<!--=====================================
BREADCRUMB
======================================-->
<section class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light p-3 rounded">

            <li class="breadcrumb-item">
                <a href="index.php">Inicio</a>
            </li>

            <li class="breadcrumb-item active">
                Ofertas
            </li>

        </ol>
    </nav>
</section>

<!--=====================================
FILTROS Y ORDENAMIENTO
======================================-->
<section class="container mb-4">

    <div class="row align-items-center">

        <!-- Contador -->
        <div class="col-md-4 mb-2">
            <!--<h5 id="totalProductos">
                Cargando productos...
            </h5>-->
        </div>
        <div class="text-muted mt-2">

            Orden actual:

            <strong id="textoOrden">

                Más recientes

            </strong>

        </div>

        <!-- Ordenar -->
        <div class="col-md-4 mb-2 text-center">

            <select
                id="ordenarProductos"
                class="form-select">

                <option value="recientes">

                    Más recientes

                </option>

                <option value="vendidos">

                    Más vendidos

                </option>

                <option value="precio_asc">

                    Precio: Menor a Mayor

                </option>

                <option value="precio_desc">

                    Precio: Mayor a Menor

                </option>

                <option value="descuento">

                    Mayor descuento

                </option>

                <option value="destacados">

                    Productos destacados

                </option>

                <option value="nombre_asc">

                    Nombre A - Z

                </option>

                <option value="nombre_desc">

                    Nombre Z - A

                </option>

            </select>

        </div>

        <!-- Vista -->
        <div class="col-md-4 mb-2 text-end">

            <button class="btn btn-outline-primary btn-sm" id="vistaGrid">
                <i class="bi bi-grid"></i>
            </button>

            <button class="btn btn-outline-primary btn-sm" id="vistaLista">
                <i class="bi bi-list"></i>
            </button>

        </div>

    </div>

</section>

<!--=====================================
PRODUCTOS OFERTAS (AJAX)
======================================-->
<!--=====================================
FILTROS + PRODUCTOS
======================================-->

<section class="container mb-5">

    <div class="row">

        <!--=====================================
        PANEL DE FILTROS
        ======================================-->

        <div class="col-lg-3 mb-4">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">

                    <i class="bi bi-funnel"></i>

                    Filtros

                </div>

                <div class="card-body">

                    <!-- Categoría -->

                    <div class="mb-3">

                        <label class="form-label">

                            Categoría

                        </label>

                        <select
                            id="filtroCategoria"
                            class="form-select">

                            <option value="">

                                Todas

                            </option>

                            <?php while ($cat = mysqli_fetch_assoc($categorias)) { ?>

                                <option value="<?= $cat["id_categorias"] ?>">

                                    <?= htmlspecialchars($cat["nombre"]) ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <!-- Marca -->

                    <div class="mb-3">

                        <label class="form-label">

                            Marca

                        </label>

                        <select
                            id="filtroMarca"
                            class="form-select">

                            <option value="">

                                Todas

                            </option>

                            <?php while ($marca = mysqli_fetch_assoc($marcas)) { ?>

                                <option value="<?= $marca["id_marca"] ?>">

                                    <?= htmlspecialchars($marca["nombre"]) ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <!-- Precio -->

                    <div class="row">

                        <div class="col">

                            <input
                                id="precioMin"
                                class="form-control"
                                type="number"
                                placeholder="Desde"
                                value="<?= intval($precio['minimo']) ?>">

                        </div>

                        <div class="col">

                            <input
                                id="precioMax"
                                class="form-control"
                                type="number"
                                placeholder="Hasta"
                                value="<?= intval($precio['maximo']) ?>">

                        </div>

                    </div>

                    <!-- Stock -->

                    <div class="form-check mt-3">

                        <input

                            id="soloStock"

                            class="form-check-input"

                            type="checkbox">

                        <label class="form-check-label">

                            Solo con stock

                        </label>

                    </div>

                    <!-- Envío -->

                    <div class="form-check">

                        <input

                            id="envioGratis"

                            class="form-check-input"

                            type="checkbox">

                        <label class="form-check-label">

                            Envío gratis

                        </label>

                    </div>

                    <button

                        id="btnAplicarFiltros"

                        class="btn btn-primary w-100 mt-4">

                        Aplicar filtros

                    </button>

                    <button

                        id="btnLimpiarFiltros"

                        class="btn btn-outline-secondary w-100 mt-2">

                        Limpiar filtros

                    </button>

                </div>

            </div>

        </div>

        <!--=====================================
        PRODUCTOS
        ======================================-->

        <div class="col-lg-9">

            <div

                class="row"

                id="contenedorOfertas">

            </div>

            <div class="mt-5">

                <nav>

                    <ul

                        id="paginacionOfertas"

                        class="pagination justify-content-center">

                    </ul>

                </nav>

            </div>

        </div>

    </div>

</section>

<!--=====================================
FOOTER
======================================-->
<?php include "includes/footer.php"; ?>
<?php include "includes/modal_producto.php"; ?>
<?php include "includes/carrito_offcanvas.php"; ?>

<!--=====================================
SCRIPTS
======================================-->
<script src="js/ofertas_pagina.js"></script>
<!--<script src="js/tienda.js"></script>-->
<script src="js/carrito.js"></script>
 <script src="js/notificaciones.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<!-- Tienda -->
<!-- AOS -->

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

<script>
    AOS.init({

        duration: 700,

        once: true

    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>