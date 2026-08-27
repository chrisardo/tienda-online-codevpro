<?php
//======================================================
// CoDevPro Technology
// ajax/guardar_preferencias.php
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
=            OBTENER ID DEL CLIENTE
======================================================*/

$idCliente = (int) $_SESSION["idCliente"];


/*======================================================
=            OBTENER LOS DATOS
======================================================*/

$correoPromociones = isset($_POST["correo_promociones"])
    ? (int) $_POST["correo_promociones"]
    : 0;

$estadoPedido = isset($_POST["estado_pedido"])
    ? (int) $_POST["estado_pedido"]
    : 0;

$nuevosProductos = isset($_POST["nuevos_productos"])
    ? (int) $_POST["nuevos_productos"]
    : 0;

$ofertasFlash = isset($_POST["ofertas_flash"])
    ? (int) $_POST["ofertas_flash"]
    : 0;

$idIdioma = !empty($_POST["id_idiomas"])
    ? (int) $_POST["id_idiomas"]
    : NULL;

$idMoneda = !empty($_POST["id_moneda"])
    ? (int) $_POST["id_moneda"]
    : NULL;
$idMetodoPago = !empty($_POST["id_metodo_pago"])
    ? (int) $_POST["id_metodo_pago"]
    : NULL;


/*======================================================
=            VERIFICAR SI EXISTEN PREFERENCIAS
======================================================*/

$sqlVerificar = "

SELECT id_preferencia

FROM preferencias_cliente

WHERE idCliente = ?

LIMIT 1

";

$stmt = mysqli_prepare($conexion, $sqlVerificar);

mysqli_stmt_bind_param(

    $stmt,
    "i",
    $idCliente

);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$existe = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);


/*======================================================
=            INSERTAR SI NO EXISTE
======================================================*/

if (!$existe) {

    $sqlInsertar = "

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
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        NOW()

    )

    ";

    $stmtInsertar = mysqli_prepare(

        $conexion,
        $sqlInsertar

    );

    mysqli_stmt_bind_param(

        $stmtInsertar,
        "iiiiiiis",

        $idCliente,
        $correoPromociones,
        $estadoPedido,
        $nuevosProductos,
        $ofertasFlash,
        $idIdioma,
        $idMoneda,
        $idMetodoPago

    );

    $respuesta = mysqli_stmt_execute($stmtInsertar);

    mysqli_stmt_close($stmtInsertar);
}


/*======================================================
=            ACTUALIZAR PREFERENCIAS
======================================================*/ else {

    $sqlActualizar = "

    UPDATE preferencias_cliente

    SET

        correo_promociones = ?,
        estado_pedido = ?,
        nuevos_productos = ?,
        ofertas_flash = ?,
        id_idiomas = ?,
        id_moneda = ?,
        id_metodo_pago = ?,
        fecha_actualizado = NOW()

    WHERE idCliente = ?

    ";

    $stmtActualizar = mysqli_prepare(

        $conexion,
        $sqlActualizar

    );

    mysqli_stmt_bind_param(

        $stmtActualizar,
        "iiiiiiii",

        $correoPromociones,
        $estadoPedido,
        $nuevosProductos,
        $ofertasFlash,
        $idIdioma,
        $idMoneda,
        $idMetodoPago,
        $idCliente

    );

    $respuesta = mysqli_stmt_execute(

        $stmtActualizar

    );

    mysqli_stmt_close($stmtActualizar);
}


/*======================================================
=            VALIDAR RESPUESTA
======================================================*/

if ($respuesta) {

    echo json_encode([

        "estado" => "ok",
        "mensaje" => "Preferencias guardadas correctamente."

    ]);
} else {

    echo json_encode([

        "estado" => "error",
        "mensaje" => "No se pudieron guardar las preferencias."

    ]);
}

/*======================================================
=            CERRAR CONEXIÓN
======================================================*/

mysqli_close($conexion);

exit;
