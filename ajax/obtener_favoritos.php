<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_favoritos.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

/*=========================================================
=            VALIDAR SESION
=========================================================*/

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}

/*=========================================================
=            PARAMETROS
=========================================================*/

$pagina = isset($_POST["pagina"]) ? (int)$_POST["pagina"] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$limite = 15;
$offset = ($pagina - 1) * $limite;

$buscar      = trim($_POST["buscar"] ?? "");
$categoria   = (int)($_POST["categoria"] ?? 0);
$fechaInicio = trim($_POST["fechaInicio"] ?? "");
$fechaFin    = trim($_POST["fechaFin"] ?? "");

/*=========================================================
=            FILTROS
=========================================================*/

$where = [];
$where[] = "p.id_user = " . (int)$idUser;

if (!empty($buscar)) {

    $buscar = mysqli_real_escape_string(
        $conexion,
        $buscar
    );

    $where[] = "(
        c.nombre LIKE '%$buscar%'
        OR p.nombre LIKE '%$buscar%'
        OR p.codigo LIKE '%$buscar%'
    )";
}

if ($categoria > 0) {

    $where[] = "p.id_categorias = $categoria";
}

if (!empty($fechaInicio)) {

    $where[] = "DATE(f.fecha) >= '$fechaInicio'";
}

if (!empty($fechaFin)) {

    $where[] = "DATE(f.fecha) <= '$fechaFin'";
}

$whereSQL = implode(" AND ", $where);

/*=========================================================
=            TOTAL REGISTROS
=========================================================*/

$sqlTotal = "

SELECT COUNT(*) AS total

FROM favoritos f

INNER JOIN clientes c
    ON c.idCliente = f.idCliente

INNER JOIN producto p
    ON p.idProducto = f.idProducto

LEFT JOIN categorias cat
    ON cat.id_categorias = p.id_categorias

LEFT JOIN marcas m
    ON m.id_marca = p.id_marca

WHERE $whereSQL

";

$resTotal = mysqli_query(
    $conexion,
    $sqlTotal
);

$filaTotal = mysqli_fetch_assoc(
    $resTotal
);

$totalRegistros = (int)$filaTotal["total"];

$totalPaginas = ceil(
    $totalRegistros / $limite
);

/*=========================================================
=            CONSULTA PRINCIPAL
=========================================================*/

$sql = "

SELECT

    f.id_favorito,
    f.fecha,

    c.idCliente,
    c.nombre AS cliente,

    p.idProducto,
    p.codigo,
    p.nombre AS producto,
    p.precio,
    p.stock,

    cat.nombre AS categoria,
    m.nombre AS marca

FROM favoritos f

INNER JOIN clientes c
    ON c.idCliente = f.idCliente

INNER JOIN producto p
    ON p.idProducto = f.idProducto

LEFT JOIN categorias cat
    ON cat.id_categorias = p.id_categorias

LEFT JOIN marcas m
    ON m.id_marca = p.id_marca

WHERE $whereSQL

ORDER BY f.fecha DESC

LIMIT $offset, $limite

";

$resultado = mysqli_query(
    $conexion,
    $sql
);

/*=========================================================
=            TABLA
=========================================================*/

$html = "";

if ($resultado && mysqli_num_rows($resultado) > 0) {

    $contador = $offset + 1;

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $stockBadge = $fila["stock"] > 0
            ? '<span class="badge bg-success">Disponible</span>'
            : '<span class="badge bg-danger">Agotado</span>';

        $html .= '

        <tr>

            <td>' . $contador++ . '</td>

            <td>

                <strong>' .
            htmlspecialchars($fila["cliente"])
            . '</strong>

            </td>

            <td>

                <div class="fw-semibold">' .
            htmlspecialchars($fila["producto"])
            . '</div>

                <small class="text-muted">' .
            htmlspecialchars($fila["codigo"])
            . '</small>

            </td>

            <td>' .
            htmlspecialchars(
                $fila["categoria"] ?? "Sin categoría"
            )
            . '</td>

            <td>' .
            htmlspecialchars(
                $fila["marca"] ?? "Sin marca"
            )
            . '</td>

            <td>

                S/ ' . number_format(
                $fila["precio"],
                2
            ) . '

            </td>

            <td>

                ' . $stockBadge . '

                <br>

                <small class="text-muted">' .
            (int)$fila["stock"] .
            ' unidades</small>

            </td>

            <td>' .

            date(
                "d/m/Y H:i",
                strtotime($fila["fecha"])
            )

            . '</td>

        </tr>';
    }
} else {

    $html = '

    <tr>

        <td colspan="9" class="text-center py-5">

            <i class="bi bi-heart text-danger fs-1"></i>

            <h5 class="mt-3">

                No existen favoritos registrados

            </h5>

        </td>

    </tr>';
}

/*=========================================================
=            PAGINACION
=========================================================*/

$paginacion = '';

if ($totalPaginas > 1) {

    $paginacion .= '
    <nav>
        <ul class="pagination justify-content-center">';

    for ($i = 1; $i <= $totalPaginas; $i++) {

        $active = ($i == $pagina)
            ? 'active'
            : '';

        $paginacion .= '

        <li class="page-item ' . $active . '">

            <button
                class="page-link btnPaginaFavoritos"
                data-pagina="' . $i . '">

                ' . $i . '

            </button>

        </li>';
    }

    $paginacion .= '
        </ul>
    </nav>';
}

/*=========================================================
=            RESPUESTA
=========================================================*/

echo json_encode([

    "ok" => true,

    "tabla" => $html,

    "paginacion" => $paginacion,

    "totalRegistros" => $totalRegistros,

    "totalPaginas" => $totalPaginas

], JSON_UNESCAPED_UNICODE);
