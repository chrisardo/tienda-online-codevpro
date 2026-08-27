<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_deposito_gasto.php
// Módulo: Ingresos y Gastos
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

        <main class="container-fluid py-4 px-4 deposito-gasto-page">


            <!--=================================================
                ENCABEZADO DE LA PÁGINA
            ==================================================-->

            <div class="deposito-gasto-header mb-4">

                <div class="d-flex flex-column flex-lg-row
                    justify-content-between align-items-lg-center gap-3">

                    <div>

                        <div class="page-breadcrumb">

                            <span>
                                Contabilidad
                            </span>

                            <i class="bi bi-chevron-right"></i>

                            <span class="active">
                                Ingresos y Gastos
                            </span>

                        </div>


                        <h1 class="deposito-gasto-title">

                            <i class="bi bi-arrow-left-right"></i>

                            Ingresos y Gastos

                        </h1>


                        <p class="deposito-gasto-description mb-0">

                            Administra y controla los movimientos de ingresos
                            y gastos de tu empresa.

                        </p>

                    </div>


                    <!-- BOTÓN NUEVO MOVIMIENTO -->

                    <div>

                        <button
                            type="button"
                            class="btn btn-primary btn-nuevo-movimiento"
                            data-bs-toggle="modal"
                            data-bs-target="#modalNuevoMovimiento">

                            <i class="bi bi-plus-lg me-2"></i>

                            Nuevo movimiento

                        </button>

                    </div>

                </div>

            </div>


            <!--=================================================
                TARJETAS RESUMEN
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- TOTAL INGRESOS -->

                <div class="col-xl-3 col-md-6">

                    <div class="card resumen-card resumen-ingresos">

                        <div class="card-body">

                            <div class="resumen-card-content">

                                <div>

                                    <span class="resumen-label">

                                        Total ingresos

                                    </span>

                                    <h3 class="resumen-value">

                                        S/ 0.00

                                    </h3>

                                    <small class="resumen-description">

                                        Ingresos registrados

                                    </small>

                                </div>


                                <div class="resumen-icon">

                                    <i class="bi bi-arrow-down-left"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- TOTAL GASTOS -->

                <div class="col-xl-3 col-md-6">

                    <div class="card resumen-card resumen-gastos">

                        <div class="card-body">

                            <div class="resumen-card-content">

                                <div>

                                    <span class="resumen-label">

                                        Total gastos

                                    </span>

                                    <h3 class="resumen-value">

                                        S/ 0.00

                                    </h3>

                                    <small class="resumen-description">

                                        Gastos registrados

                                    </small>

                                </div>


                                <div class="resumen-icon">

                                    <i class="bi bi-arrow-up-right"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- BALANCE -->

                <div class="col-xl-3 col-md-6">

                    <div class="card resumen-card resumen-balance">

                        <div class="card-body">

                            <div class="resumen-card-content">

                                <div>

                                    <span class="resumen-label">

                                        Balance

                                    </span>

                                    <h3 class="resumen-value">

                                        S/ 0.00

                                    </h3>

                                    <small class="resumen-description">

                                        Ingresos - gastos

                                    </small>

                                </div>


                                <div class="resumen-icon">

                                    <i class="bi bi-wallet2"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- MOVIMIENTOS -->

                <div class="col-xl-3 col-md-6">

                    <div class="card resumen-card resumen-movimientos">

                        <div class="card-body">

                            <div class="resumen-card-content">

                                <div>

                                    <span class="resumen-label">

                                        Movimientos

                                    </span>

                                    <h3 class="resumen-value">

                                        0

                                    </h3>

                                    <small class="resumen-description">

                                        Registros encontrados

                                    </small>

                                </div>


                                <div class="resumen-icon">

                                    <i class="bi bi-list-ul"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!--=================================================
                FILTROS
            ==================================================-->

            <div class="card filtro-card mb-4">

                <div class="card-body">

                    <div class="filtro-header">

                        <div>

                            <h5 class="filtro-title">

                                <i class="bi bi-funnel me-2"></i>

                                Filtrar movimientos

                            </h5>

                            <p class="filtro-description mb-0">

                                Utiliza los filtros para consultar
                                tus movimientos contables.

                            </p>

                        </div>


                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            id="btnLimpiarFiltros">

                            <i class="bi bi-arrow-counterclockwise me-1"></i>

                            Limpiar filtros

                        </button>

                    </div>


                    <div class="row g-3 mt-1">


                        <!-- TIPO -->

                        <div class="col-xl-2 col-lg-3 col-md-6">

                            <label
                                for="filtroTipo"
                                class="form-label">

                                Tipo

                            </label>

                            <select
                                class="form-select"
                                id="filtroTipo">

                                <option value="">
                                    Todos
                                </option>

                                <option value="INGRESO">
                                    Ingresos
                                </option>

                                <option value="GASTO">
                                    Gastos
                                </option>

                            </select>

                        </div>


                        <!-- CUENTA BANCARIA -->

                        <div class="col-xl-2 col-lg-3 col-md-6">

                            <label
                                for="filtroCuenta"
                                class="form-label">

                                Cuenta bancaria

                            </label>

                            <select
                                class="form-select"
                                id="filtroCuenta">

                                <option value="">
                                    Todas las cuentas
                                </option>

                            </select>

                        </div>


                        <!-- CATEGORÍA -->

                        <div class="col-xl-2 col-lg-3 col-md-6">

                            <label
                                for="filtroCategoria"
                                class="form-label">

                                Categoría

                            </label>

                            <select
                                class="form-select"
                                id="filtroCategoria">

                                <option value="">
                                    Todas las categorías
                                </option>

                            </select>

                        </div>


                        <!-- MÉTODO DE PAGO -->

                        <div class="col-xl-2 col-lg-3 col-md-6">

                            <label
                                for="filtroMetodoPago"
                                class="form-label">

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


                        <!-- FECHA DESDE -->

                        <div class="col-xl-2 col-lg-3 col-md-6">

                            <label
                                for="filtroFechaDesde"
                                class="form-label">

                                Desde

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="filtroFechaDesde"
                                    placeholder="dd/mm/aaaa">

                            </div>

                        </div>


                        <!-- FECHA HASTA -->

                        <div class="col-xl-2 col-lg-3 col-md-6">

                            <label
                                for="filtroFechaHasta"
                                class="form-label">

                                Hasta

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="filtroFechaHasta"
                                    placeholder="dd/mm/aaaa">

                            </div>

                        </div>


                        <!-- BUSCAR -->

                        <div class="col-xl-6 col-lg-6 col-md-12">

                            <label
                                for="filtroBusqueda"
                                class="form-label">

                                Buscar

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-search"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="filtroBusqueda"
                                    placeholder="Buscar por concepto o descripción...">

                            </div>

                        </div>


                        <!-- PROVEEDOR -->

                        <div class="col-xl-3 col-lg-3 col-md-6">

                            <label
                                for="filtroProveedor"
                                class="form-label">

                                Proveedor

                            </label>

                            <select
                                class="form-select"
                                id="filtroProveedor">

                                <option value="">
                                    Todos los proveedores
                                </option>

                            </select>

                        </div>


                        <!-- BOTÓN BUSCAR -->

                        <div class="col-xl-3 col-lg-3 col-md-6 d-flex align-items-end">

                            <button
                                type="button"
                                class="btn btn-primary w-100 btn-aplicar-filtros"
                                id="btnAplicarFiltros">

                                <i class="bi bi-search me-2"></i>

                                Aplicar filtros

                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                TABLA DE MOVIMIENTOS
            ==================================================-->

            <div class="card movimientos-card">


                <!-- CABECERA -->

                <div class="card-header movimientos-card-header">

                    <div>

                        <h5 class="movimientos-title">

                            <i class="bi bi-arrow-left-right me-2"></i>

                            Movimientos

                        </h5>

                        <span class="movimientos-subtitle">

                            Registro de ingresos y gastos

                        </span>

                    </div>


                    <div class="movimientos-actions">

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            title="Actualizar"
                            id="btnActualizarMovimientos">

                            <i class="bi bi-arrow-clockwise"></i>

                        </button>

                    </div>

                </div>


                <!-- TABLA -->

                <div class="table-responsive">

                    <table
                        class="table movimientos-table align-middle mb-0">

                        <thead>

                            <tr>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Tipo
                                </th>

                                <th>
                                    Concepto
                                </th>

                                <th>
                                    Cuenta bancaria
                                </th>

                                <th>
                                    Categoría
                                </th>

                                <th>
                                    Proveedor
                                </th>

                                <th>
                                    Método de pago
                                </th>

                                <th class="text-end">
                                    Monto
                                </th>

                                <th class="text-center">
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody id="tablaMovimientos">

                            <!-- ESTADO VACÍO -->

                            <tr id="estadoVacioMovimientos">

                                <td
                                    colspan="9"
                                    class="text-center">

                                    <div class="empty-state">

                                        <div class="empty-state-icon">

                                            <i class="bi bi-arrow-left-right"></i>

                                        </div>

                                        <h6>
                                            No hay movimientos registrados
                                        </h6>

                                        <p>
                                            Todavía no existen ingresos
                                            o gastos para mostrar.
                                        </p>

                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalNuevoMovimiento">

                                            <i class="bi bi-plus-lg me-1"></i>

                                            Registrar movimiento

                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- PAGINACIÓN -->

                <div class="movimientos-footer">

                    <div class="movimientos-info">

                        Mostrando
                        <strong>0</strong>
                        movimientos

                    </div>


                    <nav aria-label="Paginación de movimientos">

                        <ul class="pagination pagination-sm mb-0">

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

                            <li class="page-item disabled">

                                <a
                                    class="page-link"
                                    href="#">

                                    <i class="bi bi-chevron-right"></i>

                                </a>

                            </li>

                        </ul>

                    </nav>

                </div>

            </div>


        </main>

    </div>

</div>




<!--=====================================================
    MODALES
======================================================-->

<?php include "modal/modal_adm_registrar_movimiento.php"; ?>

<?php include "modal/modal_adm_editar_movimiento.php"; ?>
<?php include "modal/modal_adm_eliminar_movimineto.php"; ?>

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

<script src="js/adm_deposito_gasto.js"></script>

<script src="js/menu.js"></script>


</body>

</html>