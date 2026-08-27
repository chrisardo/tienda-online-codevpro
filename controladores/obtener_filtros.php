<?php
// Toda esta parte es de controladores/obtener_filtros.php
require_once "conexion.php";

/* ==========================
   CATEGORÍAS
========================== */

$sqlCategorias = "SELECT
                    id_categorias,
                    nombre
                  FROM categorias
                  ORDER BY nombre ASC";

$categorias = mysqli_query($conexion, $sqlCategorias);

if(!$categorias){
    die(mysqli_error($conexion));
}

/* ==========================
   MARCAS
========================== */

$sqlMarcas = "SELECT
                id_marca,
                nombre
              FROM marcas
              ORDER BY nombre ASC";

$marcas = mysqli_query($conexion, $sqlMarcas);

if(!$marcas){
    die(mysqli_error($conexion));
}

/* ==========================
   PRECIO MÍNIMO Y MÁXIMO
========================== */

$sqlPrecio = "SELECT
                MIN(precio) AS minimo,
                MAX(precio) AS maximo
              FROM producto";

$resultPrecio = mysqli_query($conexion, $sqlPrecio);

$precio = mysqli_fetch_assoc($resultPrecio);

?>