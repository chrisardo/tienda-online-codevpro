<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/guardar_monedas_impuestos.php
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
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responderJSON(
        false,
        "Método de solicitud no permitido."
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
// OBTENER DATOS
//=====================================================

$nombreMoneda = isset($_POST["nombre_moneda"])
    ? trim($_POST["nombre_moneda"])
    : "";

$codigoMoneda = isset($_POST["codigo_moneda"])
    ? strtoupper(trim($_POST["codigo_moneda"]))
    : "";

$simboloMoneda = isset($_POST["simbolo_moneda"])
    ? trim($_POST["simbolo_moneda"])
    : "";

$decimales = isset($_POST["decimales"])
    ? filter_var(
        $_POST["decimales"],
        FILTER_VALIDATE_INT
    )
    : false;

$separadorDecimal = isset($_POST["separador_decimal"])
    ? (string) $_POST["separador_decimal"]
    : "";

$separadorMiles = isset($_POST["separador_miles"])
    ? (string) $_POST["separador_miles"]
    : "";

$posicionSimbolo = isset($_POST["posicion_simbolo"])
    ? strtoupper(trim($_POST["posicion_simbolo"]))
    : "";

$impuestoActivo = isset($_POST["impuesto_activo"])
    ? (int) $_POST["impuesto_activo"]
    : 0;

$nombreImpuesto = isset($_POST["nombre_impuesto"])
    ? trim($_POST["nombre_impuesto"])
    : "";

$porcentajeImpuesto = isset($_POST["porcentaje_impuesto"])
    ? (float) $_POST["porcentaje_impuesto"]
    : 0;

$preciosIncluyenImpuesto = isset(
    $_POST["precios_incluyen_impuesto"]
)
    ? (int) $_POST["precios_incluyen_impuesto"]
    : 0;

//=====================================================
// NORMALIZAR BOOLEANOS
//=====================================================

$impuestoActivo = $impuestoActivo === 1
    ? 1
    : 0;

$preciosIncluyenImpuesto = $preciosIncluyenImpuesto === 1
    ? 1
    : 0;

//=====================================================
// NORMALIZAR DECIMALES
//=====================================================

if ($decimales === false) {

    responderJSON(
        false,
        "La cantidad de decimales no es válida."
    );
}

//=====================================================
// NORMALIZAR PORCENTAJE
//=====================================================

$porcentajeImpuesto = round(
    $porcentajeImpuesto,
    2
);

//=====================================================
// VALIDACIONES
//=====================================================

//-----------------------------------------------------
// NOMBRE MONEDA
//-----------------------------------------------------

if ($nombreMoneda === "") {

    responderJSON(
        false,
        "Debes ingresar el nombre de la moneda."
    );
}

if (mb_strlen($nombreMoneda, "UTF-8") > 100) {

    responderJSON(
        false,
        "El nombre de la moneda no puede superar los 100 caracteres."
    );
}

//-----------------------------------------------------
// CÓDIGO MONEDA
//-----------------------------------------------------

if ($codigoMoneda === "") {

    responderJSON(
        false,
        "Debes ingresar el código de la moneda."
    );
}

if (!preg_match(
    '/^[A-Z0-9]{1,10}$/',
    $codigoMoneda
)) {

    responderJSON(
        false,
        "El código de moneda solo puede contener letras mayúsculas y números."
    );
}

//-----------------------------------------------------
// SÍMBOLO
//-----------------------------------------------------

if ($simboloMoneda === "") {

    responderJSON(
        false,
        "Debes ingresar el símbolo de la moneda."
    );
}

if (mb_strlen($simboloMoneda, "UTF-8") > 10) {

    responderJSON(
        false,
        "El símbolo de moneda no puede superar los 10 caracteres."
    );
}

//-----------------------------------------------------
// DECIMALES
//-----------------------------------------------------

if ($decimales < 0 || $decimales > 4) {

    responderJSON(
        false,
        "La cantidad de decimales debe estar entre 0 y 4."
    );
}

//-----------------------------------------------------
// SEPARADOR DECIMAL
//-----------------------------------------------------

$separadoresPermitidos = [
    ".",
    ","
];

if (!in_array(
    $separadorDecimal,
    $separadoresPermitidos,
    true
)) {

    responderJSON(
        false,
        "El separador decimal seleccionado no es válido."
    );
}

//-----------------------------------------------------
// SEPARADOR MILES
//-----------------------------------------------------

$separadoresMilesPermitidos = [
    ",",
    ".",
    " "
];

if (!in_array(
    $separadorMiles,
    $separadoresMilesPermitidos,
    true
)) {

    responderJSON(
        false,
        "El separador de miles seleccionado no es válido."
    );
}

//-----------------------------------------------------
// SEPARADORES IGUALES
//-----------------------------------------------------

if ($separadorDecimal === $separadorMiles) {

    responderJSON(
        false,
        "El separador decimal y el separador de miles no pueden ser iguales."
    );
}

//-----------------------------------------------------
// POSICIÓN DEL SÍMBOLO
//-----------------------------------------------------

if (
    $posicionSimbolo !== "ANTES" &&
    $posicionSimbolo !== "DESPUES"
) {

    responderJSON(
        false,
        "La posición del símbolo no es válida."
    );
}

//-----------------------------------------------------
// IMPUESTO ACTIVO
//-----------------------------------------------------

if (
    $impuestoActivo !== 0 &&
    $impuestoActivo !== 1
) {

    responderJSON(
        false,
        "El estado del impuesto no es válido."
    );
}

//-----------------------------------------------------
// NOMBRE IMPUESTO
//-----------------------------------------------------

if ($impuestoActivo === 1) {

    if ($nombreImpuesto === "") {

        responderJSON(
            false,
            "Debes ingresar el nombre del impuesto."
        );
    }

    if (mb_strlen($nombreImpuesto, "UTF-8") > 100) {

        responderJSON(
            false,
            "El nombre del impuesto no puede superar los 100 caracteres."
        );
    }
} else {

    /*
     * Si el impuesto está desactivado no es obligatorio
     * que exista un nombre.
     *
     * Sin embargo, conservamos el valor recibido si existe.
     */

    if (mb_strlen($nombreImpuesto, "UTF-8") > 100) {

        responderJSON(
            false,
            "El nombre del impuesto no puede superar los 100 caracteres."
        );
    }
}

//-----------------------------------------------------
// PORCENTAJE IMPUESTO
//-----------------------------------------------------

if (
    $porcentajeImpuesto < 0 ||
    $porcentajeImpuesto > 100
) {

    responderJSON(
        false,
        "El porcentaje del impuesto debe estar entre 0 y 100."
    );
}

//=====================================================
// TRANSACCIÓN
//=====================================================

if (!$conexion->begin_transaction()) {

    error_log(
        "Error iniciando transacción guardar_monedas_impuestos.php: " .
            $conexion->error
    );

    responderJSON(
        false,
        "No se pudo iniciar la operación."
    );
}

try {

    //=================================================
    // BUSCAR CONFIGURACIÓN DEL USUARIO
    //=================================================

    $sqlBuscar = "
        SELECT
            id_configuracion
        FROM configuracion_monedas_impuestos
        WHERE id_user = ?
        ORDER BY id_configuracion DESC
        LIMIT 1
        FOR UPDATE
    ";

    $stmtBuscar = $conexion->prepare($sqlBuscar);

    if (!$stmtBuscar) {

        throw new Exception(
            "No se pudo consultar la configuración."
        );
    }

    $stmtBuscar->bind_param(
        "i",
        $idUser
    );

    if (!$stmtBuscar->execute()) {

        $stmtBuscar->close();

        throw new Exception(
            "No se pudo consultar la configuración."
        );
    }

    $resultadoBuscar = $stmtBuscar->get_result();

    $idConfiguracion = 0;

    if ($resultadoBuscar && $resultadoBuscar->num_rows > 0) {

        $fila = $resultadoBuscar->fetch_assoc();

        $idConfiguracion = (int) $fila["id_configuracion"];
    }

    $stmtBuscar->close();

    //=================================================
    // FECHA ACTUAL
    //=================================================

    $fechaActualizado = date("Y-m-d H:i:s");

    //=================================================
    // ACTUALIZAR
    //=================================================

    if ($idConfiguracion > 0) {

        $sqlActualizar = "
            UPDATE configuracion_monedas_impuestos
            SET
                nombre_moneda = ?,
                codigo_moneda = ?,
                simbolo_moneda = ?,
                decimales = ?,
                separador_decimal = ?,
                separador_miles = ?,
                posicion_simbolo = ?,
                impuesto_activo = ?,
                nombre_impuesto = ?,
                porcentaje_impuesto = ?,
                precios_incluyen_impuesto = ?,
                fecha_actualizado = ?
            WHERE
                id_configuracion = ?
                AND id_user = ?
        ";

        $stmtActualizar = $conexion->prepare(
            $sqlActualizar
        );

        if (!$stmtActualizar) {

            throw new Exception(
                "No se pudo preparar la actualización."
            );
        }

        $stmtActualizar->bind_param(
            "sssisssisdisii",
            $nombreMoneda,
            $codigoMoneda,
            $simboloMoneda,
            $decimales,
            $separadorDecimal,
            $separadorMiles,
            $posicionSimbolo,
            $impuestoActivo,
            $nombreImpuesto,
            $porcentajeImpuesto,
            $preciosIncluyenImpuesto,
            $fechaActualizado,
            $idConfiguracion,
            $idUser
        );

        if (!$stmtActualizar->execute()) {

            $error = $stmtActualizar->error;

            $stmtActualizar->close();

            throw new Exception(
                "No se pudo actualizar la configuración. " . $error
            );
        }

        $stmtActualizar->close();

        $operacion = "actualizada";
    } else {

        //=================================================
        // INSERTAR
        //=================================================

        $sqlInsertar = "
            INSERT INTO configuracion_monedas_impuestos (
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
            )
            VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ";

        $stmtInsertar = $conexion->prepare(
            $sqlInsertar
        );

        if (!$stmtInsertar) {

            throw new Exception(
                "No se pudo preparar el registro."
            );
        }

        $stmtInsertar->bind_param(
            "isssisssisdis",
            $idUser,
            $nombreMoneda,
            $codigoMoneda,
            $simboloMoneda,
            $decimales,
            $separadorDecimal,
            $separadorMiles,
            $posicionSimbolo,
            $impuestoActivo,
            $nombreImpuesto,
            $porcentajeImpuesto,
            $preciosIncluyenImpuesto,
            $fechaActualizado
        );

        if (!$stmtInsertar->execute()) {

            $error = $stmtInsertar->error;

            $stmtInsertar->close();

            throw new Exception(
                "No se pudo guardar la configuración. " . $error
            );
        }

        $idConfiguracion = $conexion->insert_id;

        $stmtInsertar->close();

        $operacion = "creada";
    }

    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    if (!$conexion->commit()) {

        throw new Exception(
            "No se pudo confirmar la operación."
        );
    }

    //=================================================
    // CONFIGURACIÓN DEVUELTA AL JS
    //=================================================

    $configuracionGuardada = [

        "nombre_moneda" => $nombreMoneda,

        "codigo_moneda" => $codigoMoneda,

        "simbolo_moneda" => $simboloMoneda,

        "decimales" => $decimales,

        "separador_decimal" => $separadorDecimal,

        "separador_miles" => $separadorMiles,

        "posicion_simbolo" => $posicionSimbolo,

        "impuesto_activo" => $impuestoActivo,

        "nombre_impuesto" => $nombreImpuesto,

        "porcentaje_impuesto" => $porcentajeImpuesto,

        "precios_incluyen_impuesto" =>
        $preciosIncluyenImpuesto
    ];

    //=================================================
    // RESPUESTA EXITOSA
    //=================================================

    responderJSON(
        true,
        "La configuración fue " . $operacion . " correctamente.",
        $configuracionGuardada
    );
} catch (Throwable $e) {

    //=================================================
    // ROLLBACK
    //=================================================

    if ($conexion->errno === 0 || $conexion->errno !== null) {

        try {

            $conexion->rollback();
        } catch (Throwable $rollbackError) {

            error_log(
                "Error rollback guardar_monedas_impuestos.php: " .
                    $rollbackError->getMessage()
            );
        }
    }

    //=================================================
    // REGISTRAR ERROR
    //=================================================

    error_log(
        "guardar_monedas_impuestos.php: " .
            $e->getMessage()
    );

    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        false,
        $e->getMessage()
    );
}
