<?php
session_start();

if (!isset($_SESSION["usId"])) {

    exit("Acceso denegado.");
}

require_once "../controladores/conexion.php";

$idUser = $_SESSION["usId"];

$buscar = trim($_GET["buscar"] ?? "");

$estado = trim($_GET["estado"] ?? "");

$fecha = trim($_GET["fecha"] ?? "");
$sql = "SELECT

            tv.id_ticket_ventas,

            tv.serie,

            tv.numero,

            tv.total_venta,

            tv.fecha_venta,

            tv.estado_envio,

            c.nombre AS cliente,

            mp.nombre AS metodo_pago

        FROM ticket_ventas tv

        INNER JOIN clientes c

            ON c.idCliente = tv.idCliente

        LEFT JOIN metodo_pago mp

            ON mp.id_metodo_pago = tv.id_metodo_pago

        WHERE

            tv.id_user = ?";
$tipos = "i";

$parametros = [];

$parametros[] = &$idUser;
if ($buscar != "") {

    $sql .= "

    AND(

        c.nombre LIKE ?

        OR

        tv.serie LIKE ?

        OR

        tv.numero LIKE ?

    )";

    $buscarSQL = "%" . $buscar . "%";

    $tipos .= "sss";

    $parametros[] = &$buscarSQL;
    $parametros[] = &$buscarSQL;
    $parametros[] = &$buscarSQL;
}
if ($estado != "") {

    $sql .= "

    AND

    tv.estado_envio=?";

    $tipos .= "s";

    $parametros[] = &$estado;
}
if ($fecha != "") {

    $sql .= "

    AND

    tv.fecha_venta=?";

    $tipos .= "s";

    $parametros[] = &$fecha;
}
$sql .= "

ORDER BY

tv.id_ticket_ventas DESC";
$stmt = mysqli_prepare(

    $conexion,

    $sql

);

array_unshift(

    $parametros,

    $tipos

);

call_user_func_array(

    "mysqli_stmt_bind_param",

    array_merge(

        [$stmt],

        $parametros

    )

);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($resultado) == 0) {

?>
    <tr>

        <td colspan="7" class="text-center py-5">

            No existen pedidos.

        </td>

    </tr>

<?php

    exit();
}
while ($pedido = mysqli_fetch_assoc($resultado)) {

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
    $badge = "secondary";

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

            $badge = "dark";

            break;

        case "ENTREGADO":

            $badge = "success";

            break;

        case "CANCELADO":

            $badge = "danger";

            break;
    }
?>

    <tr>

        <td>

            <?= $comprobante ?>

        </td>

        <td>

            <?= htmlspecialchars($pedido["cliente"]) ?>

        </td>

        <td>

            S/

            <?= number_format(

                $pedido["total_venta"],

                2

            ) ?>

        </td>

        <td>

            <?= htmlspecialchars($pedido["metodo_pago"]) ?>

        </td>

        <td>

            <span class="badge bg-<?= $badge ?>">

                <?= htmlspecialchars($pedido["estado_envio"]) ?>

            </span>

        </td>

        <td>

            <?= date(

                "d/m/Y",

                strtotime($pedido["fecha_venta"])

            ) ?>

        </td>

        <td>
            <button

                class="btn btn-primary btn-sm btnDetalle"

                data-id="<?= $pedido["id_ticket_ventas"] ?>">

                <i class="fas fa-eye"></i>

            </button>

            <button

                class="btn btn-warning btn-sm btnEstado"

                data-id="<?= $pedido["id_ticket_ventas"] ?>">

                <i class="fas fa-edit"></i>

            </button>

            <a

                class="btn btn-danger btn-sm"

                target="_blank"

                href="../generar_pdf_pedido.php?id=<?= $pedido["id_ticket_ventas"] ?>">

                <i class="fas fa-file-pdf"></i>

            </a>

        </td>

    </tr>

<?php

}
