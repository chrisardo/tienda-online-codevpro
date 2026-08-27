<?php
//==========================================================
// CoDevPro Technology
// ajax/anular_comprobante.php
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
=            RECIBIR DATOS JSON
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
=            INICIAR TRANSACCIÓN
==========================================================*/


mysqli_begin_transaction($conexion);



try {



    /*======================================================
    =            BUSCAR COMPROBANTE
    ======================================================*/


    $sql = "

    SELECT

    id_ticket_ventas,

    estado_venta


    FROM ticket_ventas


    WHERE

    id_ticket_ventas=?

    AND id_user=?


    LIMIT 1

    ";



    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );



    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idTicket,
        $idUser
    );



    mysqli_stmt_execute($stmt);



    $resultado = mysqli_stmt_get_result($stmt);



    if (mysqli_num_rows($resultado) == 0) {


        throw new Exception(
            "El comprobante no existe"
        );
    }



    $ticket = mysqli_fetch_assoc($resultado);





    if ($ticket["estado_venta"] == "ANULADO") {


        throw new Exception(
            "El comprobante ya está anulado"
        );
    }





    /*======================================================
    =            OBTENER PRODUCTOS VENDIDOS
    ======================================================*/


    $sqlDetalle = "


    SELECT


    idProducto,


    cantidad_pedido_producto


    FROM detalle_ticket_ventas


    WHERE


    id_ticket_ventas=?


    AND id_user=?



    ";



    $stmtDetalle = mysqli_prepare(
        $conexion,
        $sqlDetalle
    );



    mysqli_stmt_bind_param(
        $stmtDetalle,
        "ii",
        $idTicket,
        $idUser
    );



    mysqli_stmt_execute($stmtDetalle);



    $productos = mysqli_stmt_get_result(
        $stmtDetalle
    );





    /*======================================================
    =            RESTAURAR STOCK
    ======================================================*/


    while ($producto = mysqli_fetch_assoc($productos)) {



        $idProducto = $producto["idProducto"];


        $cantidad = $producto["cantidad_pedido_producto"];




        /*
        Actualizar stock producto
        */


        $sqlStock = "


        UPDATE producto


        SET stock = stock + ?


        WHERE

        idProducto=?

        AND id_user=?


        ";



        $stmtStock = mysqli_prepare(
            $conexion,
            $sqlStock
        );



        mysqli_stmt_bind_param(
            $stmtStock,
            "iii",
            $cantidad,
            $idProducto,
            $idUser
        );



        if (!mysqli_stmt_execute($stmtStock)) {


            throw new Exception(
                "Error restaurando stock"
            );
        }






        /*
        Actualizar cantidad vendida
        */


        $sqlCantidad = "


        UPDATE cantidad_producto_vendido


        SET cantidad_total = 

        cantidad_total - ?


        WHERE

        idProducto=?

        AND id_user=?



        ";



        $stmtCantidad = mysqli_prepare(
            $conexion,
            $sqlCantidad
        );



        mysqli_stmt_bind_param(
            $stmtCantidad,
            "iii",
            $cantidad,
            $idProducto,
            $idUser
        );



        mysqli_stmt_execute(
            $stmtCantidad
        );
    }







    /*======================================================
    =            CAMBIAR ESTADO COMPROBANTE
    ======================================================*/


    $sqlAnular = "


    UPDATE ticket_ventas


    SET


    estado_venta='ANULADO',


    estado_envio='CANCELADO',


    fecha_cancelado=NOW()


    WHERE


    id_ticket_ventas=?


    AND id_user=?


    ";



    $stmtAnular = mysqli_prepare(
        $conexion,
        $sqlAnular
    );



    mysqli_stmt_bind_param(
        $stmtAnular,
        "ii",
        $idTicket,
        $idUser
    );



    if (!mysqli_stmt_execute($stmtAnular)) {


        throw new Exception(
            "No se pudo anular el comprobante"
        );
    }






    /*======================================================
    =            CONFIRMAR CAMBIOS
    ======================================================*/


    mysqli_commit($conexion);




    echo json_encode([


        "estado" => true,


        "mensaje" => "Comprobante anulado correctamente. Stock restaurado."



    ]);
} catch (Exception $e) {



    mysqli_rollback($conexion);



    echo json_encode([


        "estado" => false,


        "mensaje" => $e->getMessage()



    ]);
}
