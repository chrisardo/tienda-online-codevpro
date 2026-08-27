<?php

require_once "controladores/conexion.php";

$id = intval($_GET["id"] ?? 0);

$sql = "SELECT imagen
        FROM categorias
        WHERE id_categorias=$id
        LIMIT 1";

$resultado = mysqli_query($conexion, $sql);

if ($fila = mysqli_fetch_assoc($resultado)) {

    header("Content-Type:image/jpeg");

    echo $fila["imagen"];
} else {

    readfile("assets/img/categoria.png");
}
