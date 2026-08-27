<?php
//=====================================================
// CoDevPro Technology
// admin_ventas.php
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
        <!--=====================================
=            CONTENIDO
======================================-->

        <div class="container-fluid py-4 px-4">

            <!--=====================================
    =            CABECERA
    ======================================-->

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-cart-check-fill text-primary"></i>

                        Gestión de Ventas

                    </h2>

                    <p class="text-muted mb-0">

                        Administra todas las ventas, pedidos, comprobantes y estados de entrega.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <button class="btn btn-success"
                        id="btnExportarVentasExcel">

                        <i class="bi bi-file-earmark-excel"></i>

                        Excel

                    </button>

                    <button class="btn btn-danger"
                        id="btnExportarVentasPDF">

                        <i class="bi bi-file-earmark-pdf"></i>

                        PDF

                    </button>

                </div>

            </div>


            <!--=====================================
    =            KPIs
    ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Ventas Hoy

                            </small>

                            <h3 class="fw-bold text-success mb-0"
                                id="kpiVentasHoy">

                                S/ 0.00

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Ventas del Mes

                            </small>

                            <h3 class="fw-bold text-primary mb-0"
                                id="kpiVentasMes">

                                S/ 0.00

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Ticket Promedio

                            </small>

                            <h3 class="fw-bold text-warning mb-0"
                                id="kpiTicketPromedio">

                                S/ 0.00

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Pedidos Pendientes

                            </small>

                            <h3 class="fw-bold text-danger mb-0"
                                id="kpiPedidosPendientes">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

            </div>


            <!-- FILA 2 KPI -->

            <div class="row g-4 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Total Ventas

                            </small>

                            <h3 class="fw-bold mb-0"
                                id="kpiTotalVentas">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Ventas Online

                            </small>

                            <h3 class="fw-bold text-info mb-0"
                                id="kpiVentasOnline">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Entregados

                            </small>

                            <h3 class="fw-bold text-success mb-0"
                                id="kpiEntregados">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Cancelados

                            </small>

                            <h3 class="fw-bold text-danger mb-0"
                                id="kpiCancelados">

                                0

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

                                <i class="bi bi-graph-up-arrow"></i>

                                Evolución de Ventas

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas id="graficoVentasMes"></canvas>

                        </div>

                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-pie-chart-fill"></i>

                                Métodos de Pago

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas id="graficoMetodoPago"></canvas>

                        </div>

                    </div>

                </div>

            </div>


            <!-- GRAFICO ESTADO ENVIO -->

            <div class="row g-4 mb-4">

                <div class="col-lg-12">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-truck"></i>

                                Estado de Envíos

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas id="graficoEstadoEnvio"></canvas>

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

                        <!-- BUSCADOR -->

                        <div class="col-xl-3 col-lg-4">

                            <label class="form-label">

                                Buscar Venta

                            </label>

                            <input type="text"
                                class="form-control"
                                id="buscarVenta"
                                placeholder="Cliente, serie o comprobante">

                        </div>

                        <!-- ESTADO VENTA -->

                        <div class="col-xl-2 col-lg-4">

                            <label class="form-label">

                                Estado Venta

                            </label>

                            <select class="form-select"
                                id="filtroEstadoVenta">

                                <option value="">
                                    Todos
                                </option>

                                <option value="PAGADO">
                                    Pagado
                                </option>

                                <option value="PENDIENTE">
                                    Pendiente
                                </option>

                                <option value="ANULADO">
                                    Anulado
                                </option>

                            </select>

                        </div>

                        <!-- ESTADO ENVIO -->

                        <div class="col-xl-2 col-lg-4">

                            <label class="form-label">

                                Estado Envío

                            </label>

                            <select class="form-select"
                                id="filtroEstadoEnvio">

                                <option value="">
                                    Todos
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

                                <option value="ENVIADO">
                                    Enviado
                                </option>

                                <option value="ENTREGADO">
                                    Entregado
                                </option>

                                <option value="CANCELADO">
                                    Cancelado
                                </option>

                            </select>

                        </div>

                        <!-- METODO PAGO -->

                        <div class="col-xl-2 col-lg-4">

                            <label class="form-label">

                                Método Pago

                            </label>

                            <select class="form-select"
                                id="filtroMetodoPago">

                                <option value="">
                                    Todos
                                </option>

                            </select>

                        </div>

                        <!-- EMPLEADO -->

                        <div class="col-xl-2 col-lg-4">

                            <label class="form-label">

                                Empleado

                            </label>

                            <select class="form-select"
                                id="filtroEmpleado">

                                <option value="">
                                    Todos
                                </option>

                            </select>

                        </div>

                        <!-- RESET -->

                        <div class="col-xl-1 col-lg-4 d-grid">

                            <label class="form-label">

                                &nbsp;

                            </label>

                            <button class="btn btn-secondary"
                                id="btnResetFiltrosVentas">

                                <i class="bi bi-arrow-clockwise"></i>

                            </button>

                        </div>

                    </div>


                    <!-- FILA FECHAS -->

                    <div class="row g-3 mt-1">

                        <div class="col-xl-3">

                            <label class="form-label">

                                Fecha Inicio

                            </label>

                            <input type="text"
                                id="fechaInicioVenta"
                                class="form-control">

                        </div>

                        <div class="col-xl-3">

                            <label class="form-label">

                                Fecha Fin

                            </label>

                            <input type="text"
                                id="fechaFinVenta"
                                class="form-control">

                        </div>

                        <div class="col-xl-2">

                            <label class="form-label">

                                Mostrar

                            </label>

                            <select class="form-select"
                                id="limiteVentas">

                                <option value="10">10</option>

                                <option value="25">25</option>

                                <option value="50">50</option>

                                <option value="100">100</option>

                            </select>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================
    =            TABLA VENTAS
    ======================================-->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">

                        <i class="bi bi-receipt-cutoff"></i>

                        Historial de Ventas

                    </h5>

                    <span class="badge bg-primary"
                        id="totalVentasEncontradas">

                        0 registros

                    </span>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>#</th>

                                    <th>Comprobante</th>

                                    <th>Cliente</th>

                                    <th>Fecha</th>

                                    <th>Método Pago</th>

                                    <th>Empleado</th>

                                    <th>Estado Venta</th>

                                    <th>Estado Envío</th>

                                    <th>Total</th>

                                    <th class="text-center">

                                        Acciones

                                    </th>

                                </tr>

                            </thead>

                            <tbody id="tablaVentas">

                                <tr>

                                    <td colspan="10"
                                        class="text-center py-5">

                                        <div class="spinner-border text-primary"></div>

                                        <p class="mt-3 mb-0">

                                            Cargando ventas...

                                        </p>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <!--=====================================
    =            PAGINACION
    ======================================-->

            <div class="row mt-4 align-items-center">

                <div class="col-md-6">

                    <small class="text-muted"
                        id="infoPaginacionVentas">

                        Mostrando 0 registros

                    </small>

                </div>

                <div class="col-md-6">

                    <div id="paginacionVentas">

                    </div>

                </div>

            </div>
        </div>
    </div>
    <?php require "modal/modal_adm_ver_ventas.php" ?>
    <?php require "modal/modal_adm_exportar_ventas.php" ?>
    <?php require "modal/modal_adm_exportar_ventas_pdf.php" ?>
    <!-- JS -->
    <!-- Bootstrap -->
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/adm_ventas.js"></script>
    <script src="js/menu.js"></script>
    </body>

    </html>