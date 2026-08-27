<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_detalle_producto_proveedor.php
// Módulo: Productos del Proveedor
// Sistema: Inventa
//=====================================================


//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header('Content-Type: application/json; charset=utf-8');


//=====================================================
// RESPUESTA INICIAL
//=====================================================

$respuesta = [
    'success'  => false,
    'mensaje'  => '',
    'producto' => null
];


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


if ($idUser <= 0) {

    $respuesta['mensaje'] = 'Sesión no válida.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    $respuesta['mensaje'] =
        'No se pudo establecer conexión con la base de datos.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// OBTENER ID DEL PRODUCTO
//=====================================================

$idProducto = isset($_POST['idProducto'])
    ? (int) $_POST['idProducto']
    : 0;


if ($idProducto <= 0) {

    $respuesta['mensaje'] =
        'ID de producto inválido.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// CONSULTAR PRODUCTO
//=====================================================
//
// Se relacionan:
//
// producto
// categorias
// marcas
// provedores
//
// Se utiliza LEFT JOIN porque un producto podría
// tener categoría, marca o proveedor sin correspondencia.
//
//=====================================================

$sql = "
    SELECT
        p.idProducto,
        p.codigo,
        p.nombre,
        p.precio,
        p.precio_anterior,
        p.descuento,
        p.oferta,
        p.destacado,
        p.nuevo,
        p.stock,
        p.envio_gratis,
        p.id_sucursal,
        p.id_user,
        p.id_categorias,
        p.fecha_registro,
        p.descripcion,
        p.id_provedor,
        p.Eliminado,
        p.id_marca,
        p.costo_compra,
        p.fecha_actualizado,
        p.tipo,

        c.nombre AS categoria,

        m.nombre AS marca,

        pr.nombre AS proveedor

    FROM producto p

    LEFT JOIN categorias c
        ON c.id_categorias = p.id_categorias
        AND c.id_user = p.id_user

    LEFT JOIN marcas m
        ON m.id_marca = p.id_marca
        AND m.id_user = p.id_user

    LEFT JOIN provedores pr
        ON pr.id_provedor = p.id_provedor
        AND pr.id_user = p.id_user

    WHERE p.idProducto = ?
      AND p.id_user = ?

    LIMIT 1
";


//=====================================================
// PREPARAR CONSULTA
//=====================================================

$stmt = mysqli_prepare($conexion, $sql);


if (!$stmt) {

    $respuesta['mensaje'] =
        'No se pudo preparar la consulta del producto.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// ASIGNAR PARÁMETROS
//=====================================================

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idProducto,
    $idUser
);


//=====================================================
// EJECUTAR
//=====================================================

if (!mysqli_stmt_execute($stmt)) {

    $respuesta['mensaje'] =
        'No se pudo ejecutar la consulta del producto.';

    mysqli_stmt_close($stmt);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// OBTENER RESULTADO
//=====================================================

$resultado = mysqli_stmt_get_result($stmt);


if (!$resultado) {

    $respuesta['mensaje'] =
        'No se pudo obtener la información del producto.';

    mysqli_stmt_close($stmt);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// VERIFICAR PRODUCTO
//=====================================================

if (mysqli_num_rows($resultado) === 0) {

    $respuesta['mensaje'] =
        'El producto no existe o no pertenece al usuario actual.';

    mysqli_stmt_close($stmt);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// OBTENER PRODUCTO
//=====================================================

$producto = mysqli_fetch_assoc($resultado);


//=====================================================
// CERRAR STATEMENT
//=====================================================

mysqli_stmt_close($stmt);


//=====================================================
// BUSCAR IMAGEN PRINCIPAL
//=====================================================
//
// No enviamos el BLOB por JSON.
//
// Se genera una URL hacia mostrar_imagen.php.
//
// Primero se intenta obtener la imagen con menor orden.
//
//=====================================================

$imagen = null;

$sqlImagen = "
    SELECT
        id_imagen
    FROM imagenes
    WHERE idProducto = ?
    ORDER BY
        CASE
            WHEN orden IS NULL THEN 1
            ELSE 0
        END ASC,
        orden ASC,
        id_imagen ASC
    LIMIT 1
";


$stmtImagen = mysqli_prepare(
    $conexion,
    $sqlImagen
);


if ($stmtImagen) {

    mysqli_stmt_bind_param(
        $stmtImagen,
        "i",
        $idProducto
    );


    if (mysqli_stmt_execute($stmtImagen)) {

        $resultadoImagen =
            mysqli_stmt_get_result($stmtImagen);


        if (
            $resultadoImagen &&
            mysqli_num_rows($resultadoImagen) > 0
        ) {

            $filaImagen =
                mysqli_fetch_assoc($resultadoImagen);


            $idImagen =
                (int) $filaImagen['id_imagen'];


            if ($idImagen > 0) {

                $imagen =
                    "mostrar_imagen.php?id=" .
                    $idProducto .
                    "&img=" .
                    $idImagen;
            }
        }
    }


    mysqli_stmt_close($stmtImagen);
}


//=====================================================
// IMAGEN POR DEFECTO
//=====================================================

if (!$imagen) {

    $imagen = "assets/img/producto_default.png";
}


//=====================================================
// NORMALIZAR DATOS
//=====================================================

$productoRespuesta = [

    'idProducto' => (int) $producto['idProducto'],

    'codigo' => $producto['codigo'] ?? '',

    'nombre' => $producto['nombre'] ?? '',

    'tipo' => $producto['tipo'] ?? '',

    'precio' => (float) ($producto['precio'] ?? 0),

    'precio_anterior' =>
    (float) ($producto['precio_anterior'] ?? 0),

    'descuento' =>
    (int) ($producto['descuento'] ?? 0),

    'oferta' =>
    (int) ($producto['oferta'] ?? 0),

    'destacado' =>
    (int) ($producto['destacado'] ?? 0),

    'nuevo' =>
    (int) ($producto['nuevo'] ?? 0),

    'stock' =>
    (int) ($producto['stock'] ?? 0),

    'envio_gratis' =>
    (int) ($producto['envio_gratis'] ?? 0),

    'id_sucursal' =>
    (int) ($producto['id_sucursal'] ?? 0),

    'id_categorias' =>
    (int) ($producto['id_categorias'] ?? 0),

    'categoria' =>
    $producto['categoria'] ?? 'Sin categoría',

    'id_provedor' =>
    (int) ($producto['id_provedor'] ?? 0),

    'proveedor' =>
    $producto['proveedor'] ?? 'Sin proveedor',

    'id_marca' =>
    (int) ($producto['id_marca'] ?? 0),

    'marca' =>
    $producto['marca'] ?? 'Sin marca',

    'costo_compra' =>
    (float) ($producto['costo_compra'] ?? 0),

    'descripcion' =>
    $producto['descripcion'] ?? '',

    'fecha_registro' =>
    $producto['fecha_registro'] ?? '',

    'fecha_actualizado' =>
    $producto['fecha_actualizado'] ?? '',

    'Eliminado' =>
    (int) ($producto['Eliminado'] ?? 0),

    'imagen' => $imagen
];


//=====================================================
// RESPUESTA EXITOSA
//=====================================================

$respuesta['success'] = true;

$respuesta['producto'] = $productoRespuesta;


//=====================================================
// ENVIAR JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
