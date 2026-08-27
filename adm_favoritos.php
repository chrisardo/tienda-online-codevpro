<?php
//=====================================================
// CoDevPro Technology
// admin_favoritos.php
//=====================================================
//session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";
/*=============================================
=            VALIDAR USUARIO LOGUEADO
=============================================*/

$idUser = $_SESSION["idUser"] ?? 0;


if (!$idUser) {

    echo '<div class="alert alert-danger">
            No se pudo identificar al usuario.
          </div>';

    return;
}
include "includes/head.php";
?>
<div class="d-flex">

    <!--=====================================
    =            SIDEBAR
    ======================================-->

    <?php include "includes/admin_sidebar.php"; ?>


    <!--=====================================
    =            CONTENIDO PRINCIPAL
    ======================================-->

    <div class="flex-grow-1">

        <!-- NAVBAR -->

        <?php include "includes/admin_navbar.php"; ?>


        <!-- CONTENIDO -->
        <div class="container-fluid py-4 px-4">
            <!--=====================================
            =            CABECERA
            ======================================-->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-heart-fill text-danger"></i>
                        Favoritos de Clientes

                    </h2>

                    <p class="text-muted mb-0">

                        Analiza los productos más deseados por tus clientes.

                    </p>

                </div>

            </div>


            <!--=====================================
            =            KPIs
            ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Total Favoritos

                                    </small>

                                    <h3 class="fw-bold mb-0"
                                        id="kpiTotalFavoritos">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-danger">

                                    <i class="bi bi-heart-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Clientes Activos

                                    </small>

                                    <h3 class="fw-bold mb-0"
                                        id="kpiClientes">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-primary">

                                    <i class="bi bi-people-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Productos Favoritos

                                    </small>

                                    <h3 class="fw-bold mb-0"
                                        id="kpiProductos">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-success">

                                    <i class="bi bi-box-seam-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Más Deseado

                                    </small>

                                    <h6 class="fw-bold mb-0"
                                        id="kpiTopProducto">

                                        --

                                    </h6>

                                </div>

                                <div class="icon-circle bg-warning">

                                    <i class="bi bi-star-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
            <div class="row g-4 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Cliente Más Interesado

                            </small>

                            <h5 class="fw-bold mb-1"
                                id="kpiTopCliente">

                                --

                            </h5>

                            <small class="text-success">

                                Más favoritos registrados

                            </small>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Favorito del Mes

                            </small>

                            <h5 class="fw-bold mb-1"
                                id="kpiTopMes">

                                --

                            </h5>

                            <small class="text-primary">

                                Producto más guardado

                            </small>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Conversión a Venta

                            </small>

                            <h3 class="fw-bold text-success mb-0"
                                id="kpiConversion">

                                0%

                            </h3>

                        </div>

                    </div>

                </div>


                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Valor Potencial

                            </small>

                            <h3 class="fw-bold text-warning mb-0"
                                id="kpiValorPotencial">

                                S/ 0.00

                            </h3>

                        </div>

                    </div>

                </div>

            </div>

            <!--=====================================
            =            GRAFICOS
            ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-lg-8">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-bar-chart-fill text-primary"></i>
                                Productos Más Favoritos

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas id="graficoFavoritosProductos"
                                height="120"></canvas>

                        </div>
                        <!--=====================================
            =            MAPA DE CALOR COMERCIAL
            ======================================-->

                        <div class="card border-0 shadow-sm mb-4">

                            <div class="card-header bg-white">

                                <h5 class="mb-0">

                                    <i class="bi bi-fire text-danger"></i>

                                    Mapa de Calor Comercial

                                </h5>

                                <small class="text-muted">

                                    Relación entre favoritos y ventas

                                </small>

                            </div>

                            <div class="card-body p-0">

                                <div class="table-responsive">

                                    <table class="table table-hover align-middle mb-0">

                                        <thead class="table-light">

                                            <tr>

                                                <th>Producto</th>

                                                <th>Favoritos</th>

                                                <th>Ventas</th>

                                                <th>Conversión</th>

                                                <th>Estado Comercial</th>

                                            </tr>

                                        </thead>

                                        <tbody id="tablaMapaCalor">

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>


                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-pie-chart-fill text-success"></i>
                                Favoritos por Categoría

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas id="graficoCategoriasFavoritas">

                            </canvas>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================
            =            FILTROS
            ======================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-lg-3">

                            <label class="form-label">

                                Buscar

                            </label>

                            <input type="text"
                                id="buscarFavorito"
                                class="form-control"
                                placeholder="Cliente o producto...">

                        </div>

                        <div class="col-lg-3">

                            <label class="form-label">

                                Categoría

                            </label>

                            <select id="filtroCategoria"
                                class="form-select">

                                <option value="">

                                    Todas

                                </option>

                            </select>

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Fecha Inicio

                            </label>

                            <input type="text"
                                id="fechaInicio"
                                class="form-control">

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Fecha Fin

                            </label>

                            <input type="text"
                                id="fechaFin"
                                class="form-control">

                        </div>
                        <div class="col-lg-2 d-flex align-items-end">

                            <button
                                class="btn btn-outline-secondary w-100"
                                id="btnLimpiarFiltrosFavoritos">

                                <i class="bi bi-arrow-clockwise"></i>
                                Restablecer

                            </button>

                        </div>
                    </div>

                </div>

            </div>


            <!--=====================================
            =            TABLA
            ======================================-->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="mb-0">

                        <i class="bi bi-table"></i>
                        Lista de Favoritos

                    </h5>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>#</th>

                                    <th>Cliente</th>

                                    <th>Producto</th>

                                    <th>Categoría</th>

                                    <th>Marca</th>

                                    <th>Precio</th>

                                    <th>Stock</th>

                                    <th>Fecha</th>

                                </tr>

                            </thead>

                            <tbody id="tablaFavoritos">

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <!--=====================================
            =            PAGINACION
            ======================================-->

            <div class="mt-4"
                id="paginacionFavoritos">

            </div>
        </div>
    </div>
</div>

<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Idioma Español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- JS -->
<script src="js/adm_favoritos.js"></script>
<script src="js/menu.js"></script>
</body>

</html>