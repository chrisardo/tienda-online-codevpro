<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_mi_empresa.php
// Módulo: Mi Empresa
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// INCLUIR CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responder($success, $message, $data = null)
{
    $respuesta = [
        "success" => $success,
        "message" => $message
    ];

    if ($data !== null) {
        $respuesta["data"] = $data;
    }

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responder(
        false,
        "Método de solicitud no permitido."
    );
}

//=====================================================
// OBTENER DATOS
//=====================================================

$idUser = isset($_POST["id_user"])
    ? (int) $_POST["id_user"]
    : 0;

$nombreEmpresa = isset($_POST["nombreEmpresa"])
    ? trim($_POST["nombreEmpresa"])
    : "";

$ruc = isset($_POST["ruc"])
    ? trim($_POST["ruc"])
    : "";

$email = isset($_POST["email"])
    ? trim($_POST["email"])
    : "";

$celular = isset($_POST["celular"])
    ? trim($_POST["celular"])
    : "";

$direccion = isset($_POST["direccion"])
    ? trim($_POST["direccion"])
    : "";

//=====================================================
// VALIDAR ID USUARIO
//=====================================================

if ($idUser <= 0) {

    responder(
        false,
        "El identificador del usuario no es válido."
    );
}

//=====================================================
// VALIDAR NOMBRE EMPRESA
//=====================================================

if ($nombreEmpresa === "") {

    responder(
        false,
        "Debes ingresar el nombre de la empresa."
    );
}

//=====================================================
// VALIDAR LONGITUD NOMBRE
//=====================================================

if (mb_strlen($nombreEmpresa, "UTF-8") > 255) {

    responder(
        false,
        "El nombre de la empresa no puede superar los 255 caracteres."
    );
}

//=====================================================
// VALIDAR EMAIL
//=====================================================

if ($email === "") {

    responder(
        false,
        "Debes ingresar el correo electrónico."
    );
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    responder(
        false,
        "El correo electrónico no es válido."
    );
}

//=====================================================
// VALIDAR LONGITUD EMAIL
//=====================================================

if (mb_strlen($email, "UTF-8") > 255) {

    responder(
        false,
        "El correo electrónico no puede superar los 255 caracteres."
    );
}

//=====================================================
// VALIDAR RUC
//=====================================================

if ($ruc !== "") {

    /*
     * El RUC peruano normalmente tiene 11 dígitos.
     * Permitimos vacío, pero si se registra debe
     * contener exactamente 11 dígitos.
     */

    if (!preg_match('/^\d{11}$/', $ruc)) {

        responder(
            false,
            "El RUC debe contener exactamente 11 dígitos."
        );
    }
}

//=====================================================
// VALIDAR CELULAR
//=====================================================

if ($celular !== "") {

    /*
     * En usuario_acceso el celular es VARCHAR,
     * por lo que no lo convertimos a entero.
     *
     * Permitimos números y algunos caracteres
     * habituales como +, espacios y guiones.
     */

    if (!preg_match('/^[0-9+\-\s()]+$/', $celular)) {

        responder(
            false,
            "El número de celular contiene caracteres no permitidos."
        );
    }

    if (mb_strlen($celular, "UTF-8") > 50) {

        responder(
            false,
            "El número de celular es demasiado largo."
        );
    }
}

//=====================================================
// VALIDAR DIRECCIÓN
//=====================================================

if (mb_strlen($direccion, "UTF-8") > 255) {

    responder(
        false,
        "La dirección no puede superar los 255 caracteres."
    );
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    responder(
        false,
        "No se pudo establecer conexión con la base de datos."
    );
}

//=====================================================
// ESTABLECER UTF-8
//=====================================================

if (!$conexion->set_charset("utf8mb4")) {

    responder(
        false,
        "No se pudo establecer la codificación de la base de datos."
    );
}

//=====================================================
// VERIFICAR QUE EL USUARIO EXISTA
//=====================================================

$sqlExiste = "
    SELECT
        id_user,
        nombreEmpresa,
        email,
        username,
        estado,
        fecha_registro
    FROM usuario_acceso
    WHERE id_user = ?
    LIMIT 1
";

$stmtExiste = $conexion->prepare($sqlExiste);

if (!$stmtExiste) {

    responder(
        false,
        "No se pudo preparar la consulta de verificación."
    );
}

$stmtExiste->bind_param(
    "i",
    $idUser
);

if (!$stmtExiste->execute()) {

    $stmtExiste->close();

    responder(
        false,
        "No se pudo verificar la empresa."
    );
}

$resultadoExiste = $stmtExiste->get_result();

if (!$resultadoExiste || $resultadoExiste->num_rows === 0) {

    $stmtExiste->close();

    responder(
        false,
        "No se encontró la empresa asociada al usuario."
    );
}

$empresaActual = $resultadoExiste->fetch_assoc();

$stmtExiste->close();

//=====================================================
// VERIFICAR EMAIL EN OTRO USUARIO
//=====================================================

$sqlEmail = "
    SELECT id_user
    FROM usuario_acceso
    WHERE email = ?
      AND id_user <> ?
    LIMIT 1
";

$stmtEmail = $conexion->prepare($sqlEmail);

if (!$stmtEmail) {

    responder(
        false,
        "No se pudo validar el correo electrónico."
    );
}

$stmtEmail->bind_param(
    "si",
    $email,
    $idUser
);

if (!$stmtEmail->execute()) {

    $stmtEmail->close();

    responder(
        false,
        "No se pudo validar la disponibilidad del correo electrónico."
    );
}

$resultadoEmail = $stmtEmail->get_result();

if ($resultadoEmail && $resultadoEmail->num_rows > 0) {

    $stmtEmail->close();

    responder(
        false,
        "El correo electrónico ya está registrado en otra cuenta."
    );
}

$stmtEmail->close();

//=====================================================
// ACTUALIZAR DATOS DE EMPRESA
//=====================================================

$sqlActualizar = "
    UPDATE usuario_acceso
    SET
        nombreEmpresa = ?,
        ruc = ?,
        email = ?,
        celular = ?,
        direccion = ?
    WHERE id_user = ?
    LIMIT 1
";

$stmtActualizar = $conexion->prepare($sqlActualizar);

if (!$stmtActualizar) {

    responder(
        false,
        "No se pudo preparar la actualización de la empresa."
    );
}

//=====================================================
// ASIGNAR PARÁMETROS
//=====================================================

$stmtActualizar->bind_param(
    "sssssi",
    $nombreEmpresa,
    $ruc,
    $email,
    $celular,
    $direccion,
    $idUser
);

//=====================================================
// EJECUTAR ACTUALIZACIÓN
//=====================================================

if (!$stmtActualizar->execute()) {

    $error = $stmtActualizar->error;

    $stmtActualizar->close();

    error_log(
        "Error actualizar_mi_empresa.php: " . $error
    );

    responder(
        false,
        "No se pudieron actualizar los datos de la empresa."
    );
}

//=====================================================
// CERRAR STATEMENT
//=====================================================

$filasAfectadas = $stmtActualizar->affected_rows;

$stmtActualizar->close();

//=====================================================
// OBTENER DATOS ACTUALIZADOS
//=====================================================

$sqlDatosActualizados = "
    SELECT
        id_user,
        nombreEmpresa,
        ruc,
        email,
        celular,
        direccion,
        username,
        estado,
        fecha_registro,
        rol
    FROM usuario_acceso
    WHERE id_user = ?
    LIMIT 1
";

$stmtDatos = $conexion->prepare($sqlDatosActualizados);

if (!$stmtDatos) {

    responder(
        false,
        "Los datos fueron actualizados, pero no se pudieron recuperar."
    );
}

$stmtDatos->bind_param(
    "i",
    $idUser
);

if (!$stmtDatos->execute()) {

    $stmtDatos->close();

    responder(
        false,
        "Los datos fueron actualizados, pero no se pudieron recuperar."
    );
}

$resultadoDatos = $stmtDatos->get_result();

if (!$resultadoDatos || $resultadoDatos->num_rows === 0) {

    $stmtDatos->close();

    responder(
        false,
        "Los datos fueron actualizados, pero no se pudieron recuperar."
    );
}

$datosActualizados = $resultadoDatos->fetch_assoc();

$stmtDatos->close();

//=====================================================
// RESPUESTA FINAL
//=====================================================

responder(
    true,
    $filasAfectadas > 0
        ? "La información de la empresa se actualizó correctamente."
        : "No se detectaron cambios en la información de la empresa.",
    [
        "id_user" => (int) $datosActualizados["id_user"],
        "nombreEmpresa" => $datosActualizados["nombreEmpresa"] ?? "",
        "ruc" => $datosActualizados["ruc"] ?? "",
        "email" => $datosActualizados["email"] ?? "",
        "celular" => $datosActualizados["celular"] ?? "",
        "direccion" => $datosActualizados["direccion"] ?? "",
        "username" => $datosActualizados["username"] ?? "",
        "estado" => $datosActualizados["estado"] ?? "",
        "fechaRegistro" => $datosActualizados["fecha_registro"] ?? "",
        "rol" => $datosActualizados["rol"] ?? ""
    ]
);
