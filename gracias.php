<?php
session_start();

if (!isset($_GET["id"])) {

    header("Location:index.php");

    exit();
}

require_once "controladores/conexion.php";

$idTicket = intval($_GET["id"]);

$sql = "SELECT

            tv.id_ticket_ventas,
            tv.fecha_venta,
            tv.hora_venta,
            tv.total_venta,
            tv.estado_venta,
            c.nombre

        FROM ticket_ventas tv

        LEFT JOIN clientes c

        ON c.idCliente = tv.idCliente

        WHERE tv.id_ticket_ventas=?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(

    $stmt,

    "i",

    $idTicket

);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) == 0) {

    header("Location:index.php");

    exit();
}

$venta = mysqli_fetch_assoc($resultado);

?>
<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"

        content="width=device-width, initial-scale=1">

    <title>Compra realizada</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        body {

            background: #f5f7fb;

        }

        .cardGracias {

            max-width: 700px;

            margin: 60px auto;

            border: none;

            border-radius: 20px;

            box-shadow: 0 15px 40px rgba(0, 0, 0, .08);

            overflow: hidden;

        }

        .icono {

            font-size: 90px;

            color: #28a745;

            animation: zoom 1s;

        }

        @keyframes zoom {

            0% {

                transform: scale(.2);

                opacity: 0;

            }

            100% {

                transform: scale(1);

                opacity: 1;

            }

        }
    </style>

</head>

<body>

    <div class="container">

        <div class="card cardGracias">

            <div class="card-body text-center p-5">

                <i class="fas fa-circle-check icono"></i>

                <h2 class="mt-4">

                    ¡Gracias por tu compra!

                </h2>

                <p class="text-muted">

                    Tu pedido fue registrado correctamente.

                </p>

                <hr>

                <div class="row text-start">

                    <div class="col-md-6 mb-3">

                        <strong>Pedido:</strong><br>

                        #<?= $venta["id_ticket_ventas"] ?>

                    </div>

                    <div class="col-md-6 mb-3">

                        <strong>Cliente:</strong><br>

                        <?= htmlspecialchars($venta["nombre"]) ?>

                    </div>

                    <div class="col-md-6 mb-3">

                        <strong>Fecha:</strong><br>

                        <?= $venta["fecha_venta"] ?>

                    </div>

                    <div class="col-md-6 mb-3">

                        <strong>Hora:</strong><br>

                        <?= $venta["hora_venta"] ?>

                    </div>

                    <div class="col-md-6 mb-3">

                        <strong>Total:</strong><br>

                        S/ <?= number_format($venta["total_venta"], 2) ?>

                    </div>

                    <div class="col-md-6 mb-3">

                        <strong>Estado:</strong><br>

                        <span class="badge bg-success">

                            <?= $venta["estado_venta"] ?>

                        </span>

                    </div>

                </div>

                <div class="mt-4">

                    <a href="tienda.php"

                        class="btn btn-primary btn-lg">

                        <i class="fa fa-store"></i>

                        Seguir comprando

                    </a>

                    <a href="#"

                        class="btn btn-outline-dark btn-lg">

                        <i class="fa fa-box"></i>

                        Mis pedidos

                    </a>

                </div>

            </div>

        </div>

    </div>

</body>

</html>