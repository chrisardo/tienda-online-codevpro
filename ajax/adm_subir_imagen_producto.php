<?php
//======================================================
// CoDevPro Technology
// ajax/adm_subir_imagen_producto.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

mysqli_report(
    MYSQLI_REPORT_ERROR |
        MYSQLI_REPORT_STRICT
);


/*=============================================
VALIDAR SESIÓN
=============================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión inválida."
    ]);

    exit;
}


$idUser = intval($_SESSION["idUser"]);


/*=============================================
VALIDAR PRODUCTO
=============================================*/

$idProducto = intval($_POST["idProducto"] ?? 0);


if ($idProducto <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Producto inválido."
    ]);

    exit;
}


/*=============================================
OBTENER INFORMACIÓN DEL PRODUCTO
=============================================*/

$sqlProducto = "

SELECT 
    idProducto,
    tipo

FROM producto

WHERE idProducto = ?
AND id_user = ?

LIMIT 1

";


$stmtProducto = mysqli_prepare(
    $conexion,
    $sqlProducto
);


mysqli_stmt_bind_param(
    $stmtProducto,
    "ii",
    $idProducto,
    $idUser
);


mysqli_stmt_execute(
    $stmtProducto
);


$resultProducto = mysqli_stmt_get_result(
    $stmtProducto
);



if (mysqli_num_rows($resultProducto) == 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El producto no existe."
    ]);

    exit;
}



$producto = mysqli_fetch_assoc(
    $resultProducto
);



/*=============================================
SI ES SERVICIO NO USA IMÁGENES
=============================================*/


if ($producto["tipo"] == "Servicio") {


    echo json_encode([
        "estado" => true,
        "mensaje" => "Servicio registrado sin imágenes."
    ]);

    exit;
}


/*=============================================
VALIDAR IMÁGENES
=============================================*/


if (!isset($_FILES["imagenes"])) {


    echo json_encode([
        "estado" => false,
        "mensaje" => "No se recibieron imágenes."
    ]);


    exit;
}



$totalImagenes = count(
    $_FILES["imagenes"]["name"]
);



if ($totalImagenes <= 0) {


    echo json_encode([
        "estado" => false,
        "mensaje" => "Debe seleccionar al menos una imagen."
    ]);


    exit;
}



/*=============================================
CONTAR IMÁGENES EXISTENTES
=============================================*/


$sqlCount = "

SELECT COUNT(*) AS total

FROM imagenes

WHERE idProducto = ?

";


$stmtCount = mysqli_prepare(
    $conexion,
    $sqlCount
);



mysqli_stmt_bind_param(
    $stmtCount,
    "i",
    $idProducto
);



mysqli_stmt_execute(
    $stmtCount
);



$resultCount = mysqli_stmt_get_result(
    $stmtCount
);



$rowCount = mysqli_fetch_assoc(
    $resultCount
);



$totalExistentes = intval(
    $rowCount["total"]
);



if (($totalExistentes + $totalImagenes) > 4) {


    echo json_encode([
        "estado" => false,
        "mensaje" => "El producto no puede tener más de 4 imágenes."
    ]);


    exit;
}



/*=============================================
CONFIGURACIÓN
=============================================*/


$permitidos = [

    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/webp"

];


$tamanoMaximo = 2.7 * 1024 * 1024;


$fechaRegistro = date("Y-m-d");

$fechaActualizado = date("Y-m-d");



$imagenesGuardadas = 0;



/*=============================================
GUARDAR IMÁGENES
=============================================*/


try {


    foreach ($_FILES["imagenes"]["tmp_name"] as $i => $tmp) {


        if (!file_exists($tmp)) {

            continue;
        }



        $tipoArchivo = $_FILES["imagenes"]["type"][$i];


        $tamanoArchivo = $_FILES["imagenes"]["size"][$i];



        if (!in_array($tipoArchivo, $permitidos)) {

            continue;
        }



        if ($tamanoArchivo > $tamanoMaximo) {

            continue;
        }



        $imagen = file_get_contents($tmp);



        $orden = intval(
            $_POST["orden"][$i] ?? ($i + 1)
        );



        $sqlInsert = "

        INSERT INTO imagenes(

            imagenes,
            idProducto,
            fecha_registro,
            fecha_actualizado,
            orden

        )

        VALUES(

            ?,
            ?,
            ?,
            ?,
            ?

        )

        ";



        $stmt = mysqli_prepare(
            $conexion,
            $sqlInsert
        );



        $null = null;



        mysqli_stmt_bind_param(

            $stmt,

            "bissi",

            $null,
            $idProducto,
            $fechaRegistro,
            $fechaActualizado,
            $orden

        );



        mysqli_stmt_send_long_data(
            $stmt,
            0,
            $imagen
        );



        mysqli_stmt_execute(
            $stmt
        );



        mysqli_stmt_close(
            $stmt
        );


        $imagenesGuardadas++;
    }



    if ($imagenesGuardadas <= 0) {


        echo json_encode([

            "estado" => false,
            "mensaje" => "No se pudo guardar ninguna imagen."

        ]);


        exit;
    }



    echo json_encode([

        "estado" => true,

        "mensaje" => "Imágenes guardadas correctamente.",

        "cantidad" => $imagenesGuardadas

    ]);
} catch (Exception $e) {


    echo json_encode([

        "estado" => false,

        "mensaje" => "Error al guardar imágenes.",

        "error" => $e->getMessage()

    ]);
}



mysqli_close($conexion);
