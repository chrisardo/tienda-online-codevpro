<?php
//Toda esta parte es de ajax/obtener_carrito.php
session_start();

require_once "../controladores/conexion.php";
require_once "../controladores/token_carrito.php";

$idCliente = isset($_SESSION["idCliente"]) ? intval($_SESSION["idCliente"]) : 0;
$token = obtenerTokenCarrito();

/*
|--------------------------------------------------------------------------
| CONSULTA
|--------------------------------------------------------------------------
*/

if ($idCliente > 0) {

    $sql = "SELECT

                c.idCarrito,
                c.cantidad,
                c.precio,
                p.precio AS precio_actual,
                p.idProducto,
                p.nombre,
                p.stock,
                p.codigo,
                p.oferta,
                p.descuento,
                p.precio_anterior,

                ca.nombre AS categoria,
                m.nombre AS marca

            FROM carrito_online c

            INNER JOIN producto p
                ON p.idProducto = c.idProducto

            INNER JOIN categorias ca
                ON ca.id_categorias = p.id_categorias

            INNER JOIN marcas m
                ON m.id_marca = p.id_marca

            WHERE c.idCliente = ?
            AND c.estado='pendiente'

            ORDER BY c.idCarrito DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idCliente);
} else {

    $sql = "SELECT

                c.idCarrito,
                c.cantidad,
                c.precio,

                p.idProducto,
                p.nombre,
                p.stock,
                p.codigo,
                p.oferta,
                p.descuento,
                p.precio_anterior,

                ca.nombre AS categoria,
                m.nombre AS marca

            FROM carrito_online c

            INNER JOIN producto p
                ON p.idProducto = c.idProducto

            INNER JOIN categorias ca
                ON ca.id_categorias = p.id_categorias

            INNER JOIN marcas m
                ON m.id_marca = p.id_marca

            WHERE c.token = ?
            AND c.estado='pendiente'

            ORDER BY c.idCarrito DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
}

mysqli_stmt_execute($stmt);

$carrito = mysqli_stmt_get_result($stmt);

$total = 0;
?>

<?php if (mysqli_num_rows($carrito) > 0) { ?>

    <?php while ($item = mysqli_fetch_assoc($carrito)) { ?>

        <?php

        $subtotal = $item["cantidad"] * $item["precio"];

        $total += $subtotal;

        ?>

        <div class="border-bottom p-3">

            <div class="row align-items-center">

                <div class="col-3">

                    <img
                        src="mostrar_imagen.php?id=<?= $item['idProducto']; ?>"
                        class="img-fluid rounded">

                </div>

                <div class="col-9">

                    <div class="fw-bold">

                        <?= htmlspecialchars($item["nombre"]); ?>

                    </div>

                    <small class="text-muted">

                        <?= htmlspecialchars($item["marca"]); ?>

                    </small>

                    <br>

                    <small class="text-primary">

                        <?= htmlspecialchars($item["categoria"]); ?>

                    </small>

                    <div class="mt-2">

                        <?php if ($item["oferta"] == 1) { ?>

                            <small class="text-decoration-line-through text-secondary">

                                S/ <?= number_format($item["precio_anterior"], 2); ?>

                            </small>

                            <span class="badge bg-danger">

                                -<?= $item["descuento"]; ?>%

                            </span>

                            <br>

                        <?php } ?>

                        <span class="fw-bold text-primary fs-5">

                            S/ <?= number_format($item["precio"], 2); ?>

                        </span>

                    </div>

                    <div class="mt-2">

                        <div class="btn-group">

                            <button
                                class="btn btn-outline-secondary btnRestar"
                                data-id="<?= $item["idCarrito"]; ?>">

                                <i class="bi bi-dash"></i>

                            </button>

                            <span class="btn btn-light">

                                <?= $item["cantidad"]; ?>

                            </span>

                            <button
                                class="btn btn-outline-secondary btnSumar"
                                data-id="<?= $item["idCarrito"]; ?>">

                                <i class="bi bi-plus"></i>

                            </button>

                        </div>

                        <button
                            class="btn btn-outline-danger btnEliminar float-end"
                            data-id="<?= $item["idCarrito"]; ?>">

                            <i class="bi bi-trash"></i>

                        </button>

                    </div>

                    <div class="mt-2">

                        <small>

                            Subtotal

                        </small>

                        <div class="fw-bold">

                            S/ <?= number_format($subtotal, 2); ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    <?php } ?>

    <div class="p-3">

        <div class="d-flex justify-content-between fs-5 fw-bold">

            <span>Total</span>

            <span>

                S/ <?= number_format($total, 2); ?>

            </span>

        </div>

        <div class="d-grid gap-2 mt-3">

            <a
                href="carrito.php"
                class="btn btn-outline-primary">

                Ver carrito

            </a>

            <?php if (isset($_SESSION["idCliente"])) { ?>

                <a
                    href="checkout.php"
                    class="btn btn-success w-100">

                    <i class="bi bi-credit-card"></i>

                    Finalizar compra

                </a>

            <?php } else { ?>

                <button

                    class="btn btn-success w-100"

                    id="btnLoginCheckout">

                    <i class="bi bi-credit-card"></i>

                    Finalizar compra

                </button>

            <?php } ?>

        </div>

    </div>

<?php } else { ?>

    <div class="text-center py-5">

        <i class="bi bi-cart-x display-1 text-secondary"></i>

        <h5 class="mt-3">

            Tu carrito está vacío

        </h5>

        <a
            href="tienda.php"
            class="btn btn-primary mt-3">

            Ir a comprar

        </a>

    </div>

<?php } ?>