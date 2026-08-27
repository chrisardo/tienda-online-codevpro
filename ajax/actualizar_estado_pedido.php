<?php
session_start();

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["usId"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Acceso denegado."
    ]);

    exit;
}

require_once "../controladores/conexion.php";

$idUser = $_SESSION["usId"];

$idPedido = intval($_POST["idPedido"] ?? 0);

$estado = trim($_POST["estado"] ?? "");

$observacion = trim($_POST["observacion"] ?? "");

if ($idPedido <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Pedido inválido."
    ]);

    exit;
}
$estadosPermitidos = [

    "PENDIENTE",

    "CONFIRMADO",

    "PREPARANDO",

    "ENVIADO",

    "ENTREGADO",

    "CANCELADO"

];

if (!in_array($estado, $estadosPermitidos)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Estado no permitido."
    ]);

    exit;
}
$sql = "SELECT id_ticket_ventas

        FROM ticket_ventas

        WHERE

        id_ticket_ventas=?

        AND

        id_user=?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idPedido,
    $idUser
);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) == 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Pedido no encontrado."
    ]);

    exit;
}

$campoFecha = "";

switch ($estado) {

    case "CONFIRMADO":
        $campoFecha = "fecha_confirmado";
        break;

    case "PREPARANDO":
        $campoFecha = "fecha_preparando";
        break;

    case "ENVIADO":
        $campoFecha = "fecha_enviado";
        break;

    case "ENTREGADO":
        $campoFecha = "fecha_entregado";
        break;
}
$campoFecha = "";

switch ($estado) {

    case "CONFIRMADO":
        $campoFecha = "fecha_confirmado";
        break;

    case "PREPARANDO":
        $campoFecha = "fecha_preparando";
        break;

    case "ENVIADO":
        $campoFecha = "fecha_enviado";
        break;

    case "ENTREGADO":
        $campoFecha = "fecha_entregado";
        break;
}
echo json_encode([

    "estado" => true,

    "mensaje" => "Estado actualizado correctamente."

]);