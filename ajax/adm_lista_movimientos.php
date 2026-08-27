<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_lista_movimientos.php
// Módulo: Ingresos y Gastos
// Sistema: Inventa
//=====================================================


//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

header(
    'Content-Type: application/json; charset=utf-8'
);


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR USUARIO
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Usuario no identificado."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// PARÁMETROS
//=====================================================

$pagina = isset($_POST["pagina"])
    ? (int) $_POST["pagina"]
    : 1;


$registrosPorPagina = isset($_POST["registrosPorPagina"])
    ? (int) $_POST["registrosPorPagina"]
    : 10;


//=====================================================
// VALIDAR PAGINACIÓN
//=====================================================

if ($pagina < 1) {
    $pagina = 1;
}


if ($registrosPorPagina < 1) {
    $registrosPorPagina = 5;
}


if ($registrosPorPagina > 100) {
    $registrosPorPagina = 100;
}


$offset =
    ($pagina - 1) *
    $registrosPorPagina;


//=====================================================
// FILTROS
//=====================================================

$filtroTipo =
    trim($_POST["tipo"] ?? "");


$filtroCuenta =
    (int) ($_POST["id_cuenta_bancaria"] ?? 0);


$filtroCategoria =
    (int) ($_POST["id_categoria"] ?? 0);


$filtroMetodoPago =
    (int) ($_POST["id_metodo_pago"] ?? 0);


$filtroProveedor =
    (int) ($_POST["id_proveedor"] ?? 0);


$filtroFechaDesde =
    trim($_POST["fecha_desde"] ?? "");


$filtroFechaHasta =
    trim($_POST["fecha_hasta"] ?? "");


$filtroBusqueda =
    trim($_POST["busqueda"] ?? "");


//=====================================================
// CONSTRUIR WHERE
//=====================================================

$where = [];

$parametros = [];

$tipos = "";


//=====================================================
// USUARIO
//=====================================================

$where[] = "dg.id_user = ?";

$parametros[] = $idUser;

$tipos .= "i";


//=====================================================
// ELIMINADO
//=====================================================

$where[] = "
    (
        dg.Eliminado = 0
        OR dg.Eliminado IS NULL
    )
";


//=====================================================
// TIPO
//=====================================================

if ($filtroTipo !== "") {

    $where[] = "
        UPPER(TRIM(dg.tipo)) = UPPER(?)
    ";

    $parametros[] = $filtroTipo;

    $tipos .= "s";
}


//=====================================================
// CUENTA BANCARIA
//=====================================================

if ($filtroCuenta > 0) {

    $where[] = "
        dg.id_cuenta_bancaria = ?
    ";

    $parametros[] = $filtroCuenta;

    $tipos .= "i";
}


//=====================================================
// CATEGORÍA
//=====================================================

if ($filtroCategoria > 0) {

    $where[] = "
        dg.id_categoria = ?
    ";

    $parametros[] = $filtroCategoria;

    $tipos .= "i";
}


//=====================================================
// MÉTODO DE PAGO
//=====================================================

if ($filtroMetodoPago > 0) {

    $where[] = "
        dg.id_metodo_pago = ?
    ";

    $parametros[] = $filtroMetodoPago;

    $tipos .= "i";
}


//=====================================================
// PROVEEDOR
//=====================================================

if ($filtroProveedor > 0) {

    $where[] = "
        dg.id_proveedor = ?
    ";

    $parametros[] = $filtroProveedor;

    $tipos .= "i";
}


//=====================================================
// FECHA DESDE
//=====================================================

if ($filtroFechaDesde !== "") {

    $fechaDesde = DateTime::createFromFormat(
        "d/m/Y",
        $filtroFechaDesde
    );

    if ($fechaDesde !== false) {

        $fechaDesdeSQL =
            $fechaDesde->format("Y-m-d");

        $where[] = "
            dg.fecha >= ?
        ";

        $parametros[] =
            $fechaDesdeSQL;

        $tipos .= "s";
    }
}


//=====================================================
// FECHA HASTA
//=====================================================

if ($filtroFechaHasta !== "") {

    $fechaHasta = DateTime::createFromFormat(
        "d/m/Y",
        $filtroFechaHasta
    );

    if ($fechaHasta !== false) {

        $fechaHastaSQL =
            $fechaHasta->format("Y-m-d");

        $where[] = "
            dg.fecha <= ?
        ";

        $parametros[] =
            $fechaHastaSQL;

        $tipos .= "s";
    }
}


//=====================================================
// BÚSQUEDA
//=====================================================

if ($filtroBusqueda !== "") {

    $where[] = "
        (
            dg.concepto LIKE ?
            OR dg.descripcion LIKE ?
            OR cb.nombre LIKE ?
            OR cat.nombre LIKE ?
            OR prov.nombre LIKE ?
            OR mp.nombre LIKE ?
        )
    ";

    $busqueda =
        "%" .
        $filtroBusqueda .
        "%";

    $parametros[] = $busqueda;
    $parametros[] = $busqueda;
    $parametros[] = $busqueda;
    $parametros[] = $busqueda;
    $parametros[] = $busqueda;
    $parametros[] = $busqueda;

    $tipos .= "ssssss";
}


//=====================================================
// WHERE FINAL
//=====================================================

$whereSQL =
    implode(
        " AND ",
        $where
    );


//=====================================================
// TRY PRINCIPAL
//=====================================================

try {


    //=================================================
    // CONTAR TOTAL DE MOVIMIENTOS
    //=================================================

    $sqlTotal = "

        SELECT COUNT(*) AS total

        FROM deposito_gasto dg

        LEFT JOIN cuenta_banco cb
            ON cb.id_cuenta_bancaria =
               dg.id_cuenta_bancaria

        LEFT JOIN categorias cat
            ON cat.id_categorias =
               dg.id_categoria

        LEFT JOIN provedores prov
            ON prov.id_provedor =
               dg.id_proveedor

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               dg.id_metodo_pago

        WHERE
            $whereSQL

    ";


    //=================================================
    // PREPARAR TOTAL
    //=================================================

    $stmtTotal =
        $conexion->prepare($sqlTotal);


    if (!$stmtTotal) {

        throw new Exception(
            "No se pudo preparar la consulta de total."
        );
    }


    //=================================================
    // BIND TOTAL
    //=================================================

    if (!empty($parametros)) {

        $stmtTotal->bind_param(
            $tipos,
            ...$parametros
        );
    }


    //=================================================
    // EJECUTAR TOTAL
    //=================================================

    if (!$stmtTotal->execute()) {

        throw new Exception(
            "No se pudo ejecutar la consulta de total."
        );
    }


    //=================================================
    // RESULTADO TOTAL
    //=================================================

    $resultadoTotal =
        $stmtTotal->get_result();


    $filaTotal =
        $resultadoTotal->fetch_assoc();


    $totalRegistros =
        (int) (
            $filaTotal["total"] ?? 0
        );


    $stmtTotal->close();


    //=================================================
    // CALCULAR PAGINACIÓN
    //=================================================

    $totalPaginas =
        $totalRegistros > 0
        ? (int) ceil(
            $totalRegistros /
                $registrosPorPagina
        )
        : 1;


    //=================================================
    // CORREGIR PÁGINA
    //=================================================

    if ($pagina > $totalPaginas) {

        $pagina =
            $totalPaginas;

        $offset =
            ($pagina - 1) *
            $registrosPorPagina;
    }


    //=================================================
    // CONSULTAR MOVIMIENTOS
    //=================================================

    $sql = "

        SELECT

            dg.id_deposito,

            dg.id_cuenta_bancaria,

            dg.id_proveedor,

            dg.id_categoria,

            dg.id_metodo_pago,

            dg.fecha,

            dg.concepto,

            dg.descripcion,

            dg.monto_pago,

            dg.tipo,

            dg.id_user,

            cb.nombre AS cuenta_bancaria,

            cat.nombre AS categoria,

            prov.nombre AS proveedor,

            mp.nombre AS metodo_pago

        FROM deposito_gasto dg


        LEFT JOIN cuenta_banco cb

            ON cb.id_cuenta_bancaria =
               dg.id_cuenta_bancaria


        LEFT JOIN categorias cat

            ON cat.id_categorias =
               dg.id_categoria


        LEFT JOIN provedores prov

            ON prov.id_provedor =
               dg.id_proveedor


        LEFT JOIN metodo_pago mp

            ON mp.id_metodo_pago =
               dg.id_metodo_pago


        WHERE

            $whereSQL


        ORDER BY

            dg.fecha DESC,

            dg.id_deposito DESC


        LIMIT ?, ?

    ";


    //=================================================
    // PARÁMETROS DE CONSULTA
    //=================================================

    $parametrosConsulta =
        $parametros;

    $parametrosConsulta[] =
        $offset;

    $parametrosConsulta[] =
        $registrosPorPagina;


    $tiposConsulta =
        $tipos . "ii";


    //=================================================
    // PREPARAR CONSULTA
    //=================================================

    $stmt =
        $conexion->prepare($sql);


    if (!$stmt) {

        throw new Exception(
            "No se pudo preparar la consulta de movimientos."
        );
    }


    //=================================================
    // BIND CONSULTA
    //=================================================

    $stmt->bind_param(
        $tiposConsulta,
        ...$parametrosConsulta
    );


    //=================================================
    // EJECUTAR
    //=================================================

    if (!$stmt->execute()) {

        throw new Exception(
            "No se pudo ejecutar la consulta de movimientos."
        );
    }


    //=================================================
    // RESULTADO
    //=================================================

    $resultado =
        $stmt->get_result();


    $movimientos = [];


    //=================================================
    // RECORRER MOVIMIENTOS
    //=================================================

    while (
        $fila =
        $resultado->fetch_assoc()
    ) {

        //=============================================
        // TIPO
        //=============================================

        $tipoMovimiento =
            strtoupper(
                trim(
                    $fila["tipo"] ?? ""
                )
            );


        //=============================================
        // FORMATEAR FECHA
        //=============================================

        $fechaMostrar = "";

        if (!empty($fila["fecha"])) {

            $fecha =
                DateTime::createFromFormat(
                    "Y-m-d",
                    $fila["fecha"]
                );

            if ($fecha !== false) {

                $fechaMostrar =
                    $fecha->format("d/m/Y");
            } else {

                $fechaMostrar =
                    $fila["fecha"];
            }
        }


        //=============================================
        // MONTO
        //=============================================

        $monto =
            (float) (
                $fila["monto_pago"] ?? 0
            );


        //=============================================
        // MOVIMIENTO
        //=============================================

        $movimientos[] = [

            "id_deposito" =>
            (int)
            $fila["id_deposito"],

            "id_cuenta_bancaria" =>
            (int)
            (
                $fila["id_cuenta_bancaria"]
                ?? 0
            ),

            "id_proveedor" =>
            (int)
            (
                $fila["id_proveedor"]
                ?? 0
            ),

            "id_categoria" =>
            (int)
            (
                $fila["id_categoria"]
                ?? 0
            ),

            "id_metodo_pago" =>
            (int)
            (
                $fila["id_metodo_pago"]
                ?? 0
            ),

            "fecha" =>
            $fila["fecha"],

            "fecha_formateada" =>
            $fechaMostrar,

            "concepto" =>
            $fila["concepto"] ?? "",

            "descripcion" =>
            $fila["descripcion"] ?? "",

            "monto_pago" =>
            $monto,

            "tipo" =>
            $tipoMovimiento,

            "cuenta_bancaria" =>
            $fila["cuenta_bancaria"]
                ?? "Sin cuenta",

            "categoria" =>
            $fila["categoria"]
                ?? "Sin categoría",

            "proveedor" =>
            $fila["proveedor"]
                ?? "Sin proveedor",

            "metodo_pago" =>
            $fila["metodo_pago"]
                ?? "Sin método"

        ];
    }


    //=================================================
    // CERRAR
    //=================================================

    $stmt->close();


    //=================================================
    // CALCULAR REGISTROS MOSTRADOS
    //=================================================

    $registrosMostrados =
        count($movimientos);


    //=================================================
    // PRIMER REGISTRO
    //=================================================

    $desde = 0;

    if ($totalRegistros > 0) {

        $desde =
            $offset + 1;
    }


    //=================================================
    // ÚLTIMO REGISTRO
    //=================================================

    $hasta =
        $offset +
        $registrosMostrados;


    //=================================================
    // RESPUESTA
    //=================================================

    echo json_encode(
        [

            "status" =>
            "success",

            "datos" =>
            $movimientos,

            "paginacion" => [

                "pagina_actual" =>
                $pagina,

                "registros_por_pagina" =>
                $registrosPorPagina,

                "total_registros" =>
                $totalRegistros,

                "total_paginas" =>
                $totalPaginas,

                "desde" =>
                $desde,

                "hasta" =>
                $hasta,

                "hay_anterior" => ($pagina > 1),

                "hay_siguiente" => ($pagina < $totalPaginas)

            ]

        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
} catch (Exception $e) {


    //=================================================
    // ERROR
    //=================================================

    echo json_encode(
        [

            "status" =>
            "error",

            "mensaje" =>
            "No se pudieron obtener los movimientos."

        ],
        JSON_UNESCAPED_UNICODE
    );


    exit;
}
