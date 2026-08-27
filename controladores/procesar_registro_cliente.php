<?php
session_start();
require_once "conexion.php";

header('Content-Type: application/json; charset=utf-8');

/*
====================================================
RECIBIR DATOS
====================================================
*/

$nombre      = trim($_POST['nombre'] ?? '');
$dni         = trim($_POST['dni'] ?? '');
$email       = trim($_POST['email'] ?? '');
$celular     = trim($_POST['celular'] ?? '');
$password    = trim($_POST['password'] ?? '');

/*
====================================================
VALIDACIONES BÁSICAS
====================================================
*/

if ($nombre == '' || $email == '' || $password == '') {
    echo json_encode([
        "estado" => false,
        "mensaje" => "Complete los campos obligatorios"
    ]);
    exit;
}

/*
====================================================
VALIDAR SI EMAIL YA EXISTE
====================================================
*/

$sql = "SELECT idCliente FROM clientes WHERE email = ? AND Eliminado = 0 LIMIT 1";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_fetch_assoc($res)) {
    echo json_encode([
        "estado" => false,
        "mensaje" => "Este correo ya está registrado"
    ]);
    exit;
}

/*
====================================================
ENCRIPTAR PASSWORD
====================================================
*/

$hashPassword = password_hash($password, PASSWORD_BCRYPT);

/*
====================================================
INSERTAR CLIENTE
====================================================
*/

$sqlInsert = "INSERT INTO clientes
(nombre, dni_o_ruc, email, celular, password, fecha_registro, Eliminado)
VALUES (?, ?, ?, ?, ?, NOW(), 0)";

$stmt = mysqli_prepare($conexion, $sqlInsert);
mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $nombre,
    $dni,
    $email,
    $celular,
    $hashPassword
);

if (mysqli_stmt_execute($stmt)) {

    $idCliente = mysqli_insert_id($conexion);

    // Opcional: iniciar sesión automáticamente
    $_SESSION["idCliente"] = $idCliente;
    $_SESSION["nombreCliente"] = $nombre;

    echo json_encode([
        "estado" => true,
        "mensaje" => "Registro exitoso"
    ]);
} else {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al registrar cliente"
    ]);
}
