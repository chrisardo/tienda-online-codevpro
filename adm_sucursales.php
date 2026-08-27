<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_sucursales.php
// Módulo: Sucursales
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
    <div class="container py-5">
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

        <div class="container-fluid py-4 px-4">

            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-1">

                        <div
                            class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center"
                            style="width:45px; height:45px;">

                            <i class="bi bi-building fs-4"></i>

                        </div>

                        <div>

                            <h3 class="fw-bold mb-0">
                                Sucursales
                            </h3>

                            <p class="text-muted mb-0">
                                Administra las sucursales de tu empresa
                            </p>

                        </div>

                    </div>

                </div>


                <!-- BOTÓN NUEVA SUCURSAL -->

                <div class="mt-3 mt-md-0">

                    <button
                        type="button"
                        class="btn btn-primary px-4"
                        id="btnNuevaSucursal"
                        data-bs-toggle="modal"
                        data-bs-target="#modalRegistrarSucursal">

                        <i class="bi bi-plus-circle me-2"></i>

                        Nueva Sucursal

                    </button>

                </div>

            </div>


            <!--=================================================
                KPI
            ==================================================-->

            <div class="row g-4 mb-4">

                <!-- TOTAL SUCURSALES -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center justify-content-between">

                                <div>

                                    <p class="text-muted mb-1 small">
                                        Total Sucursales
                                    </p>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiTotalSucursales">

                                        0

                                    </h3>

                                </div>

                                <div
                                    class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:50px;height:50px;">

                                    <i class="bi bi-building fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- SUCURSALES ACTIVAS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center justify-content-between">

                                <div>

                                    <p class="text-muted mb-1 small">
                                        Sucursales Activas
                                    </p>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiSucursalesActivas">

                                        0

                                    </h3>

                                </div>

                                <div
                                    class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:50px;height:50px;">

                                    <i class="bi bi-check-circle fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- SUCURSALES ELIMINADAS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center justify-content-between">

                                <div>

                                    <p class="text-muted mb-1 small">
                                        Sucursales Inactivas
                                    </p>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiSucursalesInactivas">

                                        0

                                    </h3>

                                </div>

                                <div
                                    class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:50px;height:50px;">

                                    <i class="bi bi-x-circle fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ESTADO -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center justify-content-between">

                                <div>

                                    <p class="text-muted mb-1 small">
                                        Estado General
                                    </p>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiEstadoSucursales">

                                        Activo

                                    </h3>

                                </div>

                                <div
                                    class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center"
                                    style="width:50px;height:50px;">

                                    <i class="bi bi-bar-chart-line fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                CARD PRINCIPAL
            ==================================================-->

            <div class="card border-0 shadow-sm">

                <!--=================================================
                    HEADER CARD
                ==================================================-->

                <div class="card-header bg-white border-0 py-3">

                    <div class="row g-3 align-items-center">

                        <!-- TÍTULO -->

                        <div class="col-12 col-lg-5">

                            <div class="d-flex align-items-center gap-2">

                                <i class="bi bi-list-ul text-primary fs-5"></i>

                                <div>

                                    <h5 class="fw-bold mb-0">
                                        Lista de Sucursales
                                    </h5>

                                    <small class="text-muted">
                                        Gestiona las sucursales registradas
                                    </small>

                                </div>

                            </div>

                        </div>


                        <!-- BUSCADOR -->

                        <div class="col-12 col-lg-5">

                            <div class="input-group">

                                <span class="input-group-text bg-light border-end-0">

                                    <i class="bi bi-search text-muted"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control bg-light border-start-0"
                                    id="buscarSucursal"
                                    placeholder="Buscar sucursal..."
                                    autocomplete="off">

                                <button
                                    type="button"
                                    class="btn btn-light border"
                                    id="btnLimpiarBusquedaSucursal"
                                    title="Limpiar búsqueda">

                                    <i class="bi bi-x-lg"></i>

                                </button>

                            </div>

                        </div>


                        <!-- REFRESCAR -->

                        <div class="col-12 col-lg-2 text-lg-end">

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                id="btnActualizarSucursales"
                                title="Actualizar lista">

                                <i class="bi bi-arrow-clockwise me-1"></i>

                                Actualizar

                            </button>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    BODY CARD
                ==================================================-->

                <div class="card-body p-0">

                    <!-- ALERTA -->

                    <div
                        id="alertaSucursales"
                        class="px-4 pt-3 d-none">
                    </div>


                    <!-- TABLA -->

                    <div class="table-responsive">

                        <table
                            class="table table-hover align-middle mb-0"
                            id="tablaSucursales">

                            <thead class="table-light">

                                <tr>

                                    <th
                                        class="px-4"
                                        style="width:80px;">

                                        #

                                    </th>

                                    <th>

                                        <i class="bi bi-building me-1"></i>

                                        Sucursal

                                    </th>

                                    <th
                                        class="text-center"
                                        style="width:160px;">

                                        Estado

                                    </th>

                                    <th
                                        class="text-center"
                                        style="width:220px;">

                                        Acciones

                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tbodySucursales">

                                <!--
                                Los registros serán cargados
                                mediante AJAX.
                                -->

                                <tr>

                                    <td
                                        colspan="4"
                                        class="text-center py-5">

                                        <div
                                            class="spinner-border text-primary mb-3"
                                            role="status">

                                            <span class="visually-hidden">
                                                Cargando...
                                            </span>

                                        </div>

                                        <div class="text-muted">

                                            Cargando sucursales...

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                    <!--=================================================
                        SIN REGISTROS
                    ==================================================-->

                    <div
                        id="sinSucursales"
                        class="text-center py-5 d-none">

                        <div
                            class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width:80px;height:80px;">

                            <i class="bi bi-building text-muted fs-1"></i>

                        </div>

                        <h5 class="fw-semibold">
                            No hay sucursales registradas
                        </h5>

                        <p class="text-muted mb-3">

                            Aún no tienes ninguna sucursal registrada.

                        </p>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalRegistrarSucursal">

                            <i class="bi bi-plus-circle me-2"></i>

                            Registrar primera sucursal

                        </button>

                    </div>

                </div>


                <!--=================================================
                    FOOTER CARD / PAGINACIÓN
                ==================================================-->

                <div class="card-footer bg-white border-0 py-3">

                    <div class="row align-items-center">

                        <!-- INFORMACIÓN -->

                        <div class="col-12 col-md-6 mb-3 mb-md-0">

                            <small
                                class="text-muted"
                                id="infoPaginacionSucursales">

                                Mostrando 0 sucursales

                            </small>

                        </div>


                        <!-- PAGINACIÓN -->

                        <div class="col-12 col-md-6">

                            <nav
                                aria-label="Paginación de sucursales">

                                <ul
                                    class="pagination pagination-sm justify-content-md-end justify-content-center mb-0"
                                    id="paginacionSucursales">

                                    <!--
                                    La paginación será generada
                                    mediante JavaScript.
                                    -->

                                </ul>

                            </nav>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                INFORMACIÓN
            ==================================================-->

            <div class="mt-4">

                <div
                    class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-3">

                    <i class="bi bi-info-circle-fill fs-5"></i>

                    <div>

                        <strong>
                            Administración de sucursales
                        </strong>

                        <p class="mb-0 mt-1 small">

                            Las sucursales permiten organizar el inventario
                            y gestionar los productos disponibles en cada
                            establecimiento de la empresa.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<!--=====================================================
    MODALES
======================================================-->

<?php include "modal/modal_adm_registrar_sucursal.php"; ?>

<?php include "modal/modal_adm_editar_sucursal.php"; ?>


<!--=====================================================
    SCRIPTS
======================================================-->

<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Idioma Español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- JS Sucursales -->
<script src="js/adm_sucursal.js"></script>

<!-- JS Menú -->
<script src="js/menu.js"></script>

</body>

</html>