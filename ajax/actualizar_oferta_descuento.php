<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_oferta_descuento.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// CONFIGURACIÓN
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

require_once "../controladores/conexion.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responder($success, $mensaje, $datos = [])
{
    echo json_encode(
        [
            "success" => $success,
            "mensaje" => $mensaje,
            "datos"   => $datos
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VERIFICAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responder(
        false,
        "Método de solicitud no permitido."
    );
}

//=====================================================
// OBTENER USUARIO
//=====================================================

$idUser = 0;

//-----------------------------------------------------
// Intentar obtener el usuario desde las variables
// de sesión utilizadas normalmente en Inventa.
//-----------------------------------------------------

if (isset($_SESSION["id_user"])) {

    $idUser = (int) $_SESSION["id_user"];
} elseif (isset($_SESSION["idUser"])) {

    $idUser = (int) $_SESSION["idUser"];
} elseif (isset($_SESSION["usuario"]["id_user"])) {

    $idUser = (int) $_SESSION["usuario"]["id_user"];
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

if ($idUser <= 0) {

    responder(
        false,
        "La sesión del usuario no es válida. Inicie sesión nuevamente."
    );
}

//=====================================================
// OBTENER DATOS POST
//=====================================================

$idProducto = isset($_POST["idProducto"])
    ? (int) $_POST["idProducto"]
    : 0;

$precio = isset($_POST["precio"])
    ? trim($_POST["precio"])
    : "";

$precioAnterior = isset($_POST["precio_anterior"])
    ? trim($_POST["precio_anterior"])
    : "";

$descuento = isset($_POST["descuento"])
    ? trim($_POST["descuento"])
    : "";

$oferta = isset($_POST["oferta"])
    ? (int) $_POST["oferta"]
    : 0;

//=====================================================
// VALIDAR ID PRODUCTO
//=====================================================

if ($idProducto <= 0) {

    responder(
        false,
        "El producto seleccionado no es válido."
    );
}

//=====================================================
// VALIDAR PRECIO
//=====================================================

if ($precio === "" || !is_numeric($precio)) {

    responder(
        false,
        "Ingrese un precio válido."
    );
}

$precio = (float) $precio;

if ($precio < 0) {

    responder(
        false,
        "El precio no puede ser negativo."
    );
}

//=====================================================
// VALIDAR PRECIO ANTERIOR
//=====================================================

if ($precioAnterior === "") {

    $precioAnterior = 0;
} elseif (!is_numeric($precioAnterior)) {

    responder(
        false,
        "El precio anterior no es válido."
    );
} else {

    $precioAnterior = (float) $precioAnterior;
}

if ($precioAnterior < 0) {

    responder(
        false,
        "El precio anterior no puede ser negativo."
    );
}

//=====================================================
// VALIDAR DESCUENTO
//=====================================================

if ($descuento === "" || !is_numeric($descuento)) {

    $descuento = 0;
} else {

    $descuento = (float) $descuento;
}

//-----------------------------------------------------
// El descuento debe estar entre 0 y 100.
//-----------------------------------------------------

if ($descuento < 0 || $descuento > 100) {

    responder(
        false,
        "El descuento debe estar entre 0% y 100%."
    );
}

//=====================================================
// NORMALIZAR OFERTA
//=====================================================

$oferta = ($oferta === 1) ? 1 : 0;

//=====================================================
// REGLAS DE NEGOCIO
//=====================================================

//-----------------------------------------------------
// Si la oferta está desactivada, no debe existir
// descuento activo.
//-----------------------------------------------------

if ($oferta === 0) {

    $descuento = 0;
}

//-----------------------------------------------------
// Si hay oferta activa, el descuento debe ser mayor
// que cero.
//-----------------------------------------------------

if ($oferta === 1 && $descuento <= 0) {

    responder(
        false,
        "Para activar una oferta debe ingresar un descuento mayor a 0%."
    );
}

//=====================================================
// CALCULAR PRECIO FINAL
//=====================================================

$montoDescuento = $precio * ($descuento / 100);

$precioFinal = $precio - $montoDescuento;

//-----------------------------------------------------
// Redondear a dos decimales.
//-----------------------------------------------------

$montoDescuento = round($montoDescuento, 2);

$precioFinal = round($precioFinal, 2);

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    responder(
        false,
        "No se pudo conectar con la base de datos."
    );
}

//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

mysqli_begin_transaction($conexion);

try {

    //=================================================
    // VERIFICAR QUE EL PRODUCTO EXISTA
    // Y PERTENEZCA AL USUARIO
    //=================================================

    $sqlVerificar = "
        SELECT
            idProducto,
            nombre,
            precio,
            precio_anterior,
            descuento,
            oferta,
            Eliminado
        FROM producto
        WHERE idProducto = ?
          AND id_user = ?
        LIMIT 1
    ";

    $stmtVerificar = mysqli_prepare(
        $conexion,
        $sqlVerificar
    );

    if (!$stmtVerificar) {

        throw new Exception(
            "No se pudo preparar la consulta de verificación."
        );
    }

    mysqli_stmt_bind_param(
        $stmtVerificar,
        "ii",
        $idProducto,
        $idUser
    );

    if (!mysqli_stmt_execute($stmtVerificar)) {

        throw new Exception(
            "No se pudo verificar el producto."
        );
    }

    $resultado = mysqli_stmt_get_result($stmtVerificar);

    if (!$resultado) {

        throw new Exception(
            "No se pudo obtener la información del producto."
        );
    }

    $producto = mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmtVerificar);

    //=================================================
    // PRODUCTO NO EXISTE
    //=================================================

    if (!$producto) {

        throw new Exception(
            "El producto no existe o no pertenece a su empresa."
        );
    }

    //=================================================
    // VERIFICAR ELIMINADO
    //=================================================

    if ((int)$producto["Eliminado"] === 1) {

        throw new Exception(
            "El producto seleccionado se encuentra eliminado."
        );
    }

    //=================================================
    // VALIDAR PRECIO FINAL
    //=================================================

    if ($precioFinal < 0) {

        throw new Exception(
            "El precio final calculado no puede ser negativo."
        );
    }

    //=================================================
    // ACTUALIZAR PRODUCTO
    //=================================================

    $sqlActualizar = "
        UPDATE producto
        SET
            precio = ?,
            precio_anterior = ?,
            descuento = ?,
            oferta = ?,
            fecha_actualizado = CURDATE()
        WHERE idProducto = ?
          AND id_user = ?
          AND Eliminado = 0
    ";

    $stmtActualizar = mysqli_prepare(
        $conexion,
        $sqlActualizar
    );

    if (!$stmtActualizar) {

        throw new Exception(
            "No se pudo preparar la actualización del producto."
        );
    }

    mysqli_stmt_bind_param(
        $stmtActualizar,
        "dddiii",
        $precio,
        $precioAnterior,
        $descuento,
        $oferta,
        $idProducto,
        $idUser
    );

    if (!mysqli_stmt_execute($stmtActualizar)) {

        throw new Exception(
            "No se pudo actualizar la oferta y descuento."
        );
    }

    //=================================================
    // VERIFICAR ACTUALIZACIÓN
    //=================================================

    $filasAfectadas = mysqli_stmt_affected_rows(
        $stmtActualizar
    );

    mysqli_stmt_close($stmtActualizar);

    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    mysqli_commit($conexion);

    //=================================================
    // RESPUESTA
    //=================================================

    responder(
        true,
        "La oferta y el descuento se actualizaron correctamente.",
        [
            "idProducto"       => $idProducto,
            "precio"           => number_format($precio, 2, ".", ""),
            "precio_anterior"  => number_format($precioAnterior, 2, ".", ""),
            "descuento"        => number_format($descuento, 2, ".", ""),
            "oferta"           => $oferta,
            "monto_descuento"  => number_format($montoDescuento, 2, ".", ""),
            "precio_final"     => number_format($precioFinal, 2, ".", ""),
            "filas_afectadas"  => $filasAfectadas
        ]
    );
} catch (Throwable $e) {

    //=================================================
    // DESHACER CAMBIOS
    //=================================================

    mysqli_rollback($conexion);

    //=================================================
    // LOG DEL ERROR
    //=================================================

    error_log(
        "Error actualizar_oferta_descuento.php: " .
            $e->getMessage()
    );

    //=================================================
    // RESPUESTA
    //=================================================

    responder(
        false,
        $e->getMessage()
    );
}
