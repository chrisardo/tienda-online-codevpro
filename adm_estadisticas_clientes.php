<?php
//=====================================================
// CoDevPro Technology
// adm_estadisticas_clientes.php
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
        <!-- CONTENIDO -->
        <div class="container-fluid py-4 px-4">

            <!--=====================================
    =            CABECERA
    ======================================-->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-graph-up-arrow text-primary"></i>

                        Estadísticas de Clientes

                    </h2>

                    <p class="text-muted mb-0">

                        Analiza el comportamiento, compras y fidelización de tus clientes.

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <button class="btn btn-success"
                        id="btnExportarClientesExcel">

                        <i class="bi bi-file-earmark-excel"></i>
                        Excel

                    </button>

                    <button class="btn btn-danger"
                        id="btnExportarClientesPDF">

                        <i class="bi bi-file-earmark-pdf"></i>
                        PDF

                    </button>

                </div>

            </div>

            <!--=====================================
    =            KPIs PRINCIPALES
    ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Total Clientes

                            </small>

                            <h3 class="fw-bold mb-0"
                                id="kpiTotalClientes">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Clientes Activos

                            </small>

                            <h3 class="fw-bold text-success mb-0"
                                id="kpiClientesActivos">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Clientes Inactivos

                            </small>

                            <h3 class="fw-bold text-danger mb-0"
                                id="kpiClientesInactivos">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Nuevos Este Mes

                            </small>

                            <h3 class="fw-bold text-primary mb-0"
                                id="kpiNuevosMes">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

            </div>

            <!-- FILA 2 -->

            <div class="row g-4 mb-4">

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

                                Clientes VIP

                            </small>

                            <h3 class="fw-bold text-info mb-0"
                                id="kpiVip">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Valor Promedio Cliente

                            </small>

                            <h3 class="fw-bold text-success mb-0"
                                id="kpiValorCliente">

                                S/ 0.00

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Conversión Clientes

                            </small>

                            <h3 class="fw-bold text-primary mb-0"
                                id="kpiConversionClientes">

                                0%

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

                                <i class="bi bi-graph-up"></i>

                                Evolución de Clientes

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas id="graficoClientesMes"></canvas>

                        </div>

                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-pie-chart-fill"></i>

                                Segmentación

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas id="graficoSegmentacion"></canvas>

                        </div>

                    </div>

                </div>

            </div>

            <!--=====================================
            =            TOP CLIENTES
            ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-lg-6">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-trophy-fill text-warning"></i>

                                Top Clientes Compradores

                            </h5>

                        </div>

                        <div class="card-body">

                            <div id="topClientesCompradores">

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-person-plus-fill text-success"></i>

                                Clientes Recientes

                            </h5>

                        </div>

                        <div class="card-body">

                            <div id="clientesRecientes">

                            </div>

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

                        <div class="col-lg-3">

                            <label class="form-label">

                                Buscar

                            </label>

                            <input type="text"
                                class="form-control"
                                id="buscarClienteStats"
                                placeholder="Nombre, email o DNI">

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Estado

                            </label>

                            <select class="form-select"
                                id="filtroEstado">

                                <option value="">
                                    Todos
                                </option>

                                <option value="ACTIVO">
                                    Activo
                                </option>

                                <option value="INACTIVO">
                                    Inactivo
                                </option>

                            </select>

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Departamento

                            </label>

                            <select class="form-select"
                                id="filtroDepartamento">

                                <option value="">
                                    Todos
                                </option>

                            </select>

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Fecha Inicio

                            </label>

                            <input type="text"
                                id="fechaInicio"
                                class="form-control">

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Fecha Fin

                            </label>

                            <input type="text"
                                id="fechaFin"
                                class="form-control">

                        </div>

                        <div class="col-lg-1 d-grid">

                            <label class="form-label">

                                &nbsp;

                            </label>

                            <button class="btn btn-secondary"
                                id="btnResetFiltros">

                                <i class="bi bi-arrow-clockwise"></i>

                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <!--=====================================
            =            TABLA CLIENTES
            ======================================-->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="mb-0">

                        <i class="bi bi-table"></i>

                        Clientes Registrados

                    </h5>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>DNI/RUC</th>
                                    <th>Email</th>
                                    <th>Celular</th>
                                    <th>Departamento</th>
                                    <th>Pedidos</th>
                                    <th>Total Comprado</th>
                                    <th>Última Compra</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>

                                </tr>

                            </thead>

                            <tbody id="tablaEstadisticasClientes">

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!-- PAGINACION -->

            <div class="mt-4"
                id="paginacionClientes">

            </div>

        </div>
    </div>
</div>
<!--Modal: modal/-->
<?php require "modal/modal_detalle_cliente.php";?>
<!-- JS -->
<!-- Bootstrap -->
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/adm_estadistica_cliente.js"></script>
<script src="js/menu.js"></script>
</body>

</html>