<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_estadisticas_proveedores.php
// Módulo: Estadísticas de Proveedores
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


if ($idUser <= 0) {

    header("Location: login.php");

    exit;
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "controladores/conexion.php";

?>

<?php include "includes/head.php"; ?>


<!--=====================================================
    CONTENEDOR GENERAL
======================================================-->

<div class="d-flex">


    <!--=================================================
        SIDEBAR
    ==================================================-->

    <?php include "includes/admin_sidebar.php"; ?>


    <!--=================================================
        CONTENIDO PRINCIPAL
    ==================================================-->

    <div class="flex-grow-1">


        <!--=================================================
            NAVBAR
        ==================================================-->

        <?php include "includes/admin_navbar.php"; ?>


        <!--=================================================
            CONTENIDO
        ==================================================-->

        <main class="container-fluid px-4 py-4">


            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <div
                class="d-flex flex-column flex-lg-row
                       justify-content-between
                       align-items-lg-center
                       gap-3 mb-4">


                <!--=================================================
                    TÍTULO
                ==================================================-->

                <div>

                    <div class="d-flex align-items-center gap-2 mb-1">

                        <div
                            class="d-flex align-items-center
                                   justify-content-center
                                   rounded-3
                                   bg-primary-subtle
                                   text-primary"
                            style="
                                width:44px;
                                height:44px;
                            ">

                            <i class="bi bi-bar-chart-fill fs-5"></i>

                        </div>


                        <h2 class="fw-bold mb-0">

                            Estadísticas de Proveedores

                        </h2>

                    </div>


                    <p class="text-muted mb-0">

                        Analiza el rendimiento, productos, ventas,
                        inventario y gastos de tus proveedores.

                    </p>

                </div>


                <!--=================================================
                    ACCIONES
                ==================================================-->

                <div class="d-flex gap-2">


                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="btnActualizarEstadisticasProveedores">

                        <i class="bi bi-arrow-clockwise me-1"></i>

                        Actualizar

                    </button>


                    <button
                        type="button"
                        class="btn btn-primary"
                        id="btnExportarEstadisticasProveedores">

                        <i class="bi bi-download me-1"></i>

                        Exportar

                    </button>


                </div>


            </div>



            <!--=================================================
                FILTROS
            ==================================================-->

            <div
                class="card border-0 shadow-sm mb-4">


                <div class="card-body p-4">


                    <div
                        class="d-flex
                               align-items-center
                               gap-2 mb-3">


                        <div
                            class="d-flex
                                   align-items-center
                                   justify-content-center
                                   rounded-3
                                   bg-light
                                   text-primary"
                            style="
                                width:38px;
                                height:38px;
                            ">

                            <i class="bi bi-funnel-fill"></i>

                        </div>


                        <div>

                            <h5 class="fw-semibold mb-0">

                                Filtros

                            </h5>

                            <small class="text-muted">

                                Personaliza el período y proveedor
                                que deseas analizar.

                            </small>

                        </div>

                    </div>


                    <div class="row g-3">


                        <!-- PROVEEDOR -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroProveedorEstadisticas"
                                class="form-label fw-semibold">

                                Proveedor

                            </label>


                            <select
                                class="form-select"
                                id="filtroProveedorEstadisticas">

                                <option value="">

                                    Todos los proveedores

                                </option>

                            </select>

                        </div>



                        <!-- FECHA INICIO -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroFechaInicioProveedor"
                                class="form-label fw-semibold">

                                Fecha inicio

                            </label>


                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="filtroFechaInicioProveedor"
                                    placeholder="Desde">

                            </div>

                        </div>



                        <!-- FECHA FIN -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroFechaFinProveedor"
                                class="form-label fw-semibold">

                                Fecha fin

                            </label>


                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="filtroFechaFinProveedor"
                                    placeholder="Hasta">

                            </div>

                        </div>



                        <!-- ESTADO -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroEstadoProveedorEstadisticas"
                                class="form-label fw-semibold">

                                Estado

                            </label>


                            <select
                                class="form-select"
                                id="filtroEstadoProveedorEstadisticas">

                                <option value="todos">

                                    Todos

                                </option>

                                <option value="activo">

                                    Activos

                                </option>

                                <option value="inactivo">

                                    Inactivos

                                </option>

                            </select>

                        </div>


                    </div>


                    <!--=================================================
                        BOTONES FILTRO
                    ==================================================-->

                    <div
                        class="d-flex
                               justify-content-end
                               gap-2 mt-3">


                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="btnLimpiarFiltrosEstadisticasProveedores">

                            <i class="bi bi-x-circle me-1"></i>

                            Limpiar

                        </button>

                    </div>


                </div>

            </div>



            <!--=================================================
                KPI
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- TOTAL PROVEEDORES -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div
                        class="card border-0 shadow-sm h-100">

                        <div class="card-body">


                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-start">


                                <div>

                                    <div class="text-muted small mb-1">

                                        Total proveedores

                                    </div>


                                    <div
                                        class="fs-3 fw-bold"
                                        id="kpiTotalProveedores">

                                        --

                                    </div>


                                    <small class="text-muted">

                                        Registrados

                                    </small>

                                </div>


                                <div
                                    class="d-flex
                                           align-items-center
                                           justify-content-center
                                           rounded-3
                                           bg-primary-subtle
                                           text-primary"
                                    style="
                                        width:48px;
                                        height:48px;
                                    ">

                                    <i class="bi bi-people-fill fs-5"></i>

                                </div>


                            </div>


                        </div>

                    </div>

                </div>



                <!-- PROVEEDORES ACTIVOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div
                        class="card border-0 shadow-sm h-100">

                        <div class="card-body">


                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-start">


                                <div>

                                    <div class="text-muted small mb-1">

                                        Proveedores activos

                                    </div>


                                    <div
                                        class="fs-3 fw-bold"
                                        id="kpiProveedoresActivos">

                                        --

                                    </div>


                                    <small class="text-success">

                                        Actualmente activos

                                    </small>

                                </div>


                                <div
                                    class="d-flex
                                           align-items-center
                                           justify-content-center
                                           rounded-3
                                           bg-success-subtle
                                           text-success"
                                    style="
                                        width:48px;
                                        height:48px;
                                    ">

                                    <i class="bi bi-person-check-fill fs-5"></i>

                                </div>


                            </div>


                        </div>

                    </div>

                </div>



                <!-- PRODUCTOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div
                        class="card border-0 shadow-sm h-100">

                        <div class="card-body">


                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-start">


                                <div>

                                    <div class="text-muted small mb-1">

                                        Productos asociados

                                    </div>


                                    <div
                                        class="fs-3 fw-bold"
                                        id="kpiProductosProveedores">

                                        --

                                    </div>


                                    <small class="text-muted">

                                        Productos registrados

                                    </small>

                                </div>


                                <div
                                    class="d-flex
                                           align-items-center
                                           justify-content-center
                                           rounded-3
                                           bg-warning-subtle
                                           text-warning"
                                    style="
                                        width:48px;
                                        height:48px;
                                    ">

                                    <i class="bi bi-box-seam-fill fs-5"></i>

                                </div>


                            </div>


                        </div>

                    </div>

                </div>



                <!-- VALOR INVENTARIO -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div
                        class="card border-0 shadow-sm h-100">

                        <div class="card-body">


                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-start">


                                <div>

                                    <div class="text-muted small mb-1">

                                        Valor del inventario

                                    </div>


                                    <div
                                        class="fs-3 fw-bold"
                                        id="kpiValorInventarioProveedores">

                                        S/ 0.00

                                    </div>


                                    <small class="text-muted">

                                        Costo de productos

                                    </small>

                                </div>


                                <div
                                    class="d-flex
                                           align-items-center
                                           justify-content-center
                                           rounded-3
                                           bg-info-subtle
                                           text-info"
                                    style="
                                        width:48px;
                                        height:48px;
                                    ">

                                    <i class="bi bi-boxes fs-5"></i>

                                </div>


                            </div>


                        </div>

                    </div>

                </div>


            </div>



            <!--=================================================
                SEGUNDA FILA KPI
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- UNIDADES VENDIDAS -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small">

                                Unidades vendidas

                            </div>


                            <div
                                class="fs-4 fw-bold mt-1"
                                id="kpiUnidadesVendidasProveedor">

                                --

                            </div>


                            <small class="text-muted">

                                Productos vendidos

                            </small>

                        </div>

                    </div>

                </div>



                <!-- VENTAS -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small">

                                Ventas generadas

                            </div>


                            <div
                                class="fs-4 fw-bold text-primary mt-1"
                                id="kpiVentasProveedores">

                                S/ 0.00

                            </div>


                            <small class="text-muted">

                                Total vendido

                            </small>

                        </div>

                    </div>

                </div>



                <!-- COSTO -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small">

                                Costo de productos vendidos

                            </div>


                            <div
                                class="fs-4 fw-bold mt-1"
                                id="kpiCostoProductosProveedores">

                                S/ 0.00

                            </div>


                            <small class="text-muted">

                                Costo de compra

                            </small>

                        </div>

                    </div>

                </div>



                <!-- MARGEN -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="text-muted small">

                                Margen generado

                            </div>


                            <div
                                class="fs-4 fw-bold text-success mt-1"
                                id="kpiMargenProveedores">

                                S/ 0.00

                            </div>


                            <small class="text-muted">

                                Venta - costo

                            </small>

                        </div>

                    </div>

                </div>


            </div>



            <!--=================================================
                GRÁFICOS PRINCIPALES
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- VENTAS POR PROVEEDOR -->

                <div class="col-12 col-xl-8">

                    <div
                        class="card border-0 shadow-sm h-100">


                        <div class="card-body p-4">


                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-center mb-4">


                                <div>

                                    <h5 class="fw-semibold mb-1">

                                        Ventas por proveedor

                                    </h5>


                                    <small class="text-muted">

                                        Comparación del valor vendido
                                        por proveedor.

                                    </small>

                                </div>


                                <span
                                    class="badge bg-primary-subtle text-primary">

                                    Ventas

                                </span>


                            </div>


                            <div
                                style="
                                    position:relative;
                                    height:330px;
                                ">

                                <canvas
                                    id="graficoVentasProveedores">

                                </canvas>

                            </div>


                        </div>


                    </div>

                </div>



                <!-- DISTRIBUCIÓN -->

                <div class="col-12 col-xl-4">

                    <div
                        class="card border-0 shadow-sm h-100">


                        <div class="card-body p-4">


                            <div class="mb-4">

                                <h5 class="fw-semibold mb-1">

                                    Distribución de proveedores

                                </h5>


                                <small class="text-muted">

                                    Participación según ventas.

                                </small>

                            </div>


                            <div
                                style="
                                    position:relative;
                                    height:330px;
                                ">

                                <canvas
                                    id="graficoDistribucionProveedores">

                                </canvas>

                            </div>


                        </div>


                    </div>

                </div>


            </div>



            <!--=================================================
                EVOLUCIÓN
            ==================================================-->

            <div class="row g-4 mb-4">


                <div class="col-12">

                    <div
                        class="card border-0 shadow-sm">


                        <div class="card-body p-4">


                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-center mb-4">


                                <div>

                                    <h5 class="fw-semibold mb-1">

                                        Evolución de ventas

                                    </h5>


                                    <small class="text-muted">

                                        Comportamiento de las ventas
                                        durante el período seleccionado.

                                    </small>

                                </div>


                                <span
                                    class="badge bg-light text-dark border">

                                    Por período

                                </span>


                            </div>


                            <div
                                style="
                                    position:relative;
                                    height:340px;
                                ">

                                <canvas
                                    id="graficoEvolucionProveedores">

                                </canvas>

                            </div>


                        </div>


                    </div>

                </div>


            </div>



            <!--=================================================
                RANKINGS
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- TOP PROVEEDORES -->

                <div class="col-12 col-xl-6">

                    <div
                        class="card border-0 shadow-sm h-100">


                        <div class="card-body p-4">


                            <div class="d-flex align-items-center gap-3 mb-4">


                                <div
                                    class="d-flex
                                           align-items-center
                                           justify-content-center
                                           rounded-3
                                           bg-warning-subtle
                                           text-warning"
                                    style="
                                        width:42px;
                                        height:42px;
                                    ">

                                    <i class="bi bi-trophy-fill"></i>

                                </div>


                                <div>

                                    <h5 class="fw-semibold mb-0">

                                        Mejores proveedores

                                    </h5>


                                    <small class="text-muted">

                                        Ranking según ventas generadas.

                                    </small>

                                </div>


                            </div>


                            <div
                                class="table-responsive">

                                <table
                                    class="table align-middle mb-0">


                                    <thead>

                                        <tr>

                                            <th>

                                                #

                                            </th>

                                            <th>

                                                Proveedor

                                            </th>

                                            <th class="text-end">

                                                Productos

                                            </th>

                                            <th class="text-end">

                                                Ventas

                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody
                                        id="tablaRankingProveedores">

                                        <tr>

                                            <td
                                                colspan="4"
                                                class="text-center text-muted py-4">

                                                Cargando estadísticas...

                                            </td>

                                        </tr>

                                    </tbody>


                                </table>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- PRODUCTOS MÁS VENDIDOS -->

                <div class="col-12 col-xl-6">

                    <div
                        class="card border-0 shadow-sm h-100">


                        <div class="card-body p-4">


                            <div class="d-flex align-items-center gap-3 mb-4">


                                <div
                                    class="d-flex
                                           align-items-center
                                           justify-content-center
                                           rounded-3
                                           bg-success-subtle
                                           text-success"
                                    style="
                                        width:42px;
                                        height:42px;
                                    ">

                                    <i class="bi bi-box-seam-fill"></i>

                                </div>


                                <div>

                                    <h5 class="fw-semibold mb-0">

                                        Productos más vendidos

                                    </h5>


                                    <small class="text-muted">

                                        Productos con mayor rotación.

                                    </small>

                                </div>


                            </div>


                            <div
                                class="table-responsive">

                                <table
                                    class="table align-middle mb-0">


                                    <thead>

                                        <tr>

                                            <th>

                                                Producto

                                            </th>

                                            <th>

                                                Proveedor

                                            </th>

                                            <th class="text-end">

                                                Unidades

                                            </th>

                                            <th class="text-end">

                                                Ventas

                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody
                                        id="tablaProductosMasVendidosProveedor">

                                        <tr>

                                            <td
                                                colspan="4"
                                                class="text-center text-muted py-4">

                                                Cargando estadísticas...

                                            </td>

                                        </tr>

                                    </tbody>


                                </table>

                            </div>


                        </div>

                    </div>

                </div>


            </div>



            <!--=================================================
                GASTOS A PROVEEDORES
            ==================================================-->

            <div class="row g-4 mb-4">


                <div class="col-12">

                    <div
                        class="card border-0 shadow-sm">


                        <div class="card-body p-4">


                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-center mb-4">


                                <div
                                    class="d-flex
                                           align-items-center
                                           gap-3">


                                    <div
                                        class="d-flex
                                               align-items-center
                                               justify-content-center
                                               rounded-3
                                               bg-danger-subtle
                                               text-danger"
                                        style="
                                            width:42px;
                                            height:42px;
                                        ">

                                        <i class="bi bi-cash-stack"></i>

                                    </div>


                                    <div>

                                        <h5 class="fw-semibold mb-0">

                                            Gastos relacionados con proveedores

                                        </h5>


                                        <small class="text-muted">

                                            Pagos registrados a proveedores
                                            durante el período seleccionado.

                                        </small>

                                    </div>


                                </div>


                                <div>

                                    <span
                                        class="badge bg-danger-subtle text-danger"
                                        id="totalGastosProveedores">

                                        S/ 0.00

                                    </span>

                                </div>


                            </div>


                            <div
                                class="table-responsive">


                                <table
                                    class="table table-hover align-middle">


                                    <thead>

                                        <tr>

                                            <th>

                                                Fecha

                                            </th>

                                            <th>

                                                Proveedor

                                            </th>

                                            <th>

                                                Concepto

                                            </th>

                                            <th>

                                                Método de pago

                                            </th>

                                            <th>

                                                Tipo

                                            </th>

                                            <th class="text-end">

                                                Monto

                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody
                                        id="tablaGastosProveedores">


                                        <tr>

                                            <td
                                                colspan="6"
                                                class="text-center text-muted py-5">

                                                No hay información disponible.

                                            </td>

                                        </tr>


                                    </tbody>


                                </table>


                            </div>


                        </div>


                    </div>

                </div>


            </div>



            <!--=================================================
                ESTADO DE CARGA GENERAL
            ==================================================-->

            <div
                id="estadoCargaEstadisticasProveedores"
                class="d-none">


                <div
                    class="d-flex
                           align-items-center
                           justify-content-center
                           gap-2
                           text-muted
                           py-4">


                    <div
                        class="spinner-border spinner-border-sm text-primary"
                        role="status">

                        <span class="visually-hidden">

                            Cargando...

                        </span>

                    </div>


                    <span>

                        Actualizando estadísticas...

                    </span>


                </div>


            </div>


        </main>


    </div>

</div>

<?php require "modal/modal_exportar_estadisticas_proveedores.php"; ?>

<!--=====================================================
    SCRIPTS
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>

<!--=====================================================
    SHEETJS / XLSX
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js">
</script>
<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</script>


<script
    src="https://cdn.jsdelivr.net/npm/flatpickr">
</script>


<script
    src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js">
</script>


<!--=====================================================
    CHART.JS
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/chart.js">
</script>


<!--=====================================================
    JS DEL MÓDULO
======================================================-->

<script
    src="js/adm_estadisticas_proveedores.js">
</script>


<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>