<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_estadisticas_ventas.php
// Módulo: Estadísticas de Ventas
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// CONFIGURACIÓN
//=====================================================

header("Content-Type: application/json; charset=UTF-8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {
    responderError("No se pudo establecer la conexión con la base de datos.");
}

$conexion->set_charset("utf8mb4");

//=====================================================
// OBTENER USUARIO ACTUAL
//=====================================================

$idUser = obtenerIdUsuarioActual();

if ($idUser <= 0) {
    responderError("La sesión de usuario no es válida.");
}

//=====================================================
// ACCIÓN
//=====================================================

$accion = isset($_POST["accion"])
    ? trim((string) $_POST["accion"])
    : "";

switch ($accion) {

    case "cargar_filtros":

        cargarFiltros($conexion, $idUser);

        break;

    case "obtener_estadisticas":

        obtenerEstadisticas($conexion, $idUser);

        break;

    default:

        responderError("Acción no válida.");

        break;
}


//=====================================================
// OBTENER ID DEL USUARIO
//=====================================================

function obtenerIdUsuarioActual()
{
    /*
     * Se contemplan los nombres de sesión utilizados
     * habitualmente en el sistema.
     */

    $posiblesSesiones = [
        "id_user",
        "idUser",
        "id_usuario",
        "idUsuario"
    ];

    foreach ($posiblesSesiones as $nombreSesion) {

        if (isset($_SESSION[$nombreSesion])) {

            $valor = (int) $_SESSION[$nombreSesion];

            if ($valor > 0) {
                return $valor;
            }
        }
    }

    /*
     * En algunos módulos el ID puede venir dentro
     * de una estructura de usuario.
     */

    if (
        isset($_SESSION["usuario"]) &&
        is_array($_SESSION["usuario"])
    ) {

        $posiblesCampos = [
            "id_user",
            "idUser",
            "id_usuario",
            "idUsuario"
        ];

        foreach ($posiblesCampos as $campo) {

            if (isset($_SESSION["usuario"][$campo])) {

                $valor = (int) $_SESSION["usuario"][$campo];

                if ($valor > 0) {
                    return $valor;
                }
            }
        }
    }

    return 0;
}


//=====================================================
// CARGAR FILTROS INICIALES
//=====================================================

function cargarFiltros($conexion, $idUser)
{
    try {

        $respuesta = [
            "success" => true,

            "filtros" => [
                "sucursales" => [],
                "metodosPago" => [],
                "empleados" => [],
                "clientes" => [],
                "categorias" => []
            ]
        ];


        //=================================================
        // SUCURSALES
        //=================================================

        $sql = "
            SELECT
                id_sucursal,
                nombre
            FROM sucursal
            WHERE id_user = ?
              AND COALESCE(Eliminado, 0) = 0
            ORDER BY nombre ASC
        ";

        $respuesta["filtros"]["sucursales"] =
            ejecutarLista(
                $conexion,
                $sql,
                [$idUser],
                "i"
            );


        //=================================================
        // MÉTODOS DE PAGO
        //=================================================

        $sql = "
            SELECT
                id_metodo_pago,
                nombre
            FROM metodo_pago
            WHERE id_user = ?
              AND COALESCE(Eliminado, 0) = 0
            ORDER BY nombre ASC
        ";

        $respuesta["filtros"]["metodosPago"] =
            ejecutarLista(
                $conexion,
                $sql,
                [$idUser],
                "i"
            );


        //=================================================
        // EMPLEADOS
        //=================================================

        $sql = "
            SELECT
                id_empleado,
                CONCAT(
                    COALESCE(nombre, ''),
                    CASE
                        WHEN apellido IS NOT NULL
                             AND apellido <> ''
                        THEN CONCAT(' ', apellido)
                        ELSE ''
                    END
                ) AS nombre
            FROM empleados
            WHERE id_user = ?
            ORDER BY nombre ASC
        ";

        $respuesta["filtros"]["empleados"] =
            ejecutarLista(
                $conexion,
                $sql,
                [$idUser],
                "i"
            );


        //=================================================
        // CLIENTES
        //=================================================

        $sql = "
            SELECT
                idCliente,
                nombre
            FROM clientes
            WHERE id_user = ?
              AND COALESCE(Eliminado, 0) = 0
            ORDER BY nombre ASC
        ";

        $respuesta["filtros"]["clientes"] =
            ejecutarLista(
                $conexion,
                $sql,
                [$idUser],
                "i"
            );


        //=================================================
        // CATEGORÍAS
        //=================================================

        $sql = "
            SELECT
                id_categorias,
                nombre
            FROM categorias
            WHERE id_user = ?
              AND COALESCE(Eliminado, 0) = 0
            ORDER BY nombre ASC
        ";

        $respuesta["filtros"]["categorias"] =
            ejecutarLista(
                $conexion,
                $sql,
                [$idUser],
                "i"
            );


        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
        );

        exit;
    } catch (Throwable $e) {

        error_log(
            "Error cargarFiltros estadísticas ventas: " .
                $e->getMessage()
        );

        responderError(
            "No fue posible cargar los filtros."
        );
    }
}


//=====================================================
// OBTENER ESTADÍSTICAS
//=====================================================

function obtenerEstadisticas($conexion, $idUser)
{
    try {

        //=================================================
        // FILTROS
        //=================================================

        $fechaDesde = limpiarTexto(
            $_POST["fechaDesde"] ?? ""
        );

        $fechaHasta = limpiarTexto(
            $_POST["fechaHasta"] ?? ""
        );

        $sucursal = obtenerEntero(
            $_POST["sucursal"] ?? ""
        );

        $metodoPago = obtenerEntero(
            $_POST["metodoPago"] ?? ""
        );

        $empleado = obtenerEntero(
            $_POST["empleado"] ?? ""
        );

        $cliente = obtenerEntero(
            $_POST["cliente"] ?? ""
        );

        $categoria = obtenerEntero(
            $_POST["categoria"] ?? ""
        );

        $estado = strtoupper(
            limpiarTexto(
                $_POST["estado"] ?? ""
            )
        );

        $periodoGrafico = strtolower(
            limpiarTexto(
                $_POST["periodoGrafico"] ?? "dia"
            )
        );

        $pagina = max(
            1,
            obtenerEntero(
                $_POST["pagina"] ?? 1
            )
        );

        $limite = obtenerEntero(
            $_POST["limite"] ?? 10
        );

        if ($limite <= 0) {
            $limite = 10;
        }

        if ($limite > 100) {
            $limite = 100;
        }


        //=================================================
        // CONSTRUIR FILTROS
        //=================================================

        $filtros = construirFiltrosVentas(
            $idUser,
            $fechaDesde,
            $fechaHasta,
            $sucursal,
            $metodoPago,
            $estado,
            $empleado,
            $cliente,
            $categoria
        );


        //=================================================
        // RESUMEN
        //=================================================

        $resumen = obtenerResumen(
            $conexion,
            $filtros
        );


        //=================================================
        // COMPARACIONES
        //=================================================

        $comparaciones = obtenerComparaciones(
            $conexion,
            $idUser,
            $fechaDesde,
            $fechaHasta,
            $sucursal,
            $metodoPago,
            $estado,
            $empleado,
            $cliente,
            $categoria,
            $resumen
        );

        $resumen = array_merge(
            $resumen,
            $comparaciones
        );


        //=================================================
        // GRÁFICO EVOLUCIÓN
        //=================================================

        $evolucionVentas = obtenerEvolucionVentas(
            $conexion,
            $filtros,
            $periodoGrafico
        );


        //=================================================
        // GRÁFICO MÉTODOS DE PAGO
        //=================================================

        $metodosPago = obtenerVentasPorMetodoPago(
            $conexion,
            $filtros
        );


        //=================================================
        // GRÁFICO CATEGORÍAS
        //=================================================

        $categorias = obtenerVentasPorCategoria(
            $conexion,
            $filtros
        );


        //=================================================
        // GRÁFICO SUCURSALES
        //=================================================

        $sucursales = obtenerVentasPorSucursal(
            $conexion,
            $filtros
        );


        //=================================================
        // RANKING PRODUCTOS
        //=================================================

        $rankingProductos = obtenerRankingProductos(
            $conexion,
            $filtros
        );


        //=================================================
        // RANKING CLIENTES
        //=================================================

        $rankingClientes = obtenerRankingClientes(
            $conexion,
            $filtros
        );


        //=================================================
        // TOTAL REGISTROS
        //=================================================

        $totalRegistros = obtenerTotalRegistros(
            $conexion,
            $filtros
        );


        //=================================================
        // PAGINACIÓN
        //=================================================

        $totalPaginas = max(
            1,
            (int) ceil(
                $totalRegistros / $limite
            )
        );

        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
        }

        $offset = ($pagina - 1) * $limite;


        //=================================================
        // TABLA
        //=================================================

        $registros = obtenerRegistrosTabla(
            $conexion,
            $filtros,
            $limite,
            $offset
        );


        //=================================================
        // RESPUESTA
        //=================================================

        $respuesta = [

            "success" => true,

            "resumen" => $resumen,

            "graficos" => [

                "evolucionVentas" => $evolucionVentas,

                "metodosPago" => $metodosPago,

                "categorias" => $categorias,

                "sucursales" => $sucursales
            ],

            "rankings" => [

                "productos" => $rankingProductos,

                "clientes" => $rankingClientes
            ],

            "tabla" => [

                "registros" => $registros,

                "totalRegistros" => $totalRegistros,

                "pagina" => $pagina,

                "limite" => $limite,

                "totalPaginas" => $totalPaginas
            ]
        ];


        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
        );

        exit;
    } catch (Throwable $e) {

        error_log(
            "Error estadísticas ventas: " .
                $e->getMessage()
        );

        responderError(
            "No fue posible obtener las estadísticas de ventas."
        );
    }
}


//=====================================================
// CONSTRUIR FILTROS
//=====================================================

function construirFiltrosVentas(
    $idUser,
    $fechaDesde,
    $fechaHasta,
    $sucursal,
    $metodoPago,
    $estado,
    $empleado,
    $cliente,
    $categoria
) {

    $where = [];

    $parametros = [];

    $tipos = "";


    //=================================================
    // USUARIO
    //=================================================

    $where[] = "t.id_user = ?";

    $parametros[] = $idUser;

    $tipos .= "i";


    //=================================================
    // FECHA DESDE
    //=================================================

    if ($fechaDesde !== "") {

        $where[] = "t.fecha_venta >= ?";

        $parametros[] = $fechaDesde;

        $tipos .= "s";
    }


    //=================================================
    // FECHA HASTA
    //=================================================

    if ($fechaHasta !== "") {

        $where[] = "t.fecha_venta <= ?";

        $parametros[] = $fechaHasta;

        $tipos .= "s";
    }


    //=================================================
    // SUCURSAL
    //=================================================

    if ($sucursal > 0) {

        $where[] = "
            EXISTS (
                SELECT 1
                FROM detalle_ticket_ventas dt_sucursal
                INNER JOIN producto p_sucursal
                    ON p_sucursal.idProducto =
                       dt_sucursal.idProducto
                WHERE dt_sucursal.id_ticket_ventas =
                      t.id_ticket_ventas
                  AND p_sucursal.id_sucursal = ?
            )
        ";

        $parametros[] = $sucursal;

        $tipos .= "i";
    }


    //=================================================
    // MÉTODO DE PAGO
    //=================================================

    if ($metodoPago > 0) {

        $where[] = "t.id_metodo_pago = ?";

        $parametros[] = $metodoPago;

        $tipos .= "i";
    }


    //=================================================
    // ESTADO
    //=================================================

    if ($estado !== "") {

        $estadosPermitidos = [
            "PENDIENTE",
            "CONFIRMADO",
            "PREPARANDO",
            "ASIGNADO",
            "OBTENIDO",
            "ENTREGADO",
            "NO_ENTREGADO",
            "CANCELADO"
        ];

        if (
            in_array(
                $estado,
                $estadosPermitidos,
                true
            )
        ) {

            $where[] = "t.estado_envio = ?";

            $parametros[] = $estado;

            $tipos .= "s";
        }
    }


    //=================================================
    // EMPLEADO
    //=================================================

    if ($empleado > 0) {

        $where[] = "t.id_empleado = ?";

        $parametros[] = $empleado;

        $tipos .= "i";
    }


    //=================================================
    // CLIENTE
    //=================================================

    if ($cliente > 0) {

        $where[] = "t.idCliente = ?";

        $parametros[] = $cliente;

        $tipos .= "i";
    }


    //=================================================
    // CATEGORÍA
    //=================================================

    if ($categoria > 0) {

        $where[] = "
            EXISTS (
                SELECT 1
                FROM detalle_ticket_ventas dt_categoria
                INNER JOIN producto p_categoria
                    ON p_categoria.idProducto =
                       dt_categoria.idProducto
                WHERE dt_categoria.id_ticket_ventas =
                      t.id_ticket_ventas
                  AND p_categoria.id_categorias = ?
            )
        ";

        $parametros[] = $categoria;

        $tipos .= "i";
    }


    return [

        "where" => implode(
            " AND ",
            $where
        ),

        "parametros" => $parametros,

        "tipos" => $tipos
    ];
}


//=====================================================
// RESUMEN
//=====================================================

function obtenerResumen($conexion, $filtros)
{
    $sql = "
        SELECT

            COUNT(DISTINCT t.id_ticket_ventas)
                AS totalVentas,

            COALESCE(
                SUM(t.total_venta),
                0
            )
                AS ingresosTotales,

            COALESCE(
                SUM(dt.cantidad_pedido_producto),
                0
            )
                AS productosVendidos,

            COUNT(
                DISTINCT
                CASE
                    WHEN t.idCliente IS NOT NULL
                         AND t.idCliente > 0
                    THEN t.idCliente
                END
            )
                AS clientesAtendidos,

            COUNT(
                DISTINCT dt.idProducto
            )
                AS productosDiferentes,

            COALESCE(
                SUM(
                    dt.cantidad_pedido_producto *
                    COALESCE(p.costo_compra, 0)
                ),
                0
            )
                AS costoProductos

        FROM ticket_ventas t

        LEFT JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas =
               t.id_ticket_ventas

        LEFT JOIN producto p
            ON p.idProducto =
               dt.idProducto

        WHERE {$filtros["where"]}
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );

    $fila = $resultado->fetch_assoc();

    $totalVentas =
        (int) ($fila["totalVentas"] ?? 0);

    $ingresos =
        (float) ($fila["ingresosTotales"] ?? 0);

    $productos =
        (int) ($fila["productosVendidos"] ?? 0);

    $clientes =
        (int) ($fila["clientesAtendidos"] ?? 0);

    $productosDiferentes =
        (int) ($fila["productosDiferentes"] ?? 0);

    $costo =
        (float) ($fila["costoProductos"] ?? 0);

    $ticketPromedio =
        $totalVentas > 0
        ? $ingresos / $totalVentas
        : 0;

    $utilidad =
        $ingresos - $costo;

    $margen =
        $ingresos > 0
        ? ($utilidad / $ingresos) * 100
        : 0;


    return [

        "totalVentas" =>
        $totalVentas,

        "ingresosTotales" =>
        redondear($ingresos),

        "productosVendidos" =>
        $productos,

        "ticketPromedio" =>
        redondear($ticketPromedio),

        "utilidadEstimada" =>
        redondear($utilidad),

        "margenEstimado" =>
        redondear($margen),

        "clientesAtendidos" =>
        $clientes,

        "productosDiferentes" =>
        $productosDiferentes
    ];
}


//=====================================================
// COMPARACIONES
//=====================================================

function obtenerComparaciones(
    $conexion,
    $idUser,
    $fechaDesde,
    $fechaHasta,
    $sucursal,
    $metodoPago,
    $estado,
    $empleado,
    $cliente,
    $categoria,
    $resumenActual
) {

    /*
     * Si no se seleccionó un período completo,
     * no hacemos una comparación artificial.
     */

    if (
        $fechaDesde === "" ||
        $fechaHasta === ""
    ) {

        return [

            "variacionVentas" => 0,

            "variacionIngresos" => 0,

            "variacionProductos" => 0,

            "variacionTicket" => 0
        ];
    }


    $inicio = DateTime::createFromFormat(
        "Y-m-d",
        $fechaDesde
    );

    $fin = DateTime::createFromFormat(
        "Y-m-d",
        $fechaHasta
    );


    if (!$inicio || !$fin) {

        return [

            "variacionVentas" => 0,

            "variacionIngresos" => 0,

            "variacionProductos" => 0,

            "variacionTicket" => 0
        ];
    }


    $dias = $inicio->diff($fin)->days + 1;


    $finAnterior = clone $inicio;

    $finAnterior->modify("-1 day");


    $inicioAnterior = clone $finAnterior;

    $inicioAnterior->modify(
        "-" . ($dias - 1) . " days"
    );


    $filtrosActuales = construirFiltrosVentas(
        $idUser,
        $fechaDesde,
        $fechaHasta,
        $sucursal,
        $metodoPago,
        $estado,
        $empleado,
        $cliente,
        $categoria
    );


    $filtrosAnteriores = construirFiltrosVentas(
        $idUser,
        $inicioAnterior->format("Y-m-d"),
        $finAnterior->format("Y-m-d"),
        $sucursal,
        $metodoPago,
        $estado,
        $empleado,
        $cliente,
        $categoria
    );


    $actual = obtenerResumen(
        $conexion,
        $filtrosActuales
    );

    $anterior = obtenerResumen(
        $conexion,
        $filtrosAnteriores
    );


    return [

        "variacionVentas" =>
        calcularVariacion(
            $actual["totalVentas"],
            $anterior["totalVentas"]
        ),

        "variacionIngresos" =>
        calcularVariacion(
            $actual["ingresosTotales"],
            $anterior["ingresosTotales"]
        ),

        "variacionProductos" =>
        calcularVariacion(
            $actual["productosVendidos"],
            $anterior["productosVendidos"]
        ),

        "variacionTicket" =>
        calcularVariacion(
            $actual["ticketPromedio"],
            $anterior["ticketPromedio"]
        )
    ];
}


//=====================================================
// CALCULAR VARIACIÓN
//=====================================================

function calcularVariacion($actual, $anterior)
{
    $actual = (float) $actual;

    $anterior = (float) $anterior;

    if ($anterior == 0) {

        if ($actual > 0) {
            return 100;
        }

        return 0;
    }

    return redondear(
        (($actual - $anterior) / $anterior) * 100
    );
}


//=====================================================
// EVOLUCIÓN DE VENTAS
//=====================================================

function obtenerEvolucionVentas(
    $conexion,
    $filtros,
    $periodo
) {

    switch ($periodo) {

        case "mes":

            $agrupacion = "
                DATE_FORMAT(
                    t.fecha_venta,
                    '%Y-%m'
                )
            ";

            $etiqueta = "
                DATE_FORMAT(
                    t.fecha_venta,
                    '%m/%Y'
                )
            ";

            break;


        case "semana":

            $agrupacion = "
                YEARWEEK(
                    t.fecha_venta,
                    1
                )
            ";

            $etiqueta = "
                CONCAT(
                    'Sem. ',
                    WEEK(
                        t.fecha_venta,
                        1
                    ),
                    ' - ',
                    YEAR(t.fecha_venta)
                )
            ";

            break;


        case "año":
        case "ano":

            $agrupacion = "
                YEAR(t.fecha_venta)
            ";

            $etiqueta = "
                YEAR(t.fecha_venta)
            ";

            break;


        case "dia":
        default:

            $agrupacion = "
                t.fecha_venta
            ";

            $etiqueta = "
                DATE_FORMAT(
                    t.fecha_venta,
                    '%d/%m/%Y'
                )
            ";

            break;
    }


    $sql = "
        SELECT

            {$agrupacion}
                AS agrupacion,

            {$etiqueta}
                AS etiqueta,

            COALESCE(
                SUM(t.total_venta),
                0
            )
                AS total

        FROM ticket_ventas t

        WHERE {$filtros["where"]}

        GROUP BY
            {$agrupacion}

        ORDER BY
            agrupacion ASC
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $datos[] = [

            "etiqueta" =>
            (string) ($fila["etiqueta"] ?? ""),

            "total" =>
            redondear(
                $fila["total"] ?? 0
            )
        ];
    }

    return $datos;
}


//=====================================================
// VENTAS POR MÉTODO DE PAGO
//=====================================================

function obtenerVentasPorMetodoPago(
    $conexion,
    $filtros
) {

    $sql = "
        SELECT

            COALESCE(
                mp.nombre,
                'Sin método'
            )
                AS nombre,

            COALESCE(
                SUM(t.total_venta),
                0
            )
                AS total

        FROM ticket_ventas t

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               t.id_metodo_pago

        WHERE {$filtros["where"]}

        GROUP BY
            t.id_metodo_pago,
            mp.nombre

        ORDER BY
            total DESC
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $datos[] = [

            "nombre" =>
            (string) ($fila["nombre"] ?? "Sin método"),

            "total" =>
            redondear(
                $fila["total"] ?? 0
            )
        ];
    }

    return $datos;
}


//=====================================================
// VENTAS POR CATEGORÍA
//=====================================================

function obtenerVentasPorCategoria(
    $conexion,
    $filtros
) {

    /*
     * Se suma el subtotal de cada detalle.
     * Esto permite saber cuánto dinero corresponde
     * realmente a cada categoría.
     */

    $sql = "
        SELECT

            COALESCE(
                c.nombre,
                'Sin categoría'
            )
                AS nombre,

            COALESCE(
                SUM(dt.sub_total),
                0
            )
                AS total

        FROM ticket_ventas t

        INNER JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas =
               t.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto =
               dt.idProducto

        LEFT JOIN categorias c
            ON c.id_categorias =
               p.id_categorias

        WHERE {$filtros["where"]}

        GROUP BY
            p.id_categorias,
            c.nombre

        ORDER BY
            total DESC

        LIMIT 10
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $datos[] = [

            "nombre" =>
            (string) ($fila["nombre"] ?? "Sin categoría"),

            "total" =>
            redondear(
                $fila["total"] ?? 0
            )
        ];
    }

    return $datos;
}


//=====================================================
// VENTAS POR SUCURSAL
//=====================================================

function obtenerVentasPorSucursal(
    $conexion,
    $filtros
) {

    $sql = "
        SELECT

            COALESCE(
                s.nombre,
                'Sin sucursal'
            )
                AS nombre,

            COALESCE(
                SUM(dt.sub_total),
                0
            )
                AS total

        FROM ticket_ventas t

        INNER JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas =
               t.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto =
               dt.idProducto

        LEFT JOIN sucursal s
            ON s.id_sucursal =
               p.id_sucursal

        WHERE {$filtros["where"]}

        GROUP BY
            p.id_sucursal,
            s.nombre

        ORDER BY
            total DESC
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $datos[] = [

            "nombre" =>
            (string) ($fila["nombre"] ?? "Sin sucursal"),

            "total" =>
            redondear(
                $fila["total"] ?? 0
            )
        ];
    }

    return $datos;
}


//=====================================================
// RANKING PRODUCTOS
//=====================================================

function obtenerRankingProductos(
    $conexion,
    $filtros
) {

    $sql = "
        SELECT

            p.idProducto,

            p.nombre,

            COALESCE(
                SUM(
                    dt.cantidad_pedido_producto
                ),
                0
            )
                AS cantidad,

            COALESCE(
                SUM(dt.sub_total),
                0
            )
                AS total

        FROM ticket_ventas t

        INNER JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas =
               t.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto =
               dt.idProducto

        WHERE {$filtros["where"]}

        GROUP BY
            p.idProducto,
            p.nombre

        ORDER BY
            cantidad DESC,
            total DESC

        LIMIT 5
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $datos[] = [

            "idProducto" =>
            (int) ($fila["idProducto"] ?? 0),

            "nombre" =>
            (string) ($fila["nombre"] ?? "Producto"),

            "cantidad" =>
            (int) ($fila["cantidad"] ?? 0),

            "total" =>
            redondear(
                $fila["total"] ?? 0
            )
        ];
    }

    return $datos;
}


//=====================================================
// RANKING CLIENTES
//=====================================================

function obtenerRankingClientes(
    $conexion,
    $filtros
) {

    $sql = "
        SELECT

            t.idCliente,

            COALESCE(
                c.nombre,
                'Cliente general'
            )
                AS nombre,

            COUNT(
                DISTINCT t.id_ticket_ventas
            )
                AS ventas,

            COALESCE(
                SUM(t.total_venta),
                0
            )
                AS total

        FROM ticket_ventas t

        LEFT JOIN clientes c
            ON c.idCliente =
               t.idCliente

        WHERE {$filtros["where"]}

          AND t.idCliente IS NOT NULL

          AND t.idCliente > 0

        GROUP BY
            t.idCliente,
            c.nombre

        ORDER BY
            total DESC

        LIMIT 5
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $datos[] = [

            "idCliente" =>
            (int) ($fila["idCliente"] ?? 0),

            "nombre" =>
            (string) ($fila["nombre"] ?? "Cliente"),

            "ventas" =>
            (int) ($fila["ventas"] ?? 0),

            "total" =>
            redondear(
                $fila["total"] ?? 0
            )
        ];
    }

    return $datos;
}


//=====================================================
// TOTAL REGISTROS
//=====================================================

function obtenerTotalRegistros(
    $conexion,
    $filtros
) {

    $sql = "
        SELECT
            COUNT(*) AS total

        FROM ticket_ventas t

        WHERE {$filtros["where"]}
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $fila = $resultado->fetch_assoc();

    return (int) ($fila["total"] ?? 0);
}


//=====================================================
// REGISTROS DE LA TABLA
//=====================================================

function obtenerRegistrosTabla(
    $conexion,
    $filtros,
    $limite,
    $offset
) {

    /*
     * LIMIT y OFFSET no se pasan como parámetros
     * dinámicos aquí. Se convierten previamente a
     * enteros para evitar cualquier inyección.
     */

    $limite = (int) $limite;

    $offset = (int) $offset;


    $sql = "
        SELECT

            t.id_ticket_ventas,

            DATE_FORMAT(
                t.fecha_venta,
                '%d/%m/%Y'
            )
                AS fecha,

            CASE

                WHEN t.tipo_comprobante IS NULL
                     OR t.tipo_comprobante = ''
                THEN CONCAT(
                    COALESCE(t.serie, ''),
                    '-',
                    COALESCE(t.numero, '')
                )

                ELSE CONCAT(
                    t.tipo_comprobante,
                    ' ',
                    COALESCE(t.serie, ''),
                    '-',
                    COALESCE(t.numero, '')
                )

            END
                AS comprobante,

            COALESCE(
                c.nombre,
                'Cliente general'
            )
                AS cliente,

            CASE

                WHEN e.id_empleado IS NULL
                THEN '-'

                ELSE CONCAT(
                    COALESCE(e.nombre, ''),
                    CASE
                        WHEN e.apellido IS NOT NULL
                             AND e.apellido <> ''
                        THEN CONCAT(
                            ' ',
                            e.apellido
                        )
                        ELSE ''
                    END
                )

            END
                AS empleado,

            COALESCE(
                mp.nombre,
                '-'
            )
                AS metodoPago,

            COALESCE(
                (
                    SELECT
                        SUM(
                            dt2.cantidad_pedido_producto
                        )
                    FROM detalle_ticket_ventas dt2
                    WHERE dt2.id_ticket_ventas =
                          t.id_ticket_ventas
                ),
                0
            )
                AS productos,

            COALESCE(
                t.total_venta,
                0
            )
                AS total,

            COALESCE(
                t.estado_envio,
                t.estado_venta,
                'SIN ESTADO'
            )
                AS estado

        FROM ticket_ventas t

        LEFT JOIN clientes c
            ON c.idCliente =
               t.idCliente

        LEFT JOIN empleados e
            ON e.id_empleado =
               t.id_empleado

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               t.id_metodo_pago

        WHERE {$filtros["where"]}

        ORDER BY
            t.fecha_venta DESC,
            t.hora_venta DESC,
            t.id_ticket_ventas DESC

        LIMIT {$limite}
        OFFSET {$offset}
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $datos[] = [

            "fecha" =>
            (string) ($fila["fecha"] ?? ""),

            "comprobante" =>
            (string) ($fila["comprobante"] ?? ""),

            "cliente" =>
            (string) ($fila["cliente"] ?? "Cliente general"),

            "empleado" =>
            (string) ($fila["empleado"] ?? "-"),

            "metodoPago" =>
            (string) ($fila["metodoPago"] ?? "-"),

            "productos" =>
            (int) ($fila["productos"] ?? 0),

            "total" =>
            redondear(
                $fila["total"] ?? 0
            ),

            "estado" =>
            (string) ($fila["estado"] ?? "SIN ESTADO")
        ];
    }

    return $datos;
}


//=====================================================
// EJECUTAR CONSULTA
//=====================================================

function ejecutarConsulta(
    $conexion,
    $sql,
    $parametros = [],
    $tipos = ""
) {

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            "Error preparando consulta: " .
                $conexion->error
        );
    }


    if (!empty($parametros)) {

        $referencias = [];

        $referencias[] = $tipos;

        foreach ($parametros as $indice => $valor) {
            $referencias[] = &$parametros[$indice];
        }

        call_user_func_array(
            [$stmt, "bind_param"],
            $referencias
        );
    }


    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        throw new Exception(
            "Error ejecutando consulta: " .
                $error
        );
    }


    $resultado = $stmt->get_result();

    if (!$resultado) {

        $stmt->close();

        throw new Exception(
            "No se pudo obtener el resultado de la consulta."
        );
    }


    /*
     * El resultado queda asociado al statement.
     * Lo almacenamos para poder cerrar el statement
     * sin perder los datos.
     */

    $datos = $resultado->fetch_all(MYSQLI_ASSOC);

    $stmt->close();


    /*
     * Creamos un resultado mysqli simulado no es necesario.
     * Para mantener compatibilidad con las funciones
     * existentes, utilizamos un objeto temporal mediante
     * resultado de consulta directa.
     *
     * Como get_result() ya fue consumido, devolvemos
     * un ResultIterator propio.
     */

    return new ResultadoEstadisticas($datos);
}


//=====================================================
// EJECUTAR LISTA
//=====================================================

function ejecutarLista(
    $conexion,
    $sql,
    $parametros = [],
    $tipos = ""
) {

    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $parametros,
        $tipos
    );

    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {

        $datos[] = $fila;
    }

    return $datos;
}


//=====================================================
// CLASE RESULTADO
//=====================================================

class ResultadoEstadisticas
{
    private $datos = [];

    private $indice = 0;


    public function __construct($datos)
    {
        $this->datos = $datos;
    }


    public function fetch_assoc()
    {
        if (
            $this->indice >=
            count($this->datos)
        ) {

            return null;
        }

        return $this->datos[$this->indice++];
    }
}


//=====================================================
// OBTENER ENTERO
//=====================================================

function obtenerEntero($valor)
{
    if (
        $valor === null ||
        $valor === ""
    ) {
        return 0;
    }

    return (int) $valor;
}


//=====================================================
// LIMPIAR TEXTO
//=====================================================

function limpiarTexto($valor)
{
    if ($valor === null) {
        return "";
    }

    return trim(
        (string) $valor
    );
}


//=====================================================
// REDONDEAR
//=====================================================

function redondear($valor)
{
    return round(
        (float) $valor,
        2
    );
}


//=====================================================
// RESPONDER ERROR
//=====================================================

function responderError($mensaje)
{
    echo json_encode(

        [

            "success" => false,

            "mensaje" =>
            $mensaje

        ],

        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES

    );

    exit;
}
