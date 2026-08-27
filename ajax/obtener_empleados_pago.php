<?php

//=====================================================
// CoDevPro Technology
// AJAX: obtener_empleados_pago.php
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


header('Content-Type: application/json; charset=utf-8');


$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


if ($idUser <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);

    exit;
}


require_once "../controladores/conexion.php";


$sql = "
    SELECT
        id_empleado,
        nombre,
        apellido,
        dni
    FROM empleados
    WHERE id_user = ?
      AND estado = 'ACTIVO'
    ORDER BY nombre ASC, apellido ASC
";


$stmt = mysqli_prepare($conexion, $sql);


if (!$stmt) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo preparar la consulta.'
    ]);

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);


mysqli_stmt_execute($stmt);


$resultado = mysqli_stmt_get_result($stmt);


$empleados = [];


while ($fila = mysqli_fetch_assoc($resultado)) {

    $empleados[] = $fila;
}


mysqli_stmt_close($stmt);


echo json_encode([
    'success' => true,
    'empleados' => $empleados
]);

exit;
