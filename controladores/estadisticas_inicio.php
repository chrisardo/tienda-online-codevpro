<?php

require_once "conexion.php";

/*=========================================
=        CLIENTES
=========================================*/

$sql = "SELECT COUNT(*) total
        FROM clientes
        WHERE Eliminado=0";

$resultado = mysqli_query($conexion,$sql);

$totalClientes = mysqli_fetch_assoc($resultado)["total"];

/*=========================================
=        PRODUCTOS
=========================================*/

$sql = "SELECT COUNT(*) total
        FROM producto
        WHERE Eliminado=0
        AND tipo='producto'
        AND stock > 0";

$resultado = mysqli_query($conexion,$sql);

$totalProductos = mysqli_fetch_assoc($resultado)["total"];

/*=========================================
=        PEDIDOS
=========================================*/

$sql = "SELECT COUNT(*) total
        FROM ticket_ventas";

$resultado = mysqli_query($conexion,$sql);

$totalPedidos = mysqli_fetch_assoc($resultado)["total"];

/*=========================================
=        PRODUCTOS VENDIDOS
=========================================*/

$sql = "SELECT IFNULL(SUM(cantidad_total),0) total
        FROM cantidad_producto_vendido";

$resultado = mysqli_query($conexion,$sql);

$totalVendidos = mysqli_fetch_assoc($resultado)["total"];