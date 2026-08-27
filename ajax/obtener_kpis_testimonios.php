<?php
//=====================================================
// CoDevPro Technology
// ajax/obtener_kpis_testimonios.php
//=====================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}

try {

    /*=========================================
    = KPI PRINCIPALES
    =========================================*/

    $sql = "

    SELECT

        COUNT(*) AS total,

        COALESCE(
            ROUND(AVG(calificacion),1),
            0
        ) AS promedio,

        SUM(
            CASE
                WHEN estado='PENDIENTE'
                THEN 1
                ELSE 0
            END
        ) AS pendientes,

        SUM(
            CASE
                WHEN respuesta IS NOT NULL
                AND TRIM(respuesta) <> ''
                THEN 1
                ELSE 0
            END
        ) AS respondidos,

        SUM(
            CASE
                WHEN calificacion = 5
                THEN 1
                ELSE 0
            END
        ) AS cinco_estrellas

    FROM testimonios

    WHERE id_user = ?

    ";

    $stmt = mysqli_prepare($conexion, $sql);

    if (!$stmt) {

        throw new Exception(mysqli_error($conexion));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    $kpi = mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);


    /*=========================================
    = MEJOR PRODUCTO
    =========================================*/

    $sqlProducto = "

    SELECT

        p.nombre,

        ROUND(
            AVG(t.calificacion),
            2
        ) AS promedio

    FROM testimonios t

    INNER JOIN producto p
        ON p.idProducto = t.idProducto

    WHERE t.id_user = ?

    GROUP BY t.idProducto

    ORDER BY promedio DESC

    LIMIT 1

    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sqlProducto
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $producto = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);


    /*=========================================
    = CLIENTE MÁS ACTIVO
    =========================================*/

    $sqlCliente = "

    SELECT

        c.nombre,

        COUNT(*) AS total

    FROM testimonios t

    INNER JOIN clientes c
        ON c.idCliente = t.idCliente

    WHERE t.id_user = ?

    GROUP BY t.idCliente

    ORDER BY total DESC

    LIMIT 1

    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sqlCliente
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $cliente = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);


    /*=========================================
    = TASA DE RESPUESTA
    =========================================*/

    $total = (int)($kpi["total"] ?? 0);

    $respondidos = (int)($kpi["respondidos"] ?? 0);

    $tasaRespuesta = 0;

    if ($total > 0) {

        $tasaRespuesta = round(
            ($respondidos * 100) / $total,
            1
        );
    }


    /*=========================================
    = SENTIMIENTO POSITIVO
    =========================================*/

    $sqlSentimiento = "

    SELECT

        COUNT(*) AS total,

        SUM(
            CASE
                WHEN calificacion >= 4
                THEN 1
                ELSE 0
            END
        ) AS positivos

    FROM testimonios

    WHERE id_user = ?

    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sqlSentimiento
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $sentimiento = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    $positivo = 0;

    if ((int)$sentimiento["total"] > 0) {

        $positivo = round(
            (
                (int)$sentimiento["positivos"] * 100
            ) /
                (int)$sentimiento["total"],
            1
        );
    }


    /*=========================================
    = ÚLTIMO TESTIMONIO
    =========================================*/

    $sqlUltimo = "

    SELECT

        c.nombre AS cliente,

        p.nombre AS producto,

        t.comentario,

        t.calificacion,

        t.fecha

    FROM testimonios t

    INNER JOIN clientes c
        ON c.idCliente = t.idCliente

    INNER JOIN producto p
        ON p.idProducto = t.idProducto

    WHERE t.id_user = ?

    ORDER BY t.id_testimonio DESC

    LIMIT 1

    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sqlUltimo
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $ultimo = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);


    echo json_encode([

        "ok" => true,

        "total" => (int)($kpi["total"] ?? 0),

        "promedio" => number_format(
            (float)($kpi["promedio"] ?? 0),
            1
        ),

        "pendientes" => (int)($kpi["pendientes"] ?? 0),

        "respondidos" => $respondidos,

        "cinco_estrellas" => (int)($kpi["cinco_estrellas"] ?? 0),

        "mejor_producto" => $producto["nombre"] ?? "--",

        "top_cliente" => $cliente["nombre"] ?? "--",

        "tasa_respuesta" => $tasaRespuesta,

        "sentimiento" => $positivo,

        "ultimo" => $ultimo

    ]);
} catch (Exception $e) {

    echo json_encode([

        "ok" => false,

        "error" => $e->getMessage()

    ]);
}
