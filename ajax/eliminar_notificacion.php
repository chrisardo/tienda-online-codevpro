<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/eliminar_notificacion.php
// Módulo: Notificaciones
// Sistema: Inventa
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");

require_once "../controladores/conexion.php";

//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// RESPUESTA JSON
//=====================================================

function responderJSON(
    bool $success,
    string $mensaje,
    array $extra = [],
    int $codigoHTTP = 200
): void {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "mensaje" => $mensaje
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [],
        500
    );
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUser <= 0) {

    responderJSON(
        false,
        "La sesión de usuario no es válida.",
        [],
        401
    );
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responderJSON(
        false,
        "Método de solicitud no permitido.",
        [],
        405
    );
}

//=====================================================
// OBTENER ID DE NOTIFICACIÓN
//=====================================================

$idNotificacion = isset($_POST["idNotificacion"])
    ? (int) $_POST["idNotificacion"]
    : 0;

//=====================================================
// VALIDAR ID
//=====================================================

if ($idNotificacion <= 0) {

    responderJSON(
        false,
        "El identificador de la notificación no es válido.",
        [],
        400
    );
}

//=====================================================
// VERIFICAR NOTIFICACIÓN
//=====================================================
// La notificación debe:
//
// 1. Existir.
// 2. No estar eliminada.
// 3. Estar asociada a un cliente.
// 4. El cliente pertenecer al usuario actual.
// 5. El cliente no estar eliminado.
//
// Esto evita que un usuario pueda eliminar
// notificaciones pertenecientes a otra cuenta.
//=====================================================

$sqlVerificar = "

    SELECT
        n.id_notificacion,
        n.idCliente

    FROM notificaciones_cliente n

    INNER JOIN clientes c
        ON c.idCliente = n.idCliente

    WHERE
        n.id_notificacion = ?
        AND n.Eliminado = 0
        AND c.id_user = ?
        AND c.Eliminado = 0

    LIMIT 1

";

$stmtVerificar = mysqli_prepare(
    $conexion,
    $sqlVerificar
);

if (!$stmtVerificar) {

    error_log(
        "Error preparando verificación de notificación: " .
            mysqli_error($conexion)
    );

    responderJSON(
        false,
        "No se pudo verificar la notificación.",
        [],
        500
    );
}

mysqli_stmt_bind_param(
    $stmtVerificar,
    "ii",
    $idNotificacion,
    $idUser
);

//=====================================================
// EJECUTAR VERIFICACIÓN
//=====================================================

if (!mysqli_stmt_execute($stmtVerificar)) {

    $error = mysqli_stmt_error($stmtVerificar);

    mysqli_stmt_close($stmtVerificar);

    error_log(
        "Error ejecutando verificación de notificación: " .
            $error
    );

    responderJSON(
        false,
        "No se pudo verificar la notificación.",
        [],
        500
    );
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultadoVerificar = mysqli_stmt_get_result(
    $stmtVerificar
);

if (
    !$resultadoVerificar ||
    mysqli_num_rows($resultadoVerificar) === 0
) {

    mysqli_stmt_close($stmtVerificar);

    responderJSON(
        false,
        "La notificación no existe, ya fue eliminada o no pertenece a tu cuenta.",
        [],
        404
    );
}

mysqli_stmt_close($stmtVerificar);

//=====================================================
// ELIMINACIÓN LÓGICA
//=====================================================
// NO utilizamos DELETE.
// La notificación permanece en la base de datos,
// pero queda marcada como eliminada.
//
// Eliminado = 1
//=====================================================

$sqlEliminar = "

    UPDATE notificaciones_cliente

    SET
        Eliminado = 1

    WHERE
        id_notificacion = ?
        AND Eliminado = 0

    LIMIT 1

";

$stmtEliminar = mysqli_prepare(
    $conexion,
    $sqlEliminar
);

if (!$stmtEliminar) {

    error_log(
        "Error preparando eliminación de notificación: " .
            mysqli_error($conexion)
    );

    responderJSON(
        false,
        "No se pudo preparar la eliminación de la notificación.",
        [],
        500
    );
}

//=====================================================
// PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmtEliminar,
    "i",
    $idNotificacion
);

//=====================================================
// EJECUTAR ELIMINACIÓN
//=====================================================

if (!mysqli_stmt_execute($stmtEliminar)) {

    $error = mysqli_stmt_error($stmtEliminar);

    mysqli_stmt_close($stmtEliminar);

    error_log(
        "Error ejecutando eliminación de notificación: " .
            $error
    );

    responderJSON(
        false,
        "No se pudo eliminar la notificación.",
        [],
        500
    );
}

//=====================================================
// VERIFICAR FILAS AFECTADAS
//=====================================================

$filasAfectadas = mysqli_stmt_affected_rows(
    $stmtEliminar
);

mysqli_stmt_close($stmtEliminar);

//=====================================================
// VALIDAR ELIMINACIÓN
//=====================================================

if ($filasAfectadas <= 0) {

    responderJSON(
        false,
        "La notificación no pudo ser eliminada o ya se encontraba eliminada.",
        [],
        404
    );
}

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

responderJSON(
    true,
    "La notificación fue eliminada correctamente.",
    [
        "idNotificacion" => $idNotificacion
    ],
    200
);
