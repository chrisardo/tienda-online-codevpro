<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_notificaciones.php
// Módulo: Notificaciones
// Sistema: Inventa
//=====================================================

declare(strict_types=1);


//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=utf-8");

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");


//=====================================================
// CONEXIÓN
//=====================================================

require_once __DIR__ . "/../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "estado" => false,

        "mensaje" =>
        "No se pudo establecer la conexión con la base de datos."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// UTF-8
//=====================================================

$conexion->set_charset("utf8mb4");


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    http_response_code(401);

    echo json_encode([

        "success" => false,

        "estado" => false,

        "mensaje" =>
        "La sesión de usuario no es válida."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


//=====================================================
// ACCIÓN
//=====================================================

$accion =
    isset($_GET["accion"])
    ? trim((string) $_GET["accion"])
    : "";


//=====================================================
// ACCIÓN: CLIENTES
//=====================================================

if ($accion === "clientes") {

    obtenerClientes(
        $conexion,
        $idUser
    );

    exit;
}


//=====================================================
// OBTENER PARÁMETROS
//=====================================================

$pagina =
    isset($_GET["pagina"])
    ? (int) $_GET["pagina"]
    : 1;


$limite =
    isset($_GET["limite"])
    ? (int) $_GET["limite"]
    : 10;


$buscar =
    isset($_GET["buscar"])
    ? trim((string) $_GET["buscar"])
    : "";


$tipo =
    isset($_GET["tipo"])
    ? trim((string) $_GET["tipo"])
    : "";


$estado =
    isset($_GET["estado"])
    ? trim((string) $_GET["estado"])
    : "";


$fecha =
    isset($_GET["fecha"])
    ? trim((string) $_GET["fecha"])
    : "";


//=====================================================
// VALIDAR PAGINACIÓN
//=====================================================

if ($pagina < 1) {

    $pagina = 1;
}


if ($limite < 1) {

    $limite = 10;
}


// Evitar límites excesivamente grandes.

if ($limite > 100) {

    $limite = 100;
}


//=====================================================
// OFFSET
//=====================================================

$offset =
    ($pagina - 1) * $limite;


//=====================================================
// VALIDAR TIPO
//=====================================================

$tiposPermitidos = [

    "PEDIDO",
    "PRODUCTO",
    "OFERTA",
    "PROMOCION",
    "SISTEMA",
    "OTRO"

];


if (
    $tipo !== "" &&
    !in_array(
        strtoupper($tipo),
        $tiposPermitidos,
        true
    )
) {

    $tipo = "";
} else {

    $tipo =
        strtoupper($tipo);
}


//=====================================================
// VALIDAR ESTADO
//=====================================================

if (
    $estado !== "0" &&
    $estado !== "1"
) {

    $estado = "";
}


//=====================================================
// VALIDAR FECHA
//=====================================================

if (
    $fecha !== "" &&
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $fecha
    )
) {

    $fecha = "";
}


//=====================================================
// CONSTRUIR WHERE
//=====================================================
//
// La tabla notificaciones_cliente no contiene id_user.
//
// Se filtra mediante:
//
// clientes.id_user = usuario actual
//
//=====================================================

$where = [];

$parametros = [];

$tiposParametros = "";


//=====================================================
// USUARIO
//=====================================================

$where[] = "c.id_user = ?";

$parametros[] =
    $idUser;

$tiposParametros .= "i";


//=====================================================
// CLIENTE NO ELIMINADO
//=====================================================

$where[] =
    "COALESCE(c.Eliminado, 0) = 0";


//=====================================================
// NOTIFICACIÓN NO ELIMINADA
//=====================================================

$where[] =
    "COALESCE(n.Eliminado, 0) = 0";


//=====================================================
// BÚSQUEDA
//=====================================================

if ($buscar !== "") {

    $where[] = "

        (
            c.nombre LIKE ?
            OR c.dni_o_ruc LIKE ?
            OR n.titulo LIKE ?
            OR n.mensaje LIKE ?
        )

    ";

    $buscarLike =
        "%" . $buscar . "%";


    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;


    $tiposParametros .=
        "ssss";
}


//=====================================================
// FILTRO TIPO
//=====================================================

if ($tipo !== "") {

    $where[] =
        "UPPER(COALESCE(n.tipo, 'SISTEMA')) = ?";

    $parametros[] =
        $tipo;

    $tiposParametros .=
        "s";
}


//=====================================================
// FILTRO ESTADO
//=====================================================

if ($estado !== "") {

    $where[] =
        "COALESCE(n.leido, 0) = ?";

    $parametros[] =
        (int) $estado;

    $tiposParametros .=
        "i";
}


//=====================================================
// FILTRO FECHA
//=====================================================

if ($fecha !== "") {

    $where[] =
        "DATE(n.fecha) = ?";

    $parametros[] =
        $fecha;

    $tiposParametros .=
        "s";
}


//=====================================================
// WHERE FINAL
//=====================================================

$whereSQL =
    implode(
        " AND ",
        $where
    );


//=====================================================
// CONTAR TOTAL
//=====================================================

$sqlTotal = "

    SELECT
        COUNT(*) AS total

    FROM notificaciones_cliente n

    INNER JOIN clientes c
        ON c.idCliente = n.idCliente

    WHERE
        {$whereSQL}

";


$stmtTotal =
    mysqli_prepare(
        $conexion,
        $sqlTotal
    );


if (!$stmtTotal) {

    responderError(
        "No se pudo preparar la consulta de conteo."
    );
}


vincularParametros(
    $stmtTotal,
    $tiposParametros,
    $parametros
);


if (!mysqli_stmt_execute($stmtTotal)) {

    mysqli_stmt_close($stmtTotal);

    responderError(
        "No se pudo obtener el total de notificaciones."
    );
}


$resultadoTotal =
    mysqli_stmt_get_result(
        $stmtTotal
    );


$filaTotal =
    mysqli_fetch_assoc(
        $resultadoTotal
    );


$total =
    isset($filaTotal["total"])
    ? (int) $filaTotal["total"]
    : 0;


mysqli_stmt_close(
    $stmtTotal
);


//=====================================================
// TOTAL DE PÁGINAS
//=====================================================

$totalPaginas =
    $total > 0
    ? (int) ceil(
        $total / $limite
    )
    : 1;


//=====================================================
// SI LA PÁGINA SOLICITADA SUPERA EL TOTAL
//=====================================================

if (
    $pagina > $totalPaginas &&
    $total > 0
) {

    $pagina =
        $totalPaginas;


    $offset =
        ($pagina - 1) * $limite;
}


//=====================================================
// CONSULTA PRINCIPAL
//=====================================================

$sql = "

    SELECT

        n.id_notificacion,

        n.idCliente,

        c.nombre AS nombre_cliente,

        c.dni_o_ruc,

        n.titulo,

        n.mensaje,

        n.tipo,

        COALESCE(
            n.icono,
            'bi-bell-fill'
        ) AS icono,

        COALESCE(
            n.color,
            'primary'
        ) AS color,

        COALESCE(
            n.url,
            ''
        ) AS url,

        COALESCE(
            n.leido,
            0
        ) AS leido,

        n.fecha

    FROM notificaciones_cliente n

    INNER JOIN clientes c
        ON c.idCliente = n.idCliente

    WHERE
        {$whereSQL}

    ORDER BY
        n.fecha DESC,
        n.id_notificacion DESC

    LIMIT ?
    OFFSET ?

";


//=====================================================
// PARÁMETROS PARA CONSULTA PRINCIPAL
//=====================================================

$parametrosLista =
    $parametros;


$tiposLista =
    $tiposParametros;


$parametrosLista[] =
    $limite;


$parametrosLista[] =
    $offset;


$tiposLista .=
    "ii";


//=====================================================
// PREPARAR
//=====================================================

$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );


if (!$stmt) {

    responderError(
        "No se pudo preparar la consulta de notificaciones."
    );
}


//=====================================================
// BIND
//=====================================================

vincularParametros(
    $stmt,
    $tiposLista,
    $parametrosLista
);


//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    responderError(
        "No se pudieron obtener las notificaciones."
    );
}


//=====================================================
// RESULTADO
//=====================================================

$resultado =
    mysqli_stmt_get_result(
        $stmt
    );


$notificaciones = [];


//=====================================================
// RECORRER
//=====================================================

while (
    $fila =
    mysqli_fetch_assoc($resultado)
) {

    $idNotificacion =
        (int) $fila["id_notificacion"];


    $idCliente =
        (int) $fila["idCliente"];


    $leido =
        (int) $fila["leido"];


    $notificaciones[] = [

        //=================================================
        // ID
        //=================================================

        "id_notificacion" =>
        $idNotificacion,

        "idNotificacion" =>
        $idNotificacion,

        "id" =>
        $idNotificacion,


        //=================================================
        // CLIENTE
        //=================================================

        "idCliente" =>
        $idCliente,

        "id_cliente" =>
        $idCliente,

        "nombre_cliente" =>
        $fila["nombre_cliente"] ?? "",

        "cliente" =>
        $fila["nombre_cliente"] ?? "",

        "dni_o_ruc" =>
        $fila["dni_o_ruc"] ?? "",


        //=================================================
        // NOTIFICACIÓN
        //=================================================

        "titulo" =>
        $fila["titulo"] ?? "",

        "mensaje" =>
        $fila["mensaje"] ?? "",


        //=================================================
        // CONFIGURACIÓN
        //=================================================

        "tipo" =>
        $fila["tipo"] ?? "SISTEMA",

        "icono" =>
        $fila["icono"] ?? "bi-bell-fill",

        "color" =>
        $fila["color"] ?? "primary",

        "url" =>
        $fila["url"] ?? "",


        //=================================================
        // ESTADO
        //=================================================

        "leido" =>
        $leido,


        //=================================================
        // FECHA
        //=================================================

        "fecha" =>
        $fila["fecha"] ?? ""

    ];
}


mysqli_stmt_close($stmt);


//=====================================================
// INFORMACIÓN DE PAGINACIÓN
//=====================================================

$desde = 0;

$hasta = 0;


if ($total > 0) {

    $desde =
        $offset + 1;


    $hasta =
        min(
            $offset + count($notificaciones),
            $total
        );
}


//=====================================================
// RESPUESTA
//=====================================================

echo json_encode([

    "success" => true,

    "estado" => true,

    "mensaje" =>
    "Notificaciones obtenidas correctamente.",


    //=================================================
    // LISTA
    //=================================================

    "notificaciones" =>
    $notificaciones,

    "data" =>
    $notificaciones,


    //=================================================
    // PAGINACIÓN
    //=================================================

    "pagina" =>
    $pagina,

    "limite" =>
    $limite,

    "total" =>
    $total,

    "total_registros" =>
    $total,

    "totalNotificaciones" =>
    $total,

    "total_paginas" =>
    $totalPaginas,

    "totalPaginas" =>
    $totalPaginas,

    "paginas" =>
    $totalPaginas,

    "desde" =>
    $desde,

    "hasta" =>
    $hasta

], JSON_UNESCAPED_UNICODE);


//=====================================================
// CERRAR CONEXIÓN
//=====================================================

mysqli_close($conexion);


//=====================================================
// FUNCIÓN: OBTENER CLIENTES
//=====================================================

function obtenerClientes(
    mysqli $conexion,
    int $idUser
): void {

    //=================================================
    // CONSULTA
    //=================================================

    $sql = "

        SELECT

            idCliente,

            nombre,

            dni_o_ruc

        FROM clientes

        WHERE id_user = ?

        AND COALESCE(Eliminado, 0) = 0

        ORDER BY
            nombre ASC

    ";


    //=================================================
    // PREPARAR
    //=================================================

    $stmt =
        mysqli_prepare(
            $conexion,
            $sql
        );


    if (!$stmt) {

        responderError(
            "No se pudo preparar la consulta de clientes."
        );
    }


    //=================================================
    // BIND
    //=================================================

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );


    //=================================================
    // EJECUTAR
    //=================================================

    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        responderError(
            "No se pudieron obtener los clientes."
        );
    }


    //=================================================
    // RESULTADO
    //=================================================

    $resultado =
        mysqli_stmt_get_result(
            $stmt
        );


    $clientes = [];


    //=================================================
    // RECORRER CLIENTES
    //=================================================

    while (
        $fila =
        mysqli_fetch_assoc($resultado)
    ) {

        $clientes[] = [

            "idCliente" =>
            (int) $fila["idCliente"],

            "id_cliente" =>
            (int) $fila["idCliente"],

            "id" =>
            (int) $fila["idCliente"],

            "nombre" =>
            $fila["nombre"] ?? "",

            "nombre_cliente" =>
            $fila["nombre"] ?? "",

            "dni_o_ruc" =>
            $fila["dni_o_ruc"] ?? ""

        ];
    }


    mysqli_stmt_close($stmt);


    //=================================================
    // RESPUESTA
    //=================================================

    echo json_encode([

        "success" => true,

        "estado" => true,

        "mensaje" =>
        "Clientes obtenidos correctamente.",

        "clientes" =>
        $clientes,

        "data" =>
        $clientes

    ], JSON_UNESCAPED_UNICODE);


    mysqli_close($conexion);

    exit;
}


//=====================================================
// FUNCIÓN: VINCULAR PARÁMETROS DINÁMICOS
//=====================================================

function vincularParametros(
    mysqli_stmt $stmt,
    string $tipos,
    array $parametros
): void {

    if ($tipos === "" || empty($parametros)) {

        return;
    }


    $referencias = [];

    $referencias[] =
        $tipos;


    foreach (
        $parametros as $indice => $valor
    ) {

        $referencias[] =
            &$parametros[$indice];
    }


    call_user_func_array(
        [
            $stmt,
            "bind_param"
        ],
        $referencias
    );
}


//=====================================================
// FUNCIÓN: RESPONDER ERROR
//=====================================================

function responderError(
    string $mensaje,
    int $codigoHttp = 500
): void {

    http_response_code(
        $codigoHttp
    );


    echo json_encode([

        "success" => false,

        "estado" => false,

        "mensaje" => $mensaje,

        "notificaciones" => [],

        "data" => [],

        "total" => 0,

        "total_paginas" => 1,

        "totalPaginas" => 1

    ], JSON_UNESCAPED_UNICODE);


    exit;
}
