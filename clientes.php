<?php
//=====================================================
// CoDevPro Technology
// clientes.php
//=====================================================

session_start();

if (!isset($_SESSION["idUser"])) {

    header("Location: login.php");
    exit();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"];
?>

<!DOCTYPE html>
<html lang="es">

<?php include "includes/head.php"; ?>

<body>

    <div class="d-flex">

        <?php include "includes/admin_sidebar.php"; ?>

        <div class="flex-grow-1">

            <?php include "includes/admin_navbar.php"; ?>

            <div class="container-fluid px-4 py-4">

                <!--=====================================
                CABECERA
                ======================================-->

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

                    <div>

                        <h2 class="fw-bold mb-1">

                            <i class="bi bi-people-fill text-primary me-2"></i>

                            Clientes

                        </h2>

                        <p class="text-muted mb-0">

                            Gestiona y administra todos los clientes registrados.

                        </p>

                    </div>

                    <div class="d-flex gap-2">

                        <button
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalNuevoCliente">

                            <i class="bi bi-person-plus-fill me-2"></i>

                            Nuevo Cliente

                        </button>

                        <button
                            class="btn btn-success"
                            onclick="exportarExcelClientes()">

                            <i class="bi bi-file-earmark-excel-fill me-2"></i>

                            Excel

                        </button>

                    </div>

                </div>

                <!--=====================================
                KPI CARDS
                ======================================-->

                <div class="row g-3 mb-4">

                    <div class="col-xl-3 col-md-6">

                        <div class="card border-0 shadow-sm">

                            <div class="card-body">

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <small class="text-muted">

                                            Total Clientes

                                        </small>

                                        <h3
                                            class="fw-bold mt-2 mb-0"
                                            id="kpiTotalClientes">

                                            0

                                        </h3>

                                    </div>

                                    <div class="icon-kpi bg-primary-subtle">

                                        <i class="bi bi-people-fill text-primary"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-xl-3 col-md-6">

                        <div class="card border-0 shadow-sm">

                            <div class="card-body">

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <small class="text-muted">

                                            Clientes Activos

                                        </small>

                                        <h3
                                            class="fw-bold mt-2 mb-0"
                                            id="kpiClientesActivos">

                                            0

                                        </h3>

                                    </div>

                                    <div class="icon-kpi bg-success-subtle">

                                        <i class="bi bi-check-circle-fill text-success"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-xl-3 col-md-6">

                        <div class="card border-0 shadow-sm">

                            <div class="card-body">

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <small class="text-muted">

                                            Clientes con Pedidos

                                        </small>

                                        <h3
                                            class="fw-bold mt-2 mb-0"
                                            id="kpiClientesPedidos">

                                            0

                                        </h3>

                                    </div>

                                    <div class="icon-kpi bg-warning-subtle">

                                        <i class="bi bi-bag-check-fill text-warning"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-xl-3 col-md-6">

                        <div class="card border-0 shadow-sm">

                            <div class="card-body">

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <small class="text-muted">

                                            Cliente Top

                                        </small>

                                        <h6
                                            class="fw-bold mt-2 mb-0"
                                            id="kpiClienteTop">

                                            -

                                        </h6>

                                    </div>

                                    <div class="icon-kpi bg-danger-subtle">

                                        <i class="bi bi-trophy-fill text-danger"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!--=====================================
                FILTROS
                ======================================-->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-lg-4">

                                <div class="input-group">

                                    <span class="input-group-text">

                                        <i class="bi bi-search"></i>

                                    </span>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="buscarCliente"
                                        placeholder="Buscar cliente...">

                                </div>

                            </div>

                            <div class="col-lg-2">

                                <select
                                    class="form-select"
                                    id="filtroEstadoCliente">

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

                            <div class="col-lg-2">

                                <select
                                    class="form-select"
                                    id="filtroPaisCliente">

                                    <option value="">
                                        País
                                    </option>

                                </select>

                            </div>

                            <div class="col-lg-2">

                                <select
                                    class="form-select"
                                    id="filtroRubroCliente">

                                    <option value="">
                                        Rubro
                                    </option>

                                </select>

                            </div>

                            <div class="col-lg-2">

                                <select
                                    class="form-select"
                                    id="ordenarCliente">

                                    <option value="nombre_asc">
                                        Nombre A-Z
                                    </option>

                                    <option value="nombre_desc">
                                        Nombre Z-A
                                    </option>

                                    <option value="compras_desc">
                                        Más compras
                                    </option>

                                    <option value="pedidos_desc">
                                        Más pedidos
                                    </option>

                                    <option value="fecha_desc">
                                        Más recientes
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>

                </div>

                <!--=====================================
                TABLA
                ======================================-->

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <div class="d-flex justify-content-between align-items-center">

                            <h5 class="mb-0">

                                <i class="bi bi-table me-2"></i>

                                Lista de Clientes

                            </h5>

                            <div class="d-flex align-items-center gap-3">

                                <div class="btn-group">

                                    <button
                                        class="btn btn-outline-primary active"
                                        id="btnVistaLista">

                                        <i class="bi bi-list-ul"></i>

                                    </button>

                                    <button
                                        class="btn btn-outline-primary"
                                        id="btnVistaTarjeta">

                                        <i class="bi bi-grid-3x3-gap"></i>

                                    </button>

                                </div>

                                <span
                                    class="badge bg-primary"
                                    id="totalRegistrosClientes">

                                    0 registros

                                </span>

                            </div>

                        </div>

                    </div>

                    <div class="card-body p-0">

                        <!-- TABLA -->

                        <div
                            class="table-responsive"
                            id="vistaListaClientes">

                            <div id="contenedorTablaClientes">

                                <div class="text-center py-5">

                                    <div class="spinner-border text-primary"></div>

                                    <div class="mt-3">

                                        Cargando clientes...

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- TARJETAS -->

                        <div
                            class="p-3 d-none"
                            id="vistaTarjetasClientes">

                            <div
                                class="row g-3"
                                id="contenedorTarjetasClientes">

                            </div>

                        </div>

                    </div>

                </div>

                <!--=====================================
                PAGINACION
                ======================================-->

                <div
                    id="contenedorPaginacionClientes"
                    class="mt-3">

                </div>

            </div>

        </div>

    </div>

    <!--=====================================
    MODALES
    ======================================-->

    <?php include "modal/modal_nuevo_cliente.php"; ?>

    <?php include "modal/modal_ver_cliente.php"; ?>

    <?php include "modal/modal_editar_cliente.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="js/clientes.js"></script>

    <script src="js/menu.js"></script>

</body>

</html>