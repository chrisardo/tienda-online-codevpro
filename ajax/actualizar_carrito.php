<?php
//Todo esto pertenece a ajax/actualizar_carrito.php
session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";
require_once "../controladores/token_carrito.php";

$idCliente = $_SESSION["idCliente"] ?? 0;
$token = obtenerTokenCarrito();

$idCarrito = isset($_POST["idCarrito"]) ? intval($_POST["idCarrito"]) : 0;
$accion    = $_POST["accion"] ?? "";

if($idCarrito<=0){

    echo json_encode([
        "estado"=>false,
        "mensaje"=>"Registro inválido."
    ]);

    exit;

}

/*
|--------------------------------------------------------------------------
| OBTENER PRODUCTO DEL CARRITO
|--------------------------------------------------------------------------
*/

if($idCliente>0){

$sql="SELECT

c.idCarrito,
c.cantidad,

p.stock

FROM carrito_online c

INNER JOIN producto p
ON p.idProducto=c.idProducto

WHERE c.idCarrito=?
AND c.idCliente=?
AND c.estado='pendiente'

LIMIT 1";

$stmt=mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param($stmt,"ii",$idCarrito,$idCliente);

}else{

$sql="SELECT

c.idCarrito,
c.cantidad,

p.stock

FROM carrito_online c

INNER JOIN producto p
ON p.idProducto=c.idProducto

WHERE c.idCarrito=?
AND c.token=?
AND c.estado='pendiente'

LIMIT 1";

$stmt=mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param($stmt,"is",$idCarrito,$token);

}

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(!$fila=mysqli_fetch_assoc($result)){

echo json_encode([
"estado"=>false,
"mensaje"=>"Producto no encontrado."
]);

exit;

}

/*
|--------------------------------------------------------------------------
| CALCULAR NUEVA CANTIDAD
|--------------------------------------------------------------------------
*/

$nuevaCantidad=$fila["cantidad"];

switch($accion){

case "sumar":

    $nuevaCantidad++;

    if($nuevaCantidad>$fila["stock"]){

        echo json_encode([
            "estado"=>false,
            "mensaje"=>"Stock insuficiente."
        ]);

        exit;

    }

break;

case "restar":

    $nuevaCantidad--;

break;

case "eliminar":

    $nuevaCantidad=0;

break;

default:

echo json_encode([
"estado"=>false,
"mensaje"=>"Acción inválida."
]);

exit;

}

/*
|--------------------------------------------------------------------------
| ELIMINAR
|--------------------------------------------------------------------------
*/

if($nuevaCantidad<=0){

$sql="DELETE FROM carrito_online
WHERE idCarrito=?";

$stmt=mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param($stmt,"i",$idCarrito);

mysqli_stmt_execute($stmt);

}else{

$sql="UPDATE carrito_online
SET cantidad=?
WHERE idCarrito=?";

$stmt=mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param($stmt,"ii",$nuevaCantidad,$idCarrito);

mysqli_stmt_execute($stmt);

}

/*
|--------------------------------------------------------------------------
| CONTADOR
|--------------------------------------------------------------------------
*/

if($idCliente>0){

$sql="SELECT IFNULL(SUM(cantidad),0) total

FROM carrito_online

WHERE idCliente=?

AND estado='pendiente'";

$stmt=mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param($stmt,"i",$idCliente);

}else{

$sql="SELECT IFNULL(SUM(cantidad),0) total

FROM carrito_online

WHERE token=?

AND estado='pendiente'";

$stmt=mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param($stmt,"s",$token);

}

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$total=mysqli_fetch_assoc($result);

echo json_encode([

"estado"=>true,

"contador"=>$total["total"]

]);