<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/eliminar_rol.php
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


require_once "../controladores/conexion.php";


try {

    mysqli_begin_transaction($conexion);


    //=================================================
    // VERIFICAR ROL
    //=================================================

    $sqlRol = "
        SELECT
            id_rol,
            nombre
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


    $rol =
        mysqli_fetch_assoc($resultado);


    mysqli_stmt_close($stmt);


    if (!$rol) {

        throw new Exception(
            'El rol seleccionado no existe.'
        );
    }


    //=================================================
    // VERIFICAR EMPLEADOS
    //=================================================

    $sqlEmpleados = "
        SELECT
            COUNT(*) AS cantidad
        FROM empleados
        WHERE id_rol = ?
          AND id_user = ?
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlEmpleados
    );


    if (!$stmt) {

        throw new Exception(
            'Error al verificar empleados.'
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


    $fila =
        mysqli_fetch_assoc($resultado);


    mysqli_stmt_close($stmt);


    $cantidadEmpleados =
        (int) $fila['cantidad'];


    if ($cantidadEmpleados > 0) {

        throw new Exception(
            'No se puede eliminar este rol porque tiene ' .
                $cantidadEmpleados .
                ' empleado(s) asignado(s).'
        );
    }


    //=================================================
    // ELIMINAR PERMISOS
    //=================================================

    $sqlPermisos = "
        DELETE FROM permisos_rol
        WHERE id_rol = ?
          AND id_user = ?
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlPermisos
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la eliminación de permisos.'
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
            'Error al eliminar permisos.'
        );
    }


    mysqli_stmt_close($stmt);


    //=================================================
    // ELIMINAR ROL
    //=================================================

    $sqlDelete = "
        DELETE FROM rol
        WHERE id_rol = ?
          AND id_user = ?
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlDelete
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la eliminación del rol.'
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
            'Error al eliminar el rol: ' .
                mysqli_stmt_error($stmt)
        );
    }


    mysqli_stmt_close($stmt);


    mysqli_commit($conexion);


    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'El cargo o rol fue eliminado correctamente.';

    $respuesta['data'] = [

        'id_rol' =>
        $idRol
    ];
} catch (Throwable $e) {

    mysqli_rollback($conexion);


    error_log(
        'Error eliminar rol: ' .
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
