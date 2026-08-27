<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_roles.php
// Módulo: Cargos y Roles
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";


//=====================================================
// ID USUARIO
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


//=====================================================
// VALIDACIÓN DE SESIÓN
//=====================================================

if ($idUser <= 0) {

    header("Location: login.php");

    exit;
}

?>

<?php include "includes/head.php"; ?>

<link rel="stylesheet" href="css/adm_roles.css">

<div class="d-flex">

    <?php include "includes/admin_sidebar.php"; ?>

    <div class="flex-grow-1">

        <?php include "includes/admin_navbar.php"; ?>


        <main class="container-fluid px-4 py-4">


            <!--=================================================
            CABECERA
            =================================================-->

            <div class="roles-header mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-1">

                        <i class="bi bi-person-badge-fill text-primary fs-4"></i>

                        <h2 class="fw-bold mb-0">
                            Cargos y Roles
                        </h2>

                    </div>

                    <p class="text-muted mb-0">
                        Administra los cargos y permisos de los empleados.
                    </p>

                </div>


                <div>

                    <button
                        type="button"
                        class="btn btn-primary"
                        id="btnNuevoRol">

                        <i class="bi bi-plus-circle me-1"></i>

                        Nuevo Rol

                    </button>

                </div>

            </div>



            <!--=================================================
            KPI
            =================================================-->

            <div class="row g-3 mb-4">


                <div class="col-xl-4 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="text-muted small">
                                        Total de roles
                                    </div>

                                    <div
                                        class="fs-3 fw-bold"
                                        id="kpiTotalRoles">

                                        0

                                    </div>

                                </div>

                                <div class="roles-kpi-icon">

                                    <i class="bi bi-person-badge"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <div class="col-xl-4 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="text-muted small">
                                        Roles utilizados
                                    </div>

                                    <div
                                        class="fs-3 fw-bold"
                                        id="kpiRolesUtilizados">

                                        0

                                    </div>

                                </div>

                                <div class="roles-kpi-icon">

                                    <i class="bi bi-people-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <div class="col-xl-4 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="text-muted small">
                                        Roles sin empleados
                                    </div>

                                    <div
                                        class="fs-3 fw-bold"
                                        id="kpiRolesSinEmpleados">

                                        0

                                    </div>

                                </div>

                                <div class="roles-kpi-icon">

                                    <i class="bi bi-person-x"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!--=================================================
            BUSCADOR
            =================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="row g-3 align-items-end">


                        <div class="col-lg-8">

                            <label
                                for="buscarRol"
                                class="form-label fw-semibold">

                                Buscar rol

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-search"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarRol"
                                    placeholder="Buscar por nombre del cargo o rol...">

                            </div>

                        </div>


                        <div class="col-lg-4">

                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                id="btnLimpiarBusqueda">

                                <i class="bi bi-x-circle me-1"></i>

                                Limpiar búsqueda

                            </button>

                        </div>


                    </div>

                </div>

            </div>



            <!--=================================================
            TABLA
            =================================================-->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h5 class="mb-1 fw-bold">

                                <i class="bi bi-shield-lock me-2 text-primary"></i>

                                Lista de cargos y roles

                            </h5>

                            <small class="text-muted">

                                Gestiona los permisos de acceso de cada rol.

                            </small>

                        </div>


                        <span
                            class="badge bg-primary"
                            id="contadorRoles">

                            0 roles

                        </span>

                    </div>

                </div>


                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th style="width:80px;">
                                        #
                                    </th>

                                    <th>
                                        Cargo / Rol
                                    </th>

                                    <th class="text-center">
                                        Empleados
                                    </th>

                                    <th class="text-center">
                                        Permisos
                                    </th>

                                    <th class="text-center">
                                        Acciones
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tablaRoles">

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-5">

                                        <div class="spinner-border text-primary"></div>

                                        <div class="mt-2 text-muted">
                                            Cargando roles...
                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


        </main>

    </div>

</div>



<!--=====================================================
MODAL
======================================================-->

<?php include "modal/modal_editar_roles.php"; ?>



<!--=====================================================
SCRIPTS
======================================================-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script src="js/adm_lista_roles.js"></script>

<script src="js/menu.js"></script>

</body>

</html>