<?php
//Toda esta parte pertenece a controladores/obtener_testimonios.php
require_once "conexion.php";

$sql = "SELECT

            t.*,

            c.nombre AS cliente,
            c.imagen as imagen,

            p.nombre AS producto

        FROM testimonios t

        INNER JOIN clientes c
            ON c.idCliente=t.idCliente

        LEFT JOIN producto p
            ON p.idProducto=t.idProducto

        WHERE t.estado= 'APROBADO'

        ORDER BY t.fecha DESC

        LIMIT 4";

$testimonios = mysqli_query($conexion, $sql);

if (!$testimonios) {

    die(mysqli_error($conexion));
}
