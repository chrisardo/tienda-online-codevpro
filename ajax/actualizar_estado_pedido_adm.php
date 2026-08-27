<?php
//=========================================================
// CoDevPro Technology
// ajax/actualizar_estado_pedido.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser  = (int)($_SESSION["idUser"] ?? 0);
$idVenta = (int)($_POST["idVenta"] ?? 0);

$estado = trim($_POST["estado"] ?? "");

if (
    $idUser <= 0 ||
    $idVenta <= 0 ||
    empty($estado)
) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Datos inválidos"
    ]);

    exit;
}

$estadosValidos = [

    "PENDIENTE",
    "CONFIRMADO",
    "PREPARANDO",
    "ENVIADO",
    "ENTREGADO",
    "CANCELADO"

];

if (!in_array($estado, $estadosValidos)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Estado inválido"
    ]);

    exit;
}

/*=========================================
=            FECHA SEGUN ESTADO
=========================================*/

$campoFecha = null;

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

    case "CANCELADO":
        $campoFecha = "fecha_cancelado";
        break;
}

/*=========================================
=            ACTUALIZAR
=========================================*/

if ($campoFecha) {

    $sql = "

        UPDATE ticket_ventas

        SET

            estado_envio = ?,

            $campoFecha = NOW()

        WHERE id_ticket_ventas = ?

        AND id_user = ?

    ";
} else {

    $sql = "

        UPDATE ticket_ventas

        SET estado_envio = ?

        WHERE id_ticket_ventas = ?

        AND id_user = ?

    ";
}

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "sii",
    $estado,
    $idVenta,
    $idUser
);

$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

if (!$ok) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo actualizar"
    ]);

    exit;
}

echo json_encode([
    "estado" => true,
    "mensaje" => "Estado actualizado"
]);
