<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_estadisticas_productos.php
// Módulo: Estadísticas de Productos
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
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

<!-- CSS ESPECÍFICO -->
<link rel="stylesheet" href="css/adm_estadisticas_productos.css">


<!--=====================================================
    CONTENEDOR GENERAL
======================================================-->

<div class="d-flex adm-estadisticas-productos">


    <!--=================================================
        SIDEBAR
    ==================================================-->

    <?php include "includes/admin_sidebar.php"; ?>


    <!--=================================================
        CONTENIDO PRINCIPAL
    ==================================================-->

    <div class="flex-grow-1 adm-estadisticas-contenido">


        <!--=================================================
            NAVBAR
        ==================================================-->

        <?php include "includes/admin_navbar.php"; ?>


        <!--=================================================
            CONTENIDO
        ==================================================-->

        <main class="container-fluid px-3 px-md-4 py-4">


            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <section class="estadisticas-header mb-4">

                <div class="row align-items-center g-3">

                    <div class="col-12 col-lg">

                        <div class="d-flex align-items-start gap-3">

                            <div class="estadisticas-header-icon">

                                <i class="bi bi-bar-chart-line-fill"></i>

                            </div>

                            <div>

                                <div class="d-flex align-items-center gap-2 flex-wrap">

                                    <h2 class="estadisticas-titulo mb-0">

                                        Estadísticas de productos

                                    </h2>

                                    <span class="badge rounded-pill text-bg-primary">

                                        <i class="bi bi-graph-up-arrow me-1"></i>
                                        Analítica

                                    </span>

                                </div>

                                <p class="estadisticas-subtitulo mb-0 mt-1">

                                    Analiza ventas, rentabilidad, inventario y rendimiento
                                    de tus productos.

                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- ACCIONES -->

                    <div class="col-12 col-lg-auto">

                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">

                            <button
                                type="button"
                                class="btn btn-light border shadow-sm"
                                id="btnActualizarEstadisticas">

                                <i class="bi bi-arrow-clockwise me-1"></i>

                                Actualizar

                            </button>

                            <button
                                type="button"
                                class="btn btn-primary shadow-sm"
                                id="btnExportarEstadisticas">

                                <i class="bi bi-download me-1"></i>

                                Exportar

                            </button>

                        </div>

                    </div>

                </div>

            </section>


            <!--=================================================
                FILTROS
            ==================================================-->

            <section class="card estadisticas-card filtros-card mb-4">

                <div class="card-body p-3 p-lg-4">


                    <!-- CABECERA -->

                    <div class="filtros-header mb-4">

                        <div class="d-flex align-items-center gap-3">

                            <div class="filtros-icon">

                                <i class="bi bi-funnel-fill"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-0">

                                    Filtros de análisis

                                </h5>

                                <small class="text-muted">

                                    Personaliza el período y los productos que deseas analizar.

                                </small>

                            </div>

                        </div>

                    </div>


                    <!-- FILTROS -->

                    <div class="row g-3">


                        <!-- FECHA INICIO -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="fechaInicioProducto"
                                class="form-label">

                                <i class="bi bi-calendar-event me-1"></i>

                                Fecha inicio

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="fechaInicioProducto"
                                    placeholder="Seleccionar fecha"
                                    autocomplete="off">

                            </div>

                        </div>


                        <!-- FECHA FIN -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="fechaFinProducto"
                                class="form-label">

                                <i class="bi bi-calendar-event me-1"></i>

                                Fecha fin

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="fechaFinProducto"
                                    placeholder="Seleccionar fecha"
                                    autocomplete="off">

                            </div>

                        </div>


                        <!-- CATEGORÍA -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroCategoriaProducto"
                                class="form-label">

                                <i class="bi bi-tags me-1"></i>

                                Categoría

                            </label>

                            <select
                                class="form-select"
                                id="filtroCategoriaProducto">

                                <option value="0">

                                    Todas las categorías

                                </option>

                            </select>

                        </div>


                        <!-- MARCA -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroMarcaProducto"
                                class="form-label">

                                <i class="bi bi-award me-1"></i>

                                Marca

                            </label>

                            <select
                                class="form-select"
                                id="filtroMarcaProducto">

                                <option value="0">

                                    Todas las marcas

                                </option>

                            </select>

                        </div>


                        <!-- SUCURSAL -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroSucursalProducto"
                                class="form-label">

                                <i class="bi bi-shop me-1"></i>

                                Sucursal

                            </label>

                            <select
                                class="form-select"
                                id="filtroSucursalProducto">

                                <option value="0">

                                    Todas las sucursales

                                </option>

                            </select>

                        </div>


                        <!-- TIPO -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroTipoProducto"
                                class="form-label">

                                <i class="bi bi-box-seam me-1"></i>

                                Tipo

                            </label>

                            <select
                                class="form-select"
                                id="filtroTipoProducto">

                                <option value="">

                                    Todos los tipos

                                </option>

                                <option value="Producto">

                                    Producto

                                </option>

                                <option value="Servicio">

                                    Servicio

                                </option>

                            </select>

                        </div>


                        <!-- STOCK -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroStockProducto"
                                class="form-label">

                                <i class="bi bi-boxes me-1"></i>

                                Estado del stock

                            </label>

                            <select
                                class="form-select"
                                id="filtroStockProducto">

                                <option value="">

                                    Todos

                                </option>

                                <option value="disponible">

                                    Con stock

                                </option>

                                <option value="agotado">

                                    Agotados

                                </option>

                                <option value="bajo">

                                    Stock bajo

                                </option>

                            </select>

                        </div>


                        <!-- BUSCAR -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="buscarProductoEstadistica"
                                class="form-label">

                                <i class="bi bi-search me-1"></i>

                                Buscar producto

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-search"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarProductoEstadistica"
                                    placeholder="Nombre o código..."
                                    autocomplete="off">

                            </div>

                        </div>


                    </div>


                    <!-- ACCIONES FILTROS -->

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 pt-3 border-top">

                        <small class="text-muted">

                            <i class="bi bi-info-circle me-1"></i>

                            Los indicadores y gráficos se actualizarán según los filtros.

                        </small>

                        <div class="d-flex gap-2">

                            <button
                                type="button"
                                class="btn btn-light border"
                                id="btnLimpiarFiltrosProducto">

                                <i class="bi bi-x-circle me-1"></i>

                                Limpiar

                            </button>

                            <button
                                type="button"
                                class="btn btn-primary"
                                id="btnAplicarFiltrosProducto">

                                <i class="bi bi-search me-1"></i>

                                Aplicar filtros

                            </button>

                        </div>

                    </div>

                </div>

            </section>


            <!--=================================================
                KPI PRINCIPALES
            ==================================================-->

            <div class="section-title mb-3">

                <div>

                    <h5 class="fw-bold mb-1">

                        Resumen general

                    </h5>

                    <small class="text-muted">

                        Principales indicadores del catálogo y las ventas.

                    </small>

                </div>

            </div>


            <div class="row g-3 g-xl-4 mb-4">


                <!-- PRODUCTOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="kpi-card kpi-primary h-100">

                        <div class="kpi-card-body">

                            <div class="kpi-content">

                                <span class="kpi-label">

                                    Productos

                                </span>

                                <h3
                                    class="kpi-value"
                                    id="kpiProductos">

                                    0

                                </h3>

                                <span class="kpi-description">

                                    Productos registrados

                                </span>

                            </div>

                            <div class="kpi-icon">

                                <i class="bi bi-box-seam"></i>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PRODUCTOS VENDIDOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="kpi-card kpi-success h-100">

                        <div class="kpi-card-body">

                            <div class="kpi-content">

                                <span class="kpi-label">

                                    Productos vendidos

                                </span>

                                <h3
                                    class="kpi-value"
                                    id="kpiProductosVendidos">

                                    0

                                </h3>

                                <span class="kpi-description">

                                    Con al menos una venta

                                </span>

                            </div>

                            <div class="kpi-icon">

                                <i class="bi bi-cart-check"></i>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- UNIDADES -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="kpi-card kpi-warning h-100">

                        <div class="kpi-card-body">

                            <div class="kpi-content">

                                <span class="kpi-label">

                                    Unidades vendidas

                                </span>

                                <h3
                                    class="kpi-value"
                                    id="kpiUnidadesVendidas">

                                    0

                                </h3>

                                <span class="kpi-description">

                                    Cantidad total vendida

                                </span>

                            </div>

                            <div class="kpi-icon">

                                <i class="bi bi-boxes"></i>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- INGRESOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="kpi-card kpi-info h-100">

                        <div class="kpi-card-body">

                            <div class="kpi-content">

                                <span class="kpi-label">

                                    Ingresos

                                </span>

                                <h3
                                    class="kpi-value"
                                    id="kpiIngresos">

                                    S/ 0.00

                                </h3>

                                <span class="kpi-description">

                                    Ventas generadas

                                </span>

                            </div>

                            <div class="kpi-icon">

                                <i class="bi bi-cash-stack"></i>

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!--=================================================
                KPI FINANCIEROS
            ==================================================-->

            <div class="section-title mb-3">

                <div>

                    <h5 class="fw-bold mb-1">

                        Inventario y rentabilidad

                    </h5>

                    <small class="text-muted">

                        Indicadores financieros relacionados con el inventario y las ventas.

                    </small>

                </div>

            </div>


            <div class="row g-3 g-xl-4 mb-4">


                <!-- INVENTARIO -->

                <div class="col-12 col-md-4">

                    <div class="financial-card h-100">

                        <div class="financial-icon bg-primary-subtle text-primary">

                            <i class="bi bi-boxes"></i>

                        </div>

                        <div class="financial-content">

                            <span>

                                Valor del inventario

                            </span>

                            <h4 id="kpiValorInventario">

                                S/ 0.00

                            </h4>

                            <small>

                                Stock × costo de compra

                            </small>

                        </div>

                    </div>

                </div>


                <!-- GANANCIA -->

                <div class="col-12 col-md-4">

                    <div class="financial-card h-100">

                        <div class="financial-icon bg-success-subtle text-success">

                            <i class="bi bi-graph-up-arrow"></i>

                        </div>

                        <div class="financial-content">

                            <span>

                                Ganancia estimada

                            </span>

                            <h4
                                id="kpiGanancia"
                                class="text-success">

                                S/ 0.00

                            </h4>

                            <small>

                                Ingresos − costo de productos vendidos

                            </small>

                        </div>

                    </div>

                </div>


                <!-- SIN VENTAS -->

                <div class="col-12 col-md-4">

                    <div class="financial-card h-100">

                        <div class="financial-icon bg-danger-subtle text-danger">

                            <i class="bi bi-exclamation-triangle"></i>

                        </div>

                        <div class="financial-content">

                            <span>

                                Productos sin ventas

                            </span>

                            <h4
                                id="kpiProductosSinVentas"
                                class="text-danger">

                                0

                            </h4>

                            <small>

                                Productos que aún no registran ventas

                            </small>

                        </div>

                    </div>

                </div>


            </div>


            <!--=================================================
                GRÁFICOS PRINCIPALES
            ==================================================-->

            <div class="section-title mb-3">

                <div>

                    <h5 class="fw-bold mb-1">

                        Análisis visual

                    </h5>

                    <small class="text-muted">

                        Visualiza las tendencias y productos con mejor desempeño.

                    </small>

                </div>

            </div>


            <div class="row g-3 g-xl-4 mb-4">


                <!-- VENTAS POR PERÍODO -->

                <div class="col-12 col-xl-8">

                    <div class="chart-card h-100">

                        <div class="chart-card-header">

                            <div class="d-flex align-items-center gap-3">

                                <div class="chart-icon bg-primary-subtle text-primary">

                                    <i class="bi bi-graph-up"></i>

                                </div>

                                <div>

                                    <h5 class="mb-1">

                                        Ventas de productos

                                    </h5>

                                    <small>

                                        Evolución de los ingresos durante el período seleccionado

                                    </small>

                                </div>

                            </div>

                        </div>

                        <div class="chart-card-body">

                            <div class="chart-container">

                                <canvas id="graficoVentasProductos"></canvas>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- MÁS VENDIDOS -->

                <div class="col-12 col-xl-4">

                    <div class="chart-card h-100">

                        <div class="chart-card-header">

                            <div class="d-flex align-items-center gap-3">

                                <div class="chart-icon bg-success-subtle text-success">

                                    <i class="bi bi-trophy"></i>

                                </div>

                                <div>

                                    <h5 class="mb-1">

                                        Más vendidos

                                    </h5>

                                    <small>

                                        Top 10 productos por unidades

                                    </small>

                                </div>

                            </div>

                        </div>

                        <div class="chart-card-body">

                            <div class="chart-container">

                                <canvas id="graficoProductosVendidos"></canvas>

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!--=================================================
                SEGUNDO GRUPO GRÁFICOS
            ==================================================-->

            <div class="row g-3 g-xl-4 mb-4">


                <!-- INGRESOS -->

                <div class="col-12 col-xl-6">

                    <div class="chart-card h-100">

                        <div class="chart-card-header">

                            <div class="d-flex align-items-center gap-3">

                                <div class="chart-icon bg-info-subtle text-info">

                                    <i class="bi bi-cash-coin"></i>

                                </div>

                                <div>

                                    <h5 class="mb-1">

                                        Productos con mayores ingresos

                                    </h5>

                                    <small>

                                        Ranking según ingresos generados

                                    </small>

                                </div>

                            </div>

                        </div>

                        <div class="chart-card-body">

                            <div class="chart-container">

                                <canvas id="graficoIngresosProductos"></canvas>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- CATEGORÍAS -->

                <div class="col-12 col-xl-6">

                    <div class="chart-card h-100">

                        <div class="chart-card-header">

                            <div class="d-flex align-items-center gap-3">

                                <div class="chart-icon bg-warning-subtle text-warning">

                                    <i class="bi bi-pie-chart"></i>

                                </div>

                                <div>

                                    <h5 class="mb-1">

                                        Ventas por categoría

                                    </h5>

                                    <small>

                                        Distribución de unidades vendidas

                                    </small>

                                </div>

                            </div>

                        </div>

                        <div class="chart-card-body">

                            <div class="chart-container">

                                <canvas id="graficoVentasCategorias"></canvas>

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!--=================================================
                TABLA DE PRODUCTOS
            ==================================================-->

            <section class="products-table-card">


                <!-- HEADER -->

                <div class="products-table-header">

                    <div>

                        <div class="d-flex align-items-center gap-2">

                            <div class="table-title-icon">

                                <i class="bi bi-table"></i>

                            </div>

                            <h5 class="fw-bold mb-0">

                                Detalle de productos

                            </h5>

                        </div>

                        <small class="text-muted ms-5">

                            Rendimiento individual, ventas y rentabilidad.

                        </small>

                    </div>


                    <!-- ORDENAR -->

                    <div class="table-order">

                        <label
                            for="ordenarEstadisticasProducto"
                            class="small text-muted me-2">

                            Ordenar por:

                        </label>

                        <select
                            class="form-select form-select-sm"
                            id="ordenarEstadisticasProducto">

                            <option value="ventas_desc">

                                Más vendidos

                            </option>

                            <option value="ingresos_desc">

                                Mayores ingresos

                            </option>

                            <option value="ganancia_desc">

                                Mayor ganancia

                            </option>

                            <option value="stock_desc">

                                Mayor stock

                            </option>

                            <option value="nombre_asc">

                                Nombre A-Z

                            </option>

                        </select>

                    </div>

                </div>


                <!-- TABLA -->

                <div class="products-table-body">

                    <div class="table-responsive">

                        <table
                            class="table table-hover align-middle mb-0"
                            id="tablaEstadisticasProductos">

                            <thead>

                                <tr>

                                    <th>

                                        Producto

                                    </th>

                                    <th>

                                        Categoría

                                    </th>

                                    <th>

                                        Marca

                                    </th>

                                    <th class="text-center">

                                        Stock

                                    </th>

                                    <th class="text-center">

                                        Vendidos

                                    </th>

                                    <th>

                                        Precio

                                    </th>

                                    <th>

                                        Costo

                                    </th>

                                    <th>

                                        Ingresos

                                    </th>

                                    <th>

                                        Ganancia

                                    </th>

                                    <th>

                                        Margen

                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tbodyEstadisticasProductos">

                                <tr>

                                    <td
                                        colspan="10"
                                        class="text-center py-5">

                                        <div class="spinner-border text-primary"></div>

                                        <div class="mt-3 text-muted">

                                            Cargando estadísticas...

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- PAGINACIÓN -->

                <div class="products-table-footer">

                    <div
                        id="paginacionEstadisticasProductos"
                        class="d-flex justify-content-center">

                    </div>

                </div>


            </section>


        </main>


    </div>

</div>


<!--=====================================================
    MODAL EXPORTAR
======================================================-->

<?php require "modal/modal_exportar_estadisticas_productos.php"; ?>


<!--=====================================================
    BOOTSTRAP
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>


<!--=====================================================
    SHEETJS
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js">
</script>


<!--=====================================================
    SWEET ALERT
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</script>


<!--=====================================================
    FLATPICKR
======================================================-->

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
    src="js/adm_estadisticas_productos.js">
</script>


<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>