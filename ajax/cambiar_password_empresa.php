<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/cambiar_password_empresa.php
// Módulo: Mi Empresa
// Sistema: Inventa
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// RESPUESTA JSON
//=====================================================

function responderJSON($success, $message, $data = [])
{
    echo json_encode(
        [
            "success" => $success,
            "message" => $message,
            "data"    => $data
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responderJSON(
        false,
        "Método de solicitud no permitido."
    );
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUserSesion = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUserSesion <= 0) {

    responderJSON(
        false,
        "La sesión ha expirado. Inicia sesión nuevamente."
    );
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// OBTENER DATOS RECIBIDOS
//=====================================================

$idUser = isset($_POST["id_user"])
    ? (int) $_POST["id_user"]
    : 0;

$passwordActual = isset($_POST["passwordActual"])
    ? (string) $_POST["passwordActual"]
    : "";

$passwordNueva = isset($_POST["passwordNueva"])
    ? (string) $_POST["passwordNueva"]
    : "";

$passwordConfirmar = isset($_POST["passwordConfirmar"])
    ? (string) $_POST["passwordConfirmar"]
    : "";

//=====================================================
// VALIDAR ID
//=====================================================

if ($idUser <= 0) {

    responderJSON(
        false,
        "El usuario no es válido."
    );
}

//=====================================================
// SEGURIDAD:
// EL ID RECIBIDO DEBE COINCIDIR CON LA SESIÓN
//=====================================================

if ($idUser !== $idUserSesion) {

    responderJSON(
        false,
        "No tienes autorización para modificar esta cuenta."
    );
}

//=====================================================
// VALIDAR CONTRASEÑA ACTUAL
//=====================================================

if ($passwordActual === "") {

    responderJSON(
        false,
        "Debes ingresar tu contraseña actual."
    );
}

//=====================================================
// VALIDAR NUEVA CONTRASEÑA
//=====================================================

if ($passwordNueva === "") {

    responderJSON(
        false,
        "Debes ingresar la nueva contraseña."
    );
}

//=====================================================
// VALIDAR LONGITUD
//=====================================================

if (strlen($passwordNueva) < 8) {

    responderJSON(
        false,
        "La nueva contraseña debe tener al menos 8 caracteres."
    );
}

//=====================================================
// VALIDAR CONFIRMACIÓN
//=====================================================

if ($passwordConfirmar === "") {

    responderJSON(
        false,
        "Debes confirmar la nueva contraseña."
    );
}

//=====================================================
// VALIDAR QUE COINCIDAN
//=====================================================

if ($passwordNueva !== $passwordConfirmar) {

    responderJSON(
        false,
        "La nueva contraseña y su confirmación no coinciden."
    );
}

//=====================================================
// VALIDAR QUE SEA DIFERENTE
//=====================================================

if ($passwordActual === $passwordNueva) {

    responderJSON(
        false,
        "La nueva contraseña debe ser diferente a la contraseña actual."
    );
}

//=====================================================
// OBTENER USUARIO
//=====================================================

$sql = "
    SELECT
        id_user,
        contrasena,
        estado
    FROM usuario_acceso
    WHERE id_user = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    responderJSON(
        false,
        "No se pudo preparar la consulta de usuario."
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    responderJSON(
        false,
        "No se pudo consultar la cuenta de usuario."
    );
}

$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {

    mysqli_stmt_close($stmt);

    responderJSON(
        false,
        "No se pudo obtener la información de la cuenta."
    );
}

$usuario = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

//=====================================================
// VALIDAR EXISTENCIA
//=====================================================

if (!$usuario) {

    responderJSON(
        false,
        "La cuenta de usuario no existe."
    );
}

//=====================================================
// VALIDAR ESTADO
//=====================================================

$estadoUsuario = strtoupper(
    trim((string) ($usuario["estado"] ?? ""))
);

if (
    $estadoUsuario !== "" &&
    $estadoUsuario !== "ACTIVO"
) {

    responderJSON(
        false,
        "La cuenta de usuario se encuentra inactiva."
    );
}

//=====================================================
// OBTENER CONTRASEÑA ALMACENADA
//=====================================================

$contrasenaActualBD = (string) ($usuario["contrasena"] ?? "");

if ($contrasenaActualBD === "") {

    responderJSON(
        false,
        "La cuenta no tiene una contraseña configurada."
    );
}

//=====================================================
// VERIFICAR CONTRASEÑA ACTUAL
//=====================================================

if (!password_verify($passwordActual, $contrasenaActualBD)) {

    responderJSON(
        false,
        "La contraseña actual es incorrecta."
    );
}

//=====================================================
// GENERAR NUEVO HASH
//=====================================================

$nuevaContrasenaHash = password_hash(
    $passwordNueva,
    PASSWORD_DEFAULT
);

if ($nuevaContrasenaHash === false) {

    responderJSON(
        false,
        "No se pudo generar la nueva contraseña."
    );
}

//=====================================================
// ACTUALIZAR CONTRASEÑA
//=====================================================

$sqlActualizar = "
    UPDATE usuario_acceso
    SET
        contrasena = ?,
        password_changed_at = NOW()
    WHERE id_user = ?
    LIMIT 1
";

$stmtActualizar = mysqli_prepare(
    $conexion,
    $sqlActualizar
);

if (!$stmtActualizar) {

    responderJSON(
        false,
        "No se pudo preparar la actualización de contraseña."
    );
}

mysqli_stmt_bind_param(
    $stmtActualizar,
    "si",
    $nuevaContrasenaHash,
    $idUser
);

//=====================================================
// EJECUTAR ACTUALIZACIÓN
//=====================================================

if (!mysqli_stmt_execute($stmtActualizar)) {

    $error = mysqli_stmt_error($stmtActualizar);

    mysqli_stmt_close($stmtActualizar);

    error_log(
        "Error al cambiar contraseña de usuario {$idUser}: {$error}"
    );

    responderJSON(
        false,
        "No se pudo actualizar la contraseña."
    );
}

//=====================================================
// VERIFICAR FILAS AFECTADAS
//=====================================================

$filasAfectadas = mysqli_stmt_affected_rows(
    $stmtActualizar
);

mysqli_stmt_close($stmtActualizar);

//=====================================================
// CONFIRMAR ACTUALIZACIÓN
//=====================================================

if ($filasAfectadas < 1) {

    responderJSON(
        false,
        "No se realizó ningún cambio en la contraseña."
    );
}

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

responderJSON(
    true,
    "La contraseña se actualizó correctamente.",
    [
        "id_user" => $idUser,
        "password_changed_at" => date("Y-m-d H:i:s")
    ]
);
