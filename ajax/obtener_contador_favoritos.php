<?php
//Toda esta parte es de ajax/obtener_contador_favoritos.php
session_start();
header('Content-Type: application/json');

include "../controladores/conexion.php";

if (!isset($_SESSION["idCliente"])) {
    echo json_encode([
        "estado" => true,
        "contador" => 0
    ]);
    exit;
}

$idCliente = $_SESSION["idCliente"];

$sql = "SELECT COUNT(*) AS total FROM favoritos WHERE idCliente = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idCliente);
$stmt->execute();

$res = $stmt->get_result();
$row = $res->fetch_assoc();

echo json_encode([
    "estado" => true,
    "contador" => $row["total"] ?? 0
]);
