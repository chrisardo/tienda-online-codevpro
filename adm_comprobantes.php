<?php
//=====================================================
// CoDevPro Technology
// admin_comprobantes.php
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
            <!-- =====================================================
            =          HEADER COMPROBANTES
            ====================================================== -->
            <div class="row align-items-center mb-4">

                <div class="col-lg-8">

                    <nav aria-label="breadcrumb">

                        <ol class="breadcrumb mb-2">

                            <li class="breadcrumb-item">

                                <a href="admin_index.php"
                                    class="text-decoration-none">

                                    <i class="bi bi-house-door-fill"></i>
                                    Dashboard

                                </a>

                            </li>

                            <li class="breadcrumb-item">

                                Ventas

                            </li>

                            <li class="breadcrumb-item active">

                                Comprobantes

                            </li>

                        </ol>

                    </nav>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>

                        Gestión de Comprobantes

                    </h2>

                    <p class="text-muted mb-0">

                        Administra facturas, boletas, notas de venta y comprobantes emitidos.

                    </p>

                </div>
            </div>
            <!-- =====================================================
            =                    KPI CARDS
            ====================================================== -->

            <div class="row g-4 mb-4">
                <!-- TOTAL -->
                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm rounded-4 h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Total

                                    </small>

                                    <h3 class="fw-bold mt-2 mb-0" id="kpiComprobantes">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-primary-subtle">

                                    <i class="bi bi-files text-primary fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
                <!-- BOLETAS -->
                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm rounded-4 h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Boletas

                                    </small>

                                    <h3 class="fw-bold mt-2 mb-0" id="kpiBoletas">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-success-subtle">

                                    <i class="bi bi-receipt text-success fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
                <!-- FACTURAS -->
                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm rounded-4 h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Facturas

                                    </small>

                                    <h3 class="fw-bold mt-2 mb-0" id="kpiFacturas">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-warning-subtle">

                                    <i class="bi bi-file-earmark-text text-warning fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
                <!-- NOTAS -->
                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Notas Venta

                                    </small>

                                    <h3 class="fw-bold mt-2 mb-0" id="kpiNotasVenta">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-info-subtle">

                                    <i class="bi bi-file-earmark-medical text-info fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
                <!-- ANULADOS -->
                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Anulados

                                    </small>

                                    <h3 class="fw-bold mt-2 mb-0" id="kpiAnulados">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-danger-subtle">

                                    <i class="bi bi-x-circle text-danger fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
                <!-- TOTAL VENTAS -->
                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Total Ventas

                                    </small>

                                    <h4 class="fw-bold mt-2 mb-0 text-success" id="kpiMonto">

                                        S/ 0.00

                                    </h4>

                                </div>

                                <div class="icon-circle bg-success-subtle">

                                    <i class="bi bi-currency-dollar text-success fs-3"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>
            <!-- =====================================================
            =               BARRA DE ACCIONES: BUSCADOR Y FILTROS
            ====================================================== -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="row align-items-center gy-3">
                        <!-- BUSCADOR -->
                        <div class="col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input
                                    type="text"
                                    id="buscarComprobante"
                                    class="form-control"
                                    placeholder="Buscar comprobante...">
                            </div>
                        </div>
                        <!-- FECHAS -->
                        <div class="col-lg-3">
                            <input
                                type="text"
                                id="rangoFecha"
                                class="form-control"
                                placeholder="Seleccione rango">

                        </div>
                        <!-- FILTRO -->
                        <div class="col-lg-2">
                            <button
                                class="btn btn-outline-primary w-100"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#offcanvasFiltros">
                                <i class="bi bi-funnel me-2"></i>
                                Filtros
                            </button>
                        </div>
                        <!-- ACTUALIZAR -->
                        <div class="col-lg-1">
                            <button
                                class="btn btn-outline-secondary w-100"
                                id="btnActualizar">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <!-- EXPORTAR -->
                        <div class="col-lg-1">
                            <div class="dropdown w-100">
                                <button
                                    class="btn btn-success dropdown-toggle w-100"
                                    data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li>
                                        <a class="dropdown-item"
                                            href="#">
                                            <i class="bi bi-file-earmark-excel text-success me-2"></i>
                                            Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="#">
                                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                            PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--=====================================================
            =            TABLA PREMIUM COMPROBANTES
            ======================================================-->
            <div class="card border-0 shadow-sm rounded-4">

                <!-- HEADER TABLA -->

                <div class="card-header bg-white border-0 py-3">

                    <div class="row align-items-center">

                        <div class="col-lg-6">

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>

                                Lista de Comprobantes

                            </h5>

                            <small class="text-muted">

                                Administra todas las boletas, facturas y notas de venta emitidas.

                            </small>

                        </div>


                        <div class="col-lg-6">

                            <div class="d-flex justify-content-lg-end gap-2 mt-3 mt-lg-0">

                                <!-- REGISTROS -->

                                <select
                                    id="cantidadRegistros"
                                    class="form-select"
                                    style="width:110px;">

                                    <option value="10">10</option>

                                    <option value="20" selected>20</option>

                                    <option value="50">50</option>

                                    <option value="100">100</option>

                                    <option value="200">200</option>

                                </select>


                                <!-- RECARGAR -->

                                <button
                                    class="btn btn-outline-primary"
                                    id="recargarTabla">

                                    <i class="bi bi-arrow-repeat"></i>

                                </button>

                            </div>

                        </div>

                    </div>

                </div>
                <!--=====================================================
                TABLA
                ======================================================-->

                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                        id="tablaComprobantes">

                        <thead class="table-light">

                            <tr>

                                <th width="45">

                                    <input
                                        type="checkbox"
                                        id="checkTodos"
                                        class="form-check-input">

                                </th>

                                <th>#</th>

                                <th>Comprobante</th>

                                <th>Cliente</th>

                                <th>DNI / RUC</th>

                                <th>Fecha</th>

                                <th>Hora</th>

                                <th>Método Pago</th>

                                <th>Empleado</th>

                                <th>Total</th>

                                <th>Estado</th>

                                <th class="text-center">

                                    Acciones

                                </th>

                            </tr>

                        </thead>

                        <tbody id="tbodyComprobantes">
                            <!--=====================================================
                            FILA PLACEHOLDER
                            ======================================================-->

                            <tr>
                            </tr>


                            <!-- AJAX -->

                        </tbody>

                    </table>

                </div>
                <!--=====================================================
                FOOTER TABLA
                ======================================================-->

                <div class="card-footer bg-white">

                    <div class="row align-items-center">

                        <!-- INFORMACION -->

                        <div class="col-md-4">

                            <small
                                class="text-muted"
                                id="textoRegistros">

                                Mostrando 1 a 20 de 0 registros

                            </small>

                        </div>

                        <!-- PAGINACION -->

                        <div class="col-md-4">

                            <nav>

                                <ul
                                    class="pagination pagination-sm justify-content-end mb-0"
                                    id="paginacionComprobantes">

                                    <li class="page-item disabled">

                                        <a
                                            class="page-link"
                                            href="#">

                                            <i class="bi bi-chevron-double-left"></i>

                                        </a>

                                    </li>

                                    <li class="page-item disabled">

                                        <a
                                            class="page-link"
                                            href="#">

                                            <i class="bi bi-chevron-left"></i>

                                        </a>

                                    </li>

                                    <li class="page-item active">

                                        <a
                                            class="page-link"
                                            href="#">

                                            1

                                        </a>

                                    </li>

                                    <li class="page-item">

                                        <a
                                            class="page-link"
                                            href="#">

                                            2

                                        </a>

                                    </li>

                                    <li class="page-item">

                                        <a
                                            class="page-link"
                                            href="#">

                                            3

                                        </a>

                                    </li>

                                    <li class="page-item">

                                        <a
                                            class="page-link"
                                            href="#">

                                            <i class="bi bi-chevron-right"></i>

                                        </a>

                                    </li>

                                    <li class="page-item">

                                        <a
                                            class="page-link"
                                            href="#">

                                            <i class="bi bi-chevron-double-right"></i>

                                        </a>

                                    </li>

                                </ul>

                            </nav>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
    <!--=====================================================
=          OFFCANVAS FILTROS PREMIUM
======================================================-->

    <div class="offcanvas offcanvas-end"
        tabindex="-1"
        id="offcanvasFiltros"
        style="width:430px;">

        <div class="offcanvas-header border-bottom">

            <div>

                <h5 class="mb-1 fw-bold">

                    <i class="bi bi-funnel-fill text-primary me-2"></i>

                    Filtros Avanzados

                </h5>

                <small class="text-muted">

                    Filtra los comprobantes según múltiples criterios.

                </small>

            </div>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas">
            </button>

        </div>


        <div class="offcanvas-body">

            <div class="row g-3">

                <!-- Tipo -->

                <div class="col-12">

                    <label class="form-label fw-semibold">

                        Tipo de comprobante

                    </label>

                    <select
                        class="form-select"
                        id="filtroTipo">

                        <option value="">Todos</option>

                        <option value="BOLETA">
                            Boleta
                        </option>

                        <option value="FACTURA">
                            Factura
                        </option>

                        <option value="NOTA VENTA">
                            Nota de Venta
                        </option>

                    </select>

                </div>


                <!-- Estado -->

                <div class="col-12">

                    <label class="form-label fw-semibold">

                        Estado

                    </label>

                    <select
                        class="form-select"
                        id="filtroEstado">

                        <option value="">Todos</option>

                        <option value="EMITIDO">Emitido</option>

                        <option value="ANULADO">Anulado</option>

                        <option value="CANCELADO">Cancelado</option>

                    </select>

                </div>


                <!-- Método -->

                <div class="col-12">

                    <label class="form-label fw-semibold">

                        Método de pago

                    </label>

                    <select
                        class="form-select"
                        id="filtroMetodoPago">

                        <option value="">
                            Todos
                        </option>

                    </select>

                </div>


                <!-- Empleado -->

                <div class="col-12">

                    <label class="form-label fw-semibold">

                        Empleado

                    </label>

                    <select
                        class="form-select"
                        id="filtroEmpleado">

                        <option value="">
                            Todos
                        </option>

                    </select>

                </div>


                <!-- Cliente -->

                <div class="col-12">

                    <label class="form-label fw-semibold">

                        Cliente

                    </label>

                    <select id="filtroCliente" class="form-select">
                        <option value="">Todos los clientes</option>
                    </select>

                </div>


                <!-- Monto -->

                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        Monto mínimo

                    </label>

                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-control"
                        id="montoMin">

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        Monto máximo

                    </label>

                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-control"
                        id="montoMax">

                </div>


                <!-- Orden -->

                <div class="col-12">

                    <label class="form-label fw-semibold">

                        Ordenar por

                    </label>

                    <select
                        class="form-select"
                        id="ordenarPor">

                        <option value="fecha_desc">
                            Más recientes
                        </option>

                        <option value="fecha_asc">
                            Más antiguos
                        </option>

                        <option value="monto_desc">
                            Mayor monto
                        </option>

                        <option value="monto_asc">
                            Menor monto
                        </option>

                        <option value="cliente_asc">
                            Cliente A-Z
                        </option>

                        <option value="cliente_desc">
                            Cliente Z-A
                        </option>

                    </select>

                </div>


                <!-- Switches -->

                <div class="col-12 mt-3">

                    <div class="card border rounded-4">

                        <div class="card-body">

                            <div class="form-check form-switch mb-3">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="soloIGV">

                                <label
                                    class="form-check-label"
                                    for="soloIGV">

                                    Solo comprobantes con IGV

                                </label>

                            </div>


                            <div class="form-check form-switch">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="soloAnulados">

                                <label
                                    class="form-check-label"
                                    for="soloAnulados">

                                    Mostrar únicamente anulados

                                </label>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Botones -->

                <div class="col-12 mt-4">

                    <div class="d-grid gap-2">

                        <button
                            class="btn btn-primary"
                            id="btnAplicarFiltros">

                            <i class="bi bi-search me-2"></i>

                            Aplicar filtros

                        </button>

                        <button
                            class="btn btn-outline-secondary"
                            id="btnLimpiarFiltros">

                            <i class="bi bi-arrow-counterclockwise me-2"></i>

                            Limpiar filtros

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>
    <!--modal/-->
    <?php require "modal/modal_adm_ver_comprobantes.php" ?>
    <!-- JS -->
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/adm_comprobantes.js"></script>
    <script src="js/menu.js"></script>
    </body>

    </html>