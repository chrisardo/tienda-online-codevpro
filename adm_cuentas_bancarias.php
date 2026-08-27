<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_cuentas_bancarias.php
// Módulo: Cuentas Bancarias
// Sistema: Inventa
//=====================================================

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
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

        <main class="container-fluid py-4 px-4 cuentas-bancarias-page">


            <!--=================================================
                CABECERA DEL MÓDULO
            ==================================================-->

            <div class="cuentas-header mb-4">

                <div class="row align-items-center g-3">

                    <!-- TÍTULO -->

                    <div class="col-12 col-lg">

                        <div class="d-flex align-items-center">

                            <div class="cuentas-header-icon me-3">

                                <i class="bi bi-bank2"></i>

                            </div>

                            <div>

                                <h1 class="cuentas-title mb-1">
                                    Cuentas Bancarias
                                </h1>

                                <p class="cuentas-subtitle mb-0">
                                    Administra las cuentas y controla los balances financieros de tu empresa.
                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- BOTÓN NUEVA CUENTA -->

                    <div class="col-12 col-lg-auto">

                        <button
                            type="button"
                            class="btn btn-primary cuentas-btn-nueva"
                            data-bs-toggle="modal"
                            data-bs-target="#modalRegistrarCuenta">

                            <i class="bi bi-plus-circle me-2"></i>

                            Nueva cuenta

                        </button>

                    </div>

                </div>

            </div>


            <!--=================================================
                KPI
            ==================================================-->

            <div class="row g-3 mb-4">


                <!--=================================================
                    KPI TOTAL CUENTAS
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card cuentas-kpi-card h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="cuentas-kpi-label mb-2">
                                        Total de cuentas
                                    </p>

                                    <h3
                                        class="cuentas-kpi-value mb-0"
                                        id="kpiTotalCuentas">

                                        0

                                    </h3>

                                </div>


                                <div class="cuentas-kpi-icon cuentas-kpi-icon-primary">

                                    <i class="bi bi-bank"></i>

                                </div>

                            </div>

                            <div class="cuentas-kpi-footer mt-3">

                                <i class="bi bi-wallet2 me-1"></i>

                                Cuentas registradas

                            </div>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    KPI CUENTAS ACTIVAS
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card cuentas-kpi-card h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="cuentas-kpi-label mb-2">
                                        Cuentas activas
                                    </p>

                                    <h3
                                        class="cuentas-kpi-value text-success mb-0"
                                        id="kpiCuentasActivas">

                                        0

                                    </h3>

                                </div>


                                <div class="cuentas-kpi-icon cuentas-kpi-icon-success">

                                    <i class="bi bi-check-circle"></i>

                                </div>

                            </div>

                            <div class="cuentas-kpi-footer mt-3">

                                <i class="bi bi-check2-circle me-1"></i>

                                Disponibles actualmente

                            </div>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    KPI CUENTAS INACTIVAS
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card cuentas-kpi-card h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="cuentas-kpi-label mb-2">
                                        Cuentas inactivas
                                    </p>

                                    <h3
                                        class="cuentas-kpi-value text-secondary mb-0"
                                        id="kpiCuentasInactivas">

                                        0

                                    </h3>

                                </div>


                                <div class="cuentas-kpi-icon cuentas-kpi-icon-secondary">

                                    <i class="bi bi-pause-circle"></i>

                                </div>

                            </div>

                            <div class="cuentas-kpi-footer mt-3">

                                <i class="bi bi-archive me-1"></i>

                                Cuentas desactivadas

                            </div>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    KPI SALDO TOTAL
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card cuentas-kpi-card h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <p class="cuentas-kpi-label mb-2">
                                        Saldo total
                                    </p>

                                    <h3
                                        class="cuentas-kpi-value cuentas-saldo-total mb-0"
                                        id="kpiSaldoTotal">

                                        S/. 0.00

                                    </h3>

                                </div>


                                <div class="cuentas-kpi-icon cuentas-kpi-icon-info">

                                    <i class="bi bi-cash-stack"></i>

                                </div>

                            </div>

                            <div class="cuentas-kpi-footer mt-3">

                                <i class="bi bi-graph-up-arrow me-1"></i>

                                Saldo de cuentas activas

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                CARD PRINCIPAL
            ==================================================-->

            <div class="card cuentas-main-card border-0 shadow-sm">


                <!--=================================================
                    CABECERA FILTROS
                ==================================================-->

                <div class="card-body cuentas-filtros-container">


                    <div class="row g-3 align-items-end">


                        <!--=================================================
                            BUSCADOR
                        ==================================================-->

                        <div class="col-12 col-lg-5">

                            <label
                                for="buscarCuenta"
                                class="form-label cuentas-filter-label">

                                Buscar cuenta

                            </label>


                            <div class="input-group cuentas-search-group">

                                <span class="input-group-text">

                                    <i class="bi bi-search"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarCuenta"
                                    placeholder="Buscar por nombre de cuenta...">

                            </div>

                        </div>


                        <!--=================================================
                            FILTRO ESTADO
                        ==================================================-->

                        <div class="col-12 col-sm-6 col-lg-3">

                            <label
                                for="filtroEstadoCuenta"
                                class="form-label cuentas-filter-label">

                                Estado

                            </label>


                            <select
                                class="form-select"
                                id="filtroEstadoCuenta">

                                <option value="">
                                    Todos los estados
                                </option>

                                <option value="0">
                                    Activas
                                </option>

                                <option value="1">
                                    Inactivas
                                </option>

                            </select>

                        </div>


                        <!--=================================================
                            ORDENAR
                        ==================================================-->

                        <div class="col-12 col-sm-6 col-lg-3">

                            <label
                                for="ordenCuentas"
                                class="form-label cuentas-filter-label">

                                Ordenar por

                            </label>


                            <select
                                class="form-select"
                                id="ordenCuentas">

                                <option value="nombre_asc">
                                    Nombre A - Z
                                </option>

                                <option value="nombre_desc">
                                    Nombre Z - A
                                </option>

                                <option value="balance_desc">
                                    Mayor saldo
                                </option>

                                <option value="balance_asc">
                                    Menor saldo
                                </option>

                            </select>

                        </div>


                        <!--=================================================
                            LIMPIAR
                        ==================================================-->

                        <div class="col-12 col-lg-1">

                            <button
                                type="button"
                                class="btn btn-outline-secondary cuentas-btn-limpiar w-100"
                                id="btnLimpiarFiltros"
                                title="Limpiar filtros">

                                <i class="bi bi-arrow-counterclockwise"></i>

                            </button>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    SEPARADOR
                ==================================================-->

                <div class="cuentas-divider"></div>


                <!--=================================================
                    CABECERA TABLA
                ==================================================-->

                <div class="card-body py-3">

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">


                        <div>

                            <h5 class="cuentas-table-title mb-1">

                                <i class="bi bi-list-ul me-2"></i>

                                Lista de cuentas

                            </h5>

                            <p class="cuentas-table-description mb-0">

                                Administra las cuentas bancarias registradas.

                            </p>

                        </div>


                        <div class="cuentas-registros-info">

                            Mostrando
                            <strong id="registrosMostrados">
                                0
                            </strong>
                            cuentas

                        </div>

                    </div>

                </div>


                <!--=================================================
                    TABLA
                ==================================================-->

                <div class="table-responsive cuentas-table-wrapper">

                    <table class="table cuentas-table align-middle mb-0">

                        <thead>

                            <tr>

                                <th class="cuenta-columna-nombre">
                                    CUENTA BANCARIA
                                </th>

                                <th class="cuenta-columna-balance">
                                    BALANCE
                                </th>

                                <th class="cuenta-columna-estado">
                                    ESTADO
                                </th>

                                <th class="cuenta-columna-acciones text-end">
                                    ACCIONES
                                </th>

                            </tr>

                        </thead>


                        <tbody id="tablaCuentasBancarias">


                            <!--=================================================
                                ESTADO INICIAL
                                Posteriormente será reemplazado por AJAX
                            ==================================================-->

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center cuentas-empty-state">

                                    <div class="cuentas-empty-icon">

                                        <i class="bi bi-bank"></i>

                                    </div>

                                    <h6 class="mb-2">
                                        No hay cuentas para mostrar
                                    </h6>

                                    <p class="mb-3">
                                        Registra tu primera cuenta bancaria para comenzar.
                                    </p>

                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalRegistrarCuenta">

                                        <i class="bi bi-plus-circle me-1"></i>

                                        Registrar cuenta

                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!--=================================================
                    FOOTER TABLA
                ==================================================-->

                <div class="card-body cuentas-table-footer">


                    <div class="row align-items-center g-3">


                        <!-- INFORMACIÓN -->

                        <div class="col-12 col-md">

                            <div class="cuentas-footer-info">

                                <span id="textoRegistros">

                                    Mostrando 0 de 0 cuentas

                                </span>

                            </div>

                        </div>


                        <!-- PAGINACIÓN -->

                        <div class="col-12 col-md-auto">

                            <nav
                                aria-label="Paginación de cuentas bancarias">

                                <ul
                                    class="pagination cuentas-pagination mb-0"
                                    id="paginacionCuentas">

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

                </div>

            </div>

        </main>

    </div>

</div>


<!--=====================================================
    MODALES
======================================================-->

<?php include "modal/modal_adm_registrar_cuenta.php"; ?>

<?php include "modal/modal_adm_editar_cuenta.php"; ?>
<?php include "modal/modal_adm_cambiar_estado_cuenta.php"; ?>

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

<script src="js/adm_cuentas_bancarias.js"></script>

<script src="js/menu.js"></script>


</body>

</html>