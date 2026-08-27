<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/eliminar_logo_empresa.php
// Módulo: Mi Empresa
// Sistema: Inventa
//=====================================================

declare(strict_types=1);


//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header("Content-Type: application/json; charset=UTF-8");


//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(
    bool $success,
    string $message,
    array $extra = []
): void {

    $respuesta = [
        "success" => $success,
        "message" => $message
    ];

    if (!empty($extra)) {
        $respuesta = array_merge(
            $respuesta,
            $extra
        );
    }

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
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
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos."
    );
}


//=====================================================
// CONFIGURAR CHARSET
//=====================================================

mysqli_set_charset(
    $conexion,
    "utf8mb4"
);


//=====================================================
// USAR SIEMPRE EL USUARIO DE LA SESIÓN
//=====================================================
//
// No necesitamos confiar en id_user enviado por AJAX.
// El usuario autenticado determina qué empresa puede
// modificar.
//=====================================================

$idUser = $idUserSesion;


//=====================================================
// VERIFICAR QUE EL USUARIO EXISTA
// Y COMPROBAR SI TIENE LOGO
//=====================================================

$sqlVerificar = "
    SELECT
        id_user,
        CASE
            WHEN imagen IS NOT NULL
                 AND OCTET_LENGTH(imagen) > 0
            THEN 1
            ELSE 0
        END AS tiene_logo
    FROM usuario_acceso
    WHERE id_user = ?
    LIMIT 1
";


$stmtVerificar = mysqli_prepare(
    $conexion,
    $sqlVerificar
);


if (!$stmtVerificar) {

    responderJSON(
        false,
        "No se pudo preparar la consulta de verificación."
    );
}


mysqli_stmt_bind_param(
    $stmtVerificar,
    "i",
    $idUser
);


if (!mysqli_stmt_execute($stmtVerificar)) {

    $error = mysqli_stmt_error($stmtVerificar);

    mysqli_stmt_close($stmtVerificar);

    error_log(
        "eliminar_logo_empresa.php - Error verificación: " . $error
    );

    responderJSON(
        false,
        "No se pudo verificar la información de la empresa."
    );
}


$resultado = mysqli_stmt_get_result(
    $stmtVerificar
);


if (!$resultado) {

    mysqli_stmt_close($stmtVerificar);

    responderJSON(
        false,
        "No se pudo obtener la información de la empresa."
    );
}


$empresa = mysqli_fetch_assoc(
    $resultado
);


mysqli_stmt_close(
    $stmtVerificar
);


//=====================================================
// VALIDAR EMPRESA
//=====================================================

if (!$empresa) {

    responderJSON(
        false,
        "No se encontró la empresa asociada al usuario."
    );
}


//=====================================================
// VALIDAR LOGO
//=====================================================

$tieneLogo = isset($empresa["tiene_logo"])
    ? (int) $empresa["tiene_logo"]
    : 0;


if ($tieneLogo !== 1) {

    responderJSON(
        false,
        "La empresa no tiene un logo registrado."
    );
}


//=====================================================
// ELIMINAR LOGO
//=====================================================
//
// El logo está almacenado directamente en:
// usuario_acceso.imagen
//
// Como es LONGBLOB, se elimina estableciendo NULL.
//=====================================================

$sqlEliminar = "
    UPDATE usuario_acceso
    SET imagen = NULL
    WHERE id_user = ?
";


$stmtEliminar = mysqli_prepare(
    $conexion,
    $sqlEliminar
);


if (!$stmtEliminar) {

    responderJSON(
        false,
        "No se pudo preparar la eliminación del logo."
    );
}


mysqli_stmt_bind_param(
    $stmtEliminar,
    "i",
    $idUser
);


//=====================================================
// EJECUTAR ELIMINACIÓN
//=====================================================

if (!mysqli_stmt_execute($stmtEliminar)) {

    $error = mysqli_stmt_error($stmtEliminar);

    mysqli_stmt_close($stmtEliminar);

    error_log(
        "eliminar_logo_empresa.php - Error UPDATE: " . $error
    );

    responderJSON(
        false,
        "No se pudo eliminar el logo de la empresa."
    );
}


mysqli_stmt_close(
    $stmtEliminar
);


//=====================================================
// VERIFICAR QUE REALMENTE SE ELIMINÓ
//=====================================================

$sqlComprobar = "
    SELECT
        CASE
            WHEN imagen IS NOT NULL
                 AND OCTET_LENGTH(imagen) > 0
            THEN 1
            ELSE 0
        END AS tiene_logo
    FROM usuario_acceso
    WHERE id_user = ?
    LIMIT 1
";


$stmtComprobar = mysqli_prepare(
    $conexion,
    $sqlComprobar
);


if (!$stmtComprobar) {

    responderJSON(
        false,
        "El logo fue procesado, pero no se pudo verificar el resultado."
    );
}


mysqli_stmt_bind_param(
    $stmtComprobar,
    "i",
    $idUser
);


if (!mysqli_stmt_execute($stmtComprobar)) {

    mysqli_stmt_close($stmtComprobar);

    responderJSON(
        false,
        "No se pudo comprobar la eliminación del logo."
    );
}


$resultadoComprobar = mysqli_stmt_get_result(
    $stmtComprobar
);


if (!$resultadoComprobar) {

    mysqli_stmt_close($stmtComprobar);

    responderJSON(
        false,
        "No se pudo comprobar el estado final del logo."
    );
}


$estadoFinal = mysqli_fetch_assoc(
    $resultadoComprobar
);


mysqli_stmt_close(
    $stmtComprobar
);


//=====================================================
// VALIDAR ESTADO FINAL
//=====================================================

$tieneLogoFinal = isset($estadoFinal["tiene_logo"])
    ? (int) $estadoFinal["tiene_logo"]
    : 1;


if ($tieneLogoFinal === 1) {

    responderJSON(
        false,
        "El logo no pudo eliminarse de la base de datos."
    );
}


//=====================================================
// RESPUESTA EXITOSA
//=====================================================

responderJSON(
    true,
    "El logo de la empresa fue eliminado correctamente.",
    [
        "tieneLogo" => false,
        "logo" => null
    ]
);