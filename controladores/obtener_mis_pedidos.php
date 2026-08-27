<?php
//=========================================================
// CoDevPro Technology
// controladores/obtener_mis_pedidos.php
// Módulo: Mis Pedidos
// Sistema: Inventa
//=========================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "conexion.php";


/*=========================================================
VALIDAR CLIENTE
=========================================================*/

$idCliente = $_SESSION["idCliente"] ?? 0;

if ($idCliente <= 0) {

    $resultadoPedidos = false;
    $totalPedidos = 0;
    $pagina = 1;
    $totalPaginas = 0;

    return;
}


/*=========================================================
FILTROS
=========================================================*/

$buscar = trim(
    $_GET["buscar"] ?? ""
);

$estado = strtoupper(
    trim(
        $_GET["estado"] ?? ""
    )
);

$fecha = trim(
    $_GET["fecha"] ?? ""
);

$metodoPago = trim(
    $_GET["metodo"] ?? ""
);

$orden = trim(
    $_GET["orden"] ?? "recientes"
);


/*=========================================================
PÁGINA
=========================================================*/

$pagina = isset($_GET["pagina"])
    ? max(
        1,
        intval($_GET["pagina"])
    )
    : 1;

$porPagina = 5;


/*=========================================================
WHERE DINÁMICO
=========================================================*/

$where = [];

$params = [];

$types = "";


/*=========================================================
CLIENTE
=========================================================*/

$where[] = "tv.idCliente = ?";

$params[] = $idCliente;

$types .= "i";


/*=========================================================
BUSCADOR
=========================================================*/

if ($buscar != "") {

    $where[] = "

        (

            CAST(
                tv.id_ticket_ventas
                AS CHAR
            ) LIKE ?

            OR

            EXISTS (

                SELECT 1

                FROM detalle_ticket_ventas d

                INNER JOIN producto p

                    ON p.idProducto =
                       d.idProducto

                WHERE

                    d.id_ticket_ventas =
                    tv.id_ticket_ventas

                    AND

                    p.nombre LIKE ?

            )

        )

    ";

    $texto = "%{$buscar}%";

    $params[] = $texto;

    $params[] = $texto;

    $types .= "ss";
}


/*=========================================================
ESTADO
=========================================================*/

if ($estado != "") {

    /*=====================================================
    EN CAMINO

    EN_CAMINO agrupa:

    ASIGNADO
    RECOGIDO
    OBTENIDO
    ENVIADO
    =====================================================*/

    if ($estado === "EN_CAMINO") {

        $where[] = "

            tv.estado_envio IN (

                'ASIGNADO',

                'RECOGIDO',

                'OBTENIDO',

                'ENVIADO'

            )

        ";

    } else {

        $where[] =
            "tv.estado_envio = ?";

        $params[] = $estado;

        $types .= "s";
    }
}


/*=========================================================
MÉTODO DE PAGO
=========================================================*/

if ($metodoPago != "") {

    $where[] =
        "tv.id_metodo_pago = ?";

    $params[] = $metodoPago;

    $types .= "i";
}


/*=========================================================
FECHA
=========================================================*/

switch ($fecha) {

    /*=====================================================
    HOY
    =====================================================*/

    case "hoy":

        $where[] = "

            DATE(
                tv.fecha_venta
            ) = CURDATE()

        ";

        break;


    /*=====================================================
    ÚLTIMOS 7 DÍAS
    =====================================================*/

    case "7dias":

        $where[] = "

            tv.fecha_venta >=
            DATE_SUB(
                CURDATE(),
                INTERVAL 7 DAY
            )

        ";

        break;


    /*=====================================================
    ÚLTIMO MES
    =====================================================*/

    case "mes":

        $where[] = "

            tv.fecha_venta >=
            DATE_SUB(
                CURDATE(),
                INTERVAL 1 MONTH
            )

        ";

        break;


    /*=====================================================
    ÚLTIMO AÑO
    =====================================================*/

    case "anio":

        $where[] = "

            tv.fecha_venta >=
            DATE_SUB(
                CURDATE(),
                INTERVAL 1 YEAR
            )

        ";

        break;
}


/*=========================================================
ORDENAMIENTO
=========================================================*/

/*
|--------------------------------------------------------------------------
| IMPORTANTE
|--------------------------------------------------------------------------
|
| Para "recientes" utilizamos un orden personalizado:
|
| 1. NO_ENTREGADO
| 2. EN CAMINO
|    - ASIGNADO
|    - RECOGIDO
|    - OBTENIDO
|    - ENVIADO
| 3. PREPARANDO
| 4. CONFIRMADO
| 5. PENDIENTE
| 6. CANCELADO
| 7. ENTREGADO
|
| De esta forma los pedidos ENTREGADOS quedan
| siempre después de los pedidos que todavía
| requieren seguimiento.
|
*/

switch ($orden) {

    /*=====================================================
    MÁS ANTIGUOS
    =====================================================*/

    case "antiguos":

        $orderSQL = "

            tv.id_ticket_ventas ASC

        ";

        break;


    /*=====================================================
    MAYOR IMPORTE
    =====================================================*/

    case "mayor":

        $orderSQL = "

            tv.total_venta DESC,
            tv.id_ticket_ventas DESC

        ";

        break;


    /*=====================================================
    MENOR IMPORTE
    =====================================================*/

    case "menor":

        $orderSQL = "

            tv.total_venta ASC,
            tv.id_ticket_ventas DESC

        ";

        break;


    /*=====================================================
    MÁS RECIENTES
    =====================================================*/

    default:

        $orderSQL = "

            CASE

                /*-----------------------------------------
                NO ENTREGADO
                -----------------------------------------*/

                WHEN tv.estado_envio =
                     'NO_ENTREGADO'

                THEN 1


                /*-----------------------------------------
                EN CAMINO
                -----------------------------------------*/

                WHEN tv.estado_envio =
                     'ASIGNADO'

                THEN 2


                WHEN tv.estado_envio =
                     'RECOGIDO'

                THEN 2


                WHEN tv.estado_envio =
                     'OBTENIDO'

                THEN 2


                WHEN tv.estado_envio =
                     'ENVIADO'

                THEN 2


                /*-----------------------------------------
                PREPARANDO
                -----------------------------------------*/

                WHEN tv.estado_envio =
                     'PREPARANDO'

                THEN 3


                /*-----------------------------------------
                CONFIRMADO
                -----------------------------------------*/

                WHEN tv.estado_envio =
                     'CONFIRMADO'

                THEN 4


                /*-----------------------------------------
                PENDIENTE
                -----------------------------------------*/

                WHEN tv.estado_envio =
                     'PENDIENTE'

                THEN 5


                /*-----------------------------------------
                CANCELADO
                -----------------------------------------*/

                WHEN tv.estado_envio =
                     'CANCELADO'

                THEN 6


                /*-----------------------------------------
                ENTREGADO

                SIEMPRE DESPUÉS DE LOS
                PEDIDOS NO ENTREGADOS
                -----------------------------------------*/

                WHEN tv.estado_envio =
                     'ENTREGADO'

                THEN 7


                /*-----------------------------------------
                OTROS ESTADOS
                -----------------------------------------*/

                ELSE 8

            END ASC,

            /*---------------------------------------------
            DENTRO DE CADA GRUPO:

            Los más recientes primero
            ---------------------------------------------*/

            tv.id_ticket_ventas DESC

        ";

        break;
}


/*=========================================================
WHERE FINAL
=========================================================*/

$whereSQL = implode(
    " AND ",
    $where
);


/*=========================================================
CONTAR TOTAL DE PEDIDOS
=========================================================*/

$sqlTotal = "

    SELECT

        COUNT(
            DISTINCT tv.id_ticket_ventas
        ) AS total

    FROM ticket_ventas tv

    LEFT JOIN metodo_pago mp

        ON mp.id_metodo_pago =
           tv.id_metodo_pago

    WHERE

        $whereSQL

";


$stmtTotal = mysqli_prepare(
    $conexion,
    $sqlTotal
);


if (!$stmtTotal) {

    die(
        "Error SQL (COUNT): "
        . mysqli_error($conexion)
    );
}


/*=========================================================
ASIGNAR PARÁMETROS COUNT
=========================================================*/

if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmtTotal,
        $types,
        ...$params
    );
}


/*=========================================================
EJECUTAR COUNT
=========================================================*/

if (!mysqli_stmt_execute($stmtTotal)) {

    die(
        "Error al contar pedidos: "
        . mysqli_stmt_error($stmtTotal)
    );
}


/*=========================================================
OBTENER TOTAL
=========================================================*/

$resultadoTotal =
    mysqli_stmt_get_result(
        $stmtTotal
    );


$filaTotal =
    mysqli_fetch_assoc(
        $resultadoTotal
    );


$totalPedidos =
    intval(
        $filaTotal["total"] ?? 0
    );


/*=========================================================
CERRAR STATEMENT COUNT
=========================================================*/

mysqli_stmt_close(
    $stmtTotal
);


/*=========================================================
PAGINACIÓN
=========================================================*/

$totalPaginas = ($totalPedidos > 0)

    ? (int)ceil(
        $totalPedidos / $porPagina
    )

    : 1;


/*=========================================================
EVITAR PÁGINAS INEXISTENTES
=========================================================*/

if ($pagina > $totalPaginas) {

    $pagina = $totalPaginas;
}


/*=========================================================
CALCULAR OFFSET
=========================================================*/

$offset =
    ($pagina - 1) * $porPagina;


/*=========================================================
LIMIT
=========================================================*/

$limitSQL = "

    LIMIT ?, ?

";


/*=========================================================
PARÁMETROS CONSULTA PRINCIPAL
=========================================================*/

$paramsConsulta = $params;

$typesConsulta = $types;


/*=========================================================
AGREGAR OFFSET Y CANTIDAD
=========================================================*/

$paramsConsulta[] = $offset;

$paramsConsulta[] = $porPagina;

$typesConsulta .= "ii";


/*=========================================================
CONSULTA PRINCIPAL DE PEDIDOS
=========================================================*/

$sql = "

    SELECT

        tv.id_ticket_ventas,

        tv.fecha_venta,

        tv.hora_venta,

        tv.total_venta,

        tv.estado_envio,

        tv.direccion_envio,

        tv.tipo_comprobante,

        tv.serie,

        tv.numero,

        tv.aplica_igv,

        mp.nombre AS metodo_pago,


        /*=============================================
        CANTIDAD DE PRODUCTOS
        =============================================*/

        COUNT(
            dt.id_detalle_ticket
        ) AS cantidad_productos,


        /*=============================================
        IMAGEN PRINCIPAL DEL PEDIDO
        =============================================*/

        (

            SELECT

                i.id_imagen

            FROM detalle_ticket_ventas d

            INNER JOIN imagenes i

                ON i.idProducto =
                   d.idProducto

            WHERE

                d.id_ticket_ventas =
                tv.id_ticket_ventas

            ORDER BY

                i.orden ASC

            LIMIT 1

        ) AS id_imagen,


        /*=============================================
        PRIMER PRODUCTO
        =============================================*/

        (

            SELECT

                d.idProducto

            FROM detalle_ticket_ventas d

            WHERE

                d.id_ticket_ventas =
                tv.id_ticket_ventas

            LIMIT 1

        ) AS idProducto,


        /*=============================================
        NOMBRE DEL PRIMER PRODUCTO
        =============================================*/

        (

            SELECT

                p.nombre

            FROM detalle_ticket_ventas d

            INNER JOIN producto p

                ON p.idProducto =
                   d.idProducto

            WHERE

                d.id_ticket_ventas =
                tv.id_ticket_ventas

            LIMIT 1

        ) AS primer_producto,


        /*=============================================
        ENVÍO GRATIS
        =============================================*/

        (

            SELECT

                p.envio_gratis

            FROM detalle_ticket_ventas d

            INNER JOIN producto p

                ON p.idProducto =
                   d.idProducto

            WHERE

                d.id_ticket_ventas =
                tv.id_ticket_ventas

            LIMIT 1

        ) AS envio_gratis


    FROM ticket_ventas tv


    /*=============================================
    MÉTODO DE PAGO
    =============================================*/

    LEFT JOIN metodo_pago mp

        ON mp.id_metodo_pago =
           tv.id_metodo_pago


    /*=============================================
    DETALLE DEL PEDIDO
    =============================================*/

    LEFT JOIN detalle_ticket_ventas dt

        ON dt.id_ticket_ventas =
           tv.id_ticket_ventas


    /*=============================================
    FILTROS
    =============================================*/

    WHERE

        $whereSQL


    /*=============================================
    AGRUPAR POR PEDIDO
    =============================================*/

    GROUP BY

        tv.id_ticket_ventas


    /*=============================================
    ORDENAR
    =============================================*/

    ORDER BY

        $orderSQL


    /*=============================================
    PAGINACIÓN
    =============================================*/

    $limitSQL

";


/*=========================================================
PREPARAR CONSULTA
=========================================================*/

$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    die(
        "Error SQL pedidos: "
        . mysqli_error($conexion)
    );
}


/*=========================================================
ASIGNAR PARÁMETROS
=========================================================*/

if (!empty($paramsConsulta)) {

    mysqli_stmt_bind_param(
        $stmt,
        $typesConsulta,
        ...$paramsConsulta
    );
}


/*=========================================================
EJECUTAR CONSULTA
=========================================================*/

if (!mysqli_stmt_execute($stmt)) {

    die(
        "Error al obtener pedidos: "
        . mysqli_stmt_error($stmt)
    );
}


/*=========================================================
RESULTADO FINAL
=========================================================*/

$resultadoPedidos =
    mysqli_stmt_get_result(
        $stmt
    );


/*=========================================================
VALIDAR RESULTADO
=========================================================*/

if (!$resultadoPedidos) {

    $resultadoPedidos = false;
}

?>