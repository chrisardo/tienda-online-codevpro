<?php
//========================================================
// CoDevPro Technology
// ajax/obtener_kpi_comprobantes.php
//========================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

/*=========================================================
VALIDAR SESIÓN
=========================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);

/*=========================================================
RECIBIR FILTROS
=========================================================*/

$input = json_decode(file_get_contents("php://input"), true);

$buscar         = trim($input["buscar"] ?? "");
$rangoFecha     = trim($input["rangoFecha"] ?? "");
$tipo           = trim($input["tipo"] ?? "");
$estado         = trim($input["estado"] ?? "");
$metodoPago     = trim($input["metodoPago"] ?? "");
$empleado       = trim($input["empleado"] ?? "");
$cliente        = trim($input["cliente"] ?? "");
$montoMin       = trim($input["montoMin"] ?? "");
$montoMax       = trim($input["montoMax"] ?? "");
$soloIGV        = intval($input["soloIGV"] ?? 0);
$soloAnulados   = intval($input["soloAnulados"] ?? 0);
$fechaInicio = trim($input["fechaInicio"] ?? "");
$fechaFin    = trim($input["fechaFin"] ?? "");
/*=========================================================
WHERE DINÁMICO
=========================================================*/

$where = [];
$where[] = "tv.id_user=?";

$types = "i";
$params = [];
$params[] = &$idUser;

/*=========================================================
BUSCADOR
=========================================================*/

if ($buscar != "") {

    $where[] = "(
        CONCAT(tv.serie,'-',LPAD(tv.numero,8,'0')) LIKE ?
        OR c.nombre LIKE ?
        OR c.dni_o_ruc LIKE ?
        OR CONCAT(e.nombre,' ',e.apellido) LIKE ?
    )";

    $buscarLike = "%{$buscar}%";

    $types .= "ssss";

    $params[] = &$buscarLike;
    $params[] = &$buscarLike;
    $params[] = &$buscarLike;
    $params[] = &$buscarLike;
}

/*=========================================================
RANGO FECHAS
=========================================================*/

if ($fechaInicio != "" && $fechaFin != "") {

    $where[] = "tv.fecha_venta BETWEEN ? AND ?";

    $types .= "ss";

    $params[] = &$fechaInicio;

    $params[] = &$fechaFin;
}

/*=========================================================
TIPO
=========================================================*/

if ($tipo != "") {

    $where[] = "tv.tipo_comprobante=?";

    $types .= "s";

    $params[] = &$tipo;
}

/*=========================================================
ESTADO
=========================================================*/

if ($estado != "") {

    $where[] = "tv.estado_venta=?";

    $types .= "s";

    $params[] = &$estado;
}

/*=========================================================
MÉTODO
=========================================================*/

if ($metodoPago != "") {

    $where[] = "tv.id_metodo_pago=?";

    $types .= "i";

    $params[] = &$metodoPago;
}

/*=========================================================
EMPLEADO
=========================================================*/

if ($empleado != "") {

    $where[] = "tv.id_empleado=?";

    $types .= "i";

    $params[] = &$empleado;
}

/*=========================================================
CLIENTE
=========================================================*/

if ($cliente != "") {

    $clienteLike = "%{$cliente}%";

    $where[] = "c.nombre LIKE ?";

    $types .= "s";

    $params[] = &$clienteLike;
}

/*=========================================================
MONTO
=========================================================*/

if ($montoMin != "") {

    $where[] = "tv.total_venta>=?";

    $types .= "d";

    $params[] = &$montoMin;
}

if ($montoMax != "") {

    $where[] = "tv.total_venta<=?";

    $types .= "d";

    $params[] = &$montoMax;
}

/*=========================================================
SOLO IGV
=========================================================*/

if ($soloIGV == 1) {

    $where[] = "tv.aplica_igv=1";
}

/*=========================================================
SOLO ANULADOS
=========================================================*/

if ($soloAnulados == 1) {

    $where[] = "UPPER(tv.estado_venta) IN('ANULADO','CANCELADO')";
}

/*=========================================================
CONSULTA
=========================================================*/

$sql = "

SELECT

COUNT(*) total_comprobantes,

SUM(
CASE
WHEN UPPER(tv.tipo_comprobante)='BOLETA'
THEN 1
ELSE 0
END
) boletas,

SUM(
CASE
WHEN UPPER(tv.tipo_comprobante)='FACTURA'
THEN 1
ELSE 0
END
) facturas,

SUM(
CASE
WHEN UPPER(tv.tipo_comprobante) IN('NOTA VENTA','NOTA DE VENTA')
THEN 1
ELSE 0
END
) notas,

SUM(
CASE
WHEN UPPER(tv.estado_venta) IN('ANULADO','CANCELADO')
THEN 1
ELSE 0
END
) anulados,

SUM(
CASE
WHEN UPPER(tv.estado_venta) NOT IN('ANULADO','CANCELADO')
THEN tv.total_venta
ELSE 0
END
) monto_total

FROM ticket_ventas tv

LEFT JOIN clientes c
ON tv.idCliente=c.idCliente

LEFT JOIN empleados e
ON tv.id_empleado=e.id_empleado

LEFT JOIN metodo_pago mp
ON tv.id_metodo_pago=mp.id_metodo_pago

WHERE

" . implode(" AND ", $where);

$stmt = mysqli_prepare($conexion, $sql);

call_user_func_array(
    [$stmt, "bind_param"],
    array_merge([$types], $params)
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($resultado);

echo json_encode([

    "estado" => true,

    "kpi" => [

        "total" => intval($data["total_comprobantes"]),

        "boletas" => intval($data["boletas"]),

        "facturas" => intval($data["facturas"]),

        "notas" => intval($data["notas"]),

        "anulados" => intval($data["anulados"]),

        "monto" => number_format((float)$data["monto_total"], 2, ".", "")

    ]

]);

mysqli_stmt_close($stmt);

mysqli_close($conexion);
