<?php
//======================================================
// CoDevPro Technology
// controladores/obtener_categorias.php
//======================================================

require_once "conexion.php";

$sql = "SELECT
            c.id_categorias,
            c.nombre,
            c.imagen,
            COUNT(p.idProducto) AS totalProductos
        FROM categorias c

        LEFT JOIN producto p
            ON p.id_categorias = c.id_categorias
            AND p.Eliminado = 0
            AND p.tipo='producto'

        WHERE c.Eliminado = 0

        GROUP BY
            c.id_categorias,
            c.nombre,
            c.imagen

        ORDER BY c.nombre ASC";

$resultadoCategorias = mysqli_query($conexion, $sql);
