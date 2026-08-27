<?php
session_start();

if (!isset($_SESSION["idCliente"])) {

    header("Location: login_cliente.php");
    exit();
}

require_once "controladores/conexion.php";

$idCliente = $_SESSION["idCliente"];

/*=========================================================
=            VALIDAR PEDIDO
=========================================================*/

if (!isset($_GET["id"])) {

    die("Pedido no especificado.");
}

$idPedido = intval($_GET["id"]);

if ($idPedido <= 0) {

    die("Pedido inválido.");
}
/*=========================================================
=            CONSULTAR PEDIDO
=========================================================*/

$sql = "SELECT

            tv.*,

            mp.nombre AS metodo_pago,

            c.nombre,
            c.celular,
            c.email,
            c.direccion,
            c.provincia,
            c.distrito,

            ua.nombreEmpresa

        FROM ticket_ventas tv

        INNER JOIN clientes c
            ON c.idCliente = tv.idCliente

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago = tv.id_metodo_pago

        INNER JOIN usuario_acceso ua
            ON ua.id_user = tv.id_user

        WHERE

            tv.id_ticket_ventas = ?

        AND

            tv.idCliente = ?";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idPedido,
    $idCliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) == 0) {

    die("El pedido no existe.");
}

$pedido = mysqli_fetch_assoc($resultado);
/*=========================================================
=            COMPROBANTE
=========================================================*/

$comprobante =

    $pedido["serie"]

    .

    "-"

    .

    str_pad(

        $pedido["numero"],

        8,

        "0",

        STR_PAD_LEFT

    );
/*=========================================================
=            ESTADO ACTUAL
=========================================================*/

$estadoActual = $pedido["estado_envio"];
$fechas = [

    "confirmado" => $pedido["fecha_confirmado"],

    "preparando" => $pedido["fecha_preparando"],

    "enviado" => $pedido["fecha_enviado"],

    "entregado" => $pedido["fecha_entregado"]

];
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>

        Seguimiento del pedido

    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        rel="stylesheet">
    <style>
        body {

            background: #f5f6fa;

        }

        .card {

            border: none;

            border-radius: 18px;

            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);

        }

        .estado-actual {

            font-size: 18px;

            font-weight: bold;

        }

        .timeline {

            display: flex;

            justify-content: space-between;

            margin-top: 40px;

            position: relative;

        }

        .timeline::before {

            content: "";

            position: absolute;

            top: 22px;

            left: 0;

            width: 100%;

            height: 4px;

            background: #dcdcdc;

        }

        .paso {

            position: relative;

            text-align: center;

            width: 20%;

            z-index: 2;

        }

        .circulo {

            width: 45px;

            height: 45px;

            border-radius: 50%;

            background: #ced4da;

            color: #fff;

            margin: auto;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 18px;

            font-weight: bold;

        }

        .paso.completado .circulo {

            background: #198754;

        }

        .paso.actual .circulo {

            background: #0d6efd;

        }

        .fecha {

            font-size: 12px;

            color: #6c757d;

        }

        .titulo-paso {

            margin-top: 12px;

            font-weight: 600;

        }
    </style>

</head>

<body>
    <div class="container py-5">
        <div class="card">

            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h3>

                            <i class="fas fa-truck text-primary"></i>

                            Seguimiento del pedido

                        </h3>

                        <p class="text-muted mb-0">

                            <?= htmlspecialchars($pedido["nombreEmpresa"]) ?>

                        </p>

                    </div>

                    <div class="text-end">

                        <h5 class="mb-0">

                            <?= htmlspecialchars($comprobante) ?>

                        </h5>

                        <small class="text-muted">

                            Pedido N°

                            <?= $pedido["id_ticket_ventas"] ?>

                        </small>

                    </div>

                </div>
                <div class="row mb-4">

                    <div class="col-md-6">

                        <strong>Cliente</strong>

                        <br>

                        <?= htmlspecialchars($pedido["nombre"]) ?>

                        <br>

                        <?= htmlspecialchars($pedido["celular"]) ?>

                        <br>

                        <?= htmlspecialchars($pedido["email"]) ?>

                    </div>

                    <div class="col-md-6">

                        <strong>Dirección de entrega</strong>

                        <br>

                        <?= htmlspecialchars($pedido["direccion"]) ?>

                        <br>

                        <?= htmlspecialchars($pedido["distrito"]) ?>

                        <br>

                        <?= htmlspecialchars($pedido["provincia"]) ?>

                    </div>

                </div>
                <div class="alert alert-primary">

                    <strong>

                        Estado actual:

                    </strong>

                    <span class="estado-actual">

                        <?= htmlspecialchars($estadoActual) ?>

                    </span>

                </div>
                <div class="timeline">
                    <div class="paso completado">

                        <div class="circulo">

                            <i class="fas fa-shopping-cart"></i>

                        </div>

                        <div class="titulo-paso">

                            Pedido

                        </div>

                        <div class="fecha">

                            <?= date(

                                "d/m/Y",

                                strtotime($pedido["fecha_venta"])

                            ) ?>

                        </div>

                    </div>
                    <div class="paso <?= !empty($fechas["confirmado"]) ? "completado" : "" ?>">

                        <div class="circulo">

                            <i class="fas fa-check"></i>

                        </div>

                        <div class="titulo-paso">

                            Confirmado

                        </div>

                        <div class="fecha">

                            <?= !empty($fechas["confirmado"])

                                ? date(

                                    "d/m H:i",

                                    strtotime($fechas["confirmado"])

                                )

                                : "-" ?>

                        </div>

                    </div>
                    <div class="paso <?= !empty($fechas["preparando"]) ? "completado" : "" ?>">

                        <div class="circulo">

                            <i class="fas fa-box"></i>

                        </div>

                        <div class="titulo-paso">

                            Preparando

                        </div>

                        <div class="fecha">

                            <?= !empty($fechas["preparando"])

                                ? date(

                                    "d/m H:i",

                                    strtotime($fechas["preparando"])

                                )

                                : "-" ?>

                        </div>

                    </div>
                    <div class="paso <?= !empty($fechas["enviado"]) ? "completado" : "" ?>">

                        <div class="circulo">

                            <i class="fas fa-truck"></i>

                        </div>

                        <div class="titulo-paso">

                            Enviado

                        </div>

                        <div class="fecha">

                            <?= !empty($fechas["enviado"])

                                ? date(

                                    "d/m H:i",

                                    strtotime($fechas["enviado"])

                                )

                                : "-" ?>

                        </div>

                    </div>
                    <div class="paso <?= !empty($fechas["entregado"]) ? "completado" : "" ?>">

                        <div class="circulo">

                            <i class="fas fa-home"></i>

                        </div>

                        <div class="titulo-paso">

                            Entregado

                        </div>

                        <div class="fecha">

                            <?= !empty($fechas["entregado"])

                                ? date(

                                    "d/m H:i",

                                    strtotime($fechas["entregado"])

                                )

                                : "-" ?>

                        </div>

                    </div>
                </div>
            </div>
            <?php

            /*=========================================================
=            PORCENTAJE DE AVANCE
=========================================================*/

            $porcentaje = 20;

            switch ($estadoActual) {

                case "CONFIRMADO":
                    $porcentaje = 40;
                    break;

                case "PREPARANDO":
                    $porcentaje = 60;
                    break;

                case "ENVIADO":
                    $porcentaje = 80;
                    break;

                case "ENTREGADO":
                    $porcentaje = 100;
                    break;

                case "CANCELADO":
                    $porcentaje = 0;
                    break;
            }
            ?>
            <div class="mt-5">

                <h5>

                    Progreso del pedido

                </h5>

                <div class="progress" style="height:25px;">

                    <div

                        class="progress-bar progress-bar-striped progress-bar-animated bg-success"

                        role="progressbar"

                        style="width: <?= $porcentaje ?>%;">

                        <?= $porcentaje ?>%

                    </div>

                </div>

            </div>
            <div class="mt-4">

                <div class="alert alert-light border">

                    <h5>

                        <i class="fas fa-comment-dots text-primary"></i>

                        Observaciones

                    </h5>

                    <?php

                    if (!empty($pedido["observacion_envio"])) {

                        echo nl2br(

                            htmlspecialchars(

                                $pedido["observacion_envio"]

                            )

                        );
                    } else {

                        echo "<span class='text-muted'>Aún no existen observaciones para este pedido.</span>";
                    }

                    ?>

                </div>

            </div>
            <div class="row mt-4">

                <div class="col-md-4">

                    <div class="card bg-light">

                        <div class="card-body text-center">

                            <h6>Total</h6>

                            <h4 class="text-success">

                                S/

                                <?= number_format(

                                    $pedido["total_venta"],

                                    2

                                ) ?>

                            </h4>

                        </div>

                    </div>

                </div>
                <div class="col-md-4">

                    <div class="card bg-light">

                        <div class="card-body text-center">

                            <h6>

                                Método de pago

                            </h6>

                            <strong>

                                <?= htmlspecialchars(

                                    $pedido["metodo_pago"]

                                ) ?>

                            </strong>

                        </div>

                    </div>

                </div>
                <div class="col-md-4">

                    <div class="card bg-light">

                        <div class="card-body text-center">

                            <h6>

                                Fecha del pedido

                            </h6>

                            <strong>

                                <?= date(

                                    "d/m/Y",

                                    strtotime($pedido["fecha_venta"])

                                ) ?>

                            </strong>

                        </div>

                    </div>

                </div>

            </div>
            <div class="text-center mt-5">

                <a

                    href="pedido.php?id=<?= $pedido["id_ticket_ventas"] ?>"

                    class="btn btn-primary">

                    <i class="fas fa-eye"></i>

                    Ver pedido

                </a>

                <a

                    href="generar_pdf_pedido.php?id=<?= $pedido["id_ticket_ventas"] ?>"

                    target="_blank"

                    class="btn btn-danger">

                    <i class="fas fa-file-pdf"></i>

                    Descargar PDF

                </a>

                <a

                    href="mis_pedidos.php"

                    class="btn btn-secondary">

                    <i class="fas fa-arrow-left"></i>

                    Mis pedidos

                </a>

            </div>
        </div>

    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>