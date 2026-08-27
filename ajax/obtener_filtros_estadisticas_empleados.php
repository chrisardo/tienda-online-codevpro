<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_filtros_estadisticas_empleados.php
// Módulo: Estadísticas de Empleados
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// RESPUESTA JSON
//=====================================================

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

if (!$conexion) {

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo establecer la conexión con la base de datos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// EMPLEADOS
//=====================================================

$empleados = [];

$sqlEmpleados = "
    SELECT
        e.id_empleado,
        e.nombre,
        e.apellido,
        e.id_rol,
        e.estado
    FROM empleados e
    WHERE e.id_user = ?
    ORDER BY e.nombre ASC, e.apellido ASC
";

$stmtEmpleados = mysqli_prepare($conexion, $sqlEmpleados);

if ($stmtEmpleados) {

    mysqli_stmt_bind_param(
        $stmtEmpleados,
        "i",
        $idUser
    );

    mysqli_stmt_execute($stmtEmpleados);

    $resultadoEmpleados = mysqli_stmt_get_result($stmtEmpleados);

    if ($resultadoEmpleados) {

        while ($fila = mysqli_fetch_assoc($resultadoEmpleados)) {

            $empleados[] = [
                'id_empleado' => (int) $fila['id_empleado'],
                'nombre'      => $fila['nombre'],
                'apellido'    => $fila['apellido'],
                'id_rol'      => $fila['id_rol'] !== null
                    ? (int) $fila['id_rol']
                    : null,
                'estado'      => $fila['estado']
            ];
        }
    }

    mysqli_stmt_close($stmtEmpleados);
}

//=====================================================
// ROLES
//=====================================================

$roles = [];

$sqlRoles = "
    SELECT DISTINCT
        r.id_rol,
        r.nombre
    FROM rol r
    INNER JOIN empleados e
        ON e.id_rol = r.id_rol
    WHERE r.id_user = ?
    AND e.id_user = ?
    ORDER BY r.nombre ASC
";

$stmtRoles = mysqli_prepare($conexion, $sqlRoles);

if ($stmtRoles) {

    mysqli_stmt_bind_param(
        $stmtRoles,
        "ii",
        $idUser,
        $idUser
    );

    mysqli_stmt_execute($stmtRoles);

    $resultadoRoles = mysqli_stmt_get_result($stmtRoles);

    if ($resultadoRoles) {

        while ($fila = mysqli_fetch_assoc($resultadoRoles)) {

            $roles[] = [
                'id_rol' => (int) $fila['id_rol'],
                'nombre' => $fila['nombre']
            ];
        }
    }

    mysqli_stmt_close($stmtRoles);
}

//=====================================================
// RESPUESTA
//=====================================================

echo json_encode([
    'success' => true,

    'data' => [
        'empleados' => $empleados,
        'roles'     => $roles
    ]

], JSON_UNESCAPED_UNICODE);

//=====================================================
// CERRAR CONEXIÓN
//=====================================================

mysqli_close($conexion);
