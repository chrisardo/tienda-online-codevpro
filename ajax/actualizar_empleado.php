<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_empleado.php
// Módulo: Editar Empleado
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
// DATOS RECIBIDOS
//=====================================================

$idEmpleado = isset($_POST['id_empleado'])
    ? (int) $_POST['id_empleado']
    : 0;

$dni = isset($_POST['dni'])
    ? trim((string) $_POST['dni'])
    : '';

$celular = isset($_POST['celular'])
    ? trim((string) $_POST['celular'])
    : '';

$nombre = isset($_POST['nombre'])
    ? trim((string) $_POST['nombre'])
    : '';

$apellido = isset($_POST['apellido'])
    ? trim((string) $_POST['apellido'])
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

$idProvincia = isset($_POST['id_provincia'])
    ? (int) $_POST['id_provincia']
    : 0;

$idDistrito = isset($_POST['id_distrito'])
    ? (int) $_POST['id_distrito']
    : 0;

$idRol = isset($_POST['id_rol'])
    ? (int) $_POST['id_rol']
    : 0;

$estado = isset($_POST['estado'])
    ? strtoupper(trim((string) $_POST['estado']))
    : '';

$contrasena = isset($_POST['contrasena'])
    ? trim((string) $_POST['contrasena'])
    : '';


//=====================================================
// PERMISOS RECIBIDOS
//=====================================================
//
// Formato esperado:
//
// permisos[id_modulo][ver]
// permisos[id_modulo][crear]
// permisos[id_modulo][editar]
// permisos[id_modulo][eliminar]
//
// Ejemplo:
//
// permisos[1][ver] = 1
// permisos[1][crear] = 0
// permisos[1][editar] = 1
// permisos[1][eliminar] = 0
//
//=====================================================

$permisos = isset($_POST['permisos']) &&
    is_array($_POST['permisos'])
    ? $_POST['permisos']
    : [];


//=====================================================
// NORMALIZAR ESTADO
//=====================================================

if ($estado === 'ACTIVO') {

    $estado = 'ACTIVO';
} elseif ($estado === 'INACTIVO') {

    $estado = 'INACTIVO';
} else {

    $respuesta['mensaje'] =
        'El estado seleccionado no es válido.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// VALIDACIONES
//=====================================================

if ($idEmpleado <= 0) {

    $respuesta['mensaje'] =
        'Empleado no válido.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($dni === '') {

    $respuesta['mensaje'] =
        'Ingrese el DNI del empleado.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($celular === '') {

    $respuesta['mensaje'] =
        'Ingrese el número de celular.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($nombre === '') {

    $respuesta['mensaje'] =
        'Ingrese los nombres del empleado.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($apellido === '') {

    $respuesta['mensaje'] =
        'Ingrese los apellidos del empleado.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $respuesta['mensaje'] =
        'Ingrese un correo electrónico válido.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($direccion === '') {

    $respuesta['mensaje'] =
        'Ingrese la dirección del empleado.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($idPais <= 0) {

    $respuesta['mensaje'] =
        'Seleccione un país.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($idDepartamento <= 0) {

    $respuesta['mensaje'] =
        'Seleccione un departamento.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($idProvincia <= 0) {

    $respuesta['mensaje'] =
        'Seleccione una provincia.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($idDistrito <= 0) {

    $respuesta['mensaje'] =
        'Seleccione un distrito.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($idRol <= 0) {

    $respuesta['mensaje'] =
        'Seleccione un cargo o rol.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


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
            id_empleado
        FROM empleados
        WHERE id_empleado = ?
          AND id_user = ?
        LIMIT 1
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlVerificar
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la verificación del empleado: ' .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idEmpleado,
        $idUser
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }


    $resultado = mysqli_stmt_get_result($stmt);

    $empleadoExiste =
        mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);


    if (!$empleadoExiste) {

        throw new Exception(
            'No se encontró el empleado seleccionado.'
        );
    }


    //=================================================
    // VERIFICAR ROL
    //=================================================

    $sqlRol = "
        SELECT
            id_rol
        FROM rol
        WHERE id_rol = ?
          AND id_user = ?
        LIMIT 1
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlRol
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la verificación del rol: ' .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idRol,
        $idUser
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }


    $resultado = mysqli_stmt_get_result($stmt);

    $rolExiste =
        mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);


    if (!$rolExiste) {

        throw new Exception(
            'El cargo o rol seleccionado no es válido.'
        );
    }


    //=================================================
    // VERIFICAR DNI DUPLICADO
    //=================================================

    $sqlDni = "
        SELECT
            id_empleado
        FROM empleados
        WHERE dni = ?
          AND id_user = ?
          AND id_empleado <> ?
        LIMIT 1
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlDni
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la validación del DNI: ' .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $dni,
        $idUser,
        $idEmpleado
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }


    $resultado = mysqli_stmt_get_result($stmt);

    $dniDuplicado =
        mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);


    if ($dniDuplicado) {

        throw new Exception(
            'El DNI ya está registrado en otro empleado.'
        );
    }


    //=================================================
    // VERIFICAR EMAIL DUPLICADO
    //=================================================

    $sqlEmail = "
        SELECT
            id_empleado
        FROM empleados
        WHERE email = ?
          AND id_user = ?
          AND id_empleado <> ?
        LIMIT 1
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlEmail
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la validación del correo: ' .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $email,
        $idUser,
        $idEmpleado
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }


    $resultado = mysqli_stmt_get_result($stmt);

    $emailDuplicado =
        mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);


    if ($emailDuplicado) {

        throw new Exception(
            'El correo electrónico ya está registrado en otro empleado.'
        );
    }


    //=================================================
    // ACTUALIZAR EMPLEADO
    //=================================================

    if ($contrasena === '') {

        //=============================================
        // SIN CAMBIAR CONTRASEÑA
        //=============================================

        $sql = "
            UPDATE empleados
            SET
                nombre = ?,
                apellido = ?,
                dni = ?,
                celular = ?,
                email = ?,
                direccion = ?,
                id_pais = ?,
                id_departamento = ?,
                id_provincia = ?,
                id_distrito = ?,
                id_rol = ?,
                estado = ?,
                fecha_actualizado = CURDATE()

            WHERE id_empleado = ?
              AND id_user = ?
        ";


        $stmt = mysqli_prepare(
            $conexion,
            $sql
        );


        if (!$stmt) {

            throw new Exception(
                'Error al preparar la actualización: ' .
                    mysqli_error($conexion)
            );
        }


        mysqli_stmt_bind_param(
            $stmt,
            "ssssssiiiiisii",
            $nombre,
            $apellido,
            $dni,
            $celular,
            $email,
            $direccion,
            $idPais,
            $idDepartamento,
            $idProvincia,
            $idDistrito,
            $idRol,
            $estado,
            $idEmpleado,
            $idUser
        );
    } else {

        //=============================================
        // GENERAR NUEVA CONTRASEÑA
        //=============================================

        $contrasenaHash = password_hash(
            $contrasena,
            PASSWORD_DEFAULT
        );


        if ($contrasenaHash === false) {

            throw new Exception(
                'No se pudo generar la nueva contraseña.'
            );
        }


        //=============================================
        // ACTUALIZAR CON CONTRASEÑA
        //=============================================

        $sql = "
            UPDATE empleados
            SET
                nombre = ?,
                apellido = ?,
                dni = ?,
                celular = ?,
                email = ?,
                direccion = ?,
                id_pais = ?,
                id_departamento = ?,
                id_provincia = ?,
                id_distrito = ?,
                id_rol = ?,
                estado = ?,
                contrasena = ?,
                fecha_actualizado = CURDATE()

            WHERE id_empleado = ?
              AND id_user = ?
        ";


        $stmt = mysqli_prepare(
            $conexion,
            $sql
        );


        if (!$stmt) {

            throw new Exception(
                'Error al preparar la actualización con contraseña: ' .
                    mysqli_error($conexion)
            );
        }


        mysqli_stmt_bind_param(
            $stmt,
            "ssssssiiiiissii",
            $nombre,
            $apellido,
            $dni,
            $celular,
            $email,
            $direccion,
            $idPais,
            $idDepartamento,
            $idProvincia,
            $idDistrito,
            $idRol,
            $estado,
            $contrasenaHash,
            $idEmpleado,
            $idUser
        );
    }


    //=================================================
    // EJECUTAR UPDATE EMPLEADO
    //=================================================

    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            'Error al actualizar el empleado: ' .
                mysqli_stmt_error($stmt)
        );
    }


    $filasEmpleado =
        mysqli_stmt_affected_rows($stmt);


    mysqli_stmt_close($stmt);


    //=================================================
    // ACTUALIZAR PERMISOS DEL ROL
    //=================================================
    //
    // IMPORTANTE:
    //
    // Los permisos pertenecen al ROL.
    //
    // No se guardan como permisos individuales
    // del empleado.
    //
    //=================================================

    //=================================================
    // OBTENER TODOS LOS MÓDULOS DEL USUARIO
    //=================================================

    $sqlModulos = "
        SELECT
            id_modulo
        FROM modulos
        WHERE id_user = ?
          AND estado = 1
        ORDER BY id_modulo ASC
    ";


    $stmtModulos = mysqli_prepare(
        $conexion,
        $sqlModulos
    );


    if (!$stmtModulos) {

        throw new Exception(
            'Error al preparar la consulta de módulos: ' .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmtModulos,
        "i",
        $idUser
    );


    if (!mysqli_stmt_execute($stmtModulos)) {

        throw new Exception(
            'Error al obtener los módulos: ' .
                mysqli_stmt_error($stmtModulos)
        );
    }


    $resultadoModulos =
        mysqli_stmt_get_result($stmtModulos);


    $modulos = [];


    while ($filaModulo =
        mysqli_fetch_assoc($resultadoModulos)
    ) {

        $modulos[] =
            (int) $filaModulo['id_modulo'];
    }


    mysqli_stmt_close($stmtModulos);


    //=================================================
    // PREPARAR UPSERT DE PERMISOS
    //=================================================
    //
    // Si el permiso ya existe:
    //
    // UPDATE
    //
    // Si no existe:
    //
    // INSERT
    //
    //=================================================

    $sqlPermiso = "
        INSERT INTO permisos_rol
        (
            id_rol,
            id_modulo,
            id_user,
            ver,
            crear,
            editar,
            eliminar
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

        ON DUPLICATE KEY UPDATE

            ver = VALUES(ver),
            crear = VALUES(crear),
            editar = VALUES(editar),
            eliminar = VALUES(eliminar)
    ";


    $stmtPermiso = mysqli_prepare(
        $conexion,
        $sqlPermiso
    );


    if (!$stmtPermiso) {

        throw new Exception(
            'Error al preparar la actualización de permisos: ' .
                mysqli_error($conexion)
        );
    }


    //=================================================
    // RECORRER MÓDULOS
    //=================================================

    $cantidadPermisosActualizados = 0;


    foreach ($modulos as $idModulo) {

        //=============================================
        // OBTENER PERMISO RECIBIDO
        //=============================================

        $permisoModulo =
            isset($permisos[$idModulo]) &&
            is_array($permisos[$idModulo])
            ? $permisos[$idModulo]
            : [];


        //=============================================
        // CHECKBOXES
        //
        // Si no llegó el valor:
        //
        // 0
        //
        //=============================================

        $puedeVer =
            isset($permisoModulo['ver']) &&
            (int) $permisoModulo['ver'] === 1
            ? 1
            : 0;


        $puedeCrear =
            isset($permisoModulo['crear']) &&
            (int) $permisoModulo['crear'] === 1
            ? 1
            : 0;


        $puedeEditar =
            isset($permisoModulo['editar']) &&
            (int) $permisoModulo['editar'] === 1
            ? 1
            : 0;


        $puedeEliminar =
            isset($permisoModulo['eliminar']) &&
            (int) $permisoModulo['eliminar'] === 1
            ? 1
            : 0;


        //=============================================
        // BIND
        //=============================================

        mysqli_stmt_bind_param(
            $stmtPermiso,
            "iiiiiii",
            $idRol,
            $idModulo,
            $idUser,
            $puedeVer,
            $puedeCrear,
            $puedeEditar,
            $puedeEliminar
        );


        //=============================================
        // EJECUTAR
        //=============================================

        if (!mysqli_stmt_execute($stmtPermiso)) {

            throw new Exception(
                'Error al actualizar permisos del módulo ' .
                    $idModulo .
                    ': ' .
                    mysqli_stmt_error($stmtPermiso)
            );
        }


        $cantidadPermisosActualizados++;
    }


    mysqli_stmt_close($stmtPermiso);


    //=================================================
    // CONFIRMAR TRANSACCIÓN
    //=================================================

    mysqli_commit($conexion);


    //=================================================
    // RESPUESTA EXITOSA
    //=================================================

    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'Empleado y permisos actualizados correctamente.';

    $respuesta['data'] = [

        'id_empleado' =>
        $idEmpleado,

        'id_rol' =>
        $idRol,

        'estado' =>
        $estado,

        'filas_empleado' =>
        $filasEmpleado,

        'permisos_actualizados' =>
        $cantidadPermisosActualizados
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


    error_log(
        'Error actualizar empleado: ' .
            $e->getMessage()
    );


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
