<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_lista_empleados.php
// Módulo: Lista de Empleados
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
        'empleados' => [],
        'total' => 0,
        'pagina' => 1,
        'limite' => 5,
        'totalPaginas' => 1
    ]
];

//=====================================================
// ID USUARIO
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    $respuesta['mensaje'] =
        'Sesión no válida.';

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
    // RECIBIR FILTROS
    //=================================================

    $buscar = isset($_GET['buscar'])
        ? trim($_GET['buscar'])
        : '';

    $estado = isset($_GET['estado'])
        ? trim($_GET['estado'])
        : '';

    $idRol = isset($_GET['idRol'])
        ? (int) $_GET['idRol']
        : 0;

    $fechaDesde = isset($_GET['fechaDesde'])
        ? trim($_GET['fechaDesde'])
        : '';

    $fechaHasta = isset($_GET['fechaHasta'])
        ? trim($_GET['fechaHasta'])
        : '';

    //=================================================
    // PAGINACIÓN
    //=================================================

    $pagina = isset($_GET['pagina'])
        ? (int) $_GET['pagina']
        : 1;

    $limite = isset($_GET['limite'])
        ? (int) $_GET['limite']
        : 5;

    //=================================================
    // FORZAR 5 REGISTROS
    //=================================================

    $limite = 5;

    //=================================================
    // VALIDAR PÁGINA
    //=================================================

    if ($pagina < 1) {
        $pagina = 1;
    }

    //=================================================
    // CONSULTA BASE PARA CONTAR
    //=================================================

    $sqlCount = "
        SELECT COUNT(*) AS total

        FROM empleados AS e

        LEFT JOIN rol AS r
            ON r.id_rol = e.id_rol
            AND r.id_user = e.id_user

        WHERE e.id_user = ?
    ";

    $parametrosCount = [$idUser];

    $tiposCount = "i";

    //=================================================
    // FILTRO DE BÚSQUEDA
    //=================================================

    if ($buscar !== '') {

        $sqlCount .= "
            AND (
                e.nombre LIKE ?
                OR e.apellido LIKE ?

                OR CONCAT(
                    e.nombre,
                    ' ',
                    e.apellido
                ) LIKE ?

                OR e.dni LIKE ?
                OR e.email LIKE ?

                OR CAST(
                    e.celular AS CHAR
                ) LIKE ?

                OR r.nombre LIKE ?
            )
        ";

        $busquedaLike =
            '%' . $buscar . '%';

        $parametrosCount[] =
            $busquedaLike;

        $parametrosCount[] =
            $busquedaLike;

        $parametrosCount[] =
            $busquedaLike;

        $parametrosCount[] =
            $busquedaLike;

        $parametrosCount[] =
            $busquedaLike;

        $parametrosCount[] =
            $busquedaLike;

        $parametrosCount[] =
            $busquedaLike;

        $tiposCount .= "sssssss";
    }

    //=================================================
    // FILTRO ESTADO
    //=================================================

    if (
        $estado === 'ACTIVO' ||
        $estado === 'INACTIVO'
    ) {

        $sqlCount .= "
            AND e.estado = ?
        ";

        $parametrosCount[] =
            $estado;

        $tiposCount .= "s";
    }

    //=================================================
    // FILTRO ROL
    //=================================================

    if ($idRol > 0) {

        $sqlCount .= "
            AND e.id_rol = ?
        ";

        $parametrosCount[] =
            $idRol;

        $tiposCount .= "i";
    }

    //=================================================
    // FILTRO FECHA DESDE
    //=================================================

    if ($fechaDesde !== '') {

        $sqlCount .= "
            AND DATE(e.fecha_registro) >= ?
        ";

        $parametrosCount[] =
            $fechaDesde;

        $tiposCount .= "s";
    }

    //=================================================
    // FILTRO FECHA HASTA
    //=================================================

    if ($fechaHasta !== '') {

        $sqlCount .= "
            AND DATE(e.fecha_registro) <= ?
        ";

        $parametrosCount[] =
            $fechaHasta;

        $tiposCount .= "s";
    }

    //=================================================
    // PREPARAR COUNT
    //=================================================

    $stmtCount =
        $conexion->prepare(
            $sqlCount
        );

    if (!$stmtCount) {
        throw new Exception(
            'No se pudo preparar la consulta COUNT.'
        );
    }

    //=================================================
    // BIND COUNT
    //=================================================

    $bindCount = [];

    $bindCount[] =
        $tiposCount;

    foreach (
        $parametrosCount as $key => $valor
    ) {
        $bindCount[] =
            &$parametrosCount[$key];
    }

    call_user_func_array(
        [
            $stmtCount,
            'bind_param'
        ],
        $bindCount
    );

    //=================================================
    // EJECUTAR COUNT
    //=================================================

    $stmtCount->execute();

    $resultadoCount =
        $stmtCount->get_result();

    $filaCount =
        $resultadoCount->fetch_assoc();

    $stmtCount->close();

    //=================================================
    // TOTAL REGISTROS
    //=================================================

    $total =
        isset($filaCount['total'])
        ? (int) $filaCount['total']
        : 0;

    //=================================================
    // TOTAL PÁGINAS
    //=================================================

    $totalPaginas =
        $total > 0
        ? (int) ceil(
            $total / $limite
        )
        : 1;

    //=================================================
    // SI LA PÁGINA SUPERA EL TOTAL
    //=================================================

    if (
        $pagina > $totalPaginas
    ) {
        $pagina =
            $totalPaginas;
    }

    //=================================================
    // OFFSET
    //=================================================

    $offset =
        ($pagina - 1) *
        $limite;

    //=================================================
    // CONSULTA DE EMPLEADOS
    //=================================================

    $sql = "
        SELECT

            e.id_empleado,

            e.nombre,

            e.apellido,

            e.imagen,

            e.dni,

            e.celular,

            e.email,

            e.estado,

            e.fecha_registro,

            e.id_rol,

            r.nombre AS nombre_rol

        FROM empleados AS e

        LEFT JOIN rol AS r

            ON r.id_rol = e.id_rol

            AND r.id_user = e.id_user

        WHERE e.id_user = ?
    ";

    $parametros = [$idUser];

    $tipos = "i";

    //=================================================
    // FILTRO BÚSQUEDA
    //=================================================

    if ($buscar !== '') {

        $sql .= "
            AND (
                e.nombre LIKE ?
                OR e.apellido LIKE ?

                OR CONCAT(
                    e.nombre,
                    ' ',
                    e.apellido
                ) LIKE ?

                OR e.dni LIKE ?
                OR e.email LIKE ?

                OR CAST(
                    e.celular AS CHAR
                ) LIKE ?

                OR r.nombre LIKE ?
            )
        ";

        $busquedaLike =
            '%' . $buscar . '%';

        $parametros[] =
            $busquedaLike;

        $parametros[] =
            $busquedaLike;

        $parametros[] =
            $busquedaLike;

        $parametros[] =
            $busquedaLike;

        $parametros[] =
            $busquedaLike;

        $parametros[] =
            $busquedaLike;

        $parametros[] =
            $busquedaLike;

        $tipos .= "sssssss";
    }

    //=================================================
    // FILTRO ESTADO
    //=================================================

    if (
        $estado === 'ACTIVO' ||
        $estado === 'INACTIVO'
    ) {

        $sql .= "
            AND e.estado = ?
        ";

        $parametros[] =
            $estado;

        $tipos .= "s";
    }

    //=================================================
    // FILTRO ROL
    //=================================================

    if ($idRol > 0) {

        $sql .= "
            AND e.id_rol = ?
        ";

        $parametros[] =
            $idRol;

        $tipos .= "i";
    }

    //=================================================
    // FILTRO FECHA DESDE
    //=================================================

    if ($fechaDesde !== '') {

        $sql .= "
            AND DATE(e.fecha_registro) >= ?
        ";

        $parametros[] =
            $fechaDesde;

        $tipos .= "s";
    }

    //=================================================
    // FILTRO FECHA HASTA
    //=================================================

    if ($fechaHasta !== '') {

        $sql .= "
            AND DATE(e.fecha_registro) <= ?
        ";

        $parametros[] =
            $fechaHasta;

        $tipos .= "s";
    }

    //=================================================
    // ORDEN + PAGINACIÓN
    //=================================================

    $sql .= "
        ORDER BY

            e.fecha_registro DESC,

            e.apellido ASC,

            e.nombre ASC

        LIMIT ? OFFSET ?
    ";

    //=================================================
    // AGREGAR LIMIT Y OFFSET
    //=================================================

    $parametros[] =
        $limite;

    $parametros[] =
        $offset;

    $tipos .= "ii";

    //=================================================
    // PREPARAR CONSULTA
    //=================================================

    $stmt =
        $conexion->prepare(
            $sql
        );

    if (!$stmt) {
        throw new Exception(
            'No se pudo preparar la consulta de empleados.'
        );
    }

    //=================================================
    // BIND DINÁMICO
    //=================================================

    $bind = [];

    $bind[] =
        $tipos;

    foreach (
        $parametros as $key => $valor
    ) {
        $bind[] =
            &$parametros[$key];
    }

    call_user_func_array(
        [
            $stmt,
            'bind_param'
        ],
        $bind
    );

    //=================================================
    // EJECUTAR
    //=================================================

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    //=================================================
    // PROCESAR EMPLEADOS
    //=================================================

    while (
        $fila =
        $resultado->fetch_assoc()
    ) {

        $respuesta['data']['empleados'][] = [

            'id_empleado' =>
            (int)
            $fila['id_empleado'],

            'nombre' =>
            $fila['nombre'],

            'apellido' =>
            $fila['apellido'],

            'dni' =>
            $fila['dni'],

            'celular' =>
            $fila['celular'],

            'email' =>
            $fila['email'],

            'estado' =>
            $fila['estado'],

            'fecha_registro' =>
            $fila['fecha_registro'],

            'id_rol' =>
            $fila['id_rol'] !== null
                ? (int)
                $fila['id_rol']
                : 0,

            'nombre_rol' =>
            $fila['nombre_rol'] ?? ''
        ];
    }

    $stmt->close();

    //=================================================
    // RESPUESTA
    //=================================================

    $respuesta['success'] =
        true;

    $respuesta['mensaje'] =
        'Empleados cargados correctamente.';

    $respuesta['data']['total'] =
        $total;

    $respuesta['data']['pagina'] =
        $pagina;

    $respuesta['data']['limite'] =
        $limite;

    $respuesta['data']['totalPaginas'] =
        $totalPaginas;
} catch (Throwable $e) {

    //=================================================
    // ERROR
    //=================================================

    $respuesta['success'] =
        false;

    $respuesta['mensaje'] =
        'Error al obtener los empleados.';

    error_log(
        'Error lista empleados: ' .
            $e->getMessage()
    );
}

//=====================================================
// JSON
//=====================================================

echo json_encode(
    $respuesta,
    JSON_UNESCAPED_UNICODE
);

exit;
