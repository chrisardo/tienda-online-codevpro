<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_lista_proveedores.php
// Módulo: Lista de Proveedores
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

require_once "controladores/conexion.php";

?>

<?php include "includes/head.php"; ?>


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
                ENCABEZADO
            ==================================================-->

            <div class="adm-proveedores-header mb-4">


                <!-- IZQUIERDA -->

                <div>

                    <div class="d-flex align-items-center gap-2 mb-2">

                        <span class="adm-proveedores-breadcrumb">

                            Operaciones

                        </span>

                        <i class="bi bi-chevron-right text-muted small"></i>

                        <span class="text-muted small">

                            Proveedores

                        </span>

                    </div>


                    <div class="d-flex align-items-center gap-3">

                        <div class="adm-proveedores-title-icon">

                            <i class="bi bi-person-badge-fill"></i>

                        </div>

                        <div>

                            <h1 class="adm-proveedores-title mb-1">

                                Proveedores

                            </h1>

                            <p class="adm-proveedores-subtitle mb-0">

                                Administra y consulta los proveedores
                                que abastecen los productos de tu tienda.

                            </p>

                        </div>

                    </div>

                </div>


                <!-- DERECHA -->

                <div class="adm-proveedores-header-actions d-flex flex-wrap gap-2">

                    <!-- EXPORTAR -->

                    <button
                        type="button"
                        class="btn btn-success"
                        data-bs-toggle="modal"
                        data-bs-target="#modalExportarDatosProveedor">

                        <i class="bi bi-file-earmark-excel-fill me-1"></i>

                        Exportar datos

                    </button>


                    <!-- REGISTRAR -->

                    <a href="adm_registrar_proveedor.php"
                        class="btn btn-primary">

                        <i class="bi bi-person-plus-fill me-1"></i>

                        Registrar proveedor

                    </a>

                </div>


            </div>



            <!--=================================================
                KPI
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- TOTAL -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card adm-proveedor-kpi h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">


                                <div>

                                    <span class="adm-proveedor-kpi-label">

                                        Total proveedores

                                    </span>

                                    <h3
                                        class="adm-proveedor-kpi-value"
                                        id="kpiTotalProveedores">

                                        0

                                    </h3>

                                    <span
                                        class="adm-proveedor-kpi-description">

                                        Registrados en el sistema

                                    </span>

                                </div>


                                <div class="adm-proveedor-kpi-icon">

                                    <i class="bi bi-buildings-fill"></i>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>



                <!-- ACTIVOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card adm-proveedor-kpi h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">


                                <div>

                                    <span class="adm-proveedor-kpi-label">

                                        Proveedores activos

                                    </span>

                                    <h3
                                        class="adm-proveedor-kpi-value text-success"
                                        id="kpiProveedoresActivos">

                                        0

                                    </h3>

                                    <span
                                        class="adm-proveedor-kpi-description">

                                        Disponibles actualmente

                                    </span>

                                </div>


                                <div class="adm-proveedor-kpi-icon adm-proveedor-kpi-icon-success">

                                    <i class="bi bi-check-circle-fill"></i>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>



                <!-- INACTIVOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card adm-proveedor-kpi h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">


                                <div>

                                    <span class="adm-proveedor-kpi-label">

                                        Proveedores inactivos

                                    </span>

                                    <h3
                                        class="adm-proveedor-kpi-value text-warning"
                                        id="kpiProveedoresInactivos">

                                        0

                                    </h3>

                                    <span
                                        class="adm-proveedor-kpi-description">

                                        Dados de baja

                                    </span>

                                </div>


                                <div class="adm-proveedor-kpi-icon adm-proveedor-kpi-icon-warning">

                                    <i class="bi bi-pause-circle-fill"></i>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>



                <!-- CON PRODUCTOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card adm-proveedor-kpi h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">


                                <div>

                                    <span class="adm-proveedor-kpi-label">

                                        Con productos

                                    </span>

                                    <h3
                                        class="adm-proveedor-kpi-value text-primary"
                                        id="kpiProveedoresProductos">

                                        0

                                    </h3>

                                    <span
                                        class="adm-proveedor-kpi-description">

                                        Con productos asociados

                                    </span>

                                </div>


                                <div class="adm-proveedor-kpi-icon adm-proveedor-kpi-icon-primary">

                                    <i class="bi bi-box-seam-fill"></i>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>


            </div>



            <!--=================================================
                FILTROS
            ==================================================-->

            <div class="card adm-proveedores-card mb-4">


                <div class="card-header adm-proveedores-card-header">

                    <div class="d-flex align-items-center gap-2">

                        <div class="adm-proveedores-section-icon">

                            <i class="bi bi-funnel-fill"></i>

                        </div>

                        <div>

                            <h5 class="mb-0">

                                Filtros de búsqueda

                            </h5>

                            <small class="text-muted">

                                Utiliza los filtros para encontrar
                                rápidamente un proveedor.

                            </small>

                        </div>

                    </div>


                </div>


                <div class="card-body">


                    <div class="row g-3 align-items-end">


                        <!-- BUSCADOR -->

                        <div class="col-12 col-xl-4">

                            <label
                                for="buscarProveedor"
                                class="form-label fw-semibold">

                                Buscar proveedor

                            </label>


                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-search"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarProveedor"
                                    placeholder="Nombre, RUC, celular o correo..."
                                    autocomplete="off">

                            </div>

                        </div>



                        <!-- ESTADO -->

                        <div class="col-12 col-md-6 col-xl-2">

                            <label
                                for="filtroEstadoProveedor"
                                class="form-label fw-semibold">

                                Estado

                            </label>


                            <select
                                class="form-select"
                                id="filtroEstadoProveedor">

                                <option value="todos">

                                    Todos

                                </option>

                                <option value="activo">

                                    Activos

                                </option>

                                <option value="inactivo">

                                    Inactivos

                                </option>

                            </select>

                        </div>



                        <!-- DEPARTAMENTO -->

                        <div class="col-12 col-md-6 col-xl-3">

                            <label
                                for="filtroDepartamentoProveedor"
                                class="form-label fw-semibold">

                                Departamento

                            </label>


                            <select
                                class="form-select"
                                id="filtroDepartamentoProveedor">

                                <option value="">

                                    Todos los departamentos

                                </option>

                            </select>

                        </div>



                        <!-- FECHA -->

                        <div class="col-12 col-md-6 col-xl-2">

                            <label
                                for="filtroFechaProveedor"
                                class="form-label fw-semibold">

                                Fecha de registro

                            </label>


                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="filtroFechaProveedor"
                                    placeholder="Seleccionar fecha"
                                    autocomplete="off">

                            </div>

                        </div>



                        <!-- LIMPIAR -->

                        <div class="col-12 col-md-6 col-xl-1">

                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                id="btnLimpiarFiltrosProveedor"
                                title="Limpiar filtros">

                                <i class="bi bi-arrow-counterclockwise"></i>

                            </button>

                        </div>


                    </div>

                </div>

            </div>



            <!--=================================================
                LISTA DE PROVEEDORES
            ==================================================-->

            <div class="card adm-proveedores-card">


                <!-- HEADER -->

                <div class="card-header adm-proveedores-card-header">


                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">


                        <div class="d-flex align-items-center gap-2">

                            <div class="adm-proveedores-section-icon">

                                <i class="bi bi-table"></i>

                            </div>


                            <div>

                                <h5 class="mb-0">

                                    Lista de proveedores

                                </h5>

                                <small
                                    class="text-muted"
                                    id="textoResultadosProveedores">

                                    Cargando proveedores...

                                </small>

                            </div>

                        </div>
                    </div>

                </div>



                <!-- TABLA -->

                <div class="card-body p-0">


                    <div class="table-responsive">


                        <table
                            class="table table-hover align-middle adm-proveedores-table mb-0">


                            <thead>

                                <tr>

                                    <th class="ps-4">

                                        Proveedor

                                    </th>

                                    <th>

                                        RUC

                                    </th>

                                    <th>

                                        Contacto

                                    </th>

                                    <th>

                                        Ubicación

                                    </th>

                                    <th>

                                        Registro

                                    </th>

                                    <th>

                                        Estado

                                    </th>

                                    <th class="text-end pe-4">

                                        Acciones

                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tablaProveedores">


                                <!--===================================
                                    LOADING
                                ====================================-->

                                <tr id="filaCargaProveedores">

                                    <td
                                        colspan="7"
                                        class="text-center py-5">


                                        <div
                                            class="spinner-border text-primary mb-3"
                                            role="status">

                                            <span class="visually-hidden">

                                                Cargando...

                                            </span>

                                        </div>


                                        <div class="text-muted">

                                            Cargando proveedores...

                                        </div>


                                    </td>

                                </tr>


                            </tbody>


                        </table>

                    </div>

                </div>



                <!-- FOOTER / PAGINACIÓN -->

                <div class="card-footer bg-white border-top">


                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">


                        <div
                            class="small text-muted"
                            id="infoPaginacionProveedores">

                            Mostrando 0 de 0 proveedores

                        </div>


                        <nav
                            aria-label="Paginación de proveedores">

                            <ul
                                class="pagination pagination-sm mb-0"
                                id="paginacionProveedores">

                            </ul>

                        </nav>


                    </div>


                </div>


            </div>


        </main>

    </div>

</div>



<!--=====================================================
    MODAL EDITAR PROVEEDOR
======================================================-->

<?php include "modal/modal_editar_proveedor.php"; ?>
<?php include "modal/modal_actualizar_imagen_proveedor.php"; ?>
<?php include "modal/modal_exportar_datos_proveedor.php"; ?>

<!--=====================================================
    SCRIPTS
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
    JS DEL MÓDULO
======================================================-->

<script
    src="js/adm_lista_proveedores.js">
</script>

<script src="js/adm_exportar_proveedor.js"></script>
<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>