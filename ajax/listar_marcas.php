<?php
//======================================================
// CoDevPro Technology
// ajax/listar_marcas.php
//======================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);

$buscar = trim($_POST["buscar"] ?? "");
$filtro = trim($_POST["filtro"] ?? "");
$ordenar = trim(
    $_POST["ordenar"] ?? "nombre_asc"
);
$pagina = max(1, intval($_POST["pagina"] ?? 1));

$limite = 5;
$inicio = ($pagina - 1) * $limite;

/*======================================================
= FILTROS
======================================================*/

$where = "
WHERE m.id_user = ?
AND m.Eliminado = 0
";

$params = [$idUser];
$types  = "i";

if (!empty($buscar)) {

    $where .= " AND m.nombre LIKE ? ";

    $params[] = "%{$buscar}%";
    $types .= "s";
}

if ($filtro === "uso") {

    $where .= "
    AND (
        SELECT COUNT(*)
        FROM producto p
        WHERE p.id_marca = m.id_marca
        AND p.Eliminado = 0
    ) > 0
    ";
}

if ($filtro === "sin_productos") {

    $where .= "
    AND (
        SELECT COUNT(*)
        FROM producto p
        WHERE p.id_marca = m.id_marca
        AND p.Eliminado = 0
    ) = 0
    ";
}

/*======================================================
= TOTAL REGISTROS
======================================================*/

$sqlTotal = "
SELECT COUNT(*) total
FROM marcas m
{$where}
";

$stmtTotal = mysqli_prepare($conexion, $sqlTotal);

mysqli_stmt_bind_param(
    $stmtTotal,
    $types,
    ...$params
);

mysqli_stmt_execute($stmtTotal);

$totalRegistros =
    mysqli_stmt_get_result($stmtTotal)
        ->fetch_assoc()["total"];

$totalPaginas = ceil($totalRegistros / $limite);

/*======================================================
= KPIs
======================================================*/

$sqlKpi = "

SELECT

COUNT(*) total_marcas,

SUM(
    CASE
        WHEN (
            SELECT COUNT(*)
            FROM producto p
            WHERE p.id_marca = m.id_marca
            AND p.Eliminado = 0
        ) > 0
        THEN 1
        ELSE 0
    END
) marcas_uso

FROM marcas m

WHERE m.id_user = ?
AND m.Eliminado = 0

";

$stmtKpi = mysqli_prepare(
    $conexion,
    $sqlKpi
);

mysqli_stmt_bind_param(
    $stmtKpi,
    "i",
    $idUser
);

mysqli_stmt_execute($stmtKpi);

$kpi = mysqli_stmt_get_result($stmtKpi)
    ->fetch_assoc();

$totalMarcas = $kpi["total_marcas"] ?? 0;
$marcasUso   = $kpi["marcas_uso"] ?? 0;

/*======================================================
= PRODUCTOS ASOCIADOS
======================================================*/

$sqlProductos = "

SELECT COUNT(*) total

FROM producto

WHERE id_user = ?
AND Eliminado = 0
AND id_marca IS NOT NULL

";

$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);

mysqli_stmt_bind_param(
    $stmtProductos,
    "i",
    $idUser
);

mysqli_stmt_execute($stmtProductos);

$totalProductos =
    mysqli_stmt_get_result($stmtProductos)
        ->fetch_assoc()["total"];

/*======================================================
= MARCA TOP
======================================================*/

$sqlTop = "

SELECT
m.nombre,
COUNT(p.idProducto) total

FROM marcas m

LEFT JOIN producto p
ON p.id_marca = m.id_marca
AND p.Eliminado = 0

WHERE m.id_user = ?
AND m.Eliminado = 0

GROUP BY m.id_marca

ORDER BY total DESC

LIMIT 1

";

$stmtTop = mysqli_prepare(
    $conexion,
    $sqlTop
);

mysqli_stmt_bind_param(
    $stmtTop,
    "i",
    $idUser
);

mysqli_stmt_execute($stmtTop);

$marcaTop =
    mysqli_stmt_get_result($stmtTop)
    ->fetch_assoc();

$nombreMarcaTop =
    $marcaTop["nombre"] ?? "-";
$orderBy = "m.nombre ASC";

switch ($ordenar) {

    case "nombre_desc":

        $orderBy = "m.nombre DESC";
        break;

    case "productos_desc":

        $orderBy = "
(
    SELECT COUNT(*)
    FROM producto p
    WHERE p.id_marca = m.id_marca
    AND p.Eliminado = 0
) DESC";

        break;

    case "productos_asc":

        $orderBy = "
(
    SELECT COUNT(*)
    FROM producto p
    WHERE p.id_marca = m.id_marca
    AND p.Eliminado = 0
) ASC";

        break;

    case "id_desc":

        $orderBy = "m.id_marca DESC";
        break;

    case "id_asc":

        $orderBy = "m.id_marca ASC";
        break;
}
/*======================================================
= CONSULTA PRINCIPAL
======================================================*/

$sql = "

SELECT

m.*,

(
    SELECT COUNT(*)
    FROM producto p
    WHERE p.id_marca = m.id_marca
    AND p.Eliminado = 0
) total_productos

FROM marcas m

{$where}

ORDER BY {$orderBy}

LIMIT $inicio, $limite

";

$paramsConsulta = $params;
$typesConsulta  = $types;



$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    $typesConsulta,
    ...$paramsConsulta
);

mysqli_stmt_execute($stmt);

$resultado =
    mysqli_stmt_get_result($stmt);

/*======================================================
= TABLA
======================================================*/

ob_start();
?>

<div class="table-responsive">

    <table class="table table-hover align-middle">

        <thead class="table-light">

            <tr>

                <th width="90">Imagen</th>

                <th>Marca</th>

                <th class="text-center">
                    Productos
                </th>

                <th class="text-center">
                    Estado
                </th>

                <th class="text-center">
                    ID
                </th>

                <th width="180" class="text-center">
                    Acciones
                </th>

            </tr>

        </thead>

        <tbody>

            <?php if ($resultado->num_rows > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($resultado)): ?>

                    <?php

                    $imagen = "assets/img/sin_imagen.png";

                    if (!empty($row["imagen"])) {

                        $imagen =
                            "data:image/jpeg;base64," .
                            base64_encode($row["imagen"]);
                    }

                    ?>

                    <tr>

                        <td>

                            <img
                                src="<?= $imagen ?>"
                                class="rounded border"
                                style="
                                    width:60px;
                                    height:60px;
                                    object-fit:cover;
                                ">

                        </td>

                        <td>

                            <div class="fw-semibold">

                                <?= htmlspecialchars($row["nombre"]) ?>

                            </div>

                        </td>

                        <td class="text-center">

                            <span class="badge bg-primary">

                                <?= $row["total_productos"] ?>

                            </span>

                        </td>

                        <td class="text-center">

                            <?php if ($row["total_productos"] > 0): ?>

                                <span class="badge bg-success">
                                    En uso
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    Sin productos
                                </span>

                            <?php endif; ?>

                        </td>

                        <td class="text-center">

                            #<?= $row["id_marca"] ?>

                        </td>

                        <td class="text-center">

                            <div class="btn-group">

                                <button
                                    class="btn btn-outline-primary btn-sm btn-ver-marca"
                                    data-id="<?= $row["id_marca"] ?>">

                                    <i class="bi bi-eye"></i>

                                </button>

                                <button
                                    class="btn btn-outline-warning btn-sm btn-editar-marca"
                                    data-id="<?= $row["id_marca"] ?>">

                                    <i class="bi bi-pencil"></i>

                                </button>

                                <button
                                    class="btn btn-outline-danger btn-sm btn-eliminar-marca"
                                    data-id="<?= $row["id_marca"] ?>">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="6" class="text-center py-5">

                        <i class="bi bi-bookmark-star fs-1 text-muted"></i>

                        <h5 class="mt-3">

                            No se encontraron marcas

                        </h5>

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<?php
$tabla = ob_get_clean();

/*======================================================
= PAGINACION
======================================================*/

ob_start();

if ($totalPaginas > 1):
?>

    <nav>

        <ul class="pagination justify-content-center">

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>

                <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">

                    <a
                        href="#"
                        class="page-link btn-pagina"
                        data-pagina="<?= $i ?>">

                        <?= $i ?>

                    </a>

                </li>

            <?php endfor; ?>

        </ul>

    </nav>

<?php
endif;

$paginacion = ob_get_clean();

/*======================================================
= RESPUESTA
======================================================*/

echo json_encode([

    "estado" => true,

    "tabla" => $tabla,

    "paginacion" => $paginacion,

    "totalRegistros" => $totalRegistros,

    "totalMarcas" => $totalMarcas,

    "marcasUso" => $marcasUso,

    "totalProductos" => $totalProductos,

    "marcaTop" => $nombreMarcaTop

]);
