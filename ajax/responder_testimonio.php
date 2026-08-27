<?php
//Toda esta parte pertenece a ajax/responder_testimonio.php
session_start();

header('Content-Type: application/json');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Sesión inválida"
    ]);

    exit;
}

$id = (int)($_POST["id"] ?? 0);

$respuesta = trim($_POST["respuesta"] ?? "");

if ($id <= 0) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Testimonio inválido"
    ]);

    exit;
}

if ($respuesta == "") {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Debe escribir una respuesta"
    ]);

    exit;
}

$sql = "

UPDATE testimonios

SET

respuesta=?,
fecha_respuesta=NOW(),
estado='APROBADO'

WHERE id_testimonio=?
AND id_user=?

";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "sii",
    $respuesta,
    $id,
    $idUser
);

$ok = mysqli_stmt_execute($stmt);

echo json_encode([
    "ok" => $ok
]);