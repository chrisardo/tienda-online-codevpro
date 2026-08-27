<?php
//=====================================================
// CoDevPro Technology
// categorias.php
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

                <!-- ========================================= -->
                <!-- CABECERA -->
                <!-- ========================================= -->

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

                    <div>

                        <h2 class="fw-bold mb-1">

                            <i class="bi bi-tags-fill text-primary me-2"></i>

                            Categorías

                        </h2>

                        <p class="text-muted mb-0">

                            Administra las categorías de tus productos y servicios.

                        </p>

                    </div>

                    <div class="d-flex gap-2">

                        <button
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalNuevaCategoria">

                            <i class="bi bi-plus-circle me-2"></i>

                            Nueva Categoría

                        </button>

                        <button
                            class="btn btn-outline-success"
                            id="btnExportarCategorias">

                            <i class="bi bi-file-earmark-excel me-2"></i>

                            Exportar

                        </button>

                    </div>

                </div>

                <!-- ========================================= -->
                <!-- KPI CARDS -->
                <!-- ========================================= -->

                <div class="row g-3 mb-4">

                    <div class="col-xl-3 col-md-6">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-body">

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <small class="text-muted">

                                            Total Categorías

                                        </small>

                                        <h3
                                            class="fw-bold mt-2 mb-0"
                                            id="kpiTotalCategorias">

                                            0

                                        </h3>

                                    </div>

                                    <div class="icon-kpi bg-primary-subtle">

                                        <i class="bi bi-tags-fill text-primary"></i>

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

                                            Categorías en Uso

                                        </small>

                                        <h3
                                            class="fw-bold mt-2 mb-0"
                                            id="kpiCategoriasUso">

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

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-body">

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <small class="text-muted">

                                            Productos Asociados

                                        </small>

                                        <h3
                                            class="fw-bold mt-2 mb-0"
                                            id="kpiProductos">

                                            0

                                        </h3>

                                    </div>

                                    <div class="icon-kpi bg-warning-subtle">

                                        <i class="bi bi-box-seam-fill text-warning"></i>

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

                                            Categoría Top

                                        </small>

                                        <h6
                                            class="fw-bold mt-2 mb-0"
                                            id="kpiCategoriaTop">

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

                <!-- ========================================= -->
                <!-- FILTROS -->
                <!-- ========================================= -->

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body">

                        <div class="row g-3 align-items-center">

                            <div class="col-lg-5">

                                <div class="input-group">

                                    <span class="input-group-text">

                                        <i class="bi bi-search"></i>

                                    </span>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="buscarCategoria"
                                        placeholder="Buscar categoría...">

                                </div>

                            </div>

                            <div class="col-lg-3">

                                <select
                                    class="form-select"
                                    id="filtroEstado">

                                    <option value="">
                                        Todas
                                    </option>

                                    <option value="uso">
                                        Con productos
                                    </option>

                                    <option value="sin_productos">
                                        Sin productos
                                    </option>

                                </select>

                            </div>

                            <div class="col-lg-4 text-lg-end">

                                <div class="btn-group">

                                    <button
                                        class="btn btn-outline-primary active">

                                        <i class="bi bi-list-ul"></i>

                                        Lista

                                    </button>

                                    <button
                                        class="btn btn-outline-primary">

                                        <i class="bi bi-grid-3x3-gap"></i>

                                        Tarjetas

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- ========================================= -->
                <!-- TABLA -->
                <!-- ========================================= -->

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white">

                        <div class="d-flex justify-content-between align-items-center">

                            <h5 class="mb-0">

                                <i class="bi bi-table me-2"></i>

                                Lista de Categorías

                            </h5>

                            <span
                                class="badge bg-primary"
                                id="totalRegistros">

                                0 registros

                            </span>

                        </div>

                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">
                            <div id="contenedorTablaCategorias">
                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-light">

                                        <tr>

                                            <th width="70">

                                                ID

                                            </th>

                                            <th width="90">

                                                Imagen

                                            </th>

                                            <th>

                                                Categoría

                                            </th>

                                            <th width="180">

                                                Productos

                                            </th>

                                            <th width="150">

                                                Estado

                                            </th>

                                            <th width="170">

                                                Fecha

                                            </th>

                                            <th width="180">

                                                Acciones

                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody id="tablaCategorias">

                                        <tr>

                                            <td colspan="7"
                                                class="text-center py-5">

                                                <div class="spinner-border text-primary">

                                                </div>

                                                <div class="mt-3">

                                                    Cargando categorías...

                                                </div>

                                            </td>

                                        </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- PAGINACIÓN -->

                <div
                    id="contenedorPaginacionCategorias"
                    class="mt-3">

                </div>

            </div>

        </div>

    </div>

    <!-- MODALES -->

    <?php include "modal/modal_nueva_categoria.php"; ?>
    <?php require_once "modal/modal_ver_categoria.php"; ?>
    <?php include "modal/modal_editar_categoria.php"; ?>
    <?php //include "modal/modal_eliminar_categoria.php"; 
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/categorias.js"></script>
    <script src="js/menu.js"></script>
</body>

</html>