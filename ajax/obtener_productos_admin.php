<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_productos_admin.php
//======================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

/*=============================================
VALIDAR SESIÓN
=============================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);

/*=============================================
RECIBIR FILTROS
=============================================*/

$buscar = trim($_POST["buscar"] ?? "");
$tipo = trim($_POST["tipo"] ?? "");
$categoria = intval($_POST["categoria"] ?? 0);
$marca = intval($_POST["marca"] ?? 0);
$proveedor = intval($_POST["proveedor"] ?? 0);

$pagina = max(1, intval($_POST["pagina"] ?? 1));

$limite = 5;
$inicio = ($pagina - 1) * $limite;

/*=============================================
WHERE DINÁMICO
=============================================*/

$where = [];

$where[] = "p.id_user = '$idUser'";
$where[] = "p.Eliminado = 0";

if (!empty($buscar)) {

    $buscar = mysqli_real_escape_string(
        $conexion,
        $buscar
    );

    $where[] = "(
        p.nombre LIKE '%$buscar%'
        OR p.codigo LIKE '%$buscar%'
        OR p.descripcion LIKE '%$buscar%'
    )";
}

if (!empty($tipo)) {

    $tipo = mysqli_real_escape_string(
        $conexion,
        $tipo
    );

    $where[] = "p.tipo = '$tipo'";
}

if ($categoria > 0) {

    $where[] = "p.id_categorias = '$categoria'";
}

if ($marca > 0) {

    $where[] = "p.id_marca = '$marca'";
}

if ($proveedor > 0) {

    $where[] = "p.id_provedor = '$proveedor'";
}

$whereSQL = implode(" AND ", $where);

/*=============================================
TOTAL REGISTROS
=============================================*/

$sqlTotal = "
SELECT COUNT(*) total
FROM producto p
WHERE $whereSQL
";

$totalRegistros = mysqli_fetch_assoc(
    mysqli_query($conexion, $sqlTotal)
)["total"];

$totalPaginas = ceil(
    $totalRegistros / $limite
);

/*=============================================
CONSULTA PRINCIPAL
=============================================*/

$sql = "
SELECT

p.*,

c.nombre AS categoria,

m.nombre AS marca,

pr.nombre AS proveedor,

(
    SELECT id_imagen
    FROM imagenes
    WHERE idProducto = p.idProducto
    ORDER BY orden ASC
    LIMIT 1
) AS imagen_principal

FROM producto p

LEFT JOIN categorias c
ON c.id_categorias = p.id_categorias

LEFT JOIN marcas m
ON m.id_marca = p.id_marca

LEFT JOIN provedores pr
ON pr.id_provedor = p.id_provedor

WHERE $whereSQL

ORDER BY p.idProducto DESC

LIMIT $inicio,$limite
";

$resultado = mysqli_query(
    $conexion,
    $sql
);

/*=============================================
TABLA
=============================================*/

$tabla = "";

if (mysqli_num_rows($resultado) > 0) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        /*=========================
        IMAGEN
        =========================*/

        $imagen = '
        <img
            src="mostrar_imagen.php?id=' . $fila["idProducto"] . '"
            class="rounded border"
            style="
                width:60px;
                height:60px;
                object-fit:cover;
            ">
        ';

        /*=========================
        STOCK
        =========================*/

        if ($fila["stock"] <= 0) {

            $badgeStock = '
            <span class="badge bg-danger">
                Sin stock
            </span>';
        } elseif ($fila["stock"] <= 5) {

            $badgeStock = '
            <span class="badge bg-warning text-dark">
                Stock bajo
            </span>';
        } else {

            $badgeStock = '
            <span class="badge bg-success">
                Disponible
            </span>';
        }

        /*=========================
        ESTADO
        =========================*/

        $estadoPublicacion =
            $fila["estado_publicacion"] ?? "PUBLICADO";

        switch ($estadoPublicacion) {

            case "BORRADOR":

                $badgeEstado =
                    '<span class="badge bg-secondary">
                        Borrador
                    </span>';

                break;

            case "OCULTO":

                $badgeEstado =
                    '<span class="badge bg-dark">
                        Oculto
                    </span>';

                break;

            default:

                $badgeEstado =
                    '<span class="badge bg-success">
                        Publicado
                    </span>';
        }

        /*=========================
        FILA
        =========================*/

        $tabla .= '

        <tr>

            <td>
                <input
                    type="checkbox"
                    class="form-check-input check-producto"
                    value="' . $fila["idProducto"] . '">
            </td>

            <td>
                ' . $imagen . '
            </td>

            <td>
                <span class="text-muted">
                    ' . htmlspecialchars($fila["codigo"]) . '
                </span>
            </td>

            <td>

                <div class="fw-semibold">

                    ' . htmlspecialchars($fila["nombre"]) . '

                </div>

                <small class="text-muted">

                    ' . ucfirst($fila["tipo"]) . '

                </small>

            </td>

            <td>
                ' . htmlspecialchars($fila["categoria"] ?? '-') . '
            </td>

            <td>

                S/ ' . number_format(
            $fila["precio"],
            2
        ) . '

            </td>

            <td>

                <strong>
                    ' . intval($fila["stock"]) . '
                </strong>

                <br>

                ' . $badgeStock . '

            </td>

            <td>

                ' . intval($fila["nuevo"]) . '

            </td>

            <td>

                ' . $badgeEstado . '

            </td>

            <td>

                <div class="btn-group">
                    <a
                        href="adm_detalle_producto.php?id=' . $fila["idProducto"] . '"
                        class="btn btn-sm btn-info text-white"
                        title="Ver detalles">

                        <i class="bi bi-eye-fill"></i>

                    </a>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary btn-editar"
                        data-id="' . $fila["idProducto"] . '">

                        <i class="bi bi-pencil"></i>

                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-danger btn-eliminar"
                        data-id="' . $fila["idProducto"] . '"
                        data-nombre="' . htmlspecialchars($fila["nombre"]) . '">

                        <i class="bi bi-trash"></i>

                    </button>

                </div>

            </td>

        </tr>
        ';
    }
} else {

    $tabla = '

    <tr>

        <td colspan="10"
            class="text-center py-5 text-muted">

            <i class="bi bi-inbox fs-1 d-block mb-3"></i>

            No se encontraron productos.

        </td>

    </tr>';
}

/*=============================================
PAGINACIÓN SHOPIFY
=============================================*/

$paginacion = "";

if ($totalPaginas > 1) {

    $disabled =
        ($pagina <= 1)
        ? "disabled"
        : "";

    $paginaAnterior = $pagina - 1;

    $paginacion .= '

    <li class="page-item ' . $disabled . '">

        <button
            class="page-link btn-pagina"
            data-pagina="' . $paginaAnterior . '">

            <i class="bi bi-chevron-left"></i>

        </button>

    </li>';

    $inicioPaginas = max(
        1,
        $pagina - 2
    );

    $finPaginas = min(
        $totalPaginas,
        $pagina + 2
    );

    for ($i = $inicioPaginas; $i <= $finPaginas; $i++) {

        $active =
            ($i == $pagina)
            ? "active"
            : "";

        $paginacion .= '

        <li class="page-item ' . $active . '">

            <button
                class="page-link btn-pagina"
                data-pagina="' . $i . '">

                ' . $i . '

            </button>

        </li>';
    }

    $disabled =
        ($pagina >= $totalPaginas)
        ? "disabled"
        : "";

    $paginaSiguiente = $pagina + 1;

    $paginacion .= '

    <li class="page-item ' . $disabled . '">

        <button
            class="page-link btn-pagina"
            data-pagina="' . $paginaSiguiente . '">

            <i class="bi bi-chevron-right"></i>

        </button>

    </li>';
}

/*=============================================
KPIs
=============================================*/

$kpis = [];

/* Total Productos */

$sql = "
SELECT COUNT(*) total
FROM producto
WHERE id_user='$idUser'
AND Eliminado=0
";

$kpis["total_productos"] =
    mysqli_fetch_assoc(
        mysqli_query($conexion, $sql)
    )["total"];

/* Servicios */

$sql = "
SELECT COUNT(*) total
FROM producto
WHERE id_user='$idUser'
AND Eliminado=0
AND tipo='Servicio'
";

$kpis["servicios"] =
    mysqli_fetch_assoc(
        mysqli_query($conexion, $sql)
    )["total"];

/* Destacados */

$sql = "
SELECT COUNT(*) total
FROM producto
WHERE id_user='$idUser'
AND Eliminado=0
AND destacado=1
";

$kpis["destacados"] =
    mysqli_fetch_assoc(
        mysqli_query($conexion, $sql)
    )["total"];

/* Ofertas */

$sql = "
SELECT COUNT(*) total
FROM producto
WHERE id_user='$idUser'
AND Eliminado=0
AND oferta=1
";

$kpis["ofertas"] =
    mysqli_fetch_assoc(
        mysqli_query($conexion, $sql)
    )["total"];

/* Stock Bajo */

$sql = "
SELECT COUNT(*) total
FROM producto
WHERE id_user='$idUser'
AND Eliminado=0
AND stock <= 5
";

$kpis["stock_bajo"] =
    mysqli_fetch_assoc(
        mysqli_query($conexion, $sql)
    )["total"];

/* Valor Inventario */

$sql = "
SELECT
COALESCE(
    SUM(stock * costo_compra),
0) AS total
FROM producto
WHERE id_user='$idUser'
AND Eliminado=0
AND tipo='Producto'
AND stock > 0
";
$inventario = mysqli_fetch_assoc(
    mysqli_query($conexion, $sql)
)["total"];

$kpis["inventario"] = round(
    (float)$inventario,
    2
);
/*=============================================
TOP VENDIDOS
=============================================*/

$topVendidos = "";

$sqlTop = "

SELECT

    p.nombre,

    cpv.cantidad_total

FROM cantidad_producto_vendido cpv

INNER JOIN producto p
ON p.idProducto = cpv.idProducto

WHERE p.id_user = '$idUser'
AND p.Eliminado = 0

ORDER BY cpv.cantidad_total DESC

LIMIT 5

";

$rsTop = mysqli_query(
    $conexion,
    $sqlTop
);

if (mysqli_num_rows($rsTop) > 0) {

    while ($row = mysqli_fetch_assoc($rsTop)) {

        $topVendidos .= '

        <div class="d-flex justify-content-between mb-2">

            <span>

                ' . htmlspecialchars($row["nombre"]) . '

            </span>

            <span class="badge bg-primary">

                ' . intval($row["cantidad_total"]) . '

            </span>

        </div>';
    }
} else {

    $topVendidos = '

    <div class="text-muted text-center">

        No hay ventas registradas.

    </div>';
}
//Stock critico
$stockCritico = "";

$sqlStock = "
SELECT nombre, stock
FROM producto
WHERE id_user='$idUser'
AND Eliminado=0
AND stock <= 5
ORDER BY
    CASE
        WHEN stock = 0 THEN 0
        ELSE 1
    END,
    stock ASC
LIMIT 5
";

$rsStock = mysqli_query($conexion, $sqlStock);

while ($row = mysqli_fetch_assoc($rsStock)) {

    $stockCritico .= '

    <div class="mb-2">

        <strong>' .
        htmlspecialchars($row["nombre"]) .
        '</strong>

        <br>

        <span class="badge bg-warning text-dark">

            Stock: ' .
        $row["stock"] .
        '

        </span>

    </div>';
}
/*=============================================
TOP FAVORITOS
=============================================*/

$topFavoritos = "";

$sqlFavoritos = "

SELECT

    p.nombre,

    COUNT(f.id_favorito) AS total_favoritos

FROM favoritos f

INNER JOIN producto p
ON p.idProducto = f.idProducto

WHERE p.id_user = '$idUser'
AND p.Eliminado = 0

GROUP BY p.idProducto

ORDER BY total_favoritos DESC

LIMIT 5

";

$rsFavoritos = mysqli_query(
    $conexion,
    $sqlFavoritos
);

if (mysqli_num_rows($rsFavoritos) > 0) {

    while ($row = mysqli_fetch_assoc($rsFavoritos)) {

        $topFavoritos .= '

        <div class="d-flex justify-content-between mb-2">

            <span>

                ' . htmlspecialchars($row["nombre"]) . '

            </span>

            <span class="badge bg-danger">

                <i class="bi bi-heart-fill"></i>

                ' . intval($row["total_favoritos"]) . '

            </span>

        </div>';
    }
} else {

    $topFavoritos = '

    <div class="text-muted text-center">

        No hay favoritos registrados.

    </div>';
}
/*=============================================
RESPUESTA
=============================================*/

echo json_encode([

    "estado" => true,

    "tabla" => $tabla,

    "paginacion" => $paginacion,

    "totalRegistros" => $totalRegistros,

    "paginaActual" => $pagina,

    "totalPaginas" => $totalPaginas,

    "topVendidos" => $topVendidos,

    "topFavoritos" => $topFavoritos,

    "stockCritico" => $stockCritico,

    "kpis" => $kpis

]);

mysqli_close($conexion);
