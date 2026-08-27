<?php
//==========================================================
// CoDevPro Technology
// ajax/obtener_detalle_comprobante.php
//==========================================================

session_start();

header("Content-Type: application/json; charset=utf-8");


require_once "../controladores/conexion.php";



/*==========================================================
=            VALIDAR SESIÓN
==========================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([

        "estado" => false,

        "mensaje" => "Sesión expirada"

    ]);

    exit;
}



$idUser = $_SESSION["idUser"];



/*==========================================================
=            RECIBIR JSON
==========================================================*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);



$idTicket = intval($data["id"] ?? 0);



if ($idTicket <= 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Comprobante inválido"

    ]);

    exit;
}





/*==========================================================
=            OBTENER CABECERA COMPROBANTE
==========================================================*/


$sql = "

SELECT

tv.id_ticket_ventas,

tv.tipo_comprobante,

tv.serie,

tv.numero,


tv.fecha_venta,

tv.hora_venta,


tv.total_venta,

tv.pago_cliente,

tv.vuelto_venta,


tv.estado_venta,

tv.aplica_igv,


tv.id_metodo_pago,


tv.idCliente,


tv.id_empleado,



CONCAT(
    c.nombre
) AS cliente,


c.dni_o_ruc,

c.direccion,

c.email,



mp.nombre AS metodo_pago,



CONCAT(
    e.nombre,
    ' ',
    e.apellido
) AS empleado



FROM ticket_ventas tv



LEFT JOIN clientes c

ON tv.idCliente = c.idCliente



LEFT JOIN metodo_pago mp

ON tv.id_metodo_pago = mp.id_metodo_pago



LEFT JOIN empleados e

ON tv.id_empleado = e.id_empleado



WHERE 

tv.id_ticket_ventas = ?

AND tv.id_user = ?



LIMIT 1

";



$stmt = mysqli_prepare($conexion, $sql);



mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idTicket,
    $idUser
);



mysqli_stmt_execute($stmt);



$result = mysqli_stmt_get_result($stmt);



if (mysqli_num_rows($result) == 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Comprobante no encontrado"

    ]);

    exit;
}



$comprobante = mysqli_fetch_assoc($result);





/*==========================================================
=            OBTENER DETALLE PRODUCTOS
==========================================================*/


$sqlDetalle = "


SELECT


dt.id_detalle_ticket,


dt.cantidad_pedido_producto AS cantidad,


dt.sub_total AS subtotal,



p.nombre AS producto,

p.precio



FROM detalle_ticket_ventas dt



INNER JOIN producto p

ON dt.idProducto = p.idProducto



WHERE

dt.id_ticket_ventas = ?

AND dt.id_user = ?



ORDER BY dt.id_detalle_ticket ASC



";




$stmt2 = mysqli_prepare(
    $conexion,
    $sqlDetalle
);



mysqli_stmt_bind_param(
    $stmt2,
    "ii",
    $idTicket,
    $idUser
);



mysqli_stmt_execute($stmt2);



$resultDetalle = mysqli_stmt_get_result($stmt2);



$detalle = [];



$subtotal = 0;



while ($row = mysqli_fetch_assoc($resultDetalle)) {


    $subtotal += floatval($row["subtotal"]);


    $detalle[] = [

        "producto" => $row["producto"],

        "cantidad" => $row["cantidad"],

        "precio" => $row["precio"],

        "subtotal" => $row["subtotal"]

    ];
}





/*==========================================================
=            CALCULO IGV
==========================================================*/


if ($comprobante["aplica_igv"] == 1) {


    $igv = $subtotal * 0.18;
} else {


    $igv = 0;
}



$total = $subtotal + $igv;





/*==========================================================
=            RESPUESTA JSON
==========================================================*/


echo json_encode([


    "estado" => true,


    "comprobante" => [


        "id_ticket_ventas" => $comprobante["id_ticket_ventas"],


        "tipo_comprobante" => $comprobante["tipo_comprobante"],


        "serie" => $comprobante["serie"],


        "numero" => $comprobante["numero"],


        "fecha_venta" => $comprobante["fecha_venta"],


        "hora_venta" => $comprobante["hora_venta"],


        "cliente" => $comprobante["cliente"] ?? "Cliente eliminado",


        "dni_o_ruc" => $comprobante["dni_o_ruc"] ?? "-",


        "direccion" => $comprobante["direccion"] ?? "-",


        "email" => $comprobante["email"] ?? "-",


        "metodo_pago" => $comprobante["metodo_pago"] ?? "-",


        "empleado" => $comprobante["empleado"] ?? "-",


        "estado_venta" => $comprobante["estado_venta"],


        "subtotal" => number_format($subtotal, 2, '.', ''),


        "igv" => number_format($igv, 2, '.', ''),


        "total_venta" => number_format($total, 2, '.', '')


    ],



    "detalle" => $detalle



], JSON_UNESCAPED_UNICODE);
