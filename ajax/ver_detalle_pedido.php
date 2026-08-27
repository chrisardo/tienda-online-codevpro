<?php
//======================================================
// CoDevPro Technology
// ajax/ver_detalle_pedido.php
//======================================================

session_start();

require_once "../controladores/conexion.php";

/*======================================================
VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    exit("
        <div class='alert alert-danger m-3'>
            Debes iniciar sesión.
        </div>
    ");
}

$idCliente = intval($_SESSION["idCliente"]);

/*======================================================
VALIDAR ID DEL PEDIDO
======================================================*/

$idPedido = intval($_GET["id"] ?? 0);

if ($idPedido <= 0) {

    exit("
        <div class='alert alert-danger m-3'>
            Pedido inválido.
        </div>
    ");
}

/*======================================================
OBTENER PEDIDO
======================================================*/

$sql = "

SELECT

tv.*,

mp.nombre AS metodo_pago,

c.nombre,

c.email,

c.celular,

c.direccion,

c.distrito,

c.provincia,

d.nombre AS departamento

FROM ticket_ventas tv

INNER JOIN clientes c

ON c.idCliente = tv.idCliente

LEFT JOIN metodo_pago mp

ON mp.id_metodo_pago = tv.id_metodo_pago

LEFT JOIN departamento d

ON d.id_departamento = c.id_departamento

WHERE

tv.id_ticket_ventas='$idPedido'

AND

tv.idCliente='$idCliente'

LIMIT 1

";

$resultado = mysqli_query($conexion, $sql);

/*======================================================
VALIDAR EXISTENCIA
======================================================*/

if (mysqli_num_rows($resultado) == 0) {

    exit("
        <div class='alert alert-warning m-3'>
            No se encontró el pedido solicitado.
        </div>
    ");
}

$pedido = mysqli_fetch_assoc($resultado);

/*======================================================
COLOR DEL ESTADO
======================================================*/

switch ($pedido["estado_envio"]) {

    case "PENDIENTE":
        $color = "warning";
        $icono = "hourglass-split";
        break;

    case "CONFIRMADO":
        $color = "info";
        $icono = "patch-check";
        break;

    case "PREPARANDO":
        $color = "primary";
        $icono = "box-seam";
        break;

    case "ENVIADO":
        $color = "secondary";
        $icono = "truck";
        break;

    case "ENTREGADO":
        $color = "success";
        $icono = "check-circle-fill";
        break;

    case "CANCELADO":
        $color = "danger";
        $icono = "x-circle-fill";
        break;

    default:
        $color = "dark";
        $icono = "circle-fill";
}

?>

<!--======================================================
ENCABEZADO DEL PEDIDO
=======================================================-->

<div class="card shadow-sm border-0 mb-4">

    <div class="card-body">

        <div class="row">

            <div class="col-lg-8">

                <h3 class="fw-bold mb-2">

                    Pedido
                    #<?= htmlspecialchars($pedido["serie"]) ?>-<?= htmlspecialchars($pedido["numero"]) ?>

                </h3>

                <span class="badge bg-<?= $color ?> fs-6">

                    <i class="bi bi-<?= $icono ?>"></i>

                    <?= htmlspecialchars($pedido["estado_envio"]) ?>

                </span>

            </div>

            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

                <div class="fw-bold fs-4 text-success">

                    S/ <?= number_format($pedido["total_venta"], 2) ?>

                </div>

                <small class="text-muted">

                    <?= date("d/m/Y", strtotime($pedido["fecha_venta"])) ?>

                    -

                    <?= substr($pedido["hora_venta"], 0, 5) ?>

                </small>

            </div>

        </div>

    </div>

</div>

<!--======================================================
DATOS DEL CLIENTE
=======================================================-->

<div class="row mb-4">

    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white fw-bold">

                <i class="bi bi-person-fill"></i>

                Datos del Cliente

            </div>

            <div class="card-body">

                <p class="mb-2">

                    <strong>Nombre:</strong><br>

                    <?= htmlspecialchars($pedido["nombre"]) ?>

                </p>

                <p class="mb-2">

                    <strong>Email:</strong><br>

                    <?= htmlspecialchars($pedido["email"]) ?>

                </p>

                <p class="mb-0">

                    <strong>Celular:</strong><br>

                    <?= htmlspecialchars($pedido["celular"]) ?>

                </p>

            </div>

        </div>

    </div>

    <div class="col-lg-6 mt-4 mt-lg-0">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white fw-bold">

                <i class="bi bi-geo-alt-fill"></i>

                Dirección de Entrega

            </div>

            <div class="card-body">

                <p class="mb-2">

                    <?= htmlspecialchars($pedido["direccion"]) ?>

                </p>

                <p class="mb-2">

                    <?= htmlspecialchars($pedido["distrito"]) ?>

                </p>

                <p class="mb-2">

                    <?= htmlspecialchars($pedido["provincia"]) ?>

                </p>

                <p class="mb-0">

                    <?= htmlspecialchars($pedido["departamento"]) ?>

                </p>

            </div>

        </div>

    </div>

</div>

<!--======================================================
DATOS DE LA COMPRA
=======================================================-->

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white fw-bold">

        <i class="bi bi-receipt"></i>

        Información de la compra

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3">

                <small class="text-muted">

                    Comprobante

                </small>

                <div class="fw-semibold">

                    <?= htmlspecialchars($pedido["tipo_comprobante"]) ?>

                </div>

            </div>

            <div class="col-md-3">

                <small class="text-muted">

                    Método de pago

                </small>

                <div class="fw-semibold">

                    <?= htmlspecialchars($pedido["metodo_pago"]) ?>

                </div>

            </div>

            <div class="col-md-3">

                <small class="text-muted">

                    Estado

                </small>

                <div class="fw-semibold">

                    <?= htmlspecialchars($pedido["estado_venta"]) ?>

                </div>

            </div>

            <div class="col-md-3">

                <small class="text-muted">

                    Total

                </small>

                <div class="fw-bold text-success">

                    S/ <?= number_format($pedido["total_venta"], 2) ?>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Aquí continuaremos en la Parte 3B-2 -->
<?php
/*======================================================
CONSULTAR PRODUCTOS DEL PEDIDO
======================================================*/

$sqlProductos = "

SELECT

dt.id_detalle_ticket,
dt.idProducto,
dt.cantidad_pedido_producto,
dt.sub_total,

p.codigo,
p.nombre,
p.precio,

(

    SELECT i.id_imagen

    FROM imagenes i

    WHERE i.idProducto = p.idProducto

    ORDER BY i.orden ASC, i.id_imagen ASC

    LIMIT 1

) AS id_imagen

FROM detalle_ticket_ventas dt

INNER JOIN producto p

ON p.idProducto = dt.idProducto

WHERE

dt.id_ticket_ventas = '" . $pedido["id_ticket_ventas"] . "'

ORDER BY

dt.id_detalle_ticket ASC

";

$resultadoProductos = mysqli_query($conexion, $sqlProductos);

/*======================================================
VALIDAR PRODUCTOS
======================================================*/

if (!$resultadoProductos) {

?>

    <div class="alert alert-danger">

        <i class="bi bi-exclamation-triangle-fill"></i>

        No fue posible obtener los productos del pedido.

    </div>

<?php

    return;
}

$totalProductos = mysqli_num_rows($resultadoProductos);

/*======================================================
SI EL PEDIDO NO TIENE PRODUCTOS
======================================================*/

if ($totalProductos == 0) {

?>

    <div class="alert alert-warning">

        <i class="bi bi-box-seam"></i>

        Este pedido no contiene productos registrados.

    </div>

<?php

    return;
}

/*======================================================
VARIABLES PARA LOS TOTALES
======================================================*/

$subtotalPedido = 0;

$totalCantidad = 0;
?>
<!--======================================================
PRODUCTOS DEL PEDIDO
=======================================================-->

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white">

        <h5 class="fw-bold mb-0">

            <i class="bi bi-box-seam-fill text-primary"></i>

            Productos del pedido

        </h5>

    </div>

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th width="90">

                        Imagen

                    </th>

                    <th>

                        Producto

                    </th>

                    <th class="text-center">

                        Cantidad

                    </th>

                    <th class="text-end">

                        Precio

                    </th>

                    <th class="text-end">

                        Subtotal

                    </th>

                </tr>

            </thead>

            <tbody>

                <?php

                while ($producto = mysqli_fetch_assoc($resultadoProductos)) {

                    $cantidad = intval($producto["cantidad_pedido_producto"]);

                    $precioUnitario = floatval($producto["precio"]);

                    $subtotal = floatval($producto["sub_total"]);

                    $subtotalPedido += $subtotal;

                    $totalCantidad += $cantidad;

                ?>

                    <tr>

                        <!--==========================
                        IMAGEN
                        ===========================-->

                        <td>

                            <?php if (!empty($producto["id_imagen"])) { ?>

                                <img
                                    src="mostrar_imagen.php?id=<?= $producto["idProducto"] ?>&img=<?= $producto["id_imagen"] ?>"
                                    class="img-thumbnail"
                                    style="width:70px;height:70px;object-fit:cover;">

                            <?php } else { ?>

                                <img
                                    src="assets/img/sin_imagen.png"
                                    class="img-thumbnail"
                                    style="width:70px;height:70px;object-fit:cover;">

                            <?php } ?>

                        </td>

                        <!--==========================
                        PRODUCTO
                        ===========================-->

                        <td>

                            <div class="fw-bold">

                                <?= htmlspecialchars($producto["nombre"]) ?>

                            </div>

                            <small class="text-muted">

                                Código:

                                <?= htmlspecialchars($producto["codigo"]) ?>

                            </small>

                        </td>

                        <!--==========================
                        CANTIDAD
                        ===========================-->

                        <td class="text-center">

                            <span class="badge bg-primary rounded-pill fs-6">

                                <?= $cantidad ?>

                            </span>

                        </td>

                        <!--==========================
                        PRECIO
                        ===========================-->

                        <td class="text-end">

                            S/

                            <?= number_format($precioUnitario, 2) ?>

                        </td>

                        <!--==========================
                        SUBTOTAL
                        ===========================-->

                        <td class="text-end fw-bold text-success">

                            S/

                            <?= number_format($subtotal, 2) ?>

                        </td>

                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

</div>
<!--======================================================
RESUMEN DEL PEDIDO
=======================================================-->

<?php

/*=============================================
CÁLCULOS
=============================================*/

$totalVenta = floatval($pedido["total_venta"]);

$pagoCliente = floatval($pedido["pago_cliente"]);

$vuelto = floatval($pedido["vuelto_venta"]);

$igv = 0;
$subtotalSinIgv = $totalVenta;

if ($pedido["aplica_igv"] == 1) {

    $subtotalSinIgv = round($totalVenta / 1.18, 2);

    $igv = round($totalVenta - $subtotalSinIgv, 2);
}

?>

<div class="row">

    <!--=========================================
    RESUMEN
    ==========================================-->

    <div class="col-lg-7">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-calculator-fill text-success"></i>

                    Resumen del pedido

                </h5>

            </div>

            <div class="card-body">

                <div class="d-flex justify-content-between mb-3">

                    <span>

                        Productos

                    </span>

                    <strong>

                        <?= $totalCantidad ?>

                    </strong>

                </div>

                <div class="d-flex justify-content-between mb-3">

                    <span>

                        Subtotal

                    </span>

                    <strong>

                        S/ <?= number_format($subtotalSinIgv, 2) ?>

                    </strong>

                </div>

                <?php if ($pedido["aplica_igv"] == 1) { ?>

                    <div class="d-flex justify-content-between mb-3">

                        <span>

                            IGV (18%)

                        </span>

                        <strong>

                            S/ <?= number_format($igv, 2) ?>

                        </strong>

                    </div>

                <?php } ?>

                <hr>

                <div class="d-flex justify-content-between">

                    <h5 class="mb-0">

                        TOTAL

                    </h5>

                    <h4 class="text-success fw-bold mb-0">

                        S/ <?= number_format($totalVenta, 2) ?>

                    </h4>

                </div>

            </div>

        </div>

    </div>

    <!--=========================================
    INFORMACIÓN DE PAGO
    ==========================================-->

    <div class="col-lg-5 mt-4 mt-lg-0">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-credit-card-fill text-primary"></i>

                    Información del pago

                </h5>

            </div>

            <div class="card-body">

                <div class="mb-3">

                    <small class="text-muted">

                        Método de pago

                    </small>

                    <div class="fw-bold">

                        <?= htmlspecialchars($pedido["metodo_pago"]) ?>

                    </div>

                </div>

                <div class="mb-3">

                    <small class="text-muted">

                        Pago del cliente

                    </small>

                    <div class="fw-bold">

                        S/ <?= number_format($pagoCliente, 2) ?>

                    </div>

                </div>

                <div class="mb-3">

                    <small class="text-muted">

                        Vuelto

                    </small>

                    <div class="fw-bold">

                        S/ <?= number_format($vuelto, 2) ?>

                    </div>

                </div>

                <div>

                    <small class="text-muted">

                        Estado del pago

                    </small>

                    <div>

                        <?php if ($pedido["estado_venta"] == "PAGADO") { ?>

                            <span class="badge bg-success">

                                <i class="bi bi-check-circle-fill"></i>

                                Pagado

                            </span>

                        <?php } else { ?>

                            <span class="badge bg-warning text-dark">

                                <?= htmlspecialchars($pedido["estado_venta"]) ?>

                            </span>

                        <?php } ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!--=========================================
DATOS OCULTOS
==========================================-->

<input
    type="hidden"
    id="idPedidoActual"
    value="<?= $pedido["id_ticket_ventas"] ?>">

<input
    type="hidden"
    id="totalPedidoActual"
    value="<?= $pedido["total_venta"] ?>">
<!--======================================================
TIMELINE DEL PEDIDO
=======================================================-->

<?php

$pasos = [

    "PENDIENTE" => [
        "icono" => "clock-history",
        "titulo" => "Pedido recibido",
        "fecha" => $pedido["fecha_venta"] . " " . $pedido["hora_venta"]
    ],

    "CONFIRMADO" => [
        "icono" => "patch-check-fill",
        "titulo" => "Pedido confirmado",
        "fecha" => $pedido["fecha_confirmado"]
    ],

    "PREPARANDO" => [
        "icono" => "box-seam-fill",
        "titulo" => "Preparando pedido",
        "fecha" => $pedido["fecha_preparando"]
    ],

    "ENVIADO" => [
        "icono" => "truck",
        "titulo" => "Pedido enviado",
        "fecha" => $pedido["fecha_enviado"]
    ],

    "ENTREGADO" => [
        "icono" => "check-circle-fill",
        "titulo" => "Pedido entregado",
        "fecha" => $pedido["fecha_enviado"]
    ]

];

$ordenEstados = array_keys($pasos);

$estadoActual = array_search($pedido["estado_envio"], $ordenEstados);

?>

<div class="card border-0 shadow-sm mt-4">

    <div class="card-header bg-white">

        <h5 class="fw-bold mb-0">

            <i class="bi bi-truck text-primary"></i>

            Seguimiento del pedido

        </h5>

    </div>

    <div class="card-body">

        <?php

        if ($pedido["estado_envio"] == "CANCELADO") {

        ?>

            <div class="alert alert-danger mb-0">

                <div class="d-flex align-items-center">

                    <i class="bi bi-x-circle-fill display-6 me-3"></i>

                    <div>

                        <h5 class="mb-1">

                            Pedido cancelado

                        </h5>

                        <div>

                            <?= htmlspecialchars($pedido["obervacion_envio"]) ?>

                        </div>

                        <?php if (!empty($pedido["fecha_cancelado"])) { ?>

                            <small class="text-muted">

                                <?= date(
                                    "d/m/Y H:i",
                                    strtotime($pedido["fecha_cancelado"])
                                ) ?>

                            </small>

                        <?php } ?>

                    </div>

                </div>

            </div>

        <?php

        } else {

        ?>

            <div class="row text-center">

                <?php

                $i = 0;

                foreach ($pasos as $estado => $item) {

                    $activo = ($i <= $estadoActual);

                ?>

                    <div class="col">

                        <div class="mb-2">

                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"

                                style="width:60px;height:60px;
                                background:<?= $activo ? '#198754' : '#dee2e6' ?>;
                                color:white;">

                                <i class="bi bi-<?= $item["icono"] ?> fs-4"></i>

                            </div>

                        </div>

                        <div class="<?= $activo ? 'fw-bold text-success' : 'text-muted' ?>">

                            <?= $item["titulo"] ?>

                        </div>

                        <?php if (!empty($item["fecha"])) { ?>

                            <small class="text-muted">

                                <?= date(
                                    "d/m/Y H:i",
                                    strtotime($item["fecha"])
                                ) ?>

                            </small>

                        <?php } ?>

                    </div>

                    <?php

                    if ($i < count($pasos) - 1) {

                    ?>

                        <div class="col-auto d-flex align-items-center">

                            <i class="bi bi-arrow-right fs-4 text-secondary"></i>

                        </div>

                    <?php

                    }

                    ?>

                <?php

                    $i++;
                }

                ?>

            </div>

        <?php

        }

        ?>

    </div>

</div>