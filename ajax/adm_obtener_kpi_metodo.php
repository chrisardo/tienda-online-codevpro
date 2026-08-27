<?php
//=====================================================
// CoDevPro Technology
// ajax/adm_obtener_kpi_metodo.php
//=====================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

ini_set('display_errors', 0);
error_reporting(0);

require_once "../controladores/conexion.php";


/*=====================================================
=            VALIDAR SESIÓN
=====================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La sesión ha expirado."
    ]);

    exit;
}


$idUser = (int)$_SESSION["idUser"];


try {


    /*=====================================================
    =            TOTAL MÉTODOS
    =====================================================*/

    $sql = "
        SELECT COUNT(*) 
        FROM metodo_pago
        WHERE id_user = ?
    ";


    $stmt = mysqli_prepare($conexion, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );


    mysqli_stmt_execute($stmt);


    mysqli_stmt_bind_result(
        $stmt,
        $totalMetodos
    );


    mysqli_stmt_fetch($stmt);


    mysqli_stmt_close($stmt);

    /*=====================================================
    =            MÉTODOS UTILIZADOS
    =====================================================*/

    $sql = "
        SELECT COUNT(DISTINCT id_metodo_pago)
        FROM ticket_ventas
        WHERE id_user = ? AND estado_envio = 'ENTREGADO'
    ";


    $stmt = mysqli_prepare($conexion, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );


    mysqli_stmt_execute($stmt);


    mysqli_stmt_bind_result(
        $stmt,
        $utilizados
    );


    mysqli_stmt_fetch($stmt);


    mysqli_stmt_close($stmt);



    /*=====================================================
    =            TOTAL VENTAS
    =====================================================*/

    $sql = "
        SELECT COUNT(*)
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio = 'ENTREGADO'
    ";


    $stmt = mysqli_prepare($conexion, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );


    mysqli_stmt_execute($stmt);


    mysqli_stmt_bind_result(
        $stmt,
        $totalVentas
    );


    mysqli_stmt_fetch($stmt);


    mysqli_stmt_close($stmt);



    /*=====================================================
    =            MONTO VENDIDO
    =====================================================*/

    $sql = "
        SELECT COALESCE(SUM(total_venta),0)
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio = 'ENTREGADO'
    ";


    $stmt = mysqli_prepare($conexion, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );


    mysqli_stmt_execute($stmt);


    mysqli_stmt_bind_result(
        $stmt,
        $totalMonto
    );


    mysqli_stmt_fetch($stmt);


    mysqli_stmt_close($stmt);



    /*=====================================================
    =            RESPUESTA JSON
    =====================================================*/

    echo json_encode([

        "estado" => true,

        "kpi" => [

            "total_metodos" => (int)$totalMetodos,

            "utilizados" => (int)$utilizados,

            "total_ventas" => (int)$totalVentas,

            "total_monto" => (float)$totalMonto

        ]

    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {


    echo json_encode([

        "estado" => false,

        "mensaje" => $e->getMessage()

    ]);
}


if (isset($conexion)) {

    mysqli_close($conexion);
}
