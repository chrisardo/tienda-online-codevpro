<?php
//======================================================
// CoDevPro Technology
// Archivo: ajax/registrar_empleado.php
// Módulo: Registrar Empleado
//======================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
error_reporting(E_ALL);

//======================================================
// CONEXIÓN
//======================================================

require_once "../controladores/conexion.php";

//======================================================
// FUNCIÓN RESPUESTA
//======================================================

function responder(
    bool $success,
    string $mensaje,
    array $data = []
): void {

    echo json_encode(
        [
            "success" => $success,
            "mensaje" => $mensaje,
            "data"    => $data
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//======================================================
// VALIDAR MÉTODO
//======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    responder(
        false,
        "Método de solicitud no permitido."
    );
}

//======================================================
// VALIDAR CONEXIÓN
//======================================================

if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    responder(
        false,
        "No existe una conexión válida con la base de datos."
    );
}

//======================================================
// UTF8
//======================================================

$conexion->set_charset("utf8mb4");

//======================================================
// OBTENER USUARIO ACTUAL
//======================================================

$idUser = 0;

if (isset($_SESSION["id_user"])) {

    $idUser = (int)$_SESSION["id_user"];

} elseif (isset($_SESSION["idUser"])) {

    $idUser = (int)$_SESSION["idUser"];

} elseif (isset($_SESSION["idUsuario"])) {

    $idUser = (int)$_SESSION["idUsuario"];
}

//======================================================
// VALIDAR SESIÓN
//======================================================

if ($idUser <= 0) {

    responder(
        false,
        "La sesión ha expirado. Vuelva a iniciar sesión."
    );
}

//======================================================
// RECIBIR DATOS
//======================================================

$nombre = trim(
    $_POST["nombre"] ?? ""
);

$apellido = trim(
    $_POST["apellido"] ?? ""
);

$dni = trim(
    $_POST["dni"] ?? ""
);

$celular = trim(
    $_POST["celular"] ?? ""
);

$direccion = trim(
    $_POST["direccion"] ?? ""
);

$email = strtolower(
    trim(
        $_POST["email"] ?? ""
    )
);

$contrasena = $_POST["contrasena"] ?? "";

$idPais = (int)(
    $_POST["id_pais"] ?? 0
);

$idDepartamento = (int)(
    $_POST["id_departamento"] ?? 0
);

$idProvincia = (int)(
    $_POST["id_provincia"] ?? 0
);

$idDistrito = (int)(
    $_POST["id_distrito"] ?? 0
);

$idRol = (int)(
    $_POST["id_rol"] ?? 0
);

$estado = strtoupper(
    trim(
        $_POST["estado"] ?? "ACTIVO"
    )
);

//======================================================
// VALIDAR DATOS BÁSICOS
//======================================================

if ($nombre === "") {

    responder(
        false,
        "El nombre del empleado es obligatorio."
    );
}

if ($apellido === "") {

    responder(
        false,
        "El apellido del empleado es obligatorio."
    );
}

if ($dni === "") {

    responder(
        false,
        "El DNI del empleado es obligatorio."
    );
}

if ($celular === "") {

    responder(
        false,
        "El celular del empleado es obligatorio."
    );
}

if ($direccion === "") {

    responder(
        false,
        "La dirección del empleado es obligatoria."
    );
}

if ($email === "") {

    responder(
        false,
        "El correo electrónico es obligatorio."
    );
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    responder(
        false,
        "El correo electrónico no tiene un formato válido."
    );
}

//======================================================
// VALIDAR IDS
//======================================================

if ($idPais <= 0) {

    responder(
        false,
        "Debe seleccionar un país."
    );
}

if ($idDepartamento <= 0) {

    responder(
        false,
        "Debe seleccionar un departamento."
    );
}

if ($idProvincia <= 0) {

    responder(
        false,
        "Debe seleccionar una provincia."
    );
}

if ($idDistrito <= 0) {

    responder(
        false,
        "Debe seleccionar un distrito."
    );
}

if ($idRol <= 0) {

    responder(
        false,
        "Debe seleccionar un rol."
    );
}

//======================================================
// VALIDAR ESTADO
//======================================================

if (
    $estado !== "ACTIVO" &&
    $estado !== "INACTIVO"
) {

    responder(
        false,
        "El estado del empleado no es válido."
    );
}

//======================================================
// VALIDAR CELULAR
//======================================================

$celularNumerico = preg_replace(
    "/[^0-9]/",
    "",
    $celular
);

if (
    $celularNumerico === "" ||
    strlen($celularNumerico) < 9
) {

    responder(
        false,
        "El número de celular no es válido."
    );
}

//======================================================
// IMPORTANTE
//
// empleados.celular es INT
//
// Por eso se convierte a entero.
//======================================================

$celularBD = (int)$celularNumerico;

//======================================================
// CONTRASEÑA
//======================================================

$hashContrasena = null;

if ($contrasena !== "") {

    if (strlen($contrasena) < 8) {

        responder(
            false,
            "La contraseña debe tener como mínimo 8 caracteres."
        );
    }

    $hashContrasena = password_hash(
        $contrasena,
        PASSWORD_DEFAULT
    );

    if ($hashContrasena === false) {

        responder(
            false,
            "No se pudo generar la contraseña."
        );
    }
}

//======================================================
// VALIDAR FOTO
//======================================================

if (
    !isset($_FILES["imagenEmpleado"])
) {

    responder(
        false,
        "La foto del empleado es obligatoria."
    );
}

$archivoImagen = $_FILES["imagenEmpleado"];

//======================================================
// ERROR DE UPLOAD
//======================================================

if (
    $archivoImagen["error"] !== UPLOAD_ERR_OK
) {

    switch ($archivoImagen["error"]) {

        case UPLOAD_ERR_NO_FILE:

            responder(
                false,
                "Debe seleccionar una foto para el empleado."
            );

            break;

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:

            responder(
                false,
                "La foto supera el tamaño máximo permitido."
            );

            break;

        case UPLOAD_ERR_PARTIAL:

            responder(
                false,
                "La foto se cargó de forma incompleta."
            );

            break;

        default:

            responder(
                false,
                "Ocurrió un error al cargar la foto del empleado."
            );
    }
}

//======================================================
// VALIDAR ARCHIVO TEMPORAL
//======================================================

if (
    !isset($archivoImagen["tmp_name"]) ||
    !is_uploaded_file(
        $archivoImagen["tmp_name"]
    )
) {

    responder(
        false,
        "No se recibió correctamente la foto."
    );
}

//======================================================
// VALIDAR TAMAÑO
//======================================================

$maximoImagen = 2.7 * 1024 * 1024;

if (
    (int)$archivoImagen["size"] <= 0
) {

    responder(
        false,
        "La imagen seleccionada está vacía."
    );
}

if (
    (int)$archivoImagen["size"] >
    $maximoImagen
) {

    responder(
        false,
        "La foto no puede superar los 2.7 MB."
    );
}

//======================================================
// VALIDAR MIME REAL
//======================================================

$finfo = new finfo(
    FILEINFO_MIME_TYPE
);

$mime = $finfo->file(
    $archivoImagen["tmp_name"]
);

$formatosPermitidos = [
    "image/jpeg",
    "image/png",
    "image/webp"
];

if (
    !in_array(
        $mime,
        $formatosPermitidos,
        true
    )
) {

    responder(
        false,
        "Formato de imagen no permitido. Solo JPG, JPEG, PNG y WEBP."
    );
}

//======================================================
// VALIDAR QUE SEA UNA IMAGEN REAL
//======================================================

$datosImagen = @getimagesize(
    $archivoImagen["tmp_name"]
);

if ($datosImagen === false) {

    responder(
        false,
        "El archivo seleccionado no es una imagen válida."
    );
}

//======================================================
// LEER IMAGEN COMO BINARY
//======================================================

$imagenBinaria = file_get_contents(
    $archivoImagen["tmp_name"]
);

if (
    $imagenBinaria === false ||
    strlen($imagenBinaria) === 0
) {

    responder(
        false,
        "No se pudo procesar la imagen."
    );
}

//======================================================
// VALIDAR DNI DUPLICADO
//======================================================

try {

    $stmt = $conexion->prepare(
        "SELECT id_empleado
         FROM empleados
         WHERE dni = ?
           AND id_user = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "si",
        $dni,
        $idUser
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $stmt->close();

        responder(
            false,
            "El DNI ya está registrado para otro empleado."
        );
    }

    $stmt->close();

    //==================================================
    // VALIDAR EMAIL DUPLICADO
    //==================================================

    $stmt = $conexion->prepare(
        "SELECT id_empleado
         FROM empleados
         WHERE LOWER(email) = LOWER(?)
           AND id_user = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "si",
        $email,
        $idUser
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $stmt->close();

        responder(
            false,
            "El correo electrónico ya está registrado para otro empleado."
        );
    }

    $stmt->close();

    //==================================================
    // VALIDAR ROL
    //==================================================

    $stmt = $conexion->prepare(
        "SELECT id_rol, nombre
         FROM rol
         WHERE id_rol = ?
           AND id_user = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "ii",
        $idRol,
        $idUser
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {

        $stmt->close();

        responder(
            false,
            "El rol seleccionado no existe o no pertenece al usuario actual."
        );
    }

    $datosRol = $resultado->fetch_assoc();

    $stmt->close();

    //==================================================
    // VALIDAR PAÍS
    //==================================================

    $stmt = $conexion->prepare(
        "SELECT id_pais
         FROM pais
         WHERE id_pais = ?
           AND id_user = ?
           AND Eliminado = 0
         LIMIT 1"
    );

    $stmt->bind_param(
        "ii",
        $idPais,
        $idUser
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {

        $stmt->close();

        responder(
            false,
            "El país seleccionado no es válido."
        );
    }

    $stmt->close();

    //==================================================
    // VALIDAR DEPARTAMENTO
    //==================================================

    $stmt = $conexion->prepare(
        "SELECT id_departamento
         FROM departamento
         WHERE id_departamento = ?
           AND id_pais = ?
           AND id_user = ?
           AND Eliminado = 0
         LIMIT 1"
    );

    $stmt->bind_param(
        "iii",
        $idDepartamento,
        $idPais,
        $idUser
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {

        $stmt->close();

        responder(
            false,
            "El departamento seleccionado no pertenece al país seleccionado."
        );
    }

    $stmt->close();

    //==================================================
    // VALIDAR PROVINCIA
    //==================================================

    $stmt = $conexion->prepare(
        "SELECT id_provincia
         FROM provincia
         WHERE id_provincia = ?
           AND id_departamento = ?
           AND id_user = ?
           AND Eliminado = 0
         LIMIT 1"
    );

    $stmt->bind_param(
        "iii",
        $idProvincia,
        $idDepartamento,
        $idUser
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {

        $stmt->close();

        responder(
            false,
            "La provincia seleccionada no pertenece al departamento seleccionado."
        );
    }

    $stmt->close();

    //==================================================
    // VALIDAR DISTRITO
    //==================================================

    $stmt = $conexion->prepare(
        "SELECT id_distrito
         FROM distrito
         WHERE id_distrito = ?
           AND id_provincia = ?
           AND id_user = ?
           AND Eliminado = 0
         LIMIT 1"
    );

    $stmt->bind_param(
        "iii",
        $idDistrito,
        $idProvincia,
        $idUser
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {

        $stmt->close();

        responder(
            false,
            "El distrito seleccionado no pertenece a la provincia seleccionada."
        );
    }

    $stmt->close();

    //==================================================
    // PERMISOS
    //==================================================

    $permisosJSON = $_POST["permisos"] ?? "[]";

    if (
        trim($permisosJSON) === ""
    ) {

        $permisosJSON = "[]";
    }

    $permisos = json_decode(
        $permisosJSON,
        true
    );

    if (
        json_last_error() !== JSON_ERROR_NONE
    ) {

        responder(
            false,
            "Los permisos enviados no tienen un formato JSON válido."
        );
    }

    if (!is_array($permisos)) {

        responder(
            false,
            "La estructura de permisos no es válida."
        );
    }

    //==================================================
    // NORMALIZAR PERMISOS
    //==================================================

    $permisosNormalizados = [];

    $modulosUsados = [];

    foreach ($permisos as $permiso) {

        if (!is_array($permiso)) {

            continue;
        }

        $idModulo = (int)(
            $permiso["id_modulo"] ?? 0
        );

        if ($idModulo <= 0) {

            continue;
        }

        // Evitar duplicados

        if (
            isset(
                $modulosUsados[$idModulo]
            )
        ) {

            continue;
        }

        $modulosUsados[$idModulo] = true;

        $ver = (
            (int)($permiso["ver"] ?? 0)
        ) === 1 ? 1 : 0;

        $crear = (
            (int)($permiso["crear"] ?? 0)
        ) === 1 ? 1 : 0;

        $editar = (
            (int)($permiso["editar"] ?? 0)
        ) === 1 ? 1 : 0;

        $eliminar = (
            (int)($permiso["eliminar"] ?? 0)
        ) === 1 ? 1 : 0;

        //================================================
        // Si no puede VER el módulo,
        // no puede crear/editar/eliminar.
        //================================================

        if ($ver === 0) {

            $crear = 0;
            $editar = 0;
            $eliminar = 0;
        }

        $permisosNormalizados[] = [
            "id_modulo" => $idModulo,
            "ver"       => $ver,
            "crear"     => $crear,
            "editar"    => $editar,
            "eliminar"  => $eliminar
        ];
    }

    //==================================================
    // INICIAR TRANSACCIÓN
    //==================================================

    $conexion->begin_transaction();

    //==================================================
    // INSERTAR EMPLEADO
    //==================================================

    $sqlEmpleado = "
        INSERT INTO empleados
        (
            nombre,
            apellido,
            imagen,
            dni,
            celular,
            direccion,
            id_departamento,
            id_provincia,
            id_distrito,
            id_pais,
            email,
            contrasena,
            id_user,
            estado,
            fecha_registro,
            id_rol,
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
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmtEmpleado = $conexion->prepare(
        $sqlEmpleado
    );

    //==================================================
    // BIND CORRECTO
    //
    // ssbsisiiiississis
    //
    // 1  s nombre
    // 2  s apellido
    // 3  b imagen
    // 4  s dni
    // 5  i celular
    // 6  s direccion
    // 7  i id_departamento
    // 8  i id_provincia
    // 9  i id_distrito
    // 10 i id_pais
    // 11 s email
    // 12 s contrasena
    // 13 i id_user
    // 14 s estado
    // 15 s fecha_registro
    // 16 i id_rol
    // 17 s fecha_actualizado
    //
    //==================================================

    $nullImagen = null;

    $fechaRegistro = date(
        "Y-m-d H:i:s"
    );

    $fechaActualizado = date(
        "Y-m-d"
    );

    $stmtEmpleado->bind_param(
        "ssbsisiiiississis",
        $nombre,
        $apellido,
        $nullImagen,
        $dni,
        $celularBD,
        $direccion,
        $idDepartamento,
        $idProvincia,
        $idDistrito,
        $idPais,
        $email,
        $hashContrasena,
        $idUser,
        $estado,
        $fechaRegistro,
        $idRol,
        $fechaActualizado
    );

    //==================================================
    // ENVIAR BLOB
    //
    // 0 nombre
    // 1 apellido
    // 2 imagen
    //
    //==================================================

    $stmtEmpleado->send_long_data(
        2,
        $imagenBinaria
    );

    //==================================================
    // EJECUTAR
    //==================================================

    if (!$stmtEmpleado->execute()) {

        throw new Exception(
            "Error al registrar empleado: " .
            $stmtEmpleado->error
        );
    }

    //==================================================
    // ID GENERADO
    //==================================================

    $idEmpleado = $conexion->insert_id;

    if ($idEmpleado <= 0) {

        throw new Exception(
            "MySQL no devolvió el ID del empleado registrado."
        );
    }

    $stmtEmpleado->close();

    //==================================================
    // INSERTAR PERMISOS
    //==================================================

    if (
        count($permisosNormalizados) > 0
    ) {

        $sqlPermiso = "
            INSERT INTO permisos_rol
            (
                id_rol,
                id_modulo,
                ver,
                crear,
                editar,
                eliminar,
                id_user
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ";

        $stmtPermiso = $conexion->prepare(
            $sqlPermiso
        );

        foreach (
            $permisosNormalizados
            as $permiso
        ) {

            $idModulo =
                $permiso["id_modulo"];

            $ver =
                $permiso["ver"];

            $crear =
                $permiso["crear"];

            $editar =
                $permiso["editar"];

            $eliminar =
                $permiso["eliminar"];

            $stmtPermiso->bind_param(
                "iiiiiii",
                $idRol,
                $idModulo,
                $ver,
                $crear,
                $editar,
                $eliminar,
                $idUser
            );

            if (!$stmtPermiso->execute()) {

                throw new Exception(
                    "Error al registrar permisos: " .
                    $stmtPermiso->error
                );
            }
        }

        $stmtPermiso->close();
    }

    //==================================================
    // CONFIRMAR
    //==================================================

    $conexion->commit();

    //==================================================
    // RESPUESTA
    //==================================================

    responder(
        true,
        "El empleado fue registrado correctamente.",
        [
            "id_empleado" =>
                $idEmpleado,

            "nombre" =>
                $nombre,

            "apellido" =>
                $apellido,

            "dni" =>
                $dni,

            "email" =>
                $email,

            "id_rol" =>
                $idRol,

            "rol" =>
                $datosRol["nombre"] ?? "",

            "tiene_contrasena" =>
                $hashContrasena !== null,

            "tiene_imagen" =>
                true,

            "cantidad_permisos" =>
                count(
                    $permisosNormalizados
                )
        ]
    );

} catch (Throwable $e) {

    //==================================================
    // ROLLBACK
    //==================================================

    if (
        $conexion->errno ||
        $conexion->thread_id
    ) {

        try {

            $conexion->rollback();

        } catch (Throwable $rollbackError) {

            // No hacer nada.
        }
    }

    //==================================================
    // LOG REAL
    //==================================================

    error_log(
        "==================================================" .
        PHP_EOL .
        "CoDevPro Technology - registrar_empleado.php" .
        PHP_EOL .
        "ERROR: " .
        $e->getMessage() .
        PHP_EOL .
        "ARCHIVO: " .
        $e->getFile() .
        PHP_EOL .
        "LINEA: " .
        $e->getLine() .
        PHP_EOL .
        "=================================================="
    );

    //==================================================
    // DURANTE PRUEBAS:
    //
    // Mostramos el error real.
    //
    // Luego podemos volver a ocultarlo.
    //==================================================

    responder(
        false,
        "ERROR REAL: " . $e->getMessage()
    );
}
?>