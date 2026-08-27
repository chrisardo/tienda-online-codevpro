<?php

//======================================================
// CoDevPro Technology
// preferencias_cliente.php
//======================================================

require_once "conexion.php";


function obtenerPreferenciasCliente($idCliente)
{

    global $conexion;

    $sql = "

    SELECT *

    FROM preferencias_cliente

    WHERE idCliente = ?

    LIMIT 1

    ";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idCliente
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    $preferencias = mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);


    return $preferencias;
}
