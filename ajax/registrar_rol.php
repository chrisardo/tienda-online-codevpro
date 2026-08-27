<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/registrar_rol.php
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


if ($idUser <= 0) {

    $respuesta['mensaje'] =
        'Sesión no válida.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


$nombre = isset($_POST['nombre'])
    ? trim((string) $_POST['nombre'])
    : '';


$permisos = isset($_POST['permisos']) &&
    is_array($_POST['permisos'])
    ? $_POST['permisos']
    : [];


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
    // VERIFICAR DUPLICADO
    //=================================================

    $sqlExiste = "
        SELECT
            id_rol
        FROM rol
        WHERE id_user = ?
          AND LOWER(TRIM(nombre)) = LOWER(TRIM(?))
        LIMIT 1
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlExiste
    );


    if (!$stmt) {

        throw new Exception(
            'Error al verificar el nombre del rol.'
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $idUser,
        $nombre
    );


    mysqli_stmt_execute($stmt);


    $resultado =
        mysqli_stmt_get_result($stmt);


    $existe =
        mysqli_fetch_assoc($resultado);


    mysqli_stmt_close($stmt);


    if ($existe) {

        throw new Exception(
            'Ya existe un cargo o rol con ese nombre.'
        );
    }


    //=================================================
    // INSERTAR ROL
    //=================================================

    $sql = "
        INSERT INTO rol
        (
            nombre,
            id_user
        )
        VALUES
        (
            ?,
            ?
        )
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar el registro del rol.'
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $nombre,
        $idUser
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            'Error al registrar el rol: ' .
                mysqli_stmt_error($stmt)
        );
    }


    $idRol =
        mysqli_insert_id($conexion);


    mysqli_stmt_close($stmt);


    //=================================================
    // INSERTAR PERMISOS
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
            'Error al preparar los permisos.'
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
        // VERIFICAR QUE EL MÓDULO PERTENEZCA AL USER
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
                'Error al guardar permisos: ' .
                    mysqli_stmt_error($stmtPermiso)
            );
        }


        $cantidadPermisos++;
    }


    mysqli_stmt_close($stmtPermiso);


    mysqli_commit($conexion);


    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'Cargo y permisos registrados correctamente.';

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
        'Error registrar rol: ' .
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
