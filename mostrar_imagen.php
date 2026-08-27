<?php
//Toda esta parte es de mostrar_imagen.php
require_once "controladores/conexion.php";

/*
|--------------------------------------------------------------------------
| Parámetros
|--------------------------------------------------------------------------
| id   = id_producto (obligatorio)
| img  = id_imagen (opcional)
|
| Ejemplos:
| mostrar_imagen.php?id=5
| mostrar_imagen.php?id=5&img=18
|--------------------------------------------------------------------------
*/

$idProducto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idImagen   = isset($_GET['img']) ? intval($_GET['img']) : 0;

if ($idProducto <= 0) {
    http_response_code(404);
    exit();
}

if ($idImagen > 0) {

    // Mostrar una imagen específica
    $sql = "SELECT imagenes
            FROM imagenes
            WHERE id_imagen = ?
              AND idProducto = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $idImagen, $idProducto);

} else {

    // Mostrar la imagen principal (orden = 1)
    $sql = "SELECT imagenes
            FROM imagenes
            WHERE idProducto = ?
            ORDER BY orden ASC
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idProducto);

}

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $imagen = $fila['imagenes'];

    // Detectar automáticamente el tipo de imagen
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($imagen);

    if (!$mime) {
        $mime = "image/jpeg";
    }

    header("Content-Type: " . $mime);
    header("Cache-Control: public, max-age=86400");

    echo $imagen;
    exit();

}

// Si no existe imagen mostrar una imagen por defecto
$imagenDefault = "assets/img/sin_imagen.png";

if (file_exists($imagenDefault)) {

    header("Content-Type: image/png");
    readfile($imagenDefault);

} else {

    http_response_code(404);

}
?>