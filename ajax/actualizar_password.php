<?php

//======================================================
// CoDevPro Technology
// ajax/actualizar_password.php
//======================================================

session_start();

header("Content-Type: application/json");

require_once "../controladores/conexion.php";


/*======================================================
=            VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([

        "estado"  => "error",
        "mensaje" => "Debe iniciar sesión."

    ]);

    exit;
}


/*======================================================
=            VALIDAR DATOS
======================================================*/

if (

    empty($_POST["password_actual"]) ||

    empty($_POST["password_nueva"])

) {

    echo json_encode([

        "estado"  => "error",
        "mensaje" => "Complete todos los campos."

    ]);

    exit;
}


/*======================================================
=            VARIABLES
======================================================*/

$idCliente = intval($_SESSION["idCliente"]);

$passwordActual = trim($_POST["password_actual"]);

$passwordNueva = trim($_POST["password_nueva"]);

/*======================================================
=            VALIDAR FORTALEZA DE LA CONTRASEÑA
======================================================*/

/*
- Mínimo 8 caracteres.
- Al menos una letra mayúscula.
- Al menos una letra minúscula.
- Al menos un número.
- Al menos un carácter especial.
*/

if (strlen($passwordNueva) < 8) {

    echo json_encode([

        "estado"  => "error",

        "mensaje" => "La contraseña debe tener como mínimo 8 caracteres."

    ]);

    exit;
}


if (!preg_match('/[A-Z]/', $passwordNueva)) {

    echo json_encode([

        "estado"  => "error",

        "mensaje" => "La contraseña debe contener al menos una letra mayúscula."

    ]);

    exit;
}


if (!preg_match('/[a-z]/', $passwordNueva)) {

    echo json_encode([

        "estado"  => "error",

        "mensaje" => "La contraseña debe contener al menos una letra minúscula."

    ]);

    exit;
}


if (!preg_match('/[0-9]/', $passwordNueva)) {

    echo json_encode([

        "estado"  => "error",

        "mensaje" => "La contraseña debe contener al menos un número."

    ]);

    exit;
}


/*
Caracteres especiales permitidos:

! @ # $ % ^ & * ( ) - _ + = ? . , ; : / \

También acepta otros caracteres especiales.
*/

if (!preg_match('/[^a-zA-Z0-9]/', $passwordNueva)) {

    echo json_encode([

        "estado"  => "error",

        "mensaje" => "La contraseña debe contener al menos un carácter especial."

    ]);

    exit;
}
/*======================================================
=            OBTENER CONTRASEÑA ACTUAL
======================================================*/

$sql = "

SELECT

contrasena

FROM clientes

WHERE idCliente = ?

LIMIT 1

";


$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "i", $idCliente);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


/*======================================================
=            CLIENTE NO ENCONTRADO
======================================================*/

if (!$resultado || mysqli_num_rows($resultado) == 0) {

    echo json_encode([

        "estado"  => "error",
        "mensaje" => "Cliente no encontrado."

    ]);

    exit;
}


$cliente = mysqli_fetch_assoc($resultado);

$passwordBD = $cliente["contrasena"];


/*======================================================
=            VALIDAR CONTRASEÑA ACTUAL
======================================================*/

if (!password_verify($passwordActual, $passwordBD)) {

    echo json_encode([

        "estado"  => "error",
        "mensaje" => "La contraseña actual es incorrecta."

    ]);

    exit;
}


/*======================================================
=            EVITAR MISMA CONTRASEÑA
======================================================*/

if ($passwordActual === $passwordNueva) {

    echo json_encode([

        "estado"  => "error",
        "mensaje" => "La nueva contraseña no puede ser igual a la actual."

    ]);

    exit;
}


/*======================================================
=            GENERAR NUEVO HASH
======================================================*/

$nuevaPassword = password_hash(

    $passwordNueva,

    PASSWORD_DEFAULT

);


/*======================================================
=            ACTUALIZAR CONTRASEÑA
======================================================*/

$sqlActualizar = "

UPDATE clientes

SET

contrasena = ?

WHERE idCliente = ?

";


$stmtActualizar = mysqli_prepare(

    $conexion,

    $sqlActualizar

);


mysqli_stmt_bind_param(

    $stmtActualizar,

    "si",

    $nuevaPassword,

    $idCliente

);


/*======================================================
=            ACTUALIZAR PASSWORD
======================================================*/

if (mysqli_stmt_execute($stmtActualizar)) {

    echo json_encode([

        "estado"  => "ok",

        "mensaje" => "La contraseña fue actualizada correctamente."

    ]);
} else {

    echo json_encode([

        "estado"  => "error",

        "mensaje" => "No fue posible actualizar la contraseña."

    ]);
}


/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_stmt_close($stmt);

mysqli_stmt_close($stmtActualizar);

mysqli_close($conexion);

exit;
