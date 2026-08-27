<?php
//Toda esta parte es de includes/obtener_carrito_pagina.php
// ======================================================
// INICIAR SESIÓN
// ======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . "/../controladores/conexion.php";
require_once __DIR__ . "/../controladores/token_carrito.php";

/*
|--------------------------------------------------------------------------
| TOKEN DEL CARRITO
|--------------------------------------------------------------------------
*/

$token = obtenerTokenCarrito();

/*
|--------------------------------------------------------------------------
| CLIENTE LOGUEADO
|--------------------------------------------------------------------------
*/

$idCliente = $_SESSION["idCliente"] ?? 0;

/*
|--------------------------------------------------------------------------
| CONSULTA
|--------------------------------------------------------------------------
*/

if ($idCliente > 0) {

    $sql = "SELECT

                c.idCarrito,
                c.idProducto,
                c.cantidad,
                c.precio AS precio_carrito,

                p.nombre,
                p.codigo,
                p.precio AS precio_actual,
                p.precio_anterior,
                p.descuento,
                p.oferta,
                p.stock,
                p.descripcion,
                p.Eliminado,

                ca.nombre AS categoria,
                m.nombre AS marca,

                (
                    SELECT i.id_imagen
                    FROM imagenes i
                    WHERE i.idProducto = p.idProducto
                    ORDER BY i.orden ASC
                    LIMIT 1
                ) AS imagen

            FROM carrito_online c

            INNER JOIN producto p
                ON p.idProducto = c.idProducto

            LEFT JOIN categorias ca
                ON ca.id_categorias = p.id_categorias

            LEFT JOIN marcas m
                ON m.id_marca = p.id_marca

            WHERE c.idCliente = ?
            AND c.estado = 'pendiente'

            ORDER BY c.idCarrito DESC";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idCliente
    );
} else {

    $sql = "SELECT

                c.idCarrito,
                c.idProducto,
                c.cantidad,
                c.precio AS precio_carrito,

                p.nombre,
                p.codigo,
                p.precio AS precio_actual,
                p.precio_anterior,
                p.descuento,
                p.oferta,
                p.stock,
                p.descripcion,
                p.Eliminado,

                ca.nombre AS categoria,
                m.nombre AS marca,

                (
                    SELECT i.id_imagen
                    FROM imagenes i
                    WHERE i.idProducto = p.idProducto
                    ORDER BY i.orden ASC
                    LIMIT 1
                ) AS imagen

            FROM carrito_online c

            INNER JOIN producto p
                ON p.idProducto = c.idProducto

            LEFT JOIN categorias ca
                ON ca.id_categorias = p.id_categorias

            LEFT JOIN marcas m
                ON m.id_marca = p.id_marca

            WHERE c.token = ?
            AND c.estado = 'pendiente'

            ORDER BY c.idCarrito DESC";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $token
    );
}

mysqli_stmt_execute($stmt);

$productos = mysqli_stmt_get_result($stmt);

/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$totalGeneral = 0;

$cantidadProductos = mysqli_num_rows($productos);
?>
<?php

if ($cantidadProductos == 0) {
?>

    <div class="text-center py-5">

        <i class="bi bi-cart-x display-1 text-secondary"></i>

        <h3 class="mt-3">

            Tu carrito está vacío

        </h3>

        <p class="text-muted">

            Aún no has agregado productos.

        </p>

        <a href="tienda.php"
            class="btn btn-primary">

            <i class="bi bi-shop"></i>

            Ir a la tienda

        </a>

    </div>

<?php
    return;
}
?>
<?php while ($producto = mysqli_fetch_assoc($productos)): ?>

    <?php

    $precioActual = floatval($producto["precio_actual"]);

    $precioCarrito = floatval($producto["precio_carrito"]);
    $precioCambio = abs($precioActual - $precioCarrito) > 0.001;
    $subtotal = $producto["cantidad"] * $precioActual;

    $totalGeneral += $subtotal;

    ?>

    <div class="card border-0 shadow-sm mb-3">

        <div class="card-body">

            <div class="row align-items-center">
                <div class="col-lg-2 col-md-3 col-4">

                    <?php if (!empty($producto["imagen"])): ?>

                        <img
                            src="mostrar_imagen.php?id=<?= $producto["idProducto"] ?>&img=<?= $producto["imagen"] ?>"
                            class="img-fluid rounded border"
                            alt="<?= htmlspecialchars($producto["nombre"]) ?>">

                    <?php else: ?>

                        <img
                            src="assets/img/sin-imagen.png"
                            class="img-fluid rounded border"
                            alt="Sin imagen">

                    <?php endif; ?>

                </div>
                <div class="col-lg-5 col-md-9 col-8">

                    <h5 class="fw-bold mb-2">

                        <?= htmlspecialchars($producto["nombre"]) ?>

                    </h5>

                    <div class="mb-1">

                        <span class="badge bg-primary">

                            <?= htmlspecialchars($producto["marca"]) ?>

                        </span>

                        <span class="badge bg-secondary">

                            <?= htmlspecialchars($producto["categoria"]) ?>

                        </span>

                    </div>

                    <small class="text-muted">

                        Código:

                        <strong>

                            <?= htmlspecialchars($producto["codigo"]) ?>

                        </strong>

                    </small>

                    <br>

                    <small class="text-muted">

                        Stock disponible:

                        <strong>

                            <?= $producto["stock"] ?>

                        </strong>

                    </small>
                    <?php if ($producto["oferta"] == 1): ?>

                        <div class="mt-2">

                            <span class="badge bg-danger">

                                -<?= intval($producto["descuento"]) ?>%

                            </span>

                        </div>

                    <?php endif; ?>
                    <div class="mt-3">

                        <?php
                        $precioActual = floatval($producto["precio_actual"]);
                        $precioAnterior = floatval($producto["precio_anterior"]);
                        ?>

                        <?php if ($precioAnterior > $precioActual): ?>

                            <div>

                                <small class="text-decoration-line-through text-muted">

                                    S/ <?= number_format($precioAnterior, 2) ?>

                                </small>

                            </div>

                        <?php endif; ?>

                        <div class="fs-4 fw-bold text-success">

                            S/ <?= number_format($precioActual, 2) ?>

                        </div>

                        <?php if ($precioCambio): ?>

                            <div class="alert alert-warning py-2 mt-2 mb-0">

                                <small>

                                    <i class="bi bi-arrow-repeat"></i>

                                    El precio se actualizó.

                                </small>

                            </div>

                        <?php endif; ?>

                    </div>
                </div>
                <!--=========================================
=            CANTIDAD
==========================================-->

                <div class="col-lg-2 col-md-4 col-6 mt-4 mt-lg-0">

                    <label class="form-label fw-semibold">

                        Cantidad

                    </label>

                    <div class="input-group">

                        <button
                            class="btn btn-outline-secondary btnRestar"
                            data-id="<?= $producto["idCarrito"]; ?>">

                            <i class="bi bi-dash-lg"></i>

                        </button>

                        <input
                            type="text"
                            class="form-control text-center fw-bold"
                            value="<?= $producto["cantidad"]; ?>"
                            readonly>

                        <button
                            class="btn btn-outline-secondary btnSumar"
                            data-id="<?= $producto["idCarrito"]; ?>">

                            <i class="bi bi-plus-lg"></i>

                        </button>

                    </div>

                </div>
                <!--=========================================
=            SUBTOTAL
==========================================-->

                <div class="col-lg-2 col-md-4 col-6 mt-4 mt-lg-0 text-center">

                    <label class="form-label fw-semibold">

                        Subtotal

                    </label>

                    <h5 class="text-primary fw-bold">

                        S/ <?= number_format($subtotal, 2); ?>

                    </h5>

                </div>
                <!--=========================================
=            ELIMINAR
==========================================-->

                <div class="col-lg-1 col-md-4 col-12 mt-4 mt-lg-0 text-center">

                    <button
                        class="btn btn-outline-danger btnEliminar"
                        data-id="<?= $producto["idCarrito"]; ?>">

                        <i class="bi bi-trash-fill"></i>

                    </button>

                </div>
            </div>

        </div>

    </div>

<?php endwhile; ?>
<?php

/*
|--------------------------------------------------------------------------
| RESUMEN DEL CARRITO
|--------------------------------------------------------------------------
*/

$cantidadItems = 0;

mysqli_data_seek($productos, 0);

while ($fila = mysqli_fetch_assoc($productos)) {

    $cantidadItems += intval($fila["cantidad"]);
}

/*
|--------------------------------------------------------------------------
| TOTALES
|--------------------------------------------------------------------------
*/

$subtotal = $totalGeneral;

$igv = 0;

/*
|--------------------------------------------------------------------------
| Si más adelante decides aplicar IGV:
|
| $igv = $subtotal * 0.18;
|--------------------------------------------------------------------------
*/

$total = $subtotal + $igv;

?>
<!--<div class="card border-0 bg-light mt-3">

    <div class="card-body">

        <div class="row text-center">

            <div class="col-md-4">

                <small class="text-muted">

                    Productos

                </small>

                <h5 class="fw-bold">

                    <?= $cantidadItems ?>

                </h5>

            </div>

            <div class="col-md-4">

                <small class="text-muted">

                    Subtotal

                </small>

                <h5 class="fw-bold text-primary">

                    S/ <?= number_format($subtotal, 2) ?>

                </h5>

            </div>

            <div class="col-md-4">

                <small class="text-muted">

                    Total

                </small>

                <h4 class="fw-bold text-success">

                    S/ <?= number_format($total, 2) ?>

                </h4>

            </div>

        </div>

    </div>

</div>-->