<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_kpis_estadisticas_clientes.php
//=========================================================

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

    /*=============================================
    TOTAL CLIENTES
    =============================================*/

    $sql = "SELECT COUNT(*) total
            FROM clientes
            WHERE id_user = ?
            AND Eliminado = 0";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "i", $idUser);

    mysqli_stmt_execute($stmt);

    $totalClientes = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"] ?? 0;


    /*=============================================
    ACTIVOS
    =============================================*/

    $sql = "SELECT COUNT(*) total
            FROM clientes
            WHERE id_user = ?
            AND Eliminado = 0
            AND estado='ACTIVO'";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "i", $idUser);

    mysqli_stmt_execute($stmt);

    $clientesActivos = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"] ?? 0;


    /*=============================================
    INACTIVOS
    =============================================*/

    $sql = "SELECT COUNT(*) total
            FROM clientes
            WHERE id_user = ?
            AND Eliminado = 0
            AND estado='INACTIVO'";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "i", $idUser);

    mysqli_stmt_execute($stmt);

    $clientesInactivos = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"] ?? 0;


    /*=============================================
    NUEVOS ESTE MES
    =============================================*/

    $sql = "SELECT COUNT(*) total
            FROM clientes
            WHERE id_user = ?
            AND Eliminado = 0
            AND MONTH(fecha_registro)=MONTH(CURDATE())
            AND YEAR(fecha_registro)=YEAR(CURDATE())";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "i", $idUser);

    mysqli_stmt_execute($stmt);

    $nuevosMes = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"] ?? 0;


    /*=============================================
    TICKET PROMEDIO
    =============================================*/

    $sql = "SELECT AVG(total_venta) promedio
            FROM ticket_ventas
            WHERE id_user=?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "i", $idUser);

    mysqli_stmt_execute($stmt);

    $ticketPromedio = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["promedio"] ?? 0;


    /*=============================================
    CLIENTES VIP
    (3 o más compras)
    =============================================*/

    $sql = "SELECT COUNT(*) total
            FROM (

                SELECT idCliente

                FROM ticket_ventas

                WHERE id_user=?

                GROUP BY idCliente

                HAVING COUNT(*) >= 3

            ) t";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "i", $idUser);

    mysqli_stmt_execute($stmt);

    $clientesVip = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"] ?? 0;


    /*=============================================
    VALOR PROMEDIO CLIENTE
    =============================================*/

    $sql = "SELECT AVG(total_cliente) promedio
            FROM (

                SELECT SUM(total_venta) total_cliente

                FROM ticket_ventas

                WHERE id_user=?

                GROUP BY idCliente

            ) t";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "i", $idUser);

    mysqli_stmt_execute($stmt);

    $valorCliente = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["promedio"] ?? 0;


    /*=============================================
    CONVERSION CLIENTES
    =============================================*/

    $sql = "SELECT COUNT(DISTINCT idCliente) total
            FROM ticket_ventas
            WHERE id_user=?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "i", $idUser);

    mysqli_stmt_execute($stmt);

    $clientesCompraron = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"] ?? 0;

    $conversion = 0;

    if ($totalClientes > 0) {

        $conversion = round(
            ($clientesCompraron / $totalClientes) * 100,
            1
        );
    }
    /*=============================================
GRAFICO EVOLUCION CLIENTES (12 MESES)
=============================================*/

    $labelsMeses = [];
    $dataMeses = [];

    $sqlMeses = "SELECT
                MONTH(fecha_registro) mes,
                COUNT(*) total
            FROM clientes
            WHERE id_user = ?
            AND Eliminado = 0
            AND YEAR(fecha_registro)=YEAR(CURDATE())
            GROUP BY MONTH(fecha_registro)
            ORDER BY MONTH(fecha_registro)";

    $stmtMeses = mysqli_prepare($conexion, $sqlMeses);

    mysqli_stmt_bind_param($stmtMeses, "i", $idUser);

    mysqli_stmt_execute($stmtMeses);

    $resultMeses = mysqli_stmt_get_result($stmtMeses);

    $meses = [
        1 => "Ene",
        2 => "Feb",
        3 => "Mar",
        4 => "Abr",
        5 => "May",
        6 => "Jun",
        7 => "Jul",
        8 => "Ago",
        9 => "Sep",
        10 => "Oct",
        11 => "Nov",
        12 => "Dic"
    ];

    $datosMeses = [];

    while ($row = mysqli_fetch_assoc($resultMeses)) {
        $datosMeses[(int)$row["mes"]] = (int)$row["total"];
    }

    for ($i = 1; $i <= 12; $i++) {

        $labelsMeses[] = $meses[$i];

        $dataMeses[] = $datosMeses[$i] ?? 0;
    }


    /*=============================================
SEGMENTACION CLIENTES
=============================================*/

    $segmentacion = [

        "VIP" => $clientesVip,

        "Activos" => $clientesActivos,

        "Inactivos" => $clientesInactivos

    ];

    /*=============================================
TOP 5 CLIENTES COMPRADORES
=============================================*/

    $topClientes = [];

    $sqlTop = "SELECT
                c.idCliente,
                c.nombre,
                c.email,
                COUNT(tv.id_ticket_ventas) AS pedidos,
                COALESCE(SUM(tv.total_venta),0) AS totalComprado
            FROM clientes c
            INNER JOIN ticket_ventas tv
                ON tv.idCliente = c.idCliente
                AND tv.id_user = c.id_user
            WHERE c.id_user = ?
            AND c.Eliminado = 0
            GROUP BY c.idCliente, c.nombre, c.email
            ORDER BY totalComprado DESC
            LIMIT 5";

    $stmtTop = mysqli_prepare($conexion, $sqlTop);

    mysqli_stmt_bind_param($stmtTop, "i", $idUser);

    mysqli_stmt_execute($stmtTop);

    $resultTop = mysqli_stmt_get_result($stmtTop);

    while ($row = mysqli_fetch_assoc($resultTop)) {

        $topClientes[] = [

            "idCliente"      => (int)$row["idCliente"],
            "nombre"         => $row["nombre"],
            "email"          => $row["email"],
            "pedidos"        => (int)$row["pedidos"],
            "totalComprado"  => (float)$row["totalComprado"]

        ];
    }


    /*=============================================
CLIENTES RECIENTES
=============================================*/

    $clientesRecientes = [];

    $sqlRecientes = "SELECT
                    idCliente,
                    nombre,
                    email,
                    fecha_registro
                FROM clientes
                WHERE id_user = ?
                AND Eliminado = 0
                ORDER BY fecha_registro DESC
                LIMIT 5";

    $stmtRecientes = mysqli_prepare($conexion, $sqlRecientes);

    mysqli_stmt_bind_param($stmtRecientes, "i", $idUser);

    mysqli_stmt_execute($stmtRecientes);

    $resultRecientes = mysqli_stmt_get_result($stmtRecientes);

    while ($row = mysqli_fetch_assoc($resultRecientes)) {

        $clientesRecientes[] = [

            "idCliente"      => (int)$row["idCliente"],
            "nombre"         => $row["nombre"],
            "email"          => $row["email"],
            "fecha_registro" => $row["fecha_registro"]

        ];
    }
    echo json_encode([

        "ok" => true,

        "kpis" => [

            "totalClientes"      => $totalClientes,
            "clientesActivos"    => $clientesActivos,
            "clientesInactivos"  => $clientesInactivos,
            "nuevosMes"          => $nuevosMes,
            "ticketPromedio"     => number_format($ticketPromedio, 2, '.', ''),
            "clientesVip"        => $clientesVip,
            "valorCliente"       => number_format($valorCliente, 2, '.', ''),
            "conversion"         => $conversion

        ],

        "graficoClientes" => [

            "labels" => $labelsMeses,
            "data"   => $dataMeses

        ],

        "segmentacion" => [

            "labels" => array_keys($segmentacion),
            "data"   => array_values($segmentacion)

        ],

        "topClientes" => $topClientes,

        "clientesRecientes" => $clientesRecientes

    ]);
} catch (Exception $e) {

    echo json_encode([
        "ok" => false,
        "mensaje" => $e->getMessage()
    ]);
}
