<?php
//=====================================================
// CoDevPro Technology
// adm_metodos_pago.php
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
            <!--=====================================================
            =            CABECERA DEL MÓDULO
            ======================================================-->

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-credit-card-2-front-fill text-primary me-2"></i>
                        Métodos de Pago

                    </h2>

                    <nav aria-label="breadcrumb">

                        <ol class="breadcrumb mb-0">

                            <li class="breadcrumb-item">

                                <a href="admin_index.php"
                                    class="text-decoration-none">

                                    Dashboard

                                </a>

                            </li>

                            <li class="breadcrumb-item">

                                Ventas

                            </li>

                            <li class="breadcrumb-item active">

                                Métodos de Pago

                            </li>

                        </ol>

                    </nav>

                </div>


                <div class="d-flex flex-wrap gap-2">

                    <button
                        class="btn btn-primary shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalNuevoMetodo">

                        <i class="bi bi-plus-circle me-2"></i>

                        Nuevo Método

                    </button>
                </div>

            </div>



            <!--=====================================================
            =            KPI
            ======================================================-->

            <div class="row g-4 mb-4">

                <!-- TOTAL -->

                <div class="col-xl-2 col-md-4 col-sm-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">

                                        Total Métodos

                                    </small>

                                    <h3
                                        class="fw-bold mb-0 mt-2"
                                        id="kpiTotalMetodos">

                                        0

                                    </h3>

                                </div>

                                <div class="fs-1 text-primary">

                                    <i class="bi bi-credit-card-2-front-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- UTILIZADOS -->

                <div class="col-xl-2 col-md-4 col-sm-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">

                                        Métodos Utilizados

                                    </small>

                                    <h3
                                        class="fw-bold text-warning mb-0 mt-2"
                                        id="kpiUtilizados">

                                        0

                                    </h3>

                                </div>

                                <div class="fs-1 text-warning">

                                    <i class="bi bi-cart-check-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- VENTAS -->

                <div class="col-xl-2 col-md-4 col-sm-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">

                                        Total Ventas

                                    </small>

                                    <h3
                                        class="fw-bold text-info mb-0 mt-2"
                                        id="kpiVentas">

                                        0

                                    </h3>

                                </div>

                                <div class="fs-1 text-info">

                                    <i class="bi bi-receipt-cutoff"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- MONTO -->

                <div class="col-xl-2 col-md-4 col-sm-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">

                                        Monto Vendido

                                    </small>

                                    <h5
                                        class="fw-bold text-primary mb-0 mt-2"
                                        id="kpiMonto">

                                        S/ 0.00

                                    </h5>

                                </div>

                                <div class="fs-1 text-primary">

                                    <i class="bi bi-currency-dollar"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
            <!--=====================================================
            =            FILTROS AUTOMÁTICOS
            ======================================================-->

            <div class="card border-0 shadow-sm mb-2">

                <div class="card-body">

                    <div class="row g-3 align-items-end">

                        <!--=========================================
                        =            BUSCADOR
                        ==========================================-->

                        <div class="col-xl-5 col-lg-5 col-md-6">

                            <label class="form-label fw-semibold">
                                Buscar
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    id="buscarMetodo"
                                    class="form-control"
                                    placeholder="Buscar método de pago...">

                            </div>

                        </div>


                        <!--=========================================
            =            FECHA
            ==========================================-->

                        <div class="col-xl-2 col-lg-2 col-md-6">

                            <label class="form-label fw-semibold">
                                Fecha
                            </label>

                            <input
                                type="text"
                                id="rangoFecha"
                                class="form-control"
                                placeholder="Seleccionar">

                        </div>


                        <!--=========================================
            =            ORDEN
            ==========================================-->

                        <div class="col-xl-2 col-lg-2 col-md-6">

                            <label class="form-label fw-semibold">
                                Ordenar
                            </label>

                            <select
                                class="form-select"
                                id="ordenarPor">

                                <option value="nombre_asc">
                                    A-Z
                                </option>

                                <option value="nombre_desc">
                                    Z-A
                                </option>

                                <option value="ventas_desc">
                                    Más usados
                                </option>

                                <option value="ventas_asc">
                                    Menos usados
                                </option>

                            </select>

                        </div>


                        <!--=========================================
            =            BOTONES
            ==========================================-->

                        <div class="col-xl-3 col-lg-3 col-md-6">

                            <label class="form-label fw-semibold d-block">
                                Acciones
                            </label>

                            <div class="d-flex gap-2">

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary flex-fill"
                                    id="btnLimpiarFiltros">

                                    <i class="bi bi-eraser-fill me-2"></i>

                                    Limpiar

                                </button>


                                <button
                                    type="button"
                                    class="btn btn-outline-dark flex-fill"
                                    id="btnActualizar">

                                    <i class="bi bi-arrow-clockwise me-2"></i>

                                    Actualizar

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
            ```

            <!--=====================================================
            =            TABLA MÉTODOS DE PAGO
            ======================================================-->

            <div class="card border-0 shadow-sm">

                <!--=====================================
                =            HEADER CARD
                ======================================-->

                <div class="card-header bg-white border-0 py-3">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                        <div>

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-credit-card-2-front me-2 text-primary"></i>

                                Lista de Métodos de Pago

                            </h5>

                            <small class="text-muted">

                                Administra todos los métodos de pago registrados en la tienda.

                            </small>

                        </div>
                    </div>

                </div>
                <!--=====================================
                =            TABLA
                ======================================-->

                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th width="45">

                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="checkTodos">

                                </th>

                                <th width="70">

                                    #

                                </th>

                                <th>

                                    Método de Pago

                                </th>

                                <th width="140">

                                    Ventas

                                </th>

                                <th width="160">

                                    Total Vendido

                                </th>

                                <th width="140">

                                    Ticket Promedio

                                </th>

                                <th width="140">

                                    Clientes

                                </th>

                                <th width="150">

                                    Registro

                                </th>

                                <th width="210"
                                    class="text-center">

                                    Acciones

                                </th>

                            </tr>

                        </thead>

                        <tbody id="tbodyMetodosPago">

                        </tbody>

                    </table>

                </div>

            </div>
            <!--=====================================================
            =            FOOTER TABLA
            ======================================================-->

            <div class="card border-0 border-top-0 shadow-sm rounded-top-0 mb-4">

                <div class="card-body">

                    <div class="row align-items-center gy-3">

                        <!-- INFORMACIÓN -->

                        <div class="col-lg-4">

                            <div id="textoRegistros"
                                class="text-muted small">

                                Mostrando 0 a 0 de 0 registros

                            </div>

                        </div>


                        <!-- PAGINACIÓN -->

                        <div class="col-lg-8">

                            <nav class="d-flex justify-content-lg-end justify-content-center">

                                <ul class="pagination mb-0"
                                    id="paginacionMetodosPago">

                                    <!-- AJAX -->

                                </ul>

                            </nav>

                        </div>

                    </div>

                </div>

            </div>
            <!--=====================================================
            =            GRÁFICOS
            ======================================================-->
            <div class="row g-4 mb-4">

                <!--=====================================
                =            VENTAS POR MÉTODO
                ======================================-->

                <div class="col-xl-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white border-0">

                            <h5 class="fw-bold mb-0">

                                <i class="bi bi-pie-chart-fill text-primary me-2"></i>

                                Ventas por Método de Pago

                            </h5>

                        </div>

                        <div class="card-body">

                            <div style="position: relative; height: 320px;">

                                <canvas id="graficoVentasMetodo"></canvas>

                            </div>

                        </div>

                    </div>

                </div>



                <!--=====================================
                =            MONTO VENDIDO
                ======================================-->

                <div class="col-xl-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white border-0">

                            <h5 class="fw-bold mb-0">

                                <i class="bi bi-bar-chart-fill text-success me-2"></i>

                                Monto Vendido por Método

                            </h5>

                        </div>

                        <div class="card-body">

                            <div style="position: relative; height: 320px;">

                                <canvas id="graficoMontoMetodo"></canvas>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
            <!--=====================================================
            =            EVOLUCIÓN DE VENTAS
            ======================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0">

                    <div class="d-flex justify-content-between align-items-center flex-wrap">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-graph-up-arrow text-info me-2"></i>

                            Evolución de Ventas por Método

                        </h5>

                        <small class="text-muted">

                            Últimos 30 días

                        </small>

                    </div>

                </div>

                <div class="card-body">

                    <div class="card-body">

                        <div style="position: relative; height: 350px;">

                            <canvas id="graficoHistoricoMetodo"></canvas>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
<!--modal/-->
<?php include "modal/modal_adm_registrar_metodos.php"; ?>
<?php include "modal/modal_adm_editar_metodos.php"; ?>
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Idioma Español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/adm_metodos_pago.js"></script>
<script src="js/menu.js"></script>
</body>

</html>