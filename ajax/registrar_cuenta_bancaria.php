<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/registrar_cuenta_bancaria.php
// Módulo: Cuentas Bancarias
// Sistema: Inventa
//=====================================================

"use strict";

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
// RESPUESTA ESTÁNDAR
//=====================================================

$respuesta = [
    "success" => false,
    "mensaje" => "No se pudo registrar la cuenta bancaria."
];

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    $respuesta["mensaje"] = "Método de solicitud no permitido.";

    http_response_code(405);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR USUARIO AUTENTICADO
//=====================================================
//
// Se utiliza el id_user de la sesión.
// No se recibe desde el formulario por seguridad.
//

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUser <= 0) {

    $respuesta["mensaje"] = "La sesión ha expirado. Inicia sesión nuevamente.";

    http_response_code(401);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// CONEXIÓN A LA BASE DE DATOS
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    $respuesta["mensaje"] = "No se pudo establecer conexión con la base de datos.";

    http_response_code(500);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// CONFIGURAR CHARSET
//=====================================================

$conexion->set_charset("utf8mb4");

//=====================================================
// RECIBIR DATOS
//=====================================================

$nombre = isset($_POST["nombre"])
    ? trim($_POST["nombre"])
    : "";

$balanceRecibido = isset($_POST["balance"])
    ? trim($_POST["balance"])
    : "";

//=====================================================
// VALIDAR NOMBRE
//=====================================================

if ($nombre === "") {

    $respuesta["mensaje"] = "El nombre de la cuenta es obligatorio.";

    http_response_code(400);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// LIMPIAR NOMBRE
//=====================================================

$nombre = preg_replace('/\s+/', ' ', $nombre);

$nombre = trim($nombre);

//=====================================================
// VALIDAR LONGITUD
//=====================================================

if (mb_strlen($nombre, "UTF-8") > 100) {

    $respuesta["mensaje"] = "El nombre de la cuenta no puede superar los 100 caracteres.";

    http_response_code(400);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR BALANCE
//=====================================================

if ($balanceRecibido === "") {

    $respuesta["mensaje"] = "El balance inicial es obligatorio.";

    http_response_code(400);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR FORMATO NUMÉRICO
//=====================================================
//
// El input type="number" normalmente enviará:
//
// 0
// 0.00
// 150.50
//
// Se acepta también una representación decimal válida.
//

if (!is_numeric($balanceRecibido)) {

    $respuesta["mensaje"] = "El balance inicial debe ser un valor numérico.";

    http_response_code(400);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$balance = (float) $balanceRecibido;

//=====================================================
// VALIDAR BALANCE NEGATIVO
//=====================================================

if ($balance < 0) {

    $respuesta["mensaje"] = "El balance inicial no puede ser negativo.";

    http_response_code(400);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// REDONDEAR BALANCE
//=====================================================
//
// La tabla utiliza FLOAT.
//

$balance = round($balance, 2);

//=====================================================
// VALIDAR NOMBRE DUPLICADO
//=====================================================
//
// Solo se consideran cuentas activas.
//
// Una cuenta desactivada (Eliminado = 1)
// puede volver a utilizarse con el mismo nombre.
//

$sqlVerificar = "
    SELECT
        id_cuenta_bancaria
    FROM cuenta_banco
    WHERE nombre = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";

$stmtVerificar = $conexion->prepare($sqlVerificar);

if (!$stmtVerificar) {

    $respuesta["mensaje"] = "No se pudo validar la cuenta bancaria.";

    http_response_code(500);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$stmtVerificar->bind_param(
    "si",
    $nombre,
    $idUser
);

if (!$stmtVerificar->execute()) {

    $stmtVerificar->close();

    $respuesta["mensaje"] = "Ocurrió un error al validar la cuenta bancaria.";

    http_response_code(500);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$resultadoVerificar = $stmtVerificar->get_result();

$cuentaExistente = $resultadoVerificar->fetch_assoc();

$stmtVerificar->close();

//=====================================================
// CUENTA YA EXISTENTE
//=====================================================

if ($cuentaExistente) {

    $respuesta["mensaje"] =
        "Ya existe una cuenta bancaria activa con ese nombre.";

    http_response_code(409);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// INSERTAR CUENTA
//=====================================================
//
// Estructura:
//
// id_cuenta_bancaria -> AUTO_INCREMENT
// nombre             -> nombre de cuenta
// balance            -> saldo inicial
// Eliminado           -> 0 = activa
// id_user             -> usuario propietario
//

$sqlInsertar = "
    INSERT INTO cuenta_banco
    (
        nombre,
        balance,
        Eliminado,
        id_user
    )
    VALUES
    (
        ?,
        ?,
        0,
        ?
    )
";

$stmtInsertar = $conexion->prepare($sqlInsertar);

if (!$stmtInsertar) {

    $respuesta["mensaje"] = "No se pudo preparar el registro de la cuenta.";

    http_response_code(500);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// ASIGNAR PARÁMETROS
//=====================================================

$stmtInsertar->bind_param(
    "sdi",
    $nombre,
    $balance,
    $idUser
);

//=====================================================
// EJECUTAR INSERT
//=====================================================

if (!$stmtInsertar->execute()) {

    $error = $stmtInsertar->error;

    $stmtInsertar->close();

    error_log(
        "Error registrar cuenta bancaria: " . $error
    );

    $respuesta["mensaje"] =
        "No se pudo registrar la cuenta bancaria.";

    http_response_code(500);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// OBTENER ID REGISTRADO
//=====================================================

$idCuentaBancaria = $conexion->insert_id;

//=====================================================
// CERRAR STATEMENT
//=====================================================

$stmtInsertar->close();

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

$respuesta = [
    "success" => true,
    "mensaje" => "La cuenta bancaria se registró correctamente.",
    "id_cuenta_bancaria" => (int) $idCuentaBancaria,
    "cuenta" => [
        "id_cuenta_bancaria" => (int) $idCuentaBancaria,
        "nombre" => $nombre,
        "balance" => $balance,
        "Eliminado" => 0,
        "id_user" => $idUser
    ]
];

//=====================================================
// RESPONDER JSON
//=====================================================

http_response_code(200);

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
);

exit;
