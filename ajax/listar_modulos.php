<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/listar_modulos.php
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

    $respuesta['mensaje'] =
        'Sesión no válida.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


require_once "../controladores/conexion.php";


try {

    $sql = "
        SELECT
            id_modulo,
            nombre,
            codigo,
            icono,
            orden
        FROM modulos
        WHERE id_user = ?
          AND estado = 1
        ORDER BY
            orden ASC,
            id_modulo ASC
    ";


    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );


    if (!$stmt) {

        throw new Exception(
            'Error al preparar módulos: ' .
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


    $modulos = [];


    while ($fila =
        mysqli_fetch_assoc($resultado)
    ) {

        $modulos[] = [

            'id_modulo' =>
            (int) $fila['id_modulo'],

            'nombre' =>
            $fila['nombre'],

            'codigo' =>
            $fila['codigo'],

            'icono' =>
            $fila['icono'],

            'ver' =>
            0,

            'crear' =>
            0,

            'editar' =>
            0,

            'eliminar' =>
            0
        ];
    }


    mysqli_stmt_close($stmt);


    $respuesta['success'] = true;

    $respuesta['data'] =
        $modulos;
} catch (Throwable $e) {

    error_log(
        'Error listar módulos: ' .
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
