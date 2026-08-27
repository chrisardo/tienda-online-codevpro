<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_proveedores_filtro.php
// Módulo: Productos del Proveedor
// Sistema: Inventa
//=====================================================

declare(strict_types=1);


//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=UTF-8");


//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(
    bool $success,
    string $mensaje = "",
    array $datos = [],
    int $codigoHTTP = 200
): void {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "mensaje" => $mensaje
            ],
            $datos
        ),
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
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
        "Sesión no válida. Debe iniciar sesión nuevamente.",
        [],
        401
    );
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [],
        500
    );
}


//=====================================================
// CONFIGURAR CHARSET
//=====================================================

$conexion->set_charset("utf8mb4");


//=====================================================
// CONSULTA PROVEEDORES
//=====================================================
//
// Solo se cargan proveedores:
// - Del usuario actual.
// - No eliminados.
//
// Se utiliza DISTINCT para evitar duplicados en caso
// de que existan registros inconsistentes.
//=====================================================

$sql = "
    SELECT DISTINCT

        p.id_provedor,
        p.nombre

    FROM provedores p

    WHERE
        p.id_user = ?
        AND p.Eliminado = 0

    ORDER BY
        p.nombre ASC,
        p.id_provedor ASC
";


//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = $conexion->prepare($sql);


if (!$stmt) {

    responderJSON(
        false,
        "No se pudo preparar la consulta de proveedores.",
        [],
        500
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

    $stmt->close();

    responderJSON(
        false,
        "No se pudieron obtener los proveedores.",
        [],
        500
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
        "No se pudo obtener el resultado de proveedores.",
        [],
        500
    );
}


//=====================================================
// ARRAY PROVEEDORES
//=====================================================

$proveedores = [];


//=====================================================
// RECORRER PROVEEDORES
//=====================================================

while ($fila = $resultado->fetch_assoc()) {

    $proveedores[] = [

        "id_provedor" => (int) $fila["id_provedor"],

        "nombre" => $fila["nombre"] ?? ""

    ];
}


//=====================================================
// CERRAR STATEMENT
//=====================================================

$stmt->close();


//=====================================================
// RESPUESTA FINAL
//=====================================================

responderJSON(
    true,
    "Proveedores obtenidos correctamente.",
    [
        "proveedores" => $proveedores
    ]
);
