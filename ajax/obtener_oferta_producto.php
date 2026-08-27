<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_oferta_producto.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

session_start();

//=====================================================
// CONFIGURACIÓN RESPUESTA
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

//=====================================================
// VALIDAR SESIÓN
//=====================================================

if (!isset($_SESSION["idUser"]) || empty($_SESSION["idUser"])) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "Sesión no válida."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// ID USUARIO
//=====================================================

$idUser = (int) $_SESSION["idUser"];

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "No se pudo establecer la conexión con la base de datos."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// CHARSET
//=====================================================

if (!$conexion->set_charset("utf8mb4")) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "No se pudo configurar el charset de la conexión."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// RECIBIR ID PRODUCTO
//=====================================================

$idProducto = isset($_GET["idProducto"])
    ? (int) $_GET["idProducto"]
    : 0;

//=====================================================
// VALIDAR ID
//=====================================================

if ($idProducto <= 0) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "El producto seleccionado no es válido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// CONSULTAR PRODUCTO
//=====================================================
//
// Importante:
// Se valida también p.id_user para impedir que
// un usuario consulte productos de otro usuario.
//
//=====================================================

$sql = "
    SELECT

        p.idProducto,

        p.codigo,

        p.nombre,

        p.descripcion,

        p.precio,

        p.precio_anterior,

        p.descuento,

        p.oferta,

        p.destacado,

        p.nuevo,

        p.stock,

        p.envio_gratis,

        p.peso,

        p.tipo,

        p.id_user,

        p.id_categorias,

        p.id_marca,

        p.id_provedor,

        p.id_sucursal,

        p.fecha_registro,

        p.fecha_actualizado,

        c.nombre AS categoria,

        m.nombre AS marca,

        s.nombre AS sucursal

    FROM producto p

    LEFT JOIN categorias c
        ON c.id_categorias = p.id_categorias
        AND c.id_user = p.id_user

    LEFT JOIN marcas m
        ON m.id_marca = p.id_marca
        AND m.id_user = p.id_user

    LEFT JOIN sucursal s
        ON s.id_sucursal = p.id_sucursal
        AND s.id_user = p.id_user

    WHERE
        p.idProducto = ?
        AND p.id_user = ?
        AND p.Eliminado = 0

    LIMIT 1
";

//=====================================================
// PREPARAR
//=====================================================

$stmt = $conexion->prepare($sql);

if (!$stmt) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "No se pudo preparar la consulta del producto.",
            "error" => $conexion->error
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// BIND
//=====================================================

$stmt->bind_param(
    "ii",
    $idProducto,
    $idUser
);

//=====================================================
// EJECUTAR
//=====================================================

if (!$stmt->execute()) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "No se pudo obtener la información del producto.",
            "error" => $stmt->error
        ],
        JSON_UNESCAPED_UNICODE
    );

    $stmt->close();

    exit();
}

//=====================================================
// RESULTADO
//=====================================================

$resultado = $stmt->get_result();

//=====================================================
// VALIDAR EXISTENCIA
//=====================================================

if ($resultado->num_rows === 0) {

    $stmt->close();

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "El producto no existe, fue eliminado o no pertenece al usuario actual."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// OBTENER PRODUCTO
//=====================================================

$producto = $resultado->fetch_assoc();

$stmt->close();

//=====================================================
// NORMALIZAR DATOS
//=====================================================

$producto["idProducto"] = (int) (
    $producto["idProducto"] ?? 0
);

$producto["id_user"] = (int) (
    $producto["id_user"] ?? 0
);

$producto["id_categorias"] = (int) (
    $producto["id_categorias"] ?? 0
);

$producto["id_marca"] = (int) (
    $producto["id_marca"] ?? 0
);

$producto["id_provedor"] = (int) (
    $producto["id_provedor"] ?? 0
);

$producto["id_sucursal"] = (int) (
    $producto["id_sucursal"] ?? 0
);

$producto["stock"] = (int) (
    $producto["stock"] ?? 0
);

$producto["oferta"] = (int) (
    $producto["oferta"] ?? 0
);

$producto["descuento"] = (float) (
    $producto["descuento"] ?? 0
);

$producto["precio"] = (float) (
    $producto["precio"] ?? 0
);

$producto["precio_anterior"] = (float) (
    $producto["precio_anterior"] ?? 0
);

$producto["peso"] = (float) (
    $producto["peso"] ?? 0
);

$producto["destacado"] = (int) (
    $producto["destacado"] ?? 0
);

$producto["nuevo"] = (int) (
    $producto["nuevo"] ?? 0
);

$producto["envio_gratis"] = (int) (
    $producto["envio_gratis"] ?? 0
);

//=====================================================
// NORMALIZAR TEXTOS
//=====================================================

$producto["codigo"] = $producto["codigo"] ?? "";

$producto["nombre"] = $producto["nombre"] ?? "";

$producto["descripcion"] = $producto["descripcion"] ?? "";

$producto["tipo"] = $producto["tipo"] ?? "";

$producto["categoria"] = $producto["categoria"] ?? "";

$producto["marca"] = $producto["marca"] ?? "";

$producto["sucursal"] = $producto["sucursal"] ?? "";

//=====================================================
// CALCULAR PRECIO FINAL
//=====================================================

$precio = (float) $producto["precio"];

$descuento = (float) $producto["descuento"];

$precioFinal = $precio;

if ($descuento > 0) {

    $precioFinal =
        $precio -
        (
            $precio *
            $descuento /
            100
        );
}

//=====================================================
// EVITAR PRECIO NEGATIVO
//=====================================================

if ($precioFinal < 0) {
    $precioFinal = 0;
}

//=====================================================
// REDONDEAR
//=====================================================

$precioFinal = round(
    $precioFinal,
    2
);

$producto["precio_final"] = $precioFinal;

//=====================================================
// ESTADO DE PROMOCIÓN
//=====================================================

$producto["tiene_descuento"] =
    $producto["descuento"] > 0 ? 1 : 0;

$producto["esta_en_oferta"] =
    $producto["oferta"] === 1 ? 1 : 0;

//=====================================================
// RESPUESTA
//=====================================================

echo json_encode(
    [
        "success" => true,

        "mensaje" => "Producto obtenido correctamente.",

        "producto" => $producto
    ],
    JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
);

exit();
