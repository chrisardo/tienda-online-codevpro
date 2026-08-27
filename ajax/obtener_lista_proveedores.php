<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_lista_proveedores.php
// Módulo: Lista de Proveedores
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
// FUNCIÓN RESPUESTA JSON
//=====================================================

function respuestaJSON($datos)
{
    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    respuestaJSON([
        'success' => false,
        'message' => 'Sesión no válida.'
    ]);
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !$conexion) {

    respuestaJSON([
        'success' => false,
        'message' => 'No se pudo establecer la conexión con la base de datos.'
    ]);
}

//=====================================================
// UTF-8
//=====================================================

mysqli_set_charset($conexion, "utf8mb4");

//=====================================================
// ACCIÓN
//=====================================================

$accion = isset($_GET['accion'])
    ? trim($_GET['accion'])
    : 'listar';


//#####################################################################
//#####################################################################
// ACCIÓN: LISTAR
//#####################################################################
//#####################################################################

if ($accion === 'listar') {

    //=================================================
    // PARÁMETROS
    //=================================================

    $pagina = isset($_GET['pagina'])
        ? (int) $_GET['pagina']
        : 1;

    $limite = isset($_GET['limite'])
        ? (int) $_GET['limite']
        : 10;

    $buscar = isset($_GET['buscar'])
        ? trim($_GET['buscar'])
        : '';

    $estado = isset($_GET['estado'])
        ? strtolower(trim($_GET['estado']))
        : 'todos';

    $departamento = isset($_GET['departamento'])
        ? (int) $_GET['departamento']
        : 0;

    $fecha = isset($_GET['fecha'])
        ? trim($_GET['fecha'])
        : '';

    //=================================================
    // VALIDAR PAGINACIÓN
    //=================================================

    if ($pagina < 1) {
        $pagina = 1;
    }

    if ($limite < 1) {
        $limite = 10;
    }

    if ($limite > 100) {
        $limite = 100;
    }

    //=================================================
    // VALIDAR ESTADO
    //=================================================

    $estadosPermitidos = [
        'todos',
        'activo',
        'inactivo'
    ];

    if (!in_array($estado, $estadosPermitidos, true)) {
        $estado = 'todos';
    }

    //=================================================
    // VALIDAR FECHA
    //=================================================

    $fechaValida = false;

    if ($fecha !== '') {

        $fechaObj = DateTime::createFromFormat(
            'Y-m-d',
            $fecha
        );

        $fechaValida =
            $fechaObj !== false &&
            $fechaObj->format('Y-m-d') === $fecha;
    }

    //=================================================
    // CONDICIONES
    //=================================================

    $condiciones = [];

    $condiciones[] = "p.id_user = ?";

    //=================================================
    // PARÁMETROS
    //=================================================

    $parametros = [];

    $tipos = "i";

    $parametros[] = $idUser;

    //=================================================
    // BÚSQUEDA
    //
    // nombre
    // ruc
    // celular
    // email
    //=================================================

    if ($buscar !== '') {

        $condiciones[] = "
            (
                p.nombre LIKE ?
                OR p.ruc LIKE ?
                OR CAST(p.celular AS CHAR) LIKE ?
                OR p.email LIKE ?
            )
        ";

        $buscarLike = '%' . $buscar . '%';

        $tipos .= "ssss";

        $parametros[] = $buscarLike;
        $parametros[] = $buscarLike;
        $parametros[] = $buscarLike;
        $parametros[] = $buscarLike;
    }

    //=================================================
    // FILTRO ESTADO
    //=================================================

    if ($estado === 'activo') {

        $condiciones[] = "p.Eliminado = 0";
    } elseif ($estado === 'inactivo') {

        $condiciones[] = "p.Eliminado = 1";
    }

    //=================================================
    // FILTRO DEPARTAMENTO
    //=================================================

    if ($departamento > 0) {

        $condiciones[] = "p.id_departamento = ?";

        $tipos .= "i";

        $parametros[] = $departamento;
    }

    //=================================================
    // FILTRO FECHA
    //
    // provedores.fecha_registro = DATE
    //=================================================

    if ($fechaValida) {

        $condiciones[] = "p.fecha_registro = ?";

        $tipos .= "s";

        $parametros[] = $fecha;
    }

    //=================================================
    // WHERE
    //=================================================

    $whereSQL = implode(
        " AND ",
        $condiciones
    );

    //=================================================
    // CONTAR TOTAL
    //=================================================

    $sqlTotal = "
        SELECT COUNT(*) AS total

        FROM provedores p

        WHERE $whereSQL
    ";

    $stmtTotal = mysqli_prepare(
        $conexion,
        $sqlTotal
    );

    if (!$stmtTotal) {

        respuestaJSON([
            'success' => false,
            'message' => 'No se pudo preparar la consulta para contar proveedores.',
            'error' => mysqli_error($conexion)
        ]);
    }

    //=================================================
    // BIND TOTAL
    //=================================================

    mysqli_stmt_bind_param(
        $stmtTotal,
        $tipos,
        ...$parametros
    );

    //=================================================
    // EJECUTAR TOTAL
    //=================================================

    if (!mysqli_stmt_execute($stmtTotal)) {

        $error = mysqli_stmt_error($stmtTotal);

        mysqli_stmt_close($stmtTotal);

        respuestaJSON([
            'success' => false,
            'message' => 'No se pudo obtener el total de proveedores.',
            'error' => $error
        ]);
    }

    //=================================================
    // RESULTADO TOTAL
    //=================================================

    $resultadoTotal = mysqli_stmt_get_result(
        $stmtTotal
    );

    $filaTotal = $resultadoTotal
        ? mysqli_fetch_assoc($resultadoTotal)
        : null;

    $total = isset($filaTotal['total'])
        ? (int) $filaTotal['total']
        : 0;

    mysqli_stmt_close($stmtTotal);

    //=================================================
    // CALCULAR PÁGINAS
    //=================================================

    $paginas = $total > 0
        ? (int) ceil($total / $limite)
        : 1;

    //=================================================
    // CORREGIR PÁGINA
    //=================================================

    if ($pagina > $paginas) {
        $pagina = $paginas;
    }

    //=================================================
    // OFFSET
    //=================================================

    $offset = ($pagina - 1) * $limite;

    //=================================================
    // CONSULTA DE PROVEEDORES
    //=================================================

    $sqlProveedores = "
        SELECT

            p.id_provedor,
            p.nombre,
            p.ruc,
            p.imagen,

            p.id_user,

            p.id_pais,
            p.id_departamento,
            p.id_provincia,
            p.id_distrito,

            p.direccion,
            p.email,
            p.celular,

            p.Eliminado,

            p.fecha_registro,
            p.fecha_actualizado,

            pa.nombre AS pais_nombre,
            de.nombre AS departamento_nombre,
            pv.nombre AS provincia_nombre,
            di.nombre AS distrito_nombre

        FROM provedores p

        LEFT JOIN pais pa
            ON pa.id_pais = p.id_pais
            AND pa.id_user = p.id_user
            AND pa.Eliminado = 0

        LEFT JOIN departamento de
            ON de.id_departamento = p.id_departamento
            AND de.id_user = p.id_user
            AND de.Eliminado = 0

        LEFT JOIN provincia pv
            ON pv.id_provincia = p.id_provincia
            AND pv.id_user = p.id_user
            AND pv.Eliminado = 0

        LEFT JOIN distrito di
            ON di.id_distrito = p.id_distrito
            AND di.id_user = p.id_user
            AND di.Eliminado = 0

        WHERE $whereSQL

        ORDER BY
            p.Eliminado ASC,
            p.fecha_registro DESC,
            p.nombre ASC

        LIMIT ?
        OFFSET ?
    ";

    //=================================================
    // PREPARAR
    //=================================================

    $stmtProveedores = mysqli_prepare(
        $conexion,
        $sqlProveedores
    );

    if (!$stmtProveedores) {

        respuestaJSON([
            'success' => false,
            'message' => 'No se pudo preparar la consulta de proveedores.',
            'error' => mysqli_error($conexion)
        ]);
    }

    //=================================================
    // PARÁMETROS LISTADO
    //=================================================

    $parametrosListado = $parametros;

    $parametrosListado[] = $limite;
    $parametrosListado[] = $offset;

    $tiposListado = $tipos . "ii";

    //=================================================
    // BIND
    //=================================================

    mysqli_stmt_bind_param(
        $stmtProveedores,
        $tiposListado,
        ...$parametrosListado
    );

    //=================================================
    // EJECUTAR
    //=================================================

    if (!mysqli_stmt_execute($stmtProveedores)) {

        $error = mysqli_stmt_error($stmtProveedores);

        mysqli_stmt_close($stmtProveedores);

        respuestaJSON([
            'success' => false,
            'message' => 'No se pudieron obtener los proveedores.',
            'error' => $error
        ]);
    }

    //=================================================
    // RESULTADO
    //=================================================

    $resultado = mysqli_stmt_get_result(
        $stmtProveedores
    );

    $proveedores = [];

    //=================================================
    // RECORRER PROVEEDORES
    //=================================================

    if ($resultado) {

        while ($fila = mysqli_fetch_assoc($resultado)) {

            //=========================================
            // CONVERSIÓN DE TIPOS
            //=========================================

            $fila['id_provedor'] = (int) $fila['id_provedor'];

            $fila['id_user'] = (int) $fila['id_user'];

            $fila['id_pais'] =
                $fila['id_pais'] !== null
                ? (int) $fila['id_pais']
                : null;

            $fila['id_departamento'] =
                $fila['id_departamento'] !== null
                ? (int) $fila['id_departamento']
                : null;

            $fila['id_provincia'] =
                $fila['id_provincia'] !== null
                ? (int) $fila['id_provincia']
                : null;

            $fila['id_distrito'] =
                $fila['id_distrito'] !== null
                ? (int) $fila['id_distrito']
                : null;

            $fila['celular'] =
                $fila['celular'] !== null
                ? (string) $fila['celular']
                : '';

            $fila['Eliminado'] =
                (int) $fila['Eliminado'];

            //=========================================
            // IMAGEN
            //
            // longblob puede llegar como binario.
            // Se convierte a Base64 para AJAX.
            //=========================================

            if (
                isset($fila['imagen']) &&
                $fila['imagen'] !== null &&
                $fila['imagen'] !== ''
            ) {

                $fila['imagen'] = base64_encode(
                    $fila['imagen']
                );
            } else {

                $fila['imagen'] = null;
            }

            //=========================================
            // AGREGAR
            //=========================================

            $proveedores[] = $fila;
        }
    }

    mysqli_stmt_close($stmtProveedores);

    //=================================================
    // DESDE / HASTA
    //=================================================

    if ($total > 0) {

        $desde = $offset + 1;

        $hasta = min(
            $offset + count($proveedores),
            $total
        );
    } else {

        $desde = 0;
        $hasta = 0;
    }

    //=================================================
    // RESPUESTA
    //=================================================

    respuestaJSON([

        'success' => true,

        'message' => 'Proveedores obtenidos correctamente.',

        'proveedores' => $proveedores,

        'total' => $total,

        'pagina' => $pagina,

        'limite' => $limite,

        'paginas' => $paginas,

        'desde' => $desde,

        'hasta' => $hasta

    ]);
}


//#####################################################################
//#####################################################################
// ACCIÓN: OBTENER
//#####################################################################
//#####################################################################

if ($accion === 'obtener') {

    //=================================================
    // ID PROVEEDOR
    //=================================================

    $idProveedor = isset($_GET['id_provedor'])
        ? (int) $_GET['id_provedor']
        : 0;

    if ($idProveedor <= 0) {

        respuestaJSON([
            'success' => false,
            'message' => 'ID de proveedor inválido.'
        ]);
    }

    //=================================================
    // CONSULTA
    //=================================================

    $sql = "
        SELECT

            p.id_provedor,
            p.nombre,
            p.ruc,
            p.imagen,

            p.id_user,

            p.id_pais,
            p.id_departamento,
            p.id_provincia,
            p.id_distrito,

            p.direccion,
            p.email,
            p.celular,

            p.Eliminado,

            p.fecha_registro,
            p.fecha_actualizado,

            pa.nombre AS pais_nombre,
            de.nombre AS departamento_nombre,
            pv.nombre AS provincia_nombre,
            di.nombre AS distrito_nombre

        FROM provedores p

        LEFT JOIN pais pa
            ON pa.id_pais = p.id_pais
            AND pa.id_user = p.id_user
            AND pa.Eliminado = 0

        LEFT JOIN departamento de
            ON de.id_departamento = p.id_departamento
            AND de.id_user = p.id_user
            AND de.Eliminado = 0

        LEFT JOIN provincia pv
            ON pv.id_provincia = p.id_provincia
            AND pv.id_user = p.id_user
            AND pv.Eliminado = 0

        LEFT JOIN distrito di
            ON di.id_distrito = p.id_distrito
            AND di.id_user = p.id_user
            AND di.Eliminado = 0

        WHERE
            p.id_provedor = ?
            AND p.id_user = ?

        LIMIT 1
    ";

    //=================================================
    // PREPARAR
    //=================================================

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    if (!$stmt) {

        respuestaJSON([
            'success' => false,
            'message' => 'No se pudo preparar la consulta del proveedor.',
            'error' => mysqli_error($conexion)
        ]);
    }

    //=================================================
    // BIND
    //=================================================

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idProveedor,
        $idUser
    );

    //=================================================
    // EJECUTAR
    //=================================================

    if (!mysqli_stmt_execute($stmt)) {

        $error = mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        respuestaJSON([
            'success' => false,
            'message' => 'No se pudo obtener el proveedor.',
            'error' => $error
        ]);
    }

    //=================================================
    // RESULTADO
    //=================================================

    $resultado = mysqli_stmt_get_result($stmt);

    $proveedor = $resultado
        ? mysqli_fetch_assoc($resultado)
        : null;

    mysqli_stmt_close($stmt);

    //=================================================
    // VALIDAR
    //=================================================

    if (!$proveedor) {

        respuestaJSON([
            'success' => false,
            'message' => 'El proveedor no existe o no pertenece al usuario actual.'
        ]);
    }

    //=================================================
    // CONVERSIÓN DE TIPOS
    //=================================================

    $proveedor['id_provedor'] =
        (int) $proveedor['id_provedor'];

    $proveedor['id_user'] =
        (int) $proveedor['id_user'];

    $proveedor['id_pais'] =
        $proveedor['id_pais'] !== null
        ? (int) $proveedor['id_pais']
        : null;

    $proveedor['id_departamento'] =
        $proveedor['id_departamento'] !== null
        ? (int) $proveedor['id_departamento']
        : null;

    $proveedor['id_provincia'] =
        $proveedor['id_provincia'] !== null
        ? (int) $proveedor['id_provincia']
        : null;

    $proveedor['id_distrito'] =
        $proveedor['id_distrito'] !== null
        ? (int) $proveedor['id_distrito']
        : null;

    $proveedor['celular'] =
        $proveedor['celular'] !== null
        ? (string) $proveedor['celular']
        : '';

    $proveedor['Eliminado'] =
        (int) $proveedor['Eliminado'];

    //=================================================
    // IMAGEN
    //=================================================

    if (
        isset($proveedor['imagen']) &&
        $proveedor['imagen'] !== null &&
        $proveedor['imagen'] !== ''
    ) {

        $proveedor['imagen'] = base64_encode(
            $proveedor['imagen']
        );
    } else {

        $proveedor['imagen'] = null;
    }

    //=================================================
    // RESPUESTA
    //=================================================

    respuestaJSON([

        'success' => true,

        'message' => 'Proveedor obtenido correctamente.',

        'proveedor' => $proveedor

    ]);
}


//#####################################################################
//#####################################################################
// ACCIÓN NO VÁLIDA
//#####################################################################
//#####################################################################

respuestaJSON([
    'success' => false,
    'message' => 'Acción no válida.'
]);
