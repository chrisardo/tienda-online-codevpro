<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/listar_ofertas_descuentos.php
// Módulo: Ofertas y Descuentos
// Sistema: Inventa
//=====================================================

session_start();

//=====================================================
// CONFIGURACIÓN
//=====================================================

header(
    "Content-Type: application/json; charset=UTF-8"
);

//=====================================================
// VALIDAR SESIÓN
//=====================================================

if (
    !isset($_SESSION["idUser"]) ||
    empty($_SESSION["idUser"])
) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" => "Sesión no válida."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// ID USUARIO
//=====================================================

$idUser = (int) $_SESSION["idUser"];

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !$conexion
) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" =>
            "No se pudo establecer la conexión con la base de datos."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// CHARSET
//=====================================================

if (!$conexion->set_charset("utf8mb4")) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" =>
            "No se pudo configurar el charset de la conexión."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// FUNCIÓN BIND DINÁMICO
//=====================================================

function bindParametros(
    $stmt,
    $tipos,
    &$parametros
) {

    if (
        $tipos === "" ||
        empty($parametros)
    ) {

        return true;
    }

    $referencias = [];

    $referencias[] = $tipos;

    foreach (
        $parametros
        as $indice => $valor
    ) {

        $referencias[] =
            &$parametros[$indice];
    }

    return call_user_func_array(
        [
            $stmt,
            "bind_param"
        ],
        $referencias
    );
}

//=====================================================
// RECIBIR PARÁMETROS
//=====================================================

$pagina = isset($_GET["pagina"])
    ? (int) $_GET["pagina"]
    : 1;

$registros = isset($_GET["registros"])
    ? (int) $_GET["registros"]
    : 10;

$buscar = isset($_GET["buscar"])
    ? trim((string) $_GET["buscar"])
    : "";

//=====================================================
// NUEVO FILTRO OFERTA
//=====================================================

$oferta = isset($_GET["oferta"])
    ? trim((string) $_GET["oferta"])
    : "";

//=====================================================
// NUEVO FILTRO DESCUENTO
//=====================================================

$descuento = isset($_GET["descuento"])
    ? trim((string) $_GET["descuento"])
    : "";

//=====================================================
// FILTROS ADICIONALES
//=====================================================

$categoria = isset($_GET["categoria"])
    ? trim((string) $_GET["categoria"])
    : "";

$marca = isset($_GET["marca"])
    ? trim((string) $_GET["marca"])
    : "";

$sucursal = isset($_GET["sucursal"])
    ? trim((string) $_GET["sucursal"])
    : "";

$stock = isset($_GET["stock"])
    ? trim((string) $_GET["stock"])
    : "";

$orden = isset($_GET["orden"])
    ? trim((string) $_GET["orden"])
    : "";

//=====================================================
// COMPATIBILIDAD CON FILTRO ESTADO ANTIGUO
//=====================================================

$estado = isset($_GET["estado"])
    ? trim((string) $_GET["estado"])
    : "";

//=====================================================
// VALIDAR PAGINACIÓN
//=====================================================

if ($pagina < 1) {
    $pagina = 1;
}

if ($registros < 1) {
    $registros = 10;
}

if ($registros > 100) {
    $registros = 100;
}

//=====================================================
// WHERE
//=====================================================

$where = [];

$parametros = [];

$tipos = "";

//=====================================================
// FILTRO USUARIO
//=====================================================

$where[] = "p.id_user = ?";

$parametros[] = $idUser;

$tipos .= "i";

//=====================================================
// NO ELIMINADOS
//=====================================================

$where[] = "p.Eliminado = 0";

//=====================================================
// BÚSQUEDA
//=====================================================

if ($buscar !== "") {

    $where[] = "
        (
            p.nombre LIKE ?
            OR p.codigo LIKE ?
        )
    ";

    $buscarLike =
        "%" .
        $buscar .
        "%";

    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;

    $tipos .= "ss";
}

//=====================================================
// FILTRO OFERTA
//=====================================================
//
// Valores aceptados:
//
// ""             = todas
// todos          = todas
// oferta         = activas
// ofertas        = activas
// activa         = activas
// activas        = activas
// 1              = activas
//
// sin_oferta     = inactivas
// sin-oferta     = inactivas
// inactiva       = inactivas
// inactivas      = inactivas
// 0              = inactivas
//
//=====================================================

$ofertaNormalizada =
    strtolower(
        trim($oferta)
    );

if (
    $ofertaNormalizada !== "" &&
    $ofertaNormalizada !== "todos" &&
    $ofertaNormalizada !== "todo"
) {

    //-------------------------------------------------
    // OFERTA ACTIVA
    //-------------------------------------------------

    if (
        $ofertaNormalizada === "oferta" ||
        $ofertaNormalizada === "ofertas" ||
        $ofertaNormalizada === "activa" ||
        $ofertaNormalizada === "activas" ||
        $ofertaNormalizada === "1"
    ) {

        $where[] =
            "p.oferta = 1";
    }

    //-------------------------------------------------
    // OFERTA INACTIVA
    //-------------------------------------------------

    elseif (
        $ofertaNormalizada === "sin_oferta" ||
        $ofertaNormalizada === "sin-oferta" ||
        $ofertaNormalizada === "inactiva" ||
        $ofertaNormalizada === "inactivas" ||
        $ofertaNormalizada === "0"
    ) {

        $where[] =
            "p.oferta = 0";
    }
}

//=====================================================
// FILTRO DESCUENTO
//=====================================================
//
// Valores aceptados:
//
// ""             = todos
// todos          = todos
//
// descuento
// descuentos
// con
// con_descuento
// con-descuento
// 1
//
// sin
// sin_descuento
// sin-descuento
// 0
//
//=====================================================

$descuentoNormalizado =
    strtolower(
        trim($descuento)
    );

if (
    $descuentoNormalizado !== "" &&
    $descuentoNormalizado !== "todos" &&
    $descuentoNormalizado !== "todo"
) {

    //-------------------------------------------------
    // CON DESCUENTO
    //-------------------------------------------------

    if (
        $descuentoNormalizado === "descuento" ||
        $descuentoNormalizado === "descuentos" ||
        $descuentoNormalizado === "con" ||
        $descuentoNormalizado === "con_descuento" ||
        $descuentoNormalizado === "con-descuento" ||
        $descuentoNormalizado === "1"
    ) {

        $where[] = "
            p.descuento IS NOT NULL
            AND p.descuento > 0
        ";
    }

    //-------------------------------------------------
    // SIN DESCUENTO
    //-------------------------------------------------

    elseif (
        $descuentoNormalizado === "sin" ||
        $descuentoNormalizado === "sin_descuento" ||
        $descuentoNormalizado === "sin-descuento" ||
        $descuentoNormalizado === "0"
    ) {

        $where[] = "
            (
                p.descuento IS NULL
                OR p.descuento <= 0
            )
        ";
    }
}

//=====================================================
// COMPATIBILIDAD CON ESTADO ANTIGUO
//=====================================================
//
// Solo se utiliza si NO se enviaron los nuevos
// filtros oferta y descuento.
//
//=====================================================

if (
    $ofertaNormalizada === "" &&
    $descuentoNormalizado === "" &&
    $estado !== ""
) {

    $estadoNormalizado =
        strtolower(
            trim($estado)
        );

    //-------------------------------------------------
    // OFERTA ACTIVA
    //-------------------------------------------------

    if (
        $estadoNormalizado === "oferta" ||
        $estadoNormalizado === "ofertas" ||
        $estadoNormalizado === "1" ||
        $estadoNormalizado === "activa" ||
        $estadoNormalizado === "activas"
    ) {

        $where[] =
            "p.oferta = 1";
    }

    //-------------------------------------------------
    // OFERTA INACTIVA
    //-------------------------------------------------

    elseif (
        $estadoNormalizado === "sin_oferta" ||
        $estadoNormalizado === "sin-oferta" ||
        $estadoNormalizado === "0" ||
        $estadoNormalizado === "inactiva" ||
        $estadoNormalizado === "inactivas"
    ) {

        $where[] =
            "p.oferta = 0";
    }

    //-------------------------------------------------
    // CON DESCUENTO
    //-------------------------------------------------

    elseif (
        $estadoNormalizado === "descuento" ||
        $estadoNormalizado === "descuentos" ||
        $estadoNormalizado === "con" ||
        $estadoNormalizado === "con_descuento" ||
        $estadoNormalizado === "con-descuento"
    ) {

        $where[] = "
            p.descuento IS NOT NULL
            AND p.descuento > 0
        ";
    }

    //-------------------------------------------------
    // SIN DESCUENTO
    //-------------------------------------------------

    elseif (
        $estadoNormalizado === "sin_descuento" ||
        $estadoNormalizado === "sin-descuento" ||
        $estadoNormalizado === "sin"
    ) {

        $where[] = "
            (
                p.descuento IS NULL
                OR p.descuento <= 0
            )
        ";
    }
}

//=====================================================
// CATEGORÍA
//=====================================================

if (
    $categoria !== "" &&
    ctype_digit($categoria)
) {

    $where[] =
        "p.id_categorias = ?";

    $parametros[] =
        (int) $categoria;

    $tipos .= "i";
}

//=====================================================
// MARCA
//=====================================================

if (
    $marca !== "" &&
    ctype_digit($marca)
) {

    $where[] =
        "p.id_marca = ?";

    $parametros[] =
        (int) $marca;

    $tipos .= "i";
}

//=====================================================
// SUCURSAL
//=====================================================

if (
    $sucursal !== "" &&
    ctype_digit($sucursal)
) {

    $where[] =
        "p.id_sucursal = ?";

    $parametros[] =
        (int) $sucursal;

    $tipos .= "i";
}

//=====================================================
// STOCK
//=====================================================

$stockNormalizado =
    strtolower(
        trim($stock)
    );

if (
    $stockNormalizado !== "" &&
    $stockNormalizado !== "todos" &&
    $stockNormalizado !== "todo"
) {

    //-------------------------------------------------
    // CON STOCK
    //-------------------------------------------------

    if (
        $stockNormalizado === "con_stock" ||
        $stockNormalizado === "con-stock" ||
        $stockNormalizado === "disponible" ||
        $stockNormalizado === "disponibles" ||
        $stockNormalizado === "1"
    ) {

        $where[] =
            "COALESCE(p.stock, 0) > 0";
    }

    //-------------------------------------------------
    // SIN STOCK
    //-------------------------------------------------

    elseif (
        $stockNormalizado === "sin_stock" ||
        $stockNormalizado === "sin-stock" ||
        $stockNormalizado === "agotado" ||
        $stockNormalizado === "agotados" ||
        $stockNormalizado === "0"
    ) {

        $where[] =
            "COALESCE(p.stock, 0) <= 0";
    }

    //-------------------------------------------------
    // STOCK BAJO
    //-------------------------------------------------

    elseif (
        $stockNormalizado === "bajo" ||
        $stockNormalizado === "bajo_stock" ||
        $stockNormalizado === "bajo-stock"
    ) {

        $where[] = "
            COALESCE(p.stock, 0) > 0
            AND COALESCE(p.stock, 0) <= 10
        ";
    }
}

//=====================================================
// WHERE FINAL
//=====================================================

$whereSQL = "";

if (!empty($where)) {

    $whereSQL =
        "WHERE " .
        implode(
            " AND ",
            $where
        );
}

//=====================================================
// ORDENAMIENTO
//=====================================================

$orderSQL =
    "p.fecha_registro DESC";

//-----------------------------------------------------
// RECIENTES
//-----------------------------------------------------

if (
    $orden === "" ||
    $orden === "recientes" ||
    $orden === "reciente" ||
    $orden === "fecha_desc"
) {

    $orderSQL =
        "p.fecha_registro DESC";
}

//-----------------------------------------------------
// NOMBRE A-Z
//-----------------------------------------------------

elseif (
    $orden === "nombre_asc" ||
    $orden === "nombre_az" ||
    $orden === "az"
) {

    $orderSQL =
        "p.nombre ASC";
}

//-----------------------------------------------------
// NOMBRE Z-A
//-----------------------------------------------------

elseif (
    $orden === "nombre_desc" ||
    $orden === "nombre_za" ||
    $orden === "za"
) {

    $orderSQL =
        "p.nombre DESC";
}

//-----------------------------------------------------
// MAYOR DESCUENTO
//-----------------------------------------------------

elseif (
    $orden === "descuento_desc" ||
    $orden === "mayor_descuento"
) {

    $orderSQL = "
        COALESCE(p.descuento, 0) DESC,
        p.nombre ASC
    ";
}

//-----------------------------------------------------
// MENOR DESCUENTO
//-----------------------------------------------------

elseif (
    $orden === "descuento_asc" ||
    $orden === "menor_descuento"
) {

    $orderSQL = "
        COALESCE(p.descuento, 0) ASC,
        p.nombre ASC
    ";
}

//-----------------------------------------------------
// MAYOR PRECIO
//-----------------------------------------------------

elseif (
    $orden === "precio_desc" ||
    $orden === "mayor_precio"
) {

    $orderSQL = "
        COALESCE(p.precio, 0) DESC,
        p.nombre ASC
    ";
}

//-----------------------------------------------------
// MENOR PRECIO
//-----------------------------------------------------

elseif (
    $orden === "precio_asc" ||
    $orden === "menor_precio"
) {

    $orderSQL = "
        COALESCE(p.precio, 0) ASC,
        p.nombre ASC
    ";
}

//-----------------------------------------------------
// MAYOR STOCK
//-----------------------------------------------------

elseif (
    $orden === "stock_desc" ||
    $orden === "mayor_stock"
) {

    $orderSQL = "
        COALESCE(p.stock, 0) DESC,
        p.nombre ASC
    ";
}

//-----------------------------------------------------
// MENOR STOCK
//-----------------------------------------------------

elseif (
    $orden === "stock_asc" ||
    $orden === "menor_stock"
) {

    $orderSQL = "
        COALESCE(p.stock, 0) ASC,
        p.nombre ASC
    ";
}

//=====================================================
// CONTAR REGISTROS
//=====================================================

$sqlCount = "

    SELECT
        COUNT(*) AS total

    FROM producto p

    $whereSQL

";

//=====================================================
// PREPARAR COUNT
//=====================================================

$stmtCount =
    $conexion->prepare(
        $sqlCount
    );

if (!$stmtCount) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" =>
            "No se pudo preparar el conteo de productos.",
            "error" =>
            $conexion->error
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// BIND COUNT
//=====================================================

if (!empty($parametros)) {

    if (
        !bindParametros(
            $stmtCount,
            $tipos,
            $parametros
        )
    ) {

        echo json_encode(
            [
                "success" => false,
                "mensaje" =>
                "No se pudieron asociar los parámetros del conteo.",
                "error" =>
                $stmtCount->error
            ],
            JSON_UNESCAPED_UNICODE
        );

        $stmtCount->close();

        exit();
    }
}

//=====================================================
// EJECUTAR COUNT
//=====================================================

if (!$stmtCount->execute()) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" =>
            "No se pudo obtener el total de productos.",
            "error" =>
            $stmtCount->error
        ],
        JSON_UNESCAPED_UNICODE
    );

    $stmtCount->close();

    exit();
}

//=====================================================
// RESULTADO COUNT
//=====================================================

$resultadoCount =
    $stmtCount->get_result();

$filaCount =
    $resultadoCount->fetch_assoc();

$totalRegistros =
    (int) (
        $filaCount["total"] ?? 0
    );

$stmtCount->close();

//=====================================================
// TOTAL PÁGINAS
//=====================================================

$totalPaginas =
    $totalRegistros > 0
    ? (int) ceil(
        $totalRegistros /
            $registros
    )
    : 0;

//=====================================================
// CORREGIR PÁGINA
//=====================================================

if (
    $totalPaginas > 0 &&
    $pagina > $totalPaginas
) {

    $pagina =
        $totalPaginas;
}

//=====================================================
// OFFSET
//=====================================================

$offset =
    ($pagina - 1) *
    $registros;

//=====================================================
// CONSULTA PRINCIPAL
//=====================================================

$sql = "

    SELECT

        p.idProducto,

        p.codigo,

        p.nombre,

        p.descripcion,

        p.precio,

        p.precio_anterior,

        p.descuento,

        p.oferta,

        p.destacado,

        p.nuevo,

        p.stock,

        p.envio_gratis,

        p.id_sucursal,

        p.id_categorias,

        p.id_marca,

        p.fecha_registro,

        p.fecha_actualizado,

        c.nombre AS categoria,

        m.nombre AS marca,

        s.nombre AS sucursal

    FROM producto p

    LEFT JOIN categorias c
        ON c.id_categorias = p.id_categorias
        AND c.id_user = p.id_user

    LEFT JOIN marcas m
        ON m.id_marca = p.id_marca
        AND m.id_user = p.id_user

    LEFT JOIN sucursal s
        ON s.id_sucursal = p.id_sucursal
        AND s.id_user = p.id_user

    $whereSQL

    ORDER BY
        $orderSQL

    LIMIT ?, ?

";

//=====================================================
// PREPARAR
//=====================================================

$stmt =
    $conexion->prepare(
        $sql
    );

if (!$stmt) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" =>
            "No se pudo preparar la consulta de productos.",
            "error" =>
            $conexion->error
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}

//=====================================================
// PARÁMETROS CONSULTA
//=====================================================

$parametrosConsulta =
    $parametros;

$parametrosConsulta[] =
    $offset;

$parametrosConsulta[] =
    $registros;

$tiposConsulta =
    $tipos . "ii";

//=====================================================
// BIND
//=====================================================

if (
    !bindParametros(
        $stmt,
        $tiposConsulta,
        $parametrosConsulta
    )
) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" =>
            "No se pudieron asociar los parámetros de la consulta.",
            "error" =>
            $stmt->error
        ],
        JSON_UNESCAPED_UNICODE
    );

    $stmt->close();

    exit();
}

//=====================================================
// EJECUTAR
//=====================================================

if (!$stmt->execute()) {

    echo json_encode(
        [
            "success" => false,
            "mensaje" =>
            "No se pudieron obtener los productos.",
            "error" =>
            $stmt->error
        ],
        JSON_UNESCAPED_UNICODE
    );

    $stmt->close();

    exit();
}

//=====================================================
// RESULTADO
//=====================================================

$resultado =
    $stmt->get_result();

//=====================================================
// HTML
//=====================================================

$html = "";

//=====================================================
// SIN RESULTADOS
//=====================================================

if (
    $resultado->num_rows === 0
) {

    $html = '

        <tr>

            <td
                colspan="8"
                class="text-center py-5">

                <div class="mb-3">

                    <i
                        class="bi bi-tag fs-1 text-muted">
                    </i>

                </div>

                <h6 class="fw-bold mb-1">
                    No se encontraron productos
                </h6>

                <p class="text-muted mb-0">
                    No existen productos que coincidan
                    con los filtros seleccionados.
                </p>

            </td>

        </tr>

    ';
}

//=====================================================
// PRODUCTOS
//=====================================================

else {

    while (
        $producto =
        $resultado->fetch_assoc()
    ) {

        //-------------------------------------------------
        // DATOS
        //-------------------------------------------------

        $idProducto =
            (int) (
                $producto["idProducto"] ??
                0
            );

        $nombre =
            $producto["nombre"] ??
            "";

        $codigo =
            $producto["codigo"] ??
            "";

        $categoriaNombre =
            $producto["categoria"] ??
            "Sin categoría";

        $marcaNombre =
            $producto["marca"] ??
            "Sin marca";

        $sucursalNombre =
            $producto["sucursal"] ??
            "Sin sucursal";

        $precio =
            (float) (
                $producto["precio"] ??
                0
            );

        $precioAnterior =
            isset(
                $producto["precio_anterior"]
            )
            ? (float)
            $producto["precio_anterior"]
            : 0;

        $descuentoProducto =
            (float) (
                $producto["descuento"] ??
                0
            );

        $ofertaProducto =
            (int) (
                $producto["oferta"] ??
                0
            );

        $stockProducto =
            (int) (
                $producto["stock"] ??
                0
            );

        //-------------------------------------------------
        // PRECIO FINAL
        //-------------------------------------------------

        $precioFinal =
            $precio;

        if (
            $descuentoProducto > 0
        ) {

            $precioFinal =
                $precio -
                (
                    $precio *
                    $descuentoProducto /
                    100
                );
        }

        if (
            $precioFinal < 0
        ) {

            $precioFinal = 0;
        }

        //-------------------------------------------------
        // ESCAPAR
        //-------------------------------------------------

        $nombreHTML =
            htmlspecialchars(
                $nombre,
                ENT_QUOTES,
                "UTF-8"
            );

        $codigoHTML =
            htmlspecialchars(
                $codigo,
                ENT_QUOTES,
                "UTF-8"
            );

        $categoriaHTML =
            htmlspecialchars(
                $categoriaNombre,
                ENT_QUOTES,
                "UTF-8"
            );

        //-------------------------------------------------
        // PRECIOS
        //-------------------------------------------------

        $precioHTML =
            number_format(
                $precio,
                2,
                ".",
                ","
            );

        $precioFinalHTML =
            number_format(
                $precioFinal,
                2,
                ".",
                ","
            );

        //-------------------------------------------------
        // DESCUENTO
        //-------------------------------------------------

        if (
            $descuentoProducto > 0
        ) {

            $descuentoHTML =
                number_format(
                    $descuentoProducto,
                    0,
                    ".",
                    ","
                );

            $badgeDescuento = '

                <span
                    class="badge bg-danger-subtle text-danger">

                    <i class="bi bi-percent me-1"></i>

                    ' .
                $descuentoHTML .
                '%

                </span>

            ';
        } else {

            $badgeDescuento = '

                <span
                    class="badge bg-light text-secondary border">

                    Sin descuento

                </span>

            ';
        }

        //-------------------------------------------------
        // ESTADO OFERTA
        //-------------------------------------------------

        if (
            $ofertaProducto === 1
        ) {

            $badgeOferta = '

                <span
                    class="badge bg-success-subtle text-success">

                    <i
                        class="bi bi-check-circle me-1">
                    </i>

                    Activa

                </span>

            ';
        } else {

            $badgeOferta = '

                <span
                    class="badge bg-secondary-subtle text-secondary">

                    <i
                        class="bi bi-x-circle me-1">
                    </i>

                    Inactiva

                </span>

            ';
        }

        //-------------------------------------------------
        // STOCK
        //-------------------------------------------------

        if (
            $stockProducto > 10
        ) {

            $stockBadge = '

                <span
                    class="badge bg-success-subtle text-success">

                    ' .
                $stockProducto .
                '

                </span>

            ';
        } elseif (
            $stockProducto > 0
        ) {

            $stockBadge = '

                <span
                    class="badge bg-warning-subtle text-warning">

                    ' .
                $stockProducto .
                '

                </span>

            ';
        } else {

            $stockBadge = '

                <span
                    class="badge bg-danger-subtle text-danger">

                    0

                </span>

            ';
        }

        //-------------------------------------------------
        // PRECIO NORMAL
        //-------------------------------------------------

        if (
            $ofertaProducto === 1 &&
            $descuentoProducto > 0
        ) {

            $precioNormalHTML = '

                <span
                    class="text-muted text-decoration-line-through">

                    S/ ' .
                $precioHTML .
                '

                </span>

            ';
        } else {

            $precioNormalHTML = '

                <span
                    class="fw-semibold">

                    S/ ' .
                $precioHTML .
                '

                </span>

            ';
        }

        //-------------------------------------------------
        // PRECIO OFERTA
        //-------------------------------------------------

        if (
            $ofertaProducto === 1 &&
            $descuentoProducto > 0
        ) {

            $precioOfertaHTML = '

                <div>

                    <span
                        class="fw-bold text-success">

                        S/ ' .
                $precioFinalHTML .
                '

                    </span>

                </div>

            ';
        } else {

            $precioOfertaHTML = '

                <span class="text-muted">

                    —

                </span>

            ';
        }

        //-------------------------------------------------
        // ACCIONES
        //-------------------------------------------------

        $acciones = '

            <div
                class="d-flex justify-content-end gap-1">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-warning rounded-3"
                    title="Editar oferta"
                    onclick="editarOfertaDescuento(' .
            $idProducto .
            ')">

                    <i class="bi bi-pencil"></i>

                </button>

        ';

        //-------------------------------------------------
        // ACTIVAR / DESACTIVAR
        //-------------------------------------------------

        if (
            $ofertaProducto === 1
        ) {

            $acciones .= '

                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger rounded-3"
                    title="Desactivar oferta"
                    onclick="desactivarOferta(' .
                $idProducto .
                ')">

                    <i class="bi bi-toggle-on"></i>

                </button>

            ';
        } else {

            $acciones .= '

                <button
                    type="button"
                    class="btn btn-sm btn-outline-success rounded-3"
                    title="Activar oferta"
                    onclick="activarOferta(' .
                $idProducto .
                ')">

                    <i class="bi bi-toggle-off"></i>

                </button>

            ';
        }

        $acciones .= '

            </div>

        ';

        //-------------------------------------------------
        // FILA
        //-------------------------------------------------

        $html .= '

            <tr>

                <td class="ps-4">

                    <div
                        class="d-flex align-items-center">

                        <div
                            class="bg-light rounded-3 d-flex align-items-center justify-content-center me-3"
                            style="width:48px;height:48px;">

                            <i
                                class="bi bi-box-seam text-muted fs-5">
                            </i>

                        </div>

                        <div>

                            <div
                                class="fw-semibold">

                                ' .
            $nombreHTML .
            '

                            </div>

                            <small
                                class="text-muted">

                                Código:
                                ' .
            $codigoHTML .
            '

                            </small>

                        </div>

                    </div>

                </td>

                <td>

                    <span
                        class="text-muted">

                        ' .
            $categoriaHTML .
            '

                    </span>

                </td>

                <td
                    class="text-center">

                    ' .
            $precioNormalHTML .
            '

                </td>

                <td
                    class="text-center">

                    ' .
            $precioOfertaHTML .
            '

                </td>

                <td
                    class="text-center">

                    ' .
            $badgeDescuento .
            '

                </td>

                <td
                    class="text-center">

                    ' .
            $badgeOferta .
            '

                </td>

                <td
                    class="text-center">

                    ' .
            $stockBadge .
            '

                </td>

                <td
                    class="text-end pe-4">

                    ' .
            $acciones .
            '

                </td>

            </tr>

        ';
    }
}

//=====================================================
// CERRAR STATEMENT
//=====================================================

$stmt->close();

//=====================================================
// INFORMACIÓN PAGINACIÓN
//=====================================================

$desde = 0;

$hasta = 0;

if (
    $totalRegistros > 0
) {

    $desde =
        $offset + 1;

    $hasta =
        min(
            $offset +
                $registros,
            $totalRegistros
        );
}

//=====================================================
// HTML PAGINACIÓN
//=====================================================

$paginacionHTML = "";

if (
    $totalPaginas > 1
) {

    //-------------------------------------------------
    // ANTERIOR
    //-------------------------------------------------

    $paginaAnterior =
        $pagina - 1;

    if (
        $paginaAnterior < 1
    ) {

        $paginaAnterior = 1;
    }

    $paginacionHTML .= '

        <li class="page-item ' .
        (
            $pagina <= 1
            ? "disabled"
            : ""
        ) .
        '">

            <a
                href="#"
                class="page-link"
                data-pagina="' .
        $paginaAnterior .
        '">

                <i
                    class="bi bi-chevron-left">
                </i>

            </a>

        </li>

    ';

    //-------------------------------------------------
    // PÁGINAS
    //-------------------------------------------------

    for (
        $i = 1;
        $i <= $totalPaginas;
        $i++
    ) {

        $paginacionHTML .= '

            <li class="page-item ' .
            (
                $i === $pagina
                ? "active"
                : ""
            ) .
            '">

                <a
                    href="#"
                    class="page-link"
                    data-pagina="' .
            $i .
            '">

                    ' .
            $i .
            '

                </a>

            </li>

        ';
    }

    //-------------------------------------------------
    // SIGUIENTE
    //-------------------------------------------------

    $paginaSiguiente =
        $pagina + 1;

    if (
        $paginaSiguiente >
        $totalPaginas
    ) {

        $paginaSiguiente =
            $totalPaginas;
    }

    $paginacionHTML .= '

        <li class="page-item ' .
        (
            $pagina >= $totalPaginas
            ? "disabled"
            : ""
        ) .
        '">

            <a
                href="#"
                class="page-link"
                data-pagina="' .
        $paginaSiguiente .
        '">

                <i
                    class="bi bi-chevron-right">
                </i>

            </a>

        </li>

    ';
}

//=====================================================
// RESPUESTA
//=====================================================

echo json_encode(
    [

        "success" => true,

        "mensaje" =>
        "Productos cargados correctamente.",

        "html" =>
        $html,

        "tabla" =>
        $html,

        "paginacion" =>
        $paginacionHTML,

        "total_registros" =>
        $totalRegistros,

        "total_paginas" =>
        $totalPaginas,

        "pagina_actual" =>
        $pagina,

        "registros_por_pagina" =>
        $registros,

        "desde" =>
        $desde,

        "hasta" =>
        $hasta

    ],
    JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
);

exit();
