<?php

require_once "conexion.php";
require_once "token_carrito.php";

function fusionarCarrito($conexion, $idCliente)
{
    $token = obtenerTokenCarrito();

    /* Buscar productos del carrito temporal */

    $sql = "SELECT *
            FROM carrito_online
            WHERE token=?
            AND estado='pendiente'";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);

    $productos = mysqli_stmt_get_result($stmt);

    while ($item = mysqli_fetch_assoc($productos)) {
        /* Verificar si el cliente ya tenía ese producto */

        $sqlExiste = "SELECT idCarrito,cantidad
                    FROM carrito_online
                    WHERE idCliente=?
                    AND idProducto=?
                    AND estado='pendiente'";

        $stmtExiste = mysqli_prepare($conexion, $sqlExiste);

        mysqli_stmt_bind_param(
            $stmtExiste,
            "ii",
            $idCliente,
            $item["idProducto"]
        );

        mysqli_stmt_execute($stmtExiste);

        $resExiste = mysqli_stmt_get_result($stmtExiste);

        if (mysqli_num_rows($resExiste) > 0) {
            $fila = mysqli_fetch_assoc($resExiste);

            $nuevaCantidad =
                $fila["cantidad"] + $item["cantidad"];

            $sqlUpdate = "UPDATE carrito_online
                        SET cantidad=?
                        WHERE idCarrito=?";

            $stmtUpdate = mysqli_prepare($conexion, $sqlUpdate);

            mysqli_stmt_bind_param(
                $stmtUpdate,
                "ii",
                $nuevaCantidad,
                $fila["idCarrito"]
            );

            mysqli_stmt_execute($stmtUpdate);

            /* eliminar carrito temporal */

            mysqli_query(
                $conexion,
                "DELETE FROM carrito_online
                 WHERE idCarrito=" . $item["idCarrito"]
            );
        } else {

            $sqlMover = "UPDATE carrito_online
                       SET
                       idCliente=?,
                       token=NULL
                       WHERE idCarrito=?";

            $stmtMover = mysqli_prepare($conexion, $sqlMover);

            mysqli_stmt_bind_param(
                $stmtMover,
                "ii",
                $idCliente,
                $item["idCarrito"]
            );

            mysqli_stmt_execute($stmtMover);
        }
    }
}
