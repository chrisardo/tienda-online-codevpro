<?php
//=====================================================
// CoDevPro Technology
// ajax/eliminar_metodo_pago.php
//=====================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

/*=====================================================
=            VALIDAR SESIÓN
=====================================================*/

if (!isset($_SESSION["idUser"]) || empty($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La sesión ha expirado. Inicia sesión nuevamente."
    ]);

    exit;
}

$idUser = (int) $_SESSION["idUser"];


/*=====================================================
=            RECIBIR ID
=====================================================*/

$idMetodo = isset($_POST["id_metodo_pago"])
    ? (int) $_POST["id_metodo_pago"]
    : 0;


if ($idMetodo <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se recibió un método de pago válido."
    ]);

    exit;
}


/*=====================================================
=            VERIFICAR QUE EXISTA
=            Y PERTENEZCA AL USUARIO
=====================================================*/

$sqlVerificar = "
    SELECT
        id_metodo_pago,
        nombre,
        Eliminado
    FROM metodo_pago
    WHERE id_metodo_pago = ?
      AND id_user = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conexion, $sqlVerificar);

if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No fue posible preparar la consulta."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idMetodo,
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$metodo = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);


if (!$metodo) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El método de pago no existe o no pertenece a tu cuenta."
    ]);

    exit;
}


/*=====================================================
=            VALIDAR ESTADO
=====================================================*/

if ((int)$metodo["Eliminado"] === 1) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "El método de pago ya se encuentra eliminado."
    ]);

    exit;
}


/*=====================================================
=            VERIFICAR SI TIENE VENTAS
=====================================================*/

$sqlVentas = "
    SELECT COUNT(*) AS total
    FROM ticket_ventas
    WHERE id_metodo_pago = ?
";

$stmt = mysqli_prepare($conexion, $sqlVentas);

if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No fue posible verificar las ventas asociadas."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idMetodo
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$filaVentas = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

$totalVentas = (int)($filaVentas["total"] ?? 0);


/*=====================================================
=            ELIMINACIÓN LÓGICA
=====================================================*/

$sqlEliminar = "
    UPDATE metodo_pago
    SET Eliminado = 1
    WHERE id_metodo_pago = ?
      AND id_user = ?
      AND Eliminado = 0
";

$stmt = mysqli_prepare($conexion, $sqlEliminar);

if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No fue posible preparar la eliminación."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idMetodo,
    $idUser
);

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "estado" => false,
        "mensaje" => "No fue posible eliminar el método de pago."
    ]);

    exit;
}

$filasAfectadas = mysqli_stmt_affected_rows($stmt);

mysqli_stmt_close($stmt);


/*=====================================================
=            VALIDAR ACTUALIZACIÓN
=====================================================*/

if ($filasAfectadas <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "No se pudo eliminar el método de pago."
    ]);

    exit;
}


/*=====================================================
=            MENSAJE
=====================================================*/

if ($totalVentas > 0) {

    $mensaje = "El método de pago fue eliminado correctamente. "
        . "Se conservaron sus {$totalVentas} venta(s) históricas.";
} else {

    $mensaje = "El método de pago fue eliminado correctamente.";
}


/*=====================================================
=            RESPUESTA
=====================================================*/

echo json_encode([
    "estado" => true,
    "mensaje" => $mensaje,
    "id_metodo_pago" => $idMetodo,
    "ventas_asociadas" => $totalVentas
]);

mysqli_close($conexion);
