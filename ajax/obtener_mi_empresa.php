<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_mi_empresa.php
// Módulo: Mi Empresa
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// RESPUESTA ESTÁNDAR
//=====================================================

$respuesta = [
    "success" => false,
    "message" => "",
    "data" => null
];

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUserSesion = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUserSesion <= 0) {

    $respuesta["message"] = "La sesión ha expirado. Inicia sesión nuevamente.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// OBTENER ID RECIBIDO
//=====================================================

$idUser = isset($_POST["id_user"])
    ? (int) $_POST["id_user"]
    : 0;

//=====================================================
// VALIDAR ID
//=====================================================

if ($idUser <= 0) {

    $respuesta["message"] = "El ID de usuario no es válido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// SEGURIDAD
//=====================================================
// El usuario solamente puede consultar su propia
// información. No permitimos consultar otro id_user
// enviado manualmente por POST.
//=====================================================

if ($idUser !== $idUserSesion) {

    $respuesta["message"] = "No tienes permiso para consultar esta empresa.";

    echo json_encode(
        $respuesta,
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

    $respuesta["message"] = "No se pudo establecer conexión con la base de datos.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// CONFIGURAR UTF-8
//=====================================================

mysqli_set_charset($conexion, "utf8mb4");

//=====================================================
// CONSULTAR INFORMACIÓN DE LA EMPRESA
//=====================================================
//
// usuario_acceso:
// - id_user
// - nombreEmpresa
// - email
// - username
// - imagen
// - direccion
// - celular
// - estado
// - fecha_registro
// - ruc
// - rol
//
// rol:
// - id_rol
// - nombre
// - id_user
//
// Se utiliza LEFT JOIN porque la empresa debe poder
// cargarse aunque todavía no tenga un rol asociado.
//
//=====================================================

$sql = "
    SELECT
        ua.id_user,
        ua.nombreEmpresa,
        ua.email,
        ua.username,
        ua.imagen,
        ua.direccion,
        ua.celular,
        ua.estado,
        ua.fecha_registro,
        ua.ruc,
        ua.rol AS id_rol,

        r.nombre AS nombre_rol

    FROM usuario_acceso AS ua

    LEFT JOIN rol AS r
        ON r.id_rol = ua.rol
        AND r.id_user = ua.id_user

    WHERE ua.id_user = ?

    LIMIT 1
";

//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    $respuesta["message"] =
        "No se pudo preparar la consulta de la empresa.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VINCULAR ID
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    $respuesta["message"] =
        "No se pudo consultar la información de la empresa.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {

    mysqli_stmt_close($stmt);

    $respuesta["message"] =
        "No se pudo obtener el resultado de la consulta.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VERIFICAR EMPRESA
//=====================================================

if (mysqli_num_rows($resultado) === 0) {

    mysqli_stmt_close($stmt);

    $respuesta["message"] =
        "No se encontró información de la empresa.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// OBTENER DATOS
//=====================================================

$empresa = mysqli_fetch_assoc($resultado);

//=====================================================
// CERRAR CONSULTA
//=====================================================

mysqli_stmt_close($stmt);

//=====================================================
// PROCESAR LOGO
//=====================================================

$tieneLogo = false;
$logo = null;

//=====================================================
// VALIDAR BLOB
//=====================================================

if (
    isset($empresa["imagen"]) &&
    $empresa["imagen"] !== null &&
    $empresa["imagen"] !== ""
) {

    $imagenBinaria = $empresa["imagen"];

    //=================================================
    // DETECTAR TIPO MIME
    //=================================================

    $mime = null;

    if (function_exists("finfo_open")) {

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo) {

            $mimeDetectado = finfo_buffer(
                $finfo,
                $imagenBinaria
            );

            if ($mimeDetectado) {

                $mime = $mimeDetectado;
            }

            finfo_close($finfo);
        }
    }

    //=================================================
    // SEGUNDA OPCIÓN: getimagesizefromstring
    //=================================================

    if (!$mime && function_exists("getimagesizefromstring")) {

        $informacionImagen = @getimagesizefromstring(
            $imagenBinaria
        );

        if (
            $informacionImagen &&
            isset($informacionImagen["mime"])
        ) {

            $mime = $informacionImagen["mime"];
        }
    }

    //=================================================
    // VALIDAR MIME PERMITIDO
    //=================================================

    $tiposPermitidos = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    if (
        $mime &&
        in_array($mime, $tiposPermitidos, true)
    ) {

        $logo = "data:" .
            $mime .
            ";base64," .
            base64_encode($imagenBinaria);

        $tieneLogo = true;
    }
}

//=====================================================
// NORMALIZAR ESTADO
//=====================================================

$estado = isset($empresa["estado"])
    ? trim((string) $empresa["estado"])
    : "ACTIVO";

if ($estado === "") {

    $estado = "ACTIVO";
}

//=====================================================
// NORMALIZAR ROL
//=====================================================

$nombreRol = isset($empresa["nombre_rol"])
    ? trim((string) $empresa["nombre_rol"])
    : "";

if ($nombreRol === "") {

    $nombreRol = "Administrador";
}

//=====================================================
// NORMALIZAR FECHA
//=====================================================

$fechaRegistro = isset($empresa["fecha_registro"])
    ? trim((string) $empresa["fecha_registro"])
    : "";

//=====================================================
// PREPARAR DATA
//=====================================================

$datosEmpresa = [

    "idUser" => (int) $empresa["id_user"],

    "nombreEmpresa" =>
    isset($empresa["nombreEmpresa"])
        ? trim((string) $empresa["nombreEmpresa"])
        : "",

    "ruc" =>
    isset($empresa["ruc"])
        ? trim((string) $empresa["ruc"])
        : "",

    "email" =>
    isset($empresa["email"])
        ? trim((string) $empresa["email"])
        : "",

    "celular" =>
    isset($empresa["celular"])
        ? trim((string) $empresa["celular"])
        : "",

    "direccion" =>
    isset($empresa["direccion"])
        ? trim((string) $empresa["direccion"])
        : "",

    "username" =>
    isset($empresa["username"])
        ? trim((string) $empresa["username"])
        : "",

    "rol" => $nombreRol,

    "idRol" =>
    isset($empresa["id_rol"])
        ? (int) $empresa["id_rol"]
        : 0,

    "fechaRegistro" => $fechaRegistro,

    "estado" => $estado,

    "tieneLogo" => $tieneLogo,

    "logo" => $logo
];

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

$respuesta["success"] = true;

$respuesta["message"] =
    "Información de la empresa obtenida correctamente.";

$respuesta["data"] = $datosEmpresa;

//=====================================================
// RESPONDER JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
);

//=====================================================
// CERRAR CONEXIÓN
//=====================================================

mysqli_close($conexion);
