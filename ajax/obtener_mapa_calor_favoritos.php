<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false
    ]);

    exit;
}

$sql = "

SELECT

    p.idProducto,
    p.nombre,

    COUNT(DISTINCT f.id_favorito) favoritos,

    COALESCE(SUM(d.cantidad_pedido_producto),0) ventas

FROM producto p

LEFT JOIN favoritos f
    ON f.idProducto = p.idProducto

LEFT JOIN detalle_ticket_ventas d
    ON d.idProducto = p.idProducto

WHERE p.id_user = ?

AND p.Eliminado = 0

GROUP BY p.idProducto

ORDER BY favoritos DESC

LIMIT 30

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$html = "";

while ($fila = mysqli_fetch_assoc($resultado)) {

    $favoritos = (int)$fila["favoritos"];

    $ventas = (int)$fila["ventas"];

    $conversion = 0;

    if ($favoritos > 0) {

        $conversion =
            round(($ventas / $favoritos) * 100, 1);
    }

    /*=====================================
    ESTADO COMERCIAL
    =====================================*/

    if ($favoritos >= 10 && $ventas <= 2) {

        $estado = '
        <span class="badge bg-warning">
            Alta intención
        </span>';
    } elseif ($favoritos >= 10 && $ventas >= 5) {

        $estado = '
        <span class="badge bg-success">
            Producto Estrella
        </span>';
    } elseif ($favoritos <= 3 && $ventas <= 1) {

        $estado = '
        <span class="badge bg-secondary">
            Poco interés
        </span>';
    } else {

        $estado = '
        <span class="badge bg-primary">
            Normal
        </span>';
    }

    $html .= '

    <tr>

        <td>

            <strong>

                ' . htmlspecialchars(
        $fila["nombre"]
    ) . '

            </strong>

        </td>

        <td>

            <span class="badge bg-danger">

                ' . $favoritos . '

            </span>

        </td>

        <td>

            <span class="badge bg-success">

                ' . $ventas . '

            </span>

        </td>

        <td>

            ' . $conversion . '%

        </td>

        <td>

            ' . $estado . '

        </td>

    </tr>

    ';
}

echo json_encode([
    "ok" => true,
    "tabla" => $html
]);
