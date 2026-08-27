<?php
//======================================================
// CoDevPro Technology
// ajax/actualizar_producto.php
// Actualizar producto / servicio
//======================================================


session_start();

header("Content-Type: application/json; charset=utf-8");


require_once "../controladores/conexion.php";



/*======================================================
VALIDAR SESION
======================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}


$idUser = $_SESSION["idUser"];



/*======================================================
RECIBIR DATOS JSON
======================================================*/


$data = json_decode(
    file_get_contents("php://input"),
    true
);



if (!$data) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "No se recibieron datos"

    ]);

    exit;
}




/*======================================================
DATOS GENERALES
======================================================*/


$idProducto = intval($data["idProducto"] ?? 0);

$codigo = trim($data["codigo"] ?? "");

$nombre = trim($data["nombre"] ?? "");

$precio = $data["precio"] ?? "";

$tipo = $data["tipo"] ?? "";

$descripcion = trim($data["descripcion"] ?? "");
$aplicaImpuesto = isset($data["aplica_impuesto"])
    ? intval($data["aplica_impuesto"])
    : 0;


if ($idProducto <= 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Producto inválido"

    ]);

    exit;
}




/*======================================================
VALIDAR QUE EXISTE Y PERTENECE AL USUARIO
======================================================*/


$sql = "

SELECT *

FROM producto

WHERE idProducto='$idProducto'

AND id_user='$idUser'

LIMIT 1

";


$resultado = mysqli_query(
    $conexion,
    $sql
);



if (mysqli_num_rows($resultado) == 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Producto no encontrado"

    ]);


    exit;
}




/*======================================================
VALIDACIONES
======================================================*/


$errores = [];



// CAMPOS GENERALES

if ($codigo == "") {

    $errores[] = "El código es obligatorio";
}


if ($nombre == "") {

    $errores[] = "El nombre es obligatorio";
}


if ($precio === "" || !is_numeric($precio)) {


    $errores[] = "El precio es obligatorio";
}




if (count($errores) > 0) {


    echo json_encode([

        "estado" => false,

        "mensaje" => implode(", ", $errores)

    ]);


    exit;
}





/*======================================================
ACTUALIZAR PRODUCTO
======================================================*/


if ($tipo == "Producto") {



    $precio_anterior = $data["precio_anterior"] ?? "";

    $descuento = !empty($data["descuento"])
        ? intval($data["descuento"])
        : 0;

    $stock = $data["stock"] ?? "";

    $costo_compra = $data["costo_compra"] ?? "";

    $categoria = $data["id_categorias"] ?? "";

    $marca = $data["id_marca"] ?? "";

    $proveedor = $data["id_provedor"] ?? "";

    $sucursal = $data["id_sucursal"] ?? "";




    if (
        $precio_anterior === "" ||
        $stock === "" ||
        $costo_compra === "" ||
        $categoria === "" ||
        $marca === "" ||
        $proveedor === "" ||
        $sucursal === ""
    ) {


        echo json_encode([

            "estado" => false,

            "mensaje" => "Complete todos los campos obligatorios del producto"

        ]);


        exit;
    }





    $sqlUpdate = "

    UPDATE producto SET


        codigo='$codigo',

        nombre='$nombre',

        precio='$precio',

        precio_anterior='$precio_anterior',

        descuento='$descuento',

        stock='$stock',

        costo_compra='$costo_compra',
        aplica_impuesto = '$aplicaImpuesto',
        id_categorias='$categoria',

        id_marca='$marca',

        id_provedor='$proveedor',

        id_sucursal='$sucursal',

        descripcion='$descripcion',

        fecha_actualizado=CURDATE()


    WHERE idProducto='$idProducto'

    AND id_user='$idUser'


    ";
}





/*======================================================
ACTUALIZAR SERVICIO
======================================================*/ else if ($tipo == "Servicio") {



    $sucursal = $data["id_sucursal"] ?? "";




    if ($sucursal == "") {


        echo json_encode([

            "estado" => false,

            "mensaje" => "La sucursal del servicio es obligatoria"

        ]);


        exit;
    }




    $sqlUpdate = "

    UPDATE producto SET


        codigo='$codigo',

        nombre='$nombre',

        precio='$precio',

        id_sucursal='$sucursal',

        descripcion='$descripcion',

        fecha_actualizado=CURDATE()


    WHERE idProducto='$idProducto'

    AND id_user='$idUser'


    ";
} else {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Tipo de registro inválido"

    ]);


    exit;
}




/*======================================================
EJECUTAR ACTUALIZACION
======================================================*/


if (mysqli_query($conexion, $sqlUpdate)) {



    echo json_encode([

        "estado" => true,

        "mensaje" => "Cambios guardados correctamente"

    ]);
} else {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Error al actualizar: " . mysqli_error($conexion)

    ]);
}
