<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_pago_empleado.php
// Módulo: Pagos a Empleados
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

<link
    rel="stylesheet"
    href="css/adm_pago_empleado.css">


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
                CABECERA
            ==================================================-->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-wallet2 text-primary me-2"></i>

                        Pagos a Empleados

                    </h2>

                    <p class="text-muted mb-0">

                        Registra y administra los pagos realizados a tus empleados.

                    </p>

                </div>


                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnNuevoPago">

                    <i class="bi bi-plus-circle me-2"></i>

                    Registrar Pago

                </button>

            </div>


            <!--=================================================
                KPI
            ==================================================-->

            <div class="row g-3 mb-4">


                <!-- TOTAL PAGADO -->

                <div class="col-xl-4 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <span class="text-muted">
                                        Total Pagado
                                    </span>

                                    <h3
                                        class="fw-bold mt-2 mb-0"
                                        id="kpiTotalPagado">

                                        S/ 0.00

                                    </h3>

                                </div>

                                <div class="kpi-icon bg-success-subtle text-success">

                                    <i class="bi bi-cash-stack"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- PENDIENTE -->

                <div class="col-xl-4 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <span class="text-muted">
                                        Pagos Pendientes
                                    </span>

                                    <h3
                                        class="fw-bold mt-2 mb-0"
                                        id="kpiPendiente">

                                        S/ 0.00

                                    </h3>

                                </div>

                                <div class="kpi-icon bg-warning-subtle text-warning">

                                    <i class="bi bi-hourglass-split"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- MES ACTUAL -->

                <div class="col-xl-4 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <span class="text-muted">
                                        Pagado Este Mes
                                    </span>

                                    <h3
                                        class="fw-bold mt-2 mb-0"
                                        id="kpiMesActual">

                                        S/ 0.00

                                    </h3>

                                </div>

                                <div class="kpi-icon bg-primary-subtle text-primary">

                                    <i class="bi bi-calendar-check"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                FILTROS
            ==================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="row g-3 align-items-end">

                        <!-- BUSCAR -->
                        <div class="col-md-4">

                            <label class="form-label">
                                Buscar empleado
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarPagoEmpleado"
                                    placeholder="Nombre, apellido o DNI">

                            </div>

                        </div>


                        <!-- ESTADO -->
                        <div class="col-md-2">

                            <label class="form-label">
                                Estado
                            </label>

                            <select
                                class="form-select"
                                id="filtroEstadoPago">

                                <option value="">Todos</option>
                                <option value="PENDIENTE">Pendiente</option>
                                <option value="PAGADO">Pagado</option>
                                <option value="ANULADO">Anulado</option>

                            </select>

                        </div>


                        <!-- FECHA INICIO -->
                        <div class="col-md-2">

                            <label class="form-label">
                                Desde
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="filtroFechaInicioPago">

                        </div>


                        <!-- FECHA FIN -->
                        <div class="col-md-2">

                            <label class="form-label">
                                Hasta
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="filtroFechaFinPago">

                        </div>


                        <!-- RESTABLECER -->
                        <div class="col-md-2">

                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                id="btnRestablecerFiltrosPagos">

                                <i class="bi bi-arrow-counterclockwise me-1"></i>

                                Restablecer

                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                TABLA
            ==================================================-->

            <div class="card border-0 shadow-sm">

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table
                            class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>
                                        Empleado
                                    </th>

                                    <th>
                                        Periodo
                                    </th>

                                    <th>
                                        Monto Base
                                    </th>

                                    <th>
                                        Bonificaciones
                                    </th>

                                    <th>
                                        Descuentos
                                    </th>

                                    <th>
                                        Total
                                    </th>

                                    <th>
                                        Fecha Pago
                                    </th>

                                    <th>
                                        Estado
                                    </th>

                                    <th class="text-center">
                                        Acciones
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tablaPagos">

                                <tr>

                                    <td
                                        colspan="9"
                                        class="text-center py-5">

                                        <div class="spinner-border text-primary"
                                            role="status">

                                        </div>

                                        <div class="mt-2 text-muted">

                                            Cargando pagos...

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- PAGINACIÓN -->

                <div class="card-footer bg-white">

                    <div
                        class="d-flex justify-content-between align-items-center">

                        <span
                            class="text-muted small"
                            id="infoPaginacion">

                            Mostrando 0 registros

                        </span>


                        <nav>

                            <ul
                                class="pagination pagination-sm mb-0"
                                id="paginacionPagos">

                            </ul>

                        </nav>

                    </div>

                </div>

            </div>


        </main>

    </div>

</div>


<!--=====================================================
    MODAL REGISTRAR PAGO
======================================================-->

<?php

$archivoModalPago =
    "modal/modal_registrar_pago_empleado.php";

if (file_exists($archivoModalPago)) {

    include $archivoModalPago;
}

?>


<!--=====================================================
    MODAL EDITAR PAGO
======================================================-->

<?php

$archivoModalEditarPago =
    "modal/modal_editar_pago_empleado.php";

if (file_exists($archivoModalEditarPago)) {

    include $archivoModalEditarPago;
}

?>


<!--=====================================================
    LIBRERÍAS JAVASCRIPT
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
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
    JAVASCRIPT
======================================================-->

<script
    src="js/adm_lista_pagos.js">
</script>


<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>