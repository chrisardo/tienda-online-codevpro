<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/mostrar_imagen_empleado.php
// Módulo: Lista de Empleados
// Sistema: Inventa
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// ID EMPLEADO
//=====================================================

$idEmpleado = isset($_GET["id_empleado"])
    ? intval($_GET["id_empleado"])
    : 0;

//=====================================================
// VALIDAR ID
//=====================================================

if ($idEmpleado <= 0) {
    http_response_code(404);
    exit();
}

//=====================================================
// CONSULTAR IMAGEN
//=====================================================

$sql = "
    SELECT imagen
    FROM empleados
    WHERE id_empleado = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    http_response_code(500);
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idEmpleado
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

//=====================================================
// IMAGEN ENCONTRADA
//=====================================================

if ($fila = mysqli_fetch_assoc($resultado)) {

    $imagen = $fila["imagen"];

    if (!empty($imagen)) {

        //=================================================
        // DETECTAR MIME
        //=================================================

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mime = $finfo->buffer($imagen);

        if (!$mime) {
            $mime = "image/jpeg";
        }

        //=================================================
        // CABECERAS
        //=================================================

        header("Content-Type: " . $mime);

        header(
            "Cache-Control: public, max-age=86400"
        );

        echo $imagen;

        exit();
    }
}

//=====================================================
// IMAGEN POR DEFECTO
//=====================================================

$imagenDefault = "../assets/img/sin_imagen.png";

if (file_exists($imagenDefault)) {

    header("Content-Type: image/png");

    header(
        "Cache-Control: public, max-age=86400"
    );

    readfile($imagenDefault);

    exit();
}

//=====================================================
// NO EXISTE IMAGEN DEFAULT
//=====================================================

http_response_code(404);
exit();
