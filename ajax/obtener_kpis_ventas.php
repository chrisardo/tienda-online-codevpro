<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_kpis_ventas.php
// Módulo: Gestión de Ventas
// Sistema: Inventa
//=========================================================

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once "../controladores/conexion.php";


$idUser =
    $_SESSION["idUser"] ?? 0;


if (!$idUser) {

    echo json_encode([

        "estado" => false,

        "mensaje" =>
        "Sesión no válida"

    ]);

    exit;
}


/*=========================================================
=            FILTROS
=========================================================*/

$buscar =
    trim($_POST["buscar"] ?? "");


$estadoVenta =
    trim($_POST["estadoVenta"] ?? "");


$estadoEnvio =
    trim($_POST["estadoEnvio"] ?? "");


$metodoPago =
    trim($_POST["metodoPago"] ?? "");


$empleado =
    trim($_POST["empleado"] ?? "");


$fechaInicio =
    trim($_POST["fechaInicio"] ?? "");


$fechaFin =
    trim($_POST["fechaFin"] ?? "");


/*=========================================================
=            WHERE BASE
=========================================================*/

$where = "

    tv.id_user = ?

";


$params = [
    $idUser
];


$types = "i";


/*=========================================================
=            BUSCADOR
=========================================================*/

if ($buscar !== "") {

    $where .= "

        AND (

            c.nombre LIKE ?

            OR CONCAT(
                COALESCE(c.nombre,''),
                ' ',
                COALESCE(c.apellido,'')
            ) LIKE ?

            OR CONCAT(
                tv.serie,
                '-',
                tv.numero
            ) LIKE ?

            OR tv.numero LIKE ?

        )

    ";


    $buscarLike =
        "%" . $buscar . "%";


    $params[] =
        $buscarLike;

    $params[] =
        $buscarLike;

    $params[] =
        $buscarLike;

    $params[] =
        $buscarLike;


    $types .= "ssss";
}


/*=========================================================
=            ESTADO VENTA
=========================================================*/

if ($estadoVenta !== "") {

    $where .= "

        AND tv.estado_venta = ?

    ";


    $params[] =
        $estadoVenta;


    $types .= "s";
}


/*=========================================================
=            ESTADO ENVÍO
=========================================================*/

if ($estadoEnvio !== "") {

    $where .= "

        AND tv.estado_envio = ?

    ";


    $params[] =
        $estadoEnvio;


    $types .= "s";
}


/*=========================================================
=            MÉTODO PAGO
=========================================================*/

if ($metodoPago !== "") {

    $where .= "

        AND tv.id_metodo_pago = ?

    ";


    $params[] =
        (int)$metodoPago;


    $types .= "i";
}


/*=========================================================
=            EMPLEADO
=========================================================*/

if ($empleado !== "") {

    $where .= "

        AND tv.id_empleado = ?

    ";


    $params[] =
        (int)$empleado;


    $types .= "i";
}


/*=========================================================
=            FECHA INICIO
=========================================================*/

if ($fechaInicio !== "") {

    $where .= "

        AND tv.fecha_venta >= ?

    ";


    $params[] =
        $fechaInicio . " 00:00:00";


    $types .= "s";
}


/*=========================================================
=            FECHA FIN
=========================================================*/

if ($fechaFin !== "") {

    $where .= "

        AND tv.fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)

    ";


    $params[] =
        $fechaFin;


    $types .= "s";
}


try {


    /*=====================================================
    =            CONSULTA GENERAL
    =====================================================*/

    $sql = "

        SELECT

            COUNT(*) AS totalVentas,

            COALESCE(
                SUM(
                    CASE
                        WHEN DATE(tv.fecha_venta)
                            = CURDATE()
                        THEN tv.total_venta
                        ELSE 0
                    END
                ),
                0
            ) AS ventasHoy,

            COALESCE(
                SUM(
                    CASE
                        WHEN MONTH(tv.fecha_venta)
                            = MONTH(CURDATE())
                        AND YEAR(tv.fecha_venta)
                            = YEAR(CURDATE())
                        THEN tv.total_venta
                        ELSE 0
                    END
                ),
                0
            ) AS ventasMes,

            COALESCE(
                AVG(tv.total_venta),
                0
            ) AS ticketPromedio,

            SUM(
                CASE
                    WHEN tv.estado_envio =
                        'PENDIENTE'
                    THEN 1
                    ELSE 0
                END
            ) AS pendientes,

            SUM(
                CASE
                    WHEN tv.idCliente IS NOT NULL
                    THEN 1
                    ELSE 0
                END
            ) AS ventasOnline,

            SUM(
                CASE
                    WHEN tv.estado_envio =
                        'ENTREGADO'
                    THEN 1
                    ELSE 0
                END
            ) AS entregados,

            SUM(
                CASE
                    WHEN tv.estado_envio =
                        'CANCELADO'
                    THEN 1
                    ELSE 0
                END
            ) AS cancelados

        FROM ticket_ventas tv

        LEFT JOIN clientes c
            ON c.idCliente =
                tv.idCliente

        WHERE {$where}

    ";


    $stmt =
        mysqli_prepare(
            $conexion,
            $sql
        );


    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );


    mysqli_stmt_execute(
        $stmt
    );


    $resultado =
        mysqli_stmt_get_result(
            $stmt
        );


    $fila =
        mysqli_fetch_assoc(
            $resultado
        );


    mysqli_stmt_close(
        $stmt
    );


    echo json_encode([

        "estado" => true,

        "ventasHoy" =>
        number_format(
            (float)(
                $fila["ventasHoy"] ?? 0
            ),
            2,
            ".",
            ""
        ),

        "ventasMes" =>
        number_format(
            (float)(
                $fila["ventasMes"] ?? 0
            ),
            2,
            ".",
            ""
        ),

        "ticketPromedio" =>
        number_format(
            (float)(
                $fila["ticketPromedio"] ?? 0
            ),
            2,
            ".",
            ""
        ),

        "pendientes" =>
        (int)(
            $fila["pendientes"] ?? 0
        ),

        "totalVentas" =>
        (int)(
            $fila["totalVentas"] ?? 0
        ),

        "ventasOnline" =>
        (int)(
            $fila["ventasOnline"] ?? 0
        ),

        "entregados" =>
        (int)(
            $fila["entregados"] ?? 0
        ),

        "cancelados" =>
        (int)(
            $fila["cancelados"] ?? 0
        )

    ]);
} catch (Throwable $e) {

    echo json_encode([

        "estado" => false,

        "mensaje" =>
        $e->getMessage()

    ]);
}
