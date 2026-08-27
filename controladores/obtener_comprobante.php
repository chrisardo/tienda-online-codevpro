<?php
//=========================================================
// CoDevPro Technology
// controladores/obtener_comprobante.php
//=========================================================


if (session_status() == PHP_SESSION_NONE) {

    session_start();
}


require_once "conexion.php";



/*=========================================================
VALIDAR CLIENTE
=========================================================*/


$idCliente = $_SESSION["idCliente"] ?? 0;


if ($idCliente <= 0) {

    die("Cliente no válido");
}



/*=========================================================
ID PEDIDO
=========================================================*/


$idTicket = intval($_GET["id_ticket_ventas"] ?? 0);



if ($idTicket <= 0) {

    die("Pedido inválido");
}




/*=========================================================
CABECERA DEL PEDIDO
=========================================================*/


$sql = "

SELECT

tv.id_ticket_ventas,
tv.fecha_venta,
tv.hora_venta,
tv.total_venta,
tv.tipo_comprobante,
tv.serie,
tv.numero,
tv.aplica_igv,
tv.direccion_envio,
tv.estado_envio,


c.nombre,
c.dni_o_ruc,
c.email,
c.celular,
c.direccion,


mp.nombre AS metodo_pago


FROM ticket_ventas tv


INNER JOIN clientes c

ON tv.idCliente = c.idCliente


LEFT JOIN metodo_pago mp

ON tv.id_metodo_pago = mp.id_metodo_pago


WHERE

tv.id_ticket_ventas = ?

AND

tv.idCliente = ?


LIMIT 1

";



$stmt = mysqli_prepare(
    $conexion,
    $sql
);



if (!$stmt) {

    die("Error SQL cabecera: "
        . mysqli_error($conexion));
}



mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idTicket,
    $idCliente
);



mysqli_stmt_execute($stmt);



$resultado = mysqli_stmt_get_result($stmt);



$venta = mysqli_fetch_assoc($resultado);



if (!$venta) {

    die("No existe el comprobante o no pertenece al cliente");
}



/*=========================================================
DETALLE PRODUCTOS
=========================================================*/


$sqlDetalle = "

SELECT

p.nombre,

d.cantidad_pedido_producto AS cantidad,

p.precio,

d.sub_total AS subtotal


FROM detalle_ticket_ventas d


INNER JOIN producto p

ON d.idProducto = p.idProducto


WHERE

d.id_ticket_ventas = ?


";


$stmtDetalle = mysqli_prepare(
    $conexion,
    $sqlDetalle
);



if (!$stmtDetalle) {

    die("Error SQL detalle: "
        . mysqli_error($conexion));
}



mysqli_stmt_bind_param(
    $stmtDetalle,
    "i",
    $idTicket
);



mysqli_stmt_execute(
    $stmtDetalle
);



$resultadoDetalle =
    mysqli_stmt_get_result(
        $stmtDetalle
    );



$productos = [];



while ($fila = mysqli_fetch_assoc($resultadoDetalle)) {


    $productos[] = $fila;
}




/*=========================================================
DATOS EMPRESA
=========================================================*/


$empresa = [

    "nombre" => "CoDevPro Technology",

    "direccion" => "Monitor Huáscar #811",

    "telefono" => "943 239 039",

    "ruc" => "",

];




return [

    "venta" => $venta,

    "productos" => $productos,

    "empresa" => $empresa

];
