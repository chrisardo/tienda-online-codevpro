<?php
//=====================================================
// CoDevPro Technology
// ajax/registrar_categoria.php
//=====================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

/*=====================================================
=            VALIDAR SESIÓN
=====================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);

/*=====================================================
=            RECIBIR DATOS
=====================================================*/

$nombre = trim($_POST["nombre"] ?? "");

/*=====================================================
=            VALIDACIONES
=====================================================*/

if (empty($nombre)) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ingrese el nombre de la categoría."
    ]);

    exit;
}

if (mb_strlen($nombre) > 100) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El nombre es demasiado largo."
    ]);

    exit;
}

/*=====================================================
=            VALIDAR DUPLICADO
=====================================================*/

$sqlValidar = "
SELECT id_categorias
FROM categorias
WHERE nombre = ?
AND id_user = ?
AND Eliminado = 0
LIMIT 1
";

$stmtValidar = mysqli_prepare($conexion, $sqlValidar);

mysqli_stmt_bind_param(
    $stmtValidar,
    "si",
    $nombre,
    $idUser
);

mysqli_stmt_execute($stmtValidar);

$resultadoValidar =
    mysqli_stmt_get_result($stmtValidar);

if (mysqli_num_rows($resultadoValidar) > 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Ya existe una categoría con ese nombre."
    ]);

    exit;
}

/*=====================================================
=            IMAGEN
=====================================================*/

$imagen = null;

if (
    isset($_FILES["imagen"]) &&
    $_FILES["imagen"]["error"] === UPLOAD_ERR_OK
) {

    $permitidos = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    $tipoMime = mime_content_type(
        $_FILES["imagen"]["tmp_name"]
    );

    if (!in_array($tipoMime, $permitidos)) {

        echo json_encode([
            "estado" => false,
            "mensaje" => "Formato de imagen no permitido."
        ]);

        exit;
    }

    $maxSize = 2.7 * 1024 * 1024;

    if ($_FILES["imagen"]["size"] > $maxSize) {

        echo json_encode([
            "estado" => false,
            "mensaje" => "La imagen supera los 2.7 MB."
        ]);

        exit;
    }

    $imagen = file_get_contents(
        $_FILES["imagen"]["tmp_name"]
    );
}

/*=====================================================
=            REGISTRAR
=====================================================*/

$stmt = mysqli_prepare(
    $conexion,
    "INSERT INTO categorias(
        nombre,
        imagen,
        id_user,
        Eliminado
    )
    VALUES (?, ?, ?, 0)"
);
$null = NULL;
if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al preparar la consulta."
    ]);

    exit;
}



mysqli_stmt_bind_param(
    $stmt,
    "sbi",
    $nombre,
    $null,
    $idUser
);

$eliminado = 0;

if ($imagen !== null) {

    mysqli_stmt_send_long_data(
        $stmt,
        1,
        $imagen
    );
}

if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "estado" => true,
        "mensaje" => "Categoría registrada correctamente."
    ]);
} else {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo registrar la categoría.".mysqli_error($conexion)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
