<?php

require_once "conexion.php";
session_start();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idCliente = $_SESSION["idCliente"] ?? 0;
if($id<=0){

    die("Producto no encontrado");

}

$sql = "SELECT
            p.*,
            c.nombre categoria,
            m.nombre marca,
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
        LEFT JOIN imagenes i
            ON i.idProducto=p.idProducto
            AND i.orden=1
        LEFT JOIN favoritos f
            ON f.idProducto=p.idProducto
            AND f.idCliente=$idCliente
WHERE p.id_producto=?

LIMIT 1";

$stmt=mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param($stmt,"i",$id);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$producto=mysqli_fetch_assoc($result);

if(!$producto){

    die("Producto no encontrado.");

}

/*=====================
IMÁGENES
======================*/

$sqlImagenes="SELECT *

FROM imagenes

WHERE idProducto=?

ORDER BY orden ASC";

$stmtImg=mysqli_prepare($conexion,$sqlImagenes);

mysqli_stmt_bind_param($stmtImg,"i",$id);

mysqli_stmt_execute($stmtImg);

$imagenes=mysqli_stmt_get_result($stmtImg);