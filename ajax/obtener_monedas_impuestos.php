<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_monedas_impuestos.php
// Módulo: Monedas e Impuestos
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
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON($success, $message = "", $data = null)
{
    echo json_encode(
        [
            "success" => $success,
            "message" => $message,
            "configuracion" => $data
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
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
        "La sesión ha expirado. Debes iniciar sesión nuevamente."
    );
}

//=====================================================
// VALIDAR PETICIÓN AJAX
//=====================================================

if (
    isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
    strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) !== "xmlhttprequest"
) {

    responderJSON(
        false,
        "Solicitud no válida."
    );
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VERIFICAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos."
    );
}

//=====================================================
// CONFIGURACIÓN POR DEFECTO
//=====================================================

$configuracionDefecto = [

    "nombre_moneda" => "Sol peruano",

    "codigo_moneda" => "PEN",

    "simbolo_moneda" => "S/",

    "decimales" => 2,

    "separador_decimal" => ".",

    "separador_miles" => ",",

    "posicion_simbolo" => "ANTES",

    "impuesto_activo" => 1,

    "nombre_impuesto" => "IGV",

    "porcentaje_impuesto" => 18.00,

    "precios_incluyen_impuesto" => 0
];

//=====================================================
// CONSULTAR CONFIGURACIÓN
//=====================================================

$sql = "
    SELECT
        id_configuracion,
        id_user,
        nombre_moneda,
        codigo_moneda,
        simbolo_moneda,
        decimales,
        separador_decimal,
        separador_miles,
        posicion_simbolo,
        impuesto_activo,
        nombre_impuesto,
        porcentaje_impuesto,
        precios_incluyen_impuesto,
        fecha_actualizado
    FROM configuracion_monedas_impuestos
    WHERE id_user = ?
    ORDER BY id_configuracion DESC
    LIMIT 1
";

//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = $conexion->prepare($sql);

if (!$stmt) {

    error_log(
        "Error preparar obtener_monedas_impuestos.php: " .
            $conexion->error
    );

    responderJSON(
        false,
        "No se pudo consultar la configuración."
    );
}

//=====================================================
// BIND
//=====================================================

$stmt->bind_param(
    "i",
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!$stmt->execute()) {

    error_log(
        "Error ejecutar obtener_monedas_impuestos.php: " .
            $stmt->error
    );

    $stmt->close();

    responderJSON(
        false,
        "No se pudo obtener la configuración."
    );
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = $stmt->get_result();

if (!$resultado) {

    $stmt->close();

    responderJSON(
        false,
        "No se pudo procesar la configuración."
    );
}

//=====================================================
// ¿EXISTE CONFIGURACIÓN?
//=====================================================

if ($resultado->num_rows > 0) {

    $fila = $resultado->fetch_assoc();

    $stmt->close();

    //=================================================
    // NORMALIZAR TIPOS
    //=================================================

    $configuracion = [

        "nombre_moneda" => (string) $fila["nombre_moneda"],

        "codigo_moneda" => strtoupper(
            (string) $fila["codigo_moneda"]
        ),

        "simbolo_moneda" => (string) $fila["simbolo_moneda"],

        "decimales" => (int) $fila["decimales"],

        "separador_decimal" => (string) $fila["separador_decimal"],

        "separador_miles" => (string) $fila["separador_miles"],

        "posicion_simbolo" => (string) $fila["posicion_simbolo"],

        "impuesto_activo" => (int) $fila["impuesto_activo"],

        "nombre_impuesto" => (string) $fila["nombre_impuesto"],

        "porcentaje_impuesto" => (float) $fila["porcentaje_impuesto"],

        "precios_incluyen_impuesto" =>
        (int) $fila["precios_incluyen_impuesto"]
    ];

    responderJSON(
        true,
        "Configuración obtenida correctamente.",
        $configuracion
    );
}

//=====================================================
// NO EXISTE CONFIGURACIÓN
//=====================================================

$stmt->close();

responderJSON(
    true,
    "No existe una configuración guardada. Se utilizarán los valores predeterminados.",
    $configuracionDefecto
);
