<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/exportar_estadisticas_productos.php
// Módulo: Estadísticas de Productos
// Sistema: Inventa
//=====================================================

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once "../controladores/conexion.php";

session_start();

//=====================================================
// CONFIGURACIÓN
//=====================================================

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    //=================================================
    // VALIDAR CONEXIÓN
    //=================================================

    if (!isset($conexion) || !$conexion) {

        throw new Exception(
            "No se pudo establecer la conexión con la base de datos."
        );
    }

    $conexion->set_charset("utf8mb4");

    //=================================================
    // VALIDAR SESIÓN
    //=================================================

    if (!isset($_SESSION["id_user"]) || !is_numeric($_SESSION["id_user"])) {

        throw new Exception(
            "La sesión ha expirado. Inicia sesión nuevamente."
        );
    }

    $idUser = (int) $_SESSION["id_user"];

    //=================================================
    // ACCIÓN
    //=================================================

    $accion = $_POST["accion"] ?? "";

    if ($accion !== "exportar") {

        throw new Exception(
            "Acción no válida."
        );
    }

    //=================================================
    // FILTROS
    //=================================================

    $buscar = trim(
        (string) ($_POST["buscar"] ?? "")
    );

    $categoria = isset($_POST["categoria"])
        ? (int) $_POST["categoria"]
        : 0;

    $marca = isset($_POST["marca"])
        ? (int) $_POST["marca"]
        : 0;

    $sucursal = isset($_POST["sucursal"])
        ? (int) $_POST["sucursal"]
        : 0;

    $tipo = trim(
        (string) ($_POST["tipo"] ?? "")
    );

    $stock = trim(
        (string) ($_POST["stock"] ?? "")
    );

    $ordenar = trim(
        (string) ($_POST["ordenar"] ?? "ventas_desc")
    );

    $fechaInicio = trim(
        (string) ($_POST["fecha_inicio"] ?? "")
    );

    $fechaFin = trim(
        (string) ($_POST["fecha_fin"] ?? "")
    );

    $alcance = trim(
        (string) ($_POST["alcance"] ?? "todos")
    );

    //=================================================
    // PAGINACIÓN
    //=================================================

    $pagina = isset($_POST["pagina"])
        ? max(1, (int) $_POST["pagina"])
        : 1;

    $limite = isset($_POST["limite"])
        ? max(1, (int) $_POST["limite"])
        : 10;

    $offset = ($pagina - 1) * $limite;

    //=================================================
    // CAMPOS
    //=================================================

    $campos = $_POST["campos"] ?? [];

    if (!is_array($campos)) {
        $campos = [];
    }

    //=================================================
    // CAMPOS PERMITIDOS
    //=================================================

    $camposPermitidos = [

        "codigo",
        "nombre",
        "categoria",
        "marca",
        "sucursal",
        "tipo",
        "stock",
        "costo",
        "precio",
        "valor_inventario",
        "vendidos",
        "ingresos",
        "ganancia",
        "margen"

    ];

    //=================================================
    // FILTRAR CAMPOS
    //=================================================

    $camposSeleccionados = [];

    foreach ($campos as $campo) {

        $campo = trim((string) $campo);

        if (
            in_array(
                $campo,
                $camposPermitidos,
                true
            )
        ) {

            $camposSeleccionados[] = $campo;
        }
    }

    //=================================================
    // VALIDAR CAMPOS
    //=================================================

    if (empty($camposSeleccionados)) {

        throw new Exception(
            "Debes seleccionar al menos un campo para exportar."
        );
    }

    //=================================================
    // FECHAS
    //=================================================

    if ($fechaInicio !== "") {

        $fechaInicioObj = DateTime::createFromFormat(
            "Y-m-d",
            $fechaInicio
        );

        if (
            !$fechaInicioObj ||
            $fechaInicioObj->format("Y-m-d") !== $fechaInicio
        ) {

            throw new Exception(
                "La fecha inicial no es válida."
            );
        }
    }

    if ($fechaFin !== "") {

        $fechaFinObj = DateTime::createFromFormat(
            "Y-m-d",
            $fechaFin
        );

        if (
            !$fechaFinObj ||
            $fechaFinObj->format("Y-m-d") !== $fechaFin
        ) {

            throw new Exception(
                "La fecha final no es válida."
            );
        }
    }

    if (
        $fechaInicio !== "" &&
        $fechaFin !== "" &&
        $fechaInicio > $fechaFin
    ) {

        throw new Exception(
            "La fecha inicial no puede ser mayor que la fecha final."
        );
    }

    //=================================================
    // VALIDAR ALCANCE
    //=================================================

    if (
        $alcance !== "todos" &&
        $alcance !== "pagina"
    ) {

        $alcance = "todos";
    }

    //=================================================
    // SELECT PRINCIPAL
    //=================================================

    /*
     * Las estadísticas se calculan desde:
     *
     * producto
     * categorias
     * marcas
     * sucursal
     *
     * y las ventas desde:
     *
     * detalle_ticket_ventas
     * ticket_ventas
     *
     * Se utiliza LEFT JOIN para que también aparezcan
     * productos que todavía no tienen ventas.
     */

    $sql = "
        SELECT

            p.idProducto,

            p.codigo,

            p.nombre,

            p.tipo,

            p.stock,

            p.costo_compra,

            p.precio,

            p.id_categorias,

            p.id_marca,

            p.id_sucursal,

            c.nombre AS categoria,

            m.nombre AS marca,

            s.nombre AS sucursal,

            COALESCE(v.vendidos, 0) AS vendidos,

            COALESCE(v.ingresos, 0) AS ingresos,

            COALESCE(
                v.ganancia,
                0
            ) AS ganancia,

            CASE

                WHEN COALESCE(v.ingresos, 0) > 0

                THEN
                    (
                        COALESCE(v.ganancia, 0)
                        /
                        v.ingresos
                    ) * 100

                ELSE 0

            END AS margen

        FROM producto p

        LEFT JOIN categorias c
            ON c.id_categorias = p.id_categorias

        LEFT JOIN marcas m
            ON m.id_marca = p.id_marca

        LEFT JOIN sucursal s
            ON s.id_sucursal = p.id_sucursal

        LEFT JOIN (

            SELECT

                dtv.idProducto,

                SUM(
                    dtv.cantidad
                ) AS vendidos,

                SUM(
                    dtv.cantidad * dtv.precio
                ) AS ingresos,

                SUM(
                    dtv.cantidad *
                    (
                        dtv.precio -
                        COALESCE(pv.costo_compra, 0)
                    )
                ) AS ganancia

            FROM detalle_ticket_ventas dtv

            INNER JOIN ticket_ventas tv
                ON tv.id_ticket = dtv.id_ticket

            INNER JOIN producto pv
                ON pv.idProducto = dtv.idProducto

            WHERE
                tv.id_user = ?

                AND (
                    tv.estado IS NULL
                    OR tv.estado <> 'Cancelado'
                )
    ";

    //=================================================
    // FECHA INICIAL
    //=================================================

    $parametros = [$idUser];

    $tipos = "i";

    if ($fechaInicio !== "") {

        $sql .= "
            AND DATE(tv.fecha) >= ?
        ";

        $parametros[] = $fechaInicio;

        $tipos .= "s";
    }

    //=================================================
    // FECHA FINAL
    //=================================================

    if ($fechaFin !== "") {

        $sql .= "
            AND DATE(tv.fecha) <= ?
        ";

        $parametros[] = $fechaFin;

        $tipos .= "s";
    }

    //=================================================
    // CERRAR SUBCONSULTA
    //=================================================

    $sql .= "

            GROUP BY
                dtv.idProducto

        ) v
            ON v.idProducto = p.idProducto

        WHERE

            p.Eliminado = 0

            AND p.id_user = ?
    ";

    $parametros[] = $idUser;

    $tipos .= "i";

    //=================================================
    // BUSCAR
    //=================================================

    if ($buscar !== "") {

        $sql .= "
            AND (
                p.nombre LIKE ?
                OR p.codigo LIKE ?
            )
        ";

        $buscarLike = "%" . $buscar . "%";

        $parametros[] = $buscarLike;
        $parametros[] = $buscarLike;

        $tipos .= "ss";
    }

    //=================================================
    // CATEGORÍA
    //=================================================

    if ($categoria > 0) {

        $sql .= "
            AND p.id_categorias = ?
        ";

        $parametros[] = $categoria;

        $tipos .= "i";
    }

    //=================================================
    // MARCA
    //=================================================

    if ($marca > 0) {

        $sql .= "
            AND p.id_marca = ?
        ";

        $parametros[] = $marca;

        $tipos .= "i";
    }

    //=================================================
    // SUCURSAL
    //=================================================

    if ($sucursal > 0) {

        $sql .= "
            AND p.id_sucursal = ?
        ";

        $parametros[] = $sucursal;

        $tipos .= "i";
    }

    //=================================================
    // TIPO
    //=================================================

    if ($tipo !== "") {

        $sql .= "
            AND p.tipo = ?
        ";

        $parametros[] = $tipo;

        $tipos .= "s";
    }

    //=================================================
    // STOCK
    //=================================================

    switch ($stock) {

        case "agotado":

            $sql .= "
                AND p.stock <= 0
            ";

            break;

        case "bajo":

            $sql .= "
                AND p.stock > 0
                AND p.stock <= 5
            ";

            break;

        case "normal":

            $sql .= "
                AND p.stock > 5
            ";

            break;

        case "con_stock":

            $sql .= "
                AND p.stock > 0
            ";

            break;

        case "sin_stock":

            $sql .= "
                AND p.stock <= 0
            ";

            break;
    }

    //=================================================
    // ORDEN
    //=================================================

    switch ($ordenar) {

        case "nombre_asc":

            $sql .= "
                ORDER BY p.nombre ASC
            ";

            break;

        case "nombre_desc":

            $sql .= "
                ORDER BY p.nombre DESC
            ";

            break;

        case "ventas_asc":

            $sql .= "
                ORDER BY vendidos ASC, p.nombre ASC
            ";

            break;

        case "ventas_desc":

            $sql .= "
                ORDER BY vendidos DESC, p.nombre ASC
            ";

            break;

        case "ingresos_asc":

            $sql .= "
                ORDER BY ingresos ASC, p.nombre ASC
            ";

            break;

        case "ingresos_desc":

            $sql .= "
                ORDER BY ingresos DESC, p.nombre ASC
            ";

            break;

        case "stock_asc":

            $sql .= "
                ORDER BY p.stock ASC, p.nombre ASC
            ";

            break;

        case "stock_desc":

            $sql .= "
                ORDER BY p.stock DESC, p.nombre ASC
            ";

            break;

        default:

            $sql .= "
                ORDER BY vendidos DESC, p.nombre ASC
            ";

            break;
    }

    //=================================================
    // ALCANCE
    //=================================================

    if ($alcance === "pagina") {

        $sql .= "
            LIMIT ? OFFSET ?
        ";

        $parametros[] = $limite;
        $parametros[] = $offset;

        $tipos .= "ii";
    }

    //=================================================
    // PREPARAR
    //=================================================

    $stmt = $conexion->prepare($sql);

    //=================================================
    // BIND DINÁMICO
    //=================================================

    $bind = [];

    $bind[] = $tipos;

    foreach ($parametros as $key => $valor) {

        $bind[] = &$parametros[$key];
    }

    call_user_func_array(
        [$stmt, "bind_param"],
        $bind
    );

    //=================================================
    // EJECUTAR
    //=================================================

    $stmt->execute();

    $resultado = $stmt->get_result();

    //=================================================
    // CONSTRUIR DATOS
    //=================================================

    $filas = [];

    while ($fila = $resultado->fetch_assoc()) {

        //=============================================
        // VALORES NUMÉRICOS
        //=============================================

        $stockActual = (int) (
            $fila["stock"] ?? 0
        );

        $costo = (float) (
            $fila["costo_compra"] ?? 0
        );

        $precio = (float) (
            $fila["precio"] ?? 0
        );

        $vendidos = (int) (
            $fila["vendidos"] ?? 0
        );

        $ingresos = (float) (
            $fila["ingresos"] ?? 0
        );

        $ganancia = (float) (
            $fila["ganancia"] ?? 0
        );

        $valorInventario =
            $stockActual * $costo;

        $margen = 0;

        if ($ingresos > 0) {

            $margen =
                ($ganancia / $ingresos) * 100;
        }

        //=============================================
        // FILA
        //=============================================

        $filas[] = [

            "codigo" =>
            $fila["codigo"] ?? "",

            "nombre" =>
            $fila["nombre"] ?? "",

            "categoria" =>
            $fila["categoria"] ?? "",

            "marca" =>
            $fila["marca"] ?? "",

            "sucursal" =>
            $fila["sucursal"] ?? "",

            "tipo" =>
            $fila["tipo"] ?? "",

            "stock" =>
            $stockActual,

            "costo" =>
            round($costo, 2),

            "precio" =>
            round($precio, 2),

            "valor_inventario" =>
            round($valorInventario, 2),

            "vendidos" =>
            $vendidos,

            "ingresos" =>
            round($ingresos, 2),

            "ganancia" =>
            round($ganancia, 2),

            "margen" =>
            round($margen, 2)
        ];
    }

    //=================================================
    // CERRAR
    //=================================================

    $stmt->close();

    //=================================================
    // RESPUESTA
    //=================================================

    echo json_encode(

        [
            "estado" => true,

            "mensaje" =>
            "Estadísticas obtenidas correctamente.",

            "total" =>
            count($filas),

            "filas" =>
            $filas
        ],

        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
} catch (Throwable $e) {

    //=================================================
    // ERROR
    //=================================================

    http_response_code(400);

    echo json_encode(

        [
            "estado" => false,

            "mensaje" =>
            $e->getMessage(),

            "filas" =>
            []
        ],

        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}
