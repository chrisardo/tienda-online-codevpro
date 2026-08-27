<?php
session_start();

if (!isset($_SESSION["usId"])) {

    header("Location: login.php");

    exit();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["usId"];
?>
<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>

        Pedidos Online

    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        rel="stylesheet">
    <style>
        .timeline-admin {

            position: relative;

            margin-top: 25px;

            padding-left: 40px;

        }

        .timeline-admin::before {

            content: "";

            position: absolute;

            left: 20px;

            top: 0;

            bottom: 0;

            width: 4px;

            background: #dee2e6;

            border-radius: 20px;

        }

        .timeline-item {

            position: relative;

            margin-bottom: 30px;

            opacity: .45;

            transition: .3s;

        }

        .timeline-item.active {

            opacity: 1;

        }

        .timeline-icon {

            position: absolute;

            left: -31px;

            width: 34px;

            height: 34px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #fff;

            font-size: 15px;

        }

        .timeline-content {

            padding-left: 25px;

            background: #fff;

            border: 1px solid #eee;

            border-radius: 10px;

            padding: 15px;

            box-shadow: 0 3px 12px rgba(0, 0, 0, .05);

        }

        .timeline-content h6 {

            margin-bottom: 5px;

            font-weight: 600;

        }

        .timeline-content small {

            color: #6c757d;

        }
    </style>

</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2>

                <i class="fas fa-shopping-bag text-primary"></i>

                Pedidos Online

            </h2>

            <button

                class="btn btn-success"

                id="btnActualizar">

                <i class="fas fa-rotate"></i>

                Actualizar

            </button>

        </div>
        <div class="card mb-4">

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">

                        <input

                            type="text"

                            id="buscar"

                            class="form-control"

                            placeholder="Buscar cliente o comprobante">

                    </div>

                    <div class="col-md-3">

                        <select

                            id="estado"

                            class="form-select">

                            <option value="">

                                Todos los estados

                            </option>

                            <option>

                                PENDIENTE

                            </option>

                            <option>

                                CONFIRMADO

                            </option>

                            <option>

                                PREPARANDO

                            </option>

                            <option>

                                ENVIADO

                            </option>

                            <option>

                                ENTREGADO

                            </option>

                            <option>

                                CANCELADO

                            </option>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <input

                            type="date"

                            id="fecha"

                            class="form-control">

                    </div>

                    <div class="col-md-2">

                        <button

                            class="btn btn-primary w-100"

                            id="btnBuscar">

                            Buscar

                        </button>

                    </div>

                </div>

            </div>

        </div>
        <div class="card">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>Pedido</th>

                            <th>Cliente</th>

                            <th>Total</th>

                            <th>Pago</th>

                            <th>Estado</th>

                            <th>Fecha</th>

                            <th width="180">

                                Acciones

                            </th>

                        </tr>

                    </thead>

                    <tbody id="tablaPedidos">

                        <tr>

                            <td colspan="7" class="text-center">

                                Cargando pedidos...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>
        <div

            class="modal fade"

            id="modalPedido"

            tabindex="-1">

            <div class="modal-dialog modal-xl">

                <div class="modal-content">

                    <div id="detallePedido">

                    </div>

                </div>

            </div>

        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script src="js/pedidos_online.js"></script>

</body>

</html>