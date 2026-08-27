<?php
//=====================================================
// CoDevPro Technology
// Archivo: adm_productos_proveedor.php
// Módulo: Productos del Proveedor
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

            <div class="adm-productos-proveedor-header mb-4">


                <!-- IZQUIERDA -->

                <div>

                    <!-- BREADCRUMB -->

                    <div class="d-flex align-items-center gap-2 mb-2">

                        <span class="adm-productos-proveedor-breadcrumb">

                            Operaciones

                        </span>

                        <i class="bi bi-chevron-right text-muted small"></i>

                        <span class="text-muted small">

                            Proveedores

                        </span>

                        <i class="bi bi-chevron-right text-muted small"></i>

                        <span class="text-muted small">

                            Productos

                        </span>

                    </div>


                    <!-- TITULO -->

                    <div class="d-flex align-items-center gap-3">


                        <div class="adm-productos-proveedor-title-icon">

                            <i class="bi bi-box-seam-fill"></i>

                        </div>


                        <div>

                            <h1 class="adm-productos-proveedor-title mb-1">

                                Productos del proveedor

                            </h1>


                            <p class="adm-productos-proveedor-subtitle mb-0">

                                Consulta y administra los productos
                                asociados a tus proveedores.

                            </p>

                        </div>


                    </div>

                </div>


                <!-- DERECHA -->

                <div class="adm-productos-proveedor-header-actions">


                    <a href="adm_lista_proveedores.php"
                        class="btn btn-outline-secondary">

                        <i class="bi bi-arrow-left me-1"></i>

                        Volver a proveedores

                    </a>


                </div>


            </div>



            <!--=================================================
                KPI
            ==================================================-->

            <div class="row g-4 mb-4">


                <!-- TOTAL PRODUCTOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card adm-productos-proveedor-kpi h-100">

                        <div class="card-body">

                            <div
                                class="d-flex justify-content-between align-items-start">


                                <div>

                                    <span
                                        class="adm-productos-proveedor-kpi-label">

                                        Total productos

                                    </span>


                                    <h3
                                        class="adm-productos-proveedor-kpi-value"
                                        id="kpiTotalProductosProveedor">

                                        0

                                    </h3>


                                    <span
                                        class="adm-productos-proveedor-kpi-description">

                                        Productos asociados

                                    </span>

                                </div>


                                <div class="adm-productos-proveedor-kpi-icon">

                                    <i class="bi bi-box-seam-fill"></i>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>



                <!-- ACTIVOS -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card adm-productos-proveedor-kpi h-100">

                        <div class="card-body">

                            <div
                                class="d-flex justify-content-between align-items-start">


                                <div>

                                    <span
                                        class="adm-productos-proveedor-kpi-label">

                                        Productos activos

                                    </span>


                                    <h3
                                        class="adm-productos-proveedor-kpi-value text-success"
                                        id="kpiProductosActivosProveedor">

                                        0

                                    </h3>


                                    <span
                                        class="adm-productos-proveedor-kpi-description">

                                        Disponibles en catálogo

                                    </span>

                                </div>


                                <div
                                    class="adm-productos-proveedor-kpi-icon adm-productos-proveedor-kpi-icon-success">

                                    <i class="bi bi-check-circle-fill"></i>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>



                <!-- SIN STOCK -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card adm-productos-proveedor-kpi h-100">

                        <div class="card-body">

                            <div
                                class="d-flex justify-content-between align-items-start">


                                <div>

                                    <span
                                        class="adm-productos-proveedor-kpi-label">

                                        Sin stock

                                    </span>


                                    <h3
                                        class="adm-productos-proveedor-kpi-value text-danger"
                                        id="kpiProductosSinStockProveedor">

                                        0

                                    </h3>


                                    <span
                                        class="adm-productos-proveedor-kpi-description">

                                        Requieren reposición

                                    </span>

                                </div>


                                <div
                                    class="adm-productos-proveedor-kpi-icon adm-productos-proveedor-kpi-icon-danger">

                                    <i class="bi bi-box-seam"></i>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>



                <!-- VALOR INVENTARIO -->

                <div class="col-12 col-sm-6 col-xl-3">

                    <div class="card adm-productos-proveedor-kpi h-100">

                        <div class="card-body">

                            <div
                                class="d-flex justify-content-between align-items-start">


                                <div>

                                    <span
                                        class="adm-productos-proveedor-kpi-label">

                                        Valor del inventario

                                    </span>


                                    <h3
                                        class="adm-productos-proveedor-kpi-value text-primary"
                                        id="kpiValorInventarioProveedor">

                                        S/ 0.00

                                    </h3>


                                    <span
                                        class="adm-productos-proveedor-kpi-description">

                                        Calculado a costo de compra

                                    </span>

                                </div>


                                <div
                                    class="adm-productos-proveedor-kpi-icon adm-productos-proveedor-kpi-icon-primary">

                                    <i class="bi bi-cash-stack"></i>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>


            </div>



            <!--=================================================
                FILTROS
            ==================================================-->

            <div class="card adm-productos-proveedor-card mb-4">


                <!-- HEADER -->

                <div class="card-header adm-productos-proveedor-card-header">


                    <div class="d-flex align-items-center gap-2">


                        <div class="adm-productos-proveedor-section-icon">

                            <i class="bi bi-funnel-fill"></i>

                        </div>


                        <div>

                            <h5 class="mb-0">

                                Filtros de búsqueda

                            </h5>


                            <small class="text-muted">

                                Filtra los productos asociados a tus
                                proveedores.

                            </small>

                        </div>


                    </div>


                </div>



                <!-- BODY -->

                <div class="card-body">


                    <div class="row g-3 align-items-end">


                        <!-- PROVEEDOR -->

                        <div class="col-12 col-lg-3">


                            <label
                                for="filtroProveedorProducto"
                                class="form-label fw-semibold">

                                Proveedor

                            </label>


                            <select
                                class="form-select"
                                id="filtroProveedorProducto">

                                <option value="">

                                    Todos los proveedores

                                </option>

                            </select>


                        </div>



                        <!-- BUSCAR -->

                        <div class="col-12 col-lg-3">


                            <label
                                for="buscarProductoProveedor"
                                class="form-label fw-semibold">

                                Buscar producto

                            </label>


                            <div class="input-group">


                                <span class="input-group-text">

                                    <i class="bi bi-search"></i>

                                </span>


                                <input
                                    type="text"
                                    class="form-control"
                                    id="buscarProductoProveedor"
                                    placeholder="Nombre o código..."
                                    autocomplete="off">


                            </div>


                        </div>



                        <!-- CATEGORIA -->

                        <div class="col-12 col-md-6 col-lg-2">


                            <label
                                for="filtroCategoriaProductoProveedor"
                                class="form-label fw-semibold">

                                Categoría

                            </label>


                            <select
                                class="form-select"
                                id="filtroCategoriaProductoProveedor">

                                <option value="">

                                    Todas

                                </option>

                            </select>


                        </div>



                        <!-- MARCA -->

                        <div class="col-12 col-md-6 col-lg-2">


                            <label
                                for="filtroMarcaProductoProveedor"
                                class="form-label fw-semibold">

                                Marca

                            </label>


                            <select
                                class="form-select"
                                id="filtroMarcaProductoProveedor">

                                <option value="">

                                    Todas

                                </option>

                            </select>


                        </div>



                        <!-- STOCK -->

                        <div class="col-12 col-md-6 col-lg-2">


                            <label
                                for="filtroStockProductoProveedor"
                                class="form-label fw-semibold">

                                Stock

                            </label>


                            <select
                                class="form-select"
                                id="filtroStockProductoProveedor">

                                <option value="todos">

                                    Todos

                                </option>

                                <option value="disponible">

                                    Disponible

                                </option>

                                <option value="bajo">

                                    Stock bajo

                                </option>

                                <option value="agotado">

                                    Agotado

                                </option>

                            </select>


                        </div>



                        <!-- FECHA -->

                        <div class="col-12 col-md-6 col-lg-3">


                            <label
                                for="filtroFechaProductoProveedor"
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
                                    id="filtroFechaProductoProveedor"
                                    placeholder="Seleccionar fecha"
                                    autocomplete="off">


                            </div>


                        </div>



                        <!-- ESTADO -->

                        <div class="col-12 col-md-6 col-lg-2">


                            <label
                                for="filtroEstadoProductoProveedor"
                                class="form-label fw-semibold">

                                Estado

                            </label>


                            <select
                                class="form-select"
                                id="filtroEstadoProductoProveedor">

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



                        <!-- LIMPIAR -->

                        <div class="col-12 col-md-6 col-lg-2">


                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                id="btnLimpiarFiltrosProductoProveedor"
                                title="Limpiar filtros">

                                <i
                                    class="bi bi-arrow-counterclockwise me-1"></i>

                                Limpiar

                            </button>


                        </div>


                    </div>

                </div>

            </div>



            <!--=================================================
                LISTA DE PRODUCTOS
            ==================================================-->

            <div class="card adm-productos-proveedor-card">


                <!-- HEADER -->

                <div
                    class="card-header adm-productos-proveedor-card-header">


                    <div
                        class="d-flex justify-content-between align-items-center flex-wrap gap-3">


                        <!-- TITULO -->

                        <div class="d-flex align-items-center gap-2">


                            <div
                                class="adm-productos-proveedor-section-icon">

                                <i class="bi bi-table"></i>

                            </div>


                            <div>

                                <h5 class="mb-0">

                                    Productos del proveedor

                                </h5>


                                <small
                                    class="text-muted"
                                    id="textoResultadosProductosProveedor">

                                    Cargando productos...

                                </small>

                            </div>


                        </div>
                    </div>


                </div>



                <!-- TABLA -->

                <div class="card-body p-0">


                    <div class="table-responsive">


                        <table
                            class="table table-hover align-middle adm-productos-proveedor-table mb-0">


                            <thead>

                                <tr>


                                    <th class="ps-4">

                                        Producto

                                    </th>


                                    <th>

                                        Código

                                    </th>


                                    <th>

                                        Categoría

                                    </th>


                                    <th>

                                        Marca

                                    </th>


                                    <th class="text-end">

                                        Costo

                                    </th>


                                    <th class="text-end">

                                        Precio

                                    </th>


                                    <th class="text-center">

                                        Stock

                                    </th>


                                    <th>

                                        Estado

                                    </th>


                                    <th>

                                        Registro

                                    </th>


                                    <th class="text-end pe-4">

                                        Acciones

                                    </th>


                                </tr>

                            </thead>


                            <tbody id="tablaProductosProveedor">


                                <!--===================================
                                    LOADING
                                ====================================-->

                                <tr id="filaCargaProductosProveedor">


                                    <td
                                        colspan="10"
                                        class="text-center py-5">


                                        <div
                                            class="spinner-border text-primary mb-3"
                                            role="status">


                                            <span
                                                class="visually-hidden">

                                                Cargando...

                                            </span>


                                        </div>


                                        <div class="text-muted">

                                            Cargando productos...

                                        </div>


                                    </td>


                                </tr>


                            </tbody>


                        </table>

                    </div>

                </div>



                <!--=================================================
                    FOOTER / PAGINACIÓN
                ==================================================-->

                <div class="card-footer bg-white border-top">


                    <div
                        class="d-flex justify-content-between align-items-center flex-wrap gap-3">


                        <div
                            class="small text-muted"
                            id="infoPaginacionProductosProveedor">

                            Mostrando 0 de 0 productos

                        </div>


                        <nav
                            aria-label="Paginación de productos">


                            <ul
                                class="pagination pagination-sm mb-0"
                                id="paginacionProductosProveedor">

                            </ul>


                        </nav>


                    </div>


                </div>


            </div>


        </main>

    </div>

</div>



<!--=====================================================
    MODAL VER PRODUCTO
======================================================-->

<?php include "modal/modal_ver_productos_proveedor.php"; ?>
<!--=====================================================
    MODAL EDITAR PROVEEDOR DEL PRODUCTO
======================================================-->

<?php include "modal/modal_editar_proveedor_producto.php"; ?>


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
    src="js/adm_productos_proveedor.js">
</script>



<!--=====================================================
    MENÚ ADMINISTRATIVO
======================================================-->

<script
    src="js/menu.js">
</script>


</body>

</html>