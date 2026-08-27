<?php
//======================================================
// CoDevPro Technology
// ajax/guardar_testimonio.php
// Recibe el AJAX y llama al controlador
//======================================================

session_start();

/*======================================================
VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Debes iniciar sesión."
    ]);

    exit;
}

/*======================================================
RESPUESTA JSON
======================================================*/

header("Content-Type: application/json; charset=UTF-8");

/*======================================================
LLAMAR CONTROLADOR
======================================================*/

require_once "../controladores/procesar_guardar_testimonio.php";
