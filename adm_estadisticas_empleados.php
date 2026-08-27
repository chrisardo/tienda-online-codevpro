<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_estadisticas_empleados.php
// Módulo: Estadísticas de Empleados
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

            <div class="empleados-estadisticas-header mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-1">

                        <span class="empleados-estadisticas-icon">

                            <i class="bi bi-bar-chart-line-fill"></i>

                        </span>

                        <h2 class="fw-bold mb-0">

                            Estadísticas de Empleados

                        </h2>

                    </div>


                    <p class="text-muted mb-0">

                        Analiza el rendimiento, ventas, pagos y distribución
                        de tus empleados.

                    </p>

                </div>


                <!-- ACCIONES -->

                <div class="d-flex gap-2 flex-wrap">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="btnActualizarEstadisticas">

                        <i class="bi bi-arrow-clockwise me-1"></i>

                        Actualizar

                    </button>


                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modalExportarEstadisticasEmpleados">

                        <i class="bi bi-file-earmark-excel me-1"></i>

                        Exportar

                    </button>

                </div>

            </div>


            <!--=================================================
                FILTROS
            ==================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">


                    <div class="d-flex align-items-center mb-3">

                        <div class="estadisticas-section-icon">

                            <i class="bi bi-funnel-fill"></i>

                        </div>

                        <div class="ms-3">

                            <h5 class="fw-bold mb-0">

                                Filtros de análisis

                            </h5>

                            <small class="text-muted">

                                Selecciona los criterios para consultar
                                las estadísticas.

                            </small>

                        </div>

                    </div>


                    <div class="row g-3">


                        <!-- EMPLEADO -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="filtroEmpleadoEstadisticas"
                                class="form-label fw-semibold">

                                Empleado

                            </label>

                            <select
                                class="form-select"
                                id="filtroEmpleadoEstadisticas">

                                <option value="">

                                    Todos los empleados

                                </option>

                            </select>

                        </div>


                        <!-- ROL -->

                        <div class="col-12 col-md-6 col-lg-3">

                            <label
                                for="filtroRolEstadisticas"
                                class="form-label fw-semibold">

                                Cargo / Rol

                            </label>

                            <select
                                class="form-select"
                                id="filtroRolEstadisticas">

                                <option value="">

                                    Todos los roles

                                </option>

                            </select>

                        </div>


                        <!-- ESTADO -->

                        <div class="col-12 col-md-6 col-lg-2">

                            <label
                                for="filtroEstadoEstadisticas"
                                class="form-label fw-semibold">

                                Estado

                            </label>

                            <select
                                class="form-select"
                                id="filtroEstadoEstadisticas">

                                <option value="">

                                    Todos

                                </option>

                                <option value="ACTIVO">

                                    Activos

                                </option>

                                <option value="INACTIVO">

                                    Inactivos

                                </option>

                            </select>

                        </div>


                        <!-- FECHA INICIO -->

                        <div class="col-12 col-md-6 col-lg-2">

                            <label
                                for="fechaInicioEstadisticas"
                                class="form-label fw-semibold">

                                Desde

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="fechaInicioEstadisticas"
                                    placeholder="Fecha inicial"
                                    autocomplete="off">

                            </div>

                        </div>


                        <!-- FECHA FIN -->

                        <div class="col-12 col-md-6 col-lg-2">

                            <label
                                for="fechaFinEstadisticas"
                                class="form-label fw-semibold">

                                Hasta

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="fechaFinEstadisticas"
                                    placeholder="Fecha final"
                                    autocomplete="off">

                            </div>

                        </div>


                    </div>


                    <!-- ACCIONES FILTROS -->

                    <div class="d-flex justify-content-end gap-2 mt-4">

                        <button
                            type="button"
                            class="btn btn-light border"
                            id="btnLimpiarFiltrosEstadisticas">

                            <i class="bi bi-x-circle me-1"></i>

                            Limpiar

                        </button>


                        <button
                            type="button"
                            class="btn btn-primary"
                            id="btnAplicarFiltrosEstadisticas">

                            <i class="bi bi-search me-1"></i>

                            Aplicar filtros

                        </button>

                    </div>

                </div>

            </div>


            <!--=================================================
                KPI
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- TOTAL EMPLEADOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card estadistica-kpi-card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <p class="text-muted mb-1">

                                        Total empleados

                                    </p>

                                    <h3
                                        class="fw-bold mb-1"
                                        id="kpiTotalEmpleados">

                                        0

                                    </h3>

                                    <small class="text-muted">

                                        Registrados en el sistema

                                    </small>

                                </div>


                                <div class="kpi-icon kpi-icon-primary">

                                    <i class="bi bi-people-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ACTIVOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card estadistica-kpi-card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <p class="text-muted mb-1">

                                        Empleados activos

                                    </p>

                                    <h3
                                        class="fw-bold mb-1"
                                        id="kpiEmpleadosActivos">

                                        0

                                    </h3>

                                    <small class="text-success">

                                        <i class="bi bi-check-circle-fill me-1"></i>

                                        Personal activo

                                    </small>

                                </div>


                                <div class="kpi-icon kpi-icon-success">

                                    <i class="bi bi-person-check-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- VENTAS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card estadistica-kpi-card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <p class="text-muted mb-1">

                                        Ventas realizadas

                                    </p>

                                    <h3
                                        class="fw-bold mb-1"
                                        id="kpiVentasEmpleados">

                                        0

                                    </h3>

                                    <small class="text-muted">

                                        Ventas asociadas a empleados

                                    </small>

                                </div>


                                <div class="kpi-icon kpi-icon-warning">

                                    <i class="bi bi-cart-check-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- MONTO VENTAS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card estadistica-kpi-card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <p class="text-muted mb-1">

                                        Monto vendido

                                    </p>

                                    <h3
                                        class="fw-bold mb-1"
                                        id="kpiMontoVentas">

                                        S/ 0.00

                                    </h3>

                                    <small class="text-muted">

                                        Total de ventas

                                    </small>

                                </div>


                                <div class="kpi-icon kpi-icon-info">

                                    <i class="bi bi-currency-dollar"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                SEGUNDO BLOQUE KPI
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- TICKET PROMEDIO -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card estadistica-mini-card border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex align-items-center">

                                <div class="mini-icon">

                                    <i class="bi bi-receipt"></i>

                                </div>

                                <div class="ms-3">

                                    <small class="text-muted">

                                        Ticket promedio

                                    </small>

                                    <h5
                                        class="fw-bold mb-0"
                                        id="kpiTicketPromedio">

                                        S/ 0.00

                                    </h5>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PRODUCTOS VENDIDOS -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card estadistica-mini-card border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex align-items-center">

                                <div class="mini-icon">

                                    <i class="bi bi-box-seam"></i>

                                </div>

                                <div class="ms-3">

                                    <small class="text-muted">

                                        Productos vendidos

                                    </small>

                                    <h5
                                        class="fw-bold mb-0"
                                        id="kpiProductosVendidos">

                                        0

                                    </h5>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PAGOS -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card estadistica-mini-card border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex align-items-center">

                                <div class="mini-icon">

                                    <i class="bi bi-cash-stack"></i>

                                </div>

                                <div class="ms-3">

                                    <small class="text-muted">

                                        Pagos realizados

                                    </small>

                                    <h5
                                        class="fw-bold mb-0"
                                        id="kpiPagosRealizados">

                                        S/ 0.00

                                    </h5>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- SUELDO -->

                <div class="col-12 col-md-6 col-xl-3">

                    <div class="card estadistica-mini-card border-0 shadow-sm">

                        <div class="card-body">

                            <div class="d-flex align-items-center">

                                <div class="mini-icon">

                                    <i class="bi bi-wallet-fill"></i>

                                </div>

                                <div class="ms-3">

                                    <small class="text-muted">

                                        Nómina activa

                                    </small>

                                    <h5
                                        class="fw-bold mb-0"
                                        id="kpiNominaActiva">

                                        S/ 0.00

                                    </h5>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!--=================================================
                GRÁFICOS PRINCIPALES
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- VENTAS POR EMPLEADO -->

                <div class="col-12 col-xl-8">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body p-4">


                            <div class="d-flex justify-content-between align-items-start mb-4">

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        Rendimiento por empleado

                                    </h5>

                                    <p class="text-muted small mb-0">

                                        Comparación del monto de ventas
                                        realizadas por empleado.

                                    </p>

                                </div>


                                <div class="dropdown">

                                    <button
                                        class="btn btn-sm btn-light border dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown">

                                        Ventas

                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">

                                        <li>

                                            <button
                                                class="dropdown-item active"
                                                type="button">

                                                Monto vendido

                                            </button>

                                        </li>

                                        <li>

                                            <button
                                                class="dropdown-item"
                                                type="button">

                                                Número de ventas

                                            </button>

                                        </li>

                                        <li>

                                            <button
                                                class="dropdown-item"
                                                type="button">

                                                Productos vendidos

                                            </button>

                                        </li>

                                    </ul>

                                </div>

                            </div>


                            <div class="estadistica-chart-container">

                                <canvas
                                    id="graficoRendimientoEmpleados">
                                </canvas>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- ESTADO EMPLEADOS -->

                <div class="col-12 col-xl-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-1">

                                Estado de empleados

                            </h5>

                            <p class="text-muted small mb-4">

                                Distribución actual del personal.

                            </p>


                            <div class="estadistica-doughnut-container">

                                <canvas
                                    id="graficoEstadoEmpleados">
                                </canvas>

                            </div>


                            <div
                                class="d-flex justify-content-center gap-4 mt-3">

                                <div class="text-center">

                                    <span
                                        class="estadistica-leyenda-dot bg-success">
                                    </span>

                                    <small class="text-muted">

                                        Activos

                                    </small>

                                    <div
                                        class="fw-bold"
                                        id="estadoActivos">

                                        0

                                    </div>

                                </div>


                                <div class="text-center">

                                    <span
                                        class="estadistica-leyenda-dot bg-danger">
                                    </span>

                                    <small class="text-muted">

                                        Inactivos

                                    </small>

                                    <div
                                        class="fw-bold"
                                        id="estadoInactivos">

                                        0

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                EVOLUCIÓN DE VENTAS
            ==================================================-->

            <div class="row g-4 mb-4">


                <div class="col-12">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body p-4">


                            <div class="d-flex justify-content-between align-items-start mb-4">

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        Evolución de ventas

                                    </h5>

                                    <p class="text-muted small mb-0">

                                        Evolución de las ventas realizadas
                                        por los empleados durante el período.

                                    </p>

                                </div>


                                <span class="badge bg-light text-dark border">

                                    <i class="bi bi-graph-up me-1"></i>

                                    Ventas

                                </span>

                            </div>


                            <div class="estadistica-chart-line-container">

                                <canvas
                                    id="graficoEvolucionVentas">
                                </canvas>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                DISTRIBUCIÓN POR ROLES + PAGOS
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- ROLES -->

                <div class="col-12 col-lg-5">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-1">

                                Empleados por rol

                            </h5>

                            <p class="text-muted small mb-4">

                                Distribución del personal según cargo.

                            </p>


                            <div class="estadistica-chart-role-container">

                                <canvas
                                    id="graficoEmpleadosRol">
                                </canvas>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PAGOS -->

                <div class="col-12 col-lg-7">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body p-4">


                            <div class="d-flex justify-content-between align-items-start mb-4">

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        Pagos a empleados

                                    </h5>

                                    <p class="text-muted small mb-0">

                                        Resumen de pagos registrados
                                        durante el período.

                                    </p>

                                </div>


                                <span class="badge bg-light text-dark border">

                                    <i class="bi bi-wallet2 me-1"></i>

                                    Nómina

                                </span>

                            </div>


                            <div class="estadistica-chart-container">

                                <canvas
                                    id="graficoPagosEmpleados">
                                </canvas>

                            </div>


                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                RANKING DE EMPLEADOS
            ==================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">


                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>

                            <h5 class="fw-bold mb-1">

                                Ranking de empleados

                            </h5>

                            <p class="text-muted small mb-0">

                                Desempeño comercial según ventas registradas.

                            </p>

                        </div>


                        <span class="badge bg-primary-subtle text-primary">

                            <i class="bi bi-trophy-fill me-1"></i>

                            Top empleados

                        </span>

                    </div>


                    <div class="table-responsive">

                        <table
                            class="table align-middle mb-0"
                            id="tablaRankingEmpleados">

                            <thead class="table-light">

                                <tr>

                                    <th width="70">

                                        #

                                    </th>

                                    <th>

                                        Empleado

                                    </th>

                                    <th>

                                        Rol

                                    </th>

                                    <th class="text-center">

                                        Ventas

                                    </th>

                                    <th class="text-center">

                                        Productos

                                    </th>

                                    <th class="text-end">

                                        Monto vendido

                                    </th>

                                    <th class="text-center">

                                        Participación

                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <tr>

                                    <td colspan="7">

                                        <div class="estadistica-tabla-vacia">

                                            <i class="bi bi-bar-chart-line"></i>

                                            <span>

                                                No hay datos disponibles.

                                            </span>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                </div>

            </div>


            <!--=================================================
                RESUMEN ECONÓMICO
            ==================================================-->

            <div class="row g-4">


                <!-- PAGOS PENDIENTES -->

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm estadistica-resumen-card">

                        <div class="card-body">

                            <div class="d-flex align-items-center">

                                <div class="resumen-icon resumen-warning">

                                    <i class="bi bi-hourglass-split"></i>

                                </div>

                                <div class="ms-3">

                                    <small class="text-muted">

                                        Pagos pendientes

                                    </small>

                                    <h4
                                        class="fw-bold mb-0"
                                        id="resumenPagosPendientes">

                                        S/ 0.00

                                    </h4>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- BONIFICACIONES -->

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm estadistica-resumen-card">

                        <div class="card-body">

                            <div class="d-flex align-items-center">

                                <div class="resumen-icon resumen-success">

                                    <i class="bi bi-plus-circle-fill"></i>

                                </div>

                                <div class="ms-3">

                                    <small class="text-muted">

                                        Bonificaciones

                                    </small>

                                    <h4
                                        class="fw-bold mb-0"
                                        id="resumenBonificaciones">

                                        S/ 0.00

                                    </h4>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- DESCUENTOS -->

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm estadistica-resumen-card">

                        <div class="card-body">

                            <div class="d-flex align-items-center">

                                <div class="resumen-icon resumen-danger">

                                    <i class="bi bi-dash-circle-fill"></i>

                                </div>

                                <div class="ms-3">

                                    <small class="text-muted">

                                        Descuentos

                                    </small>

                                    <h4
                                        class="fw-bold mb-0"
                                        id="resumenDescuentos">

                                        S/ 0.00

                                    </h4>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


            </div>


        </main>


    </div>

</div>


<!--=====================================================
    MODAL EXPORTAR
======================================================-->

<?php require "modal/modal_exportar_estadisticas_empleados.php"; ?>


<!--=====================================================
    SCRIPTS
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
    src="js/adm_estadisticas_empleados.js">
</script>


<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>