<?php
//=====================================================
// CoDevPro Technology
// adm_nuevo_producto.php
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

/*=============================================
SUCURSALES
=============================================*/

$sucursales = mysqli_query(
    $conexion,
    "SELECT id_sucursal, nombre
     FROM sucursal
     WHERE Eliminado = 0
     AND id_user = '$idUser'
     ORDER BY nombre ASC"
);
?>

<?php include "includes/head.php" ?>

<!-- SIDEBAR -->

<?php include "includes/admin_sidebar.php"; ?>


<!-- CONTENIDO -->

<div class="flex-grow-1">

    <!-- NAVBAR -->

    <?php include "includes/admin_navbar.php"; ?>


    <!-- CONTENIDO PRINCIPAL -->

    <div class="container-fluid px-4 py-4">
        <div id="mensajeProducto"></div>
        <!-- HEADER -->

        <div class="row mb-4">

            <div class="col-lg-8">

                <h2 class="fw-bold mb-2">

                    <i class="bi bi-box-seam me-2 text-primary"></i>

                    Nuevo Inventario

                </h2>

                <p class="text-muted mb-0">

                    Registra nuevo inventario para vender en tu tienda online.

                </p>

            </div>

            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

                <a href="adm_lista_productos.php"
                    class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-left-circle me-2"></i>

                    Volver

                </a>

            </div>

        </div>


        <!-- FORMULARIO -->

        <form id="formNuevoProducto"
            enctype="multipart/form-data">

            <div class="row">

                <!-- COLUMNA IZQUIERDA -->

                <div class="col-xl-9">
                    <!-- Aquí irán los módulos -->
                    <!-- =====================================
                    INFORMACIÓN GENERAL
                    ====================================== -->

                    <div class="card shadow-sm border-0 mb-4 card-admin">

                        <div class="card-header bg-white">

                            <h5 class="mb-0 fw-bold">

                                <i class="bi bi-info-circle-fill text-primary me-2"></i>

                                Información General

                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="row g-4">

                                <!-- CÓDIGO -->

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">
                                        Código
                                        <span class="campo-obligatorio">
                                            <i class="bi bi-asterisk"></i>
                                            Obligatorio
                                        </span>
                                    </label>

                                    <input type="text"
                                        class="form-control"
                                        id="codigo"
                                        name="codigo"
                                        placeholder="Ej: PROD-001">

                                </div>

                                <!-- TIPO -->

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">
                                        Tipo
                                        <span class="campo-obligatorio">
                                            <i class="bi bi-asterisk"></i>
                                            Obligatorio
                                        </span>
                                    </label>

                                    <select class="form-select"
                                        id="tipo"
                                        name="tipo">

                                        <option value="">

                                            Seleccionar

                                        </option>

                                        <option value="Producto">

                                            Producto

                                        </option>

                                        <option value="Servicio">

                                            Servicio

                                        </option>

                                    </select>

                                </div>
                                <!-- NOMBRE -->

                                <div class="col-12">

                                    <label class="form-label fw-semibold">
                                        Nombre
                                        <span class="campo-obligatorio">
                                            <i class="bi bi-asterisk"></i>
                                            Obligatorio
                                        </span>
                                    </label>

                                    <input type="text"
                                        class="form-control"
                                        id="nombre"
                                        name="nombre"
                                        placeholder="Ej: Laptop Lenovo IdeaPad 5">

                                </div>

                            </div>

                        </div>

                    </div>
                    <div id="camposProducto">


                        <!-- Campos exclusivos de productos -->
                        <!-- =====================================
                            PRECIOS E INVENTARIO
                        ===================================== -->
                        <div class="card border-0 shadow-sm mb-4">
                            <!-- DESCRIPCIÓN -->

                            <div class="col-12">

                                <label class="form-label fw-semibold">
                                    Descripción
                                    <span class="campo-opcional">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Opcional
                                    </span>
                                </label>

                                <textarea class="form-control"
                                    id="descripcion"
                                    name="descripcion"
                                    rows="5"
                                    placeholder="Describe el producto..."></textarea>

                            </div>
                            <div class="card-header bg-white py-3">

                                <h5 class="mb-0 fw-bold">

                                    <i class="bi bi-currency-dollar text-success me-2"></i>

                                    Precios e Inventario

                                </h5>

                            </div>

                            <div class="card-body">

                                <div class="row g-3">

                                    <!-- COSTO COMPRA -->

                                    <div class="col-md-6">

                                        <label class="form-label fw-semibold">

                                            Costo de Compra
                                            <span class="campo-obligatorio">
                                                <i class="bi bi-asterisk"></i>
                                                Obligatorio
                                            </span>
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                S/

                                            </span>

                                            <input type="number"
                                                step="0.01"
                                                min="0"
                                                name="costo_compra"
                                                id="costo_compra"
                                                class="form-control"
                                                placeholder="0.00">

                                        </div>

                                        <small class="text-muted">

                                            Precio al que compras el producto.

                                        </small>

                                    </div>

                                    <!-- PRECIO VENTA -->

                                    <div class="col-md-6">

                                        <label class="form-label fw-semibold">

                                            Precio de Venta
                                            <span class="campo-obligatorio">
                                                <i class="bi bi-asterisk"></i>
                                                Obligatorio
                                            </span>
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                S/

                                            </span>

                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="precio"
                                                id="precio"
                                                class="form-control"
                                                placeholder="0.00">

                                        </div>

                                    </div>

                                    <!-- PRECIO ANTERIOR -->

                                    <div class="col-md-6">

                                        <label class="form-label fw-semibold">

                                            Precio Anterior
                                            <span class="campo-opcional">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Opcional
                                            </span>
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                S/

                                            </span>

                                            <input type="number"
                                                step="0.01"
                                                min="0"
                                                name="precio_anterior"
                                                id="precio_anterior"
                                                class="form-control"
                                                placeholder="0.00">

                                        </div>

                                        <small class="text-muted">

                                            Para mostrar descuentos.

                                        </small>

                                    </div>
                                    <!-- STOCK -->

                                    <div class="col-md-6">

                                        <label class="form-label fw-semibold">

                                            Stock Inicial
                                            <span class="campo-obligatorio">
                                                <i class="bi bi-asterisk"></i>
                                                Obligatorio
                                            </span>
                                        </label>

                                        <input type="number"
                                            min="0"
                                            value="0"
                                            name="stock"
                                            id="stock"
                                            class="form-control">

                                    </div>
                                </div>


                                <hr class="my-4">


                                <!-- DESCUENTOS -->

                                <div class="row g-4">

                                    <div class="col-md-4">

                                        <label class="form-label fw-semibold">

                                            Descuento (%)
                                            <span class="campo-opcional">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Opcional
                                            </span>
                                        </label>

                                        <div class="input-group">

                                            <input type="number"
                                                min="0"
                                                max="99"
                                                value="0"
                                                name="descuento"
                                                id="descuento"
                                                class="form-control">

                                            <span class="input-group-text">

                                                %

                                            </span>

                                        </div>

                                    </div>

                                    <!-- GANANCIA -->

                                    <div class="col-md-4">

                                        <label class="form-label fw-semibold">

                                            Ganancia Estimada

                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                S/

                                            </span>

                                            <input type="text"
                                                id="ganancia"
                                                class="form-control bg-light"
                                                readonly>

                                        </div>

                                    </div>

                                    <!-- MARGEN -->

                                    <div class="col-md-4">

                                        <label class="form-label fw-semibold">

                                            Margen %

                                        </label>

                                        <div class="input-group">

                                            <input type="text"
                                                id="margen"
                                                class="form-control bg-light"
                                                readonly>

                                            <span class="input-group-text">

                                                %

                                            </span>

                                        </div>

                                    </div>

                                </div>

                                <hr class="my-4">


                                <!-- ESTADOS PRODUCTO -->

                                <h6 class="fw-bold mb-3">

                                    Opciones de Venta

                                </h6>

                                <div class="row g-4">

                                    <div class="col-md-3">

                                        <div class="form-check form-switch">

                                            <input class="form-check-input"
                                                type="checkbox"
                                                id="oferta"
                                                name="oferta">

                                            <label class="form-check-label">

                                                Producto en Oferta

                                            </label>

                                        </div>

                                    </div>

                                    <div class="col-md-3">

                                        <div class="form-check form-switch">

                                            <input class="form-check-input"
                                                type="checkbox"
                                                id="destacado"
                                                name="destacado">

                                            <label class="form-check-label">

                                                Producto Destacado

                                            </label>

                                        </div>

                                    </div>

                                    <div class="col-md-3">

                                        <div class="form-check form-switch">

                                            <input class="form-check-input"
                                                type="checkbox"
                                                id="nuevo"
                                                name="nuevo"
                                                checked>

                                            <label class="form-check-label">

                                                Producto Nuevo

                                            </label>

                                        </div>

                                    </div>

                                    <div class="col-md-3">

                                        <div class="form-check form-switch">

                                            <input class="form-check-input"
                                                type="checkbox"
                                                id="envio_gratis"
                                                name="envio_gratis">

                                            <label class="form-check-label">

                                                Envío Gratis

                                            </label>

                                        </div>

                                    </div>
                                    <!-- APLICAR IMPUESTO -->

                                    <div class="col-md-3">

                                        <div class="form-check form-switch">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                id="aplica_impuesto"
                                                name="aplica_impuesto">

                                            <label class="form-check-label">
                                                Aplicar impuesto
                                            </label>

                                        </div>

                                        <small class="text-muted d-block mt-1">
                                            El impuesto se calculará según la configuración vigente.
                                        </small>

                                    </div>
                                </div>

                            </div>

                        </div>
                        <!-- =====================================
                            CATEGORIZACIÓN
                        ===================================== -->
                        <div class="card border-0 shadow-sm mb-4">

                            <div class="card-header bg-white py-3">

                                <h5 class="mb-0 fw-bold">

                                    <i class="bi bi-diagram-3 text-primary me-2"></i>

                                    Categorización del Producto

                                </h5>

                            </div>

                            <div class="card-body">

                                <div class="row g-4">

                                    <!-- CATEGORIA -->

                                    <div class="col-lg-6">

                                        <label class="form-label fw-semibold">

                                            Categoría
                                            <span class="campo-obligatorio">
                                                <i class="bi bi-asterisk"></i>
                                                Obligatorio
                                            </span>
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-tags"></i>

                                            </span>


                                            <select class="form-select"
                                                id="id_categorias"
                                                name="id_categorias">

                                                <option value="">

                                                    Seleccionar categoría

                                                </option>

                                                <?php while ($c = mysqli_fetch_assoc($categorias)): ?>

                                                    <option value="<?= $c['id_categorias']; ?>">

                                                        <?= htmlspecialchars($c['nombre']); ?>

                                                    </option>

                                                <?php endwhile; ?>

                                            </select>

                                        </div>

                                    </div>


                                    <!-- MARCA -->

                                    <div class="col-lg-6">

                                        <label class="form-label fw-semibold">

                                            Marca
                                            <span class="campo-obligatorio">
                                                <i class="bi bi-asterisk"></i>
                                                Obligatorio
                                            </span>
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-bookmark-star"></i>

                                            </span>

                                            <select class="form-select"
                                                id="id_marca"
                                                name="id_marca">

                                                <option value="">

                                                    Sin marca

                                                </option>

                                                <?php while ($m = mysqli_fetch_assoc($marcas)): ?>

                                                    <option value="<?= $m['id_marca']; ?>">

                                                        <?= htmlspecialchars($m['nombre']); ?>

                                                    </option>

                                                <?php endwhile; ?>

                                            </select>

                                        </div>

                                    </div>


                                    <!-- PROVEEDOR -->

                                    <div class="col-lg-6">

                                        <label class="form-label fw-semibold">

                                            Proveedor
                                            <span class="campo-obligatorio">
                                                <i class="bi bi-asterisk"></i>
                                                Obligatorio
                                            </span>
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-truck"></i>

                                            </span>

                                            <select class="form-select"
                                                id="id_provedor"
                                                name="id_provedor">

                                                <option value="">

                                                    Sin proveedor

                                                </option>

                                                <?php while ($p = mysqli_fetch_assoc($proveedores)): ?>

                                                    <option value="<?= $p['id_provedor']; ?>">

                                                        <?= htmlspecialchars($p['nombre']); ?>

                                                    </option>

                                                <?php endwhile; ?>

                                            </select>

                                        </div>

                                    </div>


                                    <!-- SUCURSAL -->

                                    <div class="col-lg-6">

                                        <label class="form-label fw-semibold">

                                            Sucursal
                                            <span class="campo-obligatorio">
                                                <i class="bi bi-asterisk"></i>
                                                Obligatorio
                                            </span>
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                <i class="bi bi-building"></i>

                                            </span>

                                            <select
                                                class="form-select"
                                                id="id_sucursal"
                                                name="id_sucursal">

                                                <option value="">

                                                    Sin sucursal

                                                </option>

                                                <?php while ($s = mysqli_fetch_assoc($sucursales)): ?>

                                                    <option value="<?= $s['id_sucursal']; ?>">

                                                        <?= htmlspecialchars($s['nombre']); ?>

                                                    </option>

                                                <?php endwhile; ?>

                                            </select>
                                        </div>

                                    </div>

                                </div>
                                <hr class="my-4">


                                <!-- RESUMEN -->

                                <div class="alert alert-light border d-flex align-items-center">

                                    <i class="bi bi-info-circle-fill text-primary me-3 fs-4"></i>

                                    <div>

                                        <strong>

                                            Organización inteligente

                                        </strong>

                                        <br>

                                        Asigna correctamente categoría, marca y proveedor para mejorar
                                        los filtros, reportes y estadísticas de tu tienda online.

                                    </div>

                                </div>

                            </div>

                        </div>
                        <!-- =====================================
                            GALERÍA DE IMÁGENES
                        ===================================== -->
                        <div class="card border-0 shadow-sm mb-4">

                            <div class="card-header bg-white py-3">

                                <h5 class="mb-0 fw-bold">

                                    <i class="bi bi-images text-primary me-2"></i>

                                    Galería de Imágenes
                                    <span class="campo-obligatorio">
                                        <i class="bi bi-asterisk"></i>
                                        Obligatorio
                                    </span>
                                </h5>

                            </div>

                            <div class="card-body">

                                <!-- ZONA DRAG & DROP -->

                                <div id="dropZone"
                                    class="dropzone-producto">

                                    <div class="text-center">

                                        <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>

                                        <h5 class="mt-3">

                                            Arrastra tus imágenes aquí

                                        </h5>

                                        <p class="text-muted mb-3">

                                            o haz clic para seleccionar archivos

                                        </p>

                                        <button type="button"
                                            class="btn btn-primary">

                                            Seleccionar Imágenes

                                        </button>

                                        <input type="file"
                                            id="imagenesProducto"
                                            name="imagenesProducto[]"
                                            multiple
                                            accept="image/*"
                                            hidden>

                                    </div>

                                </div>

                                <div class="mt-3">

                                    <small class="text-muted">

                                        Formatos permitidos:
                                        JPG, JPEG, PNG, WEBP.
                                        Máximo 2.7 MB por imagen.
                                        Máximo 4 imágenes por producto.

                                    </small>

                                </div>


                                <hr class="my-4">


                                <!-- CONTADOR -->

                                <div class="d-flex justify-content-between align-items-center mb-3">

                                    <h6 class="fw-bold mb-0">

                                        Imágenes Seleccionadas

                                    </h6>

                                    <span class="badge bg-primary"
                                        id="contadorImagenes">

                                        0 imágenes

                                    </span>

                                </div>


                                <!-- GALERIA -->

                                <div class="row g-3"
                                    id="previewImagenes">

                                </div>


                                <!-- IMAGEN PRINCIPAL -->

                                <div class="alert alert-light border mt-4 mb-0">

                                    <i class="bi bi-star-fill text-warning me-2"></i>

                                    La primera imagen será utilizada como
                                    imagen principal del producto.

                                </div>

                            </div>

                        </div>
                    </div>
                    <div id="camposServicio" style="display:none;">

                        <div class="card border-0 shadow-sm mb-4">

                            <div class="card-header bg-white">

                                <h5 class="mb-0 fw-bold">

                                    <i class="bi bi-tools text-success me-2"></i>

                                    Información del Servicio

                                </h5>

                            </div>

                            <div class="card-body">

                                <div class="row g-3">

                                    <!-- PRECIO -->

                                    <div class="col-md-6">

                                        <label class="form-label fw-semibold">

                                            Precio del Servicio

                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">

                                                S/

                                            </span>

                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                id="precio_servicio"
                                                name="precio_servicio"
                                                class="form-control">

                                        </div>

                                    </div>

                                    <!-- SUCURSAL -->

                                    <div class="col-md-6">

                                        <label class="form-label fw-semibold">

                                            Sucursal

                                        </label>

                                        <select
                                            class="form-select"
                                            id="sucursal_servicio"
                                            name="sucursal_servicio">

                                            <option value="">

                                                Sin sucursal

                                            </option>

                                            <?php
                                            mysqli_data_seek($sucursales, 0);

                                            while ($s = mysqli_fetch_assoc($sucursales)):
                                            ?>

                                                <option value="<?= $s['id_sucursal']; ?>">

                                                    <?= htmlspecialchars($s['nombre']); ?>

                                                </option>

                                            <?php endwhile; ?>

                                        </select>

                                    </div>

                                    <!-- DESCRIPCIÓN -->

                                    <div class="col-12">

                                        <label class="form-label fw-semibold">

                                            Detalle del Servicio

                                        </label>

                                        <textarea
                                            class="form-control"
                                            rows="5"
                                            id="descripcion_servicio"
                                            name="descripcion_servicio"></textarea>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>
                    <!-- MENSAJE SISTEMA -->

                    <div id="alertProducto"
                        class="alert d-none"
                        role="alert">

                    </div>
                    <!-- =====================================
                        PUBLICACIÓN
                    ===================================== -->
                    <div class="card border-0 shadow-sm mb-4 sticky-top sidebar-producto">
                        <div class="card-body">
                            <!-- BOTONES -->

                            <div class="d-grid gap-2">
                                <button type="submit"
                                    class="btn btn-success"
                                    id="btnPublicarProducto">

                                    <i class="bi bi-cloud-upload-fill me-2"></i>

                                    Publicar Producto

                                </button>


                                <button type="button"
                                    class="btn btn-primary"
                                    id="btnVistaPrevia">

                                    <i class="bi bi-eye-fill me-2"></i>

                                    Vista Previa

                                </button>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- COLUMNA DERECHA -->

                <div class="col-xl-3">

                    <!-- Resumen Shopify -->
                    <!-- =====================================
                        RESUMEN DEL PRODUCTO
                    ===================================== -->
                    <div class="card border-0 shadow-sm mb-4">

                        <div class="card-header bg-white">

                            <h5 class="mb-0 fw-bold">

                                <i class="bi bi-clipboard-data-fill text-primary me-2"></i>

                                Resumen

                            </h5>

                        </div>

                        <div class="card-body">

                            <ul class="list-group list-group-flush">

                                <li class="list-group-item d-flex justify-content-between">

                                    <span>Nombre</span>

                                    <strong id="resNombre">

                                        -
                                    </strong>

                                </li>

                                <li class="list-group-item d-flex justify-content-between">

                                    <span>Categoría</span>

                                    <strong id="resCategoria">

                                        -
                                    </strong>

                                </li>

                                <li class="list-group-item d-flex justify-content-between">

                                    <span>Marca</span>

                                    <strong id="resMarca">

                                        -
                                    </strong>

                                </li>

                                <li class="list-group-item d-flex justify-content-between">

                                    <span>Precio</span>

                                    <strong id="resPrecio">

                                        S/ 0.00
                                    </strong>

                                </li>

                                <li class="list-group-item d-flex justify-content-between">

                                    <span>Stock</span>

                                    <strong id="resStock">

                                        0
                                    </strong>

                                </li>

                                <li class="list-group-item d-flex justify-content-between">

                                    <span>Imágenes</span>

                                    <strong id="resImagenes">

                                        0
                                    </strong>

                                </li>

                            </ul>

                        </div>

                    </div>
                    <!-- =====================================
                        INDICADORES
                    ===================================== -->
                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white">

                            <h5 class="mb-0 fw-bold">

                                <i class="bi bi-graph-up-arrow text-success me-2"></i>

                                Calidad del Producto

                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="mb-3">

                                <div class="d-flex justify-content-between">

                                    <span>Completado</span>

                                    <strong id="porcentajeProducto">

                                        0%
                                    </strong>

                                </div>

                                <div class="progress mt-2">

                                    <div class="progress-bar"
                                        id="barraProducto"
                                        style="width:0%">

                                    </div>

                                </div>

                            </div>

                            <div id="checkProducto">

                                <div class="text-muted">

                                    Complete la información del producto.

                                </div>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </form>

    </div>

</div>


<!-- LOADING -->

<div id="loadingProducto"
    class="loading-producto d-none">

    <div class="spinner-border text-primary"
        role="status">

    </div>

    <span class="mt-3">

        Guardando producto...

    </span>

</div>


<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS Dashboard -->

<script src="js/dashboard.js"></script>

<!-- JS Página -->

<script src="js/adm_nuevo_producto.js"></script>

<script src="js/menu.js"></script>
</body>

</html>