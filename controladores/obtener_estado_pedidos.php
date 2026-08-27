<?php
//=========================================================
// CoDevPro Technology
// Archivo: controladores/obtener_estado_pedidos.php
// Módulo: Mis Pedidos
// Sistema: Inventa
//=========================================================

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}


require_once "conexion.php";


/*=========================================================
CABECERA JSON
=========================================================*/

header("Content-Type: application/json; charset=UTF-8");


/*=========================================================
VALIDAR CLIENTE
=========================================================*/

$idCliente =
    isset($_SESSION["idCliente"])
    ? (int)$_SESSION["idCliente"]
    : 0;


if ($idCliente <= 0) {

    echo json_encode(
        [
            "pendientes"    => 0,
            "confirmados"  => 0,
            "preparando"   => 0,
            "en_camino"    => 0,
            "entregados"   => 0,
            "no_entregados" => 0,
            "cancelados"   => 0
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*=========================================================
INICIALIZAR ESTADOS
=========================================================*/

$estados = [

    "pendientes"     => 0,

    "confirmados"    => 0,

    "preparando"     => 0,

    "en_camino"      => 0,

    "entregados"     => 0,

    "no_entregados"  => 0,

    "cancelados"     => 0

];


/*=========================================================
CONSULTAR ESTADOS
=========================================================*/

$sql = "

    SELECT

        estado_envio,

        COUNT(*) AS cantidad

    FROM ticket_ventas

    WHERE idCliente = ?

    GROUP BY estado_envio

";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    echo json_encode(
        $estados,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*=========================================================
BIND
=========================================================*/

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idCliente
);


/*=========================================================
EJECUTAR
=========================================================*/

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode(
        $estados,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*=========================================================
RESULTADO
=========================================================*/

$resultado =
    mysqli_stmt_get_result($stmt);


/*=========================================================
PROCESAR ESTADOS
=========================================================*/

if ($resultado) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $estado =
            strtoupper(
                trim(
                    $fila["estado_envio"] ?? ""
                )
            );


        $cantidad =
            (int)($fila["cantidad"] ?? 0);


        switch ($estado) {


            /*=====================================
            PENDIENTE
            =====================================*/

            case "PENDIENTE":

                $estados["pendientes"] += $cantidad;

                break;


            /*=====================================
            CONFIRMADO
            =====================================*/

            case "CONFIRMADO":

                $estados["confirmados"] += $cantidad;

                break;


            /*=====================================
            PREPARANDO
            =====================================*/

            case "PREPARANDO":

                $estados["preparando"] += $cantidad;

                break;


            /*=====================================
            EN CAMINO

            ASIGNADO
            OBTENIDO
            ENVIADO
            =====================================*/

            case "ASIGNADO":

                $estados["en_camino"] += $cantidad;

                break;


            case "OBTENIDO":

                $estados["en_camino"] += $cantidad;

                break;


            case "ENVIADO":

                $estados["en_camino"] += $cantidad;

                break;


            /*=====================================
            ENTREGADO
            =====================================*/

            case "ENTREGADO":

                $estados["entregados"] += $cantidad;

                break;


            /*=====================================
            NO ENTREGADO
            =====================================*/

            case "NO_ENTREGADO":

                $estados["no_entregados"] += $cantidad;

                break;


            /*=====================================
            CANCELADO
            =====================================*/

            case "CANCELADO":

                $estados["cancelados"] += $cantidad;

                break;
        }
    }
}


/*=========================================================
CERRAR STATEMENT
=========================================================*/

mysqli_stmt_close($stmt);


/*=========================================================
RESPUESTA
=========================================================*/

echo json_encode(
    $estados,
    JSON_UNESCAPED_UNICODE
);

exit;
