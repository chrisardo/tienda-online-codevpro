<?php
//=========================================================
// CoDevPro Technology
// Controlador: obtener_ofertas2.php
//=========================================================
session_start();
require_once "conexion.php";
$idCliente = $_SESSION["idCliente"] ?? 0;
/*=========================================================
=            PRODUCTOS EN OFERTA
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
            p.destacado,
            p.fecha_registro,

            m.nombre AS marca,

            CASE
                WHEN f.id_favorito IS NULL THEN 0
                ELSE 1
            END AS favorito,

            /*=========================================
            CALIFICACIÓN DEL PRODUCTO
            =========================================*/

            COALESCE(cal.promedio, 0) AS promedio,
            COALESCE(cal.total_opiniones, 0) AS total_opiniones,

            (
                SELECT i.imagenes
                FROM imagenes i
                WHERE i.idProducto = p.idProducto
                ORDER BY i.orden ASC
                LIMIT 1
            ) AS imagen

        FROM producto p

        LEFT JOIN marcas m
            ON m.id_marca = p.id_marca

        LEFT JOIN favoritos f
            ON f.idProducto = p.idProducto
            AND f.idCliente = $idCliente

        /*=========================================
        CALIFICACIONES
        =========================================*/

        LEFT JOIN (

            SELECT
                idProducto,
                AVG(calificacion) AS promedio,
                COUNT(*) AS total_opiniones

            FROM testimonios

            WHERE estado = 'APROBADO'

            GROUP BY idProducto

        ) cal

            ON cal.idProducto = p.idProducto

        WHERE p.tipo='producto'
        AND p.oferta = 1
        AND p.Eliminado = 0

        ORDER BY

            p.descuento DESC,

            p.fecha_registro DESC

        LIMIT 4";

$resultadoOfertas = mysqli_query($conexion, $sql);

$productosOferta = [];

while ($fila = mysqli_fetch_assoc($resultadoOfertas)) {

    /*=========================================
    DESCUENTO
    =========================================*/

    $precioAnterior = floatval($fila["precio_anterior"]);
    $precioActual   = floatval($fila["precio"]);

    if ($precioAnterior <= 0) {

        $precioAnterior = $precioActual;
    }

    if ($fila["descuento"] > 0) {

        $porcentaje = intval($fila["descuento"]);
    } elseif ($precioAnterior > $precioActual) {

        $porcentaje = round(
            (($precioAnterior - $precioActual) / $precioAnterior) * 100
        );
    } else {

        $porcentaje = 0;
    }

    /*=========================================
    PORCENTAJE STOCK
    =========================================*/

    $stock = intval($fila["stock"]);

    if ($stock >= 20) {

        $stockBarra = 100;
    } elseif ($stock >= 10) {

        $stockBarra = 70;
    } elseif ($stock >= 5) {

        $stockBarra = 45;
    } elseif ($stock > 0) {

        $stockBarra = 20;
    } else {

        $stockBarra = 0;
    }

    /*=========================================
    ETIQUETA
    =========================================*/

    $etiqueta = "";

    if ($stock == 0) {

        $etiqueta = "AGOTADO";
    } elseif ($stock <= 3) {

        $etiqueta = "ÚLTIMAS UNIDADES";
    } elseif ($porcentaje >= 40) {

        $etiqueta = "SUPER OFERTA";
    } elseif ($fila["destacado"] == 1) {

        $etiqueta = "DESTACADO";
    }

    /*=========================================
    IMAGEN
    =========================================*/

    if (!empty($fila["imagen"])) {

        $imagen = "mostrar_imagen.php?id=" . $fila["idProducto"];
    } else {

        $imagen = "./assets/img/sin_imagen.png";
    }

    /*=========================================
    NUEVO
    =========================================*/

    $nuevo = false;

    if (!empty($fila["fecha_registro"])) {

        $dias = floor(
            (time() - strtotime($fila["fecha_registro"])) / 86400
        );

        if ($dias <= 30) {

            $nuevo = true;
        }
    }

    /*=========================================
    ARRAY
    =========================================*/

    $promedio = round(floatval($fila["promedio"]), 1);
    $totalOpiniones = intval($fila["total_opiniones"]);

    $productosOferta[] = [

        "idProducto"       => $fila["idProducto"],

        "codigo"           => $fila["codigo"],

        "nombre"           => $fila["nombre"],

        "marca"            => $fila["marca"],

        "precio"           => $precioActual,

        "favorito"         => $fila["favorito"],

        "precioAnterior"   => $precioAnterior,

        "descuento"        => $porcentaje,

        "stock"            => $stock,

        "stockBarra"       => $stockBarra,

        "etiqueta"         => $etiqueta,

        "nuevo"            => $nuevo,

        "imagen"           => $imagen,

        /*=========================================
    CALIFICACIÓN
    =========================================*/

        "promedio"         => $promedio,

        "totalOpiniones"   => $totalOpiniones

    ];
}
