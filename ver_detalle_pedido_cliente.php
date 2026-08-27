<?php
//======================================================
// CoDevPro Technology
// Archivo: ver_detalle_pedido_cliente.php
// Módulo: Mis Pedidos
// Sistema: Inventa
//======================================================


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



//======================================================
// VALIDAR CLIENTE
//======================================================

if (
    !isset($_SESSION["idCliente"]) ||
    (int)$_SESSION["idCliente"] <= 0
) {
    header("Location: login.php");
    exit;
}


//======================================================
// OBTENER PEDIDO DESDE SESIÓN
//======================================================

$idTicket = isset($_SESSION["pedido_detalle"])
    ? (int)$_SESSION["pedido_detalle"]
    : 0;

if ($idTicket <= 0) {
    header("Location: mis_pedidos.php");
    exit;
}


//======================================================
// OBTENER INFORMACIÓN DEL PEDIDO
//======================================================

require_once "controladores/obtener_detalle_pedido_cliente.php";


//======================================================
// VALIDAR RESULTADO
//======================================================

if (
    !isset($pedido) ||
    !is_array($pedido) ||
    empty($pedido)
) {
    header("Location: mis_pedidos.php");
    exit;
}

if (
    !isset($productos) ||
    !is_array($productos)
) {
    $productos = [];
}
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
require_once "controladores/conexion.php";
/*=========================================
DATOS EMPRESA
=========================================*/

$sqlEmpresa = "SELECT nombreEmpresa, imagen
FROM usuario_acceso
LIMIT 1";

$resEmpresa = mysqli_query($conexion, $sqlEmpresa);

$empresa = mysqli_fetch_assoc($resEmpresa);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalle del producto | <?= $empresa['nombreEmpresa']; ?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/hero.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/detalle_pedido.css">

    <!-- Bootstrap Icons -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- AOS -->

    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- SweetAlert -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="bg-light">
    <?php
    include "includes/navbar.php";


    //======================================================
    // FUNCIONES AUXILIARES
    //======================================================

    function limpiarTexto($texto)
    {
        return htmlspecialchars(
            (string)$texto,
            ENT_QUOTES,
            "UTF-8"
        );
    }


    function formatoMoneda($valor)
    {
        return number_format(
            (float)$valor,
            2,
            ".",
            ","
        );
    }


    //======================================================
    // DATOS BÁSICOS DEL PEDIDO
    //======================================================

    $idPedido = (int)(
        $pedido["id_ticket_ventas"]
        ?? $idTicket
    );


    $serie = trim(
        (string)(
            $pedido["serie"]
            ?? ""
        )
    );


    $numero = (int)(
        $pedido["numero"]
        ?? 0
    );


    $numeroPedido = limpiarTexto(
        $serie .
            "-" .
            str_pad(
                (string)$numero,
                8,
                "0",
                STR_PAD_LEFT
            )
    );


    //======================================================
    // ESTADO DEL PEDIDO
    //======================================================

    $estadoActual = strtoupper(
        trim(
            (string)(
                $pedido["estado_envio"]
                ?? "PENDIENTE"
            )
        )
    );


    //======================================================
    // FECHA Y HORA
    //======================================================

    $fechaVenta = "-";

    if (!empty($pedido["fecha_venta"])) {

        $timestampFecha = strtotime(
            $pedido["fecha_venta"]
        );

        if ($timestampFecha !== false) {

            $fechaVenta = date(
                "d/m/Y",
                $timestampFecha
            );
        }
    }


    $horaVenta = "";

    if (!empty($pedido["hora_venta"])) {

        $timestampHora = strtotime(
            $pedido["hora_venta"]
        );

        if ($timestampHora !== false) {

            $horaVenta = date(
                "H:i",
                $timestampHora
            );
        }
    }


    //======================================================
    // DATOS DEL REPARTIDOR
    //======================================================
    //
    // Se acepta cualquiera de los dos campos:
    //
    // 1. ticket_ventas.id_repartidor
    // 2. ticket_ventas.id_empleado
    //
    // Se prioriza id_repartidor.
    // id_empleado queda como respaldo.
    //======================================================

    $idRepartidor = 0;


    //------------------------------------------------------
    // PRIMERA OPCIÓN: ID_REPARTIDOR
    //------------------------------------------------------

    if (
        isset($pedido["id_repartidor"]) &&
        (int)$pedido["id_repartidor"] > 0
    ) {

        $idRepartidor = (int)$pedido["id_repartidor"];
    }


    //------------------------------------------------------
    // SEGUNDA OPCIÓN: ID_EMPLEADO
    //------------------------------------------------------

    if (
        $idRepartidor <= 0 &&
        isset($pedido["id_empleado"]) &&
        (int)$pedido["id_empleado"] > 0
    ) {

        $idRepartidor = (int)$pedido["id_empleado"];
    }


    //======================================================
    // DATOS DEVUELTOS POR EL CONTROLADOR
    //======================================================

    $repartidorNombre = trim(
        (string)(
            $pedido["repartidor_nombre"]
            ?? $pedido["nombre_repartidor"]
            ?? ""
        )
    );


    $repartidorApellido = trim(
        (string)(
            $pedido["repartidor_apellido"]
            ?? $pedido["apellido_repartidor"]
            ?? ""
        )
    );


    $repartidorNombreCompleto = trim(
        $repartidorNombre .
            " " .
            $repartidorApellido
    );


    $repartidorCelular = trim(
        (string)(
            $pedido["repartidor_celular"]
            ?? $pedido["celular_repartidor"]
            ?? ""
        )
    );


    $repartidorRol = trim(
        (string)(
            $pedido["repartidor_rol"]
            ?? $pedido["rol_repartidor"]
            ?? ""
        )
    );


    $repartidorEstado = strtoupper(
        trim(
            (string)(
                $pedido["repartidor_estado"]
                ?? $pedido["estado_repartidor"]
                ?? ""
            )
        )
    );


    $repartidorImagen =
        $pedido["repartidor_imagen"]
        ?? $pedido["imagen_repartidor"]
        ?? null;


    //======================================================
    // VALIDAR EXISTENCIA DEL REPARTIDOR
    //======================================================

    $tieneRepartidor =
        $idRepartidor > 0 &&
        (
            $repartidorNombreCompleto !== ""
            ||
            $repartidorCelular !== ""
            ||
            !empty($repartidorImagen)
        );


    $tieneImagenRepartidor =
        !empty($repartidorImagen);


    //======================================================
    // CONTACTO DEL REPARTIDOR
    //======================================================
    //
    // IMPORTANTE:
    //
    // Si el pedido está ENTREGADO, el cliente NO podrá
    // contactar directamente al repartidor.
    //
    // En ese caso NO se genera WhatsApp ni llamada.
    //======================================================

    $puedeContactarRepartidor =
        $tieneRepartidor &&
        $estadoActual !== "ENTREGADO";


    //======================================================
    // WHATSAPP
    //======================================================

    $numeroWhatsApp = "";


    if (
        $puedeContactarRepartidor &&
        $repartidorCelular !== ""
    ) {

        $numeroWhatsApp = preg_replace(
            "/[^0-9]/",
            "",
            $repartidorCelular
        );


        //--------------------------------------------------
        // NÚMERO PERUANO
        //--------------------------------------------------

        if (
            strlen($numeroWhatsApp) === 9 &&
            substr(
                $numeroWhatsApp,
                0,
                1
            ) === "9"
        ) {

            $numeroWhatsApp =
                "51" .
                $numeroWhatsApp;
        }
    }


    //======================================================
    // CONFIGURACIÓN VISUAL DE ESTADOS
    //======================================================

    $configEstados = [

        "PENDIENTE" => [
            "texto" => "Pedido recibido",
            "descripcion" =>
            "Hemos recibido correctamente tu pedido.",
            "icono" => "bi-clock-history",
            "color" => "warning"
        ],

        "CONFIRMADO" => [
            "texto" => "Pedido confirmado",
            "descripcion" =>
            "Tu pedido ha sido confirmado.",
            "icono" => "bi-check2-circle",
            "color" => "secondary"
        ],

        "PREPARANDO" => [
            "texto" => "Preparando pedido",
            "descripcion" =>
            "Estamos preparando tus productos.",
            "icono" => "bi-box-seam",
            "color" => "info"
        ],

        "ASIGNADO" => [
            "texto" => "Repartidor asignado",
            "descripcion" =>
            "Tu pedido ya fue asignado a un repartidor.",
            "icono" => "bi-person-check-fill",
            "color" => "primary"
        ],

        "OBTENIDO" => [
            "texto" => "Pedido recogido",
            "descripcion" =>
            "El repartidor ya recogió tu pedido.",
            "icono" => "bi-box2-heart-fill",
            "color" => "primary"
        ],

        "ENVIADO" => [
            "texto" => "Pedido en camino",
            "descripcion" =>
            "Tu pedido está en camino hacia tu dirección.",
            "icono" => "bi-truck",
            "color" => "primary"
        ],

        "ENTREGADO" => [
            "texto" => "Pedido entregado",
            "descripcion" =>
            "Tu pedido fue entregado correctamente.",
            "icono" => "bi-house-check-fill",
            "color" => "success"
        ],

        "NO_ENTREGADO" => [
            "texto" => "No entregado",
            "descripcion" =>
            "No fue posible completar la entrega.",
            "icono" => "bi-exclamation-circle-fill",
            "color" => "danger"
        ],

        "CANCELADO" => [
            "texto" => "Pedido cancelado",
            "descripcion" =>
            "Este pedido fue cancelado.",
            "icono" => "bi-x-circle-fill",
            "color" => "dark"
        ]

    ];


    $estadoConfig =
        $configEstados[$estadoActual]
        ?? $configEstados["PENDIENTE"];


    //======================================================
    // ESTADOS DE SEGUIMIENTO
    //======================================================

    $estadosSeguimiento = [

        "PENDIENTE" => [
            "texto" => "Recibido",
            "icono" => "bi-receipt"
        ],

        "CONFIRMADO" => [
            "texto" => "Confirmado",
            "icono" => "bi-check2-circle"
        ],

        "PREPARANDO" => [
            "texto" => "Preparando",
            "icono" => "bi-box-seam"
        ],

        "ASIGNADO" => [
            "texto" => "Asignado",
            "icono" => "bi-person-check"
        ],

        "OBTENIDO" => [
            "texto" => "Recogido",
            "icono" => "bi-box2-heart"
        ],

        "ENTREGADO" => [
            "texto" => "Entregado",
            "icono" => "bi-house-check"
        ]

    ];


    //======================================================
    // ORDEN DE ESTADOS
    //======================================================

    $ordenEstados = [

        "PENDIENTE",
        "CONFIRMADO",
        "PREPARANDO",
        "ASIGNADO",
        "OBTENIDO",
        "ENTREGADO"

    ];


    $estadoParaProgreso = $estadoActual;


    //======================================================
    // ENVIADO SE CONSIDERA DESPUÉS DE OBTENIDO
    //======================================================

    if ($estadoActual === "ENVIADO") {

        $estadoParaProgreso = "OBTENIDO";
    }


    //======================================================
    // POSICIÓN ACTUAL
    //======================================================

    $posActual = array_search(
        $estadoParaProgreso,
        $ordenEstados,
        true
    );


    if ($posActual === false) {

        $posActual = 0;
    }


    //======================================================
    // PERMITIR CONFIRMACIÓN
    //======================================================

    $puedeConfirmarEntrega = in_array(
        $estadoActual,
        [
            "ASIGNADO",
            "OBTENIDO",
            "ENVIADO"
        ],
        true
    );


    //======================================================
    // TOTAL DE PRODUCTOS
    //======================================================

    $cantidadProductos =
        count($productos);


    //======================================================
    // CÁLCULO DE TOTALES
    //======================================================

    $subtotalGeneral = 0;

    $totalImpuestos = 0;

    $cantidadUnidades = 0;


    foreach ($productos as $producto) {

        $cantidad = (int)(
            $producto["cantidad"]
            ??
            $producto["cantidad_pedido_producto"]
            ??
            0
        );


        $subtotal = (float)(
            $producto["sub_total"]
            ?? 0
        );


        $impuesto = (float)(
            $producto["monto_impuesto"]
            ?? 0
        );


        $cantidadUnidades += $cantidad;

        $subtotalGeneral += $subtotal;

        $totalImpuestos += $impuesto;
    }


    $totalVenta = (float)(
        $pedido["total_venta"]
        ?? 0
    );


    //======================================================
    // CALCULAR IMPUESTO SI NO VIENE DESDE EL DETALLE
    //======================================================

    if (
        $totalImpuestos <= 0 &&
        $totalVenta > $subtotalGeneral
    ) {

        $totalImpuestos =
            $totalVenta -
            $subtotalGeneral;
    }

    ?>
    <div class="detalle-pedido-page">


        <!--=====================================================
BREADCRUMB
======================================================-->

        <section class="container pt-3 pb-3">

            <nav aria-label="breadcrumb">

                <ol class="breadcrumb bg-white shadow-sm rounded-4 p-3 mb-0">

                    <li class="breadcrumb-item">

                        <a
                            href="index.php"
                            class="text-decoration-none">

                            <i class="bi bi-house me-1"></i>

                            Inicio

                        </a>

                    </li>

                    <li class="breadcrumb-item">

                        <a
                            href="mis_pedidos.php"
                            class="text-decoration-none">

                            Mis pedidos

                        </a>

                    </li>

                    <li
                        class="breadcrumb-item active"
                        aria-current="page">

                        Detalle del pedido

                    </li>

                </ol>

            </nav>

        </section>


        <!--=====================================================
CABECERA
======================================================-->

        <section class="container mb-4">

            <div class="card detalle-pedido-card shadow-sm">

                <div class="pedido-header p-4 p-lg-5">

                    <div class="pedido-header-content">

                        <div class="row align-items-center g-4">

                            <div class="col-lg-8">

                                <div class="d-flex align-items-center gap-3 mb-3">

                                    <div class="pedido-icon">

                                        <i class="bi bi-bag-check-fill fs-2"></i>

                                    </div>

                                    <div>

                                        <div class="small opacity-75">
                                            PEDIDO
                                        </div>

                                        <div class="pedido-numero">

                                            #
                                            <?php
                                            echo $numeroPedido;
                                            ?>

                                        </div>

                                    </div>

                                </div>


                                <p class="mb-3 opacity-75">

                                    Aquí puedes consultar el estado,
                                    productos, pago, entrega y
                                    repartidor de tu pedido.

                                </p>


                                <div class="pedido-meta">

                                    <span>

                                        <i class="bi bi-calendar3"></i>

                                        <?php
                                        echo $fechaVenta;
                                        ?>

                                    </span>


                                    <?php if ($horaVenta !== "") : ?>

                                        <span>

                                            <i class="bi bi-clock"></i>

                                            <?php
                                            echo $horaVenta;
                                            ?>

                                        </span>

                                    <?php endif; ?>


                                    <span>

                                        <i class="bi <?php
                                                        echo limpiarTexto(
                                                            $estadoConfig["icono"]
                                                        );
                                                        ?>"></i>

                                        <?php
                                        echo limpiarTexto(
                                            $estadoConfig["texto"]
                                        );
                                        ?>

                                    </span>

                                </div>

                            </div>


                            <?php if ($estadoActual === "ENTREGADO") : ?>

                                <div class="col-lg-4 text-lg-end">

                                    <a
                                        href="descargar_comprobante.php?id_ticket_ventas=<?php echo $idPedido; ?>"
                                        target="_blank"
                                        class="btn btn-light btn-lg shadow-sm">

                                        <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>

                                        Ver comprobante

                                    </a>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!--=====================================================
ESTADO ACTUAL
======================================================-->

        <section class="container mb-4">

            <div class="card detalle-pedido-card shadow-sm">

                <div class="card-body p-4">

                    <div class="estado-card">

                        <div class="row align-items-center g-3">

                            <div class="col-md-8">

                                <div class="d-flex align-items-center gap-3">

                                    <div
                                        class="estado-icon bg-<?php
                                                                echo limpiarTexto(
                                                                    $estadoConfig["color"]
                                                                );
                                                                ?> bg-opacity-10">

                                        <i
                                            class="bi <?php
                                                        echo limpiarTexto(
                                                            $estadoConfig["icono"]
                                                        );
                                                        ?> text-<?php
                                            echo limpiarTexto(
                                                $estadoConfig["color"]
                                            );
                                            ?> fs-3">
                                        </i>

                                    </div>

                                    <div>

                                        <small class="text-muted">
                                            ESTADO ACTUAL
                                        </small>

                                        <h4 class="fw-bold mb-1">

                                            <?php
                                            echo limpiarTexto(
                                                $estadoConfig["texto"]
                                            );
                                            ?>

                                        </h4>

                                        <p class="text-muted mb-0">

                                            <?php
                                            echo limpiarTexto(
                                                $estadoConfig["descripcion"]
                                            );
                                            ?>

                                        </p>

                                    </div>

                                </div>

                            </div>


                            <?php if ($puedeConfirmarEntrega) : ?>

                                <div class="col-md-4 text-md-end">

                                    <button
                                        type="button"
                                        class="btn btn-success btn-lg btnAbrirConfirmarEntrega"
                                        data-id-pedido="<?php echo $idPedido; ?>"
                                        data-numero-pedido="<?php echo $numeroPedido; ?>">

                                        <i class="bi bi-check-circle-fill me-2"></i>

                                        Confirmar entrega

                                    </button>

                                    <div class="small text-muted mt-2">

                                        ¿Ya recibiste tu pedido?

                                    </div>

                                </div>

                            <?php endif; ?>

                        </div>


                        <?php if ($estadoActual === "CANCELADO") : ?>

                            <div class="alert alert-danger rounded-4 mt-4 mb-0">

                                <div class="d-flex gap-3">

                                    <i class="bi bi-x-circle-fill fs-4"></i>

                                    <div>

                                        <strong>
                                            Pedido cancelado
                                        </strong>

                                        <?php if (
                                            !empty($pedido["observacion_envio"])
                                        ) : ?>

                                            <div class="mt-1">

                                                <strong>
                                                    Motivo:
                                                </strong>

                                                <?php
                                                echo limpiarTexto(
                                                    $pedido["observacion_envio"]
                                                );
                                                ?>

                                            </div>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>


                        <?php if ($estadoActual === "NO_ENTREGADO") : ?>

                            <div class="alert alert-danger rounded-4 mt-4 mb-0">

                                <div class="d-flex gap-3">

                                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>

                                    <div>

                                        <strong>
                                            No fue posible entregar el pedido.
                                        </strong>

                                        <?php if (
                                            !empty($pedido["observacion_envio"])
                                        ) : ?>

                                            <div class="mt-1">

                                                <strong>
                                                    Observación:
                                                </strong>

                                                <?php
                                                echo limpiarTexto(
                                                    $pedido["observacion_envio"]
                                                );
                                                ?>

                                            </div>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </section>


        <!--=====================================================
SEGUIMIENTO
======================================================-->

        <section class="container mb-4">

            <div class="card detalle-pedido-card shadow-sm">

                <div class="card-body p-4 p-lg-5">

                    <div class="d-flex align-items-center gap-3 mb-4">

                        <div class="detalle-icon-box bg-primary bg-opacity-10">

                            <i class="bi bi-truck text-primary fs-3"></i>

                        </div>

                        <div>

                            <h5 class="section-title mb-1">
                                Seguimiento del pedido
                            </h5>

                            <div class="section-subtitle">
                                Consulta el progreso de tu pedido.
                            </div>

                        </div>

                    </div>


                    <?php if (
                        !in_array(
                            $estadoActual,
                            [
                                "CANCELADO",
                                "NO_ENTREGADO"
                            ],
                            true
                        )
                    ) : ?>

                        <div class="seguimiento-wrapper">

                            <div class="seguimiento-linea"></div>

                            <div class="row flex-nowrap g-0">

                                <?php foreach (
                                    $estadosSeguimiento
                                    as $nombreEstado => $itemEstado
                                ) : ?>

                                    <?php

                                    $posEstado = array_search(
                                        $nombreEstado,
                                        $ordenEstados,
                                        true
                                    );


                                    $completado =
                                        $posEstado !== false &&
                                        $posEstado <= $posActual;


                                    $actual =
                                        $nombreEstado ===
                                        $estadoParaProgreso;

                                    ?>

                                    <div
                                        class="col seguimiento-item text-center
                                <?php
                                    echo $completado
                                        ? "completado "
                                        : "";

                                    echo $actual
                                        ? "actual"
                                        : "";
                                ?>">

                                        <div class="seguimiento-circulo">

                                            <i
                                                class="bi <?php
                                                            echo limpiarTexto(
                                                                $itemEstado["icono"]
                                                            );
                                                            ?> fs-4">
                                            </i>

                                        </div>

                                        <div class="seguimiento-texto">

                                            <?php
                                            echo limpiarTexto(
                                                $itemEstado["texto"]
                                            );
                                            ?>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>


                        <?php if ($estadoActual === "ENTREGADO") : ?>

                            <div class="alert alert-success rounded-4 mt-4 mb-0">

                                <div class="d-flex align-items-center gap-3">

                                    <i class="bi bi-check-circle-fill fs-3"></i>

                                    <div>

                                        <strong>
                                            ¡Pedido entregado!
                                        </strong>

                                        <div class="small">
                                            Gracias por comprar con nosotros.
                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>

                    <?php endif; ?>

                </div>

            </div>

        </section>


        <!--=====================================================
REPARTIDOR
======================================================-->

        <?php if (
            $tieneRepartidor &&
            in_array(
                $estadoActual,
                [
                    "ASIGNADO",
                    "OBTENIDO",
                    "ENVIADO",
                    "ENTREGADO"
                ],
                true
            )
        ) : ?>

            <section class="container mb-4">

                <div class="card detalle-pedido-card shadow-sm repartidor-section">


                    <!--=============================================
        CABECERA
        ==============================================-->

                    <div class="repartidor-banner">

                        <div class="d-flex align-items-center gap-3">

                            <div class="detalle-icon-box bg-primary bg-opacity-10">

                                <i class="bi bi-person-badge-fill text-primary fs-3"></i>

                            </div>

                            <div>

                                <h5 class="section-title mb-1">

                                    <?php
                                    echo $estadoActual === "ENTREGADO"
                                        ? "Repartidor que realizó la entrega"
                                        : "Repartidor asignado";
                                    ?>

                                </h5>

                                <div class="section-subtitle">

                                    <?php if ($estadoActual === "ENTREGADO") : ?>

                                        Información del repartidor que realizó
                                        la entrega de tu pedido.

                                    <?php else : ?>

                                        Conoce a la persona encargada
                                        de entregar tu pedido.

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=============================================
        PERFIL
        ==============================================-->

                    <div class="repartidor-card border-0 rounded-0">

                        <div class="repartidor-profile">

                            <div class="row align-items-center g-4">


                                <!--=================================
                    FOTO DEL REPARTIDOR
                    ==================================-->

                                <div class="col-md-auto text-center">

                                    <?php if ($tieneImagenRepartidor) : ?>

                                        <img
                                            src="data:image/jpeg;base64,<?php
                                                                        echo base64_encode(
                                                                            $repartidorImagen
                                                                        );
                                                                        ?>"
                                            alt="<?php
                                                    echo limpiarTexto(
                                                        $repartidorNombreCompleto !== ""
                                                            ? $repartidorNombreCompleto
                                                            : "Repartidor"
                                                    );
                                                    ?>"
                                            class="repartidor-avatar">

                                    <?php else : ?>

                                        <div class="repartidor-avatar-placeholder">

                                            <i class="bi bi-person-fill fs-1"></i>

                                        </div>

                                    <?php endif; ?>

                                </div>


                                <!--=================================
                    INFORMACIÓN PRINCIPAL
                    ==================================-->

                                <div class="col-md">

                                    <div class="repartidor-nombre">

                                        <?php

                                        echo limpiarTexto(
                                            $repartidorNombreCompleto !== ""
                                                ? $repartidorNombreCompleto
                                                : "Repartidor asignado"
                                        );

                                        ?>

                                    </div>


                                    <div class="repartidor-rol mt-1">

                                        <i class="bi bi-person-vcard me-1"></i>

                                        <?php

                                        echo limpiarTexto(
                                            $repartidorRol !== ""
                                                ? $repartidorRol
                                                : "Repartidor"
                                        );

                                        ?>

                                    </div>


                                    <?php if (
                                        $repartidorEstado ===
                                        "ACTIVO"
                                    ) : ?>

                                        <span
                                            class="repartidor-estado bg-success bg-opacity-10 text-success">

                                            <i
                                                class="bi bi-circle-fill"
                                                style="font-size:.45rem;">
                                            </i>

                                            Personal activo

                                        </span>

                                    <?php else : ?>

                                        <span
                                            class="repartidor-estado bg-secondary bg-opacity-10 text-secondary">

                                            <i class="bi bi-person-fill"></i>

                                            Repartidor asignado

                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>


                        <!--=========================================
            DATOS DE CONTACTO
            ==========================================-->

                        <div class="repartidor-contacto">

                            <div class="row g-3">


                                <!--=================================
                    CARGO
                    ==================================-->

                                <div class="col-md-6">

                                    <div class="repartidor-dato">

                                        <div class="repartidor-dato-icono">

                                            <i class="bi bi-person-vcard-fill"></i>

                                        </div>

                                        <div>

                                            <span class="repartidor-dato-label">
                                                Cargo
                                            </span>

                                            <div class="repartidor-dato-valor">

                                                <?php

                                                echo limpiarTexto(
                                                    $repartidorRol !== ""
                                                        ? $repartidorRol
                                                        : "Repartidor"
                                                );

                                                ?>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <!--=================================
                    CELULAR
                    ==================================-->

                                <div class="col-md-6">

                                    <div class="repartidor-dato">

                                        <div class="repartidor-dato-icono">

                                            <i class="bi bi-telephone-fill"></i>

                                        </div>

                                        <div>

                                            <span class="repartidor-dato-label">
                                                Celular
                                            </span>

                                            <div class="repartidor-dato-valor">

                                                <?php

                                                echo $repartidorCelular !== ""
                                                    ? limpiarTexto(
                                                        $repartidorCelular
                                                    )
                                                    : "No disponible";

                                                ?>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!--=========================================
                CONTACTO
                ==========================================-->

                            <?php if ($estadoActual === "ENTREGADO") : ?>

                                <!--=====================================
                    PEDIDO ENTREGADO
                    NO CONTACTAR AL REPARTIDOR
                    ======================================-->

                                <div class="contacto-empresa mt-3">

                                    <div class="d-flex align-items-start gap-3">

                                        <div class="contacto-empresa-icono">

                                            <i class="bi bi-headset fs-5"></i>

                                        </div>

                                        <div>

                                            <strong class="d-block mb-1">

                                                ¿Necesitas ayuda con tu pedido?

                                            </strong>

                                            <div class="small text-muted">

                                                Este pedido ya fue entregado.
                                                Para cualquier consulta,
                                                reclamo o inconveniente,
                                                por favor comunícate directamente
                                                con nuestra empresa.

                                            </div>

                                        </div>

                                    </div>

                                </div>


                            <?php elseif (
                                $numeroWhatsApp !== ""
                            ) : ?>

                                <!--=====================================
                    WHATSAPP REPARTIDOR
                    ======================================-->

                                <div class="mt-3">

                                    <a
                                        href="https://wa.me/<?php
                                                            echo $numeroWhatsApp;
                                                            ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-success btn-whatsapp w-100">

                                        <i class="bi bi-whatsapp me-2"></i>

                                        Contactar al repartidor por WhatsApp

                                    </a>

                                </div>


                            <?php elseif (
                                $repartidorCelular !== ""
                            ) : ?>

                                <!--=====================================
                    LLAMAR REPARTIDOR
                    ======================================-->

                                <div class="mt-3">

                                    <a
                                        href="tel:<?php
                                                    echo limpiarTexto(
                                                        $repartidorCelular
                                                    );
                                                    ?>"
                                        class="btn btn-outline-primary w-100">

                                        <i class="bi bi-telephone-fill me-2"></i>

                                        Llamar al repartidor

                                    </a>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </section>


        <?php elseif (
            $estadoActual === "PREPARANDO"
        ) : ?>


            <!--=====================================================
REPARTIDOR PENDIENTE
======================================================-->

            <section class="container mb-4">

                <div class="repartidor-pendiente">

                    <div class="d-flex align-items-center gap-3">

                        <div class="detalle-icon-box bg-info bg-opacity-10">

                            <i class="bi bi-person-plus text-info fs-3"></i>

                        </div>

                        <div>

                            <h6 class="fw-bold mb-1">

                                Repartidor pendiente de asignación

                            </h6>

                            <p class="text-muted mb-0 small">

                                Cuando tu pedido esté listo para
                                ser enviado, te mostraremos aquí
                                la información del repartidor asignado.

                            </p>

                        </div>

                    </div>

                </div>

            </section>

        <?php endif; ?>


        <!--=====================================================
INFORMACIÓN DEL PEDIDO
======================================================-->

        <section class="container mb-4">

            <div class="row g-4">


                <!--=============================================
        CLIENTE
        ==============================================-->

                <div class="col-lg-7">

                    <div
                        class="card detalle-pedido-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="d-flex align-items-center gap-3 mb-4">

                                <div class="detalle-icon-box bg-primary bg-opacity-10">

                                    <i class="bi bi-person-circle text-primary fs-3"></i>

                                </div>

                                <div>

                                    <h5 class="section-title mb-1">
                                        Información del cliente
                                    </h5>

                                    <div class="section-subtitle">
                                        Datos asociados al pedido.
                                    </div>

                                </div>

                            </div>


                            <div class="row g-3">


                                <div class="col-md-6">

                                    <div class="info-item">

                                        <span class="info-item-label">
                                            Nombre completo
                                        </span>

                                        <div class="info-item-value">

                                            <?php
                                            echo limpiarTexto(
                                                $pedido["cliente"] ?? "-"
                                            );
                                            ?>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <div class="info-item">

                                        <span class="info-item-label">
                                            DNI / RUC
                                        </span>

                                        <div class="info-item-value">

                                            <?php
                                            echo limpiarTexto(
                                                $pedido["dni_o_ruc"] ?? "-"
                                            );
                                            ?>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <div class="info-item">

                                        <span class="info-item-label">
                                            Correo electrónico
                                        </span>

                                        <div class="info-item-value">

                                            <?php
                                            echo limpiarTexto(
                                                $pedido["email"] ?? "-"
                                            );
                                            ?>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <div class="info-item">

                                        <span class="info-item-label">
                                            Celular
                                        </span>

                                        <div class="info-item-value">

                                            <?php
                                            echo limpiarTexto(
                                                $pedido["celular"] ?? "-"
                                            );
                                            ?>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!--=============================================
        PAGO Y ENVÍO
        ==============================================-->

                <div class="col-lg-5">

                    <div
                        class="card detalle-pedido-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="d-flex align-items-center gap-3 mb-4">

                                <div class="detalle-icon-box bg-success bg-opacity-10">

                                    <i class="bi bi-credit-card text-success fs-3"></i>

                                </div>

                                <div>

                                    <h5 class="section-title mb-1">
                                        Pago y envío
                                    </h5>

                                    <div class="section-subtitle">
                                        Información de entrega.
                                    </div>

                                </div>

                            </div>


                            <div class="info-item mb-3">

                                <span class="info-item-label">
                                    Método de pago
                                </span>

                                <div class="info-item-value">

                                    <i class="bi bi-wallet2 me-1"></i>

                                    <?php
                                    echo limpiarTexto(
                                        $pedido["metodo_pago"] ?? "-"
                                    );
                                    ?>

                                </div>

                            </div>


                            <div class="direccion-card">

                                <div class="d-flex gap-3">

                                    <i class="bi bi-geo-alt-fill text-danger fs-4"></i>

                                    <div>

                                        <span class="info-item-label">
                                            Dirección de entrega
                                        </span>

                                        <div class="fw-semibold">

                                            <?php
                                            echo limpiarTexto(
                                                $pedido["direccion_envio"]
                                                    ?? "-"
                                            );
                                            ?>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!--=====================================================
PRODUCTOS
======================================================-->

        <section class="container mb-4">

            <div class="card detalle-pedido-card shadow-sm">

                <div class="card-body p-4 p-lg-5">

                    <div
                        class="d-flex justify-content-between
                       align-items-center
                       flex-wrap gap-3 mb-4">

                        <div class="d-flex align-items-center gap-3">

                            <div class="detalle-icon-box bg-primary bg-opacity-10">

                                <i class="bi bi-cart-check-fill text-primary fs-3"></i>

                            </div>

                            <div>

                                <h5 class="section-title mb-1">
                                    Productos comprados
                                </h5>

                                <div class="section-subtitle">
                                    Productos incluidos en este pedido.
                                </div>

                            </div>

                        </div>


                        <span class="badge bg-primary rounded-pill px-3 py-2">

                            <?php
                            echo $cantidadProductos;
                            ?>

                            <?php
                            echo $cantidadProductos === 1
                                ? " producto"
                                : " productos";
                            ?>

                        </span>

                    </div>


                    <?php if (empty($productos)) : ?>

                        <div class="alert alert-light border rounded-4">

                            <i class="bi bi-info-circle me-2"></i>

                            No se encontraron productos asociados
                            a este pedido.

                        </div>

                    <?php else : ?>

                        <div class="d-flex flex-column gap-3">

                            <?php foreach (
                                $productos as $producto
                            ) : ?>

                                <?php

                                $cantidad = (int)(
                                    $producto["cantidad"]
                                    ??
                                    $producto["cantidad_pedido_producto"]
                                    ??
                                    0
                                );


                                $precio = (float)(
                                    $producto["precio"]
                                    ?? 0
                                );


                                $subtotal = (float)(
                                    $producto["sub_total"]
                                    ?? 0
                                );

                                ?>

                                <div class="producto-card">

                                    <div class="row align-items-center g-3">


                                        <div class="col-lg-6">

                                            <div
                                                class="d-flex align-items-center gap-3">

                                                <?php if (
                                                    !empty($producto["imagenes"])
                                                ) : ?>

                                                    <img
                                                        src="data:image/jpeg;base64,<?php
                                                                                    echo base64_encode(
                                                                                        $producto["imagenes"]
                                                                                    );
                                                                                    ?>"
                                                        alt="<?php
                                                                echo limpiarTexto(
                                                                    $producto["nombre"]
                                                                        ?? "Producto"
                                                                );
                                                                ?>"
                                                        class="producto-imagen">

                                                <?php else : ?>

                                                    <div class="producto-placeholder">

                                                        <i
                                                            class="bi bi-image text-muted fs-3">
                                                        </i>

                                                    </div>

                                                <?php endif; ?>


                                                <div class="min-width-0">

                                                    <h6 class="fw-bold mb-1">

                                                        <?php
                                                        echo limpiarTexto(
                                                            $producto["nombre"]
                                                                ?? "Producto"
                                                        );
                                                        ?>

                                                    </h6>


                                                    <?php if (
                                                        !empty($producto["codigo"])
                                                    ) : ?>

                                                        <small class="text-muted">

                                                            Código:

                                                            <?php
                                                            echo limpiarTexto(
                                                                $producto["codigo"]
                                                            );
                                                            ?>

                                                        </small>

                                                    <?php endif; ?>

                                                </div>

                                            </div>

                                        </div>


                                        <div class="col-4 col-lg-2">

                                            <small class="text-muted d-block">
                                                Cantidad
                                            </small>

                                            <strong>
                                                <?php echo $cantidad; ?>
                                            </strong>

                                        </div>


                                        <div class="col-4 col-lg-2">

                                            <small class="text-muted d-block">
                                                Precio
                                            </small>

                                            <strong>

                                                S/
                                                <?php
                                                echo formatoMoneda(
                                                    $precio
                                                );
                                                ?>

                                            </strong>

                                        </div>


                                        <div class="col-4 col-lg-2 text-end">

                                            <small class="text-muted d-block">
                                                Subtotal
                                            </small>

                                            <strong class="text-primary">

                                                S/
                                                <?php
                                                echo formatoMoneda(
                                                    $subtotal
                                                );
                                                ?>

                                            </strong>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>


                    <!--=========================================
            RESUMEN
            ==========================================-->

                    <div class="row justify-content-end mt-4">

                        <div class="col-lg-5">

                            <div class="resumen-total">

                                <div
                                    class="d-flex justify-content-between mb-2">

                                    <span class="text-muted">
                                        Unidades
                                    </span>

                                    <strong>
                                        <?php
                                        echo $cantidadUnidades;
                                        ?>
                                    </strong>

                                </div>


                                <div
                                    class="d-flex justify-content-between mb-2">

                                    <span class="text-muted">
                                        Total de productos
                                    </span>

                                    <strong>

                                        S/
                                        <?php
                                        echo formatoMoneda(
                                            $subtotalGeneral
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <?php if (
                                    $totalImpuestos > 0
                                ) : ?>

                                    <div
                                        class="d-flex justify-content-between mb-2">

                                        <span class="text-muted">
                                            Impuestos
                                        </span>

                                        <strong>

                                            S/
                                            <?php
                                            echo formatoMoneda(
                                                $totalImpuestos
                                            );
                                            ?>

                                        </strong>

                                    </div>

                                <?php endif; ?>


                                <hr>


                                <div
                                    class="d-flex justify-content-between align-items-center">

                                    <span class="fw-bold">
                                        TOTAL
                                    </span>

                                    <span
                                        class="fw-bold text-primary total-final">

                                        S/
                                        <?php
                                        echo formatoMoneda(
                                            $totalVenta
                                        );
                                        ?>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!--=====================================================
ACCIONES
======================================================-->

        <section class="container mb-5">

            <div
                class="card border-0 shadow-sm acciones-pedido">

                <div class="card-body p-3">

                    <div
                        class="d-flex justify-content-between
                       align-items-center
                       flex-wrap gap-2">

                        <a
                            href="mis_pedidos.php"
                            class="btn btn-outline-secondary">

                            <i class="bi bi-arrow-left me-1"></i>

                            Volver a mis pedidos

                        </a>
                    </div>

                </div>

            </div>

        </section>


        <!--=====================================================
TESTIMONIOS
======================================================-->

        <?php if (
            $estadoActual === "ENTREGADO"
        ) : ?>

            <section class="container mb-5">

                <div class="card detalle-pedido-card shadow-sm">

                    <div class="card-header bg-white p-4">

                        <div
                            class="d-flex justify-content-between
                       align-items-center
                       flex-wrap gap-2">

                            <div class="d-flex align-items-center gap-3">

                                <div
                                    class="detalle-icon-box bg-warning bg-opacity-10">

                                    <i class="bi bi-star-fill text-warning fs-3"></i>

                                </div>

                                <div>

                                    <h5 class="section-title mb-1">
                                        Califica tu compra
                                    </h5>

                                    <div class="section-subtitle">
                                        Tu opinión nos ayuda a mejorar.
                                    </div>

                                </div>

                            </div>


                            <span
                                class="badge bg-success rounded-pill px-3 py-2">

                                <i class="bi bi-check-circle-fill me-1"></i>

                                Pedido entregado

                            </span>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <div class="alert alert-success rounded-4">

                            <div class="d-flex gap-3">

                                <i class="bi bi-chat-heart-fill fs-4"></i>

                                <div>

                                    <strong>
                                        ¡Gracias por tu compra!
                                    </strong>

                                    <div class="small mt-1">

                                        Ahora puedes calificar cada
                                        producto y dejar un comentario
                                        sobre tu experiencia.

                                    </div>

                                </div>

                            </div>

                        </div>


                        <div id="contenedorTestimonios">

                            <div class="text-center py-5">

                                <div
                                    class="spinner-border text-primary"
                                    role="status">
                                </div>

                                <p class="text-muted mt-3 mb-0">
                                    Cargando productos...
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

        <?php endif; ?>


    </div>


    <!--=====================================================
MODAL CONFIRMAR ENTREGA
======================================================-->

    <?php

    include
        "modal/modal_confirmar_entrega_cliente.php";

    ?>


    <!--=====================================================
TESTIMONIOS
======================================================-->

    <?php if (
        $estadoActual === "ENTREGADO"
    ) : ?>

        <?php

        include
            "includes/modal_registro_testimonio.php";

        ?>

        <script>
            const ID_TICKET =
                <?php
                echo $idPedido;
                ?>;
        </script>

        <script src="js/testimonios_producto.js"></script>

    <?php endif; ?>


    <!--=====================================================
CARRITO
======================================================-->

    <?php

    include
        "includes/carrito_offcanvas.php";

    ?>


    <!--=====================================================
SCRIPTS
======================================================-->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    </script>

    <script src="js/carrito.js"></script>

    <script src="js/mis_pedidos.js"></script>

    <script src="js/notificaciones.js"></script>