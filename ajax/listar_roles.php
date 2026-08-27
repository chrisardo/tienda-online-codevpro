<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/listar_roles.php
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');


$respuesta = [
    'success' => false,
    'mensaje' => '',
    'data' => []
];


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


require_once "../controladores/conexion.php";


try {


    //=================================================
    // ROLES
    //=================================================

    $sql = "
        SELECT
            r.id_rol,
            r.nombre,

            COUNT(DISTINCT e.id_empleado) AS cantidad_empleados,

            COUNT(DISTINCT pr.id_permiso) AS cantidad_permisos

        FROM rol r

        LEFT JOIN empleados e
            ON e.id_rol = r.id_rol
           AND e.id_user = r.id_user

        LEFT JOIN permisos_rol pr
            ON pr.id_rol = r.id_rol
           AND pr.id_user = r.id_user

        WHERE r.id_user = ?

        GROUP BY
            r.id_rol,
            r.nombre

        ORDER BY
            r.nombre ASC
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar la consulta de roles: ' .
                mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }


    $resultado =
        mysqli_stmt_get_result($stmt);


    $roles = [];


    while ($fila = mysqli_fetch_assoc($resultado)) {

        $roles[] = [

            'id_rol' =>
            (int) $fila['id_rol'],

            'nombre' =>
            $fila['nombre'],

            'cantidad_empleados' =>
            (int) $fila['cantidad_empleados'],

            'cantidad_permisos' =>
            (int) $fila['cantidad_permisos']
        ];
    }


    mysqli_stmt_close($stmt);


    //=================================================
    // ESTADÍSTICAS
    //=================================================

    $totalRoles = count($roles);

    $rolesUtilizados = 0;

    $rolesSinEmpleados = 0;


    foreach ($roles as $rol) {

        if ($rol['cantidad_empleados'] > 0) {

            $rolesUtilizados++;
        } else {

            $rolesSinEmpleados++;
        }
    }


    //=================================================
    // RESPUESTA
    //=================================================

    $respuesta['success'] = true;

    $respuesta['data'] = [

        'roles' =>
        $roles,

        'total_roles' =>
        $totalRoles,

        'roles_utilizados' =>
        $rolesUtilizados,

        'roles_sin_empleados' =>
        $rolesSinEmpleados
    ];
} catch (Throwable $e) {

    error_log(
        'Error listar roles: ' .
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
