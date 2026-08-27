<?php
// Toda esta parte pertenece a controladores/obtener_productos_destacados.php

include 'conexion.php';

$idCliente = $_SESSION["idCliente"] ?? 0;

$sql = "SELECT

            p.*,

            c.nombre AS categoria,

            m.nombre AS marca,

            i.imagenes AS imagen,

            i.id_imagen,

            CASE
                WHEN f.id_favorito IS NULL THEN 0
                ELSE 1
            END AS favorito,

            /* =========================================
               CALIFICACIÓN DEL PRODUCTO
            ========================================= */

            COALESCE(ROUND(AVG(t.calificacion), 1), 0) AS promedio_calificacion,

            COUNT(t.id_testimonio) AS total_opiniones

        FROM producto p

        LEFT JOIN categorias c
            ON p.id_categorias = c.id_categorias

        LEFT JOIN marcas m
            ON p.id_marca = m.id_marca

        LEFT JOIN imagenes i
            ON i.idProducto = p.idProducto
            AND i.orden = 1

        LEFT JOIN favoritos f
            ON f.idProducto = p.idProducto
            AND f.idCliente = $idCliente

        /* =========================================
           SOLO OPINIONES APROBADAS
        ========================================= */

        LEFT JOIN testimonios t
            ON t.idProducto = p.idProducto
            AND t.estado = 'APROBADO'

        WHERE p.tipo = 'producto'

        GROUP BY p.idProducto

        ORDER BY p.idProducto DESC

        LIMIT 8";

$resultadoProductos = mysqli_query($conexion, $sql);

if (!$resultadoProductos) {

    die("Error en la consulta de productos: " . mysqli_error($conexion));
}
