<?php
//Todo esto es de controladores/obtener_productos.php
require_once "conexion.php";
$idCliente = $_SESSION["idCliente"] ?? 0;
$limite = 12;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$inicio = ($pagina - 1) * $limite;

$sql = "SELECT
            p.*,
            c.nombre as categoria,
            m.nombre as marca,
            i.imagenes as imagen,
            i.id_imagen,
            CASE
                WHEN f.id_favorito IS NULL THEN 0
                ELSE 1
            END AS favorito
        FROM producto p
        INNER JOIN categorias c
            ON p.id_categorias = c.id_categorias
        INNER JOIN marcas m
            ON p.id_marca = m.id_marca
        INNER JOIN imagenes i
            ON i.idProducto=p.idProducto
            AND i.orden=1
            LEFT JOIN favoritos f
            ON f.idProducto=p.idProducto
            AND f.idCliente=$idCliente
        WHERE p.tipo='producto'
        ORDER BY p.idProducto DESC
        LIMIT $inicio,$limite";

$productos = mysqli_query($conexion, $sql);

if (!$productos) {

    die(mysqli_error($conexion));
}

/*============================
TOTAL
=============================*/

$totalConsulta = mysqli_query(
    $conexion,

    "SELECT COUNT(*) total
FROM producto
WHERE tipo='producto'"
);

$total = mysqli_fetch_assoc($totalConsulta);

$totalPaginas = ceil($total['total'] / $limite);
/*=========================
IMÁGENES
==========================*/

$sqlImagenes = "SELECT *

FROM imagenes

WHERE idProducto='$idProducto'

ORDER BY orden ASC";

$imagenes = mysqli_query($conexion, $sqlImagenes);
