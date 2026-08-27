<?php
//=====================================================
// CoDevPro Technology
// ajax/kpi_clientes.php
//=====================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado"  => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser = (int)$_SESSION["idUser"];

try {

    /*=====================================================
    TOTAL CLIENTES
    =====================================================*/

    $sql = "
    SELECT COUNT(*) total
    FROM clientes
    WHERE id_user = ?
    AND Eliminado = 0
    ";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idUser);
    mysqli_stmt_execute($stmt);

    $totalClientes = (int) mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"];

    mysqli_stmt_close($stmt);

    /*=====================================================
    CLIENTES ACTIVOS
    =====================================================*/

    $sql = "
    SELECT COUNT(*) total
    FROM clientes
    WHERE id_user = ?
    AND estado = 'ACTIVO'
    AND Eliminado = 0
    ";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idUser);
    mysqli_stmt_execute($stmt);

    $clientesActivos = (int) mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"];

    mysqli_stmt_close($stmt);

    /*=====================================================
    CLIENTES CON PEDIDOS
    =====================================================*/

    $sql = "
    SELECT COUNT(DISTINCT tv.idCliente) total
    FROM ticket_ventas tv

    INNER JOIN clientes c
        ON c.idCliente = tv.idCliente

    WHERE tv.id_user = ?
    AND c.Eliminado = 0
    AND tv.estado_envio <> 'CANCELADO'
    ";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idUser);
    mysqli_stmt_execute($stmt);

    $clientesCompradores = (int) mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"];

    mysqli_stmt_close($stmt);

    /*=====================================================
    CLIENTE TOP
    POR MONTO TOTAL COMPRADO
    =====================================================*/

    $sql = "
    SELECT
        c.nombre,
        COUNT(tv.id_ticket_ventas) AS pedidos,
        SUM(tv.total_venta) AS total_comprado

    FROM clientes c

    INNER JOIN ticket_ventas tv
        ON tv.idCliente = c.idCliente

    WHERE c.id_user = ?
    AND c.Eliminado = 0
    AND tv.estado_envio <> 'CANCELADO'

    GROUP BY c.idCliente

    ORDER BY total_comprado DESC

    LIMIT 1
    ";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    $clienteTop = "-";
    $clienteTopMonto = 0;
    $clienteTopPedidos = 0;

    if ($fila = mysqli_fetch_assoc($resultado)) {

        $clienteTop = $fila["nombre"];

        $clienteTopMonto = (float)$fila["total_comprado"];

        $clienteTopPedidos = (int)$fila["pedidos"];
    }

    mysqli_stmt_close($stmt);

    /*=====================================================
    TOTAL VENDIDO A CLIENTES
    =====================================================*/

    $sql = "
    SELECT
        IFNULL(SUM(total_venta),0) total
    FROM ticket_ventas
    WHERE id_user = ?
    AND estado_envio <> 'CANCELADO'
    ";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $ventasClientes = (float) mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["total"];

    mysqli_stmt_close($stmt);

    /*=====================================================
    TICKET PROMEDIO
    =====================================================*/

    $sql = "
    SELECT
        IFNULL(AVG(total_venta),0) promedio
    FROM ticket_ventas
    WHERE id_user = ?
    AND estado_envio <> 'CANCELADO'
    ";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $ticketPromedio = (float) mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    )["promedio"];

    mysqli_stmt_close($stmt);

    /*=====================================================
    ÚLTIMO CLIENTE REGISTRADO
    =====================================================*/

    $sql = "
    SELECT nombre
    FROM clientes
    WHERE id_user = ?
    AND Eliminado = 0
    ORDER BY idCliente DESC
    LIMIT 1
    ";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    $ultimoCliente = "-";

    if ($fila = mysqli_fetch_assoc($resultado)) {

        $ultimoCliente = $fila["nombre"];
    }

    mysqli_stmt_close($stmt);

    /*=====================================================
    RESPUESTA
    =====================================================*/

    echo json_encode([

        "estado" => true,

        "totalClientes" => $totalClientes,

        "clientesActivos" => $clientesActivos,

        "clientesCompradores" => $clientesCompradores,

        "clienteTop" => $clienteTop,

        "clienteTopMonto" => number_format(
            $clienteTopMonto,
            2
        ),

        "clienteTopPedidos" => $clienteTopPedidos,

        "ventasClientes" => number_format(
            $ventasClientes,
            2
        ),

        "ticketPromedio" => number_format(
            $ticketPromedio,
            2
        ),

        "ultimoCliente" => $ultimoCliente

    ]);
} catch (Exception $e) {

    echo json_encode([

        "estado" => false,

        "mensaje" => $e->getMessage()

    ]);
}

mysqli_close($conexion);
