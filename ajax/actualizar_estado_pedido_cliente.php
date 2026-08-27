<?php
//======================================================
// CoDevPro Technology
// ajax/actualizar_estado_pedido_cliente.php
// Consulta estado actualizado del pedido del cliente
//======================================================


session_start();



header("Content-Type: application/json; charset=utf-8");



/*======================================================
VALIDAR SESIÓN
======================================================*/


if (!isset($_SESSION["idCliente"])) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Sesión no válida"

    ]);


    exit;
}



require_once "../controladores/conexion.php";



$idCliente = intval($_SESSION["idCliente"]);



$idTicket = intval($_POST["id_ticket_ventas"] ?? 0);




if ($idTicket <= 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Pedido inválido"

    ]);


    exit;
}





/*======================================================
CONSULTAR ESTADO
======================================================*/


$sql = "

SELECT


tv.estado_envio,


tv.fecha_confirmado,


tv.fecha_preparando,


tv.fecha_enviado,


tv.fecha_cancelado,


tv.obervacion_envio



FROM ticket_ventas tv



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


    echo json_encode([

        "estado" => false,

        "mensaje" => mysqli_error($conexion)

    ]);


    exit;
}





mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idTicket,
    $idCliente
);




mysqli_stmt_execute($stmt);




$resultado = mysqli_stmt_get_result($stmt);



$pedido = mysqli_fetch_assoc($resultado);





if (!$pedido) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Pedido no encontrado"

    ]);


    exit;
}





/*======================================================
RESPUESTA JSON
======================================================*/


echo json_encode([


    "estado" => true,


    "pedido" => [


        "estado_envio" => $pedido["estado_envio"],


        "fecha_confirmado" => $pedido["fecha_confirmado"],


        "fecha_preparando" => $pedido["fecha_preparando"],


        "fecha_enviado" => $pedido["fecha_enviado"],


        "fecha_cancelado" => $pedido["fecha_cancelado"],


        "observacion" => $pedido["obervacion_envio"]


    ]


]);
