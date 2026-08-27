<?php
//======================================================
// CoDevPro Technology
// ajax/listar_categorias.php
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
$pagina = max(1, intval($_POST["pagina"] ?? 1));
$estado = trim($_POST["estado"] ?? "");
$limite = 5;
$inicio = ($pagina - 1) * $limite;

/*======================================================
=            TOTAL REGISTROS
======================================================*/

$sqlTotal = "
SELECT COUNT(*) total
FROM categorias
WHERE id_user = ?
AND Eliminado = 0
";

$params = [$idUser];
$types  = "i";

if (!empty($buscar)) {

    $sqlTotal .= " AND nombre LIKE ? ";

    $params[] = "%{$buscar}%";
    $types .= "s";
}
if ($estado === "uso") {

    $sqlTotal .= "
    AND EXISTS(
        SELECT 1
        FROM producto p
        WHERE p.id_categorias = categorias.id_categorias
        AND p.Eliminado = 0
    )
    ";
} elseif ($estado === "sin_productos") {

    $sqlTotal .= "
    AND NOT EXISTS(
        SELECT 1
        FROM producto p
        WHERE p.id_categorias = categorias.id_categorias
        AND p.Eliminado = 0
    )
    ";
}
$stmtTotal = mysqli_prepare($conexion, $sqlTotal);

mysqli_stmt_bind_param(
    $stmtTotal,
    $types,
    ...$params
);

mysqli_stmt_execute($stmtTotal);

$totalCategorias =
    mysqli_stmt_get_result($stmtTotal)
        ->fetch_assoc()["total"];

$totalPaginas = ceil($totalCategorias / $limite);
/*======================================================
= KPI CATEGORIAS EN USO
======================================================*/

$sqlUso = "
SELECT COUNT(*) total
FROM categorias c
WHERE c.id_user = ?
AND c.Eliminado = 0
AND EXISTS(
    SELECT 1
    FROM producto p
    WHERE p.id_categorias = c.id_categorias
    AND p.Eliminado = 0
)
";

$stmtUso = mysqli_prepare($conexion, $sqlUso);

mysqli_stmt_bind_param(
    $stmtUso,
    "i",
    $idUser
);

mysqli_stmt_execute($stmtUso);

$categoriasUso =
    mysqli_stmt_get_result($stmtUso)
        ->fetch_assoc()["total"];
/*======================================================
= KPI PRODUCTOS
======================================================*/

$sqlProductos = "
SELECT COUNT(*) total
FROM producto
WHERE id_user = ?
AND Eliminado = 0
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
= KPI CATEGORIA TOP
======================================================*/

$sqlTop = "
SELECT
c.nombre,
COUNT(p.idProducto) total

FROM categorias c

LEFT JOIN producto p
ON p.id_categorias = c.id_categorias
AND p.Eliminado = 0

WHERE c.id_user = ?
AND c.Eliminado = 0

GROUP BY c.id_categorias

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

$resultTop =
    mysqli_stmt_get_result($stmtTop);

$categoriaTop = "-";

if ($rowTop = mysqli_fetch_assoc($resultTop)) {

    $categoriaTop = $rowTop["nombre"];
}
/*======================================================
=            CONSULTA PRINCIPAL
======================================================*/

$sql = "
SELECT
    c.*,

    (
        SELECT COUNT(*)
        FROM producto p
        WHERE p.id_categorias = c.id_categorias
        AND p.Eliminado = 0
    ) AS total_productos,

    (
        SELECT COALESCE(SUM(cpv.cantidad_total),0)
        FROM cantidad_producto_vendido cpv
        INNER JOIN producto p2
            ON p2.idProducto = cpv.idProducto
        WHERE p2.id_categorias = c.id_categorias
    ) AS total_vendidos

FROM categorias c

WHERE c.id_user = ?
AND c.Eliminado = 0
";
if ($estado === "uso") {

    $sql .= "
    AND EXISTS(
        SELECT 1
        FROM producto p3
        WHERE p3.id_categorias = c.id_categorias
        AND p3.Eliminado = 0
    )
    ";
} elseif ($estado === "sin_productos") {

    $sql .= "
    AND NOT EXISTS(
        SELECT 1
        FROM producto p3
        WHERE p3.id_categorias = c.id_categorias
        AND p3.Eliminado = 0
    )
    ";
}
$params = [$idUser];
$types  = "i";

if (!empty($buscar)) {

    $sql .= " AND c.nombre LIKE ? ";

    $params[] = "%{$buscar}%";
    $types .= "s";
}

$sql .= "
ORDER BY c.nombre ASC
LIMIT ?, ?
";

$params[] = $inicio;
$params[] = $limite;

$types .= "ii";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*======================================================
=            TABLA
======================================================*/

ob_start();
?>

<div class="table-responsive">

    <table class="table table-hover align-middle">

        <thead class="table-light">

            <tr>

                <th width="80">Imagen</th>

                <th>Categoría</th>

                <th class="text-center">
                    Productos
                </th>

                <th class="text-center">
                    Vendidos
                </th>

                <th class="text-center">
                    ID
                </th>

                <th width="220" class="text-center">
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

                            <span class="badge bg-success">

                                <?= $row["total_vendidos"] ?>

                            </span>

                        </td>

                        <td class="text-center">

                            #<?= $row["id_categorias"] ?>

                        </td>

                        <td class="text-center">

                            <div class="btn-group">

                                <!-- VER -->

                                <button
                                    class="btn btn-outline-primary btn-sm btn-ver-categoria"
                                    data-id="<?= $row["id_categorias"] ?>">

                                    <i class="bi bi-eye"></i>

                                </button>

                                <!-- EDITAR -->

                                <button
                                    class="btn btn-outline-warning btn-sm btn-editar-categoria"
                                    data-id="<?= $row["id_categorias"] ?>">

                                    <i class="bi bi-pencil-square"></i>

                                </button>

                                <!-- ELIMINAR -->

                                <button
                                    class="btn btn-sm btn-danger btn-eliminar-categoria"
                                    data-id="<?= $row["id_categorias"] ?>">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="6">

                        <div class="text-center py-5">

                            <i class="bi bi-tags fs-1 text-muted"></i>

                            <h5 class="mt-3">

                                No se encontraron categorías

                            </h5>

                        </div>

                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<?php

$tabla = ob_get_clean();

/*======================================================
=            PAGINACION
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
=            RESPUESTA
======================================================*/

echo json_encode([

    "estado" => true,

    "tabla" => $tabla,

    "paginacion" => $paginacion,

    "totalCategorias" => $totalCategorias,

    "categoriasUso" => $categoriasUso,

    "totalProductos" => $totalProductos,

    "categoriaTop" => $categoriaTop,

    "totalRegistros" => $totalCategorias

]);
