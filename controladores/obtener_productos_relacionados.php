<?php
//Todo esto es de controladores/obtener_productos_relacionados.php
require_once "conexion.php";
/*
|--------------------------------------------------------------------------
| Se asume que $producto ya fue obtenido en
| obtener_detalle_producto.php
|--------------------------------------------------------------------------
*/

$idProducto   = $producto['idProducto'];
$idCategoria  = $producto['id_categorias'];
$idProducto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idCliente = $_SESSION["idCliente"] ?? 0;
$sqlRelacionados = "SELECT
                        p.*,
                        c.nombre AS categoria,
                        m.nombre AS marca,
                        i.imagenes AS imagen,
                        i.id_imagen,
                        CASE
                            WHEN f.id_favorito IS NULL THEN 0
                            ELSE 1
                        END AS favorito
                    FROM producto p

                    INNER JOIN categorias c
                        ON c.id_categorias=p.id_categorias

                    INNER JOIN marcas m
                        ON m.id_marca=p.id_marca
                    LEFT JOIN imagenes i
                        ON i.idProducto=p.idProducto
                        AND i.orden=1
                    LEFT JOIN favoritos f
                        ON f.idProducto=p.idProducto
                        AND f.idCliente=$idCliente
                    WHERE p.id_categorias=?
                    AND p.idProducto<>?
                    AND p.Eliminado=0

                    ORDER BY RAND()

                    LIMIT 4";

$stmt = mysqli_prepare($conexion, $sqlRelacionados);

mysqli_stmt_bind_param($stmt, "ii", $idCategoria, $idProducto);

mysqli_stmt_execute($stmt);

$productosRelacionados = mysqli_stmt_get_result($stmt);
