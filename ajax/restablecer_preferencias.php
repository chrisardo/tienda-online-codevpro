<?php
//======================================================
// CoDevPro Technology
// ajax/restablecer_preferencias.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";


/*======================================================
=            VALIDAR SESIÓN
======================================================*/

if (!isset($_SESSION["idCliente"])) {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Debe iniciar sesión."
    ]);

    exit;
}


/*======================================================
=            VALIDAR MÉTODO POST
======================================================*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "estado" => "error",
        "mensaje" => "Método no permitido."
    ]);

    exit;
}


/*======================================================
=            ID CLIENTE
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];

$respuesta = false;


/*======================================================
=            VERIFICAR SI EXISTE
======================================================*/

$sql = "

SELECT id_preferencia

FROM preferencias_cliente

WHERE idCliente = ?

LIMIT 1

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idCliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$preferencia = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);



/*======================================================
=            INSERTAR SI NO EXISTE
======================================================*/

if (!$preferencia) {

    $sqlInsert = "

    INSERT INTO preferencias_cliente(

        idCliente,
        correo_promociones,
        estado_pedido,
        nuevos_productos,
        ofertas_flash,
        id_idiomas,
        id_moneda,
        id_metodo_pago,
        fecha_actualizado

    )

    VALUES(

        ?,
        1,
        1,
        1,
        1,
        NULL,
        NULL,
        NULL,
        NOW()

    )

    ";

    $stmtInsert = mysqli_prepare(
        $conexion,
        $sqlInsert
    );

    mysqli_stmt_bind_param(
        $stmtInsert,
        "i",
        $idCliente
    );

    $respuesta = mysqli_stmt_execute(
        $stmtInsert
    );

    mysqli_stmt_close($stmtInsert);
}


/*======================================================
=            ACTUALIZAR SI EXISTE
======================================================*/ else {

    $sqlUpdate = "

    UPDATE preferencias_cliente

    SET

        correo_promociones = 1,
        estado_pedido = 1,
        nuevos_productos = 1,
        ofertas_flash = 1,
        id_idiomas = NULL,
        id_moneda = NULL,
        id_metodo_pago = NULL,
        fecha_actualizado = NOW()

    WHERE idCliente = ?

    ";

    $stmtUpdate = mysqli_prepare(
        $conexion,
        $sqlUpdate
    );

    mysqli_stmt_bind_param(
        $stmtUpdate,
        "i",
        $idCliente
    );

    $respuesta = mysqli_stmt_execute(
        $stmtUpdate
    );

    mysqli_stmt_close($stmtUpdate);
}



/*======================================================
=            RESPUESTA
======================================================*/

if ($respuesta) {

    echo json_encode([

        "estado" => "ok",
        "mensaje" => "Las preferencias fueron restablecidas correctamente."

    ]);
} else {

    echo json_encode([

        "estado" => "error",
        "mensaje" => "No se pudieron restablecer las preferencias."

    ]);
}



/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);

exit;
