<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_cuenta_bancaria.php
// Módulo: Cuentas Bancarias
// Sistema: Inventa
//=====================================================

declare(strict_types=1);

//=====================================================
// SESIÓN
//=====================================================

session_start();

//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// FUNCIÓN RESPUESTA
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
// VALIDAR PETICIÓN AJAX
//=====================================================

if (
    !isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ||
    strtolower(
        $_SERVER["HTTP_X_REQUESTED_WITH"]
    ) !== "xmlhttprequest"
) {

    responderJSON(
        false,
        "Solicitud no permitida.",
        [],
        403
    );
}

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
        "No se pudo establecer la conexión con la base de datos.",
        [],
        500
    );
}

//=====================================================
// VALIDAR SESIÓN DEL USUARIO
//=====================================================

$idUser = 0;

//-----------------------------------------------------
// Opción principal utilizada por el sistema
//-----------------------------------------------------

if (isset($_SESSION["idUser"])) {

    $idUser =
        (int) $_SESSION["idUser"];
}

//-----------------------------------------------------
// Compatibilidad con posibles nombres de sesión
//-----------------------------------------------------

if (
    $idUser <= 0 &&
    isset($_SESSION["id_user"])
) {

    $idUser =
        (int) $_SESSION["id_user"];
}

if (
    $idUser <= 0 &&
    isset($_SESSION["idUsuario"])
) {

    $idUser =
        (int) $_SESSION["idUsuario"];
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

if ($idUser <= 0) {

    responderJSON(
        false,
        "La sesión del usuario no es válida.",
        [],
        401
    );
}

//=====================================================
// OBTENER ID DE CUENTA
//=====================================================

$idCuenta = 0;

if (
    isset(
        $_GET["id_cuenta_bancaria"]
    )
) {

    $idCuenta =
        (int) $_GET["id_cuenta_bancaria"];
}

//=====================================================
// VALIDAR ID
//=====================================================

if ($idCuenta <= 0) {

    responderJSON(
        false,
        "No se recibió un ID de cuenta bancaria válido.",
        [],
        400
    );
}

//=====================================================
// CONSULTAR CUENTA
//=====================================================

$sql = "
    SELECT
        id_cuenta_bancaria,
        nombre,
        balance,
        Eliminado
    FROM cuenta_banco
    WHERE
        id_cuenta_bancaria = ?
        AND id_user = ?
    LIMIT 1
";

//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );

if (!$stmt) {

    responderJSON(
        false,
        "No se pudo preparar la consulta de la cuenta bancaria.",
        [],
        500
    );
}

//=====================================================
// VINCULAR PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idCuenta,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (
    !mysqli_stmt_execute(
        $stmt
    )
) {

    mysqli_stmt_close(
        $stmt
    );

    responderJSON(
        false,
        "No se pudo consultar la cuenta bancaria.",
        [],
        500
    );
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado =
    mysqli_stmt_get_result(
        $stmt
    );

//=====================================================
// VALIDAR RESULTADO
//=====================================================

if (!$resultado) {

    mysqli_stmt_close(
        $stmt
    );

    responderJSON(
        false,
        "No se pudo obtener el resultado de la consulta.",
        [],
        500
    );
}

//=====================================================
// OBTENER CUENTA
//=====================================================

$cuenta =
    mysqli_fetch_assoc(
        $resultado
    );

//=====================================================
// CERRAR
//=====================================================

mysqli_free_result(
    $resultado
);

mysqli_stmt_close(
    $stmt
);

//=====================================================
// CUENTA NO ENCONTRADA
//=====================================================

if (!$cuenta) {

    responderJSON(
        false,
        "La cuenta bancaria no existe o no tienes permisos para consultarla.",
        [],
        404
    );
}

//=====================================================
// NORMALIZAR DATOS
//=====================================================

$cuenta["id_cuenta_bancaria"] =
    (int) $cuenta["id_cuenta_bancaria"];

$cuenta["nombre"] =
    (string) $cuenta["nombre"];

$cuenta["balance"] =
    number_format(
        (float) $cuenta["balance"],
        2,
        ".",
        ""
    );

$cuenta["Eliminado"] =
    (int) $cuenta["Eliminado"];

//=====================================================
// RESPUESTA CORRECTA
//=====================================================

responderJSON(
    true,
    "Cuenta bancaria obtenida correctamente.",
    [
        "cuenta" => $cuenta
    ]
);
