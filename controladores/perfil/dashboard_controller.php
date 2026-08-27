<?php
//======================================================
// CoDevPro Technology
// controladores/perfil/dashboard_controller.php
//======================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../conexion.php";

if (!isset($_SESSION["idCliente"])) {
    exit("Acceso denegado.");
}

$idCliente = intval($_SESSION["idCliente"]);

/*======================================================
=            INFORMACIÓN DEL CLIENTE
======================================================*/

$sqlCliente = "
SELECT *
FROM clientes
WHERE idCliente = '$idCliente'
LIMIT 1
";

$resultadoCliente = mysqli_query($conexion, $sqlCliente);
$cliente = mysqli_fetch_assoc($resultadoCliente);

/*======================================================
=            ESTADÍSTICAS
======================================================*/

/* Pedidos */

$sqlPedidos = "
SELECT COUNT(*) total
FROM ticket_ventas
WHERE idCliente='$idCliente'
";

$totalPedidos = mysqli_fetch_assoc(mysqli_query($conexion, $sqlPedidos));
$totalPedidos = intval($totalPedidos["total"]);

/* Total comprado */

$sqlTotalComprado = "
SELECT
IFNULL(SUM(total_venta),0) total
FROM ticket_ventas
WHERE idCliente='$idCliente'
AND estado_venta <> 'ANULADO'
";

$totalComprado = mysqli_fetch_assoc(mysqli_query($conexion, $sqlTotalComprado));
$totalComprado = floatval($totalComprado["total"]);

/* Favoritos */

$sqlFavoritos = "
SELECT COUNT(*) total
FROM favoritos
WHERE idCliente='$idCliente'
";

$totalFavoritos = mysqli_fetch_assoc(mysqli_query($conexion, $sqlFavoritos));
$totalFavoritos = intval($totalFavoritos["total"]);

/* Testimonios */

$sqlTestimonios = "
SELECT COUNT(*) total
FROM testimonios
WHERE idCliente='$idCliente'
";

$totalTestimonios = mysqli_fetch_assoc(mysqli_query($conexion, $sqlTestimonios));
$totalTestimonios = intval($totalTestimonios["total"]);

/*======================================================
=            ÚLTIMO PEDIDO
======================================================*/

$sqlUltimoPedido = "
SELECT *
FROM ticket_ventas
WHERE idCliente='$idCliente'
ORDER BY id_ticket_ventas DESC
LIMIT 1
";

$resultadoUltimoPedido = mysqli_query($conexion, $sqlUltimoPedido);

$ultimoPedido = mysqli_fetch_assoc($resultadoUltimoPedido);

/*======================================================
=            DETALLE DEL ÚLTIMO PEDIDO
======================================================*/

$detalleUltimoPedido = [];

if ($ultimoPedido) {

    $idTicket = intval($ultimoPedido["id_ticket_ventas"]);

    $sqlDetalle = "

    SELECT

        d.*,

        p.nombre,

        p.codigo,

        p.precio,

        (

            SELECT id_imagen

            FROM imagenes

            WHERE idProducto=p.idProducto

            ORDER BY orden ASC

            LIMIT 1

        ) idImagen

    FROM detalle_ticket_ventas d

    INNER JOIN producto p
        ON p.idProducto=d.idProducto

    WHERE d.id_ticket_ventas='$idTicket'

    ";

    $resultadoDetalle = mysqli_query($conexion, $sqlDetalle);

    while ($fila = mysqli_fetch_assoc($resultadoDetalle)) {

        $detalleUltimoPedido[] = $fila;
    }
}

/*======================================================
=            PRODUCTO FAVORITO
======================================================*/

$sqlFavorito = "

SELECT

f.id_favorito,
f.fecha,

p.idProducto,
p.nombre,
p.precio,
p.precio_anterior,
p.descuento,
p.oferta,
p.stock,
p.envio_gratis,

(

SELECT id_imagen

FROM imagenes

WHERE idProducto = p.idProducto

ORDER BY orden ASC

LIMIT 1

) AS idImagen

FROM favoritos f

INNER JOIN producto p
ON p.idProducto=f.idProducto

WHERE f.idCliente='$idCliente'

ORDER BY f.id_favorito DESC

LIMIT 1

";

$resultadoFavorito = mysqli_query($conexion, $sqlFavorito);

$productoFavorito = mysqli_fetch_assoc($resultadoFavorito);

/*======================================================
=            ACTIVIDAD RECIENTE
======================================================*/

$actividad = [];

/*=========================================
ÚLTIMA COMPRA
=========================================*/

$sqlCompra = "

SELECT

tv.fecha_venta AS fecha,

CONCAT('Pedido ',tv.serie,'-',tv.numero) AS titulo,

CONCAT(

COUNT(d.id_detalle_ticket),

' producto(s) - S/ ',

FORMAT(tv.total_venta,2)

) AS descripcion

FROM ticket_ventas tv

INNER JOIN detalle_ticket_ventas d
ON d.id_ticket_ventas = tv.id_ticket_ventas

WHERE tv.idCliente='$idCliente'

GROUP BY tv.id_ticket_ventas

ORDER BY tv.id_ticket_ventas DESC

LIMIT 1

";

$resultado = mysqli_query($conexion, $sqlCompra);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $fila["tipo"] = "COMPRA";

    $actividad[] = $fila;
}


/*=========================================
ÚLTIMO FAVORITO
=========================================*/

$sqlFavorito = "

SELECT

f.fecha,

p.nombre AS descripcion

FROM favoritos f

INNER JOIN producto p
ON p.idProducto=f.idProducto

WHERE f.idCliente='$idCliente'

ORDER BY f.id_favorito DESC

LIMIT 1

";

$resultado = mysqli_query($conexion, $sqlFavorito);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $fila["tipo"] = "FAVORITO";

    $fila["titulo"] = "Producto agregado a favoritos";

    $actividad[] = $fila;
}


/*=========================================
ÚLTIMO TESTIMONIO
=========================================*/

$sqlTestimonio = "

SELECT

t.fecha,

p.nombre AS descripcion

FROM testimonios t

INNER JOIN producto p
ON p.idProducto=t.idProducto

WHERE t.idCliente='$idCliente'

ORDER BY t.id_testimonio DESC

LIMIT 1

";

$resultado = mysqli_query($conexion, $sqlTestimonio);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $fila["tipo"] = "TESTIMONIO";

    $fila["titulo"] = "Testimonio enviado";

    $actividad[] = $fila;
}


/*=========================================
ORDENAR POR FECHA
=========================================*/

usort($actividad, function ($a, $b) {

    return strtotime($b["fecha"]) - strtotime($a["fecha"]);
});

/*======================================================
=            ESTADO DEL ENVÍO
======================================================*/

$estadoEnvio = "";

if ($ultimoPedido) {

    $estadoEnvio = $ultimoPedido["estado_envio"];
}
/*======================================================
=            PROGRESO DEL ENVÍO
======================================================*/

$pasosEnvio = [

    "CONFIRMADO" => false,
    "PREPARANDO" => false,
    "ENVIADO"    => false,
    "ENTREGADO"  => false,
    "CANCELADO"  => false

];

if ($ultimoPedido) {

    switch ($ultimoPedido["estado_envio"]) {

        case "PENDIENTE":

            break;

        case "CONFIRMADO":

            $pasosEnvio["CONFIRMADO"] = true;

            break;

        case "PREPARANDO":

            $pasosEnvio["CONFIRMADO"] = true;
            $pasosEnvio["PREPARANDO"] = true;

            break;

        case "ENVIADO":

            $pasosEnvio["CONFIRMADO"] = true;
            $pasosEnvio["PREPARANDO"] = true;
            $pasosEnvio["ENVIADO"] = true;

            break;

        case "ENTREGADO":

            $pasosEnvio["CONFIRMADO"] = true;
            $pasosEnvio["PREPARANDO"] = true;
            $pasosEnvio["ENVIADO"] = true;
            $pasosEnvio["ENTREGADO"] = true;

            break;

        case "CANCELADO":

            $pasosEnvio["CANCELADO"] = true;

            break;
    }
}
/*======================================================
=            VARIABLES DISPONIBLES PARA dashboard.php
======================================================*/

/*

$cliente

$totalPedidos

$totalComprado

$totalFavoritos

$totalTestimonios

$ultimoPedido

$detalleUltimoPedido

$productoFavorito

$actividad

$estadoEnvio

*/