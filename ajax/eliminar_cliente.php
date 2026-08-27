<?php
//=====================================================
// CoDevPro Technology
// ajax/eliminar_cliente.php
//=====================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser = (int) $_SESSION["idUser"];

try {

    /*=========================================
    VALIDAR ID
    =========================================*/

    $idCliente = (int)($_POST["idCliente"] ?? 0);

    if ($idCliente <= 0) {

        throw new Exception("Cliente inválido.");
    }

    /*=========================================
    VERIFICAR QUE EXISTA
    =========================================*/

    $sql = "SELECT idCliente, nombre
            FROM clientes
            WHERE idCliente = ?
            AND id_user = ?
            AND Eliminado = 0
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idCliente,
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (!mysqli_num_rows($resultado)) {

        throw new Exception("El cliente no existe.");
    }

    $cliente = mysqli_fetch_assoc($resultado);

    mysqli_stmt_close($stmt);

    /*=========================================
    VALIDAR SI TIENE PEDIDOS
    =========================================*/

    $sqlPedidos = "SELECT COUNT(*) total
                   FROM ticket_ventas
                   WHERE idCliente = ?";

    $stmtPedidos = mysqli_prepare(
        $conexion,
        $sqlPedidos
    );

    mysqli_stmt_bind_param(
        $stmtPedidos,
        "i",
        $idCliente
    );

    mysqli_stmt_execute($stmtPedidos);

    $resPedidos = mysqli_stmt_get_result(
        $stmtPedidos
    );

    $pedidos = mysqli_fetch_assoc(
        $resPedidos
    )["total"];

    mysqli_stmt_close($stmtPedidos);

    /*=========================================
    ELIMINACIÓN LÓGICA
    =========================================*/

    $sql = "UPDATE clientes
            SET Eliminado = 1
            WHERE idCliente = ?
            AND id_user = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idCliente,
        $idUser
    );

    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_error($conexion)
        );
    }

    mysqli_stmt_close($stmt);

    /*=========================================
    RESPUESTA
    =========================================*/

    $mensaje = "Cliente eliminado correctamente.";

    if ($pedidos > 0) {

        $mensaje = "Cliente enviado a papelera correctamente. Conserva {$pedidos} pedido(s) en el historial.";
    }

    echo json_encode([
        "estado" => true,
        "mensaje" => $mensaje
    ]);
} catch (Exception $e) {

    echo json_encode([
        "estado" => false,
        "mensaje" => $e->getMessage()
    ]);
}

mysqli_close($conexion);
