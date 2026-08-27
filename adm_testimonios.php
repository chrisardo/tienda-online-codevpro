<?php
//=====================================================
// CoDevPro Technology
// admin_testimonios.php
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
            <!--=====================================
            =            CABECERA
            ======================================-->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-chat-left-text-fill text-primary"></i>
                        Testimonios de Clientes

                    </h2>

                    <p class="text-muted mb-0">

                        Gestiona opiniones, valoraciones y experiencia de compra.

                    </p>

                </div>

            </div>


            <!--=====================================
            =            KPI PRINCIPALES
            ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Total Testimonios

                                    </small>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiTotalTestimonios">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-primary">

                                    <i class="bi bi-chat-left-text-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Calificación Promedio

                                    </small>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiPromedio">

                                        0.0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-warning">

                                    <i class="bi bi-star-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Pendientes

                                    </small>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiPendientes">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-danger">

                                    <i class="bi bi-clock-history"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <small class="text-muted">

                                        Respondidos

                                    </small>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="kpiRespondidos">

                                        0

                                    </h3>

                                </div>

                                <div class="icon-circle bg-success">

                                    <i class="bi bi-reply-fill"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!--=====================================
            =            KPI SECUNDARIOS
            ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <small class="text-muted">

                                Mejor Producto

                            </small>

                            <h5
                                class="fw-bold mb-0"
                                id="kpiMejorProducto">

                                --

                            </h5>

                        </div>

                    </div>

                </div>



                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <small class="text-muted">

                                Cliente Más Activo

                            </small>

                            <h5
                                class="fw-bold mb-0"
                                id="kpiTopCliente">

                                --

                            </h5>

                        </div>

                    </div>

                </div>



                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <small class="text-muted">

                                Testimonios 5 ★

                            </small>

                            <h3
                                class="fw-bold text-success mb-0"
                                id="kpiCincoEstrellas">

                                0

                            </h3>

                        </div>

                    </div>

                </div>



                <div class="col-xl-3 col-md-6">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <small class="text-muted">

                                Tasa de Respuesta

                            </small>

                            <h3
                                class="fw-bold text-primary mb-0"
                                id="kpiTasaRespuesta">

                                0%

                            </h3>

                        </div>

                    </div>

                </div>

            </div>
            <!--=====================================
            =            GRAFICOS ANALITICOS
            ======================================-->

            <div class="row g-4 mb-4">

                <!-- CALIFICACIONES POR ESTRELLAS -->

                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-star-fill text-warning"></i>
                                Distribución de Estrellas

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas
                                id="graficoEstrellas"
                                height="260">

                            </canvas>

                        </div>

                    </div>

                </div>



                <!-- TESTIMONIOS POR MES -->

                <div class="col-lg-8">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-graph-up-arrow text-primary"></i>
                                Evolución de Testimonios

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas
                                id="graficoTestimoniosMes"
                                height="120">

                            </canvas>

                        </div>

                    </div>

                </div>

            </div>



            <!--=====================================
            =            PRODUCTOS MEJOR VALORADOS
            ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-lg-12">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-trophy-fill text-success"></i>
                                Productos Mejor Valorados

                            </h5>

                        </div>

                        <div class="card-body">

                            <canvas
                                id="graficoProductosValorados"
                                height="100">

                            </canvas>

                        </div>

                    </div>

                </div>

            </div>



            <!--=====================================
            =            REPUTACION GENERAL
            ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-lg-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-emoji-smile-fill text-success"></i>
                                Sentimiento Positivo

                            </h5>

                        </div>

                        <div class="card-body text-center">

                            <div
                                id="indicadorSentimiento"
                                class="display-4 fw-bold text-success">

                                0%

                            </div>

                            <small class="text-muted">

                                Basado en calificaciones 4 y 5 estrellas

                            </small>

                        </div>

                    </div>

                </div>



                <div class="col-lg-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-header bg-white">

                            <h5 class="mb-0">

                                <i class="bi bi-chat-square-heart-fill text-danger"></i>
                                Último Testimonio

                            </h5>

                        </div>

                        <div class="card-body">

                            <div id="ultimoTestimonio">

                                <div class="text-center text-muted py-4">

                                    Sin información

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
            <!--=====================================
            =            FILTROS INTELIGENTES
            ======================================-->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-lg-4">

                            <label class="form-label">

                                Buscar

                            </label>

                            <input type="text"
                                id="buscarTestimonio"
                                class="form-control"
                                placeholder="Cliente, producto o comentario...">

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Estado

                            </label>

                            <select id="filtroEstado"
                                class="form-select">

                                <option value="">

                                    Todos

                                </option>

                                <option value="PENDIENTE">

                                    Pendiente

                                </option>

                                <option value="APROBADO">

                                    Aprobado

                                </option>

                                <option value="RECHAZADO">

                                    Rechazado

                                </option>

                            </select>

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Calificación

                            </label>

                            <select id="filtroCalificacion"
                                class="form-select">

                                <option value="">

                                    Todas

                                </option>

                                <option value="5">⭐⭐⭐⭐⭐</option>
                                <option value="4">⭐⭐⭐⭐</option>
                                <option value="3">⭐⭐⭐</option>
                                <option value="2">⭐⭐</option>
                                <option value="1">⭐</option>

                            </select>

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Desde

                            </label>

                            <input type="text"
                                id="fechaInicio"
                                class="form-control">

                        </div>

                        <div class="col-lg-2">

                            <label class="form-label">

                                Hasta

                            </label>

                            <input type="text"
                                id="fechaFin"
                                class="form-control">

                        </div>
                        <div class="col-lg-2 d-flex align-items-end">

                            <button
                                class="btn btn-outline-secondary w-100"
                                id="btnLimpiarFiltros">

                                <i class="bi bi-arrow-clockwise"></i>
                                Restablecer

                            </button>

                        </div>
                    </div>

                </div>

            </div>
            <!--=====================================
            =            TABLA TESTIMONIOS
            ======================================-->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">

                            <i class="bi bi-chat-left-text-fill text-primary"></i>

                            Testimonios de Clientes

                        </h5>

                        <span class="badge bg-primary"
                            id="contadorTestimonios">

                            0 registros

                        </span>

                    </div>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>#</th>

                                    <th>Cliente</th>

                                    <th>Producto</th>

                                    <th>Calificación</th>

                                    <th>Comentario</th>

                                    <th>Estado</th>

                                    <th>Fecha</th>

                                    <th>Acciones</th>

                                </tr>

                            </thead>

                            <tbody id="tablaTestimonios">

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <div class="mt-4"
                id="paginacionTestimonios">

            </div>
        </div>
    </div>
</div>
<?php require "modal/modal_adm_testimonios.php" ?>
<!-- JS -->
<!-- Bootstrap -->
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/adm_testimonios.js"></script>
<script src="js/menu.js"></script>
</body>

</html>