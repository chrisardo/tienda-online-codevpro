<?php
//======================================================
// pedido_confirmado.php
// CoDevPro Technology
// Sistema: Inventa
//
// FUNCIÓN:
// - Mostrar confirmación del pedido
// - Validar sesión del cliente
// - Validar que el pedido pertenezca al cliente
// - Mostrar comprobante
// - Mostrar datos del pedido
// - Mostrar método de pago
// - Mostrar dirección de entrega
// - Mostrar productos comprados
// - Mostrar ESTADO DEL PEDIDO usando estado_envio
//======================================================

session_start();

require_once "controladores/conexion.php";


//======================================================
// CONFIGURACIÓN
//======================================================

mysqli_set_charset(
    $conexion,
    "utf8mb4"
);


//======================================================
// FUNCIÓN ESCAPE HTML
//======================================================

function e($valor)
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}


//======================================================
// FUNCIÓN PARA OBTENER NOMBRE AMIGABLE DEL ESTADO
//======================================================

function nombreEstadoPedido($estado)
{
    $estado = strtoupper(trim((string)$estado));

    switch ($estado) {

        case "PENDIENTE":
            return "Pendiente";

        case "CONFIRMADO":
            return "Confirmado";

        case "PREPARANDO":
            return "Preparando";

        case "ASIGNADO":
            return "Repartidor asignado";

        case "OBTENIDO":
            return "Pedido recogido";

        case "ENTREGADO":
            return "Entregado";

        case "NO_ENTREGADO":
            return "No entregado";

        case "CANCELADO":
            return "Cancelado";

        default:
            return $estado !== ""
                ? ucwords(strtolower(str_replace("_", " ", $estado)))
                : "Pendiente";
    }
}


//======================================================
// FUNCIÓN PARA OBTENER CLASE BOOTSTRAP DEL ESTADO
//======================================================

function claseEstadoPedido($estado)
{
    $estado = strtoupper(trim((string)$estado));

    switch ($estado) {

        case "PENDIENTE":
            return "bg-warning text-dark";

        case "CONFIRMADO":
            return "bg-primary";

        case "PREPARANDO":
            return "bg-info text-dark";

        case "ASIGNADO":
            return "bg-primary";

        case "OBTENIDO":
            return "bg-info text-dark";

        case "ENTREGADO":
            return "bg-success";

        case "NO_ENTREGADO":
            return "bg-danger";

        case "CANCELADO":
            return "bg-danger";

        default:
            return "bg-secondary";
    }
}


//======================================================
// FUNCIÓN PARA OBTENER ICONO DEL ESTADO
//======================================================

function iconoEstadoPedido($estado)
{
    $estado = strtoupper(trim((string)$estado));

    switch ($estado) {

        case "PENDIENTE":
            return "fa-clock";

        case "CONFIRMADO":
            return "fa-circle-check";

        case "PREPARANDO":
            return "fa-box-open";

        case "ASIGNADO":
            return "fa-user-check";

        case "OBTENIDO":
            return "fa-box";

        case "ENTREGADO":
            return "fa-truck-fast";

        case "NO_ENTREGADO":
            return "fa-triangle-exclamation";

        case "CANCELADO":
            return "fa-circle-xmark";

        default:
            return "fa-circle-info";
    }
}


//======================================================
// VALIDAR SESIÓN DEL CLIENTE
//======================================================

if (
    !isset($_SESSION["idCliente"]) ||
    intval($_SESSION["idCliente"]) <= 0
) {

    header("Location: login.php");
    exit();
}


$idCliente = intval(
    $_SESSION["idCliente"]
);


//======================================================
// VALIDAR ID DEL PEDIDO
//======================================================

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: tienda.php");
    exit();
}


$idTicket = intval(
    $_GET["id"]
);


if ($idTicket <= 0) {

    header("Location: tienda.php");
    exit();
}


//======================================================
// OBTENER PEDIDO
//======================================================
//
// IMPORTANTE:
//
// El pedido solamente puede ser consultado por el
// cliente propietario.
//
//======================================================

$sql = "
    SELECT

        tv.id_ticket_ventas,
        tv.id_user,
        tv.idCliente,

        tv.direccion_envio,

        tv.pago_cliente,
        tv.total_venta,

        tv.id_metodo_pago,

        tv.estado_venta,

        tv.fecha_venta,
        tv.hora_venta,

        tv.vuelto_venta,

        tv.id_empleado,
        tv.id_repartidor,

        tv.tipo_comprobante,
        tv.serie,
        tv.numero,

        tv.aplica_igv,

        /*
         * ESTE ES EL ESTADO PRINCIPAL DEL PEDIDO
         */
        tv.estado_envio,

        tv.fecha_confirmado,
        tv.fecha_preparando,
        tv.fecha_asignado,
        tv.fecha_obtenido,
        tv.fecha_enviado,
        tv.fecha_entregado,
        tv.fecha_cancelado,

        tv.observacion_envio,

        c.nombre AS nombre_cliente,
        c.email AS email_cliente,
        c.celular AS celular_cliente,

        mp.nombre AS nombre_metodo_pago

    FROM ticket_ventas tv

    LEFT JOIN clientes c
        ON c.idCliente = tv.idCliente

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago = tv.id_metodo_pago

    WHERE tv.id_ticket_ventas = ?
    AND tv.idCliente = ?

    LIMIT 1
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    header("Location: tienda.php");
    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idTicket,
    $idCliente
);


if (
    !mysqli_stmt_execute($stmt)
) {

    mysqli_stmt_close($stmt);

    header("Location: tienda.php");
    exit();
}


$resultado = mysqli_stmt_get_result(
    $stmt
);


if (
    !$resultado ||
    mysqli_num_rows($resultado) === 0
) {

    mysqli_stmt_close($stmt);

    header("Location: tienda.php");
    exit();
}


$pedido = mysqli_fetch_assoc(
    $resultado
);


mysqli_stmt_close($stmt);


//======================================================
// DATOS DEL PEDIDO
//======================================================

$idTicketMostrar =
    intval(
        $pedido["id_ticket_ventas"]
    );


$fechaVenta =
    $pedido["fecha_venta"] ?? "";


$horaVenta =
    $pedido["hora_venta"] ?? "";


$totalVenta =
    floatval(
        $pedido["total_venta"] ?? 0
    );


$pagoCliente =
    floatval(
        $pedido["pago_cliente"] ?? 0
    );


$vueltoVenta =
    floatval(
        $pedido["vuelto_venta"] ?? 0
    );


$serie =
    trim(
        $pedido["serie"] ?? ""
    );


$numero =
    intval(
        $pedido["numero"] ?? 0
    );


$tipoComprobante =
    trim(
        $pedido["tipo_comprobante"] ?? ""
    );


//======================================================
// ESTADO DE VENTA
//======================================================
//
// Se conserva por compatibilidad, pero NO será el
// estado principal mostrado al cliente.
//
// El estado principal es estado_envio.
//======================================================

$estadoVenta =
    trim(
        $pedido["estado_venta"] ?? ""
    );


//======================================================
// ESTADO DEL PEDIDO
//======================================================
//
// IMPORTANTE:
// El estado que verá el cliente como "Estado del pedido"
// viene de ticket_ventas.estado_envio.
//======================================================

$estadoEnvio =
    strtoupper(
        trim(
            $pedido["estado_envio"] ?? ""
        )
    );


if ($estadoEnvio === "") {

    $estadoEnvio = "PENDIENTE";
}


$estadoEnvioNombre =
    nombreEstadoPedido(
        $estadoEnvio
    );


$estadoEnvioClase =
    claseEstadoPedido(
        $estadoEnvio
    );


$estadoEnvioIcono =
    iconoEstadoPedido(
        $estadoEnvio
    );


//======================================================
// DIRECCIÓN
//======================================================

$direccionEnvio =
    trim(
        $pedido["direccion_envio"] ?? ""
    );


//======================================================
// CLIENTE
//======================================================

$nombreCliente =
    trim(
        $pedido["nombre_cliente"] ?? ""
    );


//======================================================
// MÉTODO DE PAGO
//======================================================

$nombreMetodoPago =
    trim(
        $pedido["nombre_metodo_pago"] ?? ""
    );


//======================================================
// OBSERVACIÓN
//======================================================

$observacionEnvio =
    trim(
        $pedido["observacion_envio"] ?? ""
    );


//======================================================
// COMPROBANTE
//======================================================

$comprobante = "";


if ($tipoComprobante !== "") {

    $comprobante .=
        $tipoComprobante . " ";
}


if ($serie !== "") {

    $comprobante .=
        $serie . "-";
}


$comprobante .=
    str_pad(
        (string)$numero,
        8,
        "0",
        STR_PAD_LEFT
    );


//======================================================
// FECHA FORMATEADA
//======================================================

$fechaFormateada = "";


if (
    $fechaVenta !== "" &&
    strtotime($fechaVenta) !== false
) {

    $fechaFormateada =
        date(
            "d/m/Y",
            strtotime($fechaVenta)
        );

} else {

    $fechaFormateada =
        $fechaVenta;
}


//======================================================
// HORA FORMATEADA
//======================================================

$horaFormateada = "";


if ($horaVenta !== "") {

    $horaTimestamp =
        strtotime($horaVenta);

    if ($horaTimestamp !== false) {

        $horaFormateada =
            date(
                "H:i",
                $horaTimestamp
            );

    } else {

        $horaFormateada =
            $horaVenta;
    }
}


//======================================================
// OBTENER DETALLES DEL PEDIDO
//======================================================

$sqlDetalles = "
    SELECT

        d.id_detalle_ticket,
        d.idProducto,
        d.cantidad_pedido_producto,

        d.aplica_impuesto,
        d.porcentaje_impuesto,
        d.monto_impuesto,

        d.sub_total,

        p.nombre AS nombre_producto,
        p.codigo AS codigo_producto

    FROM detalle_ticket_ventas d

    LEFT JOIN producto p
        ON p.idProducto = d.idProducto

    WHERE d.id_ticket_ventas = ?
    AND d.id_user = ?

    ORDER BY d.id_detalle_ticket ASC
";


$stmtDetalles = mysqli_prepare(
    $conexion,
    $sqlDetalles
);


$detalles = [];


if ($stmtDetalles) {

    $idUserPedido =
        intval(
            $pedido["id_user"]
        );


    mysqli_stmt_bind_param(
        $stmtDetalles,
        "ii",
        $idTicket,
        $idUserPedido
    );


    if (
        mysqli_stmt_execute(
            $stmtDetalles
        )
    ) {

        $resultadoDetalles =
            mysqli_stmt_get_result(
                $stmtDetalles
            );


        if ($resultadoDetalles) {

            while (
                $detalle =
                mysqli_fetch_assoc(
                    $resultadoDetalles
                )
            ) {

                $detalles[] =
                    $detalle;
            }
        }
    }


    mysqli_stmt_close(
        $stmtDetalles
    );
}


//======================================================
// DETERMINAR SI EL PEDIDO TERMINÓ
//======================================================

$pedidoEntregado =
    ($estadoEnvio === "ENTREGADO");


$pedidoCancelado =
    ($estadoEnvio === "CANCELADO");


$pedidoNoEntregado =
    ($estadoEnvio === "NO_ENTREGADO");

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Pedido confirmado #<?= e($idTicketMostrar) ?>
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        rel="stylesheet"
    >


    <style>

        body {

            background: #f5f7fb;

            min-height: 100vh;

        }


        .card-confirmacion {

            max-width: 850px;

            margin: 50px auto;

            border: none;

            border-radius: 20px;

            box-shadow:
                0 15px 35px rgba(0, 0, 0, .08);

            overflow: hidden;

        }


        .cabecera-confirmacion {

            padding: 45px 30px 30px;

        }


        .icon-success {

            font-size: 85px;

            color: #198754;

        }


        .titulo {

            font-weight: 700;

        }


        .numero-pedido {

            font-size: 16px;

            color: #6c757d;

        }


        .dato {

            padding: 15px;

            background: #f8f9fa;

            border-radius: 12px;

            height: 100%;

        }


        .dato strong {

            display: block;

            margin-bottom: 7px;

            color: #495057;

        }


        .estado-principal {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            font-size: 14px;

            padding: 8px 13px;

            border-radius: 50px;

        }


        .producto {

            border-bottom: 1px solid #e9ecef;

            padding: 15px 0;

        }


        .producto:last-child {

            border-bottom: none;

        }


        .producto-nombre {

            font-weight: 600;

        }


        .producto-codigo {

            font-size: 13px;

            color: #6c757d;

        }


        .producto-total {

            font-weight: 700;

            white-space: nowrap;

        }


        .total-final {

            font-size: 30px;

            font-weight: 700;

            color: #198754;

        }


        .btn {

            border-radius: 10px;

        }


        .seccion {

            margin-top: 25px;

        }


        .estado-alerta {

            border-radius: 12px;

        }


        @media (max-width: 576px) {

            .card-confirmacion {

                margin: 20px 10px;

            }


            .cabecera-confirmacion {

                padding: 35px 20px 20px;

            }


            .icon-success {

                font-size: 70px;

            }


            .total-final {

                font-size: 25px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <div class="card card-confirmacion">


        <!-- ==========================================
             CABECERA
        =========================================== -->

        <div class="cabecera-confirmacion text-center">


            <div class="icon-success mb-3">

                <?php if ($pedidoCancelado): ?>

                    <i class="fas fa-circle-xmark text-danger"></i>

                <?php elseif ($pedidoNoEntregado): ?>

                    <i class="fas fa-triangle-exclamation text-danger"></i>

                <?php else: ?>

                    <i class="fas fa-circle-check"></i>

                <?php endif; ?>

            </div>


            <h2 class="titulo mb-2">

                <?php if ($pedidoCancelado): ?>

                    Pedido cancelado

                <?php elseif ($pedidoNoEntregado): ?>

                    Pedido no entregado

                <?php else: ?>

                    ¡Pedido confirmado!

                <?php endif; ?>

            </h2>


            <p class="text-muted mb-1">

                Tu compra fue registrada correctamente.

            </p>


            <div class="numero-pedido">

                Pedido

                <strong>
                    #<?= e($idTicketMostrar) ?>
                </strong>

            </div>


        </div>


        <div class="card-body px-4 px-md-5 pb-5">


            <!-- ======================================
                 INFORMACIÓN PRINCIPAL
            ======================================= -->

            <div class="row g-3">


                <!-- COMPROBANTE -->

                <div class="col-md-6">

                    <div class="dato">

                        <strong>

                            <i class="fas fa-file-invoice me-1"></i>

                            Comprobante

                        </strong>


                        <?= e(
                            $comprobante !== ""
                                ? $comprobante
                                : "No especificado"
                        ) ?>

                    </div>

                </div>


                <!-- CLIENTE -->

                <div class="col-md-6">

                    <div class="dato">

                        <strong>

                            <i class="fas fa-user me-1"></i>

                            Cliente

                        </strong>


                        <?= e(
                            $nombreCliente !== ""
                                ? $nombreCliente
                                : "No especificado"
                        ) ?>

                    </div>

                </div>


                <!-- FECHA -->

                <div class="col-md-6">

                    <div class="dato">

                        <strong>

                            <i class="fas fa-calendar me-1"></i>

                            Fecha

                        </strong>


                        <?= e($fechaFormateada) ?>

                    </div>

                </div>


                <!-- HORA -->

                <div class="col-md-6">

                    <div class="dato">

                        <strong>

                            <i class="fas fa-clock me-1"></i>

                            Hora

                        </strong>


                        <?= e($horaFormateada) ?>

                    </div>

                </div>


                <!-- MÉTODO DE PAGO -->

                <div class="col-md-6">

                    <div class="dato">

                        <strong>

                            <i class="fas fa-credit-card me-1"></i>

                            Método de pago

                        </strong>


                        <?= e(
                            $nombreMetodoPago !== ""
                                ? $nombreMetodoPago
                                : "No especificado"
                        ) ?>

                    </div>

                </div>


                <!-- ==================================
                     ESTADO PRINCIPAL DEL PEDIDO
                     
                     IMPORTANTE:
                     SE USA estado_envio
                =================================== -->

                <div class="col-md-6">

                    <div class="dato">

                        <strong>

                            <i class="fas fa-truck me-1"></i>

                            Estado del pedido

                        </strong>


                        <span
                            class="badge <?= e($estadoEnvioClase) ?> estado-principal"
                        >

                            <i
                                class="fas <?= e($estadoEnvioIcono) ?>"
                            ></i>

                            <?= e($estadoEnvioNombre) ?>

                        </span>

                    </div>

                </div>


                <!-- DIRECCIÓN -->

                <div class="col-md-12">

                    <div class="dato">

                        <strong>

                            <i class="fas fa-location-dot me-1"></i>

                            Dirección de entrega

                        </strong>


                        <?= e(
                            $direccionEnvio !== ""
                                ? $direccionEnvio
                                : "No especificada"
                        ) ?>

                    </div>

                </div>


            </div>


            <!-- ======================================
                 SEGUIMIENTO DEL PEDIDO
            ======================================= -->

            <div class="seccion">


                <h5 class="fw-bold mb-3">

                    <i class="fas fa-route me-2"></i>

                    Estado del pedido

                </h5>


                <div class="border rounded-3 p-3 bg-white">


                    <div class="d-flex align-items-center">


                        <div class="me-3">

                            <span
                                class="badge <?= e($estadoEnvioClase) ?> p-3"
                            >

                                <i
                                    class="fas <?= e($estadoEnvioIcono) ?> fa-lg"
                                ></i>

                            </span>

                        </div>


                        <div>

                            <div class="fw-bold">

                                <?= e($estadoEnvioNombre) ?>

                            </div>


                            <div class="text-muted small">

                                <?php

                                switch ($estadoEnvio) {

                                    case "PENDIENTE":

                                        echo "Estamos procesando tu pedido.";

                                        break;

                                    case "CONFIRMADO":

                                        echo "Tu pedido ha sido confirmado.";

                                        break;

                                    case "PREPARANDO":

                                        echo "Estamos preparando tus productos.";

                                        break;

                                    case "ASIGNADO":

                                        echo "Tu pedido ha sido asignado para su entrega.";

                                        break;

                                    case "OBTENIDO":

                                        echo "El pedido ya fue recogido para su entrega.";

                                        break;

                                    case "ENTREGADO":

                                        echo "Tu pedido fue entregado correctamente.";

                                        break;

                                    case "NO_ENTREGADO":

                                        echo "No fue posible completar la entrega.";

                                        break;

                                    case "CANCELADO":

                                        echo "Este pedido fue cancelado.";

                                        break;

                                    default:

                                        echo "Estado actual del pedido.";

                                        break;
                                }

                                ?>

                            </div>

                        </div>

                    </div>


                </div>

            </div>


            <!-- ======================================
                 PRODUCTOS
            ======================================= -->

            <div class="seccion">


                <h5 class="fw-bold mb-3">

                    <i class="fas fa-shopping-bag me-2"></i>

                    Productos del pedido

                </h5>


                <div class="border rounded-3 p-3 bg-white">


                    <?php if (count($detalles) > 0): ?>


                        <?php foreach ($detalles as $detalle): ?>


                            <?php

                            $nombreProducto =
                                trim(
                                    $detalle["nombre_producto"] ?? ""
                                );


                            $codigoProducto =
                                trim(
                                    $detalle["codigo_producto"] ?? ""
                                );


                            $cantidadProducto =
                                intval(
                                    $detalle[
                                        "cantidad_pedido_producto"
                                    ] ?? 0
                                );


                            $subtotalProducto =
                                floatval(
                                    $detalle["sub_total"] ?? 0
                                );


                            $impuestoProducto =
                                floatval(
                                    $detalle["monto_impuesto"] ?? 0
                                );

                            ?>


                            <div class="producto">


                                <div class="row align-items-center g-2">


                                    <div class="col-8">


                                        <div class="producto-nombre">

                                            <?= e(
                                                $nombreProducto !== ""
                                                    ? $nombreProducto
                                                    : "Producto"
                                            ) ?>

                                        </div>


                                        <?php if ($codigoProducto !== ""): ?>

                                            <div class="producto-codigo">

                                                Código:
                                                <?= e($codigoProducto) ?>

                                            </div>

                                        <?php endif; ?>


                                        <div class="text-muted mt-1">

                                            Cantidad:

                                            <strong>

                                                <?= e(
                                                    $cantidadProducto
                                                ) ?>

                                            </strong>

                                        </div>


                                    </div>


                                    <div
                                        class="col-4 text-end producto-total"
                                    >

                                        S/

                                        <?= number_format(
                                            $subtotalProducto,
                                            2,
                                            ".",
                                            ","
                                        ) ?>

                                    </div>


                                </div>


                                <?php if ($impuestoProducto > 0): ?>


                                    <div class="small text-muted mt-2">

                                        Impuesto incluido en el detalle:

                                        S/

                                        <?= number_format(
                                            $impuestoProducto,
                                            2,
                                            ".",
                                            ","
                                        ) ?>

                                    </div>


                                <?php endif; ?>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div class="text-center text-muted py-3">

                            <i
                                class="fas fa-box-open fa-2x mb-2"
                            ></i>

                            <div>

                                No se encontraron detalles del pedido.

                            </div>

                        </div>


                    <?php endif; ?>


                </div>


            </div>


            <!-- ======================================
                 RESUMEN DE PAGO
            ======================================= -->

            <div class="seccion">


                <div class="card border-0 bg-light">


                    <div class="card-body">


                        <div
                            class="d-flex justify-content-between mb-2"
                        >

                            <span>

                                Importe recibido

                            </span>


                            <strong>

                                S/

                                <?= number_format(
                                    $pagoCliente,
                                    2,
                                    ".",
                                    ","
                                ) ?>

                            </strong>

                        </div>


                        <div
                            class="d-flex justify-content-between mb-2"
                        >

                            <span>

                                Total del pedido

                            </span>


                            <strong>

                                S/

                                <?= number_format(
                                    $totalVenta,
                                    2,
                                    ".",
                                    ","
                                ) ?>

                            </strong>

                        </div>


                        <?php if ($vueltoVenta > 0): ?>


                            <div
                                class="d-flex justify-content-between mb-2"
                            >

                                <span>

                                    Vuelto

                                </span>


                                <strong>

                                    S/

                                    <?= number_format(
                                        $vueltoVenta,
                                        2,
                                        ".",
                                        ","
                                    ) ?>

                                </strong>

                            </div>


                        <?php endif; ?>


                        <hr>


                        <div class="text-center">


                            <div class="text-muted">

                                Total pagado

                            </div>


                            <div class="total-final">

                                S/

                                <?= number_format(
                                    $totalVenta,
                                    2,
                                    ".",
                                    ","
                                ) ?>

                            </div>


                        </div>


                    </div>


                </div>


            </div>


            <!-- ======================================
                 OBSERVACIÓN DEL ENVÍO
            ======================================= -->

            <?php if ($observacionEnvio !== ""): ?>


                <div class="alert alert-info estado-alerta mt-4">


                    <i class="fas fa-circle-info me-2"></i>


                    <strong>

                        Observación:

                    </strong>


                    <?= e($observacionEnvio) ?>


                </div>


            <?php endif; ?>


            <!-- ======================================
                 MENSAJE SEGÚN ESTADO
            ======================================= -->

            <?php if ($pedidoEntregado): ?>


                <div class="alert alert-success estado-alerta mt-4">

                    <i class="fas fa-circle-check me-2"></i>

                    Tu pedido fue

                    <strong>

                        entregado correctamente.

                    </strong>

                </div>


            <?php elseif ($pedidoCancelado): ?>


                <div class="alert alert-danger estado-alerta mt-4">

                    <i class="fas fa-circle-xmark me-2"></i>

                    Este pedido se encuentra

                    <strong>

                        cancelado.

                    </strong>

                </div>


            <?php elseif ($pedidoNoEntregado): ?>


                <div class="alert alert-danger estado-alerta mt-4">

                    <i class="fas fa-triangle-exclamation me-2"></i>

                    No fue posible completar la entrega de este pedido.

                </div>


            <?php else: ?>


                <div class="alert alert-success estado-alerta mt-4">

                    <i class="fas fa-check-circle me-2"></i>


                    Tu pedido fue registrado correctamente.


                    Guarda tu número de pedido:

                    <strong>

                        #<?= e($idTicketMostrar) ?>

                    </strong>

                </div>


            <?php endif; ?>


            <!-- ======================================
                 BOTONES
            ======================================= -->

            <div class="d-grid gap-3 mt-4">


                <a
                    href="pedido.php?id=<?= urlencode(
                        $idTicketMostrar
                    ) ?>"
                    class="btn btn-primary btn-lg"
                >

                    <i class="fas fa-file-invoice me-2"></i>

                    Ver detalle del pedido

                </a>


                <a
                    href="tienda.php"
                    class="btn btn-outline-success"
                >

                    <i class="fas fa-store me-2"></i>

                    Seguir comprando

                </a>


            </div>


        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>