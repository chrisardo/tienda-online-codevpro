<?php
//======================================================
// CoDevPro Technology
// Archivo: controladores/obtener_detalle_pedido_cliente.php
// Módulo: Mis Pedidos
// Sistema: Inventa
//
// Obtener detalle completo de pedido del cliente
// incluyendo información del repartidor asignado.
//
// COMPATIBILIDAD:
// - Pedidos nuevos  -> ticket_ventas.id_repartidor
// - Pedidos antiguos -> ticket_ventas.id_empleado
//
// PRIORIDAD:
// 1. id_repartidor
// 2. id_empleado
//======================================================


//======================================================
// INICIAR SESIÓN
//======================================================

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}


//======================================================
// CONEXIÓN
//======================================================

require_once "conexion.php";


//======================================================
// VALIDAR CLIENTE
//======================================================

$idCliente = (int)(
    $_SESSION["idCliente"] ?? 0
);


if ($idCliente <= 0) {

    die("Cliente no válido");
}


//======================================================
// VALIDAR PEDIDO
//======================================================

$idTicket = (int)(
    $_SESSION["pedido_detalle"] ?? 0
);


if ($idTicket <= 0) {

    die("Pedido inválido");
}


//======================================================
// OBTENER INFORMACIÓN DEL PEDIDO
//======================================================
//
// Para obtener el repartidor se utiliza:
//
// ticket_ventas.id_repartidor
//          ↓
//      SI ES > 0
//          ↓
//      empleados.id_empleado
//
// Si no existe:
//
// ticket_ventas.id_empleado
//          ↓
//      empleados.id_empleado
//
// De esta manera funcionan tanto los pedidos
// nuevos como los pedidos registrados anteriormente.
//======================================================

$sqlPedido = "

SELECT

    /*--------------------------------------------------
    DATOS DEL PEDIDO
    --------------------------------------------------*/

    tv.id_ticket_ventas,

    tv.id_user,

    tv.idCliente,

    tv.direccion_envio,

    tv.pago_cliente,

    tv.total_venta,

    tv.id_metodo_pago,

    tv.estado_venta,

    tv.fecha_venta,

    tv.hora_venta,

    tv.vuelto_venta,

    tv.id_empleado,

    tv.id_repartidor,

    tv.tipo_comprobante,

    tv.serie,

    tv.numero,

    tv.aplica_igv,

    tv.estado_envio,

    tv.fecha_confirmado,

    tv.fecha_preparando,

    tv.fecha_asignado,

    tv.fecha_obtenido,

    tv.fecha_enviado,

    tv.fecha_entregado,

    tv.observacion_envio,

    tv.fecha_cancelado,


    /*--------------------------------------------------
    ID EFECTIVO DEL REPARTIDOR
    --------------------------------------------------

    Prioridad:

    1. id_repartidor
    2. id_empleado

    NULLIF convierte 0 en NULL para que COALESCE
    pueda pasar al siguiente valor.
    --------------------------------------------------*/

    COALESCE(
        NULLIF(tv.id_repartidor, 0),
        NULLIF(tv.id_empleado, 0)
    ) AS id_repartidor_efectivo,


    /*--------------------------------------------------
    DATOS DEL CLIENTE
    --------------------------------------------------*/

    c.nombre AS cliente,

    c.dni_o_ruc,

    c.email,

    c.celular,

    c.direccion,


    /*--------------------------------------------------
    MÉTODO DE PAGO
    --------------------------------------------------*/

    mp.nombre AS metodo_pago,


    /*--------------------------------------------------
    DATOS DEL REPARTIDOR
    --------------------------------------------------*/

    e.id_empleado AS repartidor_id,

    e.nombre AS repartidor_nombre,

    e.apellido AS repartidor_apellido,

    e.celular AS repartidor_celular,

    e.dni AS repartidor_dni,

    e.email AS repartidor_email,

    e.direccion AS repartidor_direccion,

    e.estado AS repartidor_estado,

    e.imagen AS repartidor_imagen,


    /*--------------------------------------------------
    ROL DEL REPARTIDOR
    --------------------------------------------------*/

    r.id_rol AS repartidor_id_rol,

    r.nombre AS repartidor_rol


FROM ticket_ventas tv


/*======================================================
CLIENTE
======================================================*/

INNER JOIN clientes c

    ON tv.idCliente = c.idCliente


/*======================================================
MÉTODO DE PAGO
======================================================*/

LEFT JOIN metodo_pago mp

    ON tv.id_metodo_pago = mp.id_metodo_pago


/*======================================================
REPARTIDOR
======================================================

IMPORTANTE:

Se busca primero por id_repartidor.

Si id_repartidor es NULL o 0,
se utiliza id_empleado.

======================================================*/

LEFT JOIN empleados e

    ON e.id_empleado = COALESCE(
        NULLIF(tv.id_repartidor, 0),
        NULLIF(tv.id_empleado, 0)
    )


/*======================================================
ROL DEL REPARTIDOR
======================================================*/

LEFT JOIN rol r

    ON e.id_rol = r.id_rol


/*======================================================
VALIDACIÓN DEL PEDIDO
======================================================*/

WHERE

    tv.id_ticket_ventas = ?

    AND

    tv.idCliente = ?


LIMIT 1

";


//======================================================
// PREPARAR CONSULTA
//======================================================

$stmt = mysqli_prepare(
    $conexion,
    $sqlPedido
);


if (!$stmt) {

    die(
        "Error SQL pedido: "
        . mysqli_error($conexion)
    );
}


//======================================================
// PARÁMETROS
//======================================================

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idTicket,
    $idCliente
);


//======================================================
// EJECUTAR
//======================================================

if (!mysqli_stmt_execute($stmt)) {

    die(
        "Error al ejecutar consulta del pedido: "
        . mysqli_stmt_error($stmt)
    );
}


//======================================================
// OBTENER RESULTADO
//======================================================

$resultadoPedido = mysqli_stmt_get_result(
    $stmt
);


$pedido = mysqli_fetch_assoc(
    $resultadoPedido
);


mysqli_stmt_close($stmt);


//======================================================
// VALIDAR PEDIDO
//======================================================

if (!$pedido) {

    die(
        "El pedido no existe o no pertenece al cliente"
    );
}


//======================================================
// NORMALIZAR ID DEL REPARTIDOR
//======================================================
//
// Se utiliza el ID efectivo:
//
// 1. id_repartidor
// 2. id_empleado
//
// Esto permite que el PHP de la vista funcione
// independientemente de cómo se haya registrado
// originalmente el pedido.
//======================================================

$pedido["id_repartidor"] =
    (int)(
        $pedido["id_repartidor_efectivo"] ?? 0
    );


$pedido["repartidor_id"] =
    (int)(
        $pedido["repartidor_id"] ?? 0
    );


//======================================================
// NORMALIZAR NOMBRE
//======================================================

$pedido["repartidor_nombre"] =
    trim(
        (string)(
            $pedido["repartidor_nombre"] ?? ""
        )
    );


//======================================================
// NORMALIZAR APELLIDO
//======================================================

$pedido["repartidor_apellido"] =
    trim(
        (string)(
            $pedido["repartidor_apellido"] ?? ""
        )
    );


//======================================================
// NORMALIZAR CELULAR
//======================================================

$pedido["repartidor_celular"] =
    trim(
        (string)(
            $pedido["repartidor_celular"] ?? ""
        )
    );


//======================================================
// NORMALIZAR ROL
//======================================================

$pedido["repartidor_rol"] =
    trim(
        (string)(
            $pedido["repartidor_rol"] ?? ""
        )
    );


//======================================================
// NORMALIZAR ESTADO
//======================================================

$pedido["repartidor_estado"] =
    strtoupper(
        trim(
            (string)(
                $pedido["repartidor_estado"] ?? ""
            )
        )
    );


//======================================================
// NORMALIZAR IMAGEN
//======================================================

$pedido["repartidor_imagen"] =
    $pedido["repartidor_imagen"]
    ?? null;


//======================================================
// PRODUCTOS DEL PEDIDO
//======================================================

$sqlProductos = "

SELECT

    p.idProducto,

    p.codigo,

    p.nombre,

    p.precio,

    i.imagenes,

    d.cantidad_pedido_producto AS cantidad,

    d.aplica_impuesto,

    d.porcentaje_impuesto,

    d.monto_impuesto,

    d.sub_total


FROM detalle_ticket_ventas d


INNER JOIN producto p

    ON d.idProducto = p.idProducto


LEFT JOIN imagenes i

    ON p.idProducto = i.idProducto


WHERE

    d.id_ticket_ventas = ?


GROUP BY

    d.id_detalle_ticket


ORDER BY

    d.id_detalle_ticket ASC

";


//======================================================
// PREPARAR CONSULTA PRODUCTOS
//======================================================

$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);


if (!$stmtProductos) {

    die(
        "Error SQL productos: "
        . mysqli_error($conexion)
    );
}


//======================================================
// PARÁMETRO
//======================================================

mysqli_stmt_bind_param(
    $stmtProductos,
    "i",
    $idTicket
);


//======================================================
// EJECUTAR
//======================================================

if (!mysqli_stmt_execute($stmtProductos)) {

    die(
        "Error al obtener productos: "
        . mysqli_stmt_error($stmtProductos)
    );
}


//======================================================
// RESULTADO
//======================================================

$resultadoProductos = mysqli_stmt_get_result(
    $stmtProductos
);


//======================================================
// ARRAY PRODUCTOS
//======================================================

$productos = [];


while (
    $producto = mysqli_fetch_assoc(
        $resultadoProductos
    )
) {

    $productos[] = $producto;
}


mysqli_stmt_close($stmtProductos);


//======================================================
// ESTADO DEL PEDIDO
//======================================================

$estadoPedido = [

    "estado" =>
        $pedido["estado_envio"]
        ?? "PENDIENTE",

    "confirmado" =>
        $pedido["fecha_confirmado"]
        ?? null,

    "preparando" =>
        $pedido["fecha_preparando"]
        ?? null,

    "asignado" =>
        $pedido["fecha_asignado"]
        ?? null,

    "obtenido" =>
        $pedido["fecha_obtenido"]
        ?? null,

    "enviado" =>
        $pedido["fecha_enviado"]
        ?? null,

    "entregado" =>
        $pedido["fecha_entregado"]
        ?? null,

    "cancelado" =>
        $pedido["fecha_cancelado"]
        ?? null,

    "observacion" =>
        $pedido["observacion_envio"]
        ?? ""

];


//======================================================
// FIN
//======================================================