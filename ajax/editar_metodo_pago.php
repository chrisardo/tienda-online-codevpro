<?php
//==========================================================
// CoDevPro Technology
// Archivo: ajax/editar_metodo_pago.php
//==========================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

//==========================================================
// VALIDAR SESIÓN
//==========================================================

if (!isset($_SESSION["idUser"]) || empty($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La sesión ha expirado. Inicia sesión nuevamente."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$idUser = (int) $_SESSION["idUser"];

//==========================================================
// RECIBIR DATOS
//==========================================================

$idMetodo = isset($_POST["id_metodo_pago"])
    ? (int) $_POST["id_metodo_pago"]
    : 0;

$nombre = isset($_POST["nombre"])
    ? trim($_POST["nombre"])
    : "";

//==========================================================
// VALIDAR ID
//==========================================================

if ($idMetodo <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El ID del método de pago no es válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//==========================================================
// VALIDAR NOMBRE
//==========================================================

if ($nombre === "") {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Debe ingresar el nombre del método de pago."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//==========================================================
// LIMPIAR NOMBRE
//==========================================================

// Elimina espacios múltiples
$nombre = preg_replace('/\s+/', ' ', $nombre);

// Limitar longitud
if (mb_strlen($nombre, "UTF-8") > 100) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El nombre del método de pago no puede superar los 100 caracteres."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//==========================================================
// VALIDAR QUE EL MÉTODO PERTENEZCA AL USUARIO
//==========================================================

$sqlExiste = "
    SELECT
        id_metodo_pago,
        nombre,
        Eliminado
    FROM metodo_pago
    WHERE
        id_metodo_pago = ?
        AND id_user = ?
    LIMIT 1
";

$stmtExiste = mysqli_prepare($conexion, $sqlExiste);

if (!$stmtExiste) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al preparar la consulta del método de pago."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_bind_param(
    $stmtExiste,
    "ii",
    $idMetodo,
    $idUser
);

mysqli_stmt_execute($stmtExiste);

$resultadoExiste = mysqli_stmt_get_result($stmtExiste);

$metodoActual = mysqli_fetch_assoc($resultadoExiste);

mysqli_stmt_close($stmtExiste);

//==========================================================
// VERIFICAR EXISTENCIA
//==========================================================

if (!$metodoActual) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El método de pago no existe o no pertenece a este usuario."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//==========================================================
// VALIDAR MÉTODO ELIMINADO
//==========================================================
//
// El modal ya no permite modificar el estado.
// Por seguridad, tampoco permitimos editar un método
// que actualmente está marcado como eliminado.
//

if ((int)$metodoActual["Eliminado"] === 1) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se puede editar un método de pago que está eliminado."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//==========================================================
// COMPARAR SI EL NOMBRE REALMENTE CAMBIÓ
//==========================================================

$nombreActual = trim($metodoActual["nombre"]);

if (mb_strtolower($nombreActual, "UTF-8") === mb_strtolower($nombre, "UTF-8")) {

    echo json_encode([
        "estado" => true,
        "mensaje" => "No se realizaron cambios. El nombre del método de pago ya es el mismo."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//==========================================================
// VALIDAR NOMBRE DUPLICADO
//==========================================================
//
// Se verifica solamente entre los métodos del mismo usuario
// y que no estén eliminados.
//

$sqlDuplicado = "
    SELECT
        id_metodo_pago
    FROM metodo_pago
    WHERE
        id_user = ?
        AND id_metodo_pago <> ?
        AND Eliminado = 0
        AND LOWER(TRIM(nombre)) = LOWER(TRIM(?))
    LIMIT 1
";

$stmtDuplicado = mysqli_prepare($conexion, $sqlDuplicado);

if (!$stmtDuplicado) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al validar el nombre del método de pago."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_bind_param(
    $stmtDuplicado,
    "iis",
    $idUser,
    $idMetodo,
    $nombre
);

mysqli_stmt_execute($stmtDuplicado);

$resultadoDuplicado = mysqli_stmt_get_result($stmtDuplicado);

$duplicado = mysqli_fetch_assoc($resultadoDuplicado);

mysqli_stmt_close($stmtDuplicado);

//==========================================================
// NOMBRE DUPLICADO
//==========================================================

if ($duplicado) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ya existe otro método de pago con el nombre \"" . $nombre . "\"."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//==========================================================
// ACTUALIZAR MÉTODO
//==========================================================
//
// IMPORTANTE:
// Solo actualizamos el nombre.
// NO modificamos:
// - Eliminado
// - id_user
// - id_metodo_pago
//

$sqlActualizar = "
    UPDATE metodo_pago
    SET
        nombre = ?
    WHERE
        id_metodo_pago = ?
        AND id_user = ?
        AND Eliminado = 0
    LIMIT 1
";

$stmtActualizar = mysqli_prepare($conexion, $sqlActualizar);

if (!$stmtActualizar) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al preparar la actualización del método de pago."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

mysqli_stmt_bind_param(
    $stmtActualizar,
    "sii",
    $nombre,
    $idMetodo,
    $idUser
);

$actualizado = mysqli_stmt_execute($stmtActualizar);

$filasAfectadas = mysqli_stmt_affected_rows($stmtActualizar);

mysqli_stmt_close($stmtActualizar);

//==========================================================
// VALIDAR ACTUALIZACIÓN
//==========================================================

if (!$actualizado) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No fue posible actualizar el método de pago."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//==========================================================
// RESPUESTA
//==========================================================

echo json_encode([
    "estado" => true,
    "mensaje" => "Método de pago actualizado correctamente.",
    "metodo" => [
        "id_metodo_pago" => $idMetodo,
        "nombre" => $nombre
    ]
], JSON_UNESCAPED_UNICODE);

//==========================================================
// CERRAR CONEXIÓN
//==========================================================

mysqli_close($conexion);
