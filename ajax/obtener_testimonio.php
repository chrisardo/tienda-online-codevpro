<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_testimonio.php
// Recibe la petición AJAX y obtiene un testimonio
//======================================================

session_start();

header("Content-Type: application/json; charset=UTF-8");

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
LLAMAR CONTROLADOR
======================================================*/

require_once "../controladores/procesar_obtener_testimonio.php";
