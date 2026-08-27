<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_estadisticas_proveedores.php
// Módulo: Estadísticas de Proveedores
// Sistema: Inventa
//=====================================================

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// CONFIGURACIÓN DE RESPUESTA
//=====================================================

header('Content-Type: application/json; charset=utf-8');


//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(array $respuesta): void
{
    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_NUMERIC_CHECK
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

    responderJSON([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ]);
}


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {

    responderJSON([
        'success' => false,
        'mensaje' => 'No se pudo establecer la conexión con la base de datos.'
    ]);
}

$conexion->set_charset("utf8mb4");


//=====================================================
// ACCIÓN
//=====================================================

$accion = isset($_GET['accion'])
    ? trim((string) $_GET['accion'])
    : 'estadisticas';


//=====================================================
// PARÁMETROS
//=====================================================

$idProveedor = isset($_GET['id_provedor'])
    ? trim((string) $_GET['id_provedor'])
    : '';

$fechaInicio = isset($_GET['fecha_inicio'])
    ? trim((string) $_GET['fecha_inicio'])
    : '';

$fechaFin = isset($_GET['fecha_fin'])
    ? trim((string) $_GET['fecha_fin'])
    : '';

$estado = isset($_GET['estado'])
    ? strtoupper(trim((string) $_GET['estado']))
    : 'TODOS';


//=====================================================
// NORMALIZAR ESTADO
//=====================================================

if (!in_array($estado, ['TODOS', 'ACTIVO', 'INACTIVO'], true)) {
    $estado = 'TODOS';
}


//=====================================================
// VALIDAR ID PROVEEDOR
//=====================================================

if ($idProveedor !== '' && !ctype_digit($idProveedor)) {

    responderJSON([
        'success' => false,
        'mensaje' => 'El proveedor seleccionado no es válido.'
    ]);
}

$idProveedorInt = $idProveedor !== ''
    ? (int) $idProveedor
    : 0;


//=====================================================
// VALIDAR FECHAS
//=====================================================

function validarFecha(string $fecha): bool
{
    if ($fecha === '') {
        return true;
    }

    $objeto = DateTime::createFromFormat('Y-m-d', $fecha);

    return $objeto !== false &&
        $objeto->format('Y-m-d') === $fecha;
}


if (!validarFecha($fechaInicio)) {

    responderJSON([
        'success' => false,
        'mensaje' => 'La fecha de inicio no es válida.'
    ]);
}


if (!validarFecha($fechaFin)) {

    responderJSON([
        'success' => false,
        'mensaje' => 'La fecha de fin no es válida.'
    ]);
}


if (
    $fechaInicio !== '' &&
    $fechaFin !== '' &&
    $fechaInicio > $fechaFin
) {

    responderJSON([
        'success' => false,
        'mensaje' => 'La fecha de inicio no puede ser mayor que la fecha de fin.'
    ]);
}


//=====================================================
// FUNCIÓN PARA EJECUTAR CONSULTAS
//=====================================================

function ejecutarConsulta(
    mysqli $conexion,
    string $sql,
    string $tipos = '',
    array $parametros = []
): mysqli_result {

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            'Error preparando consulta: ' . $conexion->error
        );
    }

    if ($tipos !== '' && count($parametros) > 0) {

        $referencias = [];

        $referencias[] = $tipos;

        foreach ($parametros as $key => $valor) {
            $referencias[] = &$parametros[$key];
        }

        call_user_func_array(
            [$stmt, 'bind_param'],
            $referencias
        );
    }

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        throw new Exception(
            'Error ejecutando consulta: ' . $error
        );
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {

        $stmt->close();

        throw new Exception(
            'No se pudo obtener el resultado de la consulta.'
        );
    }

    /*
     * El result mantiene internamente la referencia al statement
     * mientras sea utilizado.
     */

    return $resultado;
}


//=====================================================
// FUNCIÓN OBTENER FILTRO DE PROVEEDOR
//=====================================================

function obtenerCondicionProveedor(
    string $aliasProveedor,
    int $idProveedor
): array {

    if ($idProveedor <= 0) {
        return [
            'sql' => '',
            'tipos' => '',
            'params' => []
        ];
    }

    return [
        'sql' => " AND {$aliasProveedor}.id_provedor = ? ",
        'tipos' => 'i',
        'params' => [$idProveedor]
    ];
}


//=====================================================
// FUNCIÓN CONDICIÓN ESTADO PROVEEDOR
//=====================================================

function obtenerCondicionEstadoProveedor(
    string $aliasProveedor,
    string $estado
): array {

    if ($estado === 'ACTIVO') {

        return [
            'sql' => " AND {$aliasProveedor}.Eliminado = 0 ",
            'tipos' => '',
            'params' => []
        ];
    }

    if ($estado === 'INACTIVO') {

        return [
            'sql' => " AND {$aliasProveedor}.Eliminado = 1 ",
            'tipos' => '',
            'params' => []
        ];
    }

    return [
        'sql' => '',
        'tipos' => '',
        'params' => []
    ];
}


//=====================================================
// ACCIÓN: PROVEEDORES
//=====================================================

if ($accion === 'proveedores') {

    try {

        $sql = "
            SELECT
                p.id_provedor,
                p.nombre

            FROM provedores p

            WHERE
                p.id_user = ?
                AND p.Eliminado = 0

            ORDER BY
                p.nombre ASC
        ";

        $resultado = ejecutarConsulta(
            $conexion,
            $sql,
            'i',
            [$idUser]
        );

        $proveedores = [];

        while ($fila = $resultado->fetch_assoc()) {

            $proveedores[] = [
                'id_provedor' => (int) $fila['id_provedor'],
                'nombre' => $fila['nombre']
            ];
        }

        responderJSON([
            'success' => true,
            'proveedores' => $proveedores
        ]);
    } catch (Throwable $e) {

        error_log(
            'Error obtener proveedores estadísticas: ' .
                $e->getMessage()
        );

        responderJSON([
            'success' => false,
            'mensaje' => 'No se pudo cargar la lista de proveedores.'
        ]);
    }
}


//=====================================================
// ACCIÓN: ESTADÍSTICAS
//=====================================================

if ($accion !== 'estadisticas') {

    if ($accion === 'exportar') {

        responderJSON([
            'success' => false,
            'mensaje' => 'La exportación todavía no está habilitada.'
        ]);
    }

    responderJSON([
        'success' => false,
        'mensaje' => 'Acción no válida.'
    ]);
}


//=====================================================
// FILTROS GENERALES
//=====================================================

$filtroProveedor = obtenerCondicionProveedor(
    'pr',
    $idProveedorInt
);

$filtroEstado = obtenerCondicionEstadoProveedor(
    'pr',
    $estado
);


//=====================================================
// FILTROS PARA VENTAS
//=====================================================

$condicionesVentas = "
    t.id_user = ?
    AND d.id_user = ?
    AND pr.id_user = ?
    AND pr.Eliminado = 0
";

$tiposVentasBase = 'iii';

$paramsVentasBase = [
    $idUser,
    $idUser,
    $idUser
];


//=====================================================
// PROVEEDOR SELECCIONADO
//=====================================================

if ($idProveedorInt > 0) {

    $condicionesVentas .= "
        AND pr.id_provedor = ?
    ";

    $tiposVentasBase .= 'i';

    $paramsVentasBase[] = $idProveedorInt;
}


//=====================================================
// ESTADO DEL PROVEEDOR
//=====================================================

if ($estado === 'INACTIVO') {

    /*
     * Los proveedores eliminados no participan en estadísticas
     * de ventas/productos.
     *
     * Se mantiene la condición Eliminado = 0 para evitar
     * mostrar productos históricos de registros eliminados.
     */

    $condicionesVentas .= "
        AND pr.Eliminado = 0
    ";
}


//=====================================================
// FECHA INICIO
//=====================================================

if ($fechaInicio !== '') {

    $condicionesVentas .= "
        AND t.fecha_venta >= ?
    ";

    $tiposVentasBase .= 's';

    $paramsVentasBase[] = $fechaInicio;
}


//=====================================================
// FECHA FIN
//=====================================================

if ($fechaFin !== '') {

    $condicionesVentas .= "
        AND t.fecha_venta <= ?
    ";

    $tiposVentasBase .= 's';

    $paramsVentasBase[] = $fechaFin;
}


//=====================================================
// EXCLUIR VENTAS CANCELADAS
//=====================================================

$condicionesVentas .= "
    AND (
        t.estado_envio IS NULL
        OR t.estado_envio <> 'CANCELADO'
    )
";


//=====================================================
// FILTROS PARA PRODUCTOS / INVENTARIO
//=====================================================

$condicionesProductos = "
    p.id_user = ?
    AND pr.id_user = ?
    AND p.Eliminado = 0
    AND pr.Eliminado = 0
";

$tiposProductos = 'ii';

$paramsProductos = [
    $idUser,
    $idUser
];


//=====================================================
// PROVEEDOR
//=====================================================

if ($idProveedorInt > 0) {

    $condicionesProductos .= "
        AND pr.id_provedor = ?
    ";

    $tiposProductos .= 'i';

    $paramsProductos[] = $idProveedorInt;
}


//=====================================================
// ESTADO
//=====================================================

if ($estado === 'INACTIVO') {

    /*
     * Un proveedor marcado como eliminado no debe mostrarse
     * como proveedor activo de productos.
     *
     * Para mantener consistencia con el sistema:
     * no se incluyen productos de proveedores eliminados.
     */

    $condicionesProductos .= "
        AND pr.Eliminado = 0
    ";
}


//=====================================================
// FILTRO ESTADO PARA PROVEEDORES
//=====================================================

$condicionesProveedores = "
    pr.id_user = ?
";

$tiposProveedores = 'i';

$paramsProveedores = [
    $idUser
];

if ($estado === 'ACTIVO') {

    $condicionesProveedores .= "
        AND pr.Eliminado = 0
    ";
}

if ($estado === 'INACTIVO') {

    $condicionesProveedores .= "
        AND pr.Eliminado = 1
    ";
}

if ($idProveedorInt > 0) {

    $condicionesProveedores .= "
        AND pr.id_provedor = ?
    ";

    $tiposProveedores .= 'i';

    $paramsProveedores[] = $idProveedorInt;
}


//=====================================================
// INICIO TRANSACCIÓN DE LECTURA
//=====================================================

try {

    //=================================================
    // KPI 1 - TOTAL PROVEEDORES
    //=================================================

    $sql = "
        SELECT
            COUNT(*) AS total_proveedores

        FROM provedores pr

        WHERE
            {$condicionesProveedores}
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposProveedores,
        $paramsProveedores
    );

    $fila = $resultado->fetch_assoc();

    $totalProveedores = (int) ($fila['total_proveedores'] ?? 0);


    //=================================================
    // KPI 2 - PROVEEDORES ACTIVOS
    //=================================================

    $sql = "
        SELECT
            COUNT(*) AS proveedores_activos

        FROM provedores pr

        WHERE
            pr.id_user = ?
            AND pr.Eliminado = 0
    ";

    $tipos = 'i';

    $params = [$idUser];

    if ($idProveedorInt > 0) {

        $sql .= "
            AND pr.id_provedor = ?
        ";

        $tipos .= 'i';

        $params[] = $idProveedorInt;
    }

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tipos,
        $params
    );

    $fila = $resultado->fetch_assoc();

    $proveedoresActivos = (int) (
        $fila['proveedores_activos'] ?? 0
    );


    //=================================================
    // KPI 3 - PRODUCTOS ASOCIADOS
    //=================================================

    $sql = "
        SELECT
            COUNT(DISTINCT p.idProducto) AS productos_asociados

        FROM producto p

        INNER JOIN provedores pr
            ON pr.id_provedor = p.id_provedor

        WHERE
            {$condicionesProductos}
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposProductos,
        $paramsProductos
    );

    $fila = $resultado->fetch_assoc();

    $productosAsociados = (int) (
        $fila['productos_asociados'] ?? 0
    );


    //=================================================
    // KPI 4 - VALOR INVENTARIO
    //=================================================

    $sql = "
        SELECT
            COALESCE(
                SUM(
                    COALESCE(p.stock, 0) *
                    COALESCE(p.costo_compra, 0)
                ),
                0
            ) AS valor_inventario

        FROM producto p

        INNER JOIN provedores pr
            ON pr.id_provedor = p.id_provedor

        WHERE
            {$condicionesProductos}
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposProductos,
        $paramsProductos
    );

    $fila = $resultado->fetch_assoc();

    $valorInventario = (float) (
        $fila['valor_inventario'] ?? 0
    );


    //=================================================
    // KPI 5 - UNIDADES VENDIDAS
    //=================================================

    $sql = "
        SELECT
            COALESCE(
                SUM(d.cantidad_pedido_producto),
                0
            ) AS unidades_vendidas

        FROM detalle_ticket_ventas d

        INNER JOIN ticket_ventas t
            ON t.id_ticket_ventas = d.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto = d.idProducto

        INNER JOIN provedores pr
            ON pr.id_provedor = p.id_provedor

        WHERE
            {$condicionesVentas}
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposVentasBase,
        $paramsVentasBase
    );

    $fila = $resultado->fetch_assoc();

    $unidadesVendidas = (int) (
        $fila['unidades_vendidas'] ?? 0
    );


    //=================================================
    // KPI 6 - VENTAS GENERADAS
    //=================================================

    $sql = "
        SELECT
            COALESCE(
                SUM(d.sub_total),
                0
            ) AS ventas_generadas

        FROM detalle_ticket_ventas d

        INNER JOIN ticket_ventas t
            ON t.id_ticket_ventas = d.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto = d.idProducto

        INNER JOIN provedores pr
            ON pr.id_provedor = p.id_provedor

        WHERE
            {$condicionesVentas}
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposVentasBase,
        $paramsVentasBase
    );

    $fila = $resultado->fetch_assoc();

    $ventasGeneradas = (float) (
        $fila['ventas_generadas'] ?? 0
    );


    //=================================================
    // KPI 7 - COSTO PRODUCTOS VENDIDOS
    //=================================================

    $sql = "
        SELECT
            COALESCE(
                SUM(
                    d.cantidad_pedido_producto *
                    COALESCE(p.costo_compra, 0)
                ),
                0
            ) AS costo_productos_vendidos

        FROM detalle_ticket_ventas d

        INNER JOIN ticket_ventas t
            ON t.id_ticket_ventas = d.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto = d.idProducto

        INNER JOIN provedores pr
            ON pr.id_provedor = p.id_provedor

        WHERE
            {$condicionesVentas}
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposVentasBase,
        $paramsVentasBase
    );

    $fila = $resultado->fetch_assoc();

    $costoProductosVendidos = (float) (
        $fila['costo_productos_vendidos'] ?? 0
    );


    //=================================================
    // KPI 8 - MARGEN
    //=================================================

    $margenGenerado =
        $ventasGeneradas -
        $costoProductosVendidos;


    //=================================================
    // GRÁFICO 1
    // VENTAS POR PROVEEDOR
    //=================================================

    /*
     * Aquí no usamos el proveedor seleccionado como filtro
     * obligatorio porque si el usuario selecciona un proveedor,
     * el gráfico mostrará únicamente ese proveedor.
     */

    $sql = "
        SELECT
            pr.nombre AS proveedor,

            COALESCE(
                SUM(d.sub_total),
                0
            ) AS ventas

        FROM detalle_ticket_ventas d

        INNER JOIN ticket_ventas t
            ON t.id_ticket_ventas = d.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto = d.idProducto

        INNER JOIN provedores pr
            ON pr.id_provedor = p.id_provedor

        WHERE
            {$condicionesVentas}

        GROUP BY
            pr.id_provedor,
            pr.nombre

        ORDER BY
            ventas DESC

        LIMIT 20
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposVentasBase,
        $paramsVentasBase
    );

    $graficoVentasEtiquetas = [];
    $graficoVentasValores = [];

    while ($fila = $resultado->fetch_assoc()) {

        $graficoVentasEtiquetas[] = $fila['proveedor'];

        $graficoVentasValores[] = (float) $fila['ventas'];
    }


    //=================================================
    // GRÁFICO 2
    // DISTRIBUCIÓN DE VENTAS
    //=================================================

    /*
     * Utilizamos los mismos datos del gráfico anterior.
     */

    $graficoDistribucionEtiquetas =
        $graficoVentasEtiquetas;

    $graficoDistribucionValores =
        $graficoVentasValores;


    //=================================================
    // GRÁFICO 3
    // EVOLUCIÓN DE VENTAS
    //=================================================

    $sql = "
        SELECT
            t.fecha_venta AS fecha,

            COALESCE(
                SUM(d.sub_total),
                0
            ) AS ventas

        FROM detalle_ticket_ventas d

        INNER JOIN ticket_ventas t
            ON t.id_ticket_ventas = d.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto = d.idProducto

        INNER JOIN provedores pr
            ON pr.id_provedor = p.id_provedor

        WHERE
            {$condicionesVentas}

        GROUP BY
            t.fecha_venta

        ORDER BY
            t.fecha_venta ASC
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposVentasBase,
        $paramsVentasBase
    );

    $graficoEvolucionEtiquetas = [];
    $graficoEvolucionValores = [];

    while ($fila = $resultado->fetch_assoc()) {

        $graficoEvolucionEtiquetas[] =
            $fila['fecha'];

        $graficoEvolucionValores[] =
            (float) $fila['ventas'];
    }


    //=================================================
    // RANKING DE PROVEEDORES
    //=================================================

    $sql = "
        SELECT
            pr.id_provedor,
            pr.nombre,

            COUNT(
                DISTINCT p.idProducto
            ) AS productos,

            COALESCE(
                SUM(d.sub_total),
                0
            ) AS ventas

        FROM provedores pr

        LEFT JOIN producto p
            ON p.id_provedor = pr.id_provedor
            AND p.id_user = ?
            AND p.Eliminado = 0

        LEFT JOIN detalle_ticket_ventas d
            ON d.idProducto = p.idProducto
            AND d.id_user = ?

        LEFT JOIN ticket_ventas t
            ON t.id_ticket_ventas = d.id_ticket_ventas
            AND t.id_user = ?

        WHERE
            pr.id_user = ?
            AND pr.Eliminado = 0
    ";

    $tiposRanking = 'iiii';

    $paramsRanking = [
        $idUser,
        $idUser,
        $idUser,
        $idUser
    ];


    //=================================================
    // RANKING - PROVEEDOR
    //=================================================

    if ($idProveedorInt > 0) {

        $sql .= "
            AND pr.id_provedor = ?
        ";

        $tiposRanking .= 'i';

        $paramsRanking[] = $idProveedorInt;
    }


    //=================================================
    // RANKING - FECHA
    //=================================================

    if ($fechaInicio !== '') {

        $sql .= "
            AND (
                t.fecha_venta >= ?
                OR t.fecha_venta IS NULL
            )
        ";

        $tiposRanking .= 's';

        $paramsRanking[] = $fechaInicio;
    }


    if ($fechaFin !== '') {

        $sql .= "
            AND (
                t.fecha_venta <= ?
                OR t.fecha_venta IS NULL
            )
        ";

        $tiposRanking .= 's';

        $paramsRanking[] = $fechaFin;
    }


    $sql .= "
        GROUP BY
            pr.id_provedor,
            pr.nombre

        ORDER BY
            ventas DESC,
            pr.nombre ASC

        LIMIT 10
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposRanking,
        $paramsRanking
    );

    $rankingProveedores = [];

    while ($fila = $resultado->fetch_assoc()) {

        $rankingProveedores[] = [
            'id_provedor' => (int) $fila['id_provedor'],
            'nombre' => $fila['nombre'],
            'productos' => (int) $fila['productos'],
            'ventas' => (float) $fila['ventas']
        ];
    }


    //=================================================
    // PRODUCTOS MÁS VENDIDOS
    //=================================================

    $sql = "
        SELECT
            p.idProducto,
            p.nombre,

            pr.nombre AS proveedor,

            COALESCE(
                SUM(d.cantidad_pedido_producto),
                0
            ) AS unidades,

            COALESCE(
                SUM(d.sub_total),
                0
            ) AS ventas

        FROM detalle_ticket_ventas d

        INNER JOIN ticket_ventas t
            ON t.id_ticket_ventas = d.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto = d.idProducto

        INNER JOIN provedores pr
            ON pr.id_provedor = p.id_provedor

        WHERE
            {$condicionesVentas}

        GROUP BY
            p.idProducto,
            p.nombre,
            pr.nombre

        ORDER BY
            unidades DESC,
            ventas DESC

        LIMIT 10
    ";

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposVentasBase,
        $paramsVentasBase
    );

    $productosMasVendidos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $productosMasVendidos[] = [
            'idProducto' => (int) $fila['idProducto'],
            'nombre' => $fila['nombre'],
            'proveedor' => $fila['proveedor'],
            'unidades' => (int) $fila['unidades'],
            'ventas' => (float) $fila['ventas']
        ];
    }


    //=====================================================
    // GASTOS RELACIONADOS CON PROVEEDORES
    //=====================================================

    $condicionesGastos = "
    g.id_user = ?
    AND g.Eliminado = 0
";

    $tiposGastos = 'i';

    $paramsGastos = [
        $idUser
    ];


    //=====================================================
    // FILTRO POR PROVEEDOR
    //=====================================================

    if ($idProveedorInt > 0) {

        $condicionesGastos .= "
        AND g.id_proveedor = ?
    ";

        $tiposGastos .= 'i';

        $paramsGastos[] = $idProveedorInt;
    }


    //=====================================================
    // FILTRO POR ESTADO DEL PROVEEDOR
    //=====================================================

    if ($estado === 'ACTIVO') {

        $condicionesGastos .= "
        AND (
            pr.Eliminado = 0
            OR pr.Eliminado IS NULL
        )
    ";
    }

    if ($estado === 'INACTIVO') {

        $condicionesGastos .= "
        AND pr.Eliminado = 1
    ";
    }


    //=====================================================
    // FILTRO FECHA INICIO
    //=====================================================

    if ($fechaInicio !== '') {

        $condicionesGastos .= "
        AND g.fecha >= ?
    ";

        $tiposGastos .= 's';

        $paramsGastos[] = $fechaInicio;
    }


    //=====================================================
    // FILTRO FECHA FIN
    //=====================================================

    if ($fechaFin !== '') {

        $condicionesGastos .= "
        AND g.fecha <= ?
    ";

        $tiposGastos .= 's';

        $paramsGastos[] = $fechaFin;
    }


    //=====================================================
    // OBTENER GASTOS
    //=====================================================

    $sql = "
    SELECT

        g.id_deposito,

        g.fecha,

        g.id_proveedor,

        COALESCE(
            pr.nombre,
            'Proveedor no disponible'
        ) AS proveedor,

        g.concepto,

        COALESCE(
            mp.nombre,
            '-'
        ) AS metodo_pago,

        g.tipo,

        COALESCE(
            g.monto_pago,
            0
        ) AS monto

    FROM deposito_gasto g

    LEFT JOIN provedores pr
        ON pr.id_provedor = g.id_proveedor

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago = g.id_metodo_pago
        AND mp.id_user = g.id_user

    WHERE
        {$condicionesGastos}

    ORDER BY
        g.fecha DESC,
        g.id_deposito DESC

    LIMIT 100
";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposGastos,
        $paramsGastos
    );


    $gastos = [];


    while ($fila = $resultado->fetch_assoc()) {

        $gastos[] = [

            'id_deposito' =>
            (int) ($fila['id_deposito'] ?? 0),

            'fecha' =>
            $fila['fecha'] ?? '',

            'id_proveedor' =>
            (int) ($fila['id_proveedor'] ?? 0),

            'proveedor' =>
            $fila['proveedor'] ?? 'Proveedor no disponible',

            'concepto' =>
            $fila['concepto'] ?? '-',

            'metodo_pago' =>
            $fila['metodo_pago'] ?? '-',

            'tipo' =>
            $fila['tipo'] ?? '-',

            'monto' =>
            (float) ($fila['monto'] ?? 0)
        ];
    }


    //=====================================================
    // TOTAL DE GASTOS
    //=====================================================

    $sql = "
    SELECT

        COALESCE(
            SUM(g.monto_pago),
            0
        ) AS total_gastos

    FROM deposito_gasto g

    LEFT JOIN provedores pr
        ON pr.id_provedor = g.id_proveedor

    WHERE
        {$condicionesGastos}
";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $tiposGastos,
        $paramsGastos
    );


    $fila = $resultado->fetch_assoc();


    $totalGastos = (float) (
        $fila['total_gastos'] ?? 0
    );


    //=================================================
    // RESPUESTA FINAL
    //=================================================

    responderJSON([
        'success' => true,

        'kpi' => [

            'total_proveedores' =>
            $totalProveedores,

            'proveedores_activos' =>
            $proveedoresActivos,

            'productos_asociados' =>
            $productosAsociados,

            'valor_inventario' =>
            round($valorInventario, 2),

            'unidades_vendidas' =>
            $unidadesVendidas,

            'ventas_generadas' =>
            round($ventasGeneradas, 2),

            'costo_productos_vendidos' =>
            round($costoProductosVendidos, 2),

            'margen_generado' =>
            round($margenGenerado, 2)
        ],


        'grafico_ventas' => [

            'etiquetas' =>
            $graficoVentasEtiquetas,

            'valores' =>
            $graficoVentasValores
        ],


        'grafico_distribucion' => [

            'etiquetas' =>
            $graficoDistribucionEtiquetas,

            'valores' =>
            $graficoDistribucionValores
        ],


        'grafico_evolucion' => [

            'etiquetas' =>
            $graficoEvolucionEtiquetas,

            'valores' =>
            $graficoEvolucionValores
        ],


        'ranking_proveedores' =>
        $rankingProveedores,


        'productos_mas_vendidos' =>
        $productosMasVendidos,


        'gastos' =>
        $gastos,


        'total_gastos' =>
        round($totalGastos, 2)
    ]);
} catch (Throwable $e) {

    //=================================================
    // REGISTRAR ERROR
    //=================================================

    error_log(
        'ERROR ESTADÍSTICAS PROVEEDORES: ' .
            $e->getMessage()
    );


    //=================================================
    // RESPUESTA DE ERROR
    //=================================================

    responderJSON([
        'success' => false,
        'mensaje' =>
        'Ocurrió un error al obtener las estadísticas de proveedores.'
    ]);
}
