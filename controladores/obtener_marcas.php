<?php

require_once "conexion.php";

$sql = "SELECT
            m.id_marca,
            m.nombre,
            m.imagen,
            COUNT(p.idProducto) AS totalProductos
        FROM marcas m
        LEFT JOIN producto p
            ON p.id_marca = m.id_marca
        GROUP BY
            m.id_marca,
            m.nombre
        ORDER BY
            totalProductos DESC,
            m.nombre ASC";

$marcas = mysqli_query($conexion, $sql);

if(!$marcas){
    die(mysqli_error($conexion));
}

?>