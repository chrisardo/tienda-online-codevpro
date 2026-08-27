<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/estadisticas_productos.php
// Módulo: Estadísticas de Productos
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

//=====================================================
// RESPUESTA BASE
//=====================================================

$respuesta = [
    "estado" => false,
    "mensaje" => "",
];

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUser <= 0) {

    $respuesta["mensaje"] = "La sesión ha expirado.";

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

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    $respuesta["mensaje"] =
        "No se pudo conectar con la base de datos.";

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON($datos)
{
    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// ACCIÓN
//=====================================================

$accion = isset($_POST["accion"])
    ? trim($_POST["accion"])
    : "";


//=====================================================
// CARGAR FILTROS
//=====================================================

if ($accion === "cargar_filtros") {

    try {

        //=================================================
        // CATEGORÍAS
        //=================================================

        $categorias = [];

        $sqlCategorias = "
            SELECT
                id_categorias,
                nombre
            FROM categorias
            WHERE id_user = ?
              AND Eliminado = 0
            ORDER BY nombre ASC
        ";

        $stmtCategorias = mysqli_prepare(
            $conexion,
            $sqlCategorias
        );

        if (!$stmtCategorias) {

            throw new Exception(
                "No se pudo preparar la consulta de categorías: " .
                    mysqli_error($conexion)
            );
        }

        mysqli_stmt_bind_param(
            $stmtCategorias,
            "i",
            $idUser
        );

        if (!mysqli_stmt_execute($stmtCategorias)) {

            throw new Exception(
                "No se pudo ejecutar la consulta de categorías: " .
                    mysqli_stmt_error($stmtCategorias)
            );
        }

        $resultadoCategorias =
            mysqli_stmt_get_result(
                $stmtCategorias
            );

        while (
            $fila =
            mysqli_fetch_assoc($resultadoCategorias)
        ) {

            $categorias[] = [

                "id_categorias" =>
                (int) $fila["id_categorias"],

                "nombre" =>
                $fila["nombre"],
            ];
        }

        mysqli_stmt_close($stmtCategorias);


        //=================================================
        // MARCAS
        //=================================================

        $marcas = [];

        $sqlMarcas = "
            SELECT
                id_marca,
                nombre
            FROM marcas
            WHERE id_user = ?
              AND Eliminado = 0
            ORDER BY nombre ASC
        ";

        $stmtMarcas = mysqli_prepare(
            $conexion,
            $sqlMarcas
        );

        if (!$stmtMarcas) {

            throw new Exception(
                "No se pudo preparar la consulta de marcas: " .
                    mysqli_error($conexion)
            );
        }

        mysqli_stmt_bind_param(
            $stmtMarcas,
            "i",
            $idUser
        );

        if (!mysqli_stmt_execute($stmtMarcas)) {

            throw new Exception(
                "No se pudo ejecutar la consulta de marcas: " .
                    mysqli_stmt_error($stmtMarcas)
            );
        }

        $resultadoMarcas =
            mysqli_stmt_get_result(
                $stmtMarcas
            );

        while (
            $fila =
            mysqli_fetch_assoc($resultadoMarcas)
        ) {

            $marcas[] = [

                "id_marca" =>
                (int) $fila["id_marca"],

                "nombre" =>
                $fila["nombre"],
            ];
        }

        mysqli_stmt_close($stmtMarcas);


        //=================================================
        // SUCURSALES
        //=================================================

        $sucursales = [];

        $sqlSucursales = "
            SELECT
                id_sucursal,
                nombre
            FROM sucursal
            WHERE id_user = ?
              AND Eliminado = 0
            ORDER BY nombre ASC
        ";

        $stmtSucursales = mysqli_prepare(
            $conexion,
            $sqlSucursales
        );

        if (!$stmtSucursales) {

            throw new Exception(
                "No se pudo preparar la consulta de sucursales: " .
                    mysqli_error($conexion)
            );
        }

        mysqli_stmt_bind_param(
            $stmtSucursales,
            "i",
            $idUser
        );

        if (!mysqli_stmt_execute($stmtSucursales)) {

            throw new Exception(
                "No se pudo ejecutar la consulta de sucursales: " .
                    mysqli_stmt_error($stmtSucursales)
            );
        }

        $resultadoSucursales =
            mysqli_stmt_get_result(
                $stmtSucursales
            );

        while (
            $fila =
            mysqli_fetch_assoc($resultadoSucursales)
        ) {

            $sucursales[] = [

                "id_sucursal" =>
                (int) $fila["id_sucursal"],

                "nombre" =>
                $fila["nombre"],
            ];
        }

        mysqli_stmt_close($stmtSucursales);


        //=================================================
        // RESPUESTA
        //=================================================

        responderJSON([

            "estado" => true,

            "mensaje" =>
            "Filtros cargados correctamente.",

            "categorias" =>
            $categorias,

            "marcas" =>
            $marcas,

            "sucursales" =>
            $sucursales,
        ]);
    } catch (Throwable $e) {

        responderJSON([

            "estado" => false,

            "mensaje" =>
            $e->getMessage(),
        ]);
    }
}


//=====================================================
// OBTENER ESTADÍSTICAS
//=====================================================

if ($accion === "obtener_estadisticas") {

    try {

        //=================================================
        // FILTROS
        //=================================================

        $buscar = isset($_POST["buscar"])
            ? trim($_POST["buscar"])
            : "";

        $categoria = isset($_POST["categoria"])
            ? (int) $_POST["categoria"]
            : 0;

        $marca = isset($_POST["marca"])
            ? (int) $_POST["marca"]
            : 0;

        $sucursal = isset($_POST["sucursal"])
            ? (int) $_POST["sucursal"]
            : 0;

        $tipo = isset($_POST["tipo"])
            ? trim($_POST["tipo"])
            : "";

        $stock = isset($_POST["stock"])
            ? trim($_POST["stock"])
            : "";

        $ordenar = isset($_POST["ordenar"])
            ? trim($_POST["ordenar"])
            : "ventas_desc";

        $fechaInicio = isset($_POST["fecha_inicio"])
            ? trim($_POST["fecha_inicio"])
            : "";

        $fechaFin = isset($_POST["fecha_fin"])
            ? trim($_POST["fecha_fin"])
            : "";

        $pagina = isset($_POST["pagina"])
            ? (int) $_POST["pagina"]
            : 1;

        $limite = isset($_POST["limite"])
            ? (int) $_POST["limite"]
            : 10;


        //=================================================
        // VALIDACIONES
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

        $offset = ($pagina - 1) * $limite;


        //=================================================
        // CONDICIONES PRODUCTOS
        //=================================================

        $condiciones = [];

        $parametros = [];

        $tipos = "";


        //=================================================
        // USUARIO
        //=================================================

        $condiciones[] = "p.id_user = ?";

        $parametros[] = $idUser;

        $tipos .= "i";


        //=================================================
        // NO ELIMINADOS
        //=================================================

        $condiciones[] = "p.Eliminado = 0";


        //=================================================
        // BÚSQUEDA
        //=================================================

        if ($buscar !== "") {

            $condiciones[] = "
                (
                    p.nombre LIKE ?
                    OR p.codigo LIKE ?
                )
            ";

            $buscarLike =
                "%" . $buscar . "%";

            $parametros[] =
                $buscarLike;

            $parametros[] =
                $buscarLike;

            $tipos .= "ss";
        }


        //=================================================
        // CATEGORÍA
        //=================================================

        if ($categoria > 0) {

            $condiciones[] =
                "p.id_categorias = ?";

            $parametros[] =
                $categoria;

            $tipos .= "i";
        }


        //=================================================
        // MARCA
        //=================================================

        if ($marca > 0) {

            $condiciones[] =
                "p.id_marca = ?";

            $parametros[] =
                $marca;

            $tipos .= "i";
        }


        //=================================================
        // SUCURSAL
        //=================================================

        if ($sucursal > 0) {

            $condiciones[] =
                "p.id_sucursal = ?";

            $parametros[] =
                $sucursal;

            $tipos .= "i";
        }


        //=================================================
        // TIPO
        //=================================================

        if ($tipo !== "") {

            $condiciones[] =
                "p.tipo = ?";

            $parametros[] =
                $tipo;

            $tipos .= "s";
        }


        //=================================================
        // STOCK
        //=================================================

        if ($stock === "disponible") {

            $condiciones[] =
                "p.stock > 0";
        } elseif ($stock === "agotado") {

            $condiciones[] =
                "p.stock <= 0";
        } elseif ($stock === "bajo") {

            $condiciones[] = "
                p.stock > 0
                AND p.stock <= 5
            ";
        }


        //=================================================
        // CONDICIONES FECHA DE VENTA
        //=================================================

        $condicionesVenta = [];

        $parametrosVenta = [];

        $tiposVenta = "";


        if ($fechaInicio !== "") {

            $condicionesVenta[] = "
                tv.fecha_venta >= ?
            ";

            $parametrosVenta[] =
                $fechaInicio . " 00:00:00";

            $tiposVenta .= "s";
        }


        if ($fechaFin !== "") {

            $condicionesVenta[] = "
                tv.fecha_venta <= ?
            ";

            $parametrosVenta[] =
                $fechaFin . " 23:59:59";

            $tiposVenta .= "s";
        }


        $condicionFechaVenta = "";

        if (!empty($condicionesVenta)) {

            $condicionFechaVenta =
                " AND " .
                implode(
                    " AND ",
                    $condicionesVenta
                );
        }


        //=================================================
        // WHERE PRODUCTOS
        //=================================================

        $whereProductos =
            implode(
                " AND ",
                $condiciones
            );


        //=================================================
        // SUBCONSULTA DE VENTAS
        //=================================================
        //
        // Una fila por producto.
        //
        // Esto evita duplicar los productos cuando tienen
        // varias ventas.
        //
        //=================================================

        $sqlVentas = "
            SELECT

                dtv.idProducto,

                COALESCE(
                    SUM(
                        dtv.cantidad_pedido_producto
                    ),
                    0
                ) AS unidades_vendidas,

                COALESCE(
                    SUM(
                        dtv.sub_total
                    ),
                    0
                ) AS ingresos

            FROM detalle_ticket_ventas dtv

            INNER JOIN ticket_ventas tv
                ON tv.id_ticket_ventas =
                   dtv.id_ticket_ventas

            WHERE dtv.id_user = ?

              AND tv.id_user = ?

              AND tv.estado_venta NOT IN (
                  'Cancelado',
                  'Anulado'
              )

              {$condicionFechaVenta}

            GROUP BY
                dtv.idProducto
        ";


        //=================================================
        // PARÁMETROS SUBCONSULTA VENTAS
        //=================================================

        $parametrosVentas = [

            $idUser,
            $idUser,
        ];

        $tiposVentas = "ii";


        foreach ($parametrosVenta as $parametro) {

            $parametrosVentas[] =
                $parametro;
        }

        $tiposVentas .=
            $tiposVenta;


        //=================================================
        // QUERY BASE PRODUCTOS
        //=================================================

        $sqlBase = "
            SELECT

                p.idProducto,

                p.codigo,

                p.nombre,

                p.tipo,

                p.precio,

                p.costo_compra,

                p.stock,

                p.id_categorias,

                p.id_marca,

                p.id_sucursal,

                COALESCE(
                    c.nombre,
                    'Sin categoría'
                ) AS categoria,

                COALESCE(
                    m.nombre,
                    'Sin marca'
                ) AS marca,

                COALESCE(
                    s.nombre,
                    'Sin sucursal'
                ) AS sucursal,

                COALESCE(
                    v.unidades_vendidas,
                    0
                ) AS unidades_vendidas,

                COALESCE(
                    v.ingresos,
                    0
                ) AS ingresos

            FROM producto p

            LEFT JOIN categorias c
                ON c.id_categorias =
                   p.id_categorias

            LEFT JOIN marcas m
                ON m.id_marca =
                   p.id_marca

            LEFT JOIN sucursal s
                ON s.id_sucursal =
                   p.id_sucursal

            LEFT JOIN (
                {$sqlVentas}
            ) v
                ON v.idProducto =
                   p.idProducto

            WHERE {$whereProductos}
        ";


        //=================================================
        // ORDENAMIENTO
        //=================================================

        switch ($ordenar) {

            case "ingresos_desc":

                $ordenSQL = "
                    ingresos DESC,
                    p.nombre ASC
                ";

                break;


            case "ganancia_desc":

                $ordenSQL = "
                    (
                        ingresos -
                        (
                            unidades_vendidas *
                            COALESCE(
                                p.costo_compra,
                                0
                            )
                        )
                    ) DESC,
                    p.nombre ASC
                ";

                break;


            case "stock_desc":

                $ordenSQL = "
                    p.stock DESC,
                    p.nombre ASC
                ";

                break;


            case "nombre_asc":

                $ordenSQL = "
                    p.nombre ASC
                ";

                break;


            case "ventas_desc":

            default:

                $ordenSQL = "
                    unidades_vendidas DESC,
                    p.nombre ASC
                ";

                break;
        }


        //=================================================
        // CONTAR PRODUCTOS
        //=================================================

        $sqlCount = "
            SELECT
                COUNT(*) AS total

            FROM (
                {$sqlBase}
            ) consulta
        ";


        $stmtCount = mysqli_prepare(
            $conexion,
            $sqlCount
        );

        if (!$stmtCount) {

            throw new Exception(
                "No se pudo preparar el conteo de productos: " .
                    mysqli_error($conexion)
            );
        }


        //=================================================
        // PARÁMETROS COUNT
        //=================================================

        $parametrosCount =
            array_merge(
                $parametrosVentas,
                $parametros
            );

        $tiposCount =
            $tiposVentas .
            $tipos;


        mysqli_stmt_bind_param(
            $stmtCount,
            $tiposCount,
            ...$parametrosCount
        );


        if (!mysqli_stmt_execute($stmtCount)) {

            throw new Exception(
                "No se pudo ejecutar el conteo de productos: " .
                    mysqli_stmt_error($stmtCount)
            );
        }


        $resultadoCount =
            mysqli_stmt_get_result(
                $stmtCount
            );

        if (!$resultadoCount) {

            throw new Exception(
                "No se pudo obtener el conteo de productos: " .
                    mysqli_stmt_error($stmtCount)
            );
        }


        $filaCount =
            mysqli_fetch_assoc(
                $resultadoCount
            );

        $totalProductos =
            (int) (
                $filaCount["total"] ?? 0
            );

        mysqli_stmt_close(
            $stmtCount
        );


        //=================================================
        // QUERY PAGINADA
        //=================================================

        $sql = "
            {$sqlBase}

            ORDER BY {$ordenSQL}

            LIMIT ? OFFSET ?
        ";


        $stmt = mysqli_prepare(
            $conexion,
            $sql
        );

        if (!$stmt) {

            throw new Exception(
                "No se pudo preparar la consulta de estadísticas: " .
                    mysqli_error($conexion)
            );
        }


        //=================================================
        // PARÁMETROS PAGINADOS
        //=================================================

        $parametrosFinales =
            array_merge(
                $parametrosVentas,
                $parametros
            );

        $tiposFinales =
            $tiposVentas .
            $tipos .
            "ii";

        $parametrosFinales[] =
            $limite;

        $parametrosFinales[] =
            $offset;


        mysqli_stmt_bind_param(
            $stmt,
            $tiposFinales,
            ...$parametrosFinales
        );


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "No se pudo ejecutar la consulta de estadísticas: " .
                    mysqli_stmt_error($stmt)
            );
        }


        $resultado =
            mysqli_stmt_get_result(
                $stmt
            );

        if (!$resultado) {

            throw new Exception(
                "No se pudo obtener las estadísticas: " .
                    mysqli_stmt_error($stmt)
            );
        }


        //=================================================
        // PRODUCTOS
        //=================================================

        $productos = [];


        while (
            $fila =
            mysqli_fetch_assoc($resultado)
        ) {

            $unidadesVendidas =
                (float) (
                    $fila["unidades_vendidas"] ?? 0
                );

            $ingresos =
                (float) (
                    $fila["ingresos"] ?? 0
                );

            $costoCompra =
                (float) (
                    $fila["costo_compra"] ?? 0
                );

            $stockActual =
                (float) (
                    $fila["stock"] ?? 0
                );


            //=================================================
            // COSTO DE VENTAS
            //=================================================

            $costoVentas =
                $unidadesVendidas *
                $costoCompra;


            //=================================================
            // GANANCIA
            //=================================================

            $ganancia =
                $ingresos -
                $costoVentas;


            //=================================================
            // MARGEN
            //=================================================

            $margen = 0;

            if ($ingresos > 0) {

                $margen =
                    ($ganancia / $ingresos) *
                    100;
            }


            $productos[] = [

                "idProducto" =>
                (int) $fila["idProducto"],

                "codigo" =>
                $fila["codigo"],

                "nombre" =>
                $fila["nombre"],

                "tipo" =>
                $fila["tipo"],

                "categoria" =>
                $fila["categoria"],

                "marca" =>
                $fila["marca"],

                "sucursal" =>
                $fila["sucursal"],

                "stock" =>
                $stockActual,

                "vendidos" =>
                $unidadesVendidas,

                "precio" =>
                (float) $fila["precio"],

                "costo" =>
                $costoCompra,

                "ingresos" =>
                $ingresos,

                "ganancia" =>
                $ganancia,

                "margen" =>
                $margen,
            ];
        }


        mysqli_stmt_close($stmt);


        //=================================================
        // GENERAR TABLA
        //=================================================

        $tabla = "";


        foreach ($productos as $producto) {

            $stock =
                $producto["stock"];


            //=================================================
            // CLASE STOCK
            //=================================================

            $claseStock =
                "text-success";

            if ($stock <= 0) {

                $claseStock =
                    "text-danger";
            } elseif ($stock <= 5) {

                $claseStock =
                    "text-warning";
            }


            //=================================================
            // CLASE MARGEN
            //=================================================

            $margen =
                $producto["margen"];

            $claseMargen =
                "text-success";

            if ($margen < 0) {

                $claseMargen =
                    "text-danger";
            } elseif ($margen < 20) {

                $claseMargen =
                    "text-warning";
            }


            //=================================================
            // HTML
            //=================================================

            $tabla .= "

                <tr>

                    <td>

                        <div class='fw-semibold'>
                            " .
                htmlspecialchars(
                    $producto["nombre"],
                    ENT_QUOTES,
                    "UTF-8"
                )
                . "
                        </div>

                        <small class='text-muted'>
                            " .
                htmlspecialchars(
                    $producto["codigo"],
                    ENT_QUOTES,
                    "UTF-8"
                )
                . "
                        </small>

                    </td>


                    <td>
                        " .
                htmlspecialchars(
                    $producto["categoria"],
                    ENT_QUOTES,
                    "UTF-8"
                )
                . "
                    </td>


                    <td>
                        " .
                htmlspecialchars(
                    $producto["marca"],
                    ENT_QUOTES,
                    "UTF-8"
                )
                . "
                    </td>


                    <td class='text-center {$claseStock}'>

                        <strong>
                            " .
                number_format(
                    $stock,
                    0,
                    ".",
                    ","
                )
                . "
                        </strong>

                    </td>


                    <td class='text-center'>

                        <strong>
                            " .
                number_format(
                    $producto["vendidos"],
                    0,
                    ".",
                    ","
                )
                . "
                        </strong>

                    </td>


                    <td>

                        S/ " .
                number_format(
                    $producto["precio"],
                    2,
                    ".",
                    ","
                )
                . "

                    </td>


                    <td>

                        S/ " .
                number_format(
                    $producto["costo"],
                    2,
                    ".",
                    ","
                )
                . "

                    </td>


                    <td>

                        S/ " .
                number_format(
                    $producto["ingresos"],
                    2,
                    ".",
                    ","
                )
                . "

                    </td>


                    <td class='{$claseMargen}'>

                        <strong>

                            S/ " .
                number_format(
                    $producto["ganancia"],
                    2,
                    ".",
                    ","
                )
                . "

                        </strong>

                    </td>


                    <td class='{$claseMargen}'>

                        <strong>

                            " .
                number_format(
                    $producto["margen"],
                    2,
                    ".",
                    ","
                )
                . "%

                        </strong>

                    </td>

                </tr>

            ";
        }


        //=================================================
        // PAGINACIÓN
        //=================================================

        $totalPaginas =
            $totalProductos > 0
            ? (int) ceil(
                $totalProductos /
                    $limite
            )
            : 1;


        $paginacion = "";


        if ($totalPaginas > 1) {

            $paginacion .= "
                <nav>
                    <ul class='pagination mb-0'>
            ";


            //=================================================
            // ANTERIOR
            //=================================================

            $paginaAnterior =
                max(
                    1,
                    $pagina - 1
                );

            $disabledAnterior =
                $pagina <= 1
                ? "disabled"
                : "";


            $paginacion .= "

                <li class='page-item {$disabledAnterior}'>

                    <button
                        type='button'
                        class='page-link btn-pagina-estadistica-producto'
                        data-pagina='{$paginaAnterior}'>

                        <i class='bi bi-chevron-left'></i>

                    </button>

                </li>

            ";


            //=================================================
            // PÁGINAS
            //=================================================

            for (
                $i = 1;
                $i <= $totalPaginas;
                $i++
            ) {

                if (
                    $i === 1 ||
                    $i === $totalPaginas ||
                    abs($i - $pagina) <= 2
                ) {

                    $activo =
                        $i === $pagina
                        ? "active"
                        : "";

                    $paginacion .= "

                        <li class='page-item {$activo}'>

                            <button
                                type='button'
                                class='page-link btn-pagina-estadistica-producto'
                                data-pagina='{$i}'>

                                {$i}

                            </button>

                        </li>

                    ";
                } elseif (
                    $i === 2 &&
                    $pagina > 4
                ) {

                    $paginacion .= "

                        <li class='page-item disabled'>

                            <span class='page-link'>
                                ...
                            </span>

                        </li>

                    ";
                } elseif (
                    $i === $totalPaginas - 1 &&
                    $pagina < $totalPaginas - 3
                ) {

                    $paginacion .= "

                        <li class='page-item disabled'>

                            <span class='page-link'>
                                ...
                            </span>

                        </li>

                    ";
                }
            }


            //=================================================
            // SIGUIENTE
            //=================================================

            $paginaSiguiente =
                min(
                    $totalPaginas,
                    $pagina + 1
                );

            $disabledSiguiente =
                $pagina >= $totalPaginas
                ? "disabled"
                : "";


            $paginacion .= "

                <li class='page-item {$disabledSiguiente}'>

                    <button
                        type='button'
                        class='page-link btn-pagina-estadistica-producto'
                        data-pagina='{$paginaSiguiente}'>

                        <i class='bi bi-chevron-right'></i>

                    </button>

                </li>

            ";


            $paginacion .= "
                    </ul>
                </nav>
            ";
        }


        //=====================================================
        // KPI
        //=====================================================
        //
        // IMPORTANTE:
        //
        // Los KPI utilizan todos los productos que cumplen
        // los filtros.
        //
        // NO utilizan solamente los productos de la página.
        //
        //=====================================================

        $kpiTotalProductos =
            $totalProductos;


        //=====================================================
        // CONSULTA KPI
        //=====================================================
        //
        // Se utiliza la misma subconsulta de ventas que la
        // tabla principal.
        //
        // De esta manera cada producto aparece una sola vez
        // en el cálculo.
        //
        //=====================================================

        $sqlKpi = "
            SELECT

                COUNT(
                    CASE
                        WHEN COALESCE(
                            v.unidades_vendidas,
                            0
                        ) > 0
                        THEN 1
                    END
                ) AS productos_vendidos,


                COALESCE(
                    SUM(
                        COALESCE(
                            v.unidades_vendidas,
                            0
                        )
                    ),
                    0
                ) AS unidades_vendidas,


                COALESCE(
                    SUM(
                        COALESCE(
                            v.ingresos,
                            0
                        )
                    ),
                    0
                ) AS ingresos,


                COALESCE(
                    SUM(
                        p.stock *
                        COALESCE(
                            p.costo_compra,
                            0
                        )
                    ),
                    0
                ) AS valor_inventario,


                COALESCE(
                    SUM(
                        COALESCE(
                            v.ingresos,
                            0
                        )
                        -
                        (
                            COALESCE(
                                v.unidades_vendidas,
                                0
                            )
                            *
                            COALESCE(
                                p.costo_compra,
                                0
                            )
                        )
                    ),
                    0
                ) AS ganancia,


                COUNT(
                    CASE
                        WHEN COALESCE(
                            v.unidades_vendidas,
                            0
                        ) = 0
                        THEN 1
                    END
                ) AS productos_sin_ventas


            FROM producto p


            LEFT JOIN (
                {$sqlVentas}
            ) v

                ON v.idProducto =
                   p.idProducto


            WHERE {$whereProductos}
        ";


        //=====================================================
        // PARÁMETROS KPI
        //=====================================================
        //
        // El orden de los ? es:
        //
        // 1. dtv.id_user
        // 2. tv.id_user
        // 3... parámetros de fecha
        // después:
        // parámetros de producto
        //
        //=====================================================

        $parametrosKpi =
            array_merge(
                $parametrosVentas,
                $parametros
            );

        $tiposKpi =
            $tiposVentas .
            $tipos;


        //=====================================================
        // PREPARAR KPI
        //=====================================================

        $stmtKpi = mysqli_prepare(
            $conexion,
            $sqlKpi
        );

        if (!$stmtKpi) {

            throw new Exception(
                "No se pudo preparar la consulta de KPI: " .
                    mysqli_error($conexion)
            );
        }


        //=====================================================
        // BIND KPI
        //=====================================================

        if (!empty($parametrosKpi)) {

            mysqli_stmt_bind_param(
                $stmtKpi,
                $tiposKpi,
                ...$parametrosKpi
            );
        }


        //=====================================================
        // EJECUTAR KPI
        //=====================================================

        if (!mysqli_stmt_execute($stmtKpi)) {

            throw new Exception(
                "No se pudo ejecutar la consulta de KPI: " .
                    mysqli_stmt_error($stmtKpi)
            );
        }


        //=====================================================
        // RESULTADO KPI
        //=====================================================

        $resultadoKpi =
            mysqli_stmt_get_result(
                $stmtKpi
            );

        if (!$resultadoKpi) {

            throw new Exception(
                "No se pudo obtener el resultado de KPI: " .
                    mysqli_stmt_error($stmtKpi)
            );
        }


        $filaKpi =
            mysqli_fetch_assoc(
                $resultadoKpi
            );


        mysqli_stmt_close($stmtKpi);


        //=====================================================
        // OBJETO KPI
        //=====================================================

        $kpi = [

            "total_productos" =>
            (int) $kpiTotalProductos,

            "productos_vendidos" =>
            (int) (
                $filaKpi["productos_vendidos"]
                ?? 0
            ),

            "unidades_vendidas" =>
            (float) (
                $filaKpi["unidades_vendidas"]
                ?? 0
            ),

            "ingresos" =>
            (float) (
                $filaKpi["ingresos"]
                ?? 0
            ),

            "valor_inventario" =>
            (float) (
                $filaKpi["valor_inventario"]
                ?? 0
            ),

            "ganancia" =>
            (float) (
                $filaKpi["ganancia"]
                ?? 0
            ),

            "productos_sin_ventas" =>
            (int) (
                $filaKpi["productos_sin_ventas"]
                ?? 0
            ),
        ];


        //=====================================================
        // GRÁFICO - VENTAS POR PERÍODO
        //=====================================================

        $graficoVentas = [];


        $sqlGraficoVentas = "
            SELECT

                DATE(tv.fecha_venta) AS fecha,

                COALESCE(
                    SUM(dtv.sub_total),
                    0
                ) AS total

            FROM detalle_ticket_ventas dtv

            INNER JOIN ticket_ventas tv
                ON tv.id_ticket_ventas =
                   dtv.id_ticket_ventas

            INNER JOIN producto p
                ON p.idProducto =
                   dtv.idProducto

            WHERE dtv.id_user = ?

              AND tv.id_user = ?

              AND p.id_user = ?

              AND p.Eliminado = 0

              AND tv.estado_venta NOT IN (
                  'Cancelado',
                  'Anulado'
              )

              {$condicionFechaVenta}

            GROUP BY
                DATE(tv.fecha_venta)

            ORDER BY
                DATE(tv.fecha_venta) ASC
        ";


        $stmtGraficoVentas =
            mysqli_prepare(
                $conexion,
                $sqlGraficoVentas
            );

        if (!$stmtGraficoVentas) {

            throw new Exception(
                "No se pudo preparar el gráfico de ventas: " .
                    mysqli_error($conexion)
            );
        }


        $parametrosGraficoVentas = [

            $idUser,
            $idUser,
            $idUser,
        ];


        $parametrosGraficoVentas =
            array_merge(
                $parametrosGraficoVentas,
                $parametrosVenta
            );


        $tiposGraficoVentas =
            "iii" .
            $tiposVenta;


        mysqli_stmt_bind_param(
            $stmtGraficoVentas,
            $tiposGraficoVentas,
            ...$parametrosGraficoVentas
        );


        if (!mysqli_stmt_execute($stmtGraficoVentas)) {

            throw new Exception(
                "No se pudo ejecutar el gráfico de ventas: " .
                    mysqli_stmt_error($stmtGraficoVentas)
            );
        }


        $resultadoGraficoVentas =
            mysqli_stmt_get_result(
                $stmtGraficoVentas
            );


        while (
            $fila =
            mysqli_fetch_assoc(
                $resultadoGraficoVentas
            )
        ) {

            $graficoVentas[] = [

                "fecha" =>
                $fila["fecha"],

                "total" =>
                (float) $fila["total"],
            ];
        }


        mysqli_stmt_close(
            $stmtGraficoVentas
        );


        //=====================================================
        // GRÁFICO - PRODUCTOS MÁS VENDIDOS
        //=====================================================

        $graficoProductosVendidos = [];


        $sqlGraficoProductos = "
            SELECT

                p.nombre,

                COALESCE(
                    SUM(
                        dtv.cantidad_pedido_producto
                    ),
                    0
                ) AS cantidad

            FROM detalle_ticket_ventas dtv

            INNER JOIN ticket_ventas tv
                ON tv.id_ticket_ventas =
                   dtv.id_ticket_ventas

            INNER JOIN producto p
                ON p.idProducto =
                   dtv.idProducto

            WHERE dtv.id_user = ?

              AND tv.id_user = ?

              AND p.id_user = ?

              AND p.Eliminado = 0

              AND tv.estado_venta NOT IN (
                  'Cancelado',
                  'Anulado'
              )

              {$condicionFechaVenta}

            GROUP BY
                p.idProducto,
                p.nombre

            ORDER BY
                cantidad DESC

            LIMIT 10
        ";


        $stmtGraficoProductos =
            mysqli_prepare(
                $conexion,
                $sqlGraficoProductos
            );

        if (!$stmtGraficoProductos) {

            throw new Exception(
                "No se pudo preparar el gráfico de productos vendidos: " .
                    mysqli_error($conexion)
            );
        }


        $parametrosGraficoProductos = [

            $idUser,
            $idUser,
            $idUser,
        ];


        $parametrosGraficoProductos =
            array_merge(
                $parametrosGraficoProductos,
                $parametrosVenta
            );


        $tiposGraficoProductos =
            "iii" .
            $tiposVenta;


        mysqli_stmt_bind_param(
            $stmtGraficoProductos,
            $tiposGraficoProductos,
            ...$parametrosGraficoProductos
        );


        if (!mysqli_stmt_execute($stmtGraficoProductos)) {

            throw new Exception(
                "No se pudo ejecutar el gráfico de productos vendidos: " .
                    mysqli_stmt_error($stmtGraficoProductos)
            );
        }


        $resultadoGraficoProductos =
            mysqli_stmt_get_result(
                $stmtGraficoProductos
            );


        while (
            $fila =
            mysqli_fetch_assoc(
                $resultadoGraficoProductos
            )
        ) {

            $graficoProductosVendidos[] = [

                "nombre" =>
                $fila["nombre"],

                "cantidad" =>
                (float) $fila["cantidad"],
            ];
        }


        mysqli_stmt_close(
            $stmtGraficoProductos
        );


        //=====================================================
        // GRÁFICO - INGRESOS POR PRODUCTO
        //=====================================================

        $graficoIngresosProductos = [];


        $sqlGraficoIngresos = "
            SELECT

                p.nombre,

                COALESCE(
                    SUM(dtv.sub_total),
                    0
                ) AS ingresos

            FROM detalle_ticket_ventas dtv

            INNER JOIN ticket_ventas tv
                ON tv.id_ticket_ventas =
                   dtv.id_ticket_ventas

            INNER JOIN producto p
                ON p.idProducto =
                   dtv.idProducto

            WHERE dtv.id_user = ?

              AND tv.id_user = ?

              AND p.id_user = ?

              AND p.Eliminado = 0

              AND tv.estado_venta NOT IN (
                  'Cancelado',
                  'Anulado'
              )

              {$condicionFechaVenta}

            GROUP BY
                p.idProducto,
                p.nombre

            ORDER BY
                ingresos DESC

            LIMIT 10
        ";


        $stmtGraficoIngresos =
            mysqli_prepare(
                $conexion,
                $sqlGraficoIngresos
            );

        if (!$stmtGraficoIngresos) {

            throw new Exception(
                "No se pudo preparar el gráfico de ingresos: " .
                    mysqli_error($conexion)
            );
        }


        $parametrosGraficoIngresos = [

            $idUser,
            $idUser,
            $idUser,
        ];


        $parametrosGraficoIngresos =
            array_merge(
                $parametrosGraficoIngresos,
                $parametrosVenta
            );


        $tiposGraficoIngresos =
            "iii" .
            $tiposVenta;


        mysqli_stmt_bind_param(
            $stmtGraficoIngresos,
            $tiposGraficoIngresos,
            ...$parametrosGraficoIngresos
        );


        if (!mysqli_stmt_execute($stmtGraficoIngresos)) {

            throw new Exception(
                "No se pudo ejecutar el gráfico de ingresos: " .
                    mysqli_stmt_error($stmtGraficoIngresos)
            );
        }


        $resultadoGraficoIngresos =
            mysqli_stmt_get_result(
                $stmtGraficoIngresos
            );


        while (
            $fila =
            mysqli_fetch_assoc(
                $resultadoGraficoIngresos
            )
        ) {

            $graficoIngresosProductos[] = [

                "nombre" =>
                $fila["nombre"],

                "ingresos" =>
                (float) $fila["ingresos"],
            ];
        }


        mysqli_stmt_close(
            $stmtGraficoIngresos
        );


        //=====================================================
        // GRÁFICO - VENTAS POR CATEGORÍA
        //=====================================================

        $graficoCategorias = [];


        $sqlGraficoCategorias = "
            SELECT

                COALESCE(
                    c.nombre,
                    'Sin categoría'
                ) AS nombre,

                COALESCE(
                    SUM(
                        dtv.cantidad_pedido_producto
                    ),
                    0
                ) AS total

            FROM detalle_ticket_ventas dtv

            INNER JOIN ticket_ventas tv
                ON tv.id_ticket_ventas =
                   dtv.id_ticket_ventas

            INNER JOIN producto p
                ON p.idProducto =
                   dtv.idProducto

            LEFT JOIN categorias c
                ON c.id_categorias =
                   p.id_categorias

            WHERE dtv.id_user = ?

              AND tv.id_user = ?

              AND p.id_user = ?

              AND p.Eliminado = 0

              AND tv.estado_venta NOT IN (
                  'Cancelado',
                  'Anulado'
              )

              {$condicionFechaVenta}

            GROUP BY
                p.id_categorias,
                c.nombre

            ORDER BY
                total DESC
        ";


        $stmtGraficoCategorias =
            mysqli_prepare(
                $conexion,
                $sqlGraficoCategorias
            );

        if (!$stmtGraficoCategorias) {

            throw new Exception(
                "No se pudo preparar el gráfico de categorías: " .
                    mysqli_error($conexion)
            );
        }


        $parametrosGraficoCategorias = [

            $idUser,
            $idUser,
            $idUser,
        ];


        $parametrosGraficoCategorias =
            array_merge(
                $parametrosGraficoCategorias,
                $parametrosVenta
            );


        $tiposGraficoCategorias =
            "iii" .
            $tiposVenta;


        mysqli_stmt_bind_param(
            $stmtGraficoCategorias,
            $tiposGraficoCategorias,
            ...$parametrosGraficoCategorias
        );


        if (!mysqli_stmt_execute($stmtGraficoCategorias)) {

            throw new Exception(
                "No se pudo ejecutar el gráfico de categorías: " .
                    mysqli_stmt_error($stmtGraficoCategorias)
            );
        }


        $resultadoGraficoCategorias =
            mysqli_stmt_get_result(
                $stmtGraficoCategorias
            );


        while (
            $fila =
            mysqli_fetch_assoc(
                $resultadoGraficoCategorias
            )
        ) {

            $graficoCategorias[] = [

                "nombre" =>
                $fila["nombre"],

                "total" =>
                (float) $fila["total"],
            ];
        }


        mysqli_stmt_close(
            $stmtGraficoCategorias
        );


        //=====================================================
        // RESPUESTA FINAL
        //=====================================================

        responderJSON([

            "estado" => true,

            "mensaje" =>
            "Estadísticas cargadas correctamente.",

            //=================================================
            // KPI
            //=================================================

            "kpi" =>
            $kpi,

            //=================================================
            // PRODUCTOS
            //=================================================

            "productos" =>
            $productos,

            //=================================================
            // TABLA
            //=================================================

            "tabla" =>
            $tabla,

            //=================================================
            // PAGINACIÓN
            //=================================================

            "paginacion" =>
            $paginacion,

            "total" =>
            $totalProductos,

            "pagina" =>
            $pagina,

            "limite" =>
            $limite,

            "total_paginas" =>
            $totalPaginas,

            //=================================================
            // GRÁFICOS
            //=================================================

            "grafico_ventas" =>
            $graficoVentas,

            "grafico_productos_vendidos" =>
            $graficoProductosVendidos,

            "grafico_ingresos_productos" =>
            $graficoIngresosProductos,

            "grafico_categorias" =>
            $graficoCategorias,
        ]);
    } catch (Throwable $e) {

        responderJSON([

            "estado" => false,

            "mensaje" =>
            $e->getMessage(),
        ]);
    }
}


//=====================================================
// ACCIÓN NO VÁLIDA
//=====================================================

responderJSON([

    "estado" => false,

    "mensaje" =>
    "Acción no válida.",
]);
