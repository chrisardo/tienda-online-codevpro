<?php
//=====================================================
// CoDevPro Technology
// Archivo: mostrar_imagen_proveedor.php
// Módulo: Lista de Proveedores
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "controladores/conexion.php";

//=====================================================
// ID PROVEEDOR
//=====================================================

$idProveedor = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($idProveedor <= 0) {
    http_response_code(404);
    exit();
}

//=====================================================
// USUARIO ACTUAL
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {
    http_response_code(403);
    exit();
}

//=====================================================
// CONSULTAR IMAGEN
//=====================================================

$sql = "
    SELECT imagen
    FROM provedores
    WHERE id_provedor = ?
      AND id_user = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    http_response_code(500);
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idProveedor,
    $idUser
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    http_response_code(500);
    exit();
}

$resultado = mysqli_stmt_get_result($stmt);

$fila = $resultado
    ? mysqli_fetch_assoc($resultado)
    : null;

mysqli_stmt_close($stmt);

//=====================================================
// SI EXISTE IMAGEN
//=====================================================

if (
    $fila &&
    isset($fila['imagen']) &&
    $fila['imagen'] !== null &&
    $fila['imagen'] !== ''
) {

    $imagen = $fila['imagen'];

    //=================================================
    // DETECTAR MIME
    //=================================================

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->buffer($imagen);

    if (!$mime || strpos($mime, 'image/') !== 0) {
        $mime = 'image/png';
    }

    //=================================================
    // CABECERAS
    //=================================================

    header("Content-Type: " . $mime);

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

    header("Pragma: no-cache");

    echo $imagen;

    exit();
}

//=====================================================
// IMAGEN POR DEFECTO
//=====================================================

$imagenDefault = __DIR__ . "/img/proveedor_default.png";

if (file_exists($imagenDefault)) {

    header("Content-Type: image/png");

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

    header("Pragma: no-cache");

    readfile($imagenDefault);

    exit();
}

//=====================================================
// NO EXISTE IMAGEN POR DEFECTO
//=====================================================

http_response_code(404);

exit();
