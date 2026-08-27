<?php
//=====================================================
// CoDevPro Technology
// ajax/obtener_metodo_pago.php
//=====================================================

session_start();

header(
    "Content-Type: application/json; charset=utf-8"
);

require_once "../controladores/conexion.php";


/*=====================================================
=            VALIDAR SESIÓN
=====================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La sesión ha expirado."
    ]);

    exit;
}


$idUser =
    (int) $_SESSION["idUser"];


/*=====================================================
=            RECIBIR ID
=====================================================*/

$idMetodo =
    isset($_POST["id_metodo_pago"])
    ? (int) $_POST["id_metodo_pago"]
    : 0;


if ($idMetodo <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "ID de método de pago inválido."
    ]);

    exit;
}


/*=====================================================
=            CONSULTAR MÉTODO
=====================================================*/

$sql = "

SELECT
    id_metodo_pago,
    nombre,
    Eliminado

FROM metodo_pago

WHERE
    id_metodo_pago = ?
    AND id_user = ?

LIMIT 1

";


$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );


if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al preparar la consulta."
    ]);

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idMetodo,
    $idUser
);


mysqli_stmt_execute($stmt);


$resultado =
    mysqli_stmt_get_result($stmt);


$metodo =
    mysqli_fetch_assoc($resultado);


mysqli_stmt_close($stmt);


if (!$metodo) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El método de pago no existe o no pertenece a este usuario."
    ]);

    exit;
}


/*=====================================================
=            RESPUESTA
=====================================================*/

echo json_encode([

    "estado" => true,

    "metodo" => [

        "id_metodo_pago" =>
        (int) $metodo["id_metodo_pago"],

        "nombre" =>
        $metodo["nombre"],

        "Eliminado" =>
        (int) $metodo["Eliminado"]

    ]

], JSON_UNESCAPED_UNICODE);


mysqli_close($conexion);
