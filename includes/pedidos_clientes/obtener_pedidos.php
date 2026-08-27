<?php
//=====================================================
// CoDevPro Technology
// includes/pedidos_clientes/obtener_pedidos.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "./controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

$buscar = trim($_GET["buscar"] ?? "");
$estado = trim($_GET["estado"] ?? "");
$fechaInicio = $_GET["fecha_inicio"] ?? "";
$fechaFin = $_GET["fecha_fin"] ?? "";


/*=============================================
=            CONSULTA BASE
=============================================*/

$sql = "SELECT

            tv.id_ticket_ventas,
            tv.fecha_venta,
            tv.hora_venta,
            tv.total_venta,
            tv.estado_envio,
            tv.tipo_comprobante,
            tv.serie,
            tv.numero,

            c.nombre AS cliente,
            c.email,
            c.celular,

            mp.nombre AS metodo_pago

        FROM ticket_ventas tv

        INNER JOIN clientes c
        ON tv.idCliente = c.idCliente

        LEFT JOIN metodo_pago mp
        ON tv.id_metodo_pago = mp.id_metodo_pago

        WHERE tv.id_user = ?";


$tipos = "i";
$parametros = [$idUser];


/*=============================================
=            BUSCADOR
=============================================*/

if (!empty($buscar)) {

    $sql .= " AND (

                c.nombre LIKE ?
                OR c.email LIKE ?
                OR tv.id_ticket_ventas LIKE ?
                OR tv.numero LIKE ?

            )";

    $buscarLike = "%{$buscar}%";

    $tipos .= "ssss";

    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
}


/*=============================================
=            ESTADO
=============================================*/

if (!empty($estado)) {

    $sql .= " AND tv.estado_envio = ?";

    $tipos .= "s";

    $parametros[] = $estado;
}


/*=============================================
=            FECHA INICIO
=============================================*/

if (!empty($fechaInicio)) {

    $sql .= " AND tv.fecha_venta >= ?";

    $tipos .= "s";

    $parametros[] = $fechaInicio;
}


/*=============================================
=            FECHA FIN
=============================================*/

if (!empty($fechaFin)) {

    $sql .= " AND tv.fecha_venta <= ?";

    $tipos .= "s";

    $parametros[] = $fechaFin;
}


/*=============================================
=            ORDENAMIENTO
=============================================*/

$sql .= " ORDER BY tv.id_ticket_ventas DESC";


$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    $tipos,
    ...$parametros
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
?>


<div class="table-responsive">

    <table class="table table-hover align-middle">

        <thead class="table-light">

            <tr>

                <th># Pedido</th>

                <th>Cliente</th>

                <th>Fecha</th>

                <th>Método Pago</th>

                <th>Total</th>

                <th>Estado</th>

                <th class="text-center">Acciones</th>

            </tr>

        </thead>

        <tbody>

            <?php if (mysqli_num_rows($resultado) > 0): ?>

                <?php while ($pedido = mysqli_fetch_assoc($resultado)): ?>

                    <?php

                    switch ($pedido["estado_envio"]) {

                        case "PENDIENTE":
                            $badge = "warning";
                            break;

                        case "CONFIRMADO":
                            $badge = "info";
                            break;

                        case "PREPARANDO":
                            $badge = "primary";
                            break;

                        case "ENVIADO":
                            $badge = "secondary";
                            break;

                        case "ENTREGADO":
                            $badge = "success";
                            break;

                        case "CANCELADO":
                            $badge = "danger";
                            break;

                        default:
                            $badge = "dark";
                    }

                    ?>

                    <tr>

                        <!-- PEDIDO -->

                        <td>

                            <strong>

                                #<?= $pedido["id_ticket_ventas"]; ?>

                            </strong>

                            <br>

                            <small class="text-muted">

                                <?= $pedido["serie"]; ?>
                                -
                                <?= $pedido["numero"]; ?>

                            </small>

                        </td>


                        <!-- CLIENTE -->

                        <td>

                            <strong>

                                <?= htmlspecialchars($pedido["cliente"]); ?>

                            </strong>

                            <br>

                            <small class="text-muted">

                                <?= htmlspecialchars($pedido["email"]); ?>

                            </small>

                        </td>


                        <!-- FECHA -->

                        <td>

                            <?= date(
                                "d/m/Y",
                                strtotime($pedido["fecha_venta"])
                            ); ?>

                            <br>

                            <small class="text-muted">

                                <?= $pedido["hora_venta"]; ?>

                            </small>

                        </td>


                        <!-- MÉTODO DE PAGO -->

                        <td>

                            <?= htmlspecialchars(
                                $pedido["metodo_pago"]
                            ); ?>

                        </td>


                        <!-- TOTAL -->

                        <td>

                            <strong>

                                S/.
                                <?= number_format(
                                    $pedido["total_venta"],
                                    2
                                ); ?>

                            </strong>

                        </td>


                        <!-- ESTADO -->

                        <td>

                            <span class="badge bg-<?= $badge; ?>">

                                <?= $pedido["estado_envio"]; ?>

                            </span>

                        </td>


                        <!-- ACCIONES -->

                        <td class="text-center">

                            <div class="btn-group">

                                <button
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalVerPedido"
                                    data-id="<?= $pedido["id_ticket_ventas"]; ?>">

                                    <i class="bi bi-eye-fill"></i>

                                </button>

                                <button
                                    class="btn btn-sm btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEstadoPedido"
                                    data-id="<?= $pedido["id_ticket_ventas"]; ?>"
                                    data-estado="<?= $pedido["estado_envio"]; ?>">

                                    <i class="bi bi-pencil-fill"></i>

                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="7" class="text-center py-5">

                        <i class="bi bi-bag-x display-5 text-muted"></i>

                        <p class="text-muted mt-3 mb-0">

                            No existen pedidos registrados.

                        </p>

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>