<?php
//=====================================================
// CoDevPro Technology
// adm_lista_productos.php
// Parte 1 - Estructura Visual
//=====================================================

session_start();

if (!isset($_SESSION["idUser"])) {

    header("Location: login.php");
    exit();
}
$idUser = $_SESSION["idUser"];
require_once "controladores/conexion.php";
/*=============================================
CATEGORÍAS
=============================================*/

$categorias = mysqli_query(
    $conexion,
    "SELECT id_categorias, nombre
     FROM categorias
     WHERE Eliminado = 0
     AND id_user = '$idUser'
     ORDER BY nombre ASC"
);

/*=============================================
MARCAS
=============================================*/

$marcas = mysqli_query(
    $conexion,
    "SELECT id_marca, nombre
     FROM marcas
     WHERE Eliminado = 0
     AND id_user = '$idUser'
     ORDER BY nombre ASC"
);

/*=============================================
PROVEEDORES
=============================================*/

$proveedores = mysqli_query(
    $conexion,
    "SELECT id_provedor, nombre
     FROM provedores
     WHERE Eliminado = 0
     AND id_user = '$idUser'
     ORDER BY nombre ASC"
);
include "includes/head.php";
?>
<div class="d-flex">
    <!-- SIDEBAR -->
    <?php include "includes/admin_sidebar.php"; ?>

    <div class="flex-grow-1">

        <!-- NAVBAR -->
        <?php include "includes/admin_navbar.php"; ?>

        <!-- CONTENIDO -->
        <div class="container-fluid px-4 py-4">

            <!-- =====================================
        HEADER
        ====================================== -->

            <div class="row align-items-center mb-2">

                <div class="col-lg-8">

                    <h2 class="fw-bold mb-1">

                        <i class="bi bi-box-seam text-primary me-2"></i>

                        Lista de Productos

                    </h2>

                    <p class="text-muted mb-0">

                        Administra tu inventario, productos, servicios y publicaciones.

                    </p>

                </div>

                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

                    <button
                        class="btn btn-outline-success me-2"
                        id="btnModalExcel">

                        <i class="bi bi-file-earmark-excel"></i>
                        Excel
                    </button>

                    <button
                        class="btn btn-outline-danger me-2"
                        id="btnModalPDF">

                        <i class="bi bi-file-earmark-pdf"></i>
                        PDF
                    </button>

                    <a href="adm_nuevo_producto.php"
                        class="btn btn-primary">

                        <i class="bi bi-plus-circle-fill me-2"></i>

                        Nuevo Producto

                    </a>

                </div>

            </div>

            <!-- =====================================
        KPI CARDS
        ====================================== -->

            <div class="row g-2 mb-2">

                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Total Productos

                            </small>

                            <h3 class="fw-bold mb-0"
                                id="kpiTotalProductos">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Servicios

                            </small>

                            <h3 class="fw-bold mb-0 text-success"
                                id="kpiServicios">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Destacados

                            </small>

                            <h3 class="fw-bold mb-0 text-warning"
                                id="kpiDestacados">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Ofertas

                            </small>

                            <h3 class="fw-bold mb-0 text-danger"
                                id="kpiOfertas">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Stock Bajo

                            </small>

                            <h3 class="fw-bold mb-0 text-warning"
                                id="kpiStockBajo">

                                0

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-xl-2 col-md-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <small class="text-muted">

                                Inventario

                            </small>

                            <h5 class="fw-bold mb-0 text-primary"
                                id="kpiInventario">

                                S/ 0.00

                            </h5>

                        </div>

                    </div>

                </div>

            </div>

            <!-- =====================================
        CONTENIDO
        ====================================== -->

            <div class="row">

                <!-- IZQUIERDA -->
                <div class="col-xl-9">

                    <!-- =====================================
                FILTROS
                ====================================== -->

                    <div class="card border-0 shadow-sm mb-2">

                        <div class="card-body">

                            <div class="row g-3">

                                <div class="col-lg-4">

                                    <input type="text"
                                        class="form-control"
                                        id="buscarProducto"
                                        placeholder="Buscar producto...">

                                </div>

                                <div class="col-lg-2">

                                    <select class="form-select"
                                        id="filtroCategoria"
                                        name="filtroCategoria">

                                        <option value="">

                                            Categoría

                                        </option>

                                        <?php while ($c = mysqli_fetch_assoc($categorias)): ?>

                                            <option value="<?= $c['id_categorias']; ?>">

                                                <?= htmlspecialchars($c['nombre']); ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>


                                </div>

                                <div class="col-lg-2">

                                    <select class="form-select"
                                        id="filtroMarca"
                                        name="filtroMarca">

                                        <option value="">

                                            Marca

                                        </option>

                                        <?php while ($m = mysqli_fetch_assoc($marcas)): ?>

                                            <option value="<?= $m['id_marca']; ?>">

                                                <?= htmlspecialchars($m['nombre']); ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <div class="col-lg-2">

                                    <select class="form-select"
                                        id="filtroProveedor"
                                        name="filtroProveedor">

                                        <option value="">

                                            Proveedor

                                        </option>

                                        <?php while ($p = mysqli_fetch_assoc($proveedores)): ?>

                                            <option value="<?= $p['id_provedor']; ?>">

                                                <?= htmlspecialchars($p['nombre']); ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <div class="col-lg-2">

                                    <select class="form-select"
                                        id="filtroTipo">

                                        <option value="">

                                            Tipo

                                        </option>

                                        <option>

                                            Producto

                                        </option>

                                        <option>

                                            Servicio

                                        </option>

                                    </select>

                                </div>

                            </div>
                            <button
                                type="button"
                                id="btnRestablecerFiltros"
                                class="btn btn-outline-secondary">

                                <i class="bi bi-arrow-counterclockwise me-1">Restablecer</i>



                            </button>

                        </div>

                    </div>

                    <!-- =====================================
                ACCIONES MASIVAS
                ====================================== -->

                    <div class="card border-0 shadow-sm mb-2">

                        <div class="card-body d-flex flex-wrap gap-2 align-items-center">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="checkTodos">

                                <label class="form-check-label">

                                    Seleccionar Todo

                                </label>

                            </div>

                            <button
                                id="btnEliminarSeleccionados"
                                class="btn btn-outline-danger btn-sm">

                                <i class="bi bi-trash"></i>

                                Eliminar

                            </button>

                            <button
                                id="btnDestacarSeleccionados"
                                class="btn btn-outline-warning btn-sm">

                                <i class="bi bi-star"></i>

                                Destacar

                            </button>
                            <button
                                id="btnQuitarDestacados"
                                class="btn btn-outline-secondary btn-sm">

                                <i class="bi bi-star-half"></i>

                                Quitar Destacados

                            </button>
                            <button
                                id="btnOfertaSeleccionados"
                                class="btn btn-outline-success btn-sm">

                                <i class="bi bi-percent"></i>

                                Oferta

                            </button>

                            <button
                                id="btnQuitarOferta"
                                class="btn btn-outline-secondary btn-sm">

                                <i class="bi bi-percent"></i>

                                Quitar Oferta

                            </button>

                        </div>

                    </div>

                    <!-- =====================================
                TABLA PRODUCTOS
                ====================================== -->

                    <div class="card border-0 shadow-sm">

                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table align-middle mb-0">

                                    <thead class="table-light">

                                        <tr>

                                            <th width="40">
                                                <input type="checkbox">
                                            </th>

                                            <th>Imagen</th>

                                            <th>Código</th>

                                            <th>Producto</th>

                                            <th>Categoría</th>

                                            <th>Precio</th>

                                            <th>Stock</th>

                                            <th>Vendidos</th>

                                            <th>Estado</th>

                                            <th>Acciones</th>

                                        </tr>

                                    </thead>

                                    <tbody id="tablaProductos">

                                        <tr>

                                            <td colspan="10"
                                                class="text-center py-5 text-muted">

                                                Cargando productos...

                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    <!-- PAGINACION -->

                    <div class="d-flex justify-content-between align-items-center mt-2">

                        <div class="text-muted"
                            id="infoRegistros">

                            Mostrando 0 registros

                        </div>

                        <nav>

                            <ul class="pagination mb-0"
                                id="paginacionProductos">

                            </ul>

                        </nav>

                    </div>

                </div>

                <!-- SIDEBAR DERECHO -->

                <div class="col-xl-3">

                    <!-- TOP PRODUCTOS -->

                    <div class="card border-0 shadow-sm mb-2">

                        <div class="card-header bg-white">

                            <h6 class="fw-bold mb-0">

                                <i class="bi bi-trophy-fill text-warning me-2"></i>

                                Más Vendidos

                            </h6>

                        </div>

                        <div class="card-body"
                            id="topVendidos">

                            Sin información.

                        </div>

                    </div>

                    <!-- FAVORITOS -->

                    <div class="card border-0 shadow-sm mb-2">

                        <div class="card-header bg-white">

                            <h6 class="fw-bold mb-0">

                                <i class="bi bi-heart-fill text-danger me-2"></i>

                                Más Favoritos

                            </h6>

                        </div>

                        <div class="card-body"
                            id="topFavoritos">

                            Sin información.

                        </div>

                    </div>

                    <!-- STOCK BAJO -->

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h6 class="fw-bold mb-0">

                                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>

                                Stock Crítico

                            </h6>

                        </div>

                        <div class="card-body"
                            id="stockCritico">

                            Sin alertas.

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
<?php require "modal/modal_adm_editar_producto.php" ?>
<?php require "modal/modal_adm_eliminar_prodcuto.php" ?>
<?php require "modal/modal_adm_eliminar_masivo.php" ?>
<?php require "modal/modal_adm_exportar_datos.php" ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/dashboard.js"></script>
<script src="js/menu.js"></script>
<script src="js/adm_lista_productos.js"></script>

</body>

</html>