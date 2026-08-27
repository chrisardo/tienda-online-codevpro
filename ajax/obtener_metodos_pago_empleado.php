<?php

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
        id_metodo_pago,
        nombre
    FROM metodo_pago
    WHERE id_user = ?
      AND Eliminado = 0
    ORDER BY nombre ASC
";


$stmt = mysqli_prepare($conexion, $sql);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);


mysqli_stmt_execute($stmt);


$resultado = mysqli_stmt_get_result($stmt);


$metodos = [];


while ($fila = mysqli_fetch_assoc($resultado)) {

    $metodos[] = $fila;
}


mysqli_stmt_close($stmt);


echo json_encode([
    'success' => true,
    'metodos' => $metodos
]);

exit;
