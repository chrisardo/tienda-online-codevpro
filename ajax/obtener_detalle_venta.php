<?php
//=========================================================
// CoDevPro Technology
// Archivo: ajax/obtener_detalle_venta.php
// Módulo: Gestión de Ventas
// Sistema: Inventa
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

//=========================================================
// SESIÓN
//=========================================================

$idUser = (int)($_SESSION["idUser"] ?? 0);
$idVenta = (int)($_POST["idVenta"] ?? 0);

if ($idUser <= 0 || $idVenta <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Datos inválidos"
    ]);

    exit;
}

/*=========================================================
=            OBTENER CABECERA DE LA VENTA
=========================================================*/

$sql = "
SELECT

    tv.*,

    /*=====================================================
    CLIENTE
    =====================================================*/

    c.nombre AS cliente_nombre,
    c.email AS cliente_email,
    c.celular AS cliente_celular,
    c.dni_o_ruc AS cliente_dni_o_ruc,
    c.direccion AS cliente_direccion,

    /*=====================================================
    UBICACIÓN CLIENTE
    =====================================================*/

    dep.nombre AS departamento,
    pro.nombre AS provincia,
    dis.nombre AS distrito,

    /*=====================================================
    MÉTODO DE PAGO
    =====================================================*/

    mp.nombre AS metodo_pago,

    /*=====================================================
    EMPLEADO / REPARTIDOR ASIGNADO
    =====================================================*/

    e.id_empleado AS repartidor_id,

    e.nombre AS repartidor_nombre,

    e.apellido AS repartidor_apellido,

    e.celular AS repartidor_celular,

    e.email AS repartidor_email,

    e.dni AS repartidor_dni,

    CONCAT(
        COALESCE(e.nombre,''),
        ' ',
        COALESCE(e.apellido,'')
    ) AS empleado,

    /*=====================================================
    EMPRESA
    =====================================================*/

    ua.nombreEmpresa

FROM ticket_ventas tv

/*=========================================================
CLIENTE
=========================================================*/

LEFT JOIN clientes c
    ON c.idCliente = tv.idCliente

/*=========================================================
UBICACIÓN
=========================================================*/

LEFT JOIN departamento dep
    ON dep.id_departamento = c.id_departamento

LEFT JOIN provincia pro
    ON pro.id_provincia = c.id_provincia

LEFT JOIN distrito dis
    ON dis.id_distrito = c.id_distrito

/*=========================================================
MÉTODO DE PAGO
=========================================================*/

LEFT JOIN metodo_pago mp
    ON mp.id_metodo_pago = tv.id_metodo_pago

/*=========================================================
EMPLEADO / REPARTIDOR
=========================================================*/

LEFT JOIN empleados e
    ON e.id_empleado = tv.id_empleado
    AND tv.id_empleado > 0

/*=========================================================
EMPRESA
=========================================================*/

LEFT JOIN usuario_acceso ua
    ON ua.id_user = tv.id_user

WHERE tv.id_ticket_ventas = ?

AND tv.id_user = ?

LIMIT 1
";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error preparando consulta de venta."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idVenta,
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$venta = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

if (!$venta) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Venta no encontrada."
    ]);

    exit;
}

/*=========================================================
=            PRODUCTOS DE LA VENTA
=========================================================*/

$sqlProductos = "
SELECT

    dt.id_detalle_ticket,
    dt.idProducto,
    dt.cantidad_pedido_producto,
    dt.sub_total,

    p.codigo,
    p.nombre,
    p.precio,
    p.stock,
    p.destacado,
    p.nuevo,

    (
        SELECT COUNT(*)
        FROM favoritos f
        WHERE f.idProducto = p.idProducto
    ) AS favoritos,

    COALESCE(
        cpv.cantidad_total,
        0
    ) AS cantidad_vendida,

    (
        SELECT i.id_imagen
        FROM imagenes i
        WHERE i.idProducto = p.idProducto
        ORDER BY i.orden ASC, i.id_imagen ASC
        LIMIT 1
    ) AS imagen_principal

FROM detalle_ticket_ventas dt

INNER JOIN producto p
    ON p.idProducto = dt.idProducto

LEFT JOIN cantidad_producto_vendido cpv
    ON cpv.idProducto = p.idProducto
    AND cpv.id_user = p.id_user

WHERE dt.id_ticket_ventas = ?

ORDER BY p.nombre ASC
";

$stmt = mysqli_prepare($conexion, $sqlProductos);

if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error preparando productos."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idVenta
);

mysqli_stmt_execute($stmt);

$resultadoProductos = mysqli_stmt_get_result($stmt);

$productos = [];

while ($fila = mysqli_fetch_assoc($resultadoProductos)) {

    $fila["precio"] =
        (float)$fila["precio"];

    $fila["sub_total"] =
        (float)$fila["sub_total"];

    $fila["cantidad_vendida"] =
        (int)$fila["cantidad_vendida"];

    $fila["favoritos"] =
        (int)$fila["favoritos"];

    $fila["cantidad_pedido_producto"] =
        (int)$fila["cantidad_pedido_producto"];

    if (!empty($fila["imagen_principal"])) {

        $fila["imagen"] =
            "mostrar_imagen.php?id=" .
            $fila["imagen_principal"];
    } else {

        $fila["imagen"] =
            "assets/img/sin_imagen.png";
    }

    $productos[] = $fila;
}

mysqli_stmt_close($stmt);

/*=========================================================
=            CANTIDAD TOTAL DE PRODUCTOS
=========================================================*/

$cantidadProductos = 0;

foreach ($productos as $producto) {

    $cantidadProductos +=
        (int)$producto["cantidad_pedido_producto"];
}

/*=========================================================
=            DETERMINAR SI TIENE REPARTIDOR
=========================================================*/

$tieneRepartidor =
    !empty($venta["repartidor_id"]) &&
    (int)$venta["repartidor_id"] > 0;

/*=========================================================
=            DATOS DEL REPARTIDOR
=========================================================*/

$repartidor = null;

if ($tieneRepartidor) {

    $repartidor = [

        "id" =>
        (int)$venta["repartidor_id"],

        "nombre" =>
        trim(
            ($venta["repartidor_nombre"] ?? "") .
                " " .
                ($venta["repartidor_apellido"] ?? "")
        ),

        "nombre_solo" =>
        $venta["repartidor_nombre"] ?? "",

        "apellido" =>
        $venta["repartidor_apellido"] ?? "",

        "celular" =>
        $venta["repartidor_celular"] ?? "",

        "email" =>
        $venta["repartidor_email"] ?? "",

        "dni" =>
        $venta["repartidor_dni"] ?? ""
    ];
}

/*=========================================================
=            KPI RESUMEN
=========================================================*/

$resumen = [

    "cantidadProductos" =>
    $cantidadProductos,

    "totalProductos" =>
    count($productos),

    "totalVenta" =>
    (float)$venta["total_venta"],

    "pagoCliente" =>
    (float)$venta["pago_cliente"],

    "vuelto" =>
    (float)$venta["vuelto_venta"],

    "estadoVenta" =>
    $venta["estado_venta"],

    "estadoEnvio" =>
    $venta["estado_envio"]

];

/*=========================================================
=            RESPUESTA JSON
=========================================================*/

echo json_encode([

    "estado" => true,

    "venta" => [

        "id_ticket_ventas" =>
        (int)$venta["id_ticket_ventas"],

        /*=================================================
        CLIENTE
        =================================================*/

        "cliente" =>
        $venta["cliente_nombre"],

        "email" =>
        $venta["cliente_email"],

        "celular" =>
        $venta["cliente_celular"],

        "dni_o_ruc" =>
        $venta["cliente_dni_o_ruc"],

        "direccion_cliente" =>
        $venta["cliente_direccion"],

        /*=================================================
        DIRECCIÓN ENVÍO
        =================================================*/

        "direccion_envio" =>
        $venta["direccion_envio"],

        "departamento" =>
        $venta["departamento"],

        "provincia" =>
        $venta["provincia"],

        "distrito" =>
        $venta["distrito"],

        /*=================================================
        PAGO
        =================================================*/

        "metodo_pago" =>
        $venta["metodo_pago"],

        /*=================================================
        EMPLEADO
        =================================================*/

        "empleado" =>
        trim($venta["empleado"]),

        /*=================================================
        REPARTIDOR
        =================================================*/

        "tiene_repartidor" =>
        $tieneRepartidor,

        "repartidor" =>
        $repartidor,

        /*=================================================
        EMPRESA
        =================================================*/

        "empresa" =>
        $venta["nombreEmpresa"],

        /*=================================================
        COMPROBANTE
        =================================================*/

        "tipo_comprobante" =>
        $venta["tipo_comprobante"],

        "serie" =>
        $venta["serie"],

        "numero" =>
        $venta["numero"],

        /*=================================================
        FECHAS
        =================================================*/

        "fecha_venta" =>
        $venta["fecha_venta"],

        "hora_venta" =>
        $venta["hora_venta"],

        "fecha_confirmado" =>
        $venta["fecha_confirmado"],

        "fecha_preparando" =>
        $venta["fecha_preparando"],

        "fecha_enviado" =>
        $venta["fecha_enviado"],

        "fecha_entregado" =>
        $venta["fecha_entregado"],

        "fecha_cancelado" =>
        $venta["fecha_cancelado"],

        /*=================================================
        ESTADOS
        =================================================*/

        "estado_venta" =>
        $venta["estado_venta"],

        "estado_envio" =>
        $venta["estado_envio"],

        /*=================================================
        FINANCIERO
        =================================================*/

        "total_venta" =>
        (float)$venta["total_venta"],

        "pago_cliente" =>
        (float)$venta["pago_cliente"],

        "vuelto_venta" =>
        (float)$venta["vuelto_venta"],

        "aplica_igv" =>
        (int)$venta["aplica_igv"],

        /*=================================================
        OBSERVACIÓN
        =================================================*/

        "observacion_envio" =>
        $venta["observacion_envio"]

    ],

    "productos" =>
    $productos,

    "resumen" =>
    $resumen

], JSON_UNESCAPED_UNICODE);
