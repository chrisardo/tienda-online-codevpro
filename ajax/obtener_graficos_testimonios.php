<?php
//=====================================================
// CoDevPro Technology
// ajax/obtener_graficos_testimonios.php
//=====================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Sesión inválida"
    ]);

    exit;
}

try {

    /*=========================================
    = ESTRELLAS
    =========================================*/

    $estrellas = [0, 0, 0, 0, 0];

    $sql = "

    SELECT
        calificacion,
        COUNT(*) total

    FROM testimonios

    WHERE id_user = ?

    GROUP BY calificacion

    ";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($res)) {

        $indice = (int)$row["calificacion"] - 1;

        if ($indice >= 0 && $indice <= 4) {

            $estrellas[$indice] = (int)$row["total"];
        }
    }

    mysqli_stmt_close($stmt);



    /*=========================================
    = TESTIMONIOS POR MES
    =========================================*/

    $meses = [
        "Ene",
        "Feb",
        "Mar",
        "Abr",
        "May",
        "Jun",
        "Jul",
        "Ago",
        "Sep",
        "Oct",
        "Nov",
        "Dic"
    ];

    $cantidadMes = array_fill(0, 12, 0);

    $sql = "

    SELECT

        MONTH(fecha) mes,
        COUNT(*) total

    FROM testimonios

    WHERE id_user = ?
    AND YEAR(fecha)=YEAR(CURDATE())

    GROUP BY MONTH(fecha)

    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($res)) {

        $cantidadMes[(int)$row["mes"] - 1] = (int)$row["total"];
    }

    mysqli_stmt_close($stmt);



    /*=========================================
    = PRODUCTOS MEJOR VALORADOS
    =========================================*/

    $labelsProductos = [];
    $promediosProductos = [];

    $sql = "

    SELECT

        p.nombre,

        ROUND(
            AVG(t.calificacion),
            2
        ) promedio

    FROM testimonios t

    INNER JOIN producto p
        ON p.idProducto = t.idProducto

    WHERE t.id_user = ?

    GROUP BY t.idProducto

    HAVING promedio > 0

    ORDER BY promedio DESC

    LIMIT 10

    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($res)) {

        $labelsProductos[] = $row["nombre"];

        $promediosProductos[] =
            (float)$row["promedio"];
    }

    mysqli_stmt_close($stmt);



    echo json_encode([

        "ok" => true,

        "estrellas" => $estrellas,

        "meses" => $meses,

        "testimonios_mes" => $cantidadMes,

        "productos" => $labelsProductos,

        "promedios" => $promediosProductos

    ]);
} catch (Exception $e) {

    echo json_encode([

        "ok" => false,

        "error" => $e->getMessage()

    ]);
}
