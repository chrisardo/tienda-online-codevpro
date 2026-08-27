<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_lista_empleados.php
// Módulo: Lista de Empleados
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "controladores/conexion.php";
/*
|--------------------------------------------------------------------------
| ID DEL USUARIO ADMINISTRADOR
|--------------------------------------------------------------------------
| Se obtiene el usuario que inició sesión.
*/
$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;
/*
|--------------------------------------------------------------------------
| VALIDACIÓN DE SESIÓN
|--------------------------------------------------------------------------
*/
if ($idUser <= 0) {
    header("Location: login.php");
    exit;
}

?>

<?php include "includes/head.php"; ?>

<div class="d-flex">

    <?php include "includes/admin_sidebar.php"; ?>

    <div class="flex-grow-1">

        <?php include "includes/admin_navbar.php"; ?>

        <main class="container-fluid px-4 py-4">

            <!--=====================================================
            CABECERA
            ======================================================-->

            <div class="d-flex flex-column flex-lg-row
                        align-items-lg-center
                        justify-content-between
                        gap-3
                        mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-1">

                        <i class="bi bi-person-workspace
                                  text-primary
                                  fs-3"></i>

                        <h1 class="h3 fw-bold mb-0">
                            Lista de Empleados
                        </h1>

                    </div>

                    <p class="text-muted mb-0">
                        Administra, consulta y controla los empleados
                        registrados en la tienda.
                    </p>

                </div>

                <div class="d-flex align-items-center gap-2">

                    <!--=================================================
    EXPORTAR DATOS
    ==================================================-->

                    <button
                        type="button"
                        class="btn btn-outline-success"
                        data-bs-toggle="modal"
                        data-bs-target="#modalExportarDatosEmpleado">

                        <i class="bi bi-download me-2"></i>

                        Exportar datos

                    </button>


                    <!--=================================================
    REGISTRAR EMPLEADO
    ==================================================-->

                    <a
                        href="adm_registrar_empleado.php"
                        class="btn btn-primary">

                        <i class="bi bi-person-plus-fill me-2"></i>

                        Registrar Empleado

                    </a>

                </div>

            </div>


            <!--=====================================================
            KPI CARDS
            ======================================================-->

            <div class="row g-3 mb-4">

                <!-- TOTAL EMPLEADOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex
                                        align-items-center
                                        justify-content-between">

                                <div>

                                    <p class="text-muted
                                              small
                                              mb-1">

                                        Total Empleados

                                    </p>

                                    <h3 class="fw-bold mb-0"
                                        id="totalEmpleados">

                                        0

                                    </h3>

                                </div>

                                <div class="bg-primary
                                            bg-opacity-10
                                            text-primary
                                            rounded-3
                                            p-3">

                                    <i class="bi bi-people-fill
                                              fs-4"></i>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>


                <!-- ACTIVOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex
                                        align-items-center
                                        justify-content-between">

                                <div>

                                    <p class="text-muted
                                              small
                                              mb-1">

                                        Empleados Activos

                                    </p>

                                    <h3 class="fw-bold mb-0"
                                        id="empleadosActivos">

                                        0

                                    </h3>

                                </div>

                                <div class="bg-success
                                            bg-opacity-10
                                            text-success
                                            rounded-3
                                            p-3">

                                    <i class="bi bi-person-check-fill
                                              fs-4"></i>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>


                <!-- INACTIVOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex
                                        align-items-center
                                        justify-content-between">

                                <div>

                                    <p class="text-muted
                                              small
                                              mb-1">

                                        Empleados Inactivos

                                    </p>

                                    <h3 class="fw-bold mb-0"
                                        id="empleadosInactivos">

                                        0

                                    </h3>

                                </div>

                                <div class="bg-danger
                                            bg-opacity-10
                                            text-danger
                                            rounded-3
                                            p-3">

                                    <i class="bi bi-person-x-fill
                                              fs-4"></i>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>


                <!-- ROLES -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex
                                        align-items-center
                                        justify-content-between">

                                <div>

                                    <p class="text-muted
                                              small
                                              mb-1">

                                        Roles Asignados

                                    </p>

                                    <h3 class="fw-bold mb-0"
                                        id="totalRoles">

                                        0

                                    </h3>

                                </div>

                                <div class="bg-warning
                                            bg-opacity-10
                                            text-warning
                                            rounded-3
                                            p-3">

                                    <i class="bi bi-person-badge-fill
                                              fs-4"></i>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================================
            BUSQUEDA Y FILTROS
            ======================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="row g-3 align-items-end">

                        <!-- BUSCADOR -->

                        <div class="col-12 col-lg-4">

                            <label for="buscarEmpleado"
                                class="form-label fw-semibold">

                                Buscar empleado

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-search"></i>

                                </span>

                                <input type="text"
                                    id="buscarEmpleado"
                                    class="form-control"
                                    placeholder="Nombre, DNI, correo o celular...">

                            </div>

                        </div>


                        <!-- ESTADO -->

                        <div class="col-12 col-sm-6 col-lg-2">

                            <label for="filtroEstadoEmpleado"
                                class="form-label fw-semibold">

                                Estado

                            </label>

                            <select id="filtroEstadoEmpleado"
                                class="form-select">

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


                        <!-- ROL -->

                        <div class="col-12 col-sm-6 col-lg-2">

                            <label for="filtroRolEmpleado"
                                class="form-label fw-semibold">

                                Rol

                            </label>

                            <select id="filtroRolEmpleado"
                                class="form-select">

                                <option value="">
                                    Todos los roles
                                </option>

                            </select>

                        </div>


                        <!-- FECHA DESDE -->

                        <div class="col-12 col-sm-6 col-lg-2">

                            <label for="fechaDesdeEmpleado"
                                class="form-label fw-semibold">

                                Desde

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input type="text"
                                    id="fechaDesdeEmpleado"
                                    class="form-control"
                                    placeholder="Fecha">

                            </div>

                        </div>


                        <!-- FECHA HASTA -->

                        <div class="col-12 col-sm-6 col-lg-2">

                            <label for="fechaHastaEmpleado"
                                class="form-label fw-semibold">

                                Hasta

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input type="text"
                                    id="fechaHastaEmpleado"
                                    class="form-control"
                                    placeholder="Fecha">

                            </div>

                        </div>


                        <!-- LIMPIAR FILTROS -->

                        <div class="col-12">

                            <div class="d-flex
                                        justify-content-end
                                        gap-2">

                                <button type="button"
                                    id="btnLimpiarFiltros"
                                    class="btn btn-outline-secondary">

                                    <i class="bi bi-arrow-counterclockwise me-1"></i>

                                    Limpiar filtros

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================================
            TABLA
            ======================================================-->

            <div class="card border-0 shadow-sm">

                <div class="card-header
                            bg-white
                            border-0
                            py-3">

                    <div class="d-flex
                                flex-column
                                flex-md-row
                                align-items-md-center
                                justify-content-between
                                gap-3">

                        <div>

                            <h5 class="fw-bold mb-1">

                                <i class="bi bi-people-fill
                                          text-primary
                                          me-2"></i>

                                Empleados registrados

                            </h5>

                            <small class="text-muted">

                                Gestiona los empleados de tu tienda.

                            </small>

                        </div>
                    </div>
                </div>
                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table
                                      table-hover
                                      align-middle
                                      mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th class="px-4"
                                        style="width: 60px;">

                                        #

                                    </th>

                                    <th>
                                        Empleado
                                    </th>

                                    <th>
                                        DNI
                                    </th>

                                    <th>
                                        Contacto
                                    </th>

                                    <th>
                                        Rol
                                    </th>

                                    <th>
                                        Registro
                                    </th>

                                    <th>
                                        Estado
                                    </th>

                                    <th class="text-center"
                                        style="width: 100px;">

                                        Acciones

                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tablaEmpleados">

                                <!--
                                Los empleados serán cargados
                                mediante AJAX en la siguiente parte.
                                -->

                                <tr>

                                    <td colspan="8"
                                        class="text-center py-5">

                                        <div class="text-muted">

                                            <i class="bi bi-people fs-1 d-block mb-2"></i>

                                            <h6 class="fw-semibold">

                                                No hay empleados para mostrar

                                            </h6>

                                            <p class="small mb-0">

                                                Los empleados registrados
                                                aparecerán aquí.

                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!--=================================================
                PIE DE TABLA
                ==================================================-->

                <div class="card-footer
                            bg-white
                            border-0
                            py-3">

                    <div class="d-flex
                                flex-column
                                flex-md-row
                                align-items-center
                                justify-content-between
                                gap-3">

                        <div class="text-muted small">

                            Mostrando
                            <strong id="rangoInicioEmpleados">
                                0
                            </strong>
                            -
                            <strong id="rangoFinEmpleados">
                                0
                            </strong>
                            de
                            <strong id="totalRegistrosEmpleados">
                                0
                            </strong>
                            empleados

                        </div>


                        <!-- PAGINACIÓN -->

                        <nav aria-label="Paginación de empleados">

                            <ul class="pagination
                                       pagination-sm
                                       mb-0"
                                id="paginacionEmpleados">

                                <li class="page-item disabled">

                                    <a class="page-link"
                                        href="#"
                                        aria-label="Anterior">

                                        <i class="bi bi-chevron-left"></i>

                                    </a>

                                </li>

                                <li class="page-item active">

                                    <a class="page-link"
                                        href="#">

                                        1

                                    </a>

                                </li>

                                <li class="page-item disabled">

                                    <a class="page-link"
                                        href="#"
                                        aria-label="Siguiente">

                                        <i class="bi bi-chevron-right"></i>

                                    </a>

                                </li>

                            </ul>

                        </nav>

                    </div>

                </div>

            </div>

        </main>
    </div>
</div>
<!--=====================================================
    MODAL EDITAR IMAGEN EMPLEADO
======================================================-->

<?php include "modal/modal_editar_imagen_empleado.php"; ?>
<?php include "modal/modal_exportar_datos_empleado.php"; ?>
<!--=====================================================
MODALES
======================================================-->
<!--
Modal para editar los datos del empleado,
excepto la fotografía, la iamgen del empleado se editara en adm_detalles_empleado.php
-->
<?php include "modal/modal_editar_empleado.php"; ?>
<!--=====================================================
SCRIPTS
======================================================-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script src="js/adm_lista_empleados.js"></script>
<script src="js/adm_detalles_empleado.js"></script>
<script src="js/adm_exportar_empleados.js"></script>
<script src="js/menu.js"></script>
</body>

</html>