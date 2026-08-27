<?php
//======================================================
// pedido.php
// CoDevPro Technology
// Sistema: Inventa
//
// FUNCIÓN:
// - Mostrar detalle completo del pedido
// - Validar sesión del cliente
// - Validar propietario del pedido
// - Mostrar comprobante
// - Mostrar estado del pedido mediante estado_envio
// - Mostrar seguimiento horizontal del pedido
// - Mostrar dirección de entrega
// - Mostrar método de pago
// - Mostrar productos comprados
// - Permitir opiniones únicamente después de ENTREGADO
// - Mostrar resumen económico
// - Imprimir pedido
// - Descargar PDF
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
// FUNCIÓN FECHA
//======================================================

function fechaFormateada($fecha)
{
    if (
        empty($fecha) ||
        strtotime($fecha) === false
    ) {
        return "-";
    }

    return date(
        "d/m/Y",
        strtotime($fecha)
    );
}


//======================================================
// FUNCIÓN HORA
//======================================================

function horaFormateada($hora)
{
    if (
        empty($hora) ||
        strtotime($hora) === false
    ) {
        return "-";
    }

    return date(
        "H:i",
        strtotime($hora)
    );
}


//======================================================
// FUNCIÓN FECHA Y HORA COMPLETA
//======================================================

function fechaHoraFormateada($fecha)
{
    if (
        empty($fecha) ||
        strtotime($fecha) === false
    ) {
        return "";
    }

    return date(
        "d/m/Y H:i",
        strtotime($fecha)
    );
}


//======================================================
// FUNCIÓN ESTADO ENVÍO
//======================================================

function obtenerEstadoEnvio($estado)
{
    $estado = strtoupper(
        trim((string)$estado)
    );

    $datos = [

        "PENDIENTE" => [
            "texto" => "Pendiente",
            "clase" => "bg-warning text-dark",
            "icono" => "fa-clock"
        ],

        "CONFIRMADO" => [
            "texto" => "Confirmado",
            "clase" => "bg-primary",
            "icono" => "fa-circle-check"
        ],

        "PREPARANDO" => [
            "texto" => "Preparando",
            "clase" => "bg-info text-dark",
            "icono" => "fa-box-open"
        ],

        "ASIGNADO" => [
            "texto" => "Repartidor asignado",
            "clase" => "bg-primary",
            "icono" => "fa-user-check"
        ],

        "OBTENIDO" => [
            "texto" => "Pedido recogido",
            "clase" => "bg-info text-dark",
            "icono" => "fa-hand-holding-box"
        ],

        "ENTREGADO" => [
            "texto" => "Entregado",
            "clase" => "bg-success",
            "icono" => "fa-check-double"
        ],

        "NO_ENTREGADO" => [
            "texto" => "No entregado",
            "clase" => "bg-danger",
            "icono" => "fa-triangle-exclamation"
        ],

        "CANCELADO" => [
            "texto" => "Cancelado",
            "clase" => "bg-danger",
            "icono" => "fa-circle-xmark"
        ]

    ];

    if (
        isset($datos[$estado])
    ) {
        return $datos[$estado];
    }

    return [
        "texto" => (
            $estado !== ""
                ? $estado
                : "Pendiente"
        ),
        "clase" => "bg-secondary",
        "icono" => "fa-circle-question"
    ];
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

        c.direccion AS direccion_cliente,

        pr.nombre AS nombre_provincia,
        di.nombre AS nombre_distrito,

        mp.nombre AS nombre_metodo_pago

    FROM ticket_ventas tv

    LEFT JOIN clientes c
        ON c.idCliente = tv.idCliente

    LEFT JOIN provincia pr
        ON pr.id_provincia = c.id_provincia

    LEFT JOIN distrito di
        ON di.id_distrito = c.id_distrito

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


mysqli_stmt_close(
    $stmt
);


//======================================================
// DATOS PRINCIPALES
//======================================================

$idTicketMostrar = intval(
    $pedido["id_ticket_ventas"]
);


$idUserPedido = intval(
    $pedido["id_user"]
);


$fechaVenta =
    $pedido["fecha_venta"] ?? "";


$horaVenta =
    $pedido["hora_venta"] ?? "";


$totalVenta = floatval(
    $pedido["total_venta"] ?? 0
);


$pagoCliente = floatval(
    $pedido["pago_cliente"] ?? 0
);


$vueltoVenta = floatval(
    $pedido["vuelto_venta"] ?? 0
);


$estadoVenta = strtoupper(
    trim(
        $pedido["estado_venta"] ?? ""
    )
);


$estadoEnvio = strtoupper(
    trim(
        $pedido["estado_envio"] ?? ""
    )
);


$direccionEnvio = trim(
    $pedido["direccion_envio"] ?? ""
);


$nombreCliente = trim(
    $pedido["nombre_cliente"] ?? ""
);


$emailCliente = trim(
    $pedido["email_cliente"] ?? ""
);


$celularCliente = trim(
    $pedido["celular_cliente"] ?? ""
);


$direccionCliente = trim(
    $pedido["direccion_cliente"] ?? ""
);


$provincia = trim(
    $pedido["nombre_provincia"] ?? ""
);


$distrito = trim(
    $pedido["nombre_distrito"] ?? ""
);


$metodoPago = trim(
    $pedido["nombre_metodo_pago"] ?? ""
);


$observacionEnvio = trim(
    $pedido["observacion_envio"] ?? ""
);


$tipoComprobante = trim(
    $pedido["tipo_comprobante"] ?? ""
);


$serie = trim(
    $pedido["serie"] ?? ""
);


$numero = intval(
    $pedido["numero"] ?? 0
);


//======================================================
// ESTADO DEL PEDIDO
//======================================================

$datosEstadoEnvio =
    obtenerEstadoEnvio(
        $estadoEnvio
    );


$estadoEnvioTexto =
    $datosEstadoEnvio["texto"];


$estadoEnvioClase =
    $datosEstadoEnvio["clase"];


$estadoEnvioIcono =
    $datosEstadoEnvio["icono"];


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


if ($numero > 0) {

    $comprobante .= str_pad(
        (string)$numero,
        8,
        "0",
        STR_PAD_LEFT
    );
}


if (
    $comprobante === "" ||
    $comprobante === "0"
) {

    $comprobante = "No generado";
}


//======================================================
// FECHA Y HORA
//======================================================

$fechaVentaFormateada =
    fechaFormateada(
        $fechaVenta
    );


$horaVentaFormateada =
    horaFormateada(
        $horaVenta
    );


//======================================================
// DIRECCIÓN
//======================================================

$direccionMostrar =
    $direccionEnvio !== ""
        ? $direccionEnvio
        : $direccionCliente;


//======================================================
// OBTENER PRODUCTOS
//======================================================

$sqlProductos = "
    SELECT

        d.id_detalle_ticket,
        d.idProducto,

        d.cantidad_pedido_producto,

        d.aplica_impuesto,
        d.porcentaje_impuesto,
        d.monto_impuesto,

        d.sub_total,

        p.codigo,
        p.nombre,
        p.precio

    FROM detalle_ticket_ventas d

    LEFT JOIN producto p
        ON p.idProducto = d.idProducto

    WHERE d.id_ticket_ventas = ?
    AND d.id_user = ?

    ORDER BY d.id_detalle_ticket ASC
";


$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);


$productos = [];


if ($stmtProductos) {

    mysqli_stmt_bind_param(
        $stmtProductos,
        "ii",
        $idTicket,
        $idUserPedido
    );


    if (
        mysqli_stmt_execute(
            $stmtProductos
        )
    ) {

        $resultadoProductos =
            mysqli_stmt_get_result(
                $stmtProductos
            );


        if ($resultadoProductos) {

            while (
                $fila =
                mysqli_fetch_assoc(
                    $resultadoProductos
                )
            ) {

                $productos[] =
                    $fila;
            }
        }
    }


    mysqli_stmt_close(
        $stmtProductos
    );
}


//======================================================
// CANTIDADES
//======================================================

$totalProductos =
    count($productos);


$cantidadArticulos = 0;


$totalProductosCalculado = 0;


foreach (
    $productos
    as $producto
) {

    $cantidad =
        intval(
            $producto[
                "cantidad_pedido_producto"
            ] ?? 0
        );


    $subtotal =
        floatval(
            $producto[
                "sub_total"
            ] ?? 0
        );


    $cantidadArticulos +=
        $cantidad;


    $totalProductosCalculado +=
        $subtotal;
}


$totalDetalle =
    $totalProductosCalculado;


//======================================================
// TESTIMONIOS
//======================================================

$testimoniosProducto = [];


$sqlTestimonios = "
    SELECT
        idProducto,
        id_testimonio
    FROM testimonios
    WHERE id_ticket_ventas = ?
    AND idCliente = ?
";


$stmtTestimonios = mysqli_prepare(
    $conexion,
    $sqlTestimonios
);


if ($stmtTestimonios) {

    mysqli_stmt_bind_param(
        $stmtTestimonios,
        "ii",
        $idTicket,
        $idCliente
    );


    if (
        mysqli_stmt_execute(
            $stmtTestimonios
        )
    ) {

        $resultadoTestimonios =
            mysqli_stmt_get_result(
                $stmtTestimonios
            );


        if ($resultadoTestimonios) {

            while (
                $testimonio =
                mysqli_fetch_assoc(
                    $resultadoTestimonios
                )
            ) {

                $testimoniosProducto[
                    intval(
                        $testimonio["idProducto"]
                    )
                ] = intval(
                    $testimonio["id_testimonio"]
                );
            }
        }
    }


    mysqli_stmt_close(
        $stmtTestimonios
    );
}


//======================================================
// PERMISO PARA COMENTAR
//======================================================

$puedeComentar = false;


$mensajeOpinion = "";


if (
    !isset($_SESSION["idCliente"])
) {

    $mensajeOpinion =
        "Inicie sesión para poder dejar una opinión.";

} else {

    $idClienteSesion =
        intval(
            $_SESSION["idCliente"]
        );


    if (
        $idClienteSesion !==
        intval($pedido["idCliente"])
    ) {

        $mensajeOpinion =
            "Solo el comprador puede dejar una opinión.";

    } elseif (
        $estadoEnvio ===
        "ENTREGADO"
    ) {

        $puedeComentar = true;

    } else {

        $mensajeOpinion =
            "Podrá comentar cuando el pedido sea entregado.";
    }
}


//======================================================
// ESTADO DE VENTA
//======================================================

$estadoVentaClase =
    "bg-secondary";


switch ($estadoVenta) {

    case "PENDIENTE":

        $estadoVentaClase =
            "bg-warning text-dark";

        break;


    case "CONFIRMADO":

        $estadoVentaClase =
            "bg-primary";

        break;


    case "PAGADO":

        $estadoVentaClase =
            "bg-success";

        break;


    case "ANULADO":

        $estadoVentaClase =
            "bg-danger";

        break;


    case "CANCELADO":

        $estadoVentaClase =
            "bg-danger";

        break;
}


//======================================================
// SEGUIMIENTO HORIZONTAL
//======================================================

$seguimiento = [

    [
        "estado" => "PENDIENTE",
        "titulo" => "Pedido recibido",
        "icono" => "fa-clock",
        "fecha" => $pedido["fecha_venta"] ?? null
    ],

    [
        "estado" => "CONFIRMADO",
        "titulo" => "Pedido confirmado",
        "icono" => "fa-circle-check",
        "fecha" => $pedido["fecha_confirmado"] ?? null
    ],

    [
        "estado" => "PREPARANDO",
        "titulo" => "Preparando pedido",
        "icono" => "fa-box-open",
        "fecha" => $pedido["fecha_preparando"] ?? null
    ],

    [
        "estado" => "ASIGNADO",
        "titulo" => "Repartidor asignado",
        "icono" => "fa-user-check",
        "fecha" => $pedido["fecha_asignado"] ?? null
    ],

    [
        "estado" => "OBTENIDO",
        "titulo" => "Pedido recogido",
        "icono" => "fa-hand-holding-box",
        "fecha" => $pedido["fecha_obtenido"] ?? null
    ],

    [
        "estado" => "ENTREGADO",
        "titulo" => "Pedido entregado",
        "icono" => "fa-check-double",
        "fecha" => $pedido["fecha_entregado"] ?? null
    ]
];


//======================================================
// ORDEN DE ESTADOS
//======================================================

$ordenEstados = [

    "PENDIENTE" => 1,
    "CONFIRMADO" => 2,
    "PREPARANDO" => 3,
    "ASIGNADO" => 4,
    "OBTENIDO" => 5,
    "ENTREGADO" => 6
];


$posicionEstado =
    $ordenEstados[
        $estadoEnvio
    ] ?? 1;


//======================================================
// ESTADO ESPECIAL
//======================================================

$estadoEspecial = in_array(
    $estadoEnvio,
    [
        "CANCELADO",
        "NO_ENTREGADO"
    ],
    true
);


//======================================================
// IGV
//======================================================

$aplicaIgv =
    intval(
        $pedido["aplica_igv"] ?? 0
    ) === 1;


//======================================================
// VARIABLES HTML
//======================================================

$nombreClienteMostrar =
    $nombreCliente !== ""
        ? $nombreCliente
        : "Cliente";


$emailClienteMostrar =
    $emailCliente !== ""
        ? $emailCliente
        : "No especificado";


$celularClienteMostrar =
    $celularCliente !== ""
        ? $celularCliente
        : "No especificado";


$metodoPagoMostrar =
    $metodoPago !== ""
        ? $metodoPago
        : "No especificado";


$provinciaMostrar =
    $provincia !== ""
        ? $provincia
        : "-";


$distritoMostrar =
    $distrito !== ""
        ? $distrito
        : "-";


$direccionMostrar =
    $direccionMostrar !== ""
        ? $direccionMostrar
        : "No especificada";

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
        Pedido #<?= e($idTicketMostrar) ?>
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="assets/css/tienda.css"
    >


    <style>

        /* ==================================================
           GENERAL
        ================================================== */

        body {

            background: #f5f7fa;

            min-height: 100vh;

        }


        .card {

            border: none;

            border-radius: 18px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, .08);

        }


        .tituloPedido {

            font-size: 30px;

            font-weight: 700;

        }


        .iconHeader {

            width: 70px;

            height: 70px;

            min-width: 70px;

            border-radius: 50%;

            background: #198754;

            color: #fff;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 30px;

        }


        .infoTitulo {

            font-size: 13px;

            color: #6c757d;

            text-transform: uppercase;

            letter-spacing: .3px;

            margin-bottom: 4px;

        }


        .infoValor {

            font-size: 16px;

            font-weight: 600;

        }


        .estado-principal {

            font-size: 15px;

            padding: 11px 18px;

            border-radius: 30px;

        }


        /* ==================================================
           PRODUCTOS
        ================================================== */

        .producto-imagen {

            width: 75px;

            height: 75px;

            object-fit: cover;

            border-radius: 12px;

            background: #f8f9fa;

        }


        .producto-nombre {

            font-weight: 600;

        }


        .producto-codigo {

            font-size: 13px;

            color: #6c757d;

        }


        /* ==================================================
           SEGUIMIENTO HORIZONTAL
        ================================================== */

        .seguimiento-wrapper {

            width: 100%;

            overflow-x: auto;

            overflow-y: hidden;

            padding:

                20px 5px 15px;

            scrollbar-width: thin;

            -webkit-overflow-scrolling: touch;

        }


        .seguimiento {

            position: relative;

            min-width: 850px;

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            padding: 0 20px;

        }


        /*
         * Línea horizontal de fondo
         */

        .seguimiento::before {

            content: "";

            position: absolute;

            top: 29px;

            left: 70px;

            right: 70px;

            height: 4px;

            background: #dee2e6;

            border-radius: 10px;

            z-index: 1;

        }


        /*
         * Línea horizontal completada
         */

        .seguimiento-linea-activa {

            position: absolute;

            top: 29px;

            left: 70px;

            height: 4px;

            background: #198754;

            border-radius: 10px;

            z-index: 2;

            transition: width .4s ease;

        }


        .seguimiento-item {

            position: relative;

            width: 140px;

            min-width: 140px;

            text-align: center;

            z-index: 3;

        }


        .seguimiento-icono {

            width: 58px;

            height: 58px;

            margin: 0 auto 12px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #e9ecef;

            color: #6c757d;

            border: 4px solid #fff;

            box-shadow:

                0 2px 8px rgba(0, 0, 0, .10);

            font-size: 20px;

            position: relative;

            z-index: 4;

            transition:
                all .3s ease;

        }


        /*
         * Estados completados
         */

        .seguimiento-item.completado
        .seguimiento-icono {

            background: #198754;

            color: #fff;

        }


        /*
         * Estado actual
         */

        .seguimiento-item.actual
        .seguimiento-icono {

            background: #0d6efd;

            color: #fff;

            transform: scale(1.08);

            box-shadow:

                0 0 0 6px
                rgba(13, 110, 253, .12),

                0 4px 12px
                rgba(0, 0, 0, .15);

        }


        .seguimiento-titulo {

            font-size: 14px;

            font-weight: 700;

            line-height: 1.3;

            min-height: 38px;

            display: flex;

            align-items: flex-start;

            justify-content: center;

        }


        .seguimiento-fecha {

            margin-top: 5px;

            font-size: 12px;

            color: #6c757d;

            line-height: 1.3;

        }


        .seguimiento-pendiente {

            color: #adb5bd;

        }


        .seguimiento-badge {

            display: inline-block;

            margin-top: 6px;

            font-size: 10px;

            padding: 3px 8px;

            border-radius: 20px;

        }


        /*
         * Flecha / scroll hint
         */

        .seguimiento-ayuda {

            font-size: 12px;

            color: #6c757d;

            text-align: center;

            margin-top: 5px;

        }


        /* ==================================================
           ESTADOS ESPECIALES
        ================================================== */

        .estado-especial {

            border-radius: 12px;

        }


        /* ==================================================
           TOTAL
        ================================================== */

        .total-final {

            font-size: 30px;

            font-weight: 700;

            color: #198754;

        }


        /* ==================================================
           BOTONES
        ================================================== */

        .btn {

            border-radius: 10px;

        }


        /* ==================================================
           TABLA
        ================================================== */

        .table > :not(caption) > * > * {

            padding: 14px 12px;

        }


        /* ==================================================
           MÓVIL
        ================================================== */

        @media (max-width: 767px) {

            .tituloPedido {

                font-size: 24px;

            }


            .iconHeader {

                width: 60px;

                height: 60px;

                min-width: 60px;

                font-size: 25px;

            }


            /*
             * El seguimiento sigue siendo horizontal,
             * pero permite deslizarlo con el dedo.
             */

            .seguimiento {

                min-width: 820px;

            }


            .seguimiento-wrapper {

                margin-left: -5px;

                margin-right: -5px;

                width: calc(100% + 10px);

            }


            .seguimiento-ayuda {

                display: block;

            }

        }


        @media (min-width: 768px) {

            .seguimiento-ayuda {

                display: none;

            }

        }


        /* ==================================================
           IMPRESIÓN
        ================================================== */

        @media print {

            body {

                background: #fff !important;

            }


            .no-print,

            .btn,

            .opinion-columna,

            .seguimiento-ayuda {

                display: none !important;

            }


            .card {

                box-shadow: none !important;

                border: 1px solid #ddd;

                break-inside: avoid;

            }


            .table {

                font-size: 12px;

            }


            .seguimiento-wrapper {

                overflow: visible;

            }


            .seguimiento {

                min-width: 0;

            }


            .seguimiento-item {

                width: auto;

                min-width: 0;

                flex: 1;

            }


            .seguimiento::before {

                left: 5%;

                right: 5%;

            }


            .seguimiento-linea-activa {

                left: 5%;

            }

        }

    </style>

</head>


<body>


<div class="container py-4 py-md-5">


    <!-- ==================================================
         ENCABEZADO
    ================================================== -->

    <div class="card mb-4">


        <div class="card-body p-4">


            <div
                class="row align-items-center"
            >


                <div class="col-md-8">


                    <div
                        class="d-flex align-items-center"
                    >


                        <div class="iconHeader">

                            <i class="fas fa-box"></i>

                        </div>


                        <div class="ms-3 ms-md-4">


                            <div class="tituloPedido">

                                Pedido
                                #<?= e($idTicketMostrar) ?>

                            </div>


                            <div class="text-muted">

                                Realizado el

                                <?= e(
                                    $fechaVentaFormateada
                                ) ?>

                                a las

                                <?= e(
                                    $horaVentaFormateada
                                ) ?>

                            </div>


                            <?php if (
                                $comprobante !==
                                "No generado"
                            ): ?>


                                <div
                                    class="small text-muted mt-1"
                                >

                                    <i
                                        class="
                                            fas
                                            fa-file-invoice
                                            me-1
                                        "
                                    ></i>

                                    <?= e(
                                        $comprobante
                                    ) ?>

                                </div>


                            <?php endif; ?>


                        </div>

                    </div>

                </div>


                <div
                    class="
                        col-md-4
                        text-md-end
                        mt-4
                        mt-md-0
                    "
                >


                    <div
                        class="
                            small
                            text-muted
                            mb-2
                        "
                    >

                        Estado del pedido

                    </div>


                    <span
                        class="
                            badge
                            <?= e(
                                $estadoEnvioClase
                            ) ?>
                            estado-principal
                        "
                    >

                        <i
                            class="
                                fas
                                <?= e(
                                    $estadoEnvioIcono
                                ) ?>
                                me-1
                            "
                        ></i>

                        <?= e(
                            $estadoEnvioTexto
                        ) ?>

                    </span>


                </div>


            </div>


        </div>

    </div>


    <!-- ==================================================
         ESTADO ESPECIAL
    ================================================== -->

    <?php if ($estadoEspecial): ?>


        <div
            class="
                alert
                <?= $estadoEnvio === "CANCELADO"
                    ? "alert-danger"
                    : "alert-warning" ?>
                estado-especial
                mb-4
            "
        >


            <i
                class="
                    fas
                    <?= $estadoEnvio === "CANCELADO"
                        ? "fa-circle-xmark"
                        : "fa-triangle-exclamation" ?>
                    me-2
                "
            ></i>


            <strong>

                <?= e(
                    $estadoEnvioTexto
                ) ?>

            </strong>


            <?php if (
                $observacionEnvio !== ""
            ): ?>


                <div class="mt-1">

                    <?= e(
                        $observacionEnvio
                    ) ?>

                </div>


            <?php endif; ?>


        </div>


    <?php endif; ?>


    <!-- ==================================================
         INFORMACIÓN PRINCIPAL
    ================================================== -->

    <div class="row">


        <!-- CLIENTE -->

        <div class="col-lg-4 mb-4">


            <div class="card h-100">


                <div class="card-body">


                    <h5 class="fw-bold">

                        <i
                            class="
                                fas
                                fa-user
                                text-primary
                                me-2
                            "
                        ></i>

                        Cliente

                    </h5>


                    <hr>


                    <div class="mb-3">

                        <div class="infoTitulo">

                            Nombre

                        </div>


                        <div class="infoValor">

                            <?= e(
                                $nombreClienteMostrar
                            ) ?>

                        </div>

                    </div>


                    <div class="mb-3">

                        <div class="infoTitulo">

                            Correo

                        </div>


                        <div class="infoValor">

                            <?= e(
                                $emailClienteMostrar
                            ) ?>

                        </div>

                    </div>


                    <div>

                        <div class="infoTitulo">

                            Celular

                        </div>


                        <div class="infoValor">

                            <?= e(
                                $celularClienteMostrar
                            ) ?>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- DIRECCIÓN -->

        <div class="col-lg-4 mb-4">


            <div class="card h-100">


                <div class="card-body">


                    <h5 class="fw-bold">

                        <i
                            class="
                                fas
                                fa-location-dot
                                text-danger
                                me-2
                            "
                        ></i>

                        Dirección de entrega

                    </h5>


                    <hr>


                    <div class="infoValor mb-3">

                        <?= e(
                            $direccionMostrar
                        ) ?>

                    </div>


                    <div
                        class="
                            text-muted
                            mb-1
                        "
                    >

                        <i
                            class="
                                fas
                                fa-map-pin
                                me-1
                            "
                        ></i>

                        Distrito:

                        <strong>

                            <?= e(
                                $distritoMostrar
                            ) ?>

                        </strong>

                    </div>


                    <div class="text-muted">

                        <i
                            class="
                                fas
                                fa-map
                                me-1
                            "
                        ></i>

                        Provincia:

                        <strong>

                            <?= e(
                                $provinciaMostrar
                            ) ?>

                        </strong>

                    </div>


                </div>

            </div>

        </div>


        <!-- RESUMEN -->

        <div class="col-lg-4 mb-4">


            <div class="card h-100">


                <div class="card-body">


                    <h5 class="fw-bold">

                        <i
                            class="
                                fas
                                fa-receipt
                                text-success
                                me-2
                            "
                        ></i>

                        Resumen

                    </h5>


                    <hr>


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
                    >

                        <span>

                            Método de pago

                        </span>


                        <strong
                            class="text-end"
                        >

                            <?= e(
                                $metodoPagoMostrar
                            ) ?>

                        </strong>

                    </div>


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
                    >

                        <span>

                            Productos

                        </span>


                        <strong>

                            <?= e(
                                $totalProductos
                            ) ?>

                        </strong>

                    </div>


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
                    >

                        <span>

                            Artículos

                        </span>


                        <strong>

                            <?= e(
                                $cantidadArticulos
                            ) ?>

                        </strong>

                    </div>


                    <hr>


                    <div
                        class="
                            d-flex
                            justify-content-between
                            align-items-center
                        "
                    >

                        <span>

                            Total

                        </span>


                        <strong
                            class="
                                text-success
                                fs-4
                            "
                        >

                            S/

                            <?= number_format(
                                $totalVenta,
                                2,
                                ".",
                                ","
                            ) ?>

                        </strong>

                    </div>


                </div>

            </div>

        </div>


    </div>


    <!-- ==================================================
         SEGUIMIENTO DEL PEDIDO - HORIZONTAL
    ================================================== -->

    <div class="card mb-4">


        <div class="card-header bg-white py-3">


            <h4 class="mb-0 fw-bold">

                <i
                    class="
                        fas
                        fa-truck
                        text-primary
                        me-2
                    "
                ></i>

                Seguimiento del pedido

            </h4>


        </div>


        <div class="card-body">


            <div class="seguimiento-wrapper">


                <div class="seguimiento">


                    <!-- LÍNEA ACTIVA -->

                    <?php

                    /*
                     * Calculamos el porcentaje de avance.
                     *
                     * Hay 6 estados.
                     * El primer estado comienza en 0%.
                     * El último estado llega a 100%.
                     */

                    $cantidadPasos =
                        count($seguimiento);


                    if (
                        $cantidadPasos > 1
                    ) {

                        $avance =
                            (
                                ($posicionEstado - 1)
                                /
                                ($cantidadPasos - 1)
                            ) * 100;

                    } else {

                        $avance = 0;
                    }


                    if ($avance < 0) {

                        $avance = 0;
                    }


                    if ($avance > 100) {

                        $avance = 100;
                    }

                    ?>


                    <div
                        class="
                            seguimiento-linea-activa
                        "
                        style="
                            width:
                            calc(
                                <?= e($avance) ?>%
                                - 0px
                            )
                        "
                    ></div>


                    <?php foreach (
                        $seguimiento
                        as $indice => $paso
                    ): ?>


                        <?php

                        $estadoPaso =
                            $paso["estado"];


                        $posicionPaso =
                            $ordenEstados[
                                $estadoPaso
                            ] ?? 0;


                        $estaCompletado =
                            $posicionPaso <
                            $posicionEstado;


                        $esActual =
                            $estadoPaso ===
                            $estadoEnvio;


                        /*
                         * Si el pedido está ENTREGADO,
                         * también marcamos ENTREGADO
                         * como completado.
                         */

                        if (
                            $estadoEnvio ===
                            "ENTREGADO" &&
                            $estadoPaso ===
                            "ENTREGADO"
                        ) {

                            $estaCompletado = true;
                        }


                        $tieneFecha =
                            !empty(
                                $paso["fecha"]
                            );


                        $clasesSeguimiento =
                            "seguimiento-item";


                        if (
                            $estaCompletado
                        ) {

                            $clasesSeguimiento .=
                                " completado";
                        }


                        if (
                            $esActual
                        ) {

                            $clasesSeguimiento .=
                                " actual";
                        }

                        ?>


                        <div
                            class="<?= e(
                                $clasesSeguimiento
                            ) ?>"
                        >


                            <!-- ICONO -->

                            <div
                                class="
                                    seguimiento-icono
                                "
                            >

                                <i
                                    class="
                                        fas
                                        <?= e(
                                            $paso["icono"]
                                        ) ?>
                                    "
                                ></i>

                            </div>


                            <!-- TÍTULO -->

                            <div
                                class="
                                    seguimiento-titulo
                                "
                            >

                                <?= e(
                                    $paso["titulo"]
                                ) ?>

                            </div>


                            <!-- FECHA -->

                            <?php if (
                                $tieneFecha
                            ): ?>


                                <div
                                    class="
                                        seguimiento-fecha
                                    "
                                >

                                    <?= e(
                                        fechaHoraFormateada(
                                            $paso["fecha"]
                                        )
                                    ) ?>

                                </div>


                            <?php elseif (
                                $posicionPaso >
                                $posicionEstado
                            ): ?>


                                <div
                                    class="
                                        seguimiento-fecha
                                        seguimiento-pendiente
                                    "
                                >

                                    Pendiente

                                </div>


                            <?php endif; ?>


                            <!-- ACTUAL -->

                            <?php if (
                                $esActual
                            ): ?>


                                <span
                                    class="
                                        badge
                                        bg-primary
                                        seguimiento-badge
                                    "
                                >

                                    Actual

                                </span>


                            <?php elseif (
                                $estaCompletado
                            ): ?>


                                <span
                                    class="
                                        badge
                                        bg-success
                                        seguimiento-badge
                                    "
                                >

                                    Completado

                                </span>


                            <?php endif; ?>


                        </div>


                    <?php endforeach; ?>


                </div>


            </div>


            <div
                class="
                    seguimiento-ayuda
                    no-print
                "
            >

                <i
                    class="
                        fas
                        fa-arrows-left-right
                        me-1
                    "
                ></i>

                Deslice horizontalmente para ver todo
                el seguimiento

            </div>


            <?php if (
                $observacionEnvio !== ""
            ): ?>


                <div
                    class="
                        alert
                        alert-info
                        mt-4
                        mb-0
                    "
                >

                    <i
                        class="
                            fas
                            fa-circle-info
                            me-2
                        "
                    ></i>


                    <strong>

                        Observación del envío:

                    </strong>


                    <?= e(
                        $observacionEnvio
                    ) ?>

                </div>


            <?php endif; ?>


        </div>

    </div>


    <!-- ==================================================
         PRODUCTOS
    ================================================== -->

    <div class="card mb-4">


        <div
            class="
                card-header
                bg-white
                py-3
            "
        >


            <h4 class="mb-0 fw-bold">

                <i
                    class="
                        fas
                        fa-shopping-bag
                        text-primary
                        me-2
                    "
                ></i>

                Productos del pedido

            </h4>


        </div>


        <div class="table-responsive">


            <table
                class="
                    table
                    align-middle
                    table-hover
                    mb-0
                "
            >


                <thead class="table-light">


                    <tr>


                        <th width="100">

                            Imagen

                        </th>


                        <th>

                            Producto

                        </th>


                        <th
                            class="text-center"
                        >

                            Precio

                        </th>


                        <th
                            class="text-center"
                        >

                            Cantidad

                        </th>


                        <th
                            class="text-end"
                        >

                            Subtotal

                        </th>


                        <th
                            class="
                                text-center
                                opinion-columna
                            "
                        >

                            Opinión

                        </th>


                    </tr>


                </thead>


                <tbody>


                    <?php if (
                        count($productos) > 0
                    ): ?>


                        <?php foreach (
                            $productos
                            as $producto
                        ): ?>


                            <?php

                            $idProducto =
                                intval(
                                    $producto[
                                        "idProducto"
                                    ] ?? 0
                                );


                            $cantidadProducto =
                                intval(
                                    $producto[
                                        "cantidad_pedido_producto"
                                    ] ?? 0
                                );


                            $subtotalProducto =
                                floatval(
                                    $producto[
                                        "sub_total"
                                    ] ?? 0
                                );


                            $impuestoProducto =
                                floatval(
                                    $producto[
                                        "monto_impuesto"
                                    ] ?? 0
                                );


                            $precioUnitario = 0;


                            if (
                                $cantidadProducto > 0
                            ) {

                                $precioUnitario =
                                    $subtotalProducto /
                                    $cantidadProducto;
                            }


                            $codigoProducto =
                                trim(
                                    $producto[
                                        "codigo"
                                    ] ?? ""
                                );


                            $nombreProducto =
                                trim(
                                    $producto[
                                        "nombre"
                                    ] ?? ""
                                );

                            ?>


                            <tr>


                                <!-- IMAGEN -->

                                <td>


                                    <?php if (
                                        $idProducto > 0
                                    ): ?>


                                        <img
                                            src="mostrar_imagen.php?id=<?= e($idProducto) ?>"
                                            class="producto-imagen"
                                            alt="<?= e(
                                                $nombreProducto
                                            ) ?>"
                                            onerror="
                                                this.style.display='none';
                                                this.nextElementSibling.style.display='flex';
                                            "
                                        >


                                        <div
                                            style="
                                                display:none;
                                                width:75px;
                                                height:75px;
                                            "
                                            class="
                                                bg-light
                                                rounded
                                                align-items-center
                                                justify-content-center
                                                text-muted
                                            "
                                        >

                                            <i
                                                class="
                                                    fas
                                                    fa-image
                                                "
                                            ></i>

                                        </div>


                                    <?php else: ?>


                                        <div
                                            class="
                                                bg-light
                                                rounded
                                                d-flex
                                                align-items-center
                                                justify-content-center
                                            "
                                            style="
                                                width:75px;
                                                height:75px;
                                            "
                                        >

                                            <i
                                                class="
                                                    fas
                                                    fa-image
                                                    text-muted
                                                "
                                            ></i>

                                        </div>


                                    <?php endif; ?>


                                </td>


                                <!-- PRODUCTO -->

                                <td>


                                    <div
                                        class="
                                            producto-nombre
                                        "
                                    >

                                        <?= e(
                                            $nombreProducto !== ""
                                                ? $nombreProducto
                                                : "Producto"
                                        ) ?>

                                    </div>


                                    <?php if (
                                        $codigoProducto !== ""
                                    ): ?>


                                        <div
                                            class="
                                                producto-codigo
                                            "
                                        >

                                            Código:

                                            <?= e(
                                                $codigoProducto
                                            ) ?>

                                        </div>


                                    <?php endif; ?>


                                    <?php if (
                                        $impuestoProducto > 0
                                    ): ?>


                                        <div
                                            class="
                                                small
                                                text-muted
                                                mt-1
                                            "
                                        >

                                            Impuesto:

                                            S/

                                            <?= number_format(
                                                $impuestoProducto,
                                                2,
                                                ".",
                                                ","
                                            ) ?>

                                        </div>


                                    <?php endif; ?>


                                </td>


                                <!-- PRECIO -->

                                <td
                                    class="text-center"
                                >

                                    S/

                                    <?= number_format(
                                        $precioUnitario,
                                        2,
                                        ".",
                                        ","
                                    ) ?>

                                </td>


                                <!-- CANTIDAD -->

                                <td
                                    class="text-center"
                                >

                                    <span
                                        class="
                                            badge
                                            bg-secondary
                                        "
                                    >

                                        <?= e(
                                            $cantidadProducto
                                        ) ?>

                                    </span>

                                </td>


                                <!-- SUBTOTAL -->

                                <td
                                    class="text-end"
                                >

                                    <strong>

                                        S/

                                        <?= number_format(
                                            $subtotalProducto,
                                            2,
                                            ".",
                                            ","
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- OPINIÓN -->

                                <td
                                    class="
                                        text-center
                                        opinion-columna
                                    "
                                >


                                    <?php if (
                                        $puedeComentar
                                    ): ?>


                                        <?php if (
                                            isset(
                                                $testimoniosProducto[
                                                    $idProducto
                                                ]
                                            )
                                        ): ?>


                                            <span
                                                class="
                                                    badge
                                                    bg-success
                                                "
                                            >

                                                <i
                                                    class="
                                                        fas
                                                        fa-check
                                                        me-1
                                                    "
                                                ></i>

                                                Opinión enviada

                                            </span>


                                        <?php else: ?>


                                            <button
                                                type="button"
                                                class="
                                                    btn
                                                    btn-warning
                                                    btn-sm
                                                    btnTestimonio
                                                "
                                                data-ticket="<?= e(
                                                    $idTicket
                                                ) ?>"
                                                data-producto="<?= e(
                                                    $idProducto
                                                ) ?>"
                                            >

                                                <i
                                                    class="
                                                        fas
                                                        fa-star
                                                        me-1
                                                    "
                                                ></i>

                                                Opinar

                                            </button>


                                        <?php endif; ?>


                                    <?php else: ?>


                                        <span
                                            class="
                                                text-muted
                                                small
                                            "
                                            title="<?= e(
                                                $mensajeOpinion
                                            ) ?>"
                                        >

                                            <i
                                                class="
                                                    fas
                                                    fa-lock
                                                    me-1
                                                "
                                            ></i>

                                            No disponible

                                        </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>


                            <td
                                colspan="6"
                                class="
                                    text-center
                                    py-5
                                    text-muted
                                "
                            >

                                <i
                                    class="
                                        fas
                                        fa-box-open
                                        fa-2x
                                        mb-3
                                    "
                                ></i>


                                <div>

                                    No se encontraron
                                    productos asociados
                                    a este pedido.

                                </div>


                            </td>


                        </tr>


                    <?php endif; ?>


                </tbody>


                <?php if (
                    count($productos) > 0
                ): ?>


                    <tfoot>


                        <tr
                            class="
                                table-success
                            "
                        >


                            <td
                                colspan="4"
                                class="text-end"
                            >

                                <strong>

                                    TOTAL DEL PEDIDO

                                </strong>

                            </td>


                            <td
                                class="text-end"
                            >

                                <strong
                                    class="
                                        text-success
                                        fs-5
                                    "
                                >

                                    S/

                                    <?= number_format(
                                        $totalVenta,
                                        2,
                                        ".",
                                        ","
                                    ) ?>

                                </strong>

                            </td>


                            <td
                                class="
                                    opinion-columna
                                "
                            ></td>


                        </tr>


                    </tfoot>


                <?php endif; ?>


            </table>


        </div>


    </div>


    <!-- ==================================================
         RESUMEN ECONÓMICO
    ================================================== -->

    <div class="row mb-4">


        <!-- INFORMACIÓN DE PAGO -->

        <div
            class="
                col-lg-6
                mb-4
                mb-lg-0
            "
        >


            <div class="card h-100">


                <div
                    class="
                        card-header
                        bg-white
                        py-3
                    "
                >


                    <h5 class="mb-0 fw-bold">

                        <i
                            class="
                                fas
                                fa-credit-card
                                text-primary
                                me-2
                            "
                        ></i>

                        Información de pago

                    </h5>


                </div>


                <div class="card-body">


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
                    >

                        <span>

                            Método de pago

                        </span>


                        <strong>

                            <?= e(
                                $metodoPagoMostrar
                            ) ?>

                        </strong>

                    </div>


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
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


                    <?php if (
                        $vueltoVenta > 0
                    ): ?>


                        <div
                            class="
                                d-flex
                                justify-content-between
                                mb-3
                            "
                        >

                            <span>

                                Vuelto

                            </span>


                            <strong
                                class="text-success"
                            >

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


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
                    >

                        <span>

                            IGV

                        </span>


                        <strong>

                            <?= $aplicaIgv
                                ? "Incluido"
                                : "No aplica" ?>

                        </strong>

                    </div>


                </div>

            </div>

        </div>


        <!-- RESUMEN DEL PEDIDO -->

        <div class="col-lg-6">


            <div class="card h-100">


                <div
                    class="
                        card-header
                        bg-white
                        py-3
                    "
                >


                    <h5 class="mb-0 fw-bold">

                        <i
                            class="
                                fas
                                fa-calculator
                                text-success
                                me-2
                            "
                        ></i>

                        Resumen del pedido

                    </h5>


                </div>


                <div class="card-body">


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
                    >

                        <span>

                            Productos

                        </span>


                        <strong>

                            <?= e(
                                $totalProductos
                            ) ?>

                        </strong>

                    </div>


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
                    >

                        <span>

                            Artículos

                        </span>


                        <strong>

                            <?= e(
                                $cantidadArticulos
                            ) ?>

                        </strong>

                    </div>


                    <div
                        class="
                            d-flex
                            justify-content-between
                            mb-3
                        "
                    >

                        <span>

                            Subtotal de detalles

                        </span>


                        <strong>

                            S/

                            <?= number_format(
                                $totalDetalle,
                                2,
                                ".",
                                ","
                            ) ?>

                        </strong>

                    </div>


                    <hr>


                    <div class="text-center">


                        <div class="text-muted">

                            Total del pedido

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


    </div>


    <!-- ==================================================
         MENSAJE OPINIÓN
    ================================================== -->

    <?php if (
        !$puedeComentar &&
        $mensajeOpinion !== ""
    ): ?>


        <div
            class="
                alert
                alert-info
                no-print
                mb-4
            "
        >

            <i
                class="
                    fas
                    fa-circle-info
                    me-2
                "
            ></i>


            <?= e(
                $mensajeOpinion
            ) ?>


        </div>


    <?php endif; ?>


    <!-- ==================================================
         BOTONES
    ================================================== -->

    <div
        class="
            text-center
            mt-4
            mb-5
            no-print
        "
    >


        <button
            type="button"
            class="
                btn
                btn-success
                btn-lg
                me-2
                mb-2
            "
            onclick="window.print()"
        >

            <i
                class="
                    fas
                    fa-print
                    me-1
                "
            ></i>

            Imprimir

        </button>


        <a
            href="generar_pdf_pedido.php?id=<?= urlencode(
                $idTicketMostrar
            ) ?>"
            class="
                btn
                btn-danger
                btn-lg
                me-2
                mb-2
            "
        >

            <i
                class="
                    fas
                    fa-file-pdf
                    me-1
                "
            ></i>

            Descargar PDF

        </a>


        <a
            href="tienda.php"
            class="
                btn
                btn-primary
                btn-lg
                me-2
                mb-2
            "
        >

            <i
                class="
                    fas
                    fa-store
                    me-1
                "
            ></i>

            Seguir comprando

        </a>


        <button
            type="button"
            onclick="history.back();"
            class="
                btn
                btn-outline-secondary
                btn-lg
                mb-2
            "
        >

            <i
                class="
                    fas
                    fa-arrow-left
                    me-1
                "
            ></i>

            Volver

        </button>


    </div>


</div>


<!-- ==================================================
     MODAL DE TESTIMONIO
================================================== -->

<div
    class="modal fade"
    id="modalTestimonio"
    tabindex="-1"
    aria-hidden="true"
>


    <div
        class="
            modal-dialog
            modal-dialog-centered
        "
    >


        <div class="modal-content">


            <div class="modal-header">


                <h5 class="modal-title">

                    <i
                        class="
                            fas
                            fa-star
                            text-warning
                            me-2
                        "
                    ></i>

                    Dejar opinión

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>


            </div>


            <form
                id="formTestimonio"
                method="POST"
                action="guardar_testimonio.php"
            >


                <div class="modal-body">


                    <input
                        type="hidden"
                        name="id_ticket_ventas"
                        id="testimonioTicket"
                        value=""
                    >


                    <input
                        type="hidden"
                        name="idProducto"
                        id="testimonioProducto"
                        value=""
                    >


                    <div class="mb-3">


                        <label
                            class="
                                form-label
                                fw-bold
                            "
                        >

                            Calificación

                        </label>


                        <select
                            name="calificacion"
                            class="form-select"
                            required
                        >

                            <option value="">

                                Seleccione una calificación

                            </option>


                            <option value="5">

                                ★★★★★ — Excelente

                            </option>


                            <option value="4">

                                ★★★★☆ — Muy bueno

                            </option>


                            <option value="3">

                                ★★★☆☆ — Bueno

                            </option>


                            <option value="2">

                                ★★☆☆☆ — Regular

                            </option>


                            <option value="1">

                                ★☆☆☆☆ — Malo

                            </option>

                        </select>


                    </div>


                    <div class="mb-3">


                        <label
                            class="
                                form-label
                                fw-bold
                            "
                        >

                            Comentario

                        </label>


                        <textarea
                            name="comentario"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                            placeholder="Cuéntanos tu experiencia con este producto..."
                            required
                        ></textarea>


                    </div>


                </div>


                <div class="modal-footer">


                    <button
                        type="button"
                        class="
                            btn
                            btn-outline-secondary
                        "
                        data-bs-dismiss="modal"
                    >

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-warning"
                    >

                        <i
                            class="
                                fas
                                fa-paper-plane
                                me-1
                            "
                        ></i>

                        Enviar opinión

                    </button>


                </div>


            </form>


        </div>


    </div>


</div>


<!-- ==================================================
     BOOTSTRAP
================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- ==================================================
     TESTIMONIOS
================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const botones =
            document.querySelectorAll(
                ".btnTestimonio"
            );


        const modalElemento =
            document.getElementById(
                "modalTestimonio"
            );


        if (
            !modalElemento ||
            typeof bootstrap === "undefined"
        ) {

            return;
        }


        const modal =
            new bootstrap.Modal(
                modalElemento
            );


        const campoTicket =
            document.getElementById(
                "testimonioTicket"
            );


        const campoProducto =
            document.getElementById(
                "testimonioProducto"
            );


        botones.forEach(
            function (boton) {

                boton.addEventListener(
                    "click",
                    function () {

                        const ticket =
                            this.dataset.ticket;


                        const producto =
                            this.dataset.producto;


                        if (
                            campoTicket
                        ) {

                            campoTicket.value =
                                ticket;
                        }


                        if (
                            campoProducto
                        ) {

                            campoProducto.value =
                                producto;
                        }


                        modal.show();

                    }
                );

            }
        );

    }
);

</script>


</body>

</html>