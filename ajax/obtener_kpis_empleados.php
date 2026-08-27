<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_kpis_empleados.php
// Módulo: KPI Lista de Empleados
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');


//=====================================================
// RESPUESTA BASE
//=====================================================

$respuesta = [
    'success' => false,
    'mensaje' => '',
    'data' => [
        'totalEmpleados'     => 0,
        'empleadosActivos'   => 0,
        'empleadosInactivos' => 0,
        'totalRoles'         => 0
    ]
];


//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;


if ($idUser <= 0) {

    $respuesta['mensaje'] = 'Sesión no válida.';

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


try {

    //=================================================
    // KPI EMPLEADOS
    //=================================================

    $sql = "
        SELECT
            COUNT(*) AS total_empleados,

            SUM(
                CASE
                    WHEN estado = 'ACTIVO'
                    THEN 1
                    ELSE 0
                END
            ) AS empleados_activos,

            SUM(
                CASE
                    WHEN estado = 'INACTIVO'
                    THEN 1
                    ELSE 0
                END
            ) AS empleados_inactivos

        FROM empleados

        WHERE id_user = ?
    ";


    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "i",
        $idUser
    );

    $stmt->execute();


    $resultado = $stmt->get_result();

    $datosEmpleados = $resultado->fetch_assoc();


    $stmt->close();


    //=================================================
    // KPI ROLES ASIGNADOS
    //=================================================

    $sqlRoles = "
        SELECT
            COUNT(DISTINCT e.id_rol) AS total_roles

        FROM empleados AS e

        INNER JOIN rol AS r
            ON r.id_rol = e.id_rol
            AND r.id_user = e.id_user

        WHERE e.id_user = ?
          AND e.id_rol IS NOT NULL
          AND e.id_rol > 0
    ";


    $stmtRoles = $conexion->prepare($sqlRoles);

    $stmtRoles->bind_param(
        "i",
        $idUser
    );

    $stmtRoles->execute();


    $resultadoRoles = $stmtRoles->get_result();

    $datosRoles = $resultadoRoles->fetch_assoc();


    $stmtRoles->close();


    //=================================================
    // CONVERTIR RESULTADOS
    //=================================================

    $totalEmpleados = (int) (
        $datosEmpleados['total_empleados'] ?? 0
    );


    $empleadosActivos = (int) (
        $datosEmpleados['empleados_activos'] ?? 0
    );


    $empleadosInactivos = (int) (
        $datosEmpleados['empleados_inactivos'] ?? 0
    );


    $totalRoles = (int) (
        $datosRoles['total_roles'] ?? 0
    );


    //=================================================
    // RESPUESTA EXITOSA
    //=================================================

    $respuesta['success'] = true;

    $respuesta['mensaje'] =
        'KPI cargados correctamente.';


    $respuesta['data'] = [

        'totalEmpleados' =>
        $totalEmpleados,

        'empleadosActivos' =>
        $empleadosActivos,

        'empleadosInactivos' =>
        $empleadosInactivos,

        'totalRoles' =>
        $totalRoles
    ];
} catch (Throwable $e) {

    //=================================================
    // ERROR
    //=================================================

    $respuesta['success'] = false;

    $respuesta['mensaje'] =
        'Error al consultar los KPI de empleados.';

    error_log(
        'KPI empleados: ' .
            $e->getMessage()
    );
}


//=====================================================
// RESPUESTA JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
