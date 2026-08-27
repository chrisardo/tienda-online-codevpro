<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_registrar_movimiento.php
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
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Método de solicitud no permitido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// OBTENER DATOS
//=====================================================

$tipo = isset($_POST["tipoMovimiento"])
    ? strtoupper(trim($_POST["tipoMovimiento"]))
    : "";

$idCuentaBancaria = isset($_POST["id_cuenta_bancaria"])
    ? (int) $_POST["id_cuenta_bancaria"]
    : 0;

$idCategoria = isset($_POST["id_categoria"])
    ? (int) $_POST["id_categoria"]
    : 0;

$idProveedor = isset($_POST["id_proveedor"])
    ? (int) $_POST["id_proveedor"]
    : 0;

$idMetodoPago = isset($_POST["id_metodo_pago"])
    ? (int) $_POST["id_metodo_pago"]
    : 0;

$fecha = isset($_POST["fecha"])
    ? trim($_POST["fecha"])
    : "";

$montoPago = isset($_POST["monto_pago"])
    ? (float) $_POST["monto_pago"]
    : 0;

$concepto = isset($_POST["concepto"])
    ? trim($_POST["concepto"])
    : "";

$descripcion = isset($_POST["descripcion"])
    ? trim($_POST["descripcion"])
    : "";


//=====================================================
// VALIDAR TIPO
//=====================================================

if (
    $tipo !== "INGRESO" &&
    $tipo !== "GASTO"
) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "El tipo de movimiento no es válido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// VALIDAR CUENTA
//=====================================================

if ($idCuentaBancaria <= 0) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Debe seleccionar una cuenta bancaria."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// VALIDAR CATEGORÍA
//=====================================================

if ($idCategoria <= 0) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Debe seleccionar una categoría."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// VALIDAR MÉTODO DE PAGO
//=====================================================

if ($idMetodoPago <= 0) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Debe seleccionar un método de pago."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// VALIDAR FECHA
//=====================================================

if ($fecha === "") {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Debe ingresar una fecha."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// CONVERTIR FECHA
//=====================================================
// Flatpickr envía:
// dd/mm/aaaa
//
// MySQL necesita:
// aaaa-mm-dd
//=====================================================

$fechaPartes = explode("/", $fecha);


if (count($fechaPartes) !== 3) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "El formato de la fecha no es válido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


$dia = (int) $fechaPartes[0];
$mes = (int) $fechaPartes[1];
$anio = (int) $fechaPartes[2];


if (!checkdate($mes, $dia, $anio)) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "La fecha ingresada no es válida."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


$fechaMysql = sprintf(
    "%04d-%02d-%02d",
    $anio,
    $mes,
    $dia
);


//=====================================================
// VALIDAR MONTO
//=====================================================

if ($montoPago <= 0) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "El monto debe ser mayor que cero."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// REDONDEAR MONTO
//=====================================================

$montoPago = round(
    $montoPago,
    2
);


//=====================================================
// VALIDAR CONCEPTO
//=====================================================

if ($concepto === "") {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Debe ingresar el concepto del movimiento."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// LIMITAR CONCEPTO
//=====================================================

if (mb_strlen($concepto) > 255) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "El concepto no puede superar los 255 caracteres."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// PROVEEDOR OPCIONAL
//=====================================================

if ($idProveedor <= 0) {

    $idProveedor = null;
}


//=====================================================
// DESCRIPCIÓN OPCIONAL
//=====================================================

if ($descripcion === "") {

    $descripcion = null;
}


//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

$conexion->begin_transaction();


try {


    //=================================================
    // VALIDAR CUENTA BANCARIA
    //=================================================

    $sqlCuenta = "

        SELECT

            id_cuenta_bancaria,

            balance

        FROM cuenta_banco

        WHERE id_cuenta_bancaria = ?

        AND id_user = ?

        AND (
            Eliminado = 0
            OR Eliminado IS NULL
        )

        FOR UPDATE

    ";


    $stmtCuenta =
        $conexion->prepare($sqlCuenta);


    if (!$stmtCuenta) {

        throw new Exception(
            "No se pudo validar la cuenta bancaria."
        );
    }


    $stmtCuenta->bind_param(
        "ii",
        $idCuentaBancaria,
        $idUser
    );


    if (!$stmtCuenta->execute()) {

        throw new Exception(
            "No se pudo consultar la cuenta bancaria."
        );
    }


    $resultadoCuenta =
        $stmtCuenta->get_result();


    $cuenta =
        $resultadoCuenta->fetch_assoc();


    $stmtCuenta->close();


    if (!$cuenta) {

        throw new Exception(
            "La cuenta bancaria seleccionada no existe o no pertenece al usuario."
        );
    }


    //=================================================
    // OBTENER BALANCE ACTUAL
    //=================================================

    $balanceActual =
        (float) (
            $cuenta["balance"] ?? 0
        );


    //=================================================
    // VALIDAR CATEGORÍA
    //=================================================

    $sqlCategoria = "

        SELECT id_categorias

        FROM categorias

        WHERE id_categorias = ?

        AND id_user = ?

        AND (
            Eliminado = 0
            OR Eliminado IS NULL
        )

        LIMIT 1

    ";


    $stmtCategoria =
        $conexion->prepare($sqlCategoria);


    if (!$stmtCategoria) {

        throw new Exception(
            "No se pudo validar la categoría."
        );
    }


    $stmtCategoria->bind_param(
        "ii",
        $idCategoria,
        $idUser
    );


    if (!$stmtCategoria->execute()) {

        throw new Exception(
            "No se pudo consultar la categoría."
        );
    }


    $resultadoCategoria =
        $stmtCategoria->get_result();


    $categoriaExiste =
        $resultadoCategoria->num_rows > 0;


    $stmtCategoria->close();


    if (!$categoriaExiste) {

        throw new Exception(
            "La categoría seleccionada no existe o no pertenece al usuario."
        );
    }


    //=================================================
    // VALIDAR PROVEEDOR SI FUE SELECCIONADO
    //=================================================

    if ($idProveedor !== null) {

        $sqlProveedor = "

            SELECT id_provedor

            FROM provedores

            WHERE id_provedor = ?

            AND id_user = ?

            AND (
                Eliminado = 0
                OR Eliminado IS NULL
            )

            LIMIT 1

        ";


        $stmtProveedor =
            $conexion->prepare($sqlProveedor);


        if (!$stmtProveedor) {

            throw new Exception(
                "No se pudo validar el proveedor."
            );
        }


        $stmtProveedor->bind_param(
            "ii",
            $idProveedor,
            $idUser
        );


        if (!$stmtProveedor->execute()) {

            throw new Exception(
                "No se pudo consultar el proveedor."
            );
        }


        $resultadoProveedor =
            $stmtProveedor->get_result();


        $proveedorExiste =
            $resultadoProveedor->num_rows > 0;


        $stmtProveedor->close();


        if (!$proveedorExiste) {

            throw new Exception(
                "El proveedor seleccionado no existe o no pertenece al usuario."
            );
        }
    }


    //=================================================
    // VALIDAR MÉTODO DE PAGO
    //=================================================

    $sqlMetodoPago = "

        SELECT id_metodo_pago

        FROM metodo_pago

        WHERE id_metodo_pago = ?

        AND id_user = ?

        AND (
            Eliminado = 0
            OR Eliminado IS NULL
        )

        LIMIT 1

    ";


    $stmtMetodoPago =
        $conexion->prepare($sqlMetodoPago);


    if (!$stmtMetodoPago) {

        throw new Exception(
            "No se pudo validar el método de pago."
        );
    }


    $stmtMetodoPago->bind_param(
        "ii",
        $idMetodoPago,
        $idUser
    );


    if (!$stmtMetodoPago->execute()) {

        throw new Exception(
            "No se pudo consultar el método de pago."
        );
    }


    $resultadoMetodoPago =
        $stmtMetodoPago->get_result();


    $metodoExiste =
        $resultadoMetodoPago->num_rows > 0;


    $stmtMetodoPago->close();


    if (!$metodoExiste) {

        throw new Exception(
            "El método de pago seleccionado no existe o no pertenece al usuario."
        );
    }


    //=================================================
    // CALCULAR NUEVO BALANCE
    //=================================================

    if ($tipo === "INGRESO") {

        $nuevoBalance =
            $balanceActual +
            $montoPago;
    } else {

        $nuevoBalance =
            $balanceActual -
            $montoPago;
    }


    //=================================================
    // VALIDAR BALANCE PARA GASTO
    //=================================================
    // No permitimos que una cuenta quede con saldo
    // negativo al registrar un gasto.
    //=================================================

    if (
        $tipo === "GASTO" &&
        $nuevoBalance < 0
    ) {

        throw new Exception(
            "El monto del gasto supera el saldo disponible de la cuenta bancaria."
        );
    }


    //=================================================
    // REGISTRAR MOVIMIENTO
    //=================================================

    $sqlMovimiento = "

        INSERT INTO deposito_gasto (

            id_cuenta_bancaria,

            id_proveedor,

            id_categoria,

            id_metodo_pago,

            fecha,

            concepto,

            descripcion,

            monto_pago,

            tipo,

            id_user,

            Eliminado

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
            0

        )

    ";


    $stmtMovimiento =
        $conexion->prepare($sqlMovimiento);


    if (!$stmtMovimiento) {

        throw new Exception(
            "No se pudo preparar el registro del movimiento."
        );
    }


    //=================================================
    // BIND MOVIMIENTO
    //=================================================

    $stmtMovimiento->bind_param(
        "iiiisssdsi",
        $idCuentaBancaria,
        $idProveedor,
        $idCategoria,
        $idMetodoPago,
        $fechaMysql,
        $concepto,
        $descripcion,
        $montoPago,
        $tipo,
        $idUser
    );


    //=================================================
    // EJECUTAR MOVIMIENTO
    //=================================================

    if (!$stmtMovimiento->execute()) {

        throw new Exception(
            "No se pudo registrar el movimiento."
        );
    }


    //=================================================
    // ID DEL MOVIMIENTO
    //=================================================

    $idMovimiento =
        $conexion->insert_id;


    $stmtMovimiento->close();


    //=================================================
    // ACTUALIZAR BALANCE
    //=================================================

    $sqlBalance = "

        UPDATE cuenta_banco

        SET balance = ?

        WHERE id_cuenta_bancaria = ?

        AND id_user = ?

        AND (
            Eliminado = 0
            OR Eliminado IS NULL
        )

    ";


    $stmtBalance =
        $conexion->prepare($sqlBalance);


    if (!$stmtBalance) {

        throw new Exception(
            "No se pudo preparar la actualización del balance."
        );
    }


    $stmtBalance->bind_param(
        "dii",
        $nuevoBalance,
        $idCuentaBancaria,
        $idUser
    );


    if (!$stmtBalance->execute()) {

        throw new Exception(
            "No se pudo actualizar el balance de la cuenta."
        );
    }


    if ($stmtBalance->affected_rows <= 0) {

        throw new Exception(
            "No se pudo actualizar el balance de la cuenta bancaria."
        );
    }


    $stmtBalance->close();


    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    $conexion->commit();


    //=================================================
    // RESPUESTA
    //=================================================

    echo json_encode(
        [

            "status" => "success",

            "mensaje" =>
            "Movimiento registrado correctamente.",

            "datos" => [

                "id_movimiento" =>
                (int) $idMovimiento,

                "tipo" =>
                $tipo,

                "monto" =>
                $montoPago,

                "balance_anterior" =>
                $balanceActual,

                "balance_nuevo" =>
                $nuevoBalance

            ]

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
} catch (Exception $e) {


    //=================================================
    // REVERTIR TRANSACCIÓN
    //=================================================

    $conexion->rollback();


    //=================================================
    // RESPUESTA DE ERROR
    //=================================================

    echo json_encode(
        [

            "status" => "error",

            "mensaje" =>
            $e->getMessage()

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}
