<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_graficos_metodos.php
//=====================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*=====================================================
=            VALIDAR CONEXIÓN
=====================================================*/

if (!isset($conexion) || !$conexion) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo establecer conexión con la base de datos."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*=====================================================
=            VALIDAR SESIÓN
=====================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La sesión ha expirado."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$idUser = (int) $_SESSION["idUser"];


/*=====================================================
=            RESPUESTA INICIAL
=====================================================*/

$respuesta = [

    "estado" => true,

    "ventasMetodo" => [
        "labels" => [],
        "data" => []
    ],

    "montoMetodo" => [
        "labels" => [],
        "data" => []
    ],

    "historico" => [
        "labels" => [],
        "datasets" => []
    ]

];


try {


    /*=================================================
    =            1. VENTAS POR MÉTODO
    =================================================*/

    $sql = "

        SELECT

            mp.id_metodo_pago,

            mp.nombre,

            COUNT(tv.id_ticket_ventas) AS total_ventas

        FROM metodo_pago mp

        LEFT JOIN ticket_ventas tv

            ON tv.id_metodo_pago = mp.id_metodo_pago

            AND tv.id_user = ?

            AND tv.estado_venta <> 'ANULADO'

        WHERE mp.id_user = ?

        AND mp.Eliminado = 0 AND tv.estado_envio = 'ENTREGADO'

        GROUP BY

            mp.id_metodo_pago,

            mp.nombre

        ORDER BY total_ventas DESC

    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );


    if (!$stmt) {

        throw new Exception(
            "Error SQL ventas por método: " .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idUser,
        $idUser
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Error ejecutando ventas por método: " .
                mysqli_stmt_error($stmt)
        );
    }


    $resultado = mysqli_stmt_get_result($stmt);


    if (!$resultado) {

        throw new Exception(
            "No se pudo obtener resultado de ventas por método."
        );
    }


    while ($fila = mysqli_fetch_assoc($resultado)) {

        $respuesta["ventasMetodo"]["labels"][] =
            $fila["nombre"];

        $respuesta["ventasMetodo"]["data"][] =
            (int) $fila["total_ventas"];
    }


    mysqli_stmt_close($stmt);


    /*=================================================
    =            2. MONTO VENDIDO
    =================================================*/

    $sql = "

        SELECT

            mp.id_metodo_pago,

            mp.nombre,

            COALESCE(
                SUM(tv.total_venta),
                0
            ) AS total_monto

        FROM metodo_pago mp

        LEFT JOIN ticket_ventas tv

            ON tv.id_metodo_pago = mp.id_metodo_pago

            AND tv.id_user = ?

            AND tv.estado_envio = 'ENTREGADO'

        WHERE mp.id_user = ?

        AND mp.Eliminado = 0 

        GROUP BY

            mp.id_metodo_pago,

            mp.nombre

        ORDER BY total_monto DESC

    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );


    if (!$stmt) {

        throw new Exception(
            "Error SQL monto por método: " .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idUser,
        $idUser
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Error ejecutando monto por método: " .
                mysqli_stmt_error($stmt)
        );
    }


    $resultado = mysqli_stmt_get_result($stmt);


    if (!$resultado) {

        throw new Exception(
            "No se pudo obtener resultado de monto por método."
        );
    }


    while ($fila = mysqli_fetch_assoc($resultado)) {

        $respuesta["montoMetodo"]["labels"][] =
            $fila["nombre"];

        $respuesta["montoMetodo"]["data"][] =
            (float) $fila["total_monto"];
    }


    mysqli_stmt_close($stmt);


    /*=================================================
    =            3. MÉTODOS ACTIVOS
    =================================================*/

    $metodos = [];


    $sql = "

        SELECT

            id_metodo_pago,

            nombre

        FROM metodo_pago

        WHERE id_user = ?

        AND Eliminado = 0 

        ORDER BY nombre ASC

    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );


    if (!$stmt) {

        throw new Exception(
            "Error SQL métodos: " .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Error ejecutando métodos: " .
                mysqli_stmt_error($stmt)
        );
    }


    $resultado = mysqli_stmt_get_result($stmt);


    while ($fila = mysqli_fetch_assoc($resultado)) {

        $metodos[] = [

            "id" => (int) $fila["id_metodo_pago"],

            "nombre" => $fila["nombre"]

        ];
    }


    mysqli_stmt_close($stmt);


    /*=================================================
    =            4. ÚLTIMOS 30 DÍAS
    =================================================*/

    $fechas = [];


    for ($i = 29; $i >= 0; $i--) {

        $fecha = date(
            "Y-m-d",
            strtotime("-{$i} days")
        );


        $fechas[] = $fecha;


        $respuesta["historico"]["labels"][] =
            date(
                "d/m",
                strtotime($fecha)
            );
    }


    /*=================================================
    =            5. DATASETS
    =================================================*/

    foreach ($metodos as $metodo) {

        $respuesta["historico"]["datasets"][] = [

            "id" => $metodo["id"],

            "label" => $metodo["nombre"],

            "data" => array_fill(
                0,
                30,
                0
            )

        ];
    }


    /*=================================================
    =            6. HISTÓRICO
    =================================================*/

    if (!empty($metodos)) {


        $sql = "

            SELECT

                DATE(tv.fecha_venta) AS fecha,

                tv.id_metodo_pago,

                COUNT(
                    tv.id_ticket_ventas
                ) AS total_ventas

            FROM ticket_ventas tv

            INNER JOIN metodo_pago mp

                ON mp.id_metodo_pago =
                   tv.id_metodo_pago

                AND mp.id_user = ?

                AND mp.Eliminado = 0

            WHERE tv.id_user = ?

            AND tv.estado_envio = 'ENTREGADO'

            AND tv.fecha_venta >=
                DATE_SUB(
                    CURDATE(),
                    INTERVAL 29 DAY
                )

            GROUP BY

                DATE(tv.fecha_venta),

                tv.id_metodo_pago

            ORDER BY

                fecha ASC

        ";


        $stmt = mysqli_prepare(
            $conexion,
            $sql
        );


        if (!$stmt) {

            throw new Exception(
                "Error SQL histórico: " .
                    mysqli_error($conexion)
            );
        }


        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $idUser,
            $idUser
        );


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Error ejecutando histórico: " .
                    mysqli_stmt_error($stmt)
            );
        }


        $resultado =
            mysqli_stmt_get_result($stmt);


        if (!$resultado) {

            throw new Exception(
                "No se pudo obtener el histórico."
            );
        }


        /*=============================================
        =            MAPA DE MÉTODOS
        =============================================*/

        $indiceMetodo = [];


        foreach (
            $respuesta["historico"]["datasets"]
            as $indice => $dataset
        ) {

            $indiceMetodo[(int) $dataset["id"]] = $indice;
        }


        /*=============================================
        =            RESULTADOS
        =============================================*/

        while (
            $fila =
            mysqli_fetch_assoc($resultado)
        ) {


            $idMetodo =
                (int) $fila["id_metodo_pago"];


            $fecha =
                $fila["fecha"];


            $total =
                (int) $fila["total_ventas"];


            /*=========================================
            =            VALIDAR MÉTODO
            =========================================*/

            if (
                !isset(
                    $indiceMetodo[$idMetodo]
                )
            ) {

                continue;
            }


            $indice =
                $indiceMetodo[$idMetodo];


            /*=========================================
            =            BUSCAR FECHA
            =========================================*/

            $posicion =
                array_search(
                    $fecha,
                    $fechas,
                    true
                );


            if ($posicion === false) {

                continue;
            }


            /*=========================================
            =            ASIGNAR VENTA
            =========================================*/

            $respuesta["historico"]["datasets"][$indice]["data"][$posicion] = $total;
        }


        mysqli_stmt_close($stmt);
    }


    /*=================================================
    =            RESPUESTA
    =================================================*/

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );
} catch (Throwable $e) {


    /*=================================================
    =            ERROR
    =================================================*/

    http_response_code(500);


    echo json_encode(
        [

            "estado" => false,

            "mensaje" =>
            "Error al cargar los gráficos: " .
                $e->getMessage()

        ],
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );
} finally {


    /*=================================================
    =            CERRAR CONEXIÓN
    =================================================*/

    if (
        isset($conexion)
        &&
        $conexion
    ) {

        mysqli_close($conexion);
    }
}
