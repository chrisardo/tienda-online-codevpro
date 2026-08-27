<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_imagen_empleado.php
// Módulo: Editar Imagen del Empleado
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');


//=====================================================
// RESPUESTA BASE
//=====================================================

$respuesta = [
    'success' => false,
    'mensaje' => '',
    'data' => null
];


//=====================================================
// ID USUARIO
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


if ($idUser <= 0) {

    $respuesta['mensaje'] = 'Sesión no válida.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// ID EMPLEADO
//=====================================================

$idEmpleado = isset($_POST['id_empleado'])
    ? (int) $_POST['id_empleado']
    : 0;


if ($idEmpleado <= 0) {

    $respuesta['mensaje'] =
        'El empleado seleccionado no es válido.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// VERIFICAR ARCHIVO RECIBIDO
//=====================================================

if (
    !isset($_FILES['imagen']) ||
    !is_array($_FILES['imagen'])
) {

    $respuesta['mensaje'] =
        'No se recibió ninguna imagen.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


$archivo = $_FILES['imagen'];


//=====================================================
// VERIFICAR ERROR DE SUBIDA
//=====================================================

if (
    !isset($archivo['error']) ||
    $archivo['error'] !== UPLOAD_ERR_OK
) {

    $mensajeError = 'No se pudo subir la imagen.';

    switch ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) {

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:

            $mensajeError =
                'La imagen supera el tamaño máximo permitido.';

            break;


        case UPLOAD_ERR_PARTIAL:

            $mensajeError =
                'La imagen se subió parcialmente. Inténtelo nuevamente.';

            break;


        case UPLOAD_ERR_NO_FILE:

            $mensajeError =
                'Seleccione una imagen.';

            break;


        case UPLOAD_ERR_NO_TMP_DIR:

            $mensajeError =
                'No existe el directorio temporal del servidor.';

            break;


        case UPLOAD_ERR_CANT_WRITE:

            $mensajeError =
                'No se pudo guardar temporalmente la imagen.';

            break;


        case UPLOAD_ERR_EXTENSION:

            $mensajeError =
                'Una extensión del servidor impidió subir la imagen.';

            break;
    }


    $respuesta['mensaje'] = $mensajeError;

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// RUTA TEMPORAL
//=====================================================

$rutaTemporal = $archivo['tmp_name'] ?? '';


if (
    $rutaTemporal === '' ||
    !is_uploaded_file($rutaTemporal)
) {

    $respuesta['mensaje'] =
        'El archivo recibido no es válido.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// TAMAÑO MÁXIMO
//=====================================================
//
// 2.7 MB
//
//=====================================================

$maximoBytes = 2.7 * 1024 * 1024;

$tamanoArchivo = isset($archivo['size'])
    ? (int) $archivo['size']
    : 0;


if ($tamanoArchivo <= 0) {

    $respuesta['mensaje'] =
        'La imagen está vacía.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($tamanoArchivo > $maximoBytes) {

    $respuesta['mensaje'] =
        'La imagen no puede superar los 2.7 MB.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// VALIDAR IMAGEN REAL
//=====================================================
//
// No confiamos únicamente en la extensión
// enviada por el navegador.
//
//=====================================================

$informacionImagen = @getimagesize($rutaTemporal);


if ($informacionImagen === false) {

    $respuesta['mensaje'] =
        'El archivo seleccionado no es una imagen válida.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// MIME REAL
//=====================================================

$finfo = new finfo(FILEINFO_MIME_TYPE);

$mime = $finfo->file($rutaTemporal);


$mimesPermitidos = [

    'image/jpeg' => 'jpg',

    'image/png' => 'png',

    'image/webp' => 'webp'

];


if (!isset($mimesPermitidos[$mime])) {

    $respuesta['mensaje'] =
        'Formato de imagen no permitido. Utilice JPG, JPEG, PNG o WEBP.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// OBTENER EXTENSIÓN REAL
//=====================================================

$extension = $mimesPermitidos[$mime];


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    $respuesta['mensaje'] =
        'No se pudo establecer la conexión con la base de datos.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// TRANSACCIÓN
//=====================================================

try {

    mysqli_begin_transaction($conexion);


    //=================================================
    // VERIFICAR EMPLEADO
    //=================================================

    $sqlVerificar = "
        SELECT
            id_empleado,
            nombre,
            apellido
        FROM empleados
        WHERE id_empleado = ?
          AND id_user = ?
        LIMIT 1
    ";


    $stmtVerificar = mysqli_prepare(
        $conexion,
        $sqlVerificar
    );


    if (!$stmtVerificar) {

        throw new Exception(
            'Error al preparar la verificación del empleado: ' .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmtVerificar,
        "ii",
        $idEmpleado,
        $idUser
    );


    if (!mysqli_stmt_execute($stmtVerificar)) {

        throw new Exception(
            'Error al verificar el empleado: ' .
                mysqli_stmt_error($stmtVerificar)
        );
    }


    $resultadoEmpleado =
        mysqli_stmt_get_result($stmtVerificar);


    $empleado =
        mysqli_fetch_assoc($resultadoEmpleado);


    mysqli_stmt_close($stmtVerificar);


    if (!$empleado) {

        throw new Exception(
            'No se encontró el empleado seleccionado.'
        );
    }


    //=================================================
    // LEER IMAGEN
    //=================================================

    $contenidoImagen = file_get_contents(
        $rutaTemporal
    );


    if ($contenidoImagen === false) {

        throw new Exception(
            'No se pudo leer la imagen seleccionada.'
        );
    }


    if ($contenidoImagen === '') {

        throw new Exception(
            'La imagen seleccionada está vacía.'
        );
    }


    //=================================================
    // ACTUALIZAR IMAGEN
    //=================================================

    $sqlActualizar = "
        UPDATE empleados
        SET
            imagen = ?,
            fecha_actualizado = CURDATE()
        WHERE id_empleado = ?
          AND id_user = ?
    ";


    $stmtActualizar = mysqli_prepare(
        $conexion,
        $sqlActualizar
    );


    if (!$stmtActualizar) {

        throw new Exception(
            'Error al preparar la actualización de la imagen: ' .
                mysqli_error($conexion)
        );
    }


    //=================================================
    // BIND DE IMAGEN LONGBLOB
    //=================================================

    $imagen = null;


    mysqli_stmt_bind_param(
        $stmtActualizar,
        "bii",
        $imagen,
        $idEmpleado,
        $idUser
    );


    //=================================================
    // ENVIAR CONTENIDO BLOB
    //=================================================

    mysqli_stmt_send_long_data(
        $stmtActualizar,
        0,
        $contenidoImagen
    );


    //=================================================
    // EJECUTAR UPDATE
    //=================================================

    if (!mysqli_stmt_execute($stmtActualizar)) {

        throw new Exception(
            'Error al actualizar la imagen del empleado: ' .
                mysqli_stmt_error($stmtActualizar)
        );
    }


    $filasAfectadas =
        mysqli_stmt_affected_rows($stmtActualizar);


    mysqli_stmt_close($stmtActualizar);


    //=================================================
    // OBTENER FECHA ACTUALIZADA
    //=================================================

    $sqlFecha = "
        SELECT
            fecha_actualizado
        FROM empleados
        WHERE id_empleado = ?
          AND id_user = ?
        LIMIT 1
    ";


    $stmtFecha = mysqli_prepare(
        $conexion,
        $sqlFecha
    );


    if (!$stmtFecha) {

        throw new Exception(
            'Error al obtener la fecha de actualización: ' .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmtFecha,
        "ii",
        $idEmpleado,
        $idUser
    );


    if (!mysqli_stmt_execute($stmtFecha)) {

        throw new Exception(
            'Error al consultar la fecha actualizada: ' .
                mysqli_stmt_error($stmtFecha)
        );
    }


    $resultadoFecha =
        mysqli_stmt_get_result($stmtFecha);


    $datosFecha =
        mysqli_fetch_assoc($resultadoFecha);


    mysqli_stmt_close($stmtFecha);


    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    mysqli_commit($conexion);


    //=================================================
    // CONSTRUIR NOMBRE
    //=================================================

    $nombreEmpleado =
        trim(
            ($empleado['nombre'] ?? '') .
                ' ' .
                ($empleado['apellido'] ?? '')
        );


    if ($nombreEmpleado === '') {

        $nombreEmpleado = 'empleado';
    }


    //=================================================
    // CONVERTIR IMAGEN A DATA URI
    //=================================================
    //
    // Esto permite que JavaScript pueda actualizar
    // inmediatamente la imagen mostrada sin tener
    // que recargar toda la página.
    //
    //=================================================

    $imagenBase64 =
        base64_encode($contenidoImagen);


    $imagenDataURI =
        'data:' .
        $mime .
        ';base64,' .
        $imagenBase64;


    //=================================================
    // RESPUESTA EXITOSA
    //=================================================

    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'La imagen del empleado se actualizó correctamente.';

    $respuesta['data'] = [

        'id_empleado' =>
        $idEmpleado,

        'nombre' =>
        $nombreEmpleado,

        'extension' =>
        $extension,

        'mime' =>
        $mime,

        'tamano' =>
        $tamanoArchivo,

        'tamano_formateado' =>
        number_format(
            $tamanoArchivo / 1024 / 1024,
            2
        ) . ' MB',

        'filas_afectadas' =>
        $filasAfectadas,

        'fecha_actualizado' =>
        $datosFecha['fecha_actualizado'] ?? null,

        'imagen' =>
        $imagenDataURI
    ];
} catch (Throwable $e) {

    //=================================================
    // ROLLBACK
    //=================================================

    if (
        isset($conexion) &&
        $conexion instanceof mysqli
    ) {

        mysqli_rollback($conexion);
    }


    //=================================================
    // REGISTRAR ERROR
    //=================================================

    error_log(
        'Error actualizar imagen empleado: ' .
            $e->getMessage()
    );


    //=================================================
    // RESPUESTA ERROR
    //=================================================

    $respuesta['success'] = false;

    $respuesta['mensaje'] =
        $e->getMessage();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
