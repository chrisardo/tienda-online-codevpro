<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_marcas_filtro.php
// Módulo: Productos del Proveedor
// Sistema: Inventa
//=====================================================


//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header('Content-Type: application/json; charset=utf-8');


//=====================================================
// RESPUESTA INICIAL
//=====================================================

$respuesta = [
    'success' => false,
    'mensaje' => '',
    'marcas'  => []
];


//=====================================================
// VALIDAR SESIÓN
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


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    $respuesta['mensaje'] =
        'No se pudo establecer conexión con la base de datos.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// CONSULTAR MARCAS
//=====================================================
//
// Solo se muestran las marcas:
//
// - Pertenecientes al usuario actual.
// - No eliminadas.
//
//=====================================================

$sql = "
    SELECT
        id_marca,
        nombre
    FROM marcas
    WHERE id_user = ?
      AND (Eliminado = 0 OR Eliminado IS NULL)
    ORDER BY nombre ASC
";


//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);


if (!$stmt) {

    $respuesta['mensaje'] =
        'No se pudo preparar la consulta de marcas.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// ASIGNAR PARÁMETRO
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);


//=====================================================
// EJECUTAR CONSULTA
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    $respuesta['mensaje'] =
        'No se pudo ejecutar la consulta de marcas.';

    mysqli_stmt_close($stmt);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);


if (!$resultado) {

    $respuesta['mensaje'] =
        'No se pudo obtener el resultado de las marcas.';

    mysqli_stmt_close($stmt);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// RECORRER MARCAS
//=====================================================

while ($marca = mysqli_fetch_assoc($resultado)) {

    $respuesta['marcas'][] = [
        'id_marca' => (int) $marca['id_marca'],
        'nombre'   => $marca['nombre']
    ];
}


//=====================================================
// CERRAR STATEMENT
//=====================================================

mysqli_stmt_close($stmt);


//=====================================================
// RESPUESTA EXITOSA
//=====================================================

$respuesta['success'] = true;


//=====================================================
// ENVIAR RESPUESTA JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
