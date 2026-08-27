<?php
//=========================================================
// CoDevPro Technology
// Controlador: obtener_pedidos.php
//=========================================================

session_start();

require_once "conexion.php";

/*=========================================================
VALIDAR CLIENTE
=========================================================*/

$idCliente = $_SESSION["idCliente"] ?? 0;

if ($idCliente <= 0) {

    $pedidos = [];

    return;
}

/*=========================================================
FILTROS
=========================================================*/

$buscar = trim($_GET["buscar"] ?? "");

$estado = trim($_GET["estado"] ?? "");

$fecha = trim($_GET["fecha"] ?? "");

$metodo = trim($_GET["metodo"] ?? "");

$orden = trim($_GET["orden"] ?? "recientes");

/*=========================================================
WHERE DINÁMICO
=========================================================*/

$where = [];

$where[] = "tv.idCliente = " . intval($idCliente);

/*==============================
BUSCADOR
==============================*/

if (!empty($buscar)) {

    $buscar = mysqli_real_escape_string($conexion, $buscar);

    $where[] = "(
                    tv.numero LIKE '%$buscar%'
                    OR tv.serie LIKE '%$buscar%'
               )";
}

/*==============================
ESTADO
==============================*/

if (!empty($estado)) {

    $estado = mysqli_real_escape_string($conexion, $estado);

    $where[] = "tv.estado_envio = '$estado'";
}

/*==============================
MÉTODO DE PAGO
==============================*/

if (!empty($metodo)) {

    $metodo = mysqli_real_escape_string($conexion, $metodo);

    $where[] = "mp.nombre = '$metodo'";
}

/*==============================
FECHAS
==============================*/

switch ($fecha) {

    case "hoy":

        $where[] = "DATE(tv.fecha_venta)=CURDATE()";

        break;

    case "7":

        $where[] = "tv.fecha_venta>=DATE_SUB(CURDATE(),INTERVAL 7 DAY)";

        break;

    case "30":

        $where[] = "tv.fecha_venta>=DATE_SUB(CURDATE(),INTERVAL 30 DAY)";

        break;

    case "365":

        $where[] = "tv.fecha_venta>=DATE_SUB(CURDATE(),INTERVAL 365 DAY)";

        break;
}

/*=========================================================
ORDENAMIENTO
=========================================================*/

$orderBy = "tv.id_ticket_ventas DESC";

switch ($orden) {

    case "antiguos":

        $orderBy = "tv.id_ticket_ventas ASC";

        break;

    case "mayor":

        $orderBy = "tv.total_venta DESC";

        break;

    case "menor":

        $orderBy = "tv.total_venta ASC";

        break;
}

/*=========================================================
CONSULTA PRINCIPAL
=========================================================*/

$sql = "

SELECT

    tv.id_ticket_ventas,

    tv.serie,

    tv.numero,

    tv.total_venta,

    tv.fecha_venta,

    tv.hora_venta,

    tv.estado_envio,

    tv.tipo_comprobante,

    tv.pago_cliente,

    tv.vuelto_venta,

    c.nombre AS cliente,

    c.direccion,

    mp.nombre AS metodo_pago

FROM ticket_ventas tv

INNER JOIN clientes c

ON c.idCliente = tv.idCliente

LEFT JOIN metodo_pago mp

ON mp.id_metodo_pago = tv.id_metodo_pago

WHERE

" . implode(" AND ", $where) . "

ORDER BY

$orderBy

";

$resultado = mysqli_query($conexion, $sql);

/*=========================================================
ARRAY PRINCIPAL
=========================================================*/

$pedidos = [];

if (!$resultado) {

    return;
}