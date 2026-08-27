<?php
//======================================================
// CoDevPro Technology
// Archivo: adm_detalle_producto.php
// Módulo: Detalle de Producto / Servicio
// Sistema: Inventa
//======================================================

session_start();

if (!isset($_SESSION["idUser"])) {
    header("Location: login.php");
    exit();
}

require_once "controladores/conexion.php";

$idUser = intval($_SESSION["idUser"]);
$idProducto = intval($_GET["id"] ?? 0);

if ($idProducto <= 0) {
    die("Producto inválido");
}

/*======================================================
CONSULTAR PRODUCTO
======================================================*/

$sql = "
SELECT
    p.*,

    c.nombre AS categoria,
    m.nombre AS marca,
    pr.nombre AS proveedor,
    s.nombre AS sucursal

FROM producto p

LEFT JOIN categorias c
    ON c.id_categorias = p.id_categorias

LEFT JOIN marcas m
    ON m.id_marca = p.id_marca

LEFT JOIN provedores pr
    ON pr.id_provedor = p.id_provedor

LEFT JOIN sucursal s
    ON s.id_sucursal = p.id_sucursal

WHERE p.idProducto = ?
AND p.id_user = ?

LIMIT 1
";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al preparar consulta: " . $conexion->error);
}

$stmt->bind_param(
    "ii",
    $idProducto,
    $idUser
);

if (!$stmt->execute()) {
    die("Error al ejecutar consulta: " . $stmt->error);
}

$query = $stmt->get_result();

if ($query->num_rows === 0) {
    die("Producto no encontrado");
}

$producto = $query->fetch_assoc();

/*======================================================
TIPO DE REGISTRO
======================================================*/

$tipoProducto = trim($producto["tipo"] ?? "");

$esServicio = strcasecmp($tipoProducto, "Servicio") === 0;

/*======================================================
VALORES NUMÉRICOS
======================================================*/

$precio = floatval($producto["precio"] ?? 0);

$precioAnterior = floatval(
    $producto["precio_anterior"] ?? 0
);

$costoCompra = floatval(
    $producto["costo_compra"] ?? 0
);

$stock = intval(
    $producto["stock"] ?? 0
);

$descuento = intval(
    $producto["descuento"] ?? 0
);

$aplicaImpuesto = intval(
    $producto["aplica_impuesto"] ?? 0
);

$oferta = intval(
    $producto["oferta"] ?? 0
);

$destacado = intval(
    $producto["destacado"] ?? 0
);

$nuevo = intval(
    $producto["nuevo"] ?? 0
);

$envioGratis = intval(
    $producto["envio_gratis"] ?? 0
);

/*======================================================
CÁLCULO DE IMPUESTO
======================================================*/

/*
 * IGV Perú: 18%
 *
 * IMPORTANTE:
 * Aquí solamente se muestra información.
 * El precio almacenado en la tabla sigue siendo
 * el precio registrado en el producto.
 */

$porcentajeIGV = 18;

$igvCalculado = 0;
$precioConIGV = $precio;
$precioSinIGV = $precio;

if ($aplicaImpuesto === 1) {

    /*
     * Se considera que el precio registrado
     * incluye IGV.
     */

    $precioSinIGV = $precio / 1.18;

    $igvCalculado = $precio - $precioSinIGV;

    $precioConIGV = $precio;
}

/*======================================================
MARGEN
======================================================*/

$margen = 0;
$ganancia = 0;

if ($costoCompra > 0) {

    $ganancia = $precio - $costoCompra;

    $margen = ($ganancia / $costoCompra) * 100;
}

/*======================================================
IMÁGENES
======================================================*/

$sqlImagenes = "
SELECT
    id_imagen,
    imagenes,
    fecha_registro,
    fecha_actualizado

FROM imagenes

WHERE idProducto = ?

ORDER BY id_imagen ASC
";

$stmtImg = $conexion->prepare($sqlImagenes);

if (!$stmtImg) {
    die("Error al preparar consulta de imágenes:<br><br>" .
        $conexion->error);
}

$stmtImg->bind_param(
    "i",
    $idProducto
);

if (!$stmtImg->execute()) {
    die("Error al ejecutar consulta de imágenes:<br><br>" .
        $stmtImg->error);
}

$imagenes = $stmtImg->get_result();

$miniaturas = [];

$imagenDefault = "img/logo.png";

$fechaUltimaImagen = null;

/*======================================================
PROCESAR IMÁGENES
======================================================*/

if ($imagenes->num_rows > 0) {

    while ($img = $imagenes->fetch_assoc()) {

        $mime = "image/jpeg";

        /*
         * Si la imagen almacenada es JPEG/PNG/WEBP,
         * el navegador normalmente puede detectar el
         * contenido. Se mantiene JPEG como fallback.
         */

        $base64 =
            "data:" .
            $mime .
            ";base64," .
            base64_encode($img["imagenes"]);

        $miniaturas[] = $base64;
    }

    /*==================================================
    ÚLTIMA ACTUALIZACIÓN
    ==================================================*/

    $sqlFechaImg = "
    SELECT fecha_actualizado

    FROM imagenes

    WHERE idProducto = ?

    ORDER BY fecha_actualizado DESC

    LIMIT 1
    ";

    $stmtFecha = $conexion->prepare($sqlFechaImg);

    if ($stmtFecha) {

        $stmtFecha->bind_param(
            "i",
            $idProducto
        );

        $stmtFecha->execute();

        $resFecha =
            $stmtFecha
            ->get_result()
            ->fetch_assoc();

        if (
            $resFecha &&
            !empty($resFecha["fecha_actualizado"])
        ) {

            $fechaUltimaImagen = date(
                "d/m/Y H:i",
                strtotime(
                    $resFecha["fecha_actualizado"]
                )
            );
        }
    }
}

/*======================================================
DATOS PARA VISUALIZACIÓN
======================================================*/

$nombreProducto = htmlspecialchars(
    $producto["nombre"] ?? "",
    ENT_QUOTES,
    "UTF-8"
);

$codigoProducto = htmlspecialchars(
    $producto["codigo"] ?? "",
    ENT_QUOTES,
    "UTF-8"
);

$categoria = htmlspecialchars(
    $producto["categoria"] ?? "Sin categoría",
    ENT_QUOTES,
    "UTF-8"
);

$marca = htmlspecialchars(
    $producto["marca"] ?? "Sin marca",
    ENT_QUOTES,
    "UTF-8"
);

$proveedor = htmlspecialchars(
    $producto["proveedor"] ?? "Sin proveedor",
    ENT_QUOTES,
    "UTF-8"
);

$sucursal = htmlspecialchars(
    $producto["sucursal"] ?? "Sin sucursal",
    ENT_QUOTES,
    "UTF-8"
);

$descripcion = $producto["descripcion"] ?? "";

$tipoEscapado = htmlspecialchars(
    $tipoProducto,
    ENT_QUOTES,
    "UTF-8"
);

include "includes/head.php";
?>

<div class="d-flex">

    <?php include "includes/admin_sidebar.php"; ?>

    <div class="flex-grow-1">

        <?php include "includes/admin_navbar.php"; ?>

        <main class="container-fluid px-4 py-4">

            <!--==================================================
            ENCABEZADO
            ==================================================-->

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>

                    <div class="d-flex align-items-center gap-2 mb-1">

                        <span class="badge <?= $esServicio ? "bg-info text-dark" : "bg-primary"; ?> px-3 py-2">

                            <i class="bi <?= $esServicio ? "bi-tools" : "bi-box-seam"; ?> me-1"></i>

                            <?= $tipoEscapado; ?>

                        </span>

                        <?php if (!$esServicio && $nuevo): ?>

                            <span class="badge bg-success px-3 py-2">
                                <i class="bi bi-stars me-1"></i>
                                Nuevo
                            </span>

                        <?php endif; ?>

                    </div>

                    <h2 class="fw-bold mb-1">

                        <?= $nombreProducto; ?>

                    </h2>

                    <p class="text-muted mb-0">

                        <i class="bi bi-upc-scan me-1"></i>

                        Código:
                        <strong><?= $codigoProducto ?: "Sin código"; ?></strong>

                    </p>

                </div>

                <div class="d-flex flex-wrap gap-2">

                    <!-- EDITAR -->

                    <button
                        type="button"
                        class="btn btn-primary btn-editar"
                        data-id="<?= $idProducto; ?>">

                        <i class="bi bi-pencil-square me-1"></i>

                        Editar

                    </button>

                    <?php if (!$esServicio): ?>

                        <!-- EDITAR IMÁGENES -->

                        <button
                            type="button"
                            class="btn btn-warning text-dark"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditarImagenes"
                            data-id="<?= $idProducto; ?>">

                            <i class="bi bi-images me-1"></i>

                            Editar imágenes

                        </button>

                    <?php endif; ?>

                    <!-- VOLVER -->

                    <a
                        href="adm_lista_productos.php"
                        class="btn btn-secondary">

                        <i class="bi bi-arrow-left me-1"></i>

                        Volver

                    </a>

                </div>

            </div>

            <div
                id="detalleProducto"
                data-id-producto="<?= $idProducto; ?>">

                <?php if (!$esServicio): ?>

                    <!--==================================================
                    PRODUCTO
                    ==================================================-->

                    <div class="row g-4">

                        <!--==================================================
                        COLUMNA IZQUIERDA
                        ==================================================-->

                        <div class="col-xl-5">

                            <!-- GALERÍA -->

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-header bg-white border-0 pt-4 px-4">

                                    <h5 class="fw-bold mb-0">

                                        <i class="bi bi-images text-primary me-2"></i>

                                        Galería del producto

                                    </h5>

                                </div>

                                <div class="card-body p-4">

                                    <?php if (count($miniaturas) > 0): ?>

                                        <div
                                            class="border rounded-3 bg-light d-flex align-items-center justify-content-center mb-3"
                                            style="height:420px;">

                                            <img
                                                id="imagenPrincipal"
                                                src="<?= htmlspecialchars(
                                                            $miniaturas[0],
                                                            ENT_QUOTES,
                                                            "UTF-8"
                                                        ); ?>"
                                                class="img-fluid rounded"
                                                style="
                                                    width:100%;
                                                    height:100%;
                                                    object-fit:contain;
                                                "
                                                alt="<?= $nombreProducto; ?>">

                                        </div>

                                    <?php else: ?>

                                        <div
                                            class="border rounded-3 bg-light d-flex align-items-center justify-content-center mb-3"
                                            style="height:420px;">

                                            <img
                                                id="imagenPrincipal"
                                                src="<?= $imagenDefault; ?>"
                                                class="img-fluid rounded"
                                                style="
                                                    width:100%;
                                                    height:100%;
                                                    object-fit:contain;
                                                    opacity:.75;
                                                "
                                                alt="Imagen no disponible">

                                        </div>

                                    <?php endif; ?>

                                    <!-- MINIATURAS -->

                                    <div class="d-flex flex-wrap gap-2">

                                        <?php if (count($miniaturas) > 0): ?>

                                            <?php foreach ($miniaturas as $indice => $foto): ?>

                                                <img
                                                    src="<?= htmlspecialchars(
                                                                $foto,
                                                                ENT_QUOTES,
                                                                "UTF-8"
                                                            ); ?>"
                                                    class="img-thumbnail miniatura <?= $indice === 0 ? "border-primary" : ""; ?>"
                                                    style="
                                                        width:80px;
                                                        height:80px;
                                                        cursor:pointer;
                                                        object-fit:cover;
                                                    "
                                                    alt="Miniatura <?= $indice + 1; ?>">

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <img
                                                src="<?= $imagenDefault; ?>"
                                                class="img-thumbnail"
                                                style="
                                                    width:80px;
                                                    height:80px;
                                                    object-fit:cover;
                                                    opacity:.6;
                                                "
                                                alt="Sin imagen">

                                        <?php endif; ?>

                                    </div>

                                    <div class="border-top mt-4 pt-3">

                                        <small class="text-muted">

                                            <i class="bi bi-clock-history me-1"></i>

                                            Última actualización de imágenes:

                                            <strong>

                                                <?= $fechaUltimaImagen ?: "Sin actualizaciones"; ?>

                                            </strong>

                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!--==================================================
                        COLUMNA DERECHA
                        ==================================================-->

                        <div class="col-xl-7">

                            <!--==================================================
                            KPIs
                            ==================================================-->

                            <div class="row g-3 mb-4">

                                <!-- PRECIO -->

                                <div class="col-md-6">

                                    <div class="card border-0 shadow-sm h-100">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between align-items-start">

                                                <div>

                                                    <div class="text-muted small mb-1">
                                                        Precio de venta
                                                    </div>

                                                    <h3 class="fw-bold text-success mb-0">

                                                        S/
                                                        <?= number_format(
                                                            $precio,
                                                            2
                                                        ); ?>

                                                    </h3>

                                                </div>

                                                <div class="rounded-circle bg-success-subtle p-3">

                                                    <i class="bi bi-cash-stack text-success fs-4"></i>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <!-- STOCK -->

                                <div class="col-md-6">

                                    <div class="card border-0 shadow-sm h-100">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between align-items-start">

                                                <div>

                                                    <div class="text-muted small mb-1">
                                                        Stock disponible
                                                    </div>

                                                    <h3 class="fw-bold mb-0">

                                                        <?= number_format(
                                                            $stock,
                                                            0
                                                        ); ?>

                                                    </h3>

                                                </div>

                                                <div class="rounded-circle bg-primary-subtle p-3">

                                                    <i class="bi bi-box-seam text-primary fs-4"></i>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <!-- OFERTA -->

                                <div class="col-md-6">

                                    <div class="card border-0 shadow-sm h-100">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between align-items-start">

                                                <div>

                                                    <div class="text-muted small mb-2">
                                                        Estado de oferta
                                                    </div>

                                                    <?php if ($oferta): ?>

                                                        <span class="badge bg-danger fs-6 px-3 py-2">

                                                            <i class="bi bi-percent me-1"></i>

                                                            Oferta activa

                                                        </span>

                                                    <?php else: ?>

                                                        <span class="badge bg-secondary fs-6 px-3 py-2">

                                                            Sin oferta

                                                        </span>

                                                    <?php endif; ?>

                                                </div>

                                                <div class="rounded-circle bg-danger-subtle p-3">

                                                    <i class="bi bi-tag text-danger fs-4"></i>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <!-- DESTACADO -->

                                <div class="col-md-6">

                                    <div class="card border-0 shadow-sm h-100">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between align-items-start">

                                                <div>

                                                    <div class="text-muted small mb-2">
                                                        Producto destacado
                                                    </div>

                                                    <?php if ($destacado): ?>

                                                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">

                                                            <i class="bi bi-star-fill me-1"></i>

                                                            Destacado

                                                        </span>

                                                    <?php else: ?>

                                                        <span class="badge bg-secondary fs-6 px-3 py-2">

                                                            No destacado

                                                        </span>

                                                    <?php endif; ?>

                                                </div>

                                                <div class="rounded-circle bg-warning-subtle p-3">

                                                    <i class="bi bi-star-fill text-warning fs-4"></i>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!--==================================================
                            INFORMACIÓN GENERAL
                            ==================================================-->

                            <div class="card border-0 shadow-sm mb-4">

                                <div class="card-header bg-white py-3">

                                    <h5 class="fw-bold mb-0">

                                        <i class="bi bi-info-circle text-primary me-2"></i>

                                        Información general

                                    </h5>

                                </div>

                                <div class="card-body">

                                    <div class="row g-3">

                                        <!-- CÓDIGO -->

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3 h-100">

                                                <div class="text-muted small">
                                                    Código
                                                </div>

                                                <div class="fw-semibold mt-1">

                                                    <?= $codigoProducto ?: "Sin código"; ?>

                                                </div>

                                            </div>

                                        </div>

                                        <!-- CATEGORÍA -->

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3 h-100">

                                                <div class="text-muted small">
                                                    Categoría
                                                </div>

                                                <div class="fw-semibold mt-1">

                                                    <?= $categoria; ?>

                                                </div>

                                            </div>

                                        </div>

                                        <!-- MARCA -->

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3 h-100">

                                                <div class="text-muted small">
                                                    Marca
                                                </div>

                                                <div class="fw-semibold mt-1">

                                                    <?= $marca; ?>

                                                </div>

                                            </div>

                                        </div>

                                        <!-- PROVEEDOR -->

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3 h-100">

                                                <div class="text-muted small">
                                                    Proveedor
                                                </div>

                                                <div class="fw-semibold mt-1">

                                                    <?= $proveedor; ?>

                                                </div>

                                            </div>

                                        </div>

                                        <!-- SUCURSAL -->

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3 h-100">

                                                <div class="text-muted small">
                                                    Sucursal
                                                </div>

                                                <div class="fw-semibold mt-1">

                                                    <?= $sucursal; ?>

                                                </div>

                                            </div>

                                        </div>

                                        <!-- DESCUENTO -->

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3 h-100">

                                                <div class="text-muted small">
                                                    Descuento
                                                </div>

                                                <div class="fw-semibold mt-1">

                                                    <?php if ($descuento > 0): ?>

                                                        <span class="text-danger">

                                                            <?= $descuento; ?>%

                                                        </span>

                                                    <?php else: ?>

                                                        <span class="text-muted">
                                                            Sin descuento
                                                        </span>

                                                    <?php endif; ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!--==================================================
                    INFORMACIÓN ECONÓMICA
                    ==================================================-->

                    <div class="row g-4 mt-1">

                        <!-- PRECIOS -->

                        <div class="col-lg-6">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-header bg-white py-3">

                                    <h5 class="fw-bold mb-0">

                                        <i class="bi bi-currency-dollar text-success me-2"></i>

                                        Información económica

                                    </h5>

                                </div>

                                <div class="card-body">

                                    <div class="row g-3">

                                        <div class="col-6">

                                            <div class="border rounded-3 p-3">

                                                <div class="text-muted small">
                                                    Precio actual
                                                </div>

                                                <div class="fw-bold text-success fs-5">

                                                    S/
                                                    <?= number_format(
                                                        $precio,
                                                        2
                                                    ); ?>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-6">

                                            <div class="border rounded-3 p-3">

                                                <div class="text-muted small">
                                                    Precio anterior
                                                </div>

                                                <div class="fw-bold fs-5">

                                                    S/
                                                    <?= number_format(
                                                        $precioAnterior,
                                                        2
                                                    ); ?>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-6">

                                            <div class="border rounded-3 p-3">

                                                <div class="text-muted small">
                                                    Costo de compra
                                                </div>

                                                <div class="fw-bold fs-5">

                                                    S/
                                                    <?= number_format(
                                                        $costoCompra,
                                                        2
                                                    ); ?>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-6">

                                            <div class="border rounded-3 p-3">

                                                <div class="text-muted small">
                                                    Ganancia estimada
                                                </div>

                                                <div class="fw-bold text-primary fs-5">

                                                    S/
                                                    <?= number_format(
                                                        $ganancia,
                                                        2
                                                    ); ?>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-12">

                                            <div class="alert alert-primary mb-0">

                                                <div class="d-flex justify-content-between">

                                                    <span>
                                                        <i class="bi bi-graph-up me-1"></i>
                                                        Margen sobre costo
                                                    </span>

                                                    <strong>

                                                        <?= number_format(
                                                            $margen,
                                                            2
                                                        ); ?>%

                                                    </strong>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!--==================================================
                        IMPUESTO
                        ==================================================-->

                        <div class="col-lg-6">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-header bg-white py-3">

                                    <h5 class="fw-bold mb-0">

                                        <i class="bi bi-receipt text-primary me-2"></i>

                                        Información del impuesto

                                    </h5>

                                </div>

                                <div class="card-body">

                                    <?php if ($aplicaImpuesto === 1): ?>

                                        <div class="alert alert-success d-flex align-items-center">

                                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>

                                            <div>

                                                <strong>IGV aplicado</strong>

                                                <div class="small">

                                                    Este producto está sujeto al IGV.

                                                </div>

                                            </div>

                                        </div>

                                        <div class="row g-3">

                                            <div class="col-4">

                                                <div class="border rounded-3 p-3 text-center">

                                                    <div class="text-muted small">
                                                        IGV
                                                    </div>

                                                    <div class="fw-bold fs-5">

                                                        <?= $porcentajeIGV; ?>%

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-4">

                                                <div class="border rounded-3 p-3 text-center">

                                                    <div class="text-muted small">
                                                        Precio sin IGV
                                                    </div>

                                                    <div class="fw-bold fs-5">

                                                        S/
                                                        <?= number_format(
                                                            $precioSinIGV,
                                                            2
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-4">

                                                <div class="border rounded-3 p-3 text-center">

                                                    <div class="text-muted small">
                                                        IGV
                                                    </div>

                                                    <div class="fw-bold text-primary fs-5">

                                                        S/
                                                        <?= number_format(
                                                            $igvCalculado,
                                                            2
                                                        ); ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="mt-3 p-3 rounded-3 bg-light">

                                            <div class="d-flex justify-content-between">

                                                <span>
                                                    Precio final con IGV
                                                </span>

                                                <strong class="text-success">

                                                    S/
                                                    <?= number_format(
                                                        $precioConIGV,
                                                        2
                                                    ); ?>

                                                </strong>

                                            </div>

                                        </div>

                                    <?php else: ?>

                                        <div class="alert alert-secondary d-flex align-items-center mb-0">

                                            <i class="bi bi-x-circle-fill fs-4 me-3"></i>

                                            <div>

                                                <strong>Sin impuesto aplicado</strong>

                                                <div class="small">

                                                    Este producto no tiene IGV aplicado.

                                                </div>

                                            </div>

                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!--==================================================
                    ESTADO COMERCIAL
                    ==================================================-->

                    <div class="card border-0 shadow-sm mt-4">

                        <div class="card-header bg-white py-3">

                            <h5 class="fw-bold mb-0">

                                <i class="bi bi-toggles text-primary me-2"></i>

                                Estado comercial

                            </h5>

                        </div>

                        <div class="card-body">

                            <div class="row g-3">

                                <div class="col-md-3">

                                    <div class="border rounded-3 p-3 text-center h-100">

                                        <i class="bi bi-tag fs-3 <?= $oferta ? "text-danger" : "text-muted"; ?>"></i>

                                        <div class="fw-semibold mt-2">
                                            Oferta
                                        </div>

                                        <span class="badge <?= $oferta ? "bg-danger" : "bg-secondary"; ?> mt-1">

                                            <?= $oferta ? "Activa" : "No"; ?>

                                        </span>

                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <div class="border rounded-3 p-3 text-center h-100">

                                        <i class="bi bi-star-fill fs-3 <?= $destacado ? "text-warning" : "text-muted"; ?>"></i>

                                        <div class="fw-semibold mt-2">
                                            Destacado
                                        </div>

                                        <span class="badge <?= $destacado ? "bg-warning text-dark" : "bg-secondary"; ?> mt-1">

                                            <?= $destacado ? "Sí" : "No"; ?>

                                        </span>

                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <div class="border rounded-3 p-3 text-center h-100">

                                        <i class="bi bi-stars fs-3 <?= $nuevo ? "text-success" : "text-muted"; ?>"></i>

                                        <div class="fw-semibold mt-2">
                                            Producto nuevo
                                        </div>

                                        <span class="badge <?= $nuevo ? "bg-success" : "bg-secondary"; ?> mt-1">

                                            <?= $nuevo ? "Sí" : "No"; ?>

                                        </span>

                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <div class="border rounded-3 p-3 text-center h-100">

                                        <i class="bi bi-truck fs-3 <?= $envioGratis ? "text-primary" : "text-muted"; ?>"></i>

                                        <div class="fw-semibold mt-2">
                                            Envío gratis
                                        </div>

                                        <span class="badge <?= $envioGratis ? "bg-primary" : "bg-secondary"; ?> mt-1">

                                            <?= $envioGratis ? "Sí" : "No"; ?>

                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!--==================================================
                    DESCRIPCIÓN
                    ==================================================-->

                    <div class="card border-0 shadow-sm mt-4">

                        <div class="card-header bg-white py-3">

                            <h5 class="fw-bold mb-0">

                                <i class="bi bi-card-text text-primary me-2"></i>

                                Descripción

                            </h5>

                        </div>

                        <div class="card-body">

                            <?php if (trim($descripcion) !== ""): ?>

                                <div class="bg-light rounded-3 p-4">

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $descripcion,
                                            ENT_QUOTES,
                                            "UTF-8"
                                        )
                                    ); ?>

                                </div>

                            <?php else: ?>

                                <div class="text-center text-muted py-4">

                                    <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>

                                    Este producto no tiene descripción registrada.

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php else: ?>

                    <!--==================================================
                    SERVICIO
                    ==================================================-->

                    <div class="row g-4">

                        <div class="col-lg-8 mx-auto">

                            <!-- INFORMACIÓN PRINCIPAL -->

                            <div class="card border-0 shadow-sm mb-4">

                                <div class="card-header bg-white py-3">

                                    <h5 class="fw-bold mb-0">

                                        <i class="bi bi-tools text-info me-2"></i>

                                        Información del servicio

                                    </h5>

                                </div>

                                <div class="card-body">

                                    <div class="row g-4">

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3">

                                                <div class="text-muted small">
                                                    Código
                                                </div>

                                                <div class="fw-bold mt-1">

                                                    <?= $codigoProducto ?: "Sin código"; ?>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3">

                                                <div class="text-muted small">
                                                    Nombre
                                                </div>

                                                <div class="fw-bold mt-1">

                                                    <?= $nombreProducto; ?>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="bg-success-subtle rounded-3 p-3">

                                                <div class="text-muted small">
                                                    Precio
                                                </div>

                                                <div class="fw-bold text-success fs-3 mt-1">

                                                    S/
                                                    <?= number_format(
                                                        $precio,
                                                        2
                                                    ); ?>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <div class="bg-light rounded-3 p-3">

                                                <div class="text-muted small">
                                                    Sucursal
                                                </div>

                                                <div class="fw-bold mt-1">

                                                    <?= $sucursal; ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- IMPUESTO DEL SERVICIO -->

                            <div class="card border-0 shadow-sm mb-4">

                                <div class="card-header bg-white py-3">

                                    <h5 class="fw-bold mb-0">

                                        <i class="bi bi-receipt text-primary me-2"></i>

                                        Información del impuesto

                                    </h5>

                                </div>

                                <div class="card-body">

                                    <?php if ($aplicaImpuesto === 1): ?>

                                        <div class="alert alert-success mb-0">

                                            <i class="bi bi-check-circle-fill me-2"></i>

                                            <strong>IGV aplicado</strong>

                                            <span class="ms-2">

                                                <?= $porcentajeIGV; ?>%

                                            </span>

                                            <hr>

                                            Precio sin IGV:

                                            <strong>
                                                S/
                                                <?= number_format(
                                                    $precioSinIGV,
                                                    2
                                                ); ?>
                                            </strong>

                                            <br>

                                            IGV:

                                            <strong>
                                                S/
                                                <?= number_format(
                                                    $igvCalculado,
                                                    2
                                                ); ?>
                                            </strong>

                                            <br>

                                            Precio final:

                                            <strong>
                                                S/
                                                <?= number_format(
                                                    $precioConIGV,
                                                    2
                                                ); ?>
                                            </strong>

                                        </div>

                                    <?php else: ?>

                                        <div class="alert alert-secondary mb-0">

                                            <i class="bi bi-x-circle-fill me-2"></i>

                                            <strong>
                                                Sin impuesto aplicado
                                            </strong>

                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>

                            <!-- DESCRIPCIÓN -->

                            <div class="card border-0 shadow-sm">

                                <div class="card-header bg-white py-3">

                                    <h5 class="fw-bold mb-0">

                                        <i class="bi bi-card-text text-primary me-2"></i>

                                        Detalle del servicio

                                    </h5>

                                </div>

                                <div class="card-body">

                                    <?php if (trim($descripcion) !== ""): ?>

                                        <div class="bg-light rounded-3 p-4">

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $descripcion,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                )
                                            ); ?>

                                        </div>

                                    <?php else: ?>

                                        <div class="text-center text-muted py-4">

                                            <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>

                                            Este servicio no tiene descripción registrada.

                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>

</div>

<?php require "modal/modal_adm_editar_producto.php"; ?>

<?php if (!$esServicio): ?>

    <?php include "modal/modalEditarImagenes.php"; ?>

<?php endif; ?>

<?php require "modal/modal_adm_eliminar_prodcuto.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/adm_lista_productos.js"></script>

<script src="js/editar_imagenes.js"></script>

<script src="js/menu.js"></script>

<script>
    /*======================================================
CAMBIO DE IMAGEN PRINCIPAL
======================================================*/

    document.addEventListener("click", function(e) {

        const miniatura = e.target.closest(".miniatura");

        if (!miniatura) {
            return;
        }

        const principal = document.getElementById(
            "imagenPrincipal"
        );

        if (!principal) {
            return;
        }

        principal.src = miniatura.src;

        document
            .querySelectorAll(".miniatura")
            .forEach(function(item) {

                item.classList.remove(
                    "border-primary"
                );

            });

        miniatura.classList.add(
            "border-primary"
        );

    });
</script>

</body>

</html>