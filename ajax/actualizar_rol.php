<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/actualizar_rol.php
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');


$respuesta = [
    'success' => false,
    'mensaje' => '',
    'data' => null
];


$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


$idRol = isset($_POST['id_rol'])
    ? (int) $_POST['id_rol']
    : 0;


$nombre = isset($_POST['nombre'])
    ? trim((string) $_POST['nombre'])
    : '';


$permisos = isset($_POST['permisos']) &&
    is_array($_POST['permisos'])
    ? $_POST['permisos']
    : [];


if ($idUser <= 0) {

    $respuesta['mensaje'] =
        'Sesión no válida.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($idRol <= 0) {

    $respuesta['mensaje'] =
        'Rol no válido.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if ($nombre === '') {

    $respuesta['mensaje'] =
        'Ingrese el nombre del cargo o rol.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


require_once "../controladores/conexion.php";


try {

    mysqli_begin_transaction($conexion);


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
            'Error al verificar el rol.'
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idRol,
        $idUser
    );


    mysqli_stmt_execute($stmt);


    $resultado =
        mysqli_stmt_get_result($stmt);


    $rolExiste =
        mysqli_fetch_assoc($resultado);


    mysqli_stmt_close($stmt);


    if (!$rolExiste) {

        throw new Exception(
            'El rol seleccionado no existe.'
        );
    }


    //=================================================
    // VERIFICAR DUPLICADO
    //=================================================

    $sqlDuplicado = "
        SELECT
            id_rol
        FROM rol
        WHERE id_user = ?
          AND LOWER(TRIM(nombre)) = LOWER(TRIM(?))
          AND id_rol <> ?
        LIMIT 1
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlDuplicado
    );


    if (!$stmt) {

        throw new Exception(
            'Error al verificar el nombre.'
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "isi",
        $idUser,
        $nombre,
        $idRol
    );


    mysqli_stmt_execute($stmt);


    $resultado =
        mysqli_stmt_get_result($stmt);


    $duplicado =
        mysqli_fetch_assoc($resultado);


    mysqli_stmt_close($stmt);


    if ($duplicado) {

        throw new Exception(
            'Ya existe otro cargo o rol con ese nombre.'
        );
    }


    //=================================================
    // ACTUALIZAR NOMBRE
    //=================================================

    $sqlUpdate = "
        UPDATE rol
        SET
            nombre = ?
        WHERE id_rol = ?
          AND id_user = ?
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlUpdate
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la actualización.'
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $nombre,
        $idRol,
        $idUser
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            'Error al actualizar el rol: ' .
                mysqli_stmt_error($stmt)
        );
    }


    mysqli_stmt_close($stmt);


    //=================================================
    // ELIMINAR PERMISOS ANTERIORES
    //=================================================

    $sqlDelete = "
        DELETE FROM permisos_rol
        WHERE id_rol = ?
          AND id_user = ?
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlDelete
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la actualización de permisos.'
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
            'Error al limpiar permisos anteriores.'
        );
    }


    mysqli_stmt_close($stmt);


    //=================================================
    // INSERTAR NUEVOS PERMISOS
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
    ";


    $stmtPermiso = mysqli_prepare(
        $conexion,
        $sqlPermiso
    );


    if (!$stmtPermiso) {

        throw new Exception(
            'Error al preparar los nuevos permisos.'
        );
    }


    $cantidadPermisos = 0;


    foreach ($permisos as $idModulo => $permiso) {

        $idModulo = (int) $idModulo;


        if ($idModulo <= 0) {
            continue;
        }


        $ver =
            isset($permiso['ver']) &&
            (int) $permiso['ver'] === 1
            ? 1
            : 0;


        $crear =
            isset($permiso['crear']) &&
            (int) $permiso['crear'] === 1
            ? 1
            : 0;


        $editar =
            isset($permiso['editar']) &&
            (int) $permiso['editar'] === 1
            ? 1
            : 0;


        $eliminar =
            isset($permiso['eliminar']) &&
            (int) $permiso['eliminar'] === 1
            ? 1
            : 0;


        //=============================================
        // VALIDAR MÓDULO
        //=============================================

        $sqlModulo = "
            SELECT
                id_modulo
            FROM modulos
            WHERE id_modulo = ?
              AND id_user = ?
              AND estado = 1
            LIMIT 1
        ";


        $stmtModulo = mysqli_prepare(
            $conexion,
            $sqlModulo
        );


        if (!$stmtModulo) {

            throw new Exception(
                'Error al verificar módulo.'
            );
        }


        mysqli_stmt_bind_param(
            $stmtModulo,
            "ii",
            $idModulo,
            $idUser
        );


        mysqli_stmt_execute($stmtModulo);


        $resultadoModulo =
            mysqli_stmt_get_result($stmtModulo);


        $moduloExiste =
            mysqli_fetch_assoc($resultadoModulo);


        mysqli_stmt_close($stmtModulo);


        if (!$moduloExiste) {
            continue;
        }


        mysqli_stmt_bind_param(
            $stmtPermiso,
            "iiiiiii",
            $idRol,
            $idModulo,
            $idUser,
            $ver,
            $crear,
            $editar,
            $eliminar
        );


        if (!mysqli_stmt_execute($stmtPermiso)) {

            throw new Exception(
                'Error al guardar permiso del módulo ' .
                    $idModulo .
                    ': ' .
                    mysqli_stmt_error($stmtPermiso)
            );
        }


        $cantidadPermisos++;
    }


    mysqli_stmt_close($stmtPermiso);


    //=================================================
    // COMMIT
    //=================================================

    mysqli_commit($conexion);


    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'Cargo y permisos actualizados correctamente.';

    $respuesta['data'] = [

        'id_rol' =>
        $idRol,

        'nombre' =>
        $nombre,

        'permisos' =>
        $cantidadPermisos
    ];
} catch (Throwable $e) {

    mysqli_rollback($conexion);


    error_log(
        'Error actualizar rol: ' .
            $e->getMessage()
    );


    $respuesta['mensaje'] =
        $e->getMessage();
}


echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
