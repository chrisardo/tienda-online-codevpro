<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_ofertas_descuentos.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

session_start();

//=====================================================
// VALIDAR SESIÓN
//=====================================================

if (!isset($_SESSION["idUser"])) {

    header("Location: login.php");
    exit();
}

$idUser = (int)$_SESSION["idUser"];

//=====================================================
// CONEXIÓN
//=====================================================

require_once "controladores/conexion.php";

//=====================================================
// HEAD
//=====================================================

include "includes/head.php";

?>

<!-- =====================================================
     CONTENEDOR PRINCIPAL
====================================================== -->

<div class="d-flex">

    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <?php include "includes/admin_sidebar.php"; ?>


    <!-- =================================================
         CONTENIDO ADMINISTRADOR
    ================================================== -->

    <div class="flex-grow-1">


        <!-- =================================================
             NAVBAR
        ================================================== -->

        <?php include "includes/admin_navbar.php"; ?>


        <!-- =================================================
             CONTENIDO
        ================================================== -->

        <main class="container-fluid px-4 py-4">


            <!-- =================================================
                 ENCABEZADO
            ================================================== -->

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-1">

                        <i class="bi bi-percent text-primary fs-3"></i>

                        <h2 class="fw-bold mb-0">

                            Ofertas y Descuentos

                        </h2>

                    </div>

                    <p class="text-muted mb-0">

                        Administra las ofertas, descuentos y precios promocionales de tus productos.

                    </p>

                </div>


                <!-- BOTÓN ACTUALIZAR -->

                <div class="mt-3 mt-md-0">

                    <button
                        type="button"
                        class="btn btn-outline-primary rounded-3"
                        id="btnActualizarOfertas">

                        <i class="bi bi-arrow-clockwise me-1"></i>

                        Actualizar

                    </button>

                </div>

            </div>


            <!-- =================================================
                 KPI
            ================================================== -->

            <div class="row g-4 mb-4">


                <!-- =================================================
                     KPI 1
                ================================================== -->

                <div class="col-xl-3 col-md-6">

                    <div class="card card-kpi-ofertas h-100 border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="text-muted mb-2">

                                        Productos en oferta

                                    </p>

                                    <h3
                                        class="fw-bold mb-1"
                                        id="kpiProductosOferta">

                                        0

                                    </h3>

                                    <small class="text-muted">

                                        Ofertas activas

                                    </small>

                                </div>


                                <div class="icon-kpi-oferta bg-primary-subtle text-primary">

                                    <i class="bi bi-percent"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     KPI 2
                ================================================== -->

                <div class="col-xl-3 col-md-6">

                    <div class="card card-kpi-ofertas h-100 border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="text-muted mb-2">

                                        Con descuento

                                    </p>

                                    <h3
                                        class="fw-bold mb-1"
                                        id="kpiProductosDescuento">

                                        0

                                    </h3>

                                    <small class="text-muted">

                                        Productos promocionados

                                    </small>

                                </div>


                                <div class="icon-kpi-oferta bg-success-subtle text-success">

                                    <i class="bi bi-tag-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     KPI 3
                ================================================== -->

                <div class="col-xl-3 col-md-6">

                    <div class="card card-kpi-ofertas h-100 border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="text-muted mb-2">

                                        Sin descuento

                                    </p>

                                    <h3
                                        class="fw-bold mb-1"
                                        id="kpiProductosSinDescuento">

                                        0

                                    </h3>

                                    <small class="text-muted">

                                        Precio normal

                                    </small>

                                </div>


                                <div class="icon-kpi-oferta bg-secondary-subtle text-secondary">

                                    <i class="bi bi-tag"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     KPI 4
                ================================================== -->

                <div class="col-xl-3 col-md-6">

                    <div class="card card-kpi-ofertas h-100 border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="text-muted mb-2">

                                        Descuento promedio

                                    </p>

                                    <h3
                                        class="fw-bold mb-1"
                                        id="kpiDescuentoPromedio">

                                        0%

                                    </h3>

                                    <small class="text-muted">

                                        Entre productos con descuento

                                    </small>

                                </div>


                                <div class="icon-kpi-oferta bg-warning-subtle text-warning">

                                    <i class="bi bi-graph-down-arrow"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 FILTROS
            ================================================== -->

            <div class="card border-0 shadow-sm rounded-4 mb-4">

                <div class="card-body p-4">


                    <!-- CABECERA FILTROS -->

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-funnel text-primary me-1"></i>

                                Filtros

                            </h5>

                            <small class="text-muted">

                                Filtra los productos según sus promociones.

                            </small>

                        </div>


                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary rounded-3"
                            id="btnResetFiltrosOfertas">

                            <i class="bi bi-x-circle me-1"></i>

                            Limpiar filtros

                        </button>

                    </div>


                    <div class="row g-3">


                        <!-- =================================================
                             BUSCAR
                        ================================================== -->

                        <div class="col-xl-4 col-lg-6">

                            <label
                                for="buscarOferta"
                                class="form-label fw-semibold">

                                Buscar producto

                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-white">

                                    <i class="bi bi-search text-muted"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarOferta"
                                    placeholder="Nombre, código o SKU...">

                            </div>

                        </div>


                        <!-- =================================================
                             ESTADO OFERTA
                        ================================================== -->

                        <div class="col-xl-2 col-lg-6">

                            <label
                                for="filtroOferta"
                                class="form-label fw-semibold">

                                Oferta

                            </label>

                            <select
                                class="form-select"
                                id="filtroOferta">

                                <option value="">
                                    Todos
                                </option>

                                <option value="1">
                                    En oferta
                                </option>

                                <option value="0">
                                    Sin oferta
                                </option>

                            </select>

                        </div>


                        <!-- =================================================
                             ESTADO DESCUENTO
                        ================================================== -->

                        <div class="col-xl-2 col-lg-6">

                            <label
                                for="filtroDescuento"
                                class="form-label fw-semibold">

                                Descuento

                            </label>

                            <select
                                class="form-select"
                                id="filtroDescuento">

                                <option value="">
                                    Todos
                                </option>

                                <option value="con">
                                    Con descuento
                                </option>

                                <option value="sin">
                                    Sin descuento
                                </option>

                            </select>

                        </div>


                        <!-- =================================================
                             CATEGORÍA
                        ================================================== -->

                        <div class="col-xl-2 col-lg-6">

                            <label
                                for="filtroCategoriaOferta"
                                class="form-label fw-semibold">

                                Categoría

                            </label>

                            <select
                                class="form-select"
                                id="filtroCategoriaOferta">

                                <option value="">
                                    Todas
                                </option>

                            </select>

                        </div>


                        <!-- =================================================
                             MARCA
                        ================================================== -->

                        <div class="col-xl-2 col-lg-6">

                            <label
                                for="filtroMarcaOferta"
                                class="form-label fw-semibold">

                                Marca

                            </label>

                            <select
                                class="form-select"
                                id="filtroMarcaOferta">

                                <option value="">
                                    Todas
                                </option>

                            </select>

                        </div>


                        <!-- =================================================
                             ORDEN
                        ================================================== -->

                        <div class="col-xl-3 col-lg-6">

                            <label
                                for="ordenOfertas"
                                class="form-label fw-semibold">

                                Ordenar por

                            </label>

                            <select
                                class="form-select"
                                id="ordenOfertas">

                                <option value="recientes">

                                    Más recientes

                                </option>

                                <option value="nombre_asc">

                                    Nombre A-Z

                                </option>

                                <option value="nombre_desc">

                                    Nombre Z-A

                                </option>

                                <option value="descuento_desc">

                                    Mayor descuento

                                </option>

                                <option value="descuento_asc">

                                    Menor descuento

                                </option>

                                <option value="precio_desc">

                                    Mayor precio

                                </option>

                                <option value="precio_asc">

                                    Menor precio

                                </option>

                                <option value="stock_desc">

                                    Mayor stock

                                </option>

                            </select>

                        </div>
                    </div>

                </div>

            </div>


            <!-- =================================================
                 LISTADO
            ================================================== -->

            <div class="card border-0 shadow-sm rounded-4">


                <!-- HEADER LISTADO -->

                <div class="card-header bg-white border-0 px-4 py-3">

                    <div class="row align-items-center">

                        <div class="col-md-6">

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-box-seam text-primary me-1"></i>

                                Productos y promociones

                            </h5>

                            <small
                                class="text-muted"
                                id="infoOfertas">

                                Administrando ofertas y descuentos

                            </small>

                        </div>


                        <div class="col-md-6 text-md-end mt-3 mt-md-0">

                            <span
                                class="badge bg-light text-dark border px-3 py-2"
                                id="totalOfertasEncontradas">

                                0 registros

                            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     TABLA
                ================================================== -->

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="ps-4">
                                    Producto
                                </th>

                                <th>
                                    Categoría
                                </th>

                                <th class="text-center">
                                    Precio normal
                                </th>

                                <th class="text-center">
                                    Precio oferta
                                </th>

                                <th class="text-center">
                                    Descuento
                                </th>

                                <th class="text-center">
                                    Oferta
                                </th>

                                <th class="text-center">
                                    Stock
                                </th>

                                <th class="text-end pe-4">
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody id="tablaOfertasDescuentos">

                            <!-- =================================================
                                 LOADING INICIAL
                            ================================================== -->

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center py-5">

                                    <div class="spinner-border text-primary"></div>

                                    <div class="mt-3 text-muted">

                                        Cargando productos...

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- =================================================
                     FOOTER PAGINACIÓN
                ================================================== -->

                <div class="card-footer bg-white border-0 px-4 py-3">

                    <div class="row align-items-center">


                        <!-- INFORMACIÓN -->

                        <div class="col-md-6">

                            <small
                                class="text-muted"
                                id="infoPaginacionOfertas">

                                Mostrando productos

                            </small>

                        </div>


                        <!-- PAGINACIÓN -->

                        <div class="col-md-6">

                            <nav
                                aria-label="Paginación de ofertas">

                                <ul
                                    class="pagination pagination-sm justify-content-md-end mb-0"
                                    id="paginacionOfertas">

                                </ul>

                            </nav>

                        </div>

                    </div>

                </div>

            </div>


        </main>

    </div>

</div>


<!-- =====================================================
     MODALES
====================================================== -->

<?php include "modal/modal_editar_oferta_descuentos.php"; ?>

<!-- =====================================================
     SCRIPTS
====================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>


<!-- =====================================================
     SCRIPTS DEL SISTEMA
====================================================== -->

<script src="js/dashboard.js"></script>

<script src="js/menu.js"></script>

<script src="js/adm_ofertas_descuentos.js"></script>


</body>

</html>