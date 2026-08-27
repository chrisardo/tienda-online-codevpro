<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_empleado.php
// Módulo: Editar Empleado
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


//=====================================================
// USUARIO
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

$idEmpleado = isset($_GET['id_empleado'])
    ? (int) $_GET['id_empleado']
    : 0;

if ($idEmpleado <= 0) {

    $respuesta['mensaje'] = 'Empleado no válido.';

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


try {

    //=================================================
    // CONSULTAR EMPLEADO
    //=================================================

    $sql = "
        SELECT
            e.id_empleado,
            e.nombre,
            e.apellido,
            e.dni,
            e.celular,
            e.email,
            e.direccion,
            e.id_pais,
            e.id_departamento,
            e.id_provincia,
            e.id_distrito,
            e.id_rol,
            e.estado

        FROM empleados AS e

        WHERE e.id_empleado = ?
          AND e.id_user = ?

        LIMIT 1
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );


    if (!$stmt) {

        throw new Exception(
            mysqli_error($conexion)
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idEmpleado,
        $idUser
    );


    mysqli_stmt_execute($stmt);


    $resultado = mysqli_stmt_get_result($stmt);


    $empleado = mysqli_fetch_assoc($resultado);


    mysqli_stmt_close($stmt);


    if (!$empleado) {

        $respuesta['mensaje'] =
            'No se encontró el empleado.';

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    //=================================================
    // RESPUESTA
    //=================================================

    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'Empleado obtenido correctamente.';

    $respuesta['data'] = [

        'id_empleado' =>
            (int) $empleado['id_empleado'],

        'nombre' =>
            $empleado['nombre'],

        'apellido' =>
            $empleado['apellido'],

        'dni' =>
            $empleado['dni'],

        'celular' =>
            $empleado['celular'],

        'email' =>
            $empleado['email'],

        'direccion' =>
            $empleado['direccion'],

        'id_pais' =>
            (int) $empleado['id_pais'],

        'id_departamento' =>
            (int) $empleado['id_departamento'],

        'id_provincia' =>
            (int) $empleado['id_provincia'],

        'id_distrito' =>
            (int) $empleado['id_distrito'],

        'id_rol' =>
            (int) $empleado['id_rol'],

        'estado' =>
            $empleado['estado']
    ];

} catch (Throwable $e) {

    error_log(
        'Error obtener empleado: ' .
        $e->getMessage()
    );

    $respuesta['success'] = false;

    $respuesta['mensaje'] =
        'Ocurrió un error al obtener el empleado.';
}


//=====================================================
// JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
