<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_lista_sucursales.php
// Módulo: Sucursales
// Sistema: Inventa
//=====================================================

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    echo json_encode([
        "success" => false,
        "error" => true,
        "mensaje" => "No se pudo establecer la conexión con la base de datos.",
        "sucursales" => [],
        "total" => 0,
        "pagina" => 1,
        "totalPaginas" => 0,
        "kpi" => [
            "total" => 0,
            "activas" => 0,
            "inactivas" => 0,
            "estado" => "Sin sucursales"
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUser <= 0) {

    echo json_encode([
        "success" => false,
        "error" => true,
        "mensaje" => "No se pudo identificar al usuario.",
        "sucursales" => [],
        "total" => 0,
        "pagina" => 1,
        "totalPaginas" => 0,
        "kpi" => [
            "total" => 0,
            "activas" => 0,
            "inactivas" => 0,
            "estado" => "Sin sucursales"
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// PARÁMETROS
//=====================================================

$pagina = isset($_GET["pagina"])
    ? (int) $_GET["pagina"]
    : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$registrosPorPagina = isset($_GET["registros"])
    ? (int) $_GET["registros"]
    : 5;

if ($registrosPorPagina < 1) {
    $registrosPorPagina = 5;
}

if ($registrosPorPagina > 100) {
    $registrosPorPagina = 100;
}

//=====================================================
// IMPORTANTE:
// EL JS ENVÍA "busqueda"
//=====================================================

$busqueda = isset($_GET["busqueda"])
    ? trim($_GET["busqueda"])
    : "";

//=====================================================
// CALCULAR OFFSET
//=====================================================

$offset = ($pagina - 1) * $registrosPorPagina;

//=====================================================
// PREPARAR BÚSQUEDA
//=====================================================

$buscarLike = "%" . $busqueda . "%";

//=====================================================
// VARIABLES KPI
//=====================================================

$totalSucursales = 0;
$sucursalesActivas = 0;
$sucursalesInactivas = 0;

//=====================================================
// OBTENER KPI
//
// Eliminado = 0 -> Activa
// Eliminado = 1 -> Inactiva
//=====================================================

$sqlKpi = "
    SELECT
        COUNT(*) AS total,

        SUM(
            CASE
                WHEN Eliminado = 0 THEN 1
                ELSE 0
            END
        ) AS activas,

        SUM(
            CASE
                WHEN Eliminado = 1 THEN 1
                ELSE 0
            END
        ) AS inactivas

    FROM sucursal

    WHERE id_user = ?
";

$stmtKpi = mysqli_prepare($conexion, $sqlKpi);

if (!$stmtKpi) {

    echo json_encode([
        "success" => false,
        "error" => true,
        "mensaje" => "Error al preparar la consulta de KPI.",
        "detalle" => mysqli_error($conexion),
        "sucursales" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_bind_param(
    $stmtKpi,
    "i",
    $idUser
);

if (!mysqli_stmt_execute($stmtKpi)) {

    echo json_encode([
        "success" => false,
        "error" => true,
        "mensaje" => "Error al ejecutar la consulta de KPI.",
        "detalle" => mysqli_stmt_error($stmtKpi),
        "sucursales" => []
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmtKpi);

    exit;
}

$resultadoKpi = mysqli_stmt_get_result($stmtKpi);

if ($resultadoKpi) {

    $filaKpi = mysqli_fetch_assoc($resultadoKpi);

    if ($filaKpi) {

        $totalSucursales = (int) ($filaKpi["total"] ?? 0);

        $sucursalesActivas = (int) ($filaKpi["activas"] ?? 0);

        $sucursalesInactivas = (int) ($filaKpi["inactivas"] ?? 0);
    }
}

mysqli_stmt_close($stmtKpi);

//=====================================================
// CONTAR REGISTROS ACTIVOS
//
// La lista muestra solamente:
// Eliminado = 0
//=====================================================

$sqlTotal = "
    SELECT COUNT(*) AS total

    FROM sucursal

    WHERE id_user = ?

      AND Eliminado = 0

      AND nombre LIKE ?
";

$stmtTotal = mysqli_prepare($conexion, $sqlTotal);

if (!$stmtTotal) {

    echo json_encode([
        "success" => false,
        "error" => true,
        "mensaje" => "Error al preparar el conteo de sucursales.",
        "detalle" => mysqli_error($conexion),
        "sucursales" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_bind_param(
    $stmtTotal,
    "is",
    $idUser,
    $buscarLike
);

if (!mysqli_stmt_execute($stmtTotal)) {

    echo json_encode([
        "success" => false,
        "error" => true,
        "mensaje" => "Error al ejecutar el conteo de sucursales.",
        "detalle" => mysqli_stmt_error($stmtTotal),
        "sucursales" => []
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmtTotal);

    exit;
}

$resultadoTotal = mysqli_stmt_get_result($stmtTotal);

$filaTotal = $resultadoTotal
    ? mysqli_fetch_assoc($resultadoTotal)
    : null;

$totalRegistros = (int) ($filaTotal["total"] ?? 0);

mysqli_stmt_close($stmtTotal);

//=====================================================
// CALCULAR TOTAL DE PÁGINAS
//=====================================================

$totalPaginas = $totalRegistros > 0
    ? (int) ceil($totalRegistros / $registrosPorPagina)
    : 0;

//=====================================================
// CORREGIR PÁGINA
//=====================================================

if ($totalPaginas > 0 && $pagina > $totalPaginas) {

    $pagina = $totalPaginas;

    $offset = ($pagina - 1) * $registrosPorPagina;
}

//=====================================================
// OBTENER SUCURSALES
//=====================================================

$sucursales = [];

$sql = "
    SELECT
        id_sucursal,
        nombre,
        id_user,
        Eliminado

    FROM sucursal

    WHERE id_user = ?

      AND Eliminado = 0

      AND nombre LIKE ?

    ORDER BY nombre ASC

    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "error" => true,
        "mensaje" => "Error al preparar la consulta de sucursales.",
        "detalle" => mysqli_error($conexion),
        "sucursales" => []
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "isii",
    $idUser,
    $buscarLike,
    $registrosPorPagina,
    $offset
);

if (!mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "success" => false,
        "error" => true,
        "mensaje" => "Error al ejecutar la consulta de sucursales.",
        "detalle" => mysqli_stmt_error($stmt),
        "sucursales" => []
    ], JSON_UNESCAPED_UNICODE);

    mysqli_stmt_close($stmt);

    exit;
}

$resultado = mysqli_stmt_get_result($stmt);

//=====================================================
// RECORRER RESULTADOS
//=====================================================

if ($resultado) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $sucursales[] = [

            "id_sucursal" => (int) $fila["id_sucursal"],

            "nombre" => $fila["nombre"],

            "id_user" => (int) $fila["id_user"],

            "Eliminado" => (int) $fila["Eliminado"],

            "estado" => "ACTIVA"
        ];
    }
}

mysqli_stmt_close($stmt);

//=====================================================
// INFORMACIÓN DE PAGINACIÓN
//=====================================================

$desde = 0;
$hasta = 0;

if ($totalRegistros > 0) {

    $desde = $offset + 1;

    $hasta = min(
        $offset + $registrosPorPagina,
        $totalRegistros
    );
}

//=====================================================
// ESTADO GENERAL
//=====================================================

$estadoGeneral = "Sin sucursales";

if ($sucursalesActivas > 0) {

    $estadoGeneral = "Activo";
} elseif ($sucursalesInactivas > 0) {

    $estadoGeneral = "Inactivo";
}

//=====================================================
// RESPUESTA JSON
//=====================================================

echo json_encode([

    "success" => true,

    "error" => false,

    "mensaje" => "Sucursales obtenidas correctamente.",

    //=================================================
    // IMPORTANTE:
    // EL JS ESPERA "sucursales"
    //=================================================

    "sucursales" => $sucursales,

    //=================================================
    // INFORMACIÓN GENERAL
    //=================================================

    "total" => $totalRegistros,

    "pagina" => $pagina,

    "registrosPorPagina" => $registrosPorPagina,

    "totalPaginas" => $totalPaginas,

    "desde" => $desde,

    "hasta" => $hasta,

    //=================================================
    // INFORMACIÓN
    //=================================================

    "informacion" => [

        "mostrando" => count($sucursales),

        "desde" => $desde,

        "hasta" => $hasta,

        "total" => $totalRegistros
    ],

    //=================================================
    // KPI
    //=================================================

    "kpi" => [

        "total" => $totalSucursales,

        "activas" => $sucursalesActivas,

        "inactivas" => $sucursalesInactivas,

        "estado" => $estadoGeneral
    ]

], JSON_UNESCAPED_UNICODE);

exit;
