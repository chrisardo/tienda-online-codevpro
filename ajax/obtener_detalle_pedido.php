<?php
session_start();

if (!isset($_SESSION["usId"])) {
    exit("Acceso denegado.");
}

require_once "../controladores/conexion.php";

$idUser = $_SESSION["usId"];

$idPedido = intval($_GET["id"] ?? 0);

if ($idPedido <= 0) {
    exit("Pedido inválido.");
}
$sql = "SELECT

            tv.*,

            c.nombre,
            c.dni_o_ruc,
            c.celular,
            c.email,
            c.direccion,
            c.provincia,
            c.distrito,

            mp.nombre AS metodo_pago

        FROM ticket_ventas tv

        INNER JOIN clientes c
            ON c.idCliente = tv.idCliente

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago = tv.id_metodo_pago

        WHERE

            tv.id_ticket_ventas=?

        AND

            tv.id_user=?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idPedido,
    $idUser
);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) == 0) {

    exit("Pedido no encontrado.");
}

$pedido = mysqli_fetch_assoc($res);
$sqlProductos = "SELECT

                    p.nombre,

                    d.cantidad_pedido_producto,

                    d.sub_total,

                    p.codigo

                FROM detalle_ticket_ventas d

                INNER JOIN producto p

                    ON p.idProducto=d.idProducto

                WHERE

                    d.id_ticket_ventas=?";

$stmt = mysqli_prepare(

    $conexion,

    $sqlProductos

);

mysqli_stmt_bind_param(

    $stmt,

    "i",

    $idPedido

);

mysqli_stmt_execute($stmt);

$productos = mysqli_stmt_get_result($stmt);
?>

<div class="modal-header">

    <h5 class="modal-title">

        Pedido

        <?= htmlspecialchars($pedido["serie"]) ?>

        -

        <?= str_pad(

            $pedido["numero"],

            8,

            "0",

            STR_PAD_LEFT

        ) ?>

    </h5>

    <button

        type="button"

        class="btn-close"

        data-bs-dismiss="modal">

    </button>

</div>
<div class="modal-body">

    <div class="row">

        <div class="col-md-6">

            <h6>

                Cliente

            </h6>

            <p>

                <strong>

                    <?= htmlspecialchars($pedido["nombre"]) ?>

                </strong>

                <br>

                <?= htmlspecialchars($pedido["dni_o_ruc"]) ?>

                <br>

                <?= htmlspecialchars($pedido["celular"]) ?>

                <br>

                <?= htmlspecialchars($pedido["email"]) ?>

            </p>

        </div>

        <div class="col-md-6">

            <h6>

                Entrega

            </h6>

            <p>

                <?= htmlspecialchars($pedido["direccion"]) ?>

                <br>

                <?= htmlspecialchars($pedido["distrito"]) ?>

                <br>

                <?= htmlspecialchars($pedido["provincia"]) ?>

            </p>

        </div>

    </div>
    <table class="table table-bordered">

        <thead class="table-light">

            <tr>

                <th>Código</th>

                <th>Producto</th>

                <th>Cantidad</th>

                <th class="text-end">

                    Subtotal

                </th>

            </tr>

        </thead>

        <tbody>

            <?php

            while ($item = mysqli_fetch_assoc($productos)) {

            ?>

                <tr>

                    <td>

                        <?= htmlspecialchars($item["codigo"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($item["nombre"]) ?>

                    </td>

                    <td>

                        <?= $item["cantidad_pedido_producto"] ?>

                    </td>

                    <td class="text-end">

                        S/

                        <?= number_format(

                            $item["sub_total"],

                            2

                        ) ?>

                    </td>

                </tr>

            <?php

            }

            ?>

        </tbody>

    </table>
    <div class="row">

        <div class="col-md-6">

            <strong>

                Método de pago

            </strong>

            <br>

            <?= htmlspecialchars($pedido["metodo_pago"]) ?>

        </div>

        <div class="col-md-6 text-end">

            <h4>

                S/

                <?= number_format(

                    $pedido["total_venta"],

                    2

                ) ?>

            </h4>

        </div>

    </div>
    <hr>

    <div class="mb-3">

        <label class="form-label">

            Estado del pedido

        </label>

        <select

            class="form-select"

            id="nuevoEstado"

            data-id="<?= $pedido["id_ticket_ventas"] ?>">

            <?php

            $estados = [

                "PENDIENTE",

                "CONFIRMADO",

                "PREPARANDO",

                "ENVIADO",

                "ENTREGADO",

                "CANCELADO"

            ];

            foreach ($estados as $estado) {

            ?>

                <option

                    value="<?= $estado ?>"

                    <?= $pedido["estado_envio"] == $estado ? "selected" : "" ?>>

                    <?= $estado ?>

                </option>

            <?php

            }

            ?>

        </select>

    </div>
    <div class="mb-3">

        <label class="form-label">

            Observaciones

        </label>

        <textarea

            class="form-control"

            rows="3"

            id="observacionPedido"><?= htmlspecialchars($pedido["observacion_envio"]) ?></textarea>

    </div>
</div>

<div class="modal-footer">

    <button

        class="btn btn-success"

        id="btnGuardarEstado"

        data-id="<?= $pedido["id_ticket_ventas"] ?>">

        <i class="fas fa-save"></i>

        Guardar cambios

    </button>

    <button

        class="btn btn-secondary"

        data-bs-dismiss="modal">

        Cerrar

    </button>

</div>
<hr>

<h5 class="mb-4">

    <i class="fas fa-route text-primary"></i>

    Seguimiento del pedido

</h5>

<div class="timeline-admin">
    <div class="timeline-item active">

        <div class="timeline-icon bg-primary">

            <i class="fas fa-shopping-cart"></i>

        </div>

        <div class="timeline-content">

            <h6>

                Pedido recibido

            </h6>

            <small>

                <?= date(

                    "d/m/Y H:i",

                    strtotime(

                        $pedido["fecha_venta"] . " " . $pedido["hora_venta"]

                    )

                ) ?>

            </small>

        </div>

    </div>
    <div class="timeline-item <?= !empty($pedido["fecha_confirmado"]) ? "active" : "" ?>">

        <div class="timeline-icon bg-info">

            <i class="fas fa-check"></i>

        </div>

        <div class="timeline-content">

            <h6>

                Pedido confirmado

            </h6>

            <?php if (!empty($pedido["fecha_confirmado"])): ?>

                <small>

                    <?= date(

                        "d/m/Y H:i",

                        strtotime($pedido["fecha_confirmado"])

                    ) ?>

                </small>

            <?php endif; ?>

        </div>

    </div>
    <div class="timeline-item <?= !empty($pedido["fecha_enviado"]) ? "active" : "" ?>">

        <div class="timeline-icon bg-dark">

            <i class="fas fa-truck"></i>

        </div>

        <div class="timeline-content">

            <h6>

                Pedido enviado

            </h6>

            <?php if (!empty($pedido["fecha_enviado"])): ?>

                <small>

                    <?= date(

                        "d/m/Y H:i",

                        strtotime($pedido["fecha_enviado"])

                    ) ?>

                </small>

            <?php endif; ?>

        </div>

    </div>
    <div class="timeline-item <?= !empty($pedido["fecha_entregado"]) ? "active" : "" ?>">

        <div class="timeline-icon bg-success">

            <i class="fas fa-home"></i>

        </div>

        <div class="timeline-content">

            <h6>

                Pedido entregado

            </h6>

            <?php if (!empty($pedido["fecha_entregado"])): ?>

                <small>

                    <?= date(

                        "d/m/Y H:i",

                        strtotime($pedido["fecha_entregado"])

                    ) ?>

                </small>

            <?php endif; ?>

        </div>

    </div>
    <?php if ($pedido["estado_envio"] == "CANCELADO"): ?>

        <div class="timeline-item active">

            <div class="timeline-icon bg-danger">

                <i class="fas fa-times"></i>

            </div>

            <div class="timeline-content">

                <h6>

                    Pedido cancelado

                </h6>

                <small>

                    <?= date("d/m/Y H:i") ?>

                </small>

            </div>

        </div>

    <?php endif; ?>

</div>