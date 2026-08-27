<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_rol.php
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


    //=================================================
    // OBTENER ROL
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
            'Error al preparar el rol.'
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
            'No se encontró el rol seleccionado.'
        );
    }



    //=================================================
    // MÓDULOS Y PERMISOS
    //=================================================

    $sqlPermisos = "
        SELECT

            m.id_modulo,
            m.nombre,
            m.codigo,
            m.icono,

            COALESCE(pr.ver, 0) AS ver,
            COALESCE(pr.crear, 0) AS crear,
            COALESCE(pr.editar, 0) AS editar,
            COALESCE(pr.eliminar, 0) AS eliminar

        FROM modulos m

        LEFT JOIN permisos_rol pr

            ON pr.id_modulo = m.id_modulo

           AND pr.id_rol = ?

           AND pr.id_user = ?

        WHERE m.id_user = ?
          AND m.estado = 1

        ORDER BY
            m.orden ASC,
            m.id_modulo ASC
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sqlPermisos
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar los permisos.'
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "iii",
        $idRol,
        $idUser,
        $idUser
    );


    mysqli_stmt_execute($stmt);


    $resultado =
        mysqli_stmt_get_result($stmt);


    $permisos = [];


    while ($fila =
        mysqli_fetch_assoc($resultado)
    ) {

        $permisos[] = [

            'id_modulo' =>
            (int) $fila['id_modulo'],

            'nombre' =>
            $fila['nombre'],

            'codigo' =>
            $fila['codigo'],

            'icono' =>
            $fila['icono'],

            'ver' =>
            (int) $fila['ver'],

            'crear' =>
            (int) $fila['crear'],

            'editar' =>
            (int) $fila['editar'],

            'eliminar' =>
            (int) $fila['eliminar']
        ];
    }


    mysqli_stmt_close($stmt);


    $respuesta['success'] = true;

    $respuesta['data'] = [

        'id_rol' =>
        (int) $rol['id_rol'],

        'nombre' =>
        $rol['nombre'],

        'permisos' =>
        $permisos
    ];
} catch (Throwable $e) {

    error_log(
        'Error obtener rol: ' .
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
