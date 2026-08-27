<?php

// ======================================================
// CoDevPro Technology
// controladores/obtener_producto2.php
// ======================================================

require_once "conexion.php";


// ======================================================
// SESIÓN
// ======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ======================================================
// DATOS INICIALES
// ======================================================

$idProducto = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

$idCliente = isset($_SESSION["idCliente"])
    ? intval($_SESSION["idCliente"])
    : 0;


// ======================================================
// VALIDAR PRODUCTO
// ======================================================

if ($idProducto <= 0) {

    die("Producto no válido");
}


// ======================================================
// OBTENER PRODUCTO
// ======================================================

$sql = "
SELECT

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
    ON c.id_categorias = p.id_categorias

LEFT JOIN marcas m
    ON m.id_marca = p.id_marca

LEFT JOIN imagenes i
    ON i.idProducto = p.idProducto
    AND i.orden = 1

LEFT JOIN favoritos f
    ON f.idProducto = p.idProducto
    AND f.idCliente = $idCliente

WHERE p.idProducto = $idProducto

LIMIT 1
";


$resultado = mysqli_query($conexion, $sql);


if (!$resultado) {

    die("Error al consultar producto: " .
        mysqli_error($conexion));
}


if (mysqli_num_rows($resultado) === 0) {

    die("Producto no encontrado");
}


$producto = mysqli_fetch_assoc($resultado);


// ======================================================
// IMÁGENES
// ======================================================

$sqlImagenes = "
SELECT *

FROM imagenes

WHERE idProducto = $idProducto

ORDER BY orden ASC, id_imagen ASC
";


$imagenes = mysqli_query(
    $conexion,
    $sqlImagenes
);


// ======================================================
// TESTIMONIOS
// ======================================================

$sqlTestimonios = "

SELECT

    t.calificacion,

    t.comentario,

    t.fecha,

    c.nombre

FROM testimonios t

INNER JOIN clientes c
    ON c.idCliente = t.idCliente

WHERE

    t.idProducto = $idProducto

    AND t.estado = 'APROBADO'

ORDER BY t.fecha DESC

LIMIT 5

";


$testimonios = mysqli_query(
    $conexion,
    $sqlTestimonios
);


// ======================================================
// CALIFICACIÓN DEL PRODUCTO
// ======================================================

$sqlRating = "

SELECT

    AVG(calificacion) AS promedio,

    COUNT(*) AS total

FROM testimonios

WHERE

    idProducto = $idProducto

    AND estado = 'APROBADO'

";


$resultRating = mysqli_query(
    $conexion,
    $sqlRating
);


$rating = mysqli_fetch_assoc(
    $resultRating
);


$promedio = round(
    $rating['promedio'] ?? 0,
    1
);


$totalOpiniones = intval(
    $rating['total'] ?? 0
);


// ======================================================
// CONFIGURACIÓN DE MONEDA E IMPUESTOS
// ======================================================

$idUserProducto = intval(
    $producto['id_user'] ?? 0
);


$sqlConfiguracion = "

SELECT

    nombre_moneda,

    codigo_moneda,

    simbolo_moneda,

    decimales,

    separador_decimal,

    separador_miles,

    posicion_simbolo,

    impuesto_activo,

    nombre_impuesto,

    porcentaje_impuesto,

    precios_incluyen_impuesto

FROM configuracion_monedas_impuestos

WHERE id_user = $idUserProducto

ORDER BY id_configuracion DESC

LIMIT 1

";


$resultConfiguracion = mysqli_query(
    $conexion,
    $sqlConfiguracion
);


// ======================================================
// VALORES PREDETERMINADOS
// ======================================================

$configImpuesto = [

    'nombre_moneda' => 'Sol Peruano',

    'codigo_moneda' => 'PEN',

    'simbolo_moneda' => 'S/',

    'decimales' => 2,

    'separador_decimal' => '.',

    'separador_miles' => ',',

    'posicion_simbolo' => 'ANTES',

    'impuesto_activo' => 0,

    'nombre_impuesto' => 'IGV',

    'porcentaje_impuesto' => 18,

    'precios_incluyen_impuesto' => 1

];


if (
    $resultConfiguracion &&
    mysqli_num_rows($resultConfiguracion) > 0
) {

    $configBD = mysqli_fetch_assoc(
        $resultConfiguracion
    );

    $configImpuesto = array_merge(
        $configImpuesto,
        $configBD
    );
}


// ======================================================
// DATOS DEL IMPUESTO
// ======================================================

$simboloMoneda = $configImpuesto['simbolo_moneda'] ?? 'S/';

$decimales = intval(
    $configImpuesto['decimales'] ?? 2
);

$impuestoActivo = intval(
    $configImpuesto['impuesto_activo'] ?? 0
);

$nombreImpuesto = trim(
    $configImpuesto['nombre_impuesto'] ?? 'IGV'
);

$porcentajeImpuesto = floatval(
    $configImpuesto['porcentaje_impuesto'] ?? 0
);

$preciosIncluyenImpuesto = intval(
    $configImpuesto['precios_incluyen_impuesto'] ?? 1
);


// ======================================================
// APLICA IMPUESTO AL PRODUCTO
// ======================================================

$productoAplicaImpuesto = intval(
    $producto['aplica_impuesto'] ?? 0
);


// ======================================================
// PRECIO DEL PRODUCTO
// ======================================================

$precioProducto = floatval(
    $producto['precio'] ?? 0
);


// ======================================================
// CÁLCULO DEL IMPUESTO
// ======================================================

$precioBase = $precioProducto;

$montoImpuesto = 0;

$precioFinal = $precioProducto;


// ------------------------------------------------------
// CASO 1:
// PRODUCTO CON IMPUESTO
// Y PRECIO YA INCLUYE IMPUESTO
// ------------------------------------------------------

if (
    $productoAplicaImpuesto === 1 &&
    $impuestoActivo === 1 &&
    $porcentajeImpuesto > 0 &&
    $preciosIncluyenImpuesto === 1
) {

    $precioBase =
        $precioProducto /
        (1 + ($porcentajeImpuesto / 100));

    $montoImpuesto =
        $precioProducto -
        $precioBase;

    $precioFinal =
        $precioProducto;
}


// ------------------------------------------------------
// CASO 2:
// PRODUCTO CON IMPUESTO
// PERO PRECIO NO INCLUYE IMPUESTO
// ------------------------------------------------------

elseif (
    $productoAplicaImpuesto === 1 &&
    $impuestoActivo === 1 &&
    $porcentajeImpuesto > 0 &&
    $preciosIncluyenImpuesto === 0
) {

    $precioBase =
        $precioProducto;

    $montoImpuesto =
        $precioBase *
        ($porcentajeImpuesto / 100);

    $precioFinal =
        $precioBase +
        $montoImpuesto;
}


// ------------------------------------------------------
// CASO 3:
// NO APLICA IMPUESTO
// ------------------------------------------------------

else {

    $precioBase =
        $precioProducto;

    $montoImpuesto = 0;

    $precioFinal =
        $precioProducto;
}


// ======================================================
// ESTADO FINAL DEL IMPUESTO
// ======================================================

$productoTieneImpuesto =
    (
        $productoAplicaImpuesto === 1 &&
        $impuestoActivo === 1 &&
        $porcentajeImpuesto > 0
    );


// ======================================================
// VARIABLES ÚTILES PARA LA VISTA
// ======================================================

$textoPrecioImpuesto = $productoTieneImpuesto
    ? (
        $preciosIncluyenImpuesto
        ? "Precio incluye $nombreImpuesto"
        : "Precio más $nombreImpuesto"
    )
    : "Producto sin impuesto";
