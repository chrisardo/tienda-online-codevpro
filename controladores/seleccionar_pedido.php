<?php
//======================================================
// CoDevPro Technology
// controladores/seleccionar_pedido.php
// Guarda pedido seleccionado en sesión
//======================================================


session_start();


/*=====================================
VALIDAR CLIENTE
=====================================*/

if (!isset($_SESSION["idCliente"])) {

    header("Location: ../login.php");
    exit;
}



/*=====================================
VALIDAR ID PEDIDO
=====================================*/


$idTicket = intval($_GET["id"] ?? 0);



if ($idTicket <= 0) {

    header("Location: ../mis_pedidos.php");
    exit;
}



/*=====================================
GUARDAR PEDIDO EN SESIÓN
=====================================*/


$_SESSION["pedido_detalle"] = $idTicket;



/*=====================================
REDIRECCIÓN LIMPIA
=====================================*/


header(
    "Location: ../ver_detalle_pedido_cliente.php"
);

exit;
