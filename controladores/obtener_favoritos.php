<?php
//=========================================================
// CoDevPro Technology
// Controlador: obtener_favoritos.php
//=========================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "conexion.php";

$idCliente = $_SESSION["idCliente"] ?? 0;

if ($idCliente <= 0) {

    $resultadoFavoritos = false;
    return;
}

/*=========================================================
=            FAVORITOS DEL CLIENTE
=========================================================*/

$sql = "SELECT

            p.idProducto,
            p.codigo,
            p.nombre,
            p.precio,
            p.precio_anterior,
            p.stock,
            p.descuento,
            p.descripcion,
            p.destacado,
            p.oferta,

            m.nombre AS marca,

            i.id_imagen,

            (
                SELECT imagenes
                FROM imagenes
                WHERE idProducto = p.idProducto
                ORDER BY orden ASC
                LIMIT 1
            ) AS imagen

        FROM favoritos f

        INNER JOIN producto p
            ON p.idProducto = f.idProducto

        LEFT JOIN marcas m
            ON m.id_marca = p.id_marca

        LEFT JOIN imagenes i
            ON i.idProducto = p.idProducto
            AND i.orden = 1

        WHERE

            f.idCliente = ?

            AND p.Eliminado = 0

            AND p.tipo='producto'

        ORDER BY

            f.id_favorito DESC";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "i", $idCliente);

mysqli_stmt_execute($stmt);

$resultadoFavoritos = mysqli_stmt_get_result($stmt);
