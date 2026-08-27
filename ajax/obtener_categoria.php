<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_categoria.php
//======================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);
$idCategoria = intval($_POST["idCategoria"] ?? 0);

/*======================================================
=            CATEGORIA
======================================================*/

$sql = "

SELECT *
FROM categorias
WHERE id_categorias = ?
AND id_user = ?
AND Eliminado = 0

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idCategoria,
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($resultado->num_rows == 0) {

    echo json_encode([
        "estado" => false
    ]);

    exit;
}

$categoria = mysqli_fetch_assoc($resultado);

/*======================================================
=            TOTAL PRODUCTOS
======================================================*/

$sqlProductos = "

SELECT COUNT(*) total

FROM producto

WHERE id_categorias = ?
AND Eliminado = 0

";

$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);

mysqli_stmt_bind_param(
    $stmtProductos,
    "i",
    $idCategoria
);

mysqli_stmt_execute($stmtProductos);

$totalProductos =
    mysqli_stmt_get_result($stmtProductos)
        ->fetch_assoc()["total"];

/*======================================================
=            TOTAL VENDIDOS
======================================================*/

$sqlVendidos = "

SELECT
COALESCE(SUM(cpv.cantidad_total),0) total

FROM cantidad_producto_vendido cpv

INNER JOIN producto p
ON p.idProducto = cpv.idProducto

WHERE p.id_categorias = ?

";

$stmtVendidos = mysqli_prepare(
    $conexion,
    $sqlVendidos
);

mysqli_stmt_bind_param(
    $stmtVendidos,
    "i",
    $idCategoria
);

mysqli_stmt_execute($stmtVendidos);

$totalVendidos =
    mysqli_stmt_get_result($stmtVendidos)
        ->fetch_assoc()["total"];

/*======================================================
=            ÚLTIMOS PRODUCTOS
======================================================*/

$sqlUltimos = "

SELECT
idProducto,
nombre,
precio,
stock

FROM producto

WHERE id_categorias = ?
AND Eliminado = 0

ORDER BY idProducto DESC

LIMIT 5

";

$stmtUltimos = mysqli_prepare(
    $conexion,
    $sqlUltimos
);

mysqli_stmt_bind_param(
    $stmtUltimos,
    "i",
    $idCategoria
);

mysqli_stmt_execute($stmtUltimos);

$ultimosProductos =
    mysqli_stmt_get_result($stmtUltimos);

/*======================================================
=            IMAGEN
======================================================*/

$imagen = "img/logo.png";

if (!empty($categoria["imagen"])) {

    $imagen =
        "data:image/jpeg;base64," .
        base64_encode($categoria["imagen"]);
}

/*======================================================
=            HTML
======================================================*/

ob_start();
?>

<div class="container-fluid">

    <div class="row g-4">

        <!-- IMAGEN -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center">

                    <img
                        src="<?= $imagen ?>"
                        class="img-fluid rounded shadow-sm border"
                        style="
                            width:100%;
                            height:280px;
                            object-fit:cover;
                        ">

                    <h4 class="mt-3 fw-bold">

                        <?= htmlspecialchars($categoria["nombre"]) ?>

                    </h4>

                    <span class="badge bg-primary">

                        Categoría

                    </span>

                </div>

            </div>

        </div>

        <!-- INFORMACIÓN -->

        <div class="col-lg-8">

            <!-- KPI -->

            <div class="row g-3 mb-4">

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <i class="bi bi-box-seam fs-1 text-primary"></i>

                            <h3 class="fw-bold mt-2">

                                <?= $totalProductos ?>

                            </h3>

                            <small class="text-muted">

                                Productos

                            </small>

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <i class="bi bi-cart-check fs-1 text-success"></i>

                            <h3 class="fw-bold mt-2">

                                <?= $totalVendidos ?>

                            </h3>

                            <small class="text-muted">

                                Vendidos

                            </small>

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <i class="bi bi-tags-fill fs-1 text-warning"></i>

                            <h3 class="fw-bold mt-2">

                                #<?= $categoria["id_categorias"] ?>

                            </h3>

                            <small class="text-muted">

                                ID Categoría

                            </small>

                        </div>

                    </div>

                </div>

            </div>

            <!-- INFO -->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="mb-0">

                        <i class="bi bi-info-circle me-2"></i>

                        Información General

                    </h5>

                </div>

                <div class="card-body">

                    <table class="table table-borderless">

                        <tr>

                            <th width="180">

                                Nombre

                            </th>

                            <td>

                                <?= htmlspecialchars($categoria["nombre"]) ?>

                            </td>

                        </tr>

                        <tr>

                            <th>ID</th>

                            <td>

                                <?= $categoria["id_categorias"] ?>

                            </td>

                        </tr>

                        <tr>

                            <th>Total Productos</th>

                            <td>

                                <?= $totalProductos ?>

                            </td>

                        </tr>

                        <tr>

                            <th>Total Vendidos</th>

                            <td>

                                <?= $totalVendidos ?>

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <!-- PRODUCTOS -->

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                <i class="bi bi-box-seam me-2"></i>

                Últimos Productos

            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Producto</th>

                            <th>Precio</th>

                            <th>Stock</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($prod = mysqli_fetch_assoc($ultimosProductos)): ?>

                            <tr>

                                <td>

                                    #<?= $prod["idProducto"] ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($prod["nombre"]) ?>

                                </td>

                                <td>

                                    S/ <?= number_format($prod["precio"], 2) ?>

                                </td>

                                <td>

                                    <?= $prod["stock"] ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php

$html = ob_get_clean();

echo json_encode([

    "estado" => true,

    "html" => $html

]);
