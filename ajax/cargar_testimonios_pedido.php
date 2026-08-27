<?php
//======================================================
// CoDevPro Technology
// ajax/cargar_testimonios_pedido.php
//======================================================

session_start();

if (!isset($_SESSION["idCliente"])) {
    exit;
}

require_once "../controladores/conexion.php";

/*======================================================
VALIDAR DATOS
======================================================*/

$idCliente = intval($_SESSION["idCliente"]);
$idTicket  = intval($_GET["id_ticket"] ?? 0);

if ($idTicket <= 0) {
    exit;
}

/*======================================================
VALIDAR QUE EL PEDIDO PERTENEZCA AL CLIENTE
Y ESTÉ ENTREGADO
======================================================*/

$sqlPedido = "

SELECT
    id_ticket_ventas,
    estado_envio
FROM ticket_ventas
WHERE id_ticket_ventas = ?
AND idCliente = ?
LIMIT 1

";

$stmt = mysqli_prepare($conexion, $sqlPedido);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idTicket,
    $idCliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$pedido = mysqli_fetch_assoc($resultado);

if (!$pedido) {
    exit;
}

if ($pedido["estado_envio"] != "ENTREGADO") {
    exit;
}

/*======================================================
PRODUCTOS DEL PEDIDO
======================================================*/

$sql = "

SELECT

d.idProducto,

p.nombre,

p.precio,

d.cantidad_pedido_producto,

d.sub_total,

i.imagenes,

t.id_testimonio,

t.calificacion,

t.comentario,

t.estado

FROM detalle_ticket_ventas d

INNER JOIN producto p
ON p.idProducto = d.idProducto

LEFT JOIN imagenes i
ON i.idProducto=p.idProducto
AND i.orden=1

LEFT JOIN testimonios t
ON t.id_ticket_ventas = d.id_ticket_ventas
AND t.idProducto = d.idProducto
AND t.idCliente = ?

WHERE d.id_ticket_ventas = ?

GROUP BY d.id_detalle_ticket

ORDER BY p.nombre ASC

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idCliente,
    $idTicket
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*======================================================
SIN PRODUCTOS
======================================================*/

if (mysqli_num_rows($resultado) == 0) {

?>

    <div class="alert alert-warning text-center">

        No existen productos para comentar.

    </div>

<?php

    exit;
}

/*======================================================
LISTAR PRODUCTOS
======================================================*/

while ($producto = mysqli_fetch_assoc($resultado)) {

?>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <!-- Imagen -->

                <div class="col-md-2 text-center">

                    <?php if (!empty($producto["imagenes"])) { ?>

                        <img
                            src="data:image/jpeg;base64,<?php echo base64_encode($producto["imagenes"]); ?>"
                            class="img-fluid rounded"
                            style="max-height:100px;object-fit:cover;">

                    <?php } else { ?>

                        <div class="bg-light rounded p-4">

                            <i class="bi bi-image fs-1 text-secondary"></i>

                        </div>

                    <?php } ?>

                </div>

                <!-- Información -->

                <div class="col-md-6">

                    <h5 class="fw-bold mb-2">

                        <?php echo htmlspecialchars($producto["nombre"]); ?>

                    </h5>

                    <p class="mb-1">

                        Cantidad:

                        <strong>

                            <?php echo $producto["cantidad_pedido_producto"]; ?>

                        </strong>

                    </p>

                    <p class="mb-1">

                        Precio:

                        <strong>

                            S/
                            <?php echo number_format($producto["precio"], 2); ?>

                        </strong>

                    </p>

                    <p class="mb-0">

                        Total:

                        <strong class="text-primary">

                            S/
                            <?php echo number_format($producto["sub_total"], 2); ?>

                        </strong>

                    </p>

                </div>

                <!-- Acción -->
                <div class="col-md-4 text-end">

                    <?php

                    if ($producto["id_testimonio"]) {

                    ?>

                        <span class="badge bg-success mb-2">

                            Testimonio enviado

                        </span>

                        <br>

                        <button
                            class="btn btn-outline-primary editarTestimonio"

                            data-producto="<?php echo $producto["idProducto"]; ?>"
                            data-ticket="<?php echo $idTicket; ?>"

                            data-nombre="<?php echo htmlspecialchars($producto["nombre"]); ?>"
                            data-precio="<?php echo number_format($producto["precio"], 2, '.', ''); ?>"
                            data-cantidad="<?php echo $producto["cantidad_pedido_producto"]; ?>"
                            data-imagen="<?php echo base64_encode($producto["imagenes"]); ?>">

                            <i class="bi bi-pencil-square"></i>
                            Editar opinión

                        </button>

                    <?php

                    } else {

                    ?>

                        <button
                            class="btn btn-warning escribirTestimonio"

                            data-producto="<?php echo $producto["idProducto"]; ?>"
                            data-ticket="<?php echo $idTicket; ?>"

                            data-nombre="<?php echo htmlspecialchars($producto["nombre"]); ?>"
                            data-precio="<?php echo number_format($producto["precio"], 2, '.', ''); ?>"
                            data-cantidad="<?php echo $producto["cantidad_pedido_producto"]; ?>"
                            data-imagen="<?php echo base64_encode($producto["imagenes"]); ?>">

                            <i class="bi bi-star-fill"></i>
                            Escribir opinión

                        </button>

                    <?php

                    }

                    ?>

                </div>

            </div>

        </div>

    </div>

<?php

}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>