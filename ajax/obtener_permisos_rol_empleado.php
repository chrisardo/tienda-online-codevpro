<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_permisos_rol_empleado.php
// Módulo: Editar Empleado - Permisos
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
// ID ROL
//=====================================================

$idRol = isset($_GET['id_rol'])
    ? (int) $_GET['id_rol']
    : 0;

if ($idRol <= 0) {

    $respuesta['mensaje'] = 'Rol no válido.';

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
    // CONSULTAR PERMISOS DEL ROL
    //=================================================

    $sql = "
        SELECT
            m.id_modulo,
            m.nombre,
            m.codigo,
            m.icono,
            m.orden,

            COALESCE(pr.ver, 0) AS ver,
            COALESCE(pr.crear, 0) AS crear,
            COALESCE(pr.editar, 0) AS editar,
            COALESCE(pr.eliminar, 0) AS eliminar

        FROM modulos AS m

        LEFT JOIN permisos_rol AS pr
            ON pr.id_modulo = m.id_modulo
            AND pr.id_rol = ?
            AND pr.id_user = ?

        WHERE m.id_user = ?
          AND m.estado = 1

        ORDER BY m.orden ASC, m.nombre ASC
    ";


    $stmt = $conexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            $conexion->error
        );
    }


    $stmt->bind_param(
        "iii",
        $idRol,
        $idUser,
        $idUser
    );


    $stmt->execute();


    $resultado = $stmt->get_result();


    while ($fila = $resultado->fetch_assoc()) {

        $respuesta['data'][] = [

            'id_modulo' => (int) $fila['id_modulo'],

            'nombre' => $fila['nombre'],

            'codigo' => $fila['codigo'],

            'icono' => $fila['icono'],

            'orden' => (int) $fila['orden'],

            'ver' => (int) $fila['ver'],

            'crear' => (int) $fila['crear'],

            'editar' => (int) $fila['editar'],

            'eliminar' => (int) $fila['eliminar']
        ];
    }


    $stmt->close();


    //=================================================
    // RESPUESTA
    //=================================================

    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'Permisos cargados correctamente.';
} catch (Throwable $e) {

    error_log(
        'Error permisos rol empleado: ' .
            $e->getMessage()
    );

    $respuesta['success'] = false;

    $respuesta['mensaje'] =
        'Error al obtener los permisos del rol.';
}


//=====================================================
// JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
