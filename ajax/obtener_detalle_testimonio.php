<?php
//Esta parte pertenece a ajax/obtener_detalle_testimonio.php
session_start();

header('Content-Type: application/json');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

$id = (int)($_POST["id"] ?? 0);

$sql = "

SELECT

t.*,
c.nombre cliente,
p.nombre producto

FROM testimonios t

INNER JOIN clientes c
ON c.idCliente=t.idCliente

INNER JOIN producto p
ON p.idProducto=t.idProducto

WHERE t.id_testimonio=?
AND t.id_user=?

LIMIT 1

";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id,
    $idUser
);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

$row = mysqli_fetch_assoc($res);

echo json_encode([
    "ok" => true,
    "data" => $row
]);
