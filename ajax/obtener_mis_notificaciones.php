<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_mis_notificaciones.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*======================================================
=            VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Debe iniciar sesión."
    ]);

    exit;
}


/*======================================================
=            OBTENER DATOS
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];

$pagina = isset($_GET["pagina"])
    ? (int) $_GET["pagina"]
    : 1;

$tipo = isset($_GET["tipo"])
    ? trim($_GET["tipo"])
    : "";

$leido = isset($_GET["leido"])
    ? trim($_GET["leido"])
    : "";

$porPagina = 10;


/*======================================================
=            CALCULAR OFFSET
======================================================*/

if ($pagina <= 0) {

    $pagina = 1;
}

$offset = ($pagina - 1) * $porPagina;


/*======================================================
=            ARMAR WHERE
======================================================*/

$where = "WHERE idCliente = ? AND Eliminado = 0";

$tipos = "i";

$parametros = [];

$parametros[] = $idCliente;


/*======================================================
=            FILTRAR POR TIPO
======================================================*/

if ($tipo !== "") {

    $where .= " AND tipo = ?";

    $tipos .= "s";

    $parametros[] = $tipo;
}


/*======================================================
=            FILTRAR POR LEÍDO
======================================================*/

if ($leido !== "") {

    $where .= " AND leido = ?";

    $tipos .= "i";

    $parametros[] = (int)$leido;
}


/*======================================================
=            TOTAL DE REGISTROS
======================================================*/

$sqlTotal = "

SELECT COUNT(*)
AS total

FROM notificaciones_cliente

{$where}

";

$stmtTotal = mysqli_prepare($conexion, $sqlTotal);

mysqli_stmt_bind_param(
    $stmtTotal,
    $tipos,
    ...$parametros
);

mysqli_stmt_execute($stmtTotal);

$resultadoTotal = mysqli_stmt_get_result($stmtTotal);

$total = mysqli_fetch_assoc($resultadoTotal)["total"];

mysqli_stmt_close($stmtTotal);


/*======================================================
=            CONSULTAR NOTIFICACIONES
======================================================*/

$sql = "

SELECT

    id_notificacion,
    titulo,
    mensaje,
    icono,
    color,
    url,
    leido,
    fecha,
    tipo

FROM notificaciones_cliente

{$where}

ORDER BY fecha DESC

LIMIT ?, ?

";


$stmt = mysqli_prepare($conexion, $sql);


/*======================================================
=            AGREGAR LIMIT
======================================================*/

$tiposConsulta = $tipos . "ii";

$parametrosConsulta = $parametros;

$parametrosConsulta[] = $offset;
$parametrosConsulta[] = $porPagina;


mysqli_stmt_bind_param(
    $stmt,
    $tiposConsulta,
    ...$parametrosConsulta
);


mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


/*======================================================
=            GUARDAR NOTIFICACIONES
======================================================*/

$notificaciones = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $notificaciones[] = [

        "id" => (int)$fila["id_notificacion"],

        "titulo" => $fila["titulo"],

        "mensaje" => $fila["mensaje"],

        "icono" => $fila["icono"],

        "color" => $fila["color"],

        "url" => $fila["url"],

        "leido" => (int)$fila["leido"],

        "tipo" => $fila["tipo"],

        "fecha" => date(
            "d/m/Y H:i",
            strtotime($fila["fecha"])
        )

    ];
}


/*======================================================
=            CERRAR STATEMENT
======================================================*/

mysqli_stmt_close($stmt);


/*======================================================
=            TOTAL DE PÁGINAS
======================================================*/

$totalPaginas = ceil($total / $porPagina);


/*======================================================
=            RESPUESTA FINAL
======================================================*/

echo json_encode([

    "estado" => "ok",

    "pagina" => $pagina,

    "porPagina" => $porPagina,

    "total" => (int)$total,

    "totalPaginas" => (int)$totalPaginas,

    "notificaciones" => $notificaciones

], JSON_UNESCAPED_UNICODE);


/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);

exit;
