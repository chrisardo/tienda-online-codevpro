<?php
//======================================================
// CoDevPro Technology
// ajax/adm_eliminar_imagen_producto.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

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

/*=============================================
VALIDAR MÉTODO
=============================================*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Método no permitido."
    ]);

    exit;
}

/*=============================================
RECIBIR DATOS
=============================================*/

$idImagen = intval($_POST["idImagen"] ?? 0);

if ($idImagen <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Imagen inválida."
    ]);

    exit;
}

try {

    /*=============================================
    VERIFICAR EXISTENCIA
    =============================================*/

    $sql = "SELECT id_imagen
            FROM imagenes
            WHERE id_imagen = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idImagen
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) <= 0) {

        echo json_encode([
            "estado" => false,
            "mensaje" => "La imagen no existe."
        ]);

        exit;
    }

    mysqli_stmt_close($stmt);

    /*=============================================
    ELIMINAR IMAGEN
    =============================================*/

    $sqlEliminar = "DELETE FROM imagenes
                    WHERE id_imagen = ?";

    $stmtEliminar = mysqli_prepare(
        $conexion,
        $sqlEliminar
    );

    mysqli_stmt_bind_param(
        $stmtEliminar,
        "i",
        $idImagen
    );

    mysqli_stmt_execute($stmtEliminar);

    mysqli_stmt_close($stmtEliminar);

    echo json_encode([
        "estado" => true,
        "mensaje" => "Imagen eliminada correctamente."
    ]);
} catch (Exception $e) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al eliminar la imagen.",
        "error" => $e->getMessage()
    ]);
}

mysqli_close($conexion);
