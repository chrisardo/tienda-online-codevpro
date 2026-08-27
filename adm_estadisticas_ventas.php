<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_estadisticas_ventas.php
// Módulo: Estadísticas de Ventas
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

        <main class="container-fluid px-4 py-4 estadisticas-ventas-page">


            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <div class="estadisticas-header mb-4">


                <div class="estadisticas-header-info">

                    <div class="estadisticas-header-icon">

                        <i class="bi bi-graph-up-arrow"></i>

                    </div>


                    <div>

                        <h1 class="estadisticas-title">

                            Estadísticas de Ventas

                        </h1>


                        <p class="estadisticas-subtitle">

                            Analiza el comportamiento de tus ventas,
                            ingresos y rendimiento comercial.

                        </p>

                    </div>

                </div>


                <div class="estadisticas-header-actions">


                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-estadisticas"
                        id="btnLimpiarFiltros">

                        <i class="bi bi-arrow-counterclockwise me-2"></i>

                        Limpiar

                    </button>


                    <button
                        type="button"
                        class="btn btn-primary btn-estadisticas"
                        data-bs-toggle="modal"
                        data-bs-target="#modalExportarEstadisticasVentas">

                        <i class="bi bi-file-earmark-excel me-2"></i>

                        Exportar

                    </button>


                </div>


            </div>



            <!--=================================================
                FILTROS
            ==================================================-->

            <section class="card estadisticas-card filtros-card mb-4">


                <div class="card-body">


                    <div class="filtros-header">


                        <div class="filtros-title-wrapper">

                            <div class="filtros-icon">

                                <i class="bi bi-funnel-fill"></i>

                            </div>


                            <div>

                                <h2 class="filtros-title">

                                    Filtros de búsqueda

                                </h2>


                                <p class="filtros-description">

                                    Selecciona los criterios para consultar
                                    las estadísticas de ventas.

                                </p>

                            </div>

                        </div>


                        <span class="filtros-badge">

                            <i class="bi bi-sliders me-1"></i>

                            Personalizar reporte

                        </span>


                    </div>



                    <div class="row g-3 mt-2">


                        <!-- FECHA DESDE -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="fechaDesde"
                                class="form-label filtro-label">

                                <i class="bi bi-calendar3 me-1"></i>

                                Fecha desde

                            </label>


                            <div class="input-group filtro-input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar-event"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="fechaDesde"
                                    name="fechaDesde"
                                    placeholder="dd/mm/aaaa"
                                    autocomplete="off">

                            </div>

                        </div>



                        <!-- FECHA HASTA -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="fechaHasta"
                                class="form-label filtro-label">

                                <i class="bi bi-calendar3 me-1"></i>

                                Fecha hasta

                            </label>


                            <div class="input-group filtro-input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar-event"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="fechaHasta"
                                    name="fechaHasta"
                                    placeholder="dd/mm/aaaa"
                                    autocomplete="off">

                            </div>

                        </div>



                        <!-- SUCURSAL -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="filtroSucursal"
                                class="form-label filtro-label">

                                <i class="bi bi-building me-1"></i>

                                Sucursal

                            </label>


                            <select
                                class="form-select"
                                id="filtroSucursal"
                                name="filtroSucursal">

                                <option value="">

                                    Todas las sucursales

                                </option>

                            </select>

                        </div>



                        <!-- MÉTODO DE PAGO -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="filtroMetodoPago"
                                class="form-label filtro-label">

                                <i class="bi bi-credit-card me-1"></i>

                                Método de pago

                            </label>


                            <select
                                class="form-select"
                                id="filtroMetodoPago"
                                name="filtroMetodoPago">

                                <option value="">

                                    Todos los métodos

                                </option>

                            </select>

                        </div>



                        <!-- ESTADO -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="filtroEstado"
                                class="form-label filtro-label">

                                <i class="bi bi-check-circle me-1"></i>

                                Estado de venta

                            </label>


                            <select
                                class="form-select"
                                id="filtroEstado"
                                name="filtroEstado">

                                <option value="">

                                    Todos los estados

                                </option>

                                <option value="PENDIENTE">

                                    Pendiente

                                </option>

                                <option value="CONFIRMADO">

                                    Confirmado

                                </option>

                                <option value="PREPARANDO">

                                    Preparando

                                </option>

                                <option value="ASIGNADO">

                                    Asignado

                                </option>

                                <option value="OBTENIDO">

                                    Obtenido

                                </option>

                                <option value="ENTREGADO">

                                    Entregado

                                </option>

                                <option value="NO_ENTREGADO">

                                    No entregado

                                </option>

                                <option value="CANCELADO">

                                    Cancelado

                                </option>

                            </select>

                        </div>



                        <!-- EMPLEADO -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="filtroEmpleado"
                                class="form-label filtro-label">

                                <i class="bi bi-person-workspace me-1"></i>

                                Empleado

                            </label>


                            <select
                                class="form-select"
                                id="filtroEmpleado"
                                name="filtroEmpleado">

                                <option value="">

                                    Todos los empleados

                                </option>

                            </select>

                        </div>



                        <!-- CLIENTE -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="filtroCliente"
                                class="form-label filtro-label">

                                <i class="bi bi-person me-1"></i>

                                Cliente

                            </label>


                            <select
                                class="form-select"
                                id="filtroCliente"
                                name="filtroCliente">

                                <option value="">

                                    Todos los clientes

                                </option>

                            </select>

                        </div>



                        <!-- CATEGORÍA -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="filtroCategoria"
                                class="form-label filtro-label">

                                <i class="bi bi-tags me-1"></i>

                                Categoría

                            </label>


                            <select
                                class="form-select"
                                id="filtroCategoria"
                                name="filtroCategoria">

                                <option value="">

                                    Todas las categorías

                                </option>

                            </select>

                        </div>

                    </div>



                    <!-- BOTONES FILTROS -->

                    <div class="filtros-footer">


                        <div class="filtros-periodo-actual">

                            <i class="bi bi-info-circle me-2"></i>

                            <span id="textoPeriodoActual">

                                Selecciona un período para consultar las ventas.

                            </span>

                        </div>


                        <button
                            type="button"
                            class="btn btn-primary btn-aplicar-filtros"
                            id="btnAplicarFiltros">

                            <i class="bi bi-search me-2"></i>

                            Aplicar filtros

                        </button>


                    </div>


                </div>

            </section>



            <!--=================================================
                TARJETAS RESUMEN
            ==================================================-->

            <section class="row g-4 mb-4">


                <!-- VENTAS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card estadistica-resumen-card">

                        <div class="card-body">


                            <div class="estadistica-card-top">


                                <div class="estadistica-icon estadistica-icon-ventas">

                                    <i class="bi bi-receipt-cutoff"></i>

                                </div>


                                <span class="estadistica-comparacion positiva">

                                    <i class="bi bi-arrow-up-short"></i>

                                    <span>--%</span>

                                </span>


                            </div>


                            <div class="estadistica-card-content">

                                <span class="estadistica-label">

                                    Total de ventas

                                </span>


                                <h3
                                    class="estadistica-valor"
                                    id="totalVentas">

                                    0

                                </h3>


                                <span class="estadistica-periodo">

                                    Operaciones realizadas

                                </span>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- INGRESOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card estadistica-resumen-card">

                        <div class="card-body">


                            <div class="estadistica-card-top">


                                <div class="estadistica-icon estadistica-icon-ingresos">

                                    <i class="bi bi-cash-stack"></i>

                                </div>


                                <span class="estadistica-comparacion positiva">

                                    <i class="bi bi-arrow-up-short"></i>

                                    <span>--%</span>

                                </span>


                            </div>


                            <div class="estadistica-card-content">

                                <span class="estadistica-label">

                                    Ingresos totales

                                </span>


                                <h3
                                    class="estadistica-valor"
                                    id="ingresosTotales">

                                    S/ 0.00

                                </h3>


                                <span class="estadistica-periodo">

                                    Ventas del período

                                </span>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- PRODUCTOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card estadistica-resumen-card">

                        <div class="card-body">


                            <div class="estadistica-card-top">


                                <div class="estadistica-icon estadistica-icon-productos">

                                    <i class="bi bi-box-seam"></i>

                                </div>


                                <span class="estadistica-comparacion positiva">

                                    <i class="bi bi-arrow-up-short"></i>

                                    <span>--%</span>

                                </span>


                            </div>


                            <div class="estadistica-card-content">

                                <span class="estadistica-label">

                                    Productos vendidos

                                </span>


                                <h3
                                    class="estadistica-valor"
                                    id="productosVendidos">

                                    0

                                </h3>


                                <span class="estadistica-periodo">

                                    Unidades vendidas

                                </span>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- TICKET PROMEDIO -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card estadistica-resumen-card">

                        <div class="card-body">


                            <div class="estadistica-card-top">


                                <div class="estadistica-icon estadistica-icon-ticket">

                                    <i class="bi bi-wallet2"></i>

                                </div>


                                <span class="estadistica-comparacion positiva">

                                    <i class="bi bi-arrow-up-short"></i>

                                    <span>--%</span>

                                </span>


                            </div>


                            <div class="estadistica-card-content">

                                <span class="estadistica-label">

                                    Ticket promedio

                                </span>


                                <h3
                                    class="estadistica-valor"
                                    id="ticketPromedio">

                                    S/ 0.00

                                </h3>


                                <span class="estadistica-periodo">

                                    Promedio por venta

                                </span>

                            </div>


                        </div>

                    </div>

                </div>



            </section>



            <!--=================================================
                SEGUNDA FILA DE RESUMEN
            ==================================================-->

            <section class="row g-4 mb-4">


                <!-- UTILIDAD -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card estadistica-mini-card">


                        <div class="estadistica-mini-icon">

                            <i class="bi bi-graph-up"></i>

                        </div>


                        <div>

                            <span>

                                Utilidad estimada

                            </span>


                            <strong id="utilidadEstimada">

                                S/ 0.00

                            </strong>

                        </div>


                    </div>

                </div>



                <!-- MARGEN -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card estadistica-mini-card">


                        <div class="estadistica-mini-icon">

                            <i class="bi bi-percent"></i>

                        </div>


                        <div>

                            <span>

                                Margen estimado

                            </span>


                            <strong id="margenEstimado">

                                0.00%

                            </strong>

                        </div>


                    </div>

                </div>



                <!-- CLIENTES -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card estadistica-mini-card">


                        <div class="estadistica-mini-icon">

                            <i class="bi bi-people"></i>

                        </div>


                        <div>

                            <span>

                                Clientes atendidos

                            </span>


                            <strong id="clientesAtendidos">

                                0

                            </strong>

                        </div>


                    </div>

                </div>



                <!-- PRODUCTOS DIFERENTES -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card estadistica-mini-card">


                        <div class="estadistica-mini-icon">

                            <i class="bi bi-grid-3x3-gap"></i>

                        </div>


                        <div>

                            <span>

                                Productos diferentes

                            </span>


                            <strong id="productosDiferentes">

                                0

                            </strong>

                        </div>


                    </div>

                </div>


            </section>



            <!--=================================================
                GRÁFICOS PRINCIPALES
            ==================================================-->

            <section class="row g-4 mb-4">


                <!-- EVOLUCIÓN DE VENTAS -->

                <div class="col-12 col-xl-8">

                    <div class="card estadisticas-card grafico-card">


                        <div class="card-body">


                            <div class="grafico-header">


                                <div>

                                    <h2 class="grafico-title">

                                        Evolución de ventas

                                    </h2>


                                    <p class="grafico-subtitle">

                                        Comportamiento de las ventas durante
                                        el período seleccionado.

                                    </p>

                                </div>


                                <div class="grafico-selector">


                                    <select
                                        class="form-select form-select-sm"
                                        id="periodoGrafico">

                                        <option value="dia">

                                            Por día

                                        </option>

                                        <option value="semana">

                                            Por semana

                                        </option>

                                        <option value="mes">

                                            Por mes

                                        </option>

                                    </select>


                                </div>


                            </div>


                            <div class="grafico-container grafico-evolucion">

                                <canvas id="graficoVentas">

                                </canvas>


                                <div
                                    class="grafico-vacio"
                                    id="estadoGraficoVentas">

                                    <i class="bi bi-bar-chart-line"></i>

                                    <span>

                                        No hay datos disponibles

                                    </span>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- MÉTODOS DE PAGO -->

                <div class="col-12 col-xl-4">

                    <div class="card estadisticas-card grafico-card">


                        <div class="card-body">


                            <div class="grafico-header">

                                <div>

                                    <h2 class="grafico-title">

                                        Métodos de pago

                                    </h2>


                                    <p class="grafico-subtitle">

                                        Distribución de las ventas.

                                    </p>

                                </div>

                            </div>


                            <div class="grafico-container grafico-dona">

                                <canvas id="graficoMetodosPago">

                                </canvas>


                                <div
                                    class="grafico-vacio"
                                    id="estadoGraficoMetodos">

                                    <i class="bi bi-pie-chart"></i>

                                    <span>

                                        No hay datos disponibles

                                    </span>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>


            </section>



            <!--=================================================
                GRÁFICOS SECUNDARIOS
            ==================================================-->

            <section class="row g-4 mb-4">


                <!-- CATEGORÍAS -->

                <div class="col-12 col-xl-6">

                    <div class="card estadisticas-card grafico-card">


                        <div class="card-body">


                            <div class="grafico-header">


                                <div>

                                    <h2 class="grafico-title">

                                        Ventas por categoría

                                    </h2>


                                    <p class="grafico-subtitle">

                                        Categorías con mayor volumen de ventas.

                                    </p>

                                </div>

                            </div>


                            <div class="grafico-container grafico-categorias">

                                <canvas id="graficoCategorias">

                                </canvas>


                                <div
                                    class="grafico-vacio"
                                    id="estadoGraficoCategorias">

                                    <i class="bi bi-tags"></i>

                                    <span>

                                        No hay datos disponibles

                                    </span>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- SUCURSALES -->

                <div class="col-12 col-xl-6">

                    <div class="card estadisticas-card grafico-card">


                        <div class="card-body">


                            <div class="grafico-header">


                                <div>

                                    <h2 class="grafico-title">

                                        Ventas por sucursal

                                    </h2>


                                    <p class="grafico-subtitle">

                                        Comparación de ingresos por sucursal.

                                    </p>

                                </div>

                            </div>


                            <div class="grafico-container grafico-sucursales">

                                <canvas id="graficoSucursales">

                                </canvas>


                                <div
                                    class="grafico-vacio"
                                    id="estadoGraficoSucursales">

                                    <i class="bi bi-building"></i>

                                    <span>

                                        No hay datos disponibles

                                    </span>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>


            </section>



            <!--=================================================
                RANKINGS
            ==================================================-->

            <section class="row g-4 mb-4">


                <!-- PRODUCTOS MÁS VENDIDOS -->

                <div class="col-12 col-xl-6">

                    <div class="card estadisticas-card ranking-card">


                        <div class="card-body">


                            <div class="ranking-header">


                                <div class="ranking-header-info">


                                    <div class="ranking-icon">

                                        <i class="bi bi-trophy"></i>

                                    </div>


                                    <div>

                                        <h2 class="ranking-title">

                                            Productos más vendidos

                                        </h2>


                                        <p class="ranking-subtitle">

                                            Ranking por unidades vendidas.

                                        </p>

                                    </div>

                                </div>


                                <button
                                    type="button"
                                    class="btn btn-sm btn-light ranking-ver-todos">

                                    Ver todos

                                    <i class="bi bi-arrow-right ms-1"></i>

                                </button>

                            </div>



                            <div
                                class="ranking-list"
                                id="rankingProductos">


                                <!-- ITEM -->

                                <div class="ranking-empty">

                                    <i class="bi bi-box-seam"></i>

                                    <span>

                                        No hay productos para mostrar.

                                    </span>

                                </div>


                            </div>


                        </div>

                    </div>

                </div>



                <!-- CLIENTES -->

                <div class="col-12 col-xl-6">

                    <div class="card estadisticas-card ranking-card">


                        <div class="card-body">


                            <div class="ranking-header">


                                <div class="ranking-header-info">


                                    <div class="ranking-icon">

                                        <i class="bi bi-person-check"></i>

                                    </div>


                                    <div>

                                        <h2 class="ranking-title">

                                            Clientes con mayor compra

                                        </h2>


                                        <p class="ranking-subtitle">

                                            Ranking por importe comprado.

                                        </p>

                                    </div>

                                </div>


                                <button
                                    type="button"
                                    class="btn btn-sm btn-light ranking-ver-todos">

                                    Ver todos

                                    <i class="bi bi-arrow-right ms-1"></i>

                                </button>

                            </div>



                            <div
                                class="ranking-list"
                                id="rankingClientes">


                                <div class="ranking-empty">

                                    <i class="bi bi-people"></i>

                                    <span>

                                        No hay clientes para mostrar.

                                    </span>

                                </div>


                            </div>


                        </div>

                    </div>

                </div>


            </section>



            <!--=================================================
                TABLA DE VENTAS
            ==================================================-->

            <section class="card estadisticas-card tabla-ventas-card mb-4">


                <div class="card-body">


                    <div class="tabla-header">


                        <div>

                            <h2 class="tabla-title">

                                Detalle de ventas

                            </h2>


                            <p class="tabla-subtitle">

                                Consulta las operaciones realizadas
                                durante el período seleccionado.

                            </p>

                        </div>


                        <div class="tabla-header-actions">


                            <span
                                class="tabla-registros"
                                id="totalRegistrosTabla">

                                0 registros

                            </span>


                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                id="btnExportarTabla">

                                <i class="bi bi-download me-1"></i>

                                Exportar

                            </button>

                        </div>


                    </div>



                    <div class="table-responsive tabla-scroll">


                        <table
                            class="table tabla-estadisticas align-middle"
                            id="tablaVentas">


                            <thead>

                                <tr>

                                    <th>

                                        Fecha

                                    </th>


                                    <th>

                                        Comprobante

                                    </th>


                                    <th>

                                        Cliente

                                    </th>


                                    <th>

                                        Empleado

                                    </th>


                                    <th>

                                        Método de pago

                                    </th>


                                    <th class="text-center">

                                        Productos

                                    </th>


                                    <th class="text-end">

                                        Total

                                    </th>


                                    <th class="text-center">

                                        Estado

                                    </th>


                                </tr>

                            </thead>


                            <tbody id="tablaVentasBody">


                                <tr class="tabla-empty-row">

                                    <td
                                        colspan="8"
                                        class="text-center">

                                        <div class="tabla-empty">

                                            <div class="tabla-empty-icon">

                                                <i class="bi bi-receipt"></i>

                                            </div>


                                            <strong>

                                                No hay ventas para mostrar

                                            </strong>


                                            <span>

                                                Aplica los filtros para
                                                consultar información.

                                            </span>

                                        </div>

                                    </td>

                                </tr>


                            </tbody>


                        </table>

                    </div>



                    <!--=================================================
                        PAGINACIÓN
                    ==================================================-->

                    <div class="tabla-footer">


                        <div class="tabla-info">

                            Mostrando

                            <strong id="rangoRegistros">

                                0 - 0

                            </strong>

                            de

                            <strong id="totalRegistros">

                                0

                            </strong>

                            registros

                        </div>


                        <nav aria-label="Paginación de ventas">


                            <ul
                                class="pagination pagination-sm mb-0"
                                id="paginacionVentas">


                                <li class="page-item disabled">

                                    <button
                                        class="page-link"
                                        type="button">

                                        <i class="bi bi-chevron-left"></i>

                                    </button>

                                </li>


                                <li class="page-item active">

                                    <button
                                        class="page-link"
                                        type="button">

                                        1

                                    </button>

                                </li>


                                <li class="page-item disabled">

                                    <button
                                        class="page-link"
                                        type="button">

                                        <i class="bi bi-chevron-right"></i>

                                    </button>

                                </li>

                            </ul>


                        </nav>


                    </div>


                </div>

            </section>



        </main>

    </div>

</div>



<!--=====================================================
    MODAL EXPORTAR
======================================================-->

<?php require "modal/modal_exportar_estadisticas_ventas.php"; ?>



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
    src="js/adm_estadisticas_ventas.js">
</script>



<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>