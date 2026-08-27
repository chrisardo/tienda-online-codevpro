<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_obtener_movimiento.php
// Módulo: Ingresos y Gastos
// Sistema: Inventa
//=====================================================


//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

header(
    'Content-Type: application/json; charset=utf-8'
);


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR USUARIO
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Usuario no identificado."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// OBTENER ID DEL MOVIMIENTO
//=====================================================

$idDeposito = isset($_POST["id_deposito"])
    ? (int) $_POST["id_deposito"]
    : 0;


if ($idDeposito <= 0) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "ID del movimiento no válido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// CONSULTA
//=====================================================
//
// IMPORTANTE:
//
// Se devuelve directamente:
//
// dg.id_cuenta_bancaria
//
// dg.id_categoria
//
// dg.id_proveedor
//
// dg.id_metodo_pago
//
// Estos valores son los que utilizará JavaScript
// para seleccionar las opciones del modal editar.
//

$sql = "

    SELECT

        dg.id_deposito,

        dg.id_user,

        dg.id_cuenta_bancaria,

        dg.id_categoria,

        dg.id_proveedor,

        dg.id_metodo_pago,

        dg.tipo,

        dg.fecha,

        dg.concepto,

        dg.descripcion,

        dg.monto_pago,

        cb.nombre AS cuenta_bancaria,

        cat.nombre AS categoria,

        prov.nombre AS proveedor,

        mp.nombre AS metodo_pago

    FROM deposito_gasto dg

    LEFT JOIN cuenta_banco cb
        ON cb.id_cuenta_bancaria =
           dg.id_cuenta_bancaria

    LEFT JOIN categorias cat
        ON cat.id_categorias =
           dg.id_categoria

    LEFT JOIN provedores prov
        ON prov.id_provedor =
           dg.id_proveedor

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago =
           dg.id_metodo_pago

    WHERE

        dg.id_deposito = ?

        AND dg.id_user = ?

        AND (
            dg.Eliminado = 0
            OR dg.Eliminado IS NULL
        )

    LIMIT 1

";


//=====================================================
// PREPARAR
//=====================================================

$stmt = $conexion->prepare($sql);


if (!$stmt) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "No se pudo preparar la consulta del movimiento."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// BIND
//=====================================================

$stmt->bind_param(
    "ii",
    $idDeposito,
    $idUser
);


//=====================================================
// EJECUTAR
//=====================================================

if (!$stmt->execute()) {

    $stmt->close();

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "No se pudo ejecutar la consulta del movimiento."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// RESULTADO
//=====================================================

$resultado = $stmt->get_result();


//=====================================================
// VALIDAR
//=====================================================

if ($resultado->num_rows === 0) {

    $stmt->close();

    echo json_encode(
        [
            "status" => "error",
            "mensaje" =>
            "El movimiento no existe, fue eliminado o no pertenece al usuario."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// OBTENER DATOS
//=====================================================

$fila = $resultado->fetch_assoc();


//=====================================================
// CERRAR
//=====================================================

$stmt->close();


//=====================================================
// NORMALIZAR TIPO
//=====================================================

$tipo = strtoupper(
    trim(
        $fila["tipo"] ?? ""
    )
);


//=====================================================
// NORMALIZAR FECHA
//=====================================================

$fecha = $fila["fecha"] ?? "";

$fechaFormateada = "";

if (!empty($fecha)) {

    $fechaObjeto = DateTime::createFromFormat(
        "Y-m-d",
        $fecha
    );

    if ($fechaObjeto !== false) {

        $fechaFormateada =
            $fechaObjeto->format("d/m/Y");
    } else {

        $fechaFormateada = $fecha;
    }
}


//=====================================================
// NORMALIZAR IDS
//=====================================================

$idCuentaBancaria =
    !empty($fila["id_cuenta_bancaria"])
    ? (int) $fila["id_cuenta_bancaria"]
    : 0;


$idCategoria =
    !empty($fila["id_categoria"])
    ? (int) $fila["id_categoria"]
    : 0;


$idProveedor =
    !empty($fila["id_proveedor"])
    ? (int) $fila["id_proveedor"]
    : 0;


$idMetodoPago =
    !empty($fila["id_metodo_pago"])
    ? (int) $fila["id_metodo_pago"]
    : 0;


//=====================================================
// RESPUESTA
//=====================================================

echo json_encode(
    [

        "status" =>
        "success",

        "mensaje" =>
        "Movimiento obtenido correctamente.",

        "datos" => [

            //=========================================
            // IDENTIFICACIÓN
            //=========================================

            "id_deposito" =>
            (int) $fila["id_deposito"],

            "id_user" =>
            (int) $fila["id_user"],


            //=========================================
            // CUENTA BANCARIA
            //=========================================

            "id_cuenta_bancaria" =>
            $idCuentaBancaria,

            "cuenta_bancaria" =>
            $fila["cuenta_bancaria"]
                ?? "",


            //=========================================
            // CATEGORÍA
            //=========================================

            "id_categoria" =>
            $idCategoria,

            "categoria" =>
            $fila["categoria"]
                ?? "",


            //=========================================
            // PROVEEDOR
            //=========================================

            "id_proveedor" =>
            $idProveedor,

            "proveedor" =>
            $fila["proveedor"]
                ?? "",


            //=========================================
            // MÉTODO DE PAGO
            //=========================================

            "id_metodo_pago" =>
            $idMetodoPago,

            "metodo_pago" =>
            $fila["metodo_pago"]
                ?? "",


            //=========================================
            // TIPO
            //=========================================

            "tipo" =>
            $tipo,


            //=========================================
            // FECHA
            //=========================================

            "fecha" =>
            $fecha,

            "fecha_formateada" =>
            $fechaFormateada,


            //=========================================
            // CONCEPTO
            //=========================================

            "concepto" =>
            $fila["concepto"]
                ?? "",


            //=========================================
            // DESCRIPCIÓN
            //=========================================

            "descripcion" =>
            $fila["descripcion"]
                ?? "",


            //=========================================
            // MONTO
            //=========================================

            "monto_pago" =>
            (float) (
                $fila["monto_pago"]
                ?? 0
            )

        ]

    ],
    JSON_UNESCAPED_UNICODE
);

exit;
