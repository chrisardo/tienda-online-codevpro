<?php
//Toda esta parte pertenece a ajax/cambiar_estado_testimonio.php
session_start();

header('Content-Type: application/json');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

$id = (int)($_POST["id"] ?? 0);

$estado = trim($_POST["estado"] ?? "");

if (
    !in_array(
        $estado,
        ["APROBADO", "RECHAZADO"]
    )
) {

    echo json_encode([
        "ok" => false
    ]);

    exit;
}

$sql = "

UPDATE testimonios

SET estado=?

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
    $estado,
    $id,
    $idUser
);

$ok = mysqli_stmt_execute($stmt);

echo json_encode([
    "ok" => $ok
]);

