<?php
//======================================================
// CoDevPro Technology
// ajax/actualizar_foto_perfil.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

/*======================================================
=            VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([

        "estado" => "error",

        "mensaje" => "Debe iniciar sesión."

    ]);

    exit();
}

/*======================================================
=            CONEXIÓN
======================================================*/

require_once "../controladores/conexion.php";

mysqli_report(
    MYSQLI_REPORT_ERROR |
        MYSQLI_REPORT_STRICT
);

/*======================================================
=            RESPUESTA JSON
======================================================*/

function responder($estado, $mensaje, $extra = [])
{

    echo json_encode(

        array_merge(

            [

                "estado" => $estado,

                "mensaje" => $mensaje

            ],

            $extra

        )

    );

    exit();
}

/*======================================================
=            VALIDAR ARCHIVO
======================================================*/

if (!isset($_FILES["foto"])) {

    responder(

        "error",

        "No se recibió ninguna fotografía."

    );
}

if ($_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {

    responder(

        "error",

        "Error al subir la fotografía."

    );
}

$tiposPermitidos = [

    "image/jpeg",

    "image/jpg",

    "image/png",

    "image/webp"

];

$tipo = mime_content_type($_FILES["foto"]["tmp_name"]);

if (!in_array($tipo, $tiposPermitidos)) {

    responder(

        "error",

        "Formato de imagen no permitido."

    );
}

if ($_FILES["foto"]["size"] > (3 * 1024 * 1024)) {

    responder(

        "error",

        "La fotografía supera el tamaño permitido (3 MB)."

    );
}

/*======================================================
=            VARIABLES
======================================================*/

$idCliente = intval($_SESSION["idCliente"]);

$imagen = file_get_contents(

    $_FILES["foto"]["tmp_name"]

);

/*======================================================
=            PROCESO
======================================================*/

try {

    mysqli_begin_transaction($conexion);

    $sql = "

        UPDATE clientes

        SET

            imagen=?,

            fecha_actualizado=CURDATE()

        WHERE idCliente=?

    ";

    $stmt = mysqli_prepare(

        $conexion,

        $sql

    );

    mysqli_stmt_bind_param(

        $stmt,

        "bi",

        $null,

        $idCliente

    );

    mysqli_stmt_send_long_data(

        $stmt,

        0,

        $imagen

    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    mysqli_commit($conexion);

    responder(

        "ok",

        "Fotografía actualizada correctamente."

    );
} catch (Exception $e) {

    mysqli_rollback($conexion);

    responder(

        "error",

        $e->getMessage()

    );
}

mysqli_close($conexion);
