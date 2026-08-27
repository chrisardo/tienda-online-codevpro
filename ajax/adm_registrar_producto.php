<?php
//======================================================
// CoDevPro Technology
// ajax/adm_registrar_producto.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");


require_once "../controladores/conexion.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

mysqli_report(
    MYSQLI_REPORT_ERROR |
        MYSQLI_REPORT_STRICT
);


/*======================================================
VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idUser"])) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Sesión no válida."

    ]);

    exit;
}


$idUser = intval($_SESSION["idUser"]);



/*======================================================
VALIDAR MÉTODO
======================================================*/


if ($_SERVER["REQUEST_METHOD"] !== "POST") {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Método no permitido."

    ]);


    exit;
}



/*======================================================
RECIBIR DATOS GENERALES
======================================================*/


$codigo = trim($_POST["codigo"] ?? '');

$tipo = trim($_POST["tipo"] ?? '');

$nombre = trim($_POST["nombre"] ?? '');



/*======================================================
DATOS NUMÉRICOS GENERALES
======================================================*/


$precioAnterior = floatval(
    $_POST["precio_anterior"] ?? 0
);


$descuento = intval(
    $_POST["descuento"] ?? 0
);


$stock = intval(
    $_POST["stock"] ?? 0
);



/*======================================================
CHECKBOX
======================================================*/


$oferta = isset($_POST["oferta"]) ? 1 : 0;


$destacado = isset($_POST["destacado"]) ? 1 : 0;


$nuevo = isset($_POST["nuevo"]) ? 1 : 0;


$envioGratis = isset($_POST["envio_gratis"]) ? 1 : 0;

$aplicaImpuesto = isset($_POST["aplica_impuesto"])
    ? (int)$_POST["aplica_impuesto"]
    : 0;

/*======================================================
CATEGORIZACIÓN
======================================================*/


$idCategoria = intval(
    $_POST["id_categorias"] ?? 0
);


$idMarca = intval(
    $_POST["id_marca"] ?? 0
);


$idProveedor = intval(
    $_POST["id_provedor"] ?? 0
);



/*======================================================
VARIABLES QUE CAMBIAN SEGÚN TIPO
======================================================*/


$descripcion = "";

$precio = 0;

$costoCompra = 0;

$idSucursal = 0;



/*======================================================
SI ES PRODUCTO
======================================================*/


if ($tipo === "Producto") {


    $descripcion = trim(
        $_POST["descripcion"] ?? ''
    );


    $precio = floatval(
        $_POST["precio"] ?? 0
    );


    $costoCompra = floatval(
        $_POST["costo_compra"] ?? 0
    );


    $idSucursal = intval(
        $_POST["id_sucursal"] ?? 0
    );
}



/*======================================================
SI ES SERVICIO
======================================================*/


if ($tipo === "Servicio") {


    $descripcion = trim(
        $_POST["descripcion_servicio"] ?? ''
    );


    $precio = floatval(
        $_POST["precio_servicio"] ?? 0
    );


    $idSucursal = intval(
        $_POST["sucursal_servicio"] ?? 0
    );


    /*
    Los servicios no manejan:
    - stock
    - costo compra
    - proveedor
    - marca
    */


    $stock = 0;

    $costoCompra = 0;
}



/*======================================================
VALORES POR DEFECTO
======================================================*/


$fechaRegistro = date("Y-m-d");


$fechaActualizado = date("Y-m-d");


$eliminado = 0;
/*======================================================
VALIDACIONES GENERALES
======================================================*/


/*=============================================
VALIDAR CÓDIGO
=============================================*/

if (empty($codigo)) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Ingrese el código del producto."

    ]);


    exit;
}



/*=============================================
VALIDAR NOMBRE
=============================================*/

if (empty($nombre)) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Ingrese el nombre del producto o servicio."

    ]);


    exit;
}



/*=============================================
VALIDAR TIPO
=============================================*/

if (
    empty($tipo) ||
    ($tipo !== "Producto" && $tipo !== "Servicio")
) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Seleccione un tipo válido."

    ]);


    exit;
}



/*=============================================
VALIDAR PRECIO
=============================================*/

if ($precio <= 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Ingrese un precio válido."

    ]);


    exit;
}



/*=============================================
VALIDAR SUCURSAL
(PRODUCTO Y SERVICIO)
=============================================*/


if ($idSucursal <= 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Seleccione una sucursal."

    ]);


    exit;
}



/*=============================================
VALIDACIONES EXCLUSIVAS PRODUCTO
=============================================*/


if ($tipo === "Producto") {



    /*-----------------------------------------
    CATEGORÍA OBLIGATORIA
    -----------------------------------------*/


    if ($idCategoria <= 0) {


        echo json_encode([

            "estado" => false,

            "mensaje" => "Seleccione una categoría."

        ]);


        exit;
    }



    /*-----------------------------------------
    STOCK
    -----------------------------------------*/


    if ($stock < 0) {


        echo json_encode([

            "estado" => false,

            "mensaje" => "El stock no puede ser negativo."

        ]);


        exit;
    }



    /*-----------------------------------------
    COSTO COMPRA
    -----------------------------------------*/


    if ($costoCompra < 0) {


        echo json_encode([

            "estado" => false,

            "mensaje" => "El costo de compra no es válido."

        ]);


        exit;
    }
}



/*=============================================
VALIDAR DESCUENTO
=============================================*/


if ($descuento < 0 || $descuento > 99) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "El descuento debe estar entre 0 y 99%."

    ]);


    exit;
}



/*======================================================
VALIDAR CÓDIGO DUPLICADO
======================================================*/


$sqlCodigo = "

SELECT idProducto

FROM producto

WHERE codigo = ?

AND id_user = ?

AND Eliminado = 0

LIMIT 1

";



$stmtCodigo = mysqli_prepare(

    $conexion,

    $sqlCodigo

);



mysqli_stmt_bind_param(

    $stmtCodigo,

    "si",

    $codigo,

    $idUser

);



mysqli_stmt_execute(

    $stmtCodigo

);



$resultCodigo = mysqli_stmt_get_result(

    $stmtCodigo

);



if (mysqli_num_rows($resultCodigo) > 0) {



    echo json_encode([

        "estado" => false,

        "mensaje" => "Ya existe un producto con ese código."

    ]);


    exit;
}



mysqli_stmt_close($stmtCodigo);

/*======================================================
REGISTRAR PRODUCTO / SERVICIO
======================================================*/


$sql = "

INSERT INTO producto(

    codigo,

    nombre,

    precio,

    precio_anterior,

    descuento,
    aplica_impuesto,
    oferta,

    destacado,

    nuevo,

    stock,

    envio_gratis,

    id_sucursal,

    id_user,

    id_categorias,

    fecha_registro,

    descripcion,

    id_provedor,

    Eliminado,

    id_marca,

    costo_compra,

    fecha_actualizado,

    tipo


)

VALUES(


    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?,
    ?,
    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?,

    ?

)


";



$stmt = mysqli_prepare(

    $conexion,

    $sql

);



if (!$stmt) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Error al preparar registro.",

        "error" => mysqli_error($conexion)

    ]);


    exit;
}


/*======================================================
BIND PARAM
======================================================*/


mysqli_stmt_bind_param(

    $stmt,

    "ssddiiiiiiiiiissiiidss",


    $codigo,

    $nombre,

    $precio,

    $precioAnterior,

    $descuento,
    $aplicaImpuesto,

    $oferta,

    $destacado,

    $nuevo,

    $stock,

    $envioGratis,

    $idSucursal,

    $idUser,

    $idCategoria,

    $fechaRegistro,

    $descripcion,

    $idProveedor,

    $eliminado,

    $idMarca,

    $costoCompra,

    $fechaActualizado,

    $tipo

);



try {


    mysqli_stmt_execute($stmt);



    $idProducto = mysqli_insert_id($conexion);



    echo json_encode([

        "estado" => true,

        "mensaje" =>
        "Registro realizado correctamente.",

        "idProducto" => $idProducto

    ]);
} catch (Exception $e) {



    echo json_encode([

        "estado" => false,

        "mensaje" => "Error al registrar información.",

        "error" => $e->getMessage()

    ]);
}



mysqli_stmt_close($stmt);


mysqli_close($conexion);
