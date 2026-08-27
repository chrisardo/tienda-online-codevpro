<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/cambiar_estado_oferta.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

session_start();

//=====================================================
// CONFIGURACIÓN
//=====================================================

header(
    "Content-Type: application/json; charset=UTF-8"
);

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(
    $success,
    $mensaje,
    $datos = []
) {

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

    exit();
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

if (
    !isset($_SESSION["idUser"]) ||
    empty($_SESSION["idUser"])
) {

    responderJSON(
        false,
        "Sesión no válida."
    );
}

//=====================================================
// ID USUARIO
//=====================================================

$idUser =
    (int) $_SESSION["idUser"];

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !$conexion
) {

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos."
    );
}

//=====================================================
// CHARSET
//=====================================================

if (
    !$conexion->set_charset("utf8mb4")
) {

    responderJSON(
        false,
        "No se pudo configurar el charset de la conexión."
    );
}

//=====================================================
// RECIBIR ID PRODUCTO
//=====================================================

$idProducto = isset($_POST["idProducto"])
    ? (int) $_POST["idProducto"]
    : 0;

//=====================================================
// RECIBIR ESTADO
//=====================================================
//
// 1 = Activar oferta
// 0 = Desactivar oferta
//
//=====================================================

$oferta = isset($_POST["oferta"])
    ? (int) $_POST["oferta"]
    : -1;

//=====================================================
// VALIDAR ID PRODUCTO
//=====================================================

if ($idProducto <= 0) {

    responderJSON(
        false,
        "El producto seleccionado no es válido."
    );
}

//=====================================================
// VALIDAR ESTADO
//=====================================================

if (
    $oferta !== 0 &&
    $oferta !== 1
) {

    responderJSON(
        false,
        "El estado de la oferta no es válido."
    );
}

//=====================================================
// VERIFICAR PRODUCTO
//=====================================================
//
// Se verifica:
// - Que exista
// - Que pertenezca al usuario actual
// - Que no esté eliminado
//
//=====================================================

$sqlVerificar = "

    SELECT
        idProducto,
        nombre,
        oferta

    FROM producto

    WHERE
        idProducto = ?
        AND id_user = ?
        AND Eliminado = 0

    LIMIT 1

";

//=====================================================
// PREPARAR
//=====================================================

$stmtVerificar =
    $conexion->prepare(
        $sqlVerificar
    );

if (!$stmtVerificar) {

    responderJSON(
        false,
        "No se pudo verificar el producto.",
        [
            "error" =>
            $conexion->error
        ]
    );
}

//=====================================================
// BIND
//=====================================================

$stmtVerificar->bind_param(
    "ii",
    $idProducto,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (
    !$stmtVerificar->execute()
) {

    $error =
        $stmtVerificar->error;

    $stmtVerificar->close();

    responderJSON(
        false,
        "No se pudo verificar el producto.",
        [
            "error" => $error
        ]
    );
}

//=====================================================
// RESULTADO
//=====================================================

$resultadoVerificar =
    $stmtVerificar->get_result();

$producto =
    $resultadoVerificar->fetch_assoc();

$stmtVerificar->close();

//=====================================================
// PRODUCTO NO EXISTE
//=====================================================

if (!$producto) {

    responderJSON(
        false,
        "El producto no existe, fue eliminado o no pertenece a tu cuenta."
    );
}

//=====================================================
// DATOS ANTERIORES
//=====================================================

$estadoAnterior =
    (int) (
        $producto["oferta"] ?? 0
    );

$nombreProducto =
    $producto["nombre"] ?? "";

//=====================================================
// ACTUALIZAR OFERTA
//=====================================================

$sqlActualizar = "

    UPDATE producto

    SET
        oferta = ?,
        fecha_actualizado = NOW()

    WHERE
        idProducto = ?
        AND id_user = ?
        AND Eliminado = 0

    LIMIT 1

";

//=====================================================
// PREPARAR UPDATE
//=====================================================

$stmtActualizar =
    $conexion->prepare(
        $sqlActualizar
    );

if (!$stmtActualizar) {

    responderJSON(
        false,
        "No se pudo preparar la actualización de la oferta.",
        [
            "error" =>
            $conexion->error
        ]
    );
}

//=====================================================
// BIND
//=====================================================

$stmtActualizar->bind_param(
    "iii",
    $oferta,
    $idProducto,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (
    !$stmtActualizar->execute()
) {

    $error =
        $stmtActualizar->error;

    $stmtActualizar->close();

    responderJSON(
        false,
        "No se pudo actualizar el estado de la oferta.",
        [
            "error" => $error
        ]
    );
}

//=====================================================
// FILAS AFECTADAS
//=====================================================

$filasAfectadas =
    $stmtActualizar->affected_rows;

$stmtActualizar->close();

//=====================================================
// MENSAJE
//=====================================================

if ($oferta === 1) {

    $mensaje =
        "La oferta fue activada correctamente.";
} else {

    $mensaje =
        "La oferta fue desactivada correctamente.";
}

//=====================================================
// RESPUESTA
//=====================================================

responderJSON(
    true,
    $mensaje,
    [

        "idProducto" =>
        $idProducto,

        "nombre" =>
        $nombreProducto,

        "oferta" =>
        $oferta,

        "estado_anterior" =>
        $estadoAnterior,

        "filas_afectadas" =>
        $filasAfectadas

    ]
);
