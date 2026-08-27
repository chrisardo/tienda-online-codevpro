<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_contabilidad.php
// Módulo: Contabilidad
// Sistema: Inventa
//=====================================================

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "controladores/conexion.php";

//=====================================================
// VALIDAR USUARIO LOGUEADO
//=====================================================

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo '
    <div class="container mt-4">
        <div class="alert alert-danger shadow-sm border-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            No se pudo identificar al usuario.
        </div>
    </div>';

    return;
}

//=====================================================
// HEAD
//=====================================================

include "includes/head.php";

?>

<!--=====================================================
    CONTENEDOR PRINCIPAL
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

        <main class="container-fluid py-4 px-4 adm-contabilidad-page">


            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-calculator-fill text-primary me-2"></i>

                        Resumen Contable

                    </h2>

                    <p class="text-muted mb-0">

                        Consulta el estado financiero y contable de tu empresa.

                    </p>

                </div>


                <!-- ACCIONES -->

                <div class="d-flex gap-2 mt-3 mt-md-0">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="btnActualizarContabilidad">

                        <i class="bi bi-arrow-clockwise me-1"></i>

                        Actualizar

                    </button>


                    <button
                        type="button"
                        class="btn btn-primary"
                        id="btnExportarContabilidad">

                        <i class="bi bi-file-earmark-excel me-1"></i>

                        Exportar

                    </button>

                </div>

            </div>


            <!--=================================================
                FILTROS
            ==================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="row g-3 align-items-end">


                        <!-- AÑO -->

                        <div class="col-12 col-sm-6 col-lg-3">

                            <label
                                for="filtroAnioContabilidad"
                                class="form-label fw-semibold">

                                <i class="bi bi-calendar3 me-1"></i>

                                Año

                            </label>

                            <select
                                id="filtroAnioContabilidad"
                                class="form-select">

                                <option value="">
                                    Todos los años
                                </option>

                            </select>

                        </div>


                        <!-- PERÍODO -->

                        <div class="col-12 col-sm-6 col-lg-3">

                            <label
                                for="filtroPeriodoContabilidad"
                                class="form-label fw-semibold">

                                <i class="bi bi-calendar-range me-1"></i>

                                Período

                            </label>

                            <select
                                id="filtroPeriodoContabilidad"
                                class="form-select">

                                <option value="todos">
                                    Todo el año
                                </option>

                                <option value="01">
                                    Enero
                                </option>

                                <option value="02">
                                    Febrero
                                </option>

                                <option value="03">
                                    Marzo
                                </option>

                                <option value="04">
                                    Abril
                                </option>

                                <option value="05">
                                    Mayo
                                </option>

                                <option value="06">
                                    Junio
                                </option>

                                <option value="07">
                                    Julio
                                </option>

                                <option value="08">
                                    Agosto
                                </option>

                                <option value="09">
                                    Septiembre
                                </option>

                                <option value="10">
                                    Octubre
                                </option>

                                <option value="11">
                                    Noviembre
                                </option>

                                <option value="12">
                                    Diciembre
                                </option>

                            </select>

                        </div>


                        <!-- FECHA INICIAL -->

                        <div class="col-12 col-sm-6 col-lg-3">

                            <label
                                for="fechaInicioContabilidad"
                                class="form-label fw-semibold">

                                <i class="bi bi-calendar-event me-1"></i>

                                Fecha inicial

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    id="fechaInicioContabilidad"
                                    class="form-control"
                                    placeholder="Fecha inicial"
                                    autocomplete="off">

                            </div>

                        </div>


                        <!-- FECHA FINAL -->

                        <div class="col-12 col-sm-6 col-lg-3">

                            <label
                                for="fechaFinContabilidad"
                                class="form-label fw-semibold">

                                <i class="bi bi-calendar-event me-1"></i>

                                Fecha final

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    id="fechaFinContabilidad"
                                    class="form-control"
                                    placeholder="Fecha final"
                                    autocomplete="off">

                            </div>

                        </div>


                    </div>


                    <!-- BOTONES FILTRO -->

                    <div class="d-flex flex-wrap gap-2 mt-3">

                        <button
                            type="button"
                            class="btn btn-primary"
                            id="btnAplicarFiltrosContabilidad">

                            <i class="bi bi-funnel-fill me-1"></i>

                            Aplicar filtros

                        </button>


                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="btnLimpiarFiltrosContabilidad">

                            <i class="bi bi-x-circle me-1"></i>

                            Limpiar

                        </button>

                    </div>

                </div>

            </div>


            <!--=================================================
                KPIs
            ==================================================-->

            <div class="row g-4 mb-4">


                <!--=================================================
                    INGRESOS
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="text-muted mb-1">
                                        Total Ingresos
                                    </p>

                                    <h3
                                        class="fw-bold mb-2"
                                        id="kpiIngresos">

                                        S/ 0.00

                                    </h3>

                                    <small
                                        class="text-success"
                                        id="variacionIngresos">

                                        <i class="bi bi-arrow-up"></i>

                                        0%

                                    </small>

                                    <span class="text-muted small">
                                        vs. período anterior
                                    </span>

                                </div>


                                <div class="rounded-circle bg-success-subtle p-3">

                                    <i class="bi bi-arrow-down-left-circle-fill text-success fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    GASTOS
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="text-muted mb-1">
                                        Total Gastos
                                    </p>

                                    <h3
                                        class="fw-bold mb-2"
                                        id="kpiGastos">

                                        S/ 0.00

                                    </h3>

                                    <small
                                        class="text-danger"
                                        id="variacionGastos">

                                        <i class="bi bi-arrow-up"></i>

                                        0%

                                    </small>

                                    <span class="text-muted small">
                                        vs. período anterior
                                    </span>

                                </div>


                                <div class="rounded-circle bg-danger-subtle p-3">

                                    <i class="bi bi-arrow-up-right-circle-fill text-danger fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    UTILIDAD
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="text-muted mb-1">
                                        Utilidad Neta
                                    </p>

                                    <h3
                                        class="fw-bold mb-2"
                                        id="kpiUtilidad">

                                        S/ 0.00

                                    </h3>

                                    <small
                                        id="variacionUtilidad"
                                        class="text-success">

                                        <i class="bi bi-arrow-up"></i>

                                        0%

                                    </small>

                                    <span class="text-muted small">
                                        vs. período anterior
                                    </span>

                                </div>


                                <div class="rounded-circle bg-primary-subtle p-3">

                                    <i class="bi bi-graph-up-arrow text-primary fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    BALANCE
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="text-muted mb-1">
                                        Balance Bancario
                                    </p>

                                    <h3
                                        class="fw-bold mb-2"
                                        id="kpiBalance">

                                        S/ 0.00

                                    </h3>

                                    <small
                                        class="text-muted"
                                        id="cantidadCuentas">

                                        0 cuentas

                                    </small>

                                </div>


                                <div class="rounded-circle bg-warning-subtle p-3">

                                    <i class="bi bi-bank2 text-warning fs-4"></i>

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


                <!--=================================================
                    INGRESOS VS GASTOS
                ==================================================-->

                <div class="col-12 col-xl-8">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        Ingresos vs. Gastos

                                    </h5>

                                    <p class="text-muted small mb-0">

                                        Comparación financiera del período seleccionado.

                                    </p>

                                </div>


                                <span class="badge bg-light text-dark">

                                    <i class="bi bi-bar-chart-line me-1"></i>

                                    Mensual

                                </span>

                            </div>

                        </div>


                        <div class="card-body px-4">

                            <div
                                class="position-relative"
                                style="height: 330px;">

                                <canvas
                                    id="graficoIngresosGastos">
                                </canvas>

                            </div>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    DISTRIBUCIÓN DE GASTOS
                ==================================================-->

                <div class="col-12 col-xl-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <h5 class="fw-bold mb-1">

                                Distribución de Gastos

                            </h5>

                            <p class="text-muted small mb-0">

                                Gastos agrupados por categoría.

                            </p>

                        </div>


                        <div class="card-body px-4">

                            <div
                                class="position-relative"
                                style="height: 270px;">

                                <canvas
                                    id="graficoGastosCategoria">
                                </canvas>

                            </div>


                            <div
                                id="leyendaGastosCategoria"
                                class="mt-3">

                                <!-- SE GENERARÁ CON JAVASCRIPT -->

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!--=================================================
                EVOLUCIÓN FINANCIERA
            ==================================================-->

            <div class="row g-4 mb-4">

                <div class="col-12">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <div class="d-flex flex-wrap justify-content-between align-items-center">

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        Evolución Financiera

                                    </h5>

                                    <p class="text-muted small mb-0">

                                        Evolución de ingresos, gastos y utilidad.

                                    </p>

                                </div>


                                <div class="btn-group mt-3 mt-md-0"
                                    role="group"
                                    aria-label="Período gráfico">

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary btn-periodo-grafico active"
                                        data-periodo="12">

                                        12 meses

                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-periodo-grafico"
                                        data-periodo="6">

                                        6 meses

                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-periodo-grafico"
                                        data-periodo="3">

                                        3 meses

                                    </button>

                                </div>

                            </div>

                        </div>


                        <div class="card-body px-4">

                            <div
                                class="position-relative"
                                style="height: 340px;">

                                <canvas
                                    id="graficoEvolucionFinanciera">
                                </canvas>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                CUENTAS BANCARIAS + RESUMEN
            ==================================================-->

            <div class="row g-4 mb-4">


                <!--=================================================
                    CUENTAS BANCARIAS
                ==================================================-->

                <div class="col-12 col-xl-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <h5 class="fw-bold mb-1">

                                        Cuentas Bancarias

                                    </h5>

                                    <p class="text-muted small mb-0">

                                        Balance actual de las cuentas registradas.

                                    </p>

                                </div>


                                <a
                                    href="adm_cuentas_bancarias.php"
                                    class="btn btn-sm btn-outline-primary">

                                    Ver cuentas

                                </a>

                            </div>

                        </div>


                        <div class="card-body px-4">

                            <div
                                id="contenedorCuentasBancarias"
                                class="table-responsive">

                                <table class="table align-middle mb-0">

                                    <thead>

                                        <tr>

                                            <th>
                                                Cuenta
                                            </th>

                                            <th class="text-end">
                                                Balance
                                            </th>

                                            <th class="text-end">
                                                Estado
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody id="tablaCuentasBancarias">

                                        <tr>

                                            <td colspan="3"
                                                class="text-center text-muted py-4">

                                                <i class="bi bi-bank fs-3 d-block mb-2"></i>

                                                No hay información disponible.

                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    RESUMEN POR TIPO
                ==================================================-->

                <div class="col-12 col-xl-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <h5 class="fw-bold mb-1">

                                Resumen Financiero

                            </h5>

                            <p class="text-muted small mb-0">

                                Distribución general de los movimientos.

                            </p>

                        </div>


                        <div class="card-body px-4">


                            <!-- INGRESOS -->

                            <div class="d-flex justify-content-between align-items-center mb-4">

                                <div class="d-flex align-items-center">

                                    <div class="rounded-circle bg-success-subtle p-2 me-3">

                                        <i class="bi bi-plus-circle-fill text-success"></i>

                                    </div>

                                    <div>

                                        <span class="fw-semibold d-block">
                                            Ingresos
                                        </span>

                                        <small class="text-muted">
                                            Entradas de dinero
                                        </small>

                                    </div>

                                </div>


                                <strong
                                    class="text-success"
                                    id="resumenIngresos">

                                    S/ 0.00

                                </strong>

                            </div>


                            <!-- GASTOS -->

                            <div class="d-flex justify-content-between align-items-center mb-4">

                                <div class="d-flex align-items-center">

                                    <div class="rounded-circle bg-danger-subtle p-2 me-3">

                                        <i class="bi bi-dash-circle-fill text-danger"></i>

                                    </div>

                                    <div>

                                        <span class="fw-semibold d-block">
                                            Gastos
                                        </span>

                                        <small class="text-muted">
                                            Salidas de dinero
                                        </small>

                                    </div>

                                </div>


                                <strong
                                    class="text-danger"
                                    id="resumenGastos">

                                    S/ 0.00

                                </strong>

                            </div>


                            <!-- UTILIDAD -->

                            <div class="d-flex justify-content-between align-items-center mb-4">

                                <div class="d-flex align-items-center">

                                    <div class="rounded-circle bg-primary-subtle p-2 me-3">

                                        <i class="bi bi-graph-up-arrow text-primary"></i>

                                    </div>

                                    <div>

                                        <span class="fw-semibold d-block">
                                            Utilidad
                                        </span>

                                        <small class="text-muted">
                                            Ingresos menos gastos
                                        </small>

                                    </div>

                                </div>


                                <strong
                                    class="text-primary"
                                    id="resumenUtilidad">

                                    S/ 0.00

                                </strong>

                            </div>


                            <hr>


                            <!-- MARGEN -->

                            <div class="d-flex justify-content-between align-items-center">

                                <span class="fw-semibold">

                                    Margen de utilidad

                                </span>


                                <span
                                    class="badge bg-primary-subtle text-primary fs-6"
                                    id="resumenMargen">

                                    0%

                                </span>

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!--=================================================
                ÚLTIMOS MOVIMIENTOS
            ==================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 pt-4 px-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center">

                        <div>

                            <h5 class="fw-bold mb-1">

                                Últimos Movimientos

                            </h5>

                            <p class="text-muted small mb-0">

                                Últimos ingresos y gastos registrados.

                            </p>

                        </div>


                        <a
                            href="adm_deposito_gasto.php"
                            class="btn btn-sm btn-outline-primary mt-3 mt-md-0">

                            <i class="bi bi-arrow-left-right me-1"></i>

                            Ver todos

                        </a>

                    </div>

                </div>


                <div class="card-body px-4">

                    <div class="table-responsive">

                        <table
                            class="table table-hover align-middle mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Fecha
                                    </th>

                                    <th>
                                        Concepto
                                    </th>

                                    <th>
                                        Categoría
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


                            <tbody id="tablaUltimosMovimientos">

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-5">

                                        <div class="py-3">

                                            <i class="bi bi-receipt fs-1 d-block mb-3"></i>

                                            <h6 class="fw-semibold">

                                                No hay movimientos

                                            </h6>

                                            <p class="small mb-0">

                                                Los últimos movimientos aparecerán aquí.

                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <!--=================================================
                ESTADO DE CARGA
            ==================================================-->

            <div
                id="loaderContabilidad"
                class="d-none text-center py-5">

                <div
                    class="spinner-border text-primary"
                    role="status">

                    <span class="visually-hidden">
                        Cargando...
                    </span>

                </div>

                <p class="text-muted mt-3 mb-0">

                    Cargando información contable...

                </p>

            </div>


            <!--=================================================
                MENSAJE DE ERROR
            ==================================================-->

            <div
                id="errorContabilidad"
                class="alert alert-danger d-none border-0 shadow-sm">

                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                <span id="mensajeErrorContabilidad">
                    Ocurrió un error al cargar la información.
                </span>

            </div>


        </main>

    </div>

</div>
<!--=====================================================
    MODALES
======================================================-->

<?php include "modal/modal_adm_exportar_contabilidad.php"; ?>
<!--=====================================================
    FLATPICKR
======================================================-->

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>


<!--=====================================================
    BOOTSTRAP
======================================================-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>


<!--=====================================================
    CHART.JS
======================================================-->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<!--=====================================================
    JAVASCRIPT DEL MÓDULO
======================================================-->

<script src="js/adm_contabilidad.js"></script>


<!--=====================================================
    MENÚ ADMINISTRADOR
======================================================-->

<script src="js/menu.js"></script>


</body>

</html>