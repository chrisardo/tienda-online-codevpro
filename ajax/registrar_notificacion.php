<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/registrar_notificacion.php
// Módulo: Notificaciones
// Sistema: Inventa
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// CONFIGURACIÓN DE ERRORES
//=====================================================

ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");
error_reporting(E_ALL);

//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// RESPUESTA JSON
//=====================================================

function responderJSON(
    bool $success,
    string $mensaje,
    array $extra = [],
    int $codigoHTTP = 200
): void {

    http_response_code($codigoHTTP);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "mensaje" => $mensaje
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    error_log(
        "registrar_notificacion.php: conexión inválida."
    );

    responderJSON(
        false,
        "No se pudo establecer la conexión con la base de datos.",
        [],
        500
    );
}

//=====================================================
// CONFIGURAR UTF-8
//=====================================================

if (!mysqli_set_charset($conexion, "utf8mb4")) {

    error_log(
        "registrar_notificacion.php: no se pudo configurar utf8mb4: " .
            mysqli_error($conexion)
    );
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
        "La sesión de usuario no es válida.",
        [],
        401
    );
}

//=====================================================
// VALIDAR MÉTODO
//=====================================================

if (
    !isset($_SERVER["REQUEST_METHOD"]) ||
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    responderJSON(
        false,
        "Método de solicitud no permitido.",
        [],
        405
    );
}

//=====================================================
// RECIBIR DATOS
//=====================================================

$idNotificacion = isset($_POST["idNotificacion"])
    ? (int) $_POST["idNotificacion"]
    : 0;

$idCliente = isset($_POST["idCliente"])
    ? (int) $_POST["idCliente"]
    : 0;

$titulo = isset($_POST["titulo"])
    ? trim((string) $_POST["titulo"])
    : "";

$mensaje = isset($_POST["mensaje"])
    ? trim((string) $_POST["mensaje"])
    : "";

$tipo = isset($_POST["tipo"])
    ? trim((string) $_POST["tipo"])
    : "SISTEMA";

$icono = isset($_POST["icono"])
    ? trim((string) $_POST["icono"])
    : "bi-bell-fill";

$color = isset($_POST["color"])
    ? trim((string) $_POST["color"])
    : "primary";

$url = isset($_POST["url"])
    ? trim((string) $_POST["url"])
    : "";

$leido = isset($_POST["leido"])
    ? (int) $_POST["leido"]
    : 0;

//=====================================================
// NORMALIZAR
//=====================================================

$tipo = strtoupper($tipo);

$leido = ($leido === 1)
    ? 1
    : 0;

//=====================================================
// VALORES PERMITIDOS
//=====================================================

$tiposPermitidos = [

    "SISTEMA",
    "PEDIDO",
    "PRODUCTO",
    "OFERTA",
    "PROMOCION",
    "OTRO"

];

$coloresPermitidos = [

    "primary",
    "secondary",
    "success",
    "danger",
    "warning",
    "info",
    "light",
    "dark"

];

//=====================================================
// VALIDAR CLIENTE
//=====================================================

if ($idCliente <= 0) {

    responderJSON(
        false,
        "Debes seleccionar un cliente.",
        [],
        400
    );
}

//=====================================================
// VALIDAR TÍTULO
//=====================================================

if ($titulo === "") {

    responderJSON(
        false,
        "El título de la notificación es obligatorio.",
        [],
        400
    );
}

if (mb_strlen($titulo, "UTF-8") > 255) {

    responderJSON(
        false,
        "El título no puede superar los 255 caracteres.",
        [],
        400
    );
}

//=====================================================
// VALIDAR MENSAJE
//=====================================================

if ($mensaje === "") {

    responderJSON(
        false,
        "El mensaje de la notificación es obligatorio.",
        [],
        400
    );
}

//=====================================================
// VALIDAR TIPO
//=====================================================

if (
    !in_array(
        $tipo,
        $tiposPermitidos,
        true
    )
) {

    $tipo = "SISTEMA";
}

//=====================================================
// VALIDAR ICONO
//=====================================================

if ($icono === "") {

    $icono = "bi-bell-fill";
}

if (
    !preg_match(
        '/^bi-[a-zA-Z0-9_-]+$/',
        $icono
    )
) {

    $icono = "bi-bell-fill";
}

//=====================================================
// VALIDAR COLOR
//=====================================================

if (
    !in_array(
        $color,
        $coloresPermitidos,
        true
    )
) {

    $color = "primary";
}

//=====================================================
// VALIDAR URL
//=====================================================

if (
    mb_strlen(
        $url,
        "UTF-8"
    ) > 255
) {

    responderJSON(
        false,
        "La URL no puede superar los 255 caracteres.",
        [],
        400
    );
}

//=====================================================
// VALIDAR CLIENTE
//=====================================================
// El cliente debe:
//
// 1. Existir.
// 2. No estar eliminado.
// 3. Pertenecer al usuario actual.
//
//=====================================================

$sqlCliente = "

    SELECT
        idCliente

    FROM clientes

    WHERE
        idCliente = ?
        AND id_user = ?
        AND Eliminado = 0

    LIMIT 1

";

$stmtCliente = mysqli_prepare(
    $conexion,
    $sqlCliente
);

if (!$stmtCliente) {

    error_log(
        "registrar_notificacion.php - Error preparando cliente: " .
            mysqli_error($conexion)
    );

    responderJSON(
        false,
        "No se pudo validar el cliente.",
        [],
        500
    );
}

//=====================================================
// BIND CLIENTE
//=====================================================

if (
    !mysqli_stmt_bind_param(
        $stmtCliente,
        "ii",
        $idCliente,
        $idUser
    )
) {

    $error = mysqli_stmt_error(
        $stmtCliente
    );

    mysqli_stmt_close(
        $stmtCliente
    );

    error_log(
        "registrar_notificacion.php - Error bind cliente: " .
            $error
    );

    responderJSON(
        false,
        "No se pudo validar el cliente.",
        [],
        500
    );
}

//=====================================================
// EJECUTAR CLIENTE
//=====================================================

if (
    !mysqli_stmt_execute(
        $stmtCliente
    )
) {

    $error = mysqli_stmt_error(
        $stmtCliente
    );

    mysqli_stmt_close(
        $stmtCliente
    );

    error_log(
        "registrar_notificacion.php - Error ejecutando cliente: " .
            $error
    );

    responderJSON(
        false,
        "No se pudo validar el cliente.",
        [],
        500
    );
}

//=====================================================
// OBTENER RESULTADO CLIENTE
//=====================================================

$resultadoCliente =
    mysqli_stmt_get_result(
        $stmtCliente
    );

if ($resultadoCliente === false) {

    $error = mysqli_stmt_error(
        $stmtCliente
    );

    mysqli_stmt_close(
        $stmtCliente
    );

    error_log(
        "registrar_notificacion.php - Error resultado cliente: " .
            $error
    );

    responderJSON(
        false,
        "No se pudo verificar el cliente.",
        [],
        500
    );
}

//=====================================================
// COMPROBAR CLIENTE
//=====================================================

if (
    mysqli_num_rows(
        $resultadoCliente
    ) === 0
) {

    mysqli_free_result(
        $resultadoCliente
    );

    mysqli_stmt_close(
        $stmtCliente
    );

    responderJSON(
        false,
        "El cliente seleccionado no existe, está eliminado o no pertenece a tu cuenta.",
        [],
        400
    );
}

mysqli_free_result(
    $resultadoCliente
);

mysqli_stmt_close(
    $stmtCliente
);

//=====================================================
// DETERMINAR OPERACIÓN
//=====================================================

$esEdicion =
    $idNotificacion > 0;

//=====================================================
// ACTUALIZAR NOTIFICACIÓN
//=====================================================

if ($esEdicion) {

    //=================================================
    // VERIFICAR NOTIFICACIÓN
    //=================================================

    $sqlExiste = "

        SELECT
            n.id_notificacion

        FROM notificaciones_cliente AS n

        INNER JOIN clientes AS c
            ON c.idCliente = n.idCliente

        WHERE
            n.id_notificacion = ?
            AND n.Eliminado = 0
            AND c.id_user = ?
            AND c.Eliminado = 0

        LIMIT 1

    ";

    $stmtExiste = mysqli_prepare(
        $conexion,
        $sqlExiste
    );

    if (!$stmtExiste) {

        error_log(
            "registrar_notificacion.php - Error preparando existencia: " .
                mysqli_error($conexion)
        );

        responderJSON(
            false,
            "No se pudo verificar la notificación.",
            [],
            500
        );
    }

    //=================================================
    // BIND EXISTENCIA
    //=================================================

    if (
        !mysqli_stmt_bind_param(
            $stmtExiste,
            "ii",
            $idNotificacion,
            $idUser
        )
    ) {

        $error =
            mysqli_stmt_error(
                $stmtExiste
            );

        mysqli_stmt_close(
            $stmtExiste
        );

        error_log(
            "registrar_notificacion.php - Error bind existencia: " .
                $error
        );

        responderJSON(
            false,
            "No se pudo verificar la notificación.",
            [],
            500
        );
    }

    //=================================================
    // EJECUTAR EXISTENCIA
    //=================================================

    if (
        !mysqli_stmt_execute(
            $stmtExiste
        )
    ) {

        $error =
            mysqli_stmt_error(
                $stmtExiste
            );

        mysqli_stmt_close(
            $stmtExiste
        );

        error_log(
            "registrar_notificacion.php - Error ejecutando existencia: " .
                $error
        );

        responderJSON(
            false,
            "No se pudo verificar la notificación.",
            [],
            500
        );
    }

    //=================================================
    // RESULTADO
    //=================================================

    $resultadoExiste =
        mysqli_stmt_get_result(
            $stmtExiste
        );

    if ($resultadoExiste === false) {

        $error =
            mysqli_stmt_error(
                $stmtExiste
            );

        mysqli_stmt_close(
            $stmtExiste
        );

        error_log(
            "registrar_notificacion.php - Error resultado existencia: " .
                $error
        );

        responderJSON(
            false,
            "No se pudo verificar la notificación.",
            [],
            500
        );
    }

    //=================================================
    // NOTIFICACIÓN NO EXISTE
    //=================================================

    if (
        mysqli_num_rows(
            $resultadoExiste
        ) === 0
    ) {

        mysqli_free_result(
            $resultadoExiste
        );

        mysqli_stmt_close(
            $stmtExiste
        );

        responderJSON(
            false,
            "La notificación no existe, ya fue eliminada o no pertenece a tu cuenta.",
            [],
            404
        );
    }

    mysqli_free_result(
        $resultadoExiste
    );

    mysqli_stmt_close(
        $stmtExiste
    );

    //=================================================
    // SQL ACTUALIZAR
    //=================================================

    $sqlActualizar = "

        UPDATE notificaciones_cliente

        SET
            idCliente = ?,
            titulo = ?,
            mensaje = ?,
            tipo = ?,
            icono = ?,
            color = ?,
            url = ?,
            leido = ?

        WHERE
            id_notificacion = ?
            AND Eliminado = 0

        LIMIT 1

    ";

    $stmtActualizar =
        mysqli_prepare(
            $conexion,
            $sqlActualizar
        );

    if (!$stmtActualizar) {

        error_log(
            "registrar_notificacion.php - Error preparando UPDATE: " .
                mysqli_error($conexion)
        );

        responderJSON(
            false,
            "No se pudo preparar la actualización de la notificación.",
            [],
            500
        );
    }

    //=================================================
    // BIND CORRECTO
    //=================================================
    //
    // i = idCliente
    // s = titulo
    // s = mensaje
    // s = tipo
    // s = icono
    // s = color
    // s = url
    // i = leido
    // i = idNotificacion
    //
    // TOTAL: 9 parámetros
    //
    //=================================================

    if (
        !mysqli_stmt_bind_param(
            $stmtActualizar,
            "issssssii",
            $idCliente,
            $titulo,
            $mensaje,
            $tipo,
            $icono,
            $color,
            $url,
            $leido,
            $idNotificacion
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
            "registrar_notificacion.php - Error bind UPDATE: " .
                $error
        );

        responderJSON(
            false,
            "No se pudieron preparar los datos para actualizar la notificación.",
            [],
            500
        );
    }

    //=================================================
    // EJECUTAR UPDATE
    //=================================================

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
            "registrar_notificacion.php - Error UPDATE: " .
                $error
        );

        responderJSON(
            false,
            "No se pudo actualizar la notificación.",
            [],
            500
        );
    }

    //=================================================
    // FILAS AFECTADAS
    //=================================================

    $filasAfectadas =
        mysqli_stmt_affected_rows(
            $stmtActualizar
        );

    mysqli_stmt_close(
        $stmtActualizar
    );

    //=================================================
    // RESPUESTA
    //=================================================
    //
    // affected_rows puede ser 0 si los datos eran
    // exactamente iguales. Eso NO significa error.
    //
    //=================================================

    responderJSON(
        true,
        "La notificación se actualizó correctamente.",
        [
            "operacion" => "actualizar",
            "id_notificacion" => $idNotificacion,
            "filas_afectadas" => $filasAfectadas
        ]
    );
}

//=====================================================
// REGISTRAR NUEVA NOTIFICACIÓN
//=====================================================
//
// Si $idNotificacion = 0 llegamos aquí.
//
//=====================================================

$sqlInsertar = "

    INSERT INTO notificaciones_cliente
    (
        idCliente,
        titulo,
        mensaje,
        tipo,
        icono,
        color,
        url,
        leido,
        Eliminado
    )
    VALUES
    (
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

$stmtInsertar =
    mysqli_prepare(
        $conexion,
        $sqlInsertar
    );

if (!$stmtInsertar) {

    error_log(
        "registrar_notificacion.php - Error preparando INSERT: " .
            mysqli_error($conexion)
    );

    responderJSON(
        false,
        "No se pudo preparar el registro de la notificación.",
        [],
        500
    );
}

//=====================================================
// BIND INSERT
//=====================================================
//
// i = idCliente
// s = titulo
// s = mensaje
// s = tipo
// s = icono
// s = color
// s = url
// i = leido
//
// TOTAL: 8 parámetros
//
//=====================================================

if (
    !mysqli_stmt_bind_param(
        $stmtInsertar,
        "issssssi",
        $idCliente,
        $titulo,
        $mensaje,
        $tipo,
        $icono,
        $color,
        $url,
        $leido
    )
) {

    $error =
        mysqli_stmt_error(
            $stmtInsertar
        );

    mysqli_stmt_close(
        $stmtInsertar
    );

    error_log(
        "registrar_notificacion.php - Error bind INSERT: " .
            $error
    );

    responderJSON(
        false,
        "No se pudieron preparar los datos de la notificación.",
        [],
        500
    );
}

//=====================================================
// EJECUTAR INSERT
//=====================================================

if (
    !mysqli_stmt_execute(
        $stmtInsertar
    )
) {

    $error =
        mysqli_stmt_error(
            $stmtInsertar
        );

    $errno =
        mysqli_stmt_errno(
            $stmtInsertar
        );

    mysqli_stmt_close(
        $stmtInsertar
    );

    error_log(
        "registrar_notificacion.php - Error INSERT [" .
            $errno .
            "]: " .
            $error
    );

    responderJSON(
        false,
        "No se pudo registrar la notificación.",
        [
            "error_codigo" => $errno
        ],
        500
    );
}

//=====================================================
// OBTENER ID INSERTADO
//=====================================================

$idNuevo =
    mysqli_insert_id(
        $conexion
    );

//=====================================================
// FILAS AFECTADAS
//=====================================================

$filasAfectadas =
    mysqli_stmt_affected_rows(
        $stmtInsertar
    );

mysqli_stmt_close(
    $stmtInsertar
);

//=====================================================
// VALIDAR INSERCIÓN
//=====================================================

if ($idNuevo <= 0) {

    error_log(
        "registrar_notificacion.php - INSERT ejecutado pero no se obtuvo id. " .
            "filas afectadas: " .
            $filasAfectadas
    );

    responderJSON(
        false,
        "La notificación no pudo registrarse correctamente.",
        [],
        500
    );
}

//=====================================================
// RESPUESTA EXITOSA
//=====================================================

responderJSON(
    true,
    "La notificación se registró correctamente.",
    [
        "operacion" => "registrar",
        "id_notificacion" => $idNuevo,
        "idCliente" => $idCliente,
        "filas_afectadas" => $filasAfectadas
    ]
);
