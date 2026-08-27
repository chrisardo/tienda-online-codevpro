<?php
//Toda esta parte pertenece a ajax/obtener_testimonios.php
session_start();

header('Content-Type: application/json');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false
    ]);
    exit;
}

$pagina = max(1, (int)($_POST["pagina"] ?? 1));

$limite = 6;

$inicio = ($pagina - 1) * $limite;

$buscar = trim($_POST["buscar"] ?? "");
$estado = trim($_POST["estado"] ?? "");
$calificacion = trim($_POST["calificacion"] ?? "");
$fechaInicio = trim($_POST["fechaInicio"] ?? "");
$fechaFin = trim($_POST["fechaFin"] ?? "");

$where = " WHERE t.id_user=? ";

if ($buscar != "") {

    $buscarSQL = mysqli_real_escape_string(
        $conexion,
        $buscar
    );

    $where .= " AND (

        c.nombre LIKE '%$buscarSQL%'
        OR p.nombre LIKE '%$buscarSQL%'
        OR t.comentario LIKE '%$buscarSQL%'

    )";
}

if ($estado != "") {

    $estadoSQL = mysqli_real_escape_string(
        $conexion,
        $estado
    );

    $where .= " AND t.estado='$estadoSQL'";
}

if ($calificacion != "") {

    $where .= " AND t.calificacion=" . (int)$calificacion;
}

if (!empty($fechaInicio) && !empty($fechaFin)) {

    $where .= "
        AND DATE(t.fecha)
        BETWEEN '{$fechaInicio}'
        AND '{$fechaFin}'
    ";
} elseif (!empty($fechaInicio)) {

    $where .= "
        AND DATE(t.fecha) >= '{$fechaInicio}'
    ";
} elseif (!empty($fechaFin)) {

    $where .= "
        AND DATE(t.fecha) <= '{$fechaFin}'
    ";
}

$sqlTotal = "

SELECT COUNT(*) total

FROM testimonios t

INNER JOIN clientes c
ON c.idCliente=t.idCliente

INNER JOIN producto p
ON p.idProducto=t.idProducto

$where

";

$stmt = mysqli_prepare(
    $conexion,
    $sqlTotal
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$total = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"];

$sql = "

SELECT

t.*,
c.nombre cliente,
p.nombre producto

FROM testimonios t

INNER JOIN clientes c
ON c.idCliente=t.idCliente

INNER JOIN producto p
ON p.idProducto=t.idProducto

$where

ORDER BY t.id_testimonio DESC

LIMIT $inicio,$limite

";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

$tabla = "";
$iconoRespuesta =
    !empty($row["respuesta"])
    ? "bi-pencil-square"
    : "bi-reply-fill";
while ($row = mysqli_fetch_assoc($res)) {

    $estadoBadge = "secondary";

    if ($row["estado"] == "APROBADO") {
        $estadoBadge = "success";
    }

    if ($row["estado"] == "PENDIENTE") {
        $estadoBadge = "warning";
    }

    if ($row["estado"] == "RECHAZADO") {
        $estadoBadge = "danger";
    }

    $estrellas = str_repeat(
        "⭐",
        (int)$row["calificacion"]
    );

    $tabla .= '

    <tr>

        <td>' . $row["id_testimonio"] . '</td>

        <td>

            <div class="d-flex align-items-center">

                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">

                    ' . strtoupper(substr($row["cliente"], 0, 1)) . '

                </div>

                <div class="ms-2">

                    ' . $row["cliente"] . '

                </div>

            </div>

        </td>

        <td>' . $row["producto"] . '</td>

        <td>' . $estrellas . '</td>

        <td style="max-width:300px">

            ' . mb_strimwidth(
        $row["comentario"],
        0,
        80,
        "..."
    ) . '

        </td>

        <td>

            <span class="badge bg-' . $estadoBadge . '">

                ' . $row["estado"] . '

            </span>

        </td>

        <td>' . $row["fecha"] . '</td>

        <td>

            <button class="btn btn-sm btn-primary btnVerTestimonio"
                data-id="' . $row["id_testimonio"] . '">

                <i class="bi bi-eye"></i>

            </button>
        </td>

    </tr>

    ';
}
$totalPaginas = ceil($total / $limite);

$paginacion = '';

if ($totalPaginas > 1) {

    $paginacion .= '<nav><ul class="pagination justify-content-center">';

    for ($i = 1; $i <= $totalPaginas; $i++) {

        $active = ($i == $pagina) ? 'active' : '';

        $paginacion .= '
            <li class="page-item ' . $active . '">
                <button
                    class="page-link btnPaginaTestimonio"
                    data-pagina="' . $i . '">
                    ' . $i . '
                </button>
            </li>
        ';
    }

    $paginacion .= '</ul></nav>';
}
echo json_encode([

    "ok" => true,

    "tabla"      => $tabla,

    "total"      => $total,

    "paginacion" => $paginacion

]);
