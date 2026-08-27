<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_proveedor.php
// Módulo: Lista de Proveedores
// Sistema: Inventa
//======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

//======================================================
// RESPUESTA BASE
//======================================================

$respuesta = [
    "success" => false,
    "message" => ""
];

//======================================================
// VALIDAR MÉTODO
//======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    $respuesta["message"] =
        "Método de solicitud no permitido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// CONEXIÓN
//======================================================

require_once "../controladores/conexion.php";

//======================================================
// VALIDAR CONEXIÓN
//======================================================

if (!isset($conexion) || !$conexion) {

    http_response_code(500);

    $respuesta["message"] =
        "No se pudo establecer la conexión con la base de datos.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// OBTENER USUARIO
//======================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

//======================================================
// VALIDAR SESIÓN
//======================================================

if ($idUser <= 0) {

    http_response_code(401);

    $respuesta["message"] =
        "La sesión no es válida o ha expirado.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// OBTENER ACCIÓN
//======================================================

$accion = isset($_GET["accion"])
    ? trim($_GET["accion"])
    : "actualizar";

//======================================================
//======================================================
// CAMBIAR ESTADO
//======================================================
//======================================================

if ($accion === "estado") {

    //==================================================
    // ID PROVEEDOR
    //==================================================

    $idProveedor = isset($_POST["id_provedor"])
        ? (int) $_POST["id_provedor"]
        : 0;

    //==================================================
    // NUEVO ESTADO
    //==================================================

    $eliminado = isset($_POST["Eliminado"])
        ? (int) $_POST["Eliminado"]
        : -1;

    //==================================================
    // VALIDAR ID
    //==================================================

    if ($idProveedor <= 0) {

        http_response_code(400);

        $respuesta["message"] =
            "El proveedor indicado no es válido.";

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    //==================================================
    // VALIDAR ESTADO
    //==================================================

    if ($eliminado !== 0 && $eliminado !== 1) {

        http_response_code(400);

        $respuesta["message"] =
            "El estado indicado no es válido.";

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    //==================================================
    // VERIFICAR QUE EL PROVEEDOR PERTENEZCA AL USUARIO
    //==================================================

    $sqlVerificar = "
        SELECT
            id_provedor,
            nombre,
            Eliminado
        FROM provedores
        WHERE id_provedor = ?
          AND id_user = ?
        LIMIT 1
    ";

    $stmtVerificar = mysqli_prepare(
        $conexion,
        $sqlVerificar
    );

    if (!$stmtVerificar) {

        http_response_code(500);

        $respuesta["message"] =
            "No se pudo verificar el proveedor.";

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    mysqli_stmt_bind_param(
        $stmtVerificar,
        "ii",
        $idProveedor,
        $idUser
    );

    if (!mysqli_stmt_execute($stmtVerificar)) {

        mysqli_stmt_close($stmtVerificar);

        http_response_code(500);

        $respuesta["message"] =
            "No se pudo verificar el proveedor.";

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    $resultadoVerificar = mysqli_stmt_get_result(
        $stmtVerificar
    );

    $proveedor = mysqli_fetch_assoc(
        $resultadoVerificar
    );

    mysqli_stmt_close($stmtVerificar);

    //==================================================
    // PROVEEDOR NO ENCONTRADO
    //==================================================

    if (!$proveedor) {

        http_response_code(404);

        $respuesta["message"] =
            "El proveedor no existe o no pertenece al usuario actual.";

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    //==================================================
    // ACTUALIZAR ESTADO
    //==================================================

    $sqlActualizarEstado = "
        UPDATE provedores
        SET
            Eliminado = ?,
            fecha_actualizado = NOW()
        WHERE id_provedor = ?
          AND id_user = ?
        LIMIT 1
    ";

    $stmtEstado = mysqli_prepare(
        $conexion,
        $sqlActualizarEstado
    );

    if (!$stmtEstado) {

        http_response_code(500);

        $respuesta["message"] =
            "No se pudo preparar la actualización del estado.";

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    mysqli_stmt_bind_param(
        $stmtEstado,
        "iii",
        $eliminado,
        $idProveedor,
        $idUser
    );

    if (!mysqli_stmt_execute($stmtEstado)) {

        mysqli_stmt_close($stmtEstado);

        http_response_code(500);

        $respuesta["message"] =
            "No se pudo actualizar el estado del proveedor.";

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    mysqli_stmt_close($stmtEstado);

    //==================================================
    // MENSAJE
    //==================================================

    if ($eliminado === 0) {

        $respuesta["message"] =
            "El proveedor fue activado correctamente.";
    } else {

        $respuesta["message"] =
            "El proveedor fue desactivado correctamente.";
    }

    $respuesta["success"] = true;

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
//======================================================
// ACTUALIZAR DATOS DEL PROVEEDOR
//======================================================
//======================================================

//======================================================
// OBTENER DATOS
//======================================================

$idProveedor = isset($_POST["id_provedor"])
    ? (int) $_POST["id_provedor"]
    : 0;

$nombre = isset($_POST["nombre"])
    ? trim($_POST["nombre"])
    : "";

$ruc = isset($_POST["ruc"])
    ? trim($_POST["ruc"])
    : "";

$celular = isset($_POST["celular"])
    ? trim($_POST["celular"])
    : "";

$email = isset($_POST["email"])
    ? trim($_POST["email"])
    : "";

$direccion = isset($_POST["direccion"])
    ? trim($_POST["direccion"])
    : "";

$idPais = isset($_POST["id_pais"]) && $_POST["id_pais"] !== ""
    ? (int) $_POST["id_pais"]
    : null;

$idDepartamento = isset($_POST["id_departamento"]) && $_POST["id_departamento"] !== ""
    ? (int) $_POST["id_departamento"]
    : null;

$idProvincia = isset($_POST["id_provincia"]) && $_POST["id_provincia"] !== ""
    ? (int) $_POST["id_provincia"]
    : null;

$idDistrito = isset($_POST["id_distrito"]) && $_POST["id_distrito"] !== ""
    ? (int) $_POST["id_distrito"]
    : null;

//======================================================
// VALIDAR ID
//======================================================

if ($idProveedor <= 0) {

    http_response_code(400);

    $respuesta["message"] =
        "El proveedor indicado no es válido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VALIDAR NOMBRE
//======================================================

if ($nombre === "") {

    http_response_code(400);

    $respuesta["message"] =
        "El nombre del proveedor es obligatorio.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VALIDAR RUC
//======================================================

if (!preg_match('/^\d{11}$/', $ruc)) {

    http_response_code(400);

    $respuesta["message"] =
        "El RUC debe contener exactamente 11 dígitos.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VALIDAR CELULAR
//======================================================

if ($celular === "") {

    http_response_code(400);

    $respuesta["message"] =
        "El celular del proveedor es obligatorio.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

if (!preg_match('/^\d+$/', $celular)) {

    http_response_code(400);

    $respuesta["message"] =
        "El celular solo debe contener números.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VALIDAR EMAIL
//======================================================

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    $respuesta["message"] =
        "El correo electrónico no es válido.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VALIDAR DIRECCIÓN
//======================================================

if ($direccion === "") {

    http_response_code(400);

    $respuesta["message"] =
        "La dirección del proveedor es obligatoria.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VERIFICAR PROVEEDOR
//======================================================

$sqlProveedor = "
    SELECT
        id_provedor
    FROM provedores
    WHERE id_provedor = ?
      AND id_user = ?
    LIMIT 1
";

$stmtProveedor = mysqli_prepare(
    $conexion,
    $sqlProveedor
);

if (!$stmtProveedor) {

    http_response_code(500);

    $respuesta["message"] =
        "No se pudo verificar el proveedor.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

mysqli_stmt_bind_param(
    $stmtProveedor,
    "ii",
    $idProveedor,
    $idUser
);

if (!mysqli_stmt_execute($stmtProveedor)) {

    mysqli_stmt_close($stmtProveedor);

    http_response_code(500);

    $respuesta["message"] =
        "No se pudo verificar el proveedor.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$resultadoProveedor = mysqli_stmt_get_result(
    $stmtProveedor
);

$proveedorExiste = mysqli_fetch_assoc(
    $resultadoProveedor
);

mysqli_stmt_close($stmtProveedor);

if (!$proveedorExiste) {

    http_response_code(404);

    $respuesta["message"] =
        "El proveedor no existe o no pertenece al usuario actual.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VALIDAR RUC DUPLICADO
//======================================================

$sqlRuc = "
    SELECT
        id_provedor
    FROM provedores
    WHERE ruc = ?
      AND id_user = ?
      AND id_provedor <> ?
      AND Eliminado = 0
    LIMIT 1
";

$stmtRuc = mysqli_prepare(
    $conexion,
    $sqlRuc
);

if (!$stmtRuc) {

    http_response_code(500);

    $respuesta["message"] =
        "No se pudo validar el RUC.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

mysqli_stmt_bind_param(
    $stmtRuc,
    "sii",
    $ruc,
    $idUser,
    $idProveedor
);

if (!mysqli_stmt_execute($stmtRuc)) {

    mysqli_stmt_close($stmtRuc);

    http_response_code(500);

    $respuesta["message"] =
        "No se pudo validar el RUC.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$resultadoRuc = mysqli_stmt_get_result(
    $stmtRuc
);

$rucDuplicado = mysqli_fetch_assoc(
    $resultadoRuc
);

mysqli_stmt_close($stmtRuc);

if ($rucDuplicado) {

    http_response_code(409);

    $respuesta["message"] =
        "Ya existe otro proveedor registrado con ese RUC.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// ACTUALIZAR PROVEEDOR
//======================================================
//
// Se utiliza una consulta dinámica para permitir que
// los campos de ubicación puedan quedar en NULL.
//

$sqlActualizar = "
    UPDATE provedores
    SET
        nombre = ?,
        ruc = ?,
        direccion = ?,
        email = ?,
        celular = ?,
        id_pais = ?,
        id_departamento = ?,
        id_provincia = ?,
        id_distrito = ?,
        fecha_actualizado = NOW()
    WHERE id_provedor = ?
      AND id_user = ?
    LIMIT 1
";

//======================================================
// PREPARAR
//======================================================

$stmtActualizar = mysqli_prepare(
    $conexion,
    $sqlActualizar
);

if (!$stmtActualizar) {

    http_response_code(500);

    $respuesta["message"] =
        "No se pudo preparar la actualización del proveedor.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VINCULAR PARÁMETROS
//======================================================
//
// mysqli no acepta directamente tipos NULL mediante "i"
// cuando queremos enviar NULL real, por eso se preparan
// las variables como NULL cuando corresponda.
//
//======================================================

mysqli_stmt_bind_param(
    $stmtActualizar,
    "sssssiiiiii",
    $nombre,
    $ruc,
    $direccion,
    $email,
    $celular,
    $idPais,
    $idDepartamento,
    $idProvincia,
    $idDistrito,
    $idProveedor,
    $idUser
);

//======================================================
// EJECUTAR
//======================================================

if (!mysqli_stmt_execute($stmtActualizar)) {

    $error = mysqli_stmt_error($stmtActualizar);

    mysqli_stmt_close($stmtActualizar);

    http_response_code(500);

    $respuesta["message"] =
        "No se pudo actualizar el proveedor.";

    $respuesta["error"] = $error;

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// FILAS AFECTADAS
//======================================================

$filasAfectadas = mysqli_stmt_affected_rows(
    $stmtActualizar
);

mysqli_stmt_close($stmtActualizar);

//======================================================
// RESPUESTA
//======================================================

$respuesta["success"] = true;

$respuesta["message"] =
    $filasAfectadas > 0
    ? "Los datos del proveedor fueron actualizados correctamente."
    : "No se detectaron cambios en los datos del proveedor.";

$respuesta["id_provedor"] = $idProveedor;

//======================================================
// SALIDA
//======================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
