<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_sueldos.php
// Módulo: Sueldos y Pagos
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
//
// Se mantiene disponible para futuras operaciones
// PHP del módulo.
//
// La carga de empleados, sueldos, KPIs y paginación
// se realizará mediante AJAX.
//
//=====================================================

require_once "controladores/conexion.php";

?>

<?php include "includes/head.php"; ?>


<!--=====================================================
    CSS DEL MÓDULO
======================================================-->

<link
    rel="stylesheet"
    href="css/adm_sueldos.css">


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

        <main class="container-fluid px-4 py-4">


            <!--=================================================
                CABECERA
            ==================================================-->

            <div class="sueldos-header mb-4">


                <!--=================================================
                    INFORMACIÓN DEL MÓDULO
                ==================================================-->

                <div>

                    <div
                        class="d-flex
                               align-items-center
                               gap-2
                               mb-1">

                        <span class="sueldos-header-icon">

                            <i class="bi bi-cash-stack"></i>

                        </span>


                        <h3 class="fw-bold mb-0">

                            Sueldos y Pagos

                        </h3>

                    </div>


                    <p class="text-muted mb-0">

                        Administra los sueldos asignados
                        a tus empleados y consulta su estado.

                    </p>

                </div>


                <!--=================================================
                    ACCIÓN PRINCIPAL
                ==================================================-->

                <div>

                    <button
                        type="button"
                        class="btn btn-primary"
                        id="btnNuevoSueldo">

                        <i class="bi bi-plus-circle me-1"></i>

                        Asignar sueldo

                    </button>

                </div>

            </div>


            <!--=================================================
                KPIs
            ==================================================-->

            <div class="row g-3 mb-4">


                <!--=================================================
                    TOTAL EMPLEADOS
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div
                        class="card
                               sueldo-kpi-card
                               h-100">

                        <div class="card-body">

                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-start">

                                <div>

                                    <span class="kpi-label">

                                        Empleados

                                    </span>


                                    <h3
                                        class="kpi-value"
                                        id="kpiEmpleados">

                                        0

                                    </h3>

                                </div>


                                <div
                                    class="kpi-icon
                                           kpi-icon-primary">

                                    <i class="bi bi-people-fill"></i>

                                </div>

                            </div>


                            <small class="text-muted">

                                Empleados registrados

                            </small>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    SUELDOS ACTIVOS
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div
                        class="card
                               sueldo-kpi-card
                               h-100">

                        <div class="card-body">

                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-start">

                                <div>

                                    <span class="kpi-label">

                                        Sueldos activos

                                    </span>


                                    <h3
                                        class="kpi-value"
                                        id="kpiSueldosActivos">

                                        0

                                    </h3>

                                </div>


                                <div
                                    class="kpi-icon
                                           kpi-icon-success">

                                    <i class="bi bi-check-circle-fill"></i>

                                </div>

                            </div>


                            <small class="text-muted">

                                Actualmente vigentes

                            </small>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    SIN SUELDO
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div
                        class="card
                               sueldo-kpi-card
                               h-100">

                        <div class="card-body">

                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-start">

                                <div>

                                    <span class="kpi-label">

                                        Sin sueldo

                                    </span>


                                    <h3
                                        class="kpi-value"
                                        id="kpiSinSueldo">

                                        0

                                    </h3>

                                </div>


                                <div
                                    class="kpi-icon
                                           kpi-icon-warning">

                                    <i class="bi bi-exclamation-circle-fill"></i>

                                </div>

                            </div>


                            <small class="text-muted">

                                Empleados pendientes

                            </small>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    NÓMINA MENSUAL
                ==================================================-->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div
                        class="card
                               sueldo-kpi-card
                               h-100">

                        <div class="card-body">

                            <div
                                class="d-flex
                                       justify-content-between
                                       align-items-start">

                                <div>

                                    <span class="kpi-label">

                                        Nómina mensual

                                    </span>


                                    <h3
                                        class="kpi-value"
                                        id="kpiNomina">

                                        S/ 0.00

                                    </h3>

                                </div>


                                <div
                                    class="kpi-icon
                                           kpi-icon-info">

                                    <i class="bi bi-wallet2"></i>

                                </div>

                            </div>


                            <small class="text-muted">

                                Referencia de sueldos mensuales

                            </small>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                FILTROS
            ==================================================-->

            <div
                class="card
                       border-0
                       shadow-sm
                       mb-4">

                <div class="card-body">

                    <div
                        class="row
                               g-3
                               align-items-end">


                        <!--=========================================
                            BUSCAR EMPLEADO
                        ==========================================-->

                        <div class="col-12 col-lg-5">

                            <label
                                class="form-label fw-semibold"
                                for="buscarSueldo">

                                Buscar empleado

                            </label>


                            <div class="input-group">

                                <span
                                    class="input-group-text
                                           bg-white">

                                    <i class="bi bi-search"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarSueldo"
                                    autocomplete="off"
                                    placeholder="Nombre, apellido, DNI o cargo...">

                            </div>

                        </div>


                        <!--=========================================
                            ESTADO
                        ==========================================-->

                        <div
                            class="col-12
                                   col-sm-6
                                   col-lg-2">

                            <label
                                class="form-label fw-semibold"
                                for="filtroEstadoSueldo">

                                Estado

                            </label>


                            <select
                                class="form-select"
                                id="filtroEstadoSueldo">

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


                        <!--=========================================
                            PERIODICIDAD
                        ==========================================-->

                        <div
                            class="col-12
                                   col-sm-6
                                   col-lg-3">

                            <label
                                class="form-label fw-semibold"
                                for="filtroTipoSueldo">

                                Periodicidad

                            </label>


                            <select
                                class="form-select"
                                id="filtroTipoSueldo">

                                <option value="">

                                    Todas

                                </option>


                                <option value="MENSUAL">

                                    Mensual

                                </option>


                                <option value="QUINCENAL">

                                    Quincenal

                                </option>


                                <option value="SEMANAL">

                                    Semanal

                                </option>


                                <option value="DIARIO">

                                    Diario

                                </option>

                            </select>

                        </div>


                        <!--=========================================
                            LIMPIAR
                        ==========================================-->

                        <div class="col-12 col-lg-2">

                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                id="btnLimpiarFiltros">

                                <i
                                    class="bi
                                           bi-arrow-counterclockwise
                                           me-1"></i>

                                Limpiar

                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                TABLA DE SUELDOS
            ==================================================-->

            <div
                class="card
                       border-0
                       shadow-sm">


                <!--=================================================
                    HEADER TABLA
                ==================================================-->

                <div
                    class="card-header
                           bg-white
                           border-0
                           py-3">

                    <div
                        class="d-flex
                               justify-content-between
                               align-items-center
                               gap-3
                               flex-wrap">


                        <!--=========================================
                            TÍTULO
                        ==========================================-->

                        <div>

                            <h5 class="fw-bold mb-1">

                                <i
                                    class="bi
                                           bi-person-lines-fill
                                           me-2
                                           text-primary"></i>

                                Sueldos de empleados

                            </h5>


                            <small class="text-muted">

                                Listado de empleados
                                y su remuneración vigente.

                            </small>

                        </div>


                        <!--=========================================
                            CONTADOR
                        ==========================================-->

                        <span
                            class="badge
                                   bg-light
                                   text-dark
                                   border"
                            id="contadorSueldos">

                            0 registros

                        </span>

                    </div>

                </div>


                <!--=================================================
                    CUERPO TABLA
                ==================================================-->

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table
                            class="table
                                   table-hover
                                   align-middle
                                   mb-0
                                   tabla-sueldos">

                            <thead class="table-light">

                                <tr>

                                    <th class="ps-4">

                                        Empleado

                                    </th>


                                    <th>

                                        Cargo

                                    </th>


                                    <th class="text-end">

                                        Sueldo base

                                    </th>


                                    <th>

                                        Periodicidad

                                    </th>


                                    <th>

                                        Inicio

                                    </th>


                                    <th class="text-center">

                                        Estado

                                    </th>


                                    <th class="text-center pe-4">

                                        Acciones

                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tablaSueldos">

                                <!--=====================================
                                    LOADING
                                ======================================-->

                                <tr>

                                    <td
                                        colspan="7"
                                        class="text-center py-5">

                                        <div
                                            class="spinner-border
                                                   text-primary"
                                            role="status">

                                            <span class="visually-hidden">

                                                Cargando...

                                            </span>

                                        </div>


                                        <div
                                            class="text-muted
                                                   mt-2">

                                            Cargando sueldos...

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>


                <!--=================================================
                    PAGINACIÓN
                ==================================================-->

                <div
                    class="card-footer
                           bg-white
                           border-0">

                    <div
                        class="d-flex
                               justify-content-between
                               align-items-center
                               flex-wrap
                               gap-2">


                        <!--=========================================
                            INFORMACIÓN
                        ==========================================-->

                        <small
                            class="text-muted"
                            id="infoPaginacionSueldos">

                            Mostrando 0 registros

                        </small>


                        <!--=========================================
                            PAGINACIÓN
                        ==========================================-->

                        <nav
                            aria-label="Paginación de sueldos">

                            <ul
                                class="pagination
                                       pagination-sm
                                       mb-0"
                                id="paginacionSueldos">

                            </ul>

                        </nav>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>


<!--=====================================================
    MODAL ASIGNAR / EDITAR SUELDO
======================================================-->

<?php

$archivoModalSueldo =
    "modal/modal_editar_sueldos.php";

if (file_exists($archivoModalSueldo)) {

    include $archivoModalSueldo;
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
    JAVASCRIPT DEL MÓDULO
======================================================-->

<script
    src="js/adm_lista_sueldos.js">
</script>


<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>