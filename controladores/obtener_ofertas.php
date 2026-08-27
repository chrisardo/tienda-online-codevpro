<?php

require_once "conexion.php";
$idCliente = $_SESSION["idCliente"] ?? 0;
$sql = "SELECT
            p.idProducto,
            p.nombre,
            p.precio,
            p.precio_anterior,
            p.descuento,
            p.stock,
            p.descripcion,
            c.nombre AS nombreCategoria,
            m.nombre AS nombreMarca,
            CASE
                WHEN f.id_favorito IS NULL THEN 0
                ELSE 1
            END AS favorito
        FROM producto p
        INNER JOIN categorias c
            ON c.id_categorias = p.id_categorias
        INNER JOIN marcas m
            ON m.id_marca = p.id_marca
        LEFT JOIN favoritos f
            ON f.idProducto=p.idProducto
            AND f.idCliente=$idCliente
        WHERE p.oferta = 1

        ORDER BY p.idProducto DESC
        LIMIT 8";

$ofertas = mysqli_query($conexion, $sql);

if (!$ofertas) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
