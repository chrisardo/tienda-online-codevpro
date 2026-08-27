<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_notificaciones.php
// Módulo: Notificaciones
// Sistema: Inventa
//=====================================================


if (session_status() === PHP_SESSION_NONE) {

    session_start();
}


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
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

        <main class="container-fluid px-4 py-4 adm-notificaciones-page">


            <!--=================================================
                ENCABEZADO
            ==================================================-->

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">


                <!-- TITULO -->

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-bell-fill text-primary me-2"></i>

                        Notificaciones

                    </h2>


                    <p class="text-muted mb-0">

                        Administra las notificaciones enviadas a tus clientes.

                    </p>

                </div>


                <!-- BOTON NUEVA NOTIFICACION -->

                <div class="mt-3 mt-md-0">

                    <button
                        type="button"
                        class="btn btn-primary"
                        id="btnNuevaNotificacion">

                        <i class="bi bi-plus-circle me-2"></i>

                        Nueva notificación

                    </button>

                </div>

            </div>



            <!--=================================================
                KPI
            ==================================================-->

            <div class="row g-4 mb-4">


                <!--=================================================
                    KPI TOTAL
                ==================================================-->

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center justify-content-between">


                                <div>

                                    <p class="text-muted mb-1">

                                        Total Notificaciones

                                    </p>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiTotalNotificaciones">

                                        0

                                    </h3>

                                </div>


                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">

                                    <i class="bi bi-bell-fill fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!--=================================================
                    KPI NO LEIDAS
                ==================================================-->

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center justify-content-between">


                                <div>

                                    <p class="text-muted mb-1">

                                        No Leídas

                                    </p>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiNoLeidas">

                                        0

                                    </h3>

                                </div>


                                <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">

                                    <i class="bi bi-envelope-fill fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!--=================================================
                    KPI LEIDAS
                ==================================================-->

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center justify-content-between">


                                <div>

                                    <p class="text-muted mb-1">

                                        Leídas

                                    </p>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiLeidas">

                                        0

                                    </h3>

                                </div>


                                <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">

                                    <i class="bi bi-envelope-open-fill fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!--=================================================
                    KPI HOY
                ==================================================-->

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center justify-content-between">


                                <div>

                                    <p class="text-muted mb-1">

                                        Notificaciones de Hoy

                                    </p>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiNotificacionesHoy">

                                        0

                                    </h3>

                                </div>


                                <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">

                                    <i class="bi bi-calendar-event-fill fs-4"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!--=================================================
                FILTROS Y BUSQUEDA
            ==================================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="row g-3 align-items-end">


                        <!--=================================================
                            BUSCADOR
                        ==================================================-->

                        <div class="col-xl-4 col-lg-6">

                            <label
                                for="buscarNotificacion"
                                class="form-label fw-semibold">

                                Buscar

                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-white">

                                    <i class="bi bi-search"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarNotificacion"
                                    placeholder="Buscar por cliente, título o mensaje...">

                            </div>

                        </div>



                        <!--=================================================
                            FILTRO TIPO
                        ==================================================-->

                        <div class="col-xl-2 col-lg-3 col-md-6">

                            <label
                                for="filtroTipoNotificacion"
                                class="form-label fw-semibold">

                                Tipo

                            </label>

                            <select
                                class="form-select"
                                id="filtroTipoNotificacion">

                                <option value="">

                                    Todos

                                </option>

                                <option value="PEDIDO">

                                    Pedido

                                </option>

                                <option value="PRODUCTO">

                                    Producto

                                </option>

                                <option value="OFERTA">

                                    Oferta

                                </option>

                                <option value="PROMOCION">

                                    Promoción

                                </option>

                                <option value="SISTEMA">

                                    Sistema

                                </option>

                                <option value="OTRO">

                                    Otro

                                </option>

                            </select>

                        </div>



                        <!--=================================================
                            FILTRO ESTADO
                        ==================================================-->

                        <div class="col-xl-2 col-lg-3 col-md-6">

                            <label
                                for="filtroEstadoNotificacion"
                                class="form-label fw-semibold">

                                Estado

                            </label>

                            <select
                                class="form-select"
                                id="filtroEstadoNotificacion">

                                <option value="">

                                    Todas

                                </option>

                                <option value="0">

                                    No leídas

                                </option>

                                <option value="1">

                                    Leídas

                                </option>

                            </select>

                        </div>



                        <!--=================================================
                            FECHA
                        ==================================================-->

                        <div class="col-xl-2 col-lg-6 col-md-6">

                            <label
                                for="filtroFechaNotificacion"
                                class="form-label fw-semibold">

                                Fecha

                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="filtroFechaNotificacion">

                        </div>



                        <!--=================================================
                            LIMPIAR
                        ==================================================-->

                        <div class="col-xl-2 col-lg-6 col-md-6">

                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                id="btnLimpiarFiltros">

                                <i class="bi bi-arrow-counterclockwise me-2"></i>

                                Limpiar filtros

                            </button>

                        </div>

                    </div>

                </div>

            </div>



            <!--=================================================
                LISTA DE NOTIFICACIONES
            ==================================================-->

            <div class="card border-0 shadow-sm">


                <!--=================================================
                    CABECERA TABLA
                ==================================================-->

                <div class="card-header bg-white border-0 py-3">

                    <div class="d-flex flex-wrap justify-content-between align-items-center">


                        <div>

                            <h5 class="fw-bold mb-1">

                                Lista de Notificaciones

                            </h5>

                            <small class="text-muted">

                                Consulta y administra las notificaciones de tus clientes.

                            </small>
                        </div>
                    </div>

                </div>



                <!--=================================================
                    TABLA
                ==================================================-->

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th class="ps-4">
                                        #
                                    </th>

                                    <th>
                                        Cliente
                                    </th>

                                    <th>
                                        Notificación
                                    </th>

                                    <th>
                                        Tipo
                                    </th>

                                    <th>
                                        Estado
                                    </th>

                                    <th>
                                        Fecha
                                    </th>

                                    <th class="text-center">
                                        Acciones
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tablaNotificaciones">

                                <!--=====================================
                                    ESTADO INICIAL
                                ======================================-->

                                <tr>

                                    <td
                                        colspan="7"
                                        class="text-center py-5">

                                        <div class="text-muted">

                                            <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>

                                            <h6 class="fw-semibold">

                                                No hay notificaciones para mostrar

                                            </h6>

                                            <p class="mb-0">

                                                Las notificaciones aparecerán aquí.

                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>



                <!--=================================================
                    PAGINACION
                ==================================================-->

                <div class="card-footer bg-white border-0 py-3">

                    <div class="d-flex flex-wrap justify-content-between align-items-center">


                        <div>

                            <small
                                class="text-muted"
                                id="infoPaginacionNotificaciones">

                                Mostrando 0 de 0 notificaciones

                            </small>

                        </div>


                        <nav
                            aria-label="Paginación de notificaciones">

                            <ul
                                class="pagination pagination-sm mb-0"
                                id="paginacionNotificaciones">

                                <li class="page-item disabled">

                                    <button
                                        class="page-link"
                                        type="button">

                                        <i class="bi bi-chevron-left"></i>

                                    </button>

                                </li>


                                <li class="page-item active">

                                    <button
                                        class="page-link"
                                        type="button">

                                        1

                                    </button>

                                </li>


                                <li class="page-item disabled">

                                    <button
                                        class="page-link"
                                        type="button">

                                        <i class="bi bi-chevron-right"></i>

                                    </button>

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
    MODAL NUEVA / EDITAR NOTIFICACIÓN
======================================================-->
<?php include "modal/modal_nueva_editar_notificacion.php"; ?>




<!--=====================================================
    MODAL VER NOTIFICACIÓN
======================================================-->


<?php include "modal/modal_ver_notificacion.php"; ?>

<!--=====================================================
    BOOTSTRAP
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>



<!--=====================================================
    SWEET ALERT
======================================================-->

<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</script>



<!--=====================================================
    JS DEL MÓDULO
======================================================-->

<script
    src="js/adm_notificaciones.js">
</script>



<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>