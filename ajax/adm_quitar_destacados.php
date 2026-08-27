<?php
//======================================================
// CoDevPro Technology
// ajax/adm_quitar_destacados.php
//======================================================

session_start();

header(
    "Content-Type: application/json; charset=utf-8"
);

require_once "../controladores/conexion.php";

/*=============================================
VALIDAR SESIÓN
=============================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([

        "estado" => false,

        "mensaje" => "Sesión no válida."

    ]);

    exit;
}

$idUser = intval(
    $_SESSION["idUser"]
);

/*=============================================
RECIBIR DATOS
=============================================*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$ids = $data["ids"] ?? [];

if (empty($ids)) {

    echo json_encode([

        "estado" => false,

        "mensaje" => "No se seleccionaron productos."

    ]);

    exit;
}

/*=============================================
ACTUALIZAR
=============================================*/

$actualizados = 0;

foreach ($ids as $idProducto) {

    $idProducto = intval(
        $idProducto
    );

    $sql = "
    UPDATE producto
    SET destacado = 0
    WHERE idProducto = '$idProducto'
    AND id_user = '$idUser'
    ";

    if (
        mysqli_query(
            $conexion,
            $sql
        )
    ) {

        $actualizados++;
    }
}

/*=============================================
RESPUESTA
=============================================*/

echo json_encode([

    "estado" => true,

    "mensaje" =>
    "Se quitaron {$actualizados} producto(s) destacados."

]);

mysqli_close($conexion);
