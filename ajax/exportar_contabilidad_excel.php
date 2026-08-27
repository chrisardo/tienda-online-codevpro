<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/exportar_contabilidad_excel.php
// Módulo: Contabilidad
// Sistema: Inventa
//=====================================================

declare(strict_types=1);

//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// CARGAR PHPSPREADSHEET
//=====================================================
//
// Instalación esperada:
//
// composer require phpoffice/phpspreadsheet
//
// Y:
//
// /vendor/autoload.php
//
//=====================================================

$autoload = __DIR__ . "/../vendor/autoload.php";

if (!file_exists($autoload)) {

    http_response_code(500);

    die("No se encontró PhpSpreadsheet. " .
        "Verifique la instalación de Composer.");
}

require_once $autoload;

//=====================================================
// CLASES PHPSPREADSHEET
//=====================================================

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !$conexion
) {

    http_response_code(500);

    die("No se pudo establecer la conexión con la base de datos.");
}

//=====================================================
// VALIDAR USUARIO
//=====================================================

$idUser = (int)($_SESSION["idUser"] ?? 0);

if ($idUser <= 0) {

    http_response_code(401);

    die("La sesión del usuario no es válida.");
}

//=====================================================
// PARÁMETROS
//=====================================================

$anio = trim(
    (string)($_GET["anio"] ?? "")
);

$periodo = trim(
    (string)($_GET["periodo"] ?? "todos")
);

$fechaInicio = trim(
    (string)($_GET["fecha_inicio"] ?? "")
);

$fechaFin = trim(
    (string)($_GET["fecha_fin"] ?? "")
);

//=====================================================
// VALIDAR AÑO
//=====================================================

if (
    $anio !== "" &&
    !preg_match("/^\d{4}$/", $anio)
) {

    $anio = "";
}

//=====================================================
// VALIDAR PERÍODO
//=====================================================

if (
    $periodo !== "todos" &&
    !preg_match(
        "/^(0[1-9]|1[0-2])$/",
        $periodo
    )
) {

    $periodo = "todos";
}

//=====================================================
// VALIDAR FECHA
//=====================================================

function fechaValidaExcel(
    string $fecha
): bool {

    if ($fecha === "") {
        return false;
    }

    $objeto =
        DateTime::createFromFormat(
            "Y-m-d",
            $fecha
        );

    return (
        $objeto !== false &&
        $objeto->format("Y-m-d") === $fecha
    );
}

//=====================================================
// NORMALIZAR FECHAS
//=====================================================

if (
    !fechaValidaExcel($fechaInicio)
) {

    $fechaInicio = "";
}

if (
    !fechaValidaExcel($fechaFin)
) {

    $fechaFin = "";
}

//=====================================================
// CORREGIR RANGO INVERTIDO
//=====================================================

if (
    $fechaInicio !== "" &&
    $fechaFin !== "" &&
    $fechaInicio > $fechaFin
) {

    $temporal = $fechaInicio;

    $fechaInicio = $fechaFin;

    $fechaFin = $temporal;
}

//=====================================================
// FUNCIÓN CONSULTA PREPARADA
//=====================================================

function ejecutarExcel(
    mysqli $conexion,
    string $sql,
    string $types = "",
    array $params = []
): mysqli_result {

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    if (!$stmt) {

        throw new Exception(
            mysqli_error($conexion)
        );
    }

    if (
        $types !== "" &&
        count($params) > 0
    ) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    if (
        !mysqli_stmt_execute($stmt)
    ) {

        $error =
            mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        throw new Exception($error);
    }

    $resultado =
        mysqli_stmt_get_result($stmt);

    if ($resultado === false) {

        $error =
            mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);

        throw new Exception($error);
    }

    mysqli_stmt_close($stmt);

    return $resultado;
}

//=====================================================
// CONSTRUIR FILTROS
//=====================================================

$whereDG =
    "dg.id_user = ?
     AND COALESCE(dg.Eliminado, 0) = 0";

$paramsDG = [
    $idUser
];

$typesDG = "i";

$whereTV =
    "tv.id_user = ?
     AND (
        tv.estado_venta IS NULL
        OR LOWER(TRIM(tv.estado_venta))
        NOT IN (
            'cancelado',
            'cancelada',
            'anulado',
            'anulada'
        )
     )";

$paramsTV = [
    $idUser
];

$typesTV = "i";

$wherePE =
    "pe.id_user = ?
     AND UPPER(TRIM(pe.estado)) = 'PAGADO'";

$paramsPE = [
    $idUser
];

$typesPE = "i";

//=====================================================
// FILTRO AÑO
//=====================================================

if ($anio !== "") {

    $whereDG .=
        " AND YEAR(dg.fecha) = ?";

    $paramsDG[] =
        (int)$anio;

    $typesDG .= "i";


    $whereTV .=
        " AND YEAR(tv.fecha_venta) = ?";

    $paramsTV[] =
        (int)$anio;

    $typesTV .= "i";


    $wherePE .=
        " AND YEAR(pe.fecha_pago) = ?";

    $paramsPE[] =
        (int)$anio;

    $typesPE .= "i";
}

//=====================================================
// FILTRO MES
//=====================================================

if ($periodo !== "todos") {

    $whereDG .=
        " AND MONTH(dg.fecha) = ?";

    $paramsDG[] =
        (int)$periodo;

    $typesDG .= "i";


    $whereTV .=
        " AND MONTH(tv.fecha_venta) = ?";

    $paramsTV[] =
        (int)$periodo;

    $typesTV .= "i";


    $wherePE .=
        " AND MONTH(pe.fecha_pago) = ?";

    $paramsPE[] =
        (int)$periodo;

    $typesPE .= "i";
}

//=====================================================
// FECHA INICIAL
//=====================================================

if ($fechaInicio !== "") {

    $whereDG .=
        " AND dg.fecha >= ?";

    $paramsDG[] =
        $fechaInicio . " 00:00:00";

    $typesDG .= "s";


    $whereTV .=
        " AND tv.fecha_venta >= ?";

    $paramsTV[] =
        $fechaInicio . " 00:00:00";

    $typesTV .= "s";


    $wherePE .=
        " AND pe.fecha_pago >= ?";

    $paramsPE[] =
        $fechaInicio . " 00:00:00";

    $typesPE .= "s";
}

//=====================================================
// FECHA FINAL
//=====================================================
//
// Se utiliza < día siguiente para incluir
// todo el día seleccionado.
//=====================================================

if ($fechaFin !== "") {

    $whereDG .=
        " AND dg.fecha < DATE_ADD(?, INTERVAL 1 DAY)";

    $paramsDG[] =
        $fechaFin;

    $typesDG .= "s";


    $whereTV .=
        " AND tv.fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)";

    $paramsTV[] =
        $fechaFin;

    $typesTV .= "s";


    $wherePE .=
        " AND pe.fecha_pago < DATE_ADD(?, INTERVAL 1 DAY)";

    $paramsPE[] =
        $fechaFin;

    $typesPE .= "s";
}

//=====================================================
// PROCESAR EXPORTACIÓN
//=====================================================

try {

    //=================================================
    // 1. INGRESOS POR VENTAS
    //=================================================

    $sqlVentas = "
        SELECT
            COALESCE(
                SUM(tv.total_venta),
                0
            ) AS total
        FROM ticket_ventas tv
        WHERE $whereTV
    ";

    $resultado =
        ejecutarExcel(
            $conexion,
            $sqlVentas,
            $typesTV,
            $paramsTV
        );

    $fila =
        mysqli_fetch_assoc($resultado);

    $ingresosVentas =
        (float)($fila["total"] ?? 0);

    //=================================================
    // 2. ENTRADAS MANUALES
    //=================================================

    $sqlEntradas = "
        SELECT
            COALESCE(
                SUM(dg.monto_pago),
                0
            ) AS total
        FROM deposito_gasto dg
        WHERE
            $whereDG
            AND LOWER(TRIM(dg.tipo))
            IN (
                'entrada',
                'ingreso',
                'deposito',
                'depósito'
            )
    ";

    $resultado =
        ejecutarExcel(
            $conexion,
            $sqlEntradas,
            $typesDG,
            $paramsDG
        );

    $fila =
        mysqli_fetch_assoc($resultado);

    $ingresosEntradas =
        (float)($fila["total"] ?? 0);

    //=================================================
    // 3. GASTOS REGISTRADOS
    //=================================================

    $sqlGastos = "
        SELECT
            COALESCE(
                SUM(dg.monto_pago),
                0
            ) AS total
        FROM deposito_gasto dg
        WHERE
            $whereDG
            AND LOWER(TRIM(dg.tipo))
            IN (
                'salida',
                'gasto',
                'egreso'
            )
    ";

    $resultado =
        ejecutarExcel(
            $conexion,
            $sqlGastos,
            $typesDG,
            $paramsDG
        );

    $fila =
        mysqli_fetch_assoc($resultado);

    $gastosRegistrados =
        (float)($fila["total"] ?? 0);

    //=================================================
    // 4. PAGOS DE EMPLEADOS
    //=================================================

    $sqlPagosEmpleados = "
        SELECT
            COALESCE(
                SUM(pe.monto_total),
                0
            ) AS total
        FROM pago_empleado pe
        WHERE $wherePE
    ";

    $resultado =
        ejecutarExcel(
            $conexion,
            $sqlPagosEmpleados,
            $typesPE,
            $paramsPE
        );

    $fila =
        mysqli_fetch_assoc($resultado);

    $pagosEmpleados =
        (float)($fila["total"] ?? 0);

    //=================================================
    // TOTALES
    //=================================================

    $ingresos =
        $ingresosVentas +
        $ingresosEntradas;

    $gastos =
        $gastosRegistrados +
        $pagosEmpleados;

    $utilidad =
        $ingresos -
        $gastos;

    //=================================================
    // BALANCE BANCARIO
    //=================================================

    $sqlBalance = "
        SELECT
            COALESCE(
                SUM(balance),
                0
            ) AS balance,

            COUNT(*) AS cuentas

        FROM cuenta_banco

        WHERE
            id_user = ?
            AND COALESCE(Eliminado, 0) = 0
    ";

    $resultado =
        ejecutarExcel(
            $conexion,
            $sqlBalance,
            "i",
            [$idUser]
        );

    $fila =
        mysqli_fetch_assoc($resultado);

    $balance =
        (float)($fila["balance"] ?? 0);

    $cantidadCuentas =
        (int)($fila["cuentas"] ?? 0);

    //=================================================
    // CREAR SPREADSHEET
    //=================================================

    $spreadsheet =
        new Spreadsheet();

    $spreadsheet
        ->getProperties()
        ->setCreator(
            "CoDevPro Technology"
        )
        ->setLastModifiedBy(
            "CoDevPro Technology"
        )
        ->setTitle(
            "Reporte de Contabilidad"
        )
        ->setSubject(
            "Reporte financiero"
        )
        ->setDescription(
            "Reporte de contabilidad del sistema Inventa"
        );

    //=================================================
    // HOJA RESUMEN
    //=================================================

    $hojaResumen =
        $spreadsheet->getActiveSheet();

    $hojaResumen->setTitle(
        "Resumen"
    );

    $hojaResumen->mergeCells(
        "A1:D1"
    );

    $hojaResumen->setCellValue(
        "A1",
        "REPORTE DE CONTABILIDAD"
    );

    $hojaResumen->getStyle(
        "A1:D1"
    )->applyFromArray([

        "font" => [
            "bold" => true,
            "size" => 16
        ],

        "alignment" => [
            "horizontal" =>
            Alignment::HORIZONTAL_CENTER,
            "vertical" =>
            Alignment::VERTICAL_CENTER
        ],

        "fill" => [
            "fillType" =>
            Fill::FILL_SOLID,
            "color" => [
                "rgb" => "1F4E78"
            ]
        ]

    ]);

    $hojaResumen
        ->getStyle("A1")
        ->getFont()
        ->getColor()
        ->setRGB("FFFFFF");

    //=================================================
    // FILTROS
    //=================================================

    $hojaResumen->setCellValue(
        "A3",
        "Filtros aplicados"
    );

    $hojaResumen->setCellValue(
        "A4",
        "Año"
    );

    $hojaResumen->setCellValue(
        "B4",
        $anio !== ""
            ? $anio
            : "Todos"
    );

    $hojaResumen->setCellValue(
        "A5",
        "Período"
    );

    $hojaResumen->setCellValue(
        "B5",
        $periodo !== "todos"
            ? $periodo
            : "Todos"
    );

    $hojaResumen->setCellValue(
        "A6",
        "Fecha inicio"
    );

    $hojaResumen->setCellValue(
        "B6",
        $fechaInicio !== ""
            ? $fechaInicio
            : "Sin filtro"
    );

    $hojaResumen->setCellValue(
        "A7",
        "Fecha fin"
    );

    $hojaResumen->setCellValue(
        "B7",
        $fechaFin !== ""
            ? $fechaFin
            : "Sin filtro"
    );

    //=================================================
    // TABLA RESUMEN
    //=================================================

    $filaActual = 10;

    $hojaResumen->setCellValue(
        "A" . $filaActual,
        "CONCEPTO"
    );

    $hojaResumen->setCellValue(
        "B" . $filaActual,
        "MONTO"
    );

    $hojaResumen->setCellValue(
        "C" . $filaActual,
        "TIPO"
    );

    $hojaResumen->getStyle(
        "A{$filaActual}:C{$filaActual}"
    )->applyFromArray([

        "font" => [
            "bold" => true
        ],

        "fill" => [
            "fillType" =>
            Fill::FILL_SOLID,
            "color" => [
                "rgb" => "D9EAF7"
            ]
        ],

        "borders" => [
            "allBorders" => [
                "borderStyle" =>
                Border::BORDER_THIN
            ]
        ]

    ]);

    $filaActual++;

    $resumenFilas = [

        [
            "Ingresos por ventas",
            $ingresosVentas,
            "Ingreso"
        ],

        [
            "Entradas manuales",
            $ingresosEntradas,
            "Ingreso"
        ],

        [
            "TOTAL INGRESOS",
            $ingresos,
            "Ingreso"
        ],

        [
            "Gastos registrados",
            $gastosRegistrados,
            "Gasto"
        ],

        [
            "Pagos de empleados",
            $pagosEmpleados,
            "Gasto"
        ],

        [
            "TOTAL GASTOS",
            $gastos,
            "Gasto"
        ],

        [
            "UTILIDAD",
            $utilidad,
            "Resultado"
        ],

        [
            "BALANCE BANCARIO",
            $balance,
            "Balance"
        ]

    ];

    foreach ($resumenFilas as $datos) {

        $hojaResumen->setCellValue(
            "A" . $filaActual,
            $datos[0]
        );

        $hojaResumen->setCellValue(
            "B" . $filaActual,
            $datos[1]
        );

        $hojaResumen->setCellValue(
            "C" . $filaActual,
            $datos[2]
        );

        $filaActual++;
    }

    $hojaResumen->setCellValue(
        "A" . ($filaActual + 1),
        "Cantidad de cuentas"
    );

    $hojaResumen->setCellValue(
        "B" . ($filaActual + 1),
        $cantidadCuentas
    );

    //=================================================
    // FORMATO MONEDA
    //=================================================

    $hojaResumen
        ->getStyle("B11:B18")
        ->getNumberFormat()
        ->setFormatCode(
            '"S/ " #,##0.00'
        );

    //=================================================
    // HOJA CUENTAS
    //=================================================

    $hojaCuentas =
        $spreadsheet->createSheet();

    $hojaCuentas->setTitle(
        "Cuentas bancarias"
    );

    $hojaCuentas->fromArray(
        [
            [
                "ID",
                "Cuenta bancaria",
                "Balance"
            ]
        ],
        null,
        "A1"
    );

    $hojaCuentas->getStyle(
        "A1:C1"
    )->applyFromArray([

        "font" => [
            "bold" => true
        ],

        "fill" => [
            "fillType" =>
            Fill::FILL_SOLID,
            "color" => [
                "rgb" => "D9EAF7"
            ]
        ]

    ]);

    //=================================================
    // OBTENER CUENTAS
    //=================================================

    $sqlCuentas = "
        SELECT
            id_cuenta_bancaria,
            nombre,
            balance

        FROM cuenta_banco

        WHERE
            id_user = ?
            AND COALESCE(Eliminado, 0) = 0

        ORDER BY
            nombre ASC
    ";

    $resultado =
        ejecutarExcel(
            $conexion,
            $sqlCuentas,
            "i",
            [$idUser]
        );

    $filaCuenta = 2;

    while (
        $cuenta =
        mysqli_fetch_assoc($resultado)
    ) {

        $hojaCuentas->setCellValue(
            "A" . $filaCuenta,
            (int)$cuenta["id_cuenta_bancaria"]
        );

        $hojaCuentas->setCellValue(
            "B" . $filaCuenta,
            $cuenta["nombre"]
        );

        $hojaCuentas->setCellValue(
            "C" . $filaCuenta,
            (float)$cuenta["balance"]
        );

        $filaCuenta++;
    }

    $hojaCuentas
        ->getStyle("C2:C{$filaCuenta}")
        ->getNumberFormat()
        ->setFormatCode(
            '"S/ " #,##0.00'
        );

    //=================================================
    // HOJA MOVIMIENTOS
    //=================================================

    $hojaMovimientos =
        $spreadsheet->createSheet();

    $hojaMovimientos->setTitle(
        "Movimientos"
    );

    $encabezados = [

        "ID",
        "Fecha",
        "Concepto",
        "Categoría",
        "Método de pago",
        "Tipo",
        "Monto",
        "Origen"

    ];

    $hojaMovimientos->fromArray(
        [$encabezados],
        null,
        "A1"
    );

    $hojaMovimientos->getStyle(
        "A1:H1"
    )->applyFromArray([

        "font" => [
            "bold" => true
        ],

        "fill" => [
            "fillType" =>
            Fill::FILL_SOLID,
            "color" => [
                "rgb" => "1F4E78"
            ]
        ]

    ]);

    $hojaMovimientos
        ->getStyle("A1:H1")
        ->getFont()
        ->getColor()
        ->setRGB("FFFFFF");

    //=================================================
    // CONSULTAR MOVIMIENTOS
    //=================================================

    $filtroVentas = "";
    $filtroGastos = "";
    $filtroPagos = "";

    $tipos = "";
    $parametros = [];

    //-------------------------------------------------
    // VENTAS
    //-------------------------------------------------

    $tipos .= "i";
    $parametros[] = $idUser;

    if ($anio !== "") {

        $filtroVentas .=
            " AND YEAR(tv.fecha_venta) = ?";

        $tipos .= "i";
        $parametros[] = (int)$anio;
    }

    if ($periodo !== "todos") {

        $filtroVentas .=
            " AND MONTH(tv.fecha_venta) = ?";

        $tipos .= "i";
        $parametros[] = (int)$periodo;
    }

    if ($fechaInicio !== "") {

        $filtroVentas .=
            " AND tv.fecha_venta >= ?";

        $tipos .= "s";
        $parametros[] =
            $fechaInicio . " 00:00:00";
    }

    if ($fechaFin !== "") {

        $filtroVentas .=
            " AND tv.fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)";

        $tipos .= "s";
        $parametros[] =
            $fechaFin;
    }

    //-------------------------------------------------
    // GASTOS
    //-------------------------------------------------

    $tipos .= "i";
    $parametros[] = $idUser;

    if ($anio !== "") {

        $filtroGastos .=
            " AND YEAR(dg.fecha) = ?";

        $tipos .= "i";
        $parametros[] = (int)$anio;
    }

    if ($periodo !== "todos") {

        $filtroGastos .=
            " AND MONTH(dg.fecha) = ?";

        $tipos .= "i";
        $parametros[] = (int)$periodo;
    }

    if ($fechaInicio !== "") {

        $filtroGastos .=
            " AND dg.fecha >= ?";

        $tipos .= "s";
        $parametros[] =
            $fechaInicio . " 00:00:00";
    }

    if ($fechaFin !== "") {

        $filtroGastos .=
            " AND dg.fecha < DATE_ADD(?, INTERVAL 1 DAY)";

        $tipos .= "s";
        $parametros[] =
            $fechaFin;
    }

    //-------------------------------------------------
    // PAGOS EMPLEADOS
    //-------------------------------------------------

    $tipos .= "i";
    $parametros[] = $idUser;

    if ($anio !== "") {

        $filtroPagos .=
            " AND YEAR(pe.fecha_pago) = ?";

        $tipos .= "i";
        $parametros[] = (int)$anio;
    }

    if ($periodo !== "todos") {

        $filtroPagos .=
            " AND MONTH(pe.fecha_pago) = ?";

        $tipos .= "i";
        $parametros[] = (int)$periodo;
    }

    if ($fechaInicio !== "") {

        $filtroPagos .=
            " AND pe.fecha_pago >= ?";

        $tipos .= "s";
        $parametros[] =
            $fechaInicio . " 00:00:00";
    }

    if ($fechaFin !== "") {

        $filtroPagos .=
            " AND pe.fecha_pago < DATE_ADD(?, INTERVAL 1 DAY)";

        $tipos .= "s";
        $parametros[] =
            $fechaFin;
    }

    //=================================================
    // SQL MOVIMIENTOS
    //=================================================

    $sqlMovimientos = "

        SELECT

            tv.id_ticket_ventas AS id_movimiento,

            tv.fecha_venta AS fecha,

            CONCAT(
                'Venta #',
                tv.id_ticket_ventas
            ) AS concepto,

            'Ventas' AS categoria,

            COALESCE(
                mp.nombre,
                'Sin método'
            ) AS metodo_pago,

            'Ingreso' AS tipo,

            CAST(
                tv.total_venta AS DECIMAL(15,2)
            ) AS monto,

            'ticket_ventas' AS origen

        FROM ticket_ventas tv

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               tv.id_metodo_pago
            AND mp.id_user =
               tv.id_user
            AND COALESCE(mp.Eliminado, 0) = 0

        WHERE
            tv.id_user = ?

            AND (
                tv.estado_venta IS NULL
                OR UPPER(
                    TRIM(tv.estado_venta)
                ) NOT IN (
                    'CANCELADO',
                    'CANCELADA',
                    'ANULADO',
                    'ANULADA'
                )
            )

            $filtroVentas


        UNION ALL


        SELECT

            dg.id_deposito AS id_movimiento,

            dg.fecha AS fecha,

            COALESCE(
                NULLIF(
                    TRIM(dg.concepto),
                    ''
                ),
                'Movimiento registrado'
            ) AS concepto,

            COALESCE(
                c.nombre,
                'Sin categoría'
            ) AS categoria,

            COALESCE(
                mp.nombre,
                'Sin método'
            ) AS metodo_pago,

            CASE
                WHEN LOWER(TRIM(dg.tipo))
                IN (
                    'entrada',
                    'ingreso',
                    'deposito',
                    'depósito'
                )
                THEN 'Ingreso'
                ELSE 'Gasto'
            END AS tipo,

            CAST(
                dg.monto_pago AS DECIMAL(15,2)
            ) AS monto,

            'deposito_gasto' AS origen

        FROM deposito_gasto dg

        LEFT JOIN categorias c
            ON c.id_categorias =
               dg.id_categoria
            AND c.id_user =
               dg.id_user
            AND COALESCE(c.Eliminado, 0) = 0

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               dg.id_metodo_pago
            AND mp.id_user =
               dg.id_user
            AND COALESCE(mp.Eliminado, 0) = 0

        WHERE
            dg.id_user = ?

            AND COALESCE(
                dg.Eliminado,
                0
            ) = 0

            $filtroGastos


        UNION ALL


        SELECT

            pe.id_pago AS id_movimiento,

            pe.fecha_pago AS fecha,

            CONCAT(
                'Pago de empleado: ',
                COALESCE(
                    CONCAT(
                        e.nombre,
                        ' ',
                        e.apellido
                    ),
                    'Empleado'
                )
            ) AS concepto,

            'Sueldos y pagos' AS categoria,

            COALESCE(
                mp.nombre,
                'Sin método'
            ) AS metodo_pago,

            'Gasto' AS tipo,

            CAST(
                pe.monto_total AS DECIMAL(15,2)
            ) AS monto,

            'pago_empleado' AS origen

        FROM pago_empleado pe

        LEFT JOIN empleados e
            ON e.id_empleado =
               pe.id_empleado
            AND e.id_user =
               pe.id_user

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               pe.id_metodo_pago
            AND mp.id_user =
               pe.id_user
            AND COALESCE(mp.Eliminado, 0) = 0

        WHERE
            pe.id_user = ?

            AND UPPER(
                TRIM(pe.estado)
            ) = 'PAGADO'

            $filtroPagos

        ORDER BY
            fecha DESC,
            id_movimiento DESC
    ";

    $resultado =
        ejecutarExcel(
            $conexion,
            $sqlMovimientos,
            $tipos,
            $parametros
        );

    //=================================================
    // LLENAR MOVIMIENTOS
    //=================================================

    $filaMovimiento = 2;

    while (
        $movimiento =
        mysqli_fetch_assoc($resultado)
    ) {

        $hojaMovimientos->setCellValue(
            "A" . $filaMovimiento,
            (int)$movimiento["id_movimiento"]
        );

        $hojaMovimientos->setCellValue(
            "B" . $filaMovimiento,
            $movimiento["fecha"]
        );

        $hojaMovimientos->setCellValue(
            "C" . $filaMovimiento,
            $movimiento["concepto"]
        );

        $hojaMovimientos->setCellValue(
            "D" . $filaMovimiento,
            $movimiento["categoria"]
        );

        $hojaMovimientos->setCellValue(
            "E" . $filaMovimiento,
            $movimiento["metodo_pago"]
        );

        $hojaMovimientos->setCellValue(
            "F" . $filaMovimiento,
            $movimiento["tipo"]
        );

        $hojaMovimientos->setCellValue(
            "G" . $filaMovimiento,
            (float)$movimiento["monto"]
        );

        $hojaMovimientos->setCellValue(
            "H" . $filaMovimiento,
            $movimiento["origen"]
        );

        $filaMovimiento++;
    }

    //=================================================
    // FORMATO MOVIMIENTOS
    //=================================================

    if ($filaMovimiento > 2) {

        $hojaMovimientos
            ->getStyle(
                "G2:G" . ($filaMovimiento - 1)
            )
            ->getNumberFormat()
            ->setFormatCode(
                '"S/ " #,##0.00'
            );
    }

    //=================================================
    // AUTO FILTER
    //=================================================

    $hojaMovimientos->setAutoFilter(
        "A1:H" .
            max(1, $filaMovimiento - 1)
    );

    //=================================================
    // AJUSTAR COLUMNAS
    //=================================================

    foreach (
        [
            $hojaResumen,
            $hojaCuentas,
            $hojaMovimientos
        ]
        as $hoja
    ) {

        foreach (
            range(
                1,
                $hoja->getHighestColumn()
                    ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                        $hoja->getHighestColumn()
                    )
                    : 1
            )
            as $columna
        ) {

            $letra =
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                    $columna
                );

            $hoja
                ->getColumnDimension($letra)
                ->setAutoSize(true);
        }
    }

    //=================================================
    // CONGELAR ENCABEZADOS
    //=================================================

    $hojaCuentas->freezePane("A2");

    $hojaMovimientos->freezePane("A2");

    //=================================================
    // DESCARGA
    //=================================================

    $nombreArchivo =
        "reporte_contabilidad_" .
        date("Y-m-d_H-i-s") .
        ".xlsx";

    while (ob_get_level()) {
        ob_end_clean();
    }

    header(
        "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    );

    header(
        "Content-Disposition: attachment; filename=\"" .
            $nombreArchivo .
            "\""
    );

    header(
        "Cache-Control: max-age=0"
    );

    $writer =
        new Xlsx(
            $spreadsheet
        );

    $writer->save(
        "php://output"
    );

    exit;
} catch (Throwable $e) {

    http_response_code(500);

    while (ob_get_level()) {
        ob_end_clean();
    }

    die("Error al generar el archivo Excel: " .
        $e->getMessage());
}
