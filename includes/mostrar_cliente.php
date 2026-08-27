<?php

require_once "controladores/conexion.php";

$idCliente = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

$sql = "SELECT imagen
        FROM clientes
        WHERE idCliente=?";

$stmt = mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param($stmt,"i",$idCliente);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if($fila=mysqli_fetch_assoc($resultado)){

    if(!empty($fila["imagen"])){

        header("Content-Type: image/jpeg");

        echo $fila["imagen"];

        exit;

    }

}

readfile("assets/img/usuario.png");