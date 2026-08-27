<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_imagen_proveedor.php
// Módulo: Lista de Proveedores
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// RESPUESTA JSON
//=====================================================

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function respuestaJSON($datos)
{
    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    respuestaJSON([
        'success' => false,
        'message' => 'Sesión no válida. Inicia sesión nuevamente.'
    ]);
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudo establecer la conexión con la base de datos.'
    ]);
}

//=====================================================
// UTF-8
//=====================================================

mysqli_set_charset(
    $conexion,
    "utf8mb4"
);


//#####################################################################
//#####################################################################
// VALIDAR MÉTODO
//#####################################################################
//#####################################################################

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    respuestaJSON([
        'success' => false,
        'message' => 'Método de solicitud no permitido.'
    ]);
}


//#####################################################################
//#####################################################################
// OBTENER ID DEL PROVEEDOR
//#####################################################################
//#####################################################################

$idProveedor = isset($_POST['id_provedor'])
    ? (int) $_POST['id_provedor']
    : 0;

if ($idProveedor <= 0) {

    respuestaJSON([
        'success' => false,
        'message' => 'El ID del proveedor no es válido.'
    ]);
}


//#####################################################################
//#####################################################################
// VALIDAR ARCHIVO
//#####################################################################
//#####################################################################

if (
    !isset($_FILES['imagen']) ||
    !is_array($_FILES['imagen'])
) {

    respuestaJSON([
        'success' => false,
        'message' => 'No se recibió ninguna imagen.'
    ]);
}


//=====================================================
// INFORMACIÓN DEL ARCHIVO
//=====================================================

$archivo = $_FILES['imagen'];

$errorArchivo = isset($archivo['error'])
    ? (int) $archivo['error']
    : UPLOAD_ERR_NO_FILE;


//=====================================================
// VALIDAR ERROR DE SUBIDA
//=====================================================

if ($errorArchivo !== UPLOAD_ERR_OK) {

    $mensajesErrores = [

        UPLOAD_ERR_INI_SIZE =>
        'La imagen supera el tamaño máximo permitido por el servidor.',

        UPLOAD_ERR_FORM_SIZE =>
        'La imagen supera el tamaño máximo permitido.',

        UPLOAD_ERR_PARTIAL =>
        'La imagen se cargó parcialmente. Inténtalo nuevamente.',

        UPLOAD_ERR_NO_FILE =>
        'Debes seleccionar una imagen.',

        UPLOAD_ERR_NO_TMP_DIR =>
        'No existe el directorio temporal del servidor.',

        UPLOAD_ERR_CANT_WRITE =>
        'No se pudo guardar temporalmente la imagen.',

        UPLOAD_ERR_EXTENSION =>
        'Una extensión del servidor impidió cargar la imagen.'
    ];

    respuestaJSON([
        'success' => false,
        'message' =>
        $mensajesErrores[$errorArchivo]
            ?? 'Ocurrió un error al cargar la imagen.'
    ]);
}


//#####################################################################
//#####################################################################
// VALIDAR ARCHIVO SUBIDO
//#####################################################################
//#####################################################################

if (
    !isset($archivo['tmp_name']) ||
    !is_uploaded_file($archivo['tmp_name'])
) {

    respuestaJSON([
        'success' => false,
        'message' => 'El archivo recibido no es válido.'
    ]);
}


//#####################################################################
//#####################################################################
// VALIDAR TAMAÑO
//#####################################################################
//#####################################################################

$MAX_TAMANO = (int) (2.7 * 1024 * 1024);

$tamanoArchivo = isset($archivo['size'])
    ? (int) $archivo['size']
    : 0;

if ($tamanoArchivo <= 0) {

    respuestaJSON([
        'success' => false,
        'message' => 'La imagen está vacía o no pudo ser procesada.'
    ]);
}

if ($tamanoArchivo > $MAX_TAMANO) {

    respuestaJSON([
        'success' => false,
        'message' => 'La imagen supera el tamaño máximo permitido de 2.7 MB.'
    ]);
}


//#####################################################################
//#####################################################################
// VALIDAR MIME REAL
//#####################################################################
//#####################################################################

$tiposPermitidos = [

    'image/jpeg',
    'image/png',
    'image/webp'

];

$finfo = finfo_open(FILEINFO_MIME_TYPE);

if (!$finfo) {

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudo validar el formato de la imagen.'
    ]);
}

$tipoMime = finfo_file(
    $finfo,
    $archivo['tmp_name']
);

finfo_close($finfo);


//=====================================================
// VALIDAR MIME
//=====================================================

if (!in_array($tipoMime, $tiposPermitidos, true)) {

    respuestaJSON([
        'success' => false,
        'message' => 'Formato no permitido. Solo se aceptan JPG, JPEG, PNG y WEBP.'
    ]);
}


//#####################################################################
//#####################################################################
// VALIDAR QUE REALMENTE SEA UNA IMAGEN
//#####################################################################
//#####################################################################

$informacionImagen = @getimagesize(
    $archivo['tmp_name']
);

if ($informacionImagen === false) {

    respuestaJSON([
        'success' => false,
        'message' => 'El archivo seleccionado no es una imagen válida.'
    ]);
}


//=====================================================
// VALIDAR DIMENSIONES
//=====================================================

$ancho = isset($informacionImagen[0])
    ? (int) $informacionImagen[0]
    : 0;

$alto = isset($informacionImagen[1])
    ? (int) $informacionImagen[1]
    : 0;

if ($ancho <= 0 || $alto <= 0) {

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudieron determinar las dimensiones de la imagen.'
    ]);
}


//#####################################################################
//#####################################################################
// OBTENER CONTENIDO BINARIO
//#####################################################################
//#####################################################################

$contenidoImagen = file_get_contents(
    $archivo['tmp_name']
);

if ($contenidoImagen === false) {

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudo leer la imagen seleccionada.'
    ]);
}

if ($contenidoImagen === '') {

    respuestaJSON([
        'success' => false,
        'message' => 'La imagen seleccionada está vacía.'
    ]);
}


//#####################################################################
//#####################################################################
// VERIFICAR QUE EL PROVEEDOR PERTENEZCA AL USUARIO
//#####################################################################
//#####################################################################

$sqlProveedor = "
    SELECT
        id_provedor,
        nombre
    FROM provedores
    WHERE
        id_provedor = ?
        AND id_user = ?
    LIMIT 1
";

$stmtProveedor = mysqli_prepare(
    $conexion,
    $sqlProveedor
);

if (!$stmtProveedor) {

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudo verificar el proveedor.',
        'error' => mysqli_error($conexion)
    ]);
}


//=====================================================
// BIND
//=====================================================

mysqli_stmt_bind_param(
    $stmtProveedor,
    "ii",
    $idProveedor,
    $idUser
);


//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmtProveedor)) {

    $error = mysqli_stmt_error(
        $stmtProveedor
    );

    mysqli_stmt_close(
        $stmtProveedor
    );

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudo verificar el proveedor.',
        'error' => $error
    ]);
}


//=====================================================
// RESULTADO
//=====================================================

$resultadoProveedor = mysqli_stmt_get_result(
    $stmtProveedor
);

$proveedor = $resultadoProveedor
    ? mysqli_fetch_assoc($resultadoProveedor)
    : null;

mysqli_stmt_close(
    $stmtProveedor
);


//#####################################################################
//#####################################################################
// VALIDAR PROVEEDOR
//#####################################################################
//#####################################################################

if (!$proveedor) {

    respuestaJSON([
        'success' => false,
        'message' => 'El proveedor no existe o no pertenece al usuario actual.'
    ]);
}


//#####################################################################
//#####################################################################
// ACTUALIZAR IMAGEN
//#####################################################################
//#####################################################################

$sqlActualizar = "
    UPDATE provedores
    SET
        imagen = ?,
        fecha_actualizado = NOW()
    WHERE
        id_provedor = ?
        AND id_user = ?
    LIMIT 1
";

$stmtActualizar = mysqli_prepare(
    $conexion,
    $sqlActualizar
);

if (!$stmtActualizar) {

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudo preparar la actualización de la imagen.',
        'error' => mysqli_error($conexion)
    ]);
}


//=====================================================
// BIND BLOB
//=====================================================

mysqli_stmt_bind_param(
    $stmtActualizar,
    "bii",
    $null,
    $idProveedor,
    $idUser
);


//=====================================================
// ENVIAR BLOB POR PARTES
//=====================================================

$null = null;

mysqli_stmt_send_long_data(
    $stmtActualizar,
    0,
    $contenidoImagen
);


//=====================================================
// EJECUTAR ACTUALIZACIÓN
//=====================================================

if (!mysqli_stmt_execute($stmtActualizar)) {

    $error = mysqli_stmt_error(
        $stmtActualizar
    );

    mysqli_stmt_close(
        $stmtActualizar
    );

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudo actualizar la imagen del proveedor.',
        'error' => $error
    ]);
}


//=====================================================
// FILAS AFECTADAS
//=====================================================

$filasAfectadas = mysqli_stmt_affected_rows(
    $stmtActualizar
);

mysqli_stmt_close(
    $stmtActualizar
);


//#####################################################################
//#####################################################################
// RESPUESTA
//#####################################################################
//#####################################################################

respuestaJSON([

    'success' => true,

    'message' =>
    'La imagen del proveedor fue actualizada correctamente.',

    'id_provedor' => $idProveedor,

    'nombre' =>
    $proveedor['nombre'] ?? '',

    'tamano' => $tamanoArchivo,

    'tipo' => $tipoMime,

    'ancho' => $ancho,

    'alto' => $alto,

    'actualizado' => $filasAfectadas > 0

]);
