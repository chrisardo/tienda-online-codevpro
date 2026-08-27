<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_graficos_ventas.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}

/*=========================================================
=            FILTROS
=========================================================*/

$buscar       = trim($_POST["buscar"] ?? "");
$estadoVenta  = trim($_POST["estadoVenta"] ?? "");
$estadoEnvio  = trim($_POST["estadoEnvio"] ?? "");
$metodoPago   = trim($_POST["metodoPago"] ?? "");
$empleado     = trim($_POST["empleado"] ?? "");
$fechaInicio  = trim($_POST["fechaInicio"] ?? "");
$fechaFin     = trim($_POST["fechaFin"] ?? "");

/*=========================================================
=            WHERE DINÁMICO
=========================================================*/

$where = " tv.id_user = ? ";

$params = [$idUser];
$types  = "i";

if (!empty($buscar)) {

    $where .= " AND (
                    c.nombre LIKE ?
                    OR tv.serie LIKE ?
                    OR tv.numero LIKE ?
                ) ";

    $buscarLike = "%{$buscar}%";

    $params[] = $buscarLike;
    $params[] = $buscarLike;
    $params[] = $buscarLike;

    $types .= "sss";
}

if (!empty($estadoVenta)) {

    $where .= " AND tv.estado_venta = ? ";

    $params[] = $estadoVenta;
    $types .= "s";
}

if (!empty($estadoEnvio)) {

    $where .= " AND tv.estado_envio = ? ";

    $params[] = $estadoEnvio;
    $types .= "s";
}

if (!empty($metodoPago)) {

    $where .= " AND tv.id_metodo_pago = ? ";

    $params[] = $metodoPago;
    $types .= "i";
}

if (!empty($empleado)) {

    $where .= " AND tv.id_empleado = ? ";

    $params[] = $empleado;
    $types .= "i";
}

if (!empty($fechaInicio)) {

    $where .= " AND DATE(tv.fecha_venta) >= ? ";

    $params[] = $fechaInicio;
    $types .= "s";
}

if (!empty($fechaFin)) {

    $where .= " AND DATE(tv.fecha_venta) <= ? ";

    $params[] = $fechaFin;
    $types .= "s";
}

try {

    /*=========================================================
    =            GRAFICO 1: EVOLUCIÓN VENTAS
    =========================================================*/

    $labelsVentas = [];
    $dataVentas   = [];

    $sqlVentas = "
        SELECT
            DATE_FORMAT(tv.fecha_venta,'%Y-%m') AS periodo,
            SUM(tv.total_venta) AS total
        FROM ticket_ventas tv
        LEFT JOIN clientes c
            ON c.idCliente = tv.idCliente
        WHERE {$where}
        GROUP BY periodo
        ORDER BY periodo ASC
        LIMIT 12
    ";

    $stmt = mysqli_prepare($conexion, $sqlVentas);

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($fila = mysqli_fetch_assoc($result)) {

        $labelsVentas[] = $fila["periodo"];

        $dataVentas[] = (float)$fila["total"];
    }

    mysqli_stmt_close($stmt);


    /*=========================================================
    =            GRAFICO 2: MÉTODOS DE PAGO
    =========================================================*/

    $labelsMetodo = [];
    $dataMetodo   = [];

    $sqlMetodo = "
        SELECT
            COALESCE(mp.nombre,'Sin método') AS metodo,
            COUNT(*) AS total
        FROM ticket_ventas tv
        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago = tv.id_metodo_pago
        LEFT JOIN clientes c
            ON c.idCliente = tv.idCliente
        WHERE {$where}
        GROUP BY mp.nombre
        ORDER BY total DESC
    ";

    $stmt = mysqli_prepare($conexion, $sqlMetodo);

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($fila = mysqli_fetch_assoc($result)) {

        $labelsMetodo[] = $fila["metodo"];

        $dataMetodo[] = (int)$fila["total"];
    }

    mysqli_stmt_close($stmt);


    /*=========================================================
    =            GRAFICO 3: ESTADO ENVÍOS
    =========================================================*/

    $labelsEstado = [];
    $dataEstado   = [];

    $sqlEstado = "
        SELECT
            tv.estado_envio,
            COUNT(*) AS total
        FROM ticket_ventas tv
        LEFT JOIN clientes c
            ON c.idCliente = tv.idCliente
        WHERE {$where}
        GROUP BY tv.estado_envio
        ORDER BY total DESC
    ";

    $stmt = mysqli_prepare($conexion, $sqlEstado);

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($fila = mysqli_fetch_assoc($result)) {

        $labelsEstado[] = $fila["estado_envio"];

        $dataEstado[] = (int)$fila["total"];
    }

    mysqli_stmt_close($stmt);


    echo json_encode([
        "estado" => true,

        "ventas" => [
            "labels" => $labelsVentas,
            "data"   => $dataVentas
        ],

        "metodosPago" => [
            "labels" => $labelsMetodo,
            "data"   => $dataMetodo
        ],

        "estadoEnvio" => [
            "labels" => $labelsEstado,
            "data"   => $dataEstado
        ]
    ]);
} catch (Exception $e) {

    echo json_encode([
        "estado" => false,
        "mensaje" => $e->getMessage()
    ]);
}
