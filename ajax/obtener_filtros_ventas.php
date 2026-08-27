<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_filtros_ventas.php
//=========================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida"
    ]);

    exit;
}

try {

    $metodosPago = [];
    $empleados   = [];

    /*=============================================
    =            MÉTODOS DE PAGO
    =============================================*/

    $sqlMetodos = "
        SELECT
            id_metodo_pago,
            nombre
        FROM metodo_pago
        WHERE id_user = ?
        AND Eliminado = 0
        ORDER BY nombre ASC
    ";

    $stmt = mysqli_prepare($conexion, $sqlMetodos);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($fila = mysqli_fetch_assoc($result)) {

        $metodosPago[] = [
            "id"     => (int)$fila["id_metodo_pago"],
            "nombre" => $fila["nombre"]
        ];
    }

    mysqli_stmt_close($stmt);

    /*=============================================
    =            EMPLEADOS
    =============================================*/

    $sqlEmpleados = "
        SELECT
            id_empleado,
            nombre,
            apellido
        FROM empleados
        WHERE id_user = ?
        AND estado = 'activo'
        ORDER BY nombre ASC
    ";

    $stmt = mysqli_prepare($conexion, $sqlEmpleados);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($fila = mysqli_fetch_assoc($result)) {

        $empleados[] = [
            "id" => (int)$fila["id_empleado"],
            "nombre" => trim(
                $fila["nombre"] . " " . $fila["apellido"]
            )
        ];
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
        "estado" => true,
        "metodosPago" => $metodosPago,
        "empleados" => $empleados
    ]);
} catch (Exception $e) {

    echo json_encode([
        "estado" => false,
        "mensaje" => $e->getMessage()
    ]);
}
