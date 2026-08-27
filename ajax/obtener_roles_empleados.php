<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_roles_empleados.php
// Módulo: Lista de Empleados - Roles
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
    'data' => []
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
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


try {

    //=================================================
    // OBTENER TODOS LOS ROLES DEL USUARIO
    //=================================================
    //
    // Para el modal de editar empleado necesitamos
    // todos los roles pertenecientes al administrador
    // actual.
    //
    // No debemos depender de que el rol tenga
    // empleados asignados actualmente.
    //
    //=================================================

    $sql = "
        SELECT
            r.id_rol,
            r.nombre

        FROM rol AS r

        WHERE r.id_user = ?

        ORDER BY r.nombre ASC
    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            $conexion->error
        );
    }


    $stmt->bind_param(
        "i",
        $idUser
    );


    $stmt->execute();


    $resultado = $stmt->get_result();


    while ($fila = $resultado->fetch_assoc()) {

        $respuesta['data'][] = [
            'id_rol' => (int) $fila['id_rol'],
            'nombre' => $fila['nombre']
        ];
    }


    $stmt->close();


    //=================================================
    // RESPUESTA
    //=================================================

    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'Roles cargados correctamente.';
} catch (Throwable $e) {

    $respuesta['success'] = false;

    $respuesta['mensaje'] =
        'Error al obtener los roles.';

    error_log(
        'Error roles empleados: ' .
            $e->getMessage()
    );
}


//=====================================================
// JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
