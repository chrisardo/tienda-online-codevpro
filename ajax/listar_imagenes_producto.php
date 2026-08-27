<?php
//Toda esta parte pertenece a Ajax/listar_imagenes_producto.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUser'])) {
    echo json_encode([
        "estado" => "error",
        "mensaje" => "Sesión expirada"
    ]);
    exit;
}

require_once "../controladores/conexion.php";

$idProducto = isset($_GET['idProducto'])
    ? (int)$_GET['idProducto']
    : 0;

if ($idProducto <= 0) {
    echo json_encode([
        "estado" => "error",
        "mensaje" => "Producto inválido"
    ]);
    exit;
}

/* Verificar que el producto pertenece al usuario */

$sql = "SELECT idProducto
        FROM producto
        WHERE idProducto = ?
        AND id_user = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $idProducto, $_SESSION['idUser']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {

    echo json_encode([
        "estado"=>"error",
        "mensaje"=>"Producto no encontrado"
    ]);

    exit;
}

/* Obtener imágenes */

$sql = "SELECT
            id_imagen,
            orden,
            imagenes
        FROM imagenes
        WHERE idProducto=?
        ORDER BY orden ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i",$idProducto);
$stmt->execute();

$resultado = $stmt->get_result();

$imagenes=[];

while($fila=$resultado->fetch_assoc()){

    $imagenes[]=[
        "id_imagen"=>$fila["id_imagen"],
        "orden"=>$fila["orden"],
        "imagen"=>"data:image/jpeg;base64,".base64_encode($fila["imagenes"])
    ];

}

echo json_encode([
    "estado"=>"ok",
    "imagenes"=>$imagenes
]);