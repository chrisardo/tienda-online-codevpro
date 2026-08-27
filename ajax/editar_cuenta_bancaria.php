<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/editar_cuenta_bancaria.php
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
// VALIDAR PETICIÓN AJAX
//=====================================================

if (
    !isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ||
    strtolower(
        (string) $_SERVER["HTTP_X_REQUESTED_WITH"]
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
// VALIDAR MÉTODO POST
//=====================================================

if (
    ($_SERVER["REQUEST_METHOD"] ?? "") !== "POST"
) {

    responderJSON(
        false,
        "Método de solicitud no permitido.",
        [],
        405
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
// Opción principal
//-----------------------------------------------------

if (isset($_SESSION["idUser"])) {

    $idUser = (int) $_SESSION["idUser"];
}

//-----------------------------------------------------
// Compatibilidad
//-----------------------------------------------------

if (
    $idUser <= 0 &&
    isset($_SESSION["id_user"])
) {

    $idUser = (int) $_SESSION["id_user"];
}

if (
    $idUser <= 0 &&
    isset($_SESSION["idUsuario"])
) {

    $idUser = (int) $_SESSION["idUsuario"];
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
// OBTENER DATOS
//=====================================================

$idCuenta = 0;

$nombre = "";

$balance = "";

$estado = null;

//-----------------------------------------------------
// ID CUENTA
//-----------------------------------------------------

if (
    isset($_POST["id_cuenta_bancaria"])
) {

    $idCuenta =
        (int) $_POST["id_cuenta_bancaria"];
}

//-----------------------------------------------------
// NOMBRE
//-----------------------------------------------------

if (
    isset($_POST["nombre"])
) {

    $nombre =
        trim(
            (string) $_POST["nombre"]
        );
}

//-----------------------------------------------------
// BALANCE
//-----------------------------------------------------

if (
    isset($_POST["balance"])
) {

    $balance =
        trim(
            (string) $_POST["balance"]
        );
}

//-----------------------------------------------------
// ESTADO
//-----------------------------------------------------

if (
    isset($_POST["estado"])
) {

    $estado =
        (int) $_POST["estado"];
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
// VALIDAR NOMBRE
//=====================================================

if ($nombre === "") {

    responderJSON(
        false,
        "Ingresa el nombre de la cuenta bancaria.",
        [],
        400
    );
}

//-----------------------------------------------------
// MÍNIMO
//-----------------------------------------------------

if (mb_strlen($nombre, "UTF-8") < 2) {

    responderJSON(
        false,
        "El nombre de la cuenta debe tener al menos 2 caracteres.",
        [],
        400
    );
}

//-----------------------------------------------------
// MÁXIMO
//-----------------------------------------------------

if (mb_strlen($nombre, "UTF-8") > 100) {

    responderJSON(
        false,
        "El nombre de la cuenta no puede superar los 100 caracteres.",
        [],
        400
    );
}

//=====================================================
// VALIDAR BALANCE
//=====================================================

if ($balance === "") {

    responderJSON(
        false,
        "Ingresa el balance de la cuenta.",
        [],
        400
    );
}

//-----------------------------------------------------
// NORMALIZAR DECIMAL
//-----------------------------------------------------

$balance = str_replace(",", ".", $balance);

//-----------------------------------------------------
// VALIDAR FORMATO NUMÉRICO
//-----------------------------------------------------

if (
    !preg_match(
        '/^\d+(?:\.\d{1,2})?$/',
        $balance
    )
) {

    responderJSON(
        false,
        "El balance ingresado no es válido. Usa un máximo de 2 decimales.",
        [],
        400
    );
}

//-----------------------------------------------------
// CONVERTIR A FLOAT
//-----------------------------------------------------

$balanceNumero = (float) $balance;

//-----------------------------------------------------
// VALIDAR NEGATIVO
//-----------------------------------------------------

if ($balanceNumero < 0) {

    responderJSON(
        false,
        "El balance no puede ser negativo.",
        [],
        400
    );
}

//=====================================================
// NORMALIZAR BALANCE
//=====================================================

$balanceNumero =
    number_format(
        $balanceNumero,
        2,
        ".",
        ""
    );

//=====================================================
// VALIDAR ESTADO
//=====================================================
//
// 0 = ACTIVA
// 1 = INACTIVA
//
//=====================================================

if (
    !in_array(
        $estado,
        [0, 1],
        true
    )
) {

    responderJSON(
        false,
        "El estado seleccionado no es válido.",
        [],
        400
    );
}

//=====================================================
// VERIFICAR QUE LA CUENTA EXISTA
// Y PERTENEZCA AL USUARIO
//=====================================================

$sqlVerificar = "
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
// PREPARAR
//=====================================================

$stmtVerificar =
    mysqli_prepare(
        $conexion,
        $sqlVerificar
    );

if (!$stmtVerificar) {

    responderJSON(
        false,
        "No se pudo preparar la validación de la cuenta bancaria.",
        [],
        500
    );
}

//=====================================================
// VINCULAR
//=====================================================

mysqli_stmt_bind_param(
    $stmtVerificar,
    "ii",
    $idCuenta,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (
    !mysqli_stmt_execute(
        $stmtVerificar
    )
) {

    mysqli_stmt_close(
        $stmtVerificar
    );

    responderJSON(
        false,
        "No se pudo verificar la cuenta bancaria.",
        [],
        500
    );
}

//=====================================================
// RESULTADO
//=====================================================

$resultadoVerificar =
    mysqli_stmt_get_result(
        $stmtVerificar
    );

if (!$resultadoVerificar) {

    mysqli_stmt_close(
        $stmtVerificar
    );

    responderJSON(
        false,
        "No se pudo obtener la información de la cuenta bancaria.",
        [],
        500
    );
}

//=====================================================
// OBTENER CUENTA
//=====================================================

$cuentaActual =
    mysqli_fetch_assoc(
        $resultadoVerificar
    );

//=====================================================
// CERRAR CONSULTA
//=====================================================

mysqli_free_result(
    $resultadoVerificar
);

mysqli_stmt_close(
    $stmtVerificar
);

//=====================================================
// CUENTA NO ENCONTRADA
//=====================================================

if (!$cuentaActual) {

    responderJSON(
        false,
        "La cuenta bancaria no existe o no tienes permisos para modificarla.",
        [],
        404
    );
}

//=====================================================
// VALIDAR NOMBRE DUPLICADO
//=====================================================
//
// Se permite conservar el mismo nombre de la cuenta
// que se está editando, pero no utilizar el nombre
// de otra cuenta del mismo usuario.
//
//=====================================================

$sqlDuplicado = "
    SELECT
        id_cuenta_bancaria
    FROM cuenta_banco
    WHERE
        id_user = ?
        AND id_cuenta_bancaria <> ?
        AND LOWER(TRIM(nombre)) = LOWER(TRIM(?))
    LIMIT 1
";

//=====================================================
// PREPARAR
//=====================================================

$stmtDuplicado =
    mysqli_prepare(
        $conexion,
        $sqlDuplicado
    );

if (!$stmtDuplicado) {

    responderJSON(
        false,
        "No se pudo validar el nombre de la cuenta.",
        [],
        500
    );
}

//=====================================================
// VINCULAR
//=====================================================

mysqli_stmt_bind_param(
    $stmtDuplicado,
    "iis",
    $idUser,
    $idCuenta,
    $nombre
);

//=====================================================
// EJECUTAR
//=====================================================

if (
    !mysqli_stmt_execute(
        $stmtDuplicado
    )
) {

    mysqli_stmt_close(
        $stmtDuplicado
    );

    responderJSON(
        false,
        "No se pudo validar el nombre de la cuenta.",
        [],
        500
    );
}

//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultadoDuplicado =
    mysqli_stmt_get_result(
        $stmtDuplicado
    );

if (!$resultadoDuplicado) {

    mysqli_stmt_close(
        $stmtDuplicado
    );

    responderJSON(
        false,
        "No se pudo comprobar el nombre de la cuenta.",
        [],
        500
    );
}

//=====================================================
// CUENTA DUPLICADA
//=====================================================

$cuentaDuplicada =
    mysqli_fetch_assoc(
        $resultadoDuplicado
    );

//=====================================================
// CERRAR
//=====================================================

mysqli_free_result(
    $resultadoDuplicado
);

mysqli_stmt_close(
    $stmtDuplicado
);

//=====================================================
// VALIDAR DUPLICADO
//=====================================================

if ($cuentaDuplicada) {

    responderJSON(
        false,
        "Ya existe otra cuenta bancaria con ese nombre.",
        [],
        409
    );
}

//=====================================================
// ACTUALIZAR CUENTA
//=====================================================

$sqlActualizar = "
    UPDATE cuenta_banco
    SET
        nombre = ?,
        balance = ?,
        Eliminado = ?
    WHERE
        id_cuenta_bancaria = ?
        AND id_user = ?
    LIMIT 1
";

//=====================================================
// PREPARAR
//=====================================================

$stmtActualizar =
    mysqli_prepare(
        $conexion,
        $sqlActualizar
    );

if (!$stmtActualizar) {

    responderJSON(
        false,
        "No se pudo preparar la actualización de la cuenta bancaria.",
        [],
        500
    );
}

//=====================================================
// VINCULAR PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmtActualizar,
    "sdiii",
    $nombre,
    $balanceNumero,
    $estado,
    $idCuenta,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (
    !mysqli_stmt_execute(
        $stmtActualizar
    )
) {

    $error =
        mysqli_stmt_error(
            $stmtActualizar
        );

    mysqli_stmt_close(
        $stmtActualizar
    );

    error_log(
        "Error al actualizar cuenta bancaria: " .
            $error
    );

    responderJSON(
        false,
        "No se pudo actualizar la cuenta bancaria.",
        [],
        500
    );
}

//=====================================================
// FILAS AFECTADAS
//=====================================================

$filasAfectadas =
    mysqli_stmt_affected_rows(
        $stmtActualizar
    );

//=====================================================
// CERRAR
//=====================================================

mysqli_stmt_close(
    $stmtActualizar
);

//=====================================================
// OBTENER INFORMACIÓN ACTUALIZADA
//=====================================================

$sqlActualizada = "
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
// PREPARAR
//=====================================================

$stmtActualizada =
    mysqli_prepare(
        $conexion,
        $sqlActualizada
    );

if (!$stmtActualizada) {

    responderJSON(
        false,
        "La cuenta fue actualizada, pero no se pudo recuperar la información actualizada.",
        [],
        500
    );
}

//=====================================================
// VINCULAR
//=====================================================

mysqli_stmt_bind_param(
    $stmtActualizada,
    "ii",
    $idCuenta,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (
    !mysqli_stmt_execute(
        $stmtActualizada
    )
) {

    mysqli_stmt_close(
        $stmtActualizada
    );

    responderJSON(
        false,
        "La cuenta fue actualizada, pero no se pudo recuperar la información actualizada.",
        [],
        500
    );
}

//=====================================================
// RESULTADO
//=====================================================

$resultadoActualizada =
    mysqli_stmt_get_result(
        $stmtActualizada
    );

if (!$resultadoActualizada) {

    mysqli_stmt_close(
        $stmtActualizada
    );

    responderJSON(
        false,
        "La cuenta fue actualizada, pero no se pudo obtener el resultado.",
        [],
        500
    );
}

//=====================================================
// OBTENER DATOS
//=====================================================

$cuentaActualizada =
    mysqli_fetch_assoc(
        $resultadoActualizada
    );

//=====================================================
// CERRAR
//=====================================================

mysqli_free_result(
    $resultadoActualizada
);

mysqli_stmt_close(
    $stmtActualizada
);

//=====================================================
// VALIDAR CUENTA ACTUALIZADA
//=====================================================

if (!$cuentaActualizada) {

    responderJSON(
        false,
        "La cuenta fue actualizada, pero no se pudo verificar el resultado.",
        [],
        500
    );
}

//=====================================================
// NORMALIZAR RESPUESTA
//=====================================================

$cuentaActualizada["id_cuenta_bancaria"] =
    (int) $cuentaActualizada["id_cuenta_bancaria"];

$cuentaActualizada["nombre"] =
    (string) $cuentaActualizada["nombre"];

$cuentaActualizada["balance"] =
    number_format(
        (float) $cuentaActualizada["balance"],
        2,
        ".",
        ""
    );

$cuentaActualizada["Eliminado"] =
    (int) $cuentaActualizada["Eliminado"];

//=====================================================
// MENSAJE SEGÚN ESTADO
//=====================================================

if ($estado === 0) {

    $mensaje =
        "La cuenta bancaria fue actualizada correctamente.";
} else {

    $mensaje =
        "La cuenta bancaria fue actualizada y desactivada correctamente.";
}

//=====================================================
// RESPUESTA CORRECTA
//=====================================================

responderJSON(
    true,
    $mensaje,
    [
        "cuenta" => $cuentaActualizada,
        "filas_afectadas" => $filasAfectadas
    ]
);
