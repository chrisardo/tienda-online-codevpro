<?php
//=========================================================
// CoDevPro Technology
// ajax/listar_estadisticas_clientes.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Sesión inválida"
    ]);

    exit;
}

/*=============================================
FILTROS
=============================================*/

$buscar       = trim($_POST["buscar"] ?? "");
$estado       = trim($_POST["estado"] ?? "");
$departamento = trim($_POST["departamento"] ?? "");
$fechaInicio  = trim($_POST["fechaInicio"] ?? "");
$fechaFin     = trim($_POST["fechaFin"] ?? "");

$pagina = max(1, (int)($_POST["pagina"] ?? 1));

$limite = 6;

$offset = ($pagina - 1) * $limite;

/*=============================================
WHERE DINAMICO
=============================================*/

$where = "
    WHERE c.id_user = ?
    AND c.Eliminado = 0
";

$tipos = "i";

$parametros = [$idUser];

if (!empty($buscar)) {

    $where .= "
        AND (
            c.nombre LIKE ?
            OR c.email LIKE ?
            OR c.dni_o_ruc LIKE ?
        )
    ";

    $buscarLike = "%{$buscar}%";

    $tipos .= "sss";

    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
}

if (!empty($estado)) {

    $where .= " AND c.estado = ? ";

    $tipos .= "s";

    $parametros[] = $estado;
}

if (!empty($departamento)) {

    $where .= " AND c.id_departamento = ? ";

    $tipos .= "i";

    $parametros[] = (int)$departamento;
}

if (!empty($fechaInicio)) {

    $where .= " AND c.fecha_registro >= ? ";

    $tipos .= "s";

    $parametros[] = $fechaInicio;
}

if (!empty($fechaFin)) {

    $where .= " AND c.fecha_registro <= ? ";

    $tipos .= "s";

    $parametros[] = $fechaFin;
}

/*=============================================
TOTAL REGISTROS
=============================================*/

$sqlTotal = "
    SELECT COUNT(*) total
    FROM clientes c
    $where
";

$stmt = mysqli_prepare($conexion, $sqlTotal);

mysqli_stmt_bind_param(
    $stmt,
    $tipos,
    ...$parametros
);

mysqli_stmt_execute($stmt);

$resultadoTotal = mysqli_stmt_get_result($stmt);

$totalRegistros = mysqli_fetch_assoc($resultadoTotal)["total"] ?? 0;

$totalPaginas = max(1, ceil($totalRegistros / $limite));

/*=============================================
LISTADO CLIENTES
=============================================*/

$sql = "
SELECT

    c.idCliente,
    c.nombre,
    c.email,
    c.dni_o_ruc,
    c.celular,
    c.estado,
    c.fecha_registro,

    d.nombre AS departamento,

    COUNT(tv.id_ticket_ventas) AS pedidos,

    COALESCE(
        SUM(tv.total_venta),
        0
    ) AS totalComprado,

    MAX(tv.fecha_venta) AS ultimaCompra

FROM clientes c

LEFT JOIN departamento d
    ON d.id_departamento = c.id_departamento

LEFT JOIN ticket_ventas tv
    ON tv.idCliente = c.idCliente
    AND tv.id_user = c.id_user

$where

GROUP BY c.idCliente

ORDER BY c.idCliente DESC

LIMIT ?, ?
";

$stmt = mysqli_prepare($conexion, $sql);

$tiposConsulta = $tipos . "ii";

$parametrosConsulta = $parametros;

$parametrosConsulta[] = $offset;
$parametrosConsulta[] = $limite;

mysqli_stmt_bind_param(
    $stmt,
    $tiposConsulta,
    ...$parametrosConsulta
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*=============================================
TABLA
=============================================*/

$tabla = "";

$contador = $offset + 1;

while ($row = mysqli_fetch_assoc($resultado)) {

    $estadoBadge = $row["estado"] === "ACTIVO"
        ? "success"
        : "danger";

    $ultimaCompra = !empty($row["ultimaCompra"])
        ? date("d/m/Y", strtotime($row["ultimaCompra"]))
        : "-";

    $tabla .= '
    <tr>

        <td>' . $contador++ . '</td>

        <td>

            <div class="fw-semibold">
                ' . htmlspecialchars($row["nombre"]) . '
            </div>

        </td>

        <td>
            ' . htmlspecialchars($row["dni_o_ruc"]) . '
        </td>

        <td>
            ' . htmlspecialchars($row["email"]) . '
        </td>

        <td>
            ' . htmlspecialchars($row["celular"]) . '
        </td>

        <td>
            ' . htmlspecialchars($row["departamento"] ?? "-") . '
        </td>

        <td>
            ' . (int)$row["pedidos"] . '
        </td>

        <td>

            <span class="fw-bold text-success">

                S/ ' . number_format($row["totalComprado"], 2) . '

            </span>

        </td>

        <td>
            ' . $ultimaCompra . '
        </td>

        <td>

            <span class="badge bg-' . $estadoBadge . '">

                ' . htmlspecialchars($row["estado"]) . '

            </span>

        </td>

        <td>

            <button
                class="btn btn-sm btn-primary btnVerCliente"
                data-id="' . $row["idCliente"] . '">

                <i class="bi bi-eye"></i>

            </button>

        </td>

    </tr>';
}

/*=============================================
SIN RESULTADOS
=============================================*/

if ($tabla === "") {

    $tabla = '
    <tr>

        <td colspan="11" class="text-center py-5">

            <div class="text-muted">

                <i class="bi bi-search fs-1 d-block mb-2"></i>

                No se encontraron clientes.

            </div>

        </td>

    </tr>';
}

/*=============================================
PAGINACION
=============================================*/

$paginacion = "";

if ($totalPaginas > 1) {

    $paginacion .= '<nav>';
    $paginacion .= '<ul class="pagination justify-content-center">';

    for ($i = 1; $i <= $totalPaginas; $i++) {

        $active = ($i == $pagina)
            ? "active"
            : "";

        $paginacion .= '

        <li class="page-item ' . $active . '">

            <a href="#"
               class="page-link"
               data-pagina="' . $i . '">

                ' . $i . '

            </a>

        </li>';
    }

    $paginacion .= '</ul>';
    $paginacion .= '</nav>';
}

/*=============================================
RESPUESTA
=============================================*/

echo json_encode([

    "ok" => true,

    "tabla" => $tabla,

    "paginacion" => $paginacion,

    "totalRegistros" => $totalRegistros,

    "totalPaginas" => $totalPaginas

]);
