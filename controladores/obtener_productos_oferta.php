<?php
//=========================================================
// CoDevPro Technology
// controladores/obtener_productos_oferta.php
//=========================================================

require_once "conexion.php";

/*=========================================================
=            CONFIGURACIÓN PAGINACIÓN
=========================================================*/
$idCliente = $_SESSION["idCliente"] ?? 0;
$limite = isset($_GET["limite"]) ? intval($_GET["limite"]) : 12;

$pagina = isset($_GET["pagina"]) ? intval($_GET["pagina"]) : 1;

if ($pagina <= 0) {
    $pagina = 1;
}

$offset = ($pagina - 1) * $limite;

/*=========================================================
=            ORDENAMIENTO
=========================================================*/

$orden = $_GET["orden"] ?? "recientes";

switch ($orden) {

    case "precio_asc":
        $orderBy = "p.precio ASC";
        break;

    case "precio_desc":
        $orderBy = "p.precio DESC";
        break;

    case "descuento":
        $orderBy = "p.descuento DESC";
        break;

    case "vendidos":

        $orderBy = "IFNULL(v.cantidad_total,0) DESC";

        break;

    default:

        $orderBy = "p.idProducto DESC";

        break;
}

/*=========================================================
=            TOTAL PRODUCTOS EN OFERTA
=========================================================*/

$sqlTotal = "SELECT COUNT(*) total

FROM producto

WHERE oferta = 1

AND Eliminado = 0";

$resTotal = mysqli_query($conexion, $sqlTotal);

$filaTotal = mysqli_fetch_assoc($resTotal);

$totalProductos = intval($filaTotal["total"]);

/*=========================================================
=            PRODUCTOS
=========================================================*/

$sql = "SELECT

p.idProducto,
p.codigo,
p.nombre,
p.precio,
p.precio_anterior,
p.descuento,
p.stock,
p.descripcion,

m.nombre AS marca,

(
SELECT id_imagen
FROM imagenes
WHERE idProducto=p.idProducto
ORDER BY orden ASC,id_imagen ASC
LIMIT 1
) AS idImagen,

IFNULL(v.cantidad_total,0) AS vendidos,
CASE
    WHEN f.id_favorito IS NULL THEN 0
    ELSE 1
END AS favorito

FROM producto p

LEFT JOIN marcas m

ON p.id_marca=m.id_marca

LEFT JOIN cantidad_producto_vendido v

ON p.idProducto=v.idProducto
LEFT JOIN favoritos f
            ON f.idProducto=p.idProducto
            AND f.idCliente=$idCliente

WHERE

p.oferta=1

AND p.Eliminado=0

ORDER BY $orderBy

LIMIT ?,?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $offset,
    $limite
);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

/*=========================================================
=            ARRAY RESULTADO
=========================================================*/

$productos = [];

while ($row = mysqli_fetch_assoc($res)) {

    $precioAnterior = floatval($row["precio_anterior"]);

    $precioActual = floatval($row["precio"]);

    if ($precioAnterior > 0) {

        $porcentaje = round(
            (($precioAnterior - $precioActual) /
                $precioAnterior) * 100
        );
    } else {

        $porcentaje = intval($row["descuento"]);
    }

    $row["porcentaje_descuento"] = $porcentaje;

    $productos[] = $row;
}

/*=========================================================
=            RESPUESTA
=========================================================*/

return [

    "productos" => $productos,

    "total" => $totalProductos,

    "pagina" => $pagina,

    "limite" => $limite

];
