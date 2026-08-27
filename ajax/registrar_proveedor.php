<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/registrar_proveedor.php
// Módulo: Registrar Proveedor
// Sistema: Inventa
//=====================================================

declare(strict_types=1);


//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header('Content-Type: application/json; charset=utf-8');


//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responder(
    bool $estado,
    string $mensaje,
    array $datos = [],
    int $codigoHttp = 200
): never {

    http_response_code($codigoHttp);

    echo json_encode(
        [
            'estado'  => $estado,
            'mensaje' => $mensaje,
            'datos'   => $datos
        ],
        JSON_UNESCAPED_UNICODE
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

    responder(
        false,
        'La sesión ha expirado. Inicie sesión nuevamente.',
        [],
        401
    );
}


//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    responder(
        false,
        'Método de solicitud no permitido.',
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

if (!isset($conexion) || !($conexion instanceof mysqli)) {

    responder(
        false,
        'No se pudo establecer la conexión con la base de datos.',
        [],
        500
    );
}


//=====================================================
// CONFIGURACIÓN MYSQLI
//=====================================================

mysqli_report(MYSQLI_REPORT_OFF);

$conexion->set_charset('utf8mb4');


//=====================================================
// RECIBIR DATOS
//=====================================================

$nombre = isset($_POST['nombre'])
    ? trim((string) $_POST['nombre'])
    : '';

$ruc = isset($_POST['ruc'])
    ? trim((string) $_POST['ruc'])
    : '';

$celular = isset($_POST['celular'])
    ? trim((string) $_POST['celular'])
    : '';

$email = isset($_POST['email'])
    ? trim((string) $_POST['email'])
    : '';

$direccion = isset($_POST['direccion'])
    ? trim((string) $_POST['direccion'])
    : '';

$idPais = isset($_POST['id_pais'])
    ? (int) $_POST['id_pais']
    : 0;

$idDepartamento = isset($_POST['id_departamento'])
    ? (int) $_POST['id_departamento']
    : 0;

$idProvincia = isset($_POST['provincia'])
    ? (int) $_POST['provincia']
    : 0;

$idDistrito = isset($_POST['id_distrito'])
    ? (int) $_POST['id_distrito']
    : 0;


//=====================================================
// NORMALIZAR DATOS
//=====================================================

$nombre = preg_replace('/\s+/', ' ', $nombre);
$nombre = trim($nombre);

$ruc = preg_replace('/\D/', '', $ruc);

$celular = preg_replace('/\D/', '', $celular);

$email = strtolower($email);

$direccion = preg_replace('/\s+/', ' ', $direccion);
$direccion = trim($direccion);


//=====================================================
// VALIDAR NOMBRE
//=====================================================

if ($nombre === '') {

    responder(
        false,
        'Ingrese el nombre o razón social del proveedor.',
        [],
        400
    );
}


if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 150) {

    responder(
        false,
        'El nombre o razón social debe tener entre 2 y 150 caracteres.',
        [],
        400
    );
}


//=====================================================
// VALIDAR RUC
//=====================================================

if ($ruc === '') {

    responder(
        false,
        'Ingrese el RUC del proveedor.',
        [],
        400
    );
}


if (!preg_match('/^[0-9]{11}$/', $ruc)) {

    responder(
        false,
        'El RUC debe contener exactamente 11 dígitos.',
        [],
        400
    );
}


//=====================================================
// VALIDAR CELULAR
//=====================================================

if ($celular === '') {

    responder(
        false,
        'Ingrese el número de celular del proveedor.',
        [],
        400
    );
}


if (!preg_match('/^[0-9]{9,15}$/', $celular)) {

    responder(
        false,
        'El número de celular debe contener entre 9 y 15 dígitos.',
        [],
        400
    );
}


//=====================================================
// VALIDAR EMAIL
//=====================================================

if ($email === '') {

    responder(
        false,
        'Ingrese el correo electrónico del proveedor.',
        [],
        400
    );
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    responder(
        false,
        'Ingrese un correo electrónico válido.',
        [],
        400
    );
}


if (mb_strlen($email) > 150) {

    responder(
        false,
        'El correo electrónico no puede superar los 150 caracteres.',
        [],
        400
    );
}


//=====================================================
// VALIDAR DIRECCIÓN
//=====================================================

if (mb_strlen($direccion) > 250) {

    responder(
        false,
        'La dirección no puede superar los 250 caracteres.',
        [],
        400
    );
}


//=====================================================
// VALIDAR PAÍS
//=====================================================

if ($idPais <= 0) {

    responder(
        false,
        'Seleccione un país.',
        [],
        400
    );
}


//=====================================================
// VALIDAR DEPARTAMENTO
//=====================================================

if ($idDepartamento <= 0) {

    responder(
        false,
        'Seleccione un departamento.',
        [],
        400
    );
}


//=====================================================
// VALIDAR PROVINCIA
//=====================================================

if ($idProvincia <= 0) {

    responder(
        false,
        'Seleccione una provincia.',
        [],
        400
    );
}


//=====================================================
// VALIDAR DISTRITO
//=====================================================

if ($idDistrito <= 0) {

    responder(
        false,
        'Seleccione un distrito.',
        [],
        400
    );
}


//=====================================================
// VALIDAR PAÍS
//=====================================================

$sqlPais = "
    SELECT
        id_pais
    FROM pais
    WHERE id_pais = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";


$stmtPais = $conexion->prepare($sqlPais);


if (!$stmtPais) {

    responder(
        false,
        'No se pudo validar el país seleccionado.',
        [],
        500
    );
}


$stmtPais->bind_param(
    'ii',
    $idPais,
    $idUser
);


$stmtPais->execute();

$resultadoPais = $stmtPais->get_result();

$paisExiste = $resultadoPais->num_rows > 0;

$stmtPais->close();


if (!$paisExiste) {

    responder(
        false,
        'El país seleccionado no es válido.',
        [],
        400
    );
}


//=====================================================
// VALIDAR DEPARTAMENTO
//=====================================================

$sqlDepartamento = "
    SELECT
        id_departamento
    FROM departamento
    WHERE id_departamento = ?
      AND id_pais = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";


$stmtDepartamento = $conexion->prepare($sqlDepartamento);


if (!$stmtDepartamento) {

    responder(
        false,
        'No se pudo validar el departamento seleccionado.',
        [],
        500
    );
}


$stmtDepartamento->bind_param(
    'iii',
    $idDepartamento,
    $idPais,
    $idUser
);


$stmtDepartamento->execute();

$resultadoDepartamento = $stmtDepartamento->get_result();

$departamentoExiste = $resultadoDepartamento->num_rows > 0;

$stmtDepartamento->close();


if (!$departamentoExiste) {

    responder(
        false,
        'El departamento seleccionado no pertenece al país indicado.',
        [],
        400
    );
}


//=====================================================
// VALIDAR PROVINCIA
//=====================================================

$sqlProvincia = "
    SELECT
        id_provincia
    FROM provincia
    WHERE id_provincia = ?
      AND id_departamento = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";


$stmtProvincia = $conexion->prepare($sqlProvincia);


if (!$stmtProvincia) {

    responder(
        false,
        'No se pudo validar la provincia seleccionada.',
        [],
        500
    );
}


$stmtProvincia->bind_param(
    'iii',
    $idProvincia,
    $idDepartamento,
    $idUser
);


$stmtProvincia->execute();

$resultadoProvincia = $stmtProvincia->get_result();

$provinciaExiste = $resultadoProvincia->num_rows > 0;

$stmtProvincia->close();


if (!$provinciaExiste) {

    responder(
        false,
        'La provincia seleccionada no pertenece al departamento indicado.',
        [],
        400
    );
}


//=====================================================
// VALIDAR DISTRITO
//=====================================================

$sqlDistrito = "
    SELECT
        id_distrito
    FROM distrito
    WHERE id_distrito = ?
      AND id_provincia = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";


$stmtDistrito = $conexion->prepare($sqlDistrito);


if (!$stmtDistrito) {

    responder(
        false,
        'No se pudo validar el distrito seleccionado.',
        [],
        500
    );
}


$stmtDistrito->bind_param(
    'iii',
    $idDistrito,
    $idProvincia,
    $idUser
);


$stmtDistrito->execute();

$resultadoDistrito = $stmtDistrito->get_result();

$distritoExiste = $resultadoDistrito->num_rows > 0;

$stmtDistrito->close();


if (!$distritoExiste) {

    responder(
        false,
        'El distrito seleccionado no pertenece a la provincia indicada.',
        [],
        400
    );
}


//=====================================================
// VERIFICAR RUC EXISTENTE
//=====================================================

$sqlRuc = "
    SELECT
        id_provedor,
        nombre
    FROM provedores
    WHERE ruc = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";


$stmtRuc = $conexion->prepare($sqlRuc);


if (!$stmtRuc) {

    responder(
        false,
        'No se pudo verificar el RUC del proveedor.',
        [],
        500
    );
}


$stmtRuc->bind_param(
    'si',
    $ruc,
    $idUser
);


$stmtRuc->execute();

$resultadoRuc = $stmtRuc->get_result();

$proveedorRuc = $resultadoRuc->fetch_assoc();

$stmtRuc->close();


if ($proveedorRuc) {

    responder(
        false,
        'Ya existe un proveedor registrado con el RUC ' . $ruc . '.',
        [
            'id_provedor' => (int) $proveedorRuc['id_provedor']
        ],
        409
    );
}


//=====================================================
// VERIFICAR EMAIL EXISTENTE
//=====================================================

$sqlEmail = "
    SELECT
        id_provedor,
        nombre
    FROM provedores
    WHERE email = ?
      AND id_user = ?
      AND Eliminado = 0
    LIMIT 1
";


$stmtEmail = $conexion->prepare($sqlEmail);


if (!$stmtEmail) {

    responder(
        false,
        'No se pudo verificar el correo del proveedor.',
        [],
        500
    );
}


$stmtEmail->bind_param(
    'si',
    $email,
    $idUser
);


$stmtEmail->execute();

$resultadoEmail = $stmtEmail->get_result();

$proveedorEmail = $resultadoEmail->fetch_assoc();

$stmtEmail->close();


if ($proveedorEmail) {

    responder(
        false,
        'Ya existe un proveedor registrado con el correo ' . $email . '.',
        [
            'id_provedor' => (int) $proveedorEmail['id_provedor']
        ],
        409
    );
}

//=====================================================
// PROCESAR IMAGEN
//=====================================================

$imagenBinaria = null;

$imagenEnviada = false;


if (
    isset($_FILES['imagen']) &&
    is_array($_FILES['imagen'])
) {

    $archivo = $_FILES['imagen'];

    //==============================================
    // VALIDAR ERROR
    //==============================================

    if (
        isset($archivo['error']) &&
        $archivo['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($archivo['error'] !== UPLOAD_ERR_OK) {

            responder(
                false,
                'No se pudo cargar la imagen del proveedor.',
                [],
                400
            );
        }

        $imagenEnviada = true;
    }


    //==============================================
    // VALIDAR TAMAÑO
    //==============================================

    if ($imagenEnviada) {

        $maximoImagen = 2.8 * 1024 * 1024;

        if ((int) $archivo['size'] > $maximoImagen) {

            responder(
                false,
                'La imagen supera el tamaño máximo permitido de 2 MB.',
                [],
                400
            );
        }


        //==========================================
        // VALIDAR TIPO MIME REAL
        //==========================================

        $tiposPermitidos = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];


        $finfo = finfo_open(FILEINFO_MIME_TYPE);


        if ($finfo === false) {

            responder(
                false,
                'No se pudo validar el formato de la imagen.',
                [],
                500
            );
        }


        $tipoMime = finfo_file(
            $finfo,
            $archivo['tmp_name']
        );


        finfo_close($finfo);


        if (!in_array($tipoMime, $tiposPermitidos, true)) {

            responder(
                false,
                'El formato de imagen no es válido. Solo se permiten JPG, PNG y WEBP.',
                [],
                400
            );
        }


        //==========================================
        // VALIDAR QUE REALMENTE SEA UNA IMAGEN
        //==========================================

        $informacionImagen = @getimagesize(
            $archivo['tmp_name']
        );


        if ($informacionImagen === false) {

            responder(
                false,
                'El archivo seleccionado no es una imagen válida.',
                [],
                400
            );
        }


        //==========================================
        // LEER IMAGEN
        //==========================================

        $imagenBinaria = file_get_contents(
            $archivo['tmp_name']
        );


        if ($imagenBinaria === false) {

            responder(
                false,
                'No se pudo procesar la imagen seleccionada.',
                [],
                500
            );
        }
    }
}


//=====================================================
// FECHAS
//=====================================================

$fechaActual = date('Y-m-d');


//=====================================================
// INICIAR TRANSACCIÓN
//=====================================================

$conexion->begin_transaction();


try {

    //=================================================
    // INSERTAR PROVEEDOR CON IMAGEN
    //=================================================

    if ($imagenEnviada && $imagenBinaria !== null) {

        $sqlInsertar = "
            INSERT INTO provedores
            (
                nombre,
                ruc,
                imagen,
                id_user,
                id_pais,
                id_departamento,
                id_provincia,
                id_distrito,
                direccion,
                email,
                celular,
                Eliminado,
                fecha_registro,
                fecha_actualizado
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
                ?,
                ?,
                ?,
                0,
                ?,
                ?
            )
        ";


        $stmtInsertar = $conexion->prepare($sqlInsertar);


        if (!$stmtInsertar) {

            throw new Exception(
                'No se pudo preparar el registro del proveedor.'
            );
        }


        $null = null;


        $stmtInsertar->bind_param(
            'ssbiiiiississ',
            $nombre,
            $ruc,
            $null,
            $idUser,
            $idPais,
            $idDepartamento,
            $idProvincia,
            $idDistrito,
            $direccion,
            $email,
            $celular,
            $fechaActual,
            $fechaActual
        );


        /*
         * Enviar el contenido BLOB.
         */

        $stmtInsertar->send_long_data(
            2,
            $imagenBinaria
        );


        if (!$stmtInsertar->execute()) {

            throw new Exception(
                'No se pudo registrar el proveedor: ' .
                    $stmtInsertar->error
            );
        }


        $idProveedor = $conexion->insert_id;

        $stmtInsertar->close();
    } else {

        //=================================================
        // INSERTAR PROVEEDOR SIN IMAGEN
        //=================================================

        $sqlInsertar = "
            INSERT INTO provedores
            (
                nombre,
                ruc,
                id_user,
                id_pais,
                id_departamento,
                id_provincia,
                id_distrito,
                direccion,
                email,
                celular,
                Eliminado,
                fecha_registro,
                fecha_actualizado
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
                ?,
                ?,
                0,
                ?,
                ?
            )
        ";


        $stmtInsertar = $conexion->prepare($sqlInsertar);


        if (!$stmtInsertar) {

            throw new Exception(
                'No se pudo preparar el registro del proveedor.'
            );
        }


        $stmtInsertar->bind_param(
            'ssiiiisssiss',
            $nombre,
            $ruc,
            $idUser,
            $idPais,
            $idDepartamento,
            $idProvincia,
            $idDistrito,
            $direccion,
            $email,
            $celular,
            $fechaActual,
            $fechaActual
        );


        if (!$stmtInsertar->execute()) {

            throw new Exception(
                'No se pudo registrar el proveedor: ' .
                    $stmtInsertar->error
            );
        }


        $idProveedor = $conexion->insert_id;

        $stmtInsertar->close();
    }


    //=================================================
    // VALIDAR ID GENERADO
    //=================================================

    if ($idProveedor <= 0) {

        throw new Exception(
            'No se pudo obtener el identificador del proveedor registrado.'
        );
    }


    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    $conexion->commit();


    //=================================================
    // RESPUESTA EXITOSA
    //=================================================

    responder(
        true,
        'Proveedor registrado correctamente.',
        [
            'id_provedor' => (int) $idProveedor,
            'nombre'      => $nombre,
            'ruc'         => $ruc
        ],
        200
    );
} catch (Throwable $e) {

    //=================================================
    // DESHACER TRANSACCIÓN
    //=================================================

    $conexion->rollback();


    //=================================================
    // REGISTRO DEL ERROR
    //=================================================

    error_log(
        'adm_registrar_proveedor.php: ' .
            $e->getMessage()
    );


    //=================================================
    // RESPUESTA ERROR
    //=================================================

    responder(
        false,
        'No se pudo registrar el proveedor. Intente nuevamente.',
        [],
        500
    );
}
