<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_logo_empresa.php
// Módulo: Mi Empresa
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// MANEJO DE ERRORES
//=====================================================

ini_set("display_errors", "0");
error_reporting(E_ALL);

//=====================================================
// RESPUESTA JSON
//=====================================================

function responderJSON($success, $message = "", $datos = [])
{
    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
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
        "No se pudo establecer conexión con la base de datos."
    );
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
// OBTENER ID DE USUARIO
//=====================================================

$idUser = isset($_POST["id_user"])
    ? intval($_POST["id_user"])
    : 0;

//=====================================================
// VALIDAR ID DE USUARIO
//=====================================================

if ($idUser <= 0) {

    responderJSON(
        false,
        "El ID de usuario no es válido."
    );
}

//=====================================================
// VALIDAR ARCHIVO
//=====================================================

if (!isset($_FILES["logo"])) {

    responderJSON(
        false,
        "No se recibió ningún archivo de logo."
    );
}

//=====================================================
// DATOS DEL ARCHIVO
//=====================================================

$archivo = $_FILES["logo"];

//=====================================================
// VALIDAR ERROR DE SUBIDA
//=====================================================

if (!isset($archivo["error"]) || $archivo["error"] !== UPLOAD_ERR_OK) {

    $mensajeError = "No se pudo subir el archivo.";

    switch ($archivo["error"] ?? UPLOAD_ERR_NO_FILE) {

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $mensajeError = "El archivo supera el tamaño máximo permitido.";
            break;

        case UPLOAD_ERR_PARTIAL:
            $mensajeError = "El archivo se subió parcialmente.";
            break;

        case UPLOAD_ERR_NO_FILE:
            $mensajeError = "No se seleccionó ningún archivo.";
            break;

        case UPLOAD_ERR_NO_TMP_DIR:
            $mensajeError = "No existe el directorio temporal del servidor.";
            break;

        case UPLOAD_ERR_CANT_WRITE:
            $mensajeError = "No se pudo escribir el archivo temporal.";
            break;

        case UPLOAD_ERR_EXTENSION:
            $mensajeError = "La subida del archivo fue detenida por una extensión del servidor.";
            break;
    }

    responderJSON(false, $mensajeError);
}

//=====================================================
// VALIDAR TAMAÑO
//=====================================================

$maximoTamano = 2 * 1024 * 1024; // 2 MB

if ($archivo["size"] <= 0) {

    responderJSON(
        false,
        "El archivo seleccionado está vacío."
    );
}

if ($archivo["size"] > $maximoTamano) {

    responderJSON(
        false,
        "El logo no puede superar los 2 MB."
    );
}

//=====================================================
// VALIDAR ARCHIVO TEMPORAL
//=====================================================

if (
    !isset($archivo["tmp_name"]) ||
    !is_uploaded_file($archivo["tmp_name"])
) {

    responderJSON(
        false,
        "El archivo recibido no es válido."
    );
}

//=====================================================
// VALIDAR MIME REAL DE LA IMAGEN
//=====================================================

$tiposPermitidos = [
    "image/jpeg",
    "image/png",
    "image/webp"
];

$finfo = new finfo(FILEINFO_MIME_TYPE);

$mimeReal = $finfo->file($archivo["tmp_name"]);

if (!$mimeReal) {

    responderJSON(
        false,
        "No se pudo determinar el tipo de imagen."
    );
}

if (!in_array($mimeReal, $tiposPermitidos, true)) {

    responderJSON(
        false,
        "Formato no permitido. Solo se aceptan imágenes JPG, JPEG, PNG o WEBP."
    );
}

//=====================================================
// VALIDAR QUE REALMENTE SEA UNA IMAGEN
//=====================================================

$informacionImagen = @getimagesize($archivo["tmp_name"]);

if ($informacionImagen === false) {

    responderJSON(
        false,
        "El archivo seleccionado no es una imagen válida."
    );
}

//=====================================================
// VALIDAR MIME DE IMAGEN CON getimagesize
//=====================================================

$mimeImagen = $informacionImagen["mime"] ?? "";

if (!in_array($mimeImagen, $tiposPermitidos, true)) {

    responderJSON(
        false,
        "El contenido de la imagen no corresponde a un formato permitido."
    );
}

//=====================================================
// VALIDAR EXTENSIÓN
//=====================================================

$nombreOriginal = $archivo["name"] ?? "";

$extension = strtolower(
    pathinfo($nombreOriginal, PATHINFO_EXTENSION)
);

$extensionesPermitidas = [
    "jpg",
    "jpeg",
    "png",
    "webp"
];

if (!in_array($extension, $extensionesPermitidas, true)) {

    responderJSON(
        false,
        "La extensión del archivo no está permitida."
    );
}

//=====================================================
// VALIDAR RELACIÓN MIME / EXTENSIÓN
//=====================================================

$relacionMimeExtension = [
    "image/jpeg" => ["jpg", "jpeg"],
    "image/png"  => ["png"],
    "image/webp" => ["webp"]
];

if (
    !isset($relacionMimeExtension[$mimeReal]) ||
    !in_array(
        $extension,
        $relacionMimeExtension[$mimeReal],
        true
    )
) {

    responderJSON(
        false,
        "La extensión del archivo no coincide con su formato real."
    );
}

//=====================================================
// VERIFICAR QUE EL USUARIO EXISTA
//=====================================================

$sqlUsuario = "
    SELECT
        id_user
    FROM usuario_acceso
    WHERE id_user = ?
    LIMIT 1
";

$stmtUsuario = mysqli_prepare($conexion, $sqlUsuario);

if (!$stmtUsuario) {

    responderJSON(
        false,
        "No se pudo preparar la consulta del usuario."
    );
}

mysqli_stmt_bind_param(
    $stmtUsuario,
    "i",
    $idUser
);

if (!mysqli_stmt_execute($stmtUsuario)) {

    mysqli_stmt_close($stmtUsuario);

    responderJSON(
        false,
        "No se pudo verificar el usuario."
    );
}

$resultadoUsuario = mysqli_stmt_get_result($stmtUsuario);

if (!$resultadoUsuario || mysqli_num_rows($resultadoUsuario) === 0) {

    mysqli_stmt_close($stmtUsuario);

    responderJSON(
        false,
        "El usuario de la empresa no existe."
    );
}

mysqli_stmt_close($stmtUsuario);

//=====================================================
// LEER CONTENIDO BINARIO DEL LOGO
//=====================================================

$contenidoLogo = file_get_contents($archivo["tmp_name"]);

if ($contenidoLogo === false || $contenidoLogo === "") {

    responderJSON(
        false,
        "No se pudo leer el contenido del logo."
    );
}

//=====================================================
// ACTUALIZAR LOGO
//=====================================================

$sqlActualizar = "
    UPDATE usuario_acceso
    SET imagen = ?
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
        "No se pudo preparar la actualización del logo."
    );
}

//=====================================================
// BIND DEL BLOB
//=====================================================
//
// 'b' = binary
// 'i' = integer
//
// Se utiliza mysqli_stmt_send_long_data()
// para enviar correctamente el contenido
// binario del archivo.
//

$null = null;

mysqli_stmt_bind_param(
    $stmtActualizar,
    "bi",
    $null,
    $idUser
);

if (!mysqli_stmt_send_long_data(
    $stmtActualizar,
    0,
    $contenidoLogo
)) {

    mysqli_stmt_close($stmtActualizar);

    responderJSON(
        false,
        "No se pudo procesar el contenido del logo."
    );
}

//=====================================================
// EJECUTAR ACTUALIZACIÓN
//=====================================================

if (!mysqli_stmt_execute($stmtActualizar)) {

    $error = mysqli_stmt_error($stmtActualizar);

    mysqli_stmt_close($stmtActualizar);

    error_log(
        "Error actualizar_logo_empresa.php: " . $error
    );

    responderJSON(
        false,
        "No se pudo actualizar el logo de la empresa."
    );
}

//=====================================================
// VERIFICAR FILAS AFECTADAS
//=====================================================

$filasAfectadas = mysqli_stmt_affected_rows($stmtActualizar);

mysqli_stmt_close($stmtActualizar);

//=====================================================
// OBTENER LOGO ACTUALIZADO
//=====================================================
//
// Para que el JS pueda actualizar datos.logo,
// generamos una URL hacia mostrar_imagen.php
//
// Se utiliza el mismo id_user como referencia.
//

$logoURL = "../mostrar_imagen.php?id_user=" . $idUser;

//=====================================================
// RESPUESTA FINAL
//=====================================================

responderJSON(
    true,
    "El logo de la empresa se actualizó correctamente.",
    [
        "logo" => $logoURL,
        "id_user" => $idUser,
        "tamano" => $archivo["size"],
        "tipo" => $mimeReal,
        "actualizado" => $filasAfectadas > 0
    ]
);
