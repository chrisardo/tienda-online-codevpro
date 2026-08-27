<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/exportar_contabilidad_pdf.php
// Módulo: Contabilidad
// Sistema: Inventa
//=====================================================

session_start();

ini_set("display_errors", "0");
error_reporting(E_ALL);

//=====================================================
// FPDF
//=====================================================

require_once "../controladores/conexion.php";
require_once "../fpdf/fpdf.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {
    die("No se pudo establecer conexión con la base de datos.");
}

$conexion->set_charset("utf8mb4");

//=====================================================
// OBTENER USUARIO
//=====================================================

$idUser = 0;

if (
    isset($_SESSION["id_user"]) &&
    intval($_SESSION["id_user"]) > 0
) {
    $idUser = intval($_SESSION["id_user"]);
} elseif (
    isset($_SESSION["idUser"]) &&
    intval($_SESSION["idUser"]) > 0
) {
    $idUser = intval($_SESSION["idUser"]);
} elseif (
    isset($_SESSION["id"]) &&
    intval($_SESSION["id"]) > 0
) {
    $idUser = intval($_SESSION["id"]);
}

//=====================================================
// VALIDAR SESIÓN
//=====================================================

if ($idUser <= 0) {
    die("La sesión ha expirado o no es válida.");
}

//=====================================================
// FUNCIONES GENERALES
//=====================================================

function limpiarTextoPDF($valor)
{
    if ($valor === null) {
        return "";
    }

    return trim((string)$valor);
}

//-----------------------------------------------------

function convertirUTF8PDF($texto)
{
    $texto = limpiarTextoPDF($texto);

    if ($texto === "") {
        return "";
    }

    $resultado = @iconv(
        "UTF-8",
        "ISO-8859-1//TRANSLIT",
        $texto
    );

    if ($resultado === false) {
        return utf8_decode($texto);
    }

    return $resultado;
}

//-----------------------------------------------------

function dineroPDF($valor)
{
    return "S/ " . number_format(
        (float)$valor,
        2,
        ".",
        ","
    );
}

//-----------------------------------------------------

function fechaPDF($fecha)
{
    if (!$fecha) {
        return "-";
    }

    $timestamp = strtotime($fecha);

    if ($timestamp === false) {
        return $fecha;
    }

    return date("d/m/Y", $timestamp);
}

//-----------------------------------------------------

function obtenerNombrePeriodo($periodo)
{
    $meses = [
        1  => "Enero",
        2  => "Febrero",
        3  => "Marzo",
        4  => "Abril",
        5  => "Mayo",
        6  => "Junio",
        7  => "Julio",
        8  => "Agosto",
        9  => "Septiembre",
        10 => "Octubre",
        11 => "Noviembre",
        12 => "Diciembre"
    ];

    $periodo = intval($periodo);

    return $meses[$periodo] ?? "Todos los períodos";
}

//-----------------------------------------------------

function textoCorto($texto, $longitud = 30)
{
    $texto = limpiarTextoPDF($texto);

    if ($texto === "") {
        return "-";
    }

    if (function_exists("mb_strimwidth")) {

        return mb_strimwidth(
            $texto,
            0,
            $longitud,
            "...",
            "UTF-8"
        );
    }

    return strlen($texto) > $longitud
        ? substr($texto, 0, $longitud - 3) . "..."
        : $texto;
}

//=====================================================
// EJECUTAR CONSULTA PREPARADA
// SIN get_result()
//=====================================================

function ejecutarConsultaPreparada(
    $conexion,
    $sql,
    $tipos = "",
    $parametros = []
) {

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        die("Error al preparar consulta: " .
            $conexion->error);
    }

    if (!empty($parametros)) {

        $referencias = [];

        $referencias[] = $tipos;

        foreach ($parametros as $indice => $valor) {
            $referencias[] = &$parametros[$indice];
        }

        if (!call_user_func_array(
            [$stmt, "bind_param"],
            $referencias
        )) {

            $error = $stmt->error;

            $stmt->close();

            die("Error al enlazar parámetros: " .
                $error);
        }
    }

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        die("Error al ejecutar consulta: " .
            $error);
    }

    $resultado = [];

    $meta = $stmt->result_metadata();

    if ($meta) {

        $campos = [];
        $fila = [];
        $referencias = [];

        while ($campo = $meta->fetch_field()) {

            $campos[] = $campo->name;

            $fila[$campo->name] = null;

            $referencias[] = &$fila[$campo->name];
        }

        call_user_func_array(
            [$stmt, "bind_result"],
            $referencias
        );

        while ($stmt->fetch()) {

            $registro = [];

            foreach ($campos as $campo) {
                $registro[$campo] = $fila[$campo];
            }

            $resultado[] = $registro;
        }

        $meta->free();
    }

    $stmt->close();

    return $resultado;
}

//=====================================================
// AGREGAR CONDICIONES DE FECHA
//=====================================================

function agregarCondicionesFecha(
    &$condiciones,
    &$parametros,
    &$tipos,
    $campoFecha,
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin
) {

    //-------------------------------------------------
    // RANGO MANUAL
    //-------------------------------------------------

    if ($fechaInicio !== "") {

        $condiciones[] =
            "$campoFecha >= ?";

        $parametros[] =
            $fechaInicio;

        $tipos .= "s";
    }

    if ($fechaFin !== "") {

        /*
         * Como algunos campos son DATE y otros
         * pueden ser DATETIME, utilizamos <=
         * al final del día cuando corresponde.
         */
        $condiciones[] =
            "$campoFecha <= ?";

        $parametros[] =
            $fechaFin;

        $tipos .= "s";
    }

    //-------------------------------------------------
    // AÑO / MES
    //-------------------------------------------------

    if (
        $fechaInicio === "" &&
        $fechaFin === ""
    ) {

        if ($anio !== "") {

            $condiciones[] =
                "YEAR($campoFecha) = ?";

            $parametros[] =
                intval($anio);

            $tipos .= "i";
        }

        if (
            $periodo !== "" &&
            $periodo !== "todos"
        ) {

            $numeroPeriodo =
                intval($periodo);

            if (
                $numeroPeriodo >= 1 &&
                $numeroPeriodo <= 12
            ) {

                $condiciones[] =
                    "MONTH($campoFecha) = ?";

                $parametros[] =
                    $numeroPeriodo;

                $tipos .= "i";
            }
        }
    }
}

//=====================================================
// RECIBIR FILTROS
//=====================================================

$anio = isset($_GET["anio"])
    ? trim($_GET["anio"])
    : "";

$periodo = isset($_GET["periodo"])
    ? trim($_GET["periodo"])
    : "todos";

$fechaInicio = isset($_GET["fecha_inicio"])
    ? trim($_GET["fecha_inicio"])
    : "";

$fechaFin = isset($_GET["fecha_fin"])
    ? trim($_GET["fecha_fin"])
    : "";

//=====================================================
// NORMALIZAR PERÍODO
//=====================================================

if ($periodo === "") {
    $periodo = "todos";
}

//=====================================================
// VALIDAR AÑO
//=====================================================

if ($anio !== "") {

    if (
        !preg_match("/^\d{4}$/", $anio) ||
        intval($anio) < 2000 ||
        intval($anio) > 2100
    ) {

        die("El año seleccionado no es válido.");
    }
}

//=====================================================
// VALIDAR PERÍODO
//=====================================================

if ($periodo !== "todos") {

    $periodoNumero =
        intval($periodo);

    if (
        $periodoNumero < 1 ||
        $periodoNumero > 12
    ) {

        die("El período seleccionado no es válido.");
    }
}

//=====================================================
// VALIDAR FECHA INICIO
//=====================================================

if ($fechaInicio !== "") {

    $fecha =
        DateTime::createFromFormat(
            "Y-m-d",
            $fechaInicio
        );

    if (
        !$fecha ||
        $fecha->format("Y-m-d") !==
        $fechaInicio
    ) {

        die("La fecha de inicio no es válida.");
    }
}

//=====================================================
// VALIDAR FECHA FIN
//=====================================================

if ($fechaFin !== "") {

    $fecha =
        DateTime::createFromFormat(
            "Y-m-d",
            $fechaFin
        );

    if (
        !$fecha ||
        $fecha->format("Y-m-d") !==
        $fechaFin
    ) {

        die("La fecha final no es válida.");
    }
}

//=====================================================
// VALIDAR RANGO
//=====================================================

if (
    $fechaInicio !== "" &&
    $fechaFin !== "" &&
    $fechaInicio > $fechaFin
) {

    die("La fecha de inicio no puede ser mayor " .
        "que la fecha final.");
}

//=====================================================
// TEXTO DEL PERÍODO
//=====================================================

if (
    $fechaInicio !== "" ||
    $fechaFin !== ""
) {

    if (
        $fechaInicio !== "" &&
        $fechaFin !== ""
    ) {

        $textoPeriodo =
            fechaPDF($fechaInicio) .
            " - " .
            fechaPDF($fechaFin);
    } elseif ($fechaInicio !== "") {

        $textoPeriodo =
            "Desde " .
            fechaPDF($fechaInicio);
    } else {

        $textoPeriodo =
            "Hasta " .
            fechaPDF($fechaFin);
    }
} elseif ($anio !== "") {

    if (
        $periodo !== "todos" &&
        intval($periodo) >= 1 &&
        intval($periodo) <= 12
    ) {

        $textoPeriodo =
            obtenerNombrePeriodo($periodo) .
            " " .
            $anio;
    } else {

        $textoPeriodo =
            "Año " . $anio;
    }
} else {

    $textoPeriodo =
        "Todos los años";
}

//=====================================================
// VARIABLES CONTABLES
//=====================================================

$totalVentas = 0;
$totalEntradas = 0;
$totalGastos = 0;
$totalGastosEmpleados = 0;

//=====================================================
// MOVIMIENTOS
//=====================================================

$movimientos = [];

//=====================================================
// 1. VENTAS
//=====================================================

$condicionesVentas = [
    "tv.id_user = ?",
    "tv.estado_venta = 'Vendido'"
];

$parametrosVentas = [
    $idUser
];

$tiposVentas = "i";

agregarCondicionesFecha(
    $condicionesVentas,
    $parametrosVentas,
    $tiposVentas,
    "tv.fecha_venta",
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin
);

$sqlVentas = "
    SELECT
        tv.id_ticket_ventas,
        tv.fecha_venta,
        tv.total_venta,
        tv.tipo_comprobante,
        tv.serie,
        tv.numero,
        mp.nombre AS nombre_metodo,
        c.nombre AS nombre_cliente
    FROM ticket_ventas tv

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago =
           tv.id_metodo_pago

    LEFT JOIN clientes c
        ON c.idCliente =
           tv.idCliente

    WHERE " .
    implode(
        " AND ",
        $condicionesVentas
    ) . "

    ORDER BY
        tv.fecha_venta DESC,
        tv.id_ticket_ventas DESC
";

$ventas =
    ejecutarConsultaPreparada(
        $conexion,
        $sqlVentas,
        $tiposVentas,
        $parametrosVentas
    );

foreach ($ventas as $venta) {

    $monto =
        (float)(
            $venta["total_venta"] ?? 0
        );

    $totalVentas += $monto;

    $movimientos[] = [
        "fecha" =>
        $venta["fecha_venta"],

        "concepto" =>
        "Venta " .
            (
                !empty($venta["serie"])
                ? $venta["serie"] . "-"
                : ""
            ) .
            (
                !empty($venta["numero"])
                ? $venta["numero"]
                : $venta["id_ticket_ventas"]
            ),

        "categoria" =>
        "Venta",

        "metodo" =>
        $venta["nombre_metodo"] ??
            "Sin método",

        "cuenta" =>
        "-",

        "tipo" =>
        "Ingreso",

        "monto" =>
        $monto
    ];
}

//=====================================================
// 2. DEPÓSITOS / GASTOS MANUALES
//=====================================================

$condicionesDepositos = [
    "dg.id_user = ?",
    "dg.Eliminado = 0"
];

$parametrosDepositos = [
    $idUser
];

$tiposDepositos = "i";

agregarCondicionesFecha(
    $condicionesDepositos,
    $parametrosDepositos,
    $tiposDepositos,
    "dg.fecha",
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin
);

$sqlDepositos = "
    SELECT

        dg.id_deposito,
        dg.fecha,
        dg.concepto,
        dg.descripcion,
        dg.monto_pago,
        dg.tipo,

        cb.nombre AS nombre_cuenta,

        c.nombre AS nombre_categoria,

        mp.nombre AS nombre_metodo,

        p.nombre AS nombre_proveedor

    FROM deposito_gasto dg

    LEFT JOIN cuenta_banco cb
        ON cb.id_cuenta_bancaria =
           dg.id_cuenta_bancaria

    LEFT JOIN categorias c
        ON c.id_categorias =
           dg.id_categoria

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago =
           dg.id_metodo_pago

    LEFT JOIN provedores p
        ON p.id_provedor =
           dg.id_proveedor

    WHERE " .
    implode(
        " AND ",
        $condicionesDepositos
    ) . "

    ORDER BY
        dg.fecha DESC,
        dg.id_deposito DESC
";

$depositos =
    ejecutarConsultaPreparada(
        $conexion,
        $sqlDepositos,
        $tiposDepositos,
        $parametrosDepositos
    );

foreach ($depositos as $deposito) {

    $tipoMovimiento =
        strtolower(
            trim(
                $deposito["tipo"] ?? ""
            )
        );

    $esEntrada =
        (
            $tipoMovimiento === "entrada" ||
            $tipoMovimiento === "ingreso" ||
            $tipoMovimiento === "deposito" ||
            $tipoMovimiento === "depósito"
        );

    $monto =
        (float)(
            $deposito["monto_pago"] ?? 0
        );

    if ($esEntrada) {

        $totalEntradas += $monto;

        $tipoPDF = "Ingreso";
    } else {

        $totalGastos += $monto;

        $tipoPDF = "Gasto";
    }

    $movimientos[] = [
        "fecha" =>
        $deposito["fecha"],

        "concepto" =>
        $deposito["concepto"] ??
            "Sin concepto",

        "categoria" =>
        $deposito["nombre_categoria"] ??
            "Sin categoría",

        "metodo" =>
        $deposito["nombre_metodo"] ??
            "Sin método",

        "cuenta" =>
        $deposito["nombre_cuenta"] ??
            "Sin cuenta",

        "tipo" =>
        $tipoPDF,

        "monto" =>
        $monto
    ];
}

//=====================================================
// 3. PAGOS DE EMPLEADOS
//=====================================================
//
// Los pagos de empleados PAGADOS representan
// una salida/gasto contable.
//
// NO se incluyen:
// PENDIENTE
// ANULADO
//
//=====================================================

$condicionesPagos = [
    "pe.id_user = ?",
    "pe.estado = 'PAGADO'"
];

$parametrosPagos = [
    $idUser
];

$tiposPagos = "i";

agregarCondicionesFecha(
    $condicionesPagos,
    $parametrosPagos,
    $tiposPagos,
    "pe.fecha_pago",
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin
);

$sqlPagos = "
    SELECT

        pe.id_pago,
        pe.periodo_inicio,
        pe.periodo_fin,
        pe.monto_total,
        pe.fecha_pago,
        pe.observacion,

        e.nombre AS nombre_empleado,
        e.apellido AS apellido_empleado,

        cb.nombre AS nombre_cuenta,

        mp.nombre AS nombre_metodo

    FROM pago_empleado pe

    INNER JOIN empleados e
        ON e.id_empleado =
           pe.id_empleado

    LEFT JOIN cuenta_banco cb
        ON cb.id_cuenta_bancaria =
           pe.id_cuenta_bancaria

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago =
           pe.id_metodo_pago

    WHERE " .
    implode(
        " AND ",
        $condicionesPagos
    ) . "

    ORDER BY
        pe.fecha_pago DESC,
        pe.id_pago DESC
";

$pagosEmpleados =
    ejecutarConsultaPreparada(
        $conexion,
        $sqlPagos,
        $tiposPagos,
        $parametrosPagos
    );

foreach (
    $pagosEmpleados
    as $pago
) {

    $monto =
        (float)(
            $pago["monto_total"] ?? 0
        );

    $totalGastosEmpleados += $monto;

    $totalGastos += $monto;

    $nombreEmpleado =
        trim(
            ($pago["nombre_empleado"] ?? "") .
                " " .
                ($pago["apellido_empleado"] ?? "")
        );

    if ($nombreEmpleado === "") {
        $nombreEmpleado =
            "Empleado";
    }

    $movimientos[] = [
        "fecha" =>
        $pago["fecha_pago"],

        "concepto" =>
        "Pago empleado: " .
            $nombreEmpleado,

        "categoria" =>
        "Sueldos",

        "metodo" =>
        $pago["nombre_metodo"] ??
            "Sin método",

        "cuenta" =>
        $pago["nombre_cuenta"] ??
            "Sin cuenta",

        "tipo" =>
        "Gasto",

        "monto" =>
        $monto
    ];
}

//=====================================================
// ORDENAR TODOS LOS MOVIMIENTOS POR FECHA
//=====================================================

usort(
    $movimientos,
    function ($a, $b) {

        $fechaA =
            strtotime(
                $a["fecha"] ?? ""
            );

        $fechaB =
            strtotime(
                $b["fecha"] ?? ""
            );

        if ($fechaA === $fechaB) {
            return 0;
        }

        return
            $fechaA < $fechaB
            ? 1
            : -1;
    }
);

//=====================================================
// TOTAL INGRESOS
//=====================================================

$totalIngresos =
    $totalVentas +
    $totalEntradas;

//=====================================================
// UTILIDAD
//=====================================================

$utilidad =
    $totalIngresos -
    $totalGastos;

//=====================================================
// BALANCE BANCARIO ACTUAL
//=====================================================
//
// IMPORTANTE:
// Este balance NO se filtra por período.
// Representa el balance actual de las cuentas.
//
//=====================================================

$sqlBalance = "
    SELECT
        COALESCE(
            SUM(balance),
            0
        ) AS balance
    FROM cuenta_banco

    WHERE
        id_user = ?
        AND Eliminado = 0
";

$balanceResultado =
    ejecutarConsultaPreparada(
        $conexion,
        $sqlBalance,
        "i",
        [$idUser]
    );

$balanceBancario = 0;

if (!empty($balanceResultado)) {

    $balanceBancario =
        (float)(
            $balanceResultado[0]["balance"]
            ?? 0
        );
}

//=====================================================
// CUENTAS BANCARIAS
//=====================================================

$sqlCuentas = "
    SELECT
        id_cuenta_bancaria,
        nombre,
        balance
    FROM cuenta_banco

    WHERE
        id_user = ?
        AND Eliminado = 0

    ORDER BY
        nombre ASC
";

$cuentas =
    ejecutarConsultaPreparada(
        $conexion,
        $sqlCuentas,
        "i",
        [$idUser]
    );

//=====================================================
// DATOS DE EMPRESA
//=====================================================

$nombreEmpresa =
    "CoDevPro Technology";

if (
    isset($_SESSION["nombre"]) &&
    trim($_SESSION["nombre"]) !== ""
) {

    $nombreEmpresa =
        trim($_SESSION["nombre"]);
}

if (
    isset($_SESSION["nombreEmpresa"]) &&
    trim($_SESSION["nombreEmpresa"]) !== ""
) {

    $nombreEmpresa =
        trim($_SESSION["nombreEmpresa"]);
}

//=====================================================
// CLASE PDF
//=====================================================

class PDFContabilidad extends FPDF
{
    public $nombreEmpresa = "";
    public $textoPeriodo = "";

    //-------------------------------------------------
    // HEADER
    //-------------------------------------------------

    public function Header()
    {
        $this->SetTextColor(
            0,
            0,
            0
        );

        $this->SetFont(
            "Arial",
            "B",
            16
        );

        $this->Cell(
            0,
            8,
            convertirUTF8PDF(
                $this->nombreEmpresa
            ),
            0,
            1,
            "C"
        );

        $this->SetFont(
            "Arial",
            "B",
            13
        );

        $this->Cell(
            0,
            7,
            convertirUTF8PDF(
                "REPORTE DE CONTABILIDAD"
            ),
            0,
            1,
            "C"
        );

        $this->SetFont(
            "Arial",
            "",
            9
        );

        $this->Cell(
            0,
            6,
            convertirUTF8PDF(
                "Período: " .
                    $this->textoPeriodo
            ),
            0,
            1,
            "C"
        );

        $this->Ln(5);

        $this->SetDrawColor(
            180,
            180,
            180
        );

        $this->Line(
            10,
            $this->GetY(),
            287,
            $this->GetY()
        );

        $this->Ln(5);
    }

    //-------------------------------------------------
    // FOOTER
    //-------------------------------------------------

    public function Footer()
    {
        $this->SetY(-15);

        $this->SetFont(
            "Arial",
            "",
            8
        );

        $this->SetTextColor(
            100,
            100,
            100
        );

        $this->Cell(
            0,
            5,
            convertirUTF8PDF(
                "Sistema Inventa - " .
                    $this->nombreEmpresa
            ),
            0,
            0,
            "L"
        );

        $this->Cell(
            0,
            5,
            convertirUTF8PDF(
                "Página " .
                    $this->PageNo()
            ),
            0,
            0,
            "R"
        );
    }
}

//=====================================================
// CREAR PDF
//=====================================================

$pdf =
    new PDFContabilidad(
        "L",
        "mm",
        "A4"
    );

$pdf->nombreEmpresa =
    $nombreEmpresa;

$pdf->textoPeriodo =
    $textoPeriodo;

$pdf->SetMargins(
    10,
    10,
    10
);

$pdf->SetAutoPageBreak(
    true,
    18
);

$pdf->AddPage();

//=====================================================
// RESUMEN FINANCIERO
//=====================================================

$pdf->SetFont(
    "Arial",
    "B",
    11
);

$pdf->SetFillColor(
    240,
    240,
    240
);

$pdf->Cell(
    0,
    8,
    convertirUTF8PDF(
        "RESUMEN FINANCIERO"
    ),
    0,
    1,
    "L",
    true
);

$pdf->Ln(3);

$anchoResumen = 68;

$pdf->SetFont(
    "Arial",
    "B",
    9
);

$pdf->Cell(
    $anchoResumen,
    7,
    convertirUTF8PDF("Ingresos"),
    1,
    0,
    "C"
);

$pdf->Cell(
    $anchoResumen,
    7,
    convertirUTF8PDF("Gastos"),
    1,
    0,
    "C"
);

$pdf->Cell(
    $anchoResumen,
    7,
    convertirUTF8PDF("Utilidad"),
    1,
    0,
    "C"
);

$pdf->Cell(
    $anchoResumen,
    7,
    convertirUTF8PDF(
        "Balance bancario"
    ),
    1,
    1,
    "C"
);

$pdf->SetFont(
    "Arial",
    "",
    10
);

$pdf->Cell(
    $anchoResumen,
    9,
    convertirUTF8PDF(
        dineroPDF($totalIngresos)
    ),
    1,
    0,
    "C"
);

$pdf->Cell(
    $anchoResumen,
    9,
    convertirUTF8PDF(
        dineroPDF($totalGastos)
    ),
    1,
    0,
    "C"
);

$pdf->Cell(
    $anchoResumen,
    9,
    convertirUTF8PDF(
        dineroPDF($utilidad)
    ),
    1,
    0,
    "C"
);

$pdf->Cell(
    $anchoResumen,
    9,
    convertirUTF8PDF(
        dineroPDF($balanceBancario)
    ),
    1,
    1,
    "C"
);

$pdf->Ln(7);

//=====================================================
// DESGLOSE DE INGRESOS
//=====================================================

$pdf->SetFont(
    "Arial",
    "B",
    11
);

$pdf->Cell(
    0,
    8,
    convertirUTF8PDF(
        "DESGLOSE DE INGRESOS"
    ),
    0,
    1,
    "L"
);

$pdf->SetFont(
    "Arial",
    "",
    9
);

$pdf->Cell(
    100,
    7,
    convertirUTF8PDF(
        "Ventas"
    ),
    1,
    0,
    "L"
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        dineroPDF($totalVentas)
    ),
    1,
    1,
    "R"
);

$pdf->Cell(
    100,
    7,
    convertirUTF8PDF(
        "Entradas manuales"
    ),
    1,
    0,
    "L"
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        dineroPDF($totalEntradas)
    ),
    1,
    1,
    "R"
);

$pdf->Cell(
    100,
    7,
    convertirUTF8PDF(
        "Total ingresos"
    ),
    1,
    0,
    "L"
);

$pdf->SetFont(
    "Arial",
    "B",
    9
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        dineroPDF($totalIngresos)
    ),
    1,
    1,
    "R"
);

$pdf->Ln(7);

//=====================================================
// DESGLOSE DE GASTOS
//=====================================================

$pdf->SetFont(
    "Arial",
    "B",
    11
);

$pdf->Cell(
    0,
    8,
    convertirUTF8PDF(
        "DESGLOSE DE GASTOS"
    ),
    0,
    1,
    "L"
);

$pdf->SetFont(
    "Arial",
    "",
    9
);

$pdf->Cell(
    100,
    7,
    convertirUTF8PDF(
        "Gastos manuales"
    ),
    1,
    0,
    "L"
);

$gastosManuales =
    $totalGastos -
    $totalGastosEmpleados;

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        dineroPDF($gastosManuales)
    ),
    1,
    1,
    "R"
);

$pdf->Cell(
    100,
    7,
    convertirUTF8PDF(
        "Pagos de empleados"
    ),
    1,
    0,
    "L"
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        dineroPDF(
            $totalGastosEmpleados
        )
    ),
    1,
    1,
    "R"
);

$pdf->SetFont(
    "Arial",
    "B",
    9
);

$pdf->Cell(
    100,
    7,
    convertirUTF8PDF(
        "Total gastos"
    ),
    1,
    0,
    "L"
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        dineroPDF($totalGastos)
    ),
    1,
    1,
    "R"
);

$pdf->Ln(7);

//=====================================================
// CUENTAS BANCARIAS
//=====================================================

$pdf->SetFont(
    "Arial",
    "B",
    11
);

$pdf->Cell(
    0,
    8,
    convertirUTF8PDF(
        "CUENTAS BANCARIAS"
    ),
    0,
    1,
    "L"
);

$pdf->SetFont(
    "Arial",
    "B",
    9
);

$pdf->Cell(
    110,
    7,
    convertirUTF8PDF(
        "Cuenta"
    ),
    1,
    0,
    "C"
);

$pdf->Cell(
    70,
    7,
    convertirUTF8PDF(
        "Balance"
    ),
    1,
    1,
    "C"
);

$pdf->SetFont(
    "Arial",
    "",
    9
);

if (empty($cuentas)) {

    $pdf->Cell(
        180,
        8,
        convertirUTF8PDF(
            "No hay cuentas bancarias registradas."
        ),
        1,
        1,
        "C"
    );
} else {

    foreach ($cuentas as $cuenta) {

        $pdf->Cell(
            110,
            7,
            convertirUTF8PDF(
                $cuenta["nombre"] ??
                    "Sin nombre"
            ),
            1,
            0,
            "L"
        );

        $pdf->Cell(
            70,
            7,
            convertirUTF8PDF(
                dineroPDF(
                    $cuenta["balance"] ??
                        0
                )
            ),
            1,
            1,
            "R"
        );
    }
}

$pdf->Ln(7);

//=====================================================
// MOVIMIENTOS CONTABLES
//=====================================================

$pdf->SetFont(
    "Arial",
    "B",
    11
);

$pdf->Cell(
    0,
    8,
    convertirUTF8PDF(
        "MOVIMIENTOS CONTABLES"
    ),
    0,
    1,
    "L"
);

//=====================================================
// ENCABEZADOS
//=====================================================

$pdf->SetFont(
    "Arial",
    "B",
    8
);

$pdf->SetFillColor(
    230,
    230,
    230
);

$pdf->Cell(
    24,
    8,
    convertirUTF8PDF("Fecha"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    52,
    8,
    convertirUTF8PDF("Concepto"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    38,
    8,
    convertirUTF8PDF("Categoría"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    38,
    8,
    convertirUTF8PDF("Método"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    35,
    8,
    convertirUTF8PDF("Cuenta"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    25,
    8,
    convertirUTF8PDF("Tipo"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    30,
    8,
    convertirUTF8PDF("Monto"),
    1,
    1,
    "C",
    true
);

//=====================================================
// DETALLE DE MOVIMIENTOS
//=====================================================

$pdf->SetFont(
    "Arial",
    "",
    7.5
);

if (empty($movimientos)) {

    $pdf->Cell(
        242,
        8,
        convertirUTF8PDF(
            "No existen movimientos para el período seleccionado."
        ),
        1,
        1,
        "C"
    );
} else {

    foreach ($movimientos as $movimiento) {

        $pdf->Cell(
            24,
            7,
            convertirUTF8PDF(
                fechaPDF(
                    $movimiento["fecha"] ??
                        ""
                )
            ),
            1,
            0,
            "C"
        );

        $pdf->Cell(
            52,
            7,
            convertirUTF8PDF(
                textoCorto(
                    $movimiento["concepto"] ??
                        "",
                    34
                )
            ),
            1,
            0,
            "L"
        );

        $pdf->Cell(
            38,
            7,
            convertirUTF8PDF(
                textoCorto(
                    $movimiento["categoria"] ??
                        "",
                    24
                )
            ),
            1,
            0,
            "L"
        );

        $pdf->Cell(
            38,
            7,
            convertirUTF8PDF(
                textoCorto(
                    $movimiento["metodo"] ??
                        "",
                    24
                )
            ),
            1,
            0,
            "L"
        );

        $pdf->Cell(
            35,
            7,
            convertirUTF8PDF(
                textoCorto(
                    $movimiento["cuenta"] ??
                        "",
                    22
                )
            ),
            1,
            0,
            "L"
        );

        $pdf->Cell(
            25,
            7,
            convertirUTF8PDF(
                $movimiento["tipo"] ??
                    "-"
            ),
            1,
            0,
            "C"
        );

        $pdf->Cell(
            30,
            7,
            convertirUTF8PDF(
                dineroPDF(
                    $movimiento["monto"] ??
                        0
                )
            ),
            1,
            1,
            "R"
        );
    }
}

//=====================================================
// RESUMEN DE MOVIMIENTOS
//=====================================================

$pdf->Ln(7);

$pdf->SetFont(
    "Arial",
    "B",
    10
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        "Total ventas"
    ),
    1,
    0,
    "L"
);

$pdf->Cell(
    45,
    7,
    convertirUTF8PDF(
        dineroPDF($totalVentas)
    ),
    1,
    0,
    "R"
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        "Entradas manuales"
    ),
    1,
    0,
    "L"
);

$pdf->Cell(
    45,
    7,
    convertirUTF8PDF(
        dineroPDF($totalEntradas)
    ),
    1,
    1,
    "R"
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        "Gastos manuales"
    ),
    1,
    0,
    "L"
);

$pdf->Cell(
    45,
    7,
    convertirUTF8PDF(
        dineroPDF($gastosManuales)
    ),
    1,
    0,
    "R"
);

$pdf->Cell(
    80,
    7,
    convertirUTF8PDF(
        "Pagos empleados"
    ),
    1,
    0,
    "L"
);

$pdf->Cell(
    45,
    7,
    convertirUTF8PDF(
        dineroPDF(
            $totalGastosEmpleados
        )
    ),
    1,
    1,
    "R"
);

//=====================================================
// UTILIDAD FINAL
//=====================================================

$pdf->Ln(5);

$pdf->SetFont(
    "Arial",
    "B",
    11
);

$pdf->Cell(
    0,
    9,
    convertirUTF8PDF(
        "UTILIDAD NETA: " .
            dineroPDF($utilidad)
    ),
    1,
    1,
    "R"
);

//=====================================================
// INFORMACIÓN FINAL
//=====================================================

$pdf->Ln(5);

$pdf->SetFont(
    "Arial",
    "",
    8
);

$pdf->SetTextColor(
    100,
    100,
    100
);

$pdf->MultiCell(
    0,
    5,
    convertirUTF8PDF(
        "Reporte generado el " .
            date("d/m/Y H:i:s") .
            "."
    ),
    0,
    "L"
);

//=====================================================
// NOMBRE DEL ARCHIVO
//=====================================================

$nombreArchivo =
    "reporte_contabilidad";

if ($anio !== "") {

    $nombreArchivo .=
        "_" . $anio;
}

if (
    $periodo !== "" &&
    $periodo !== "todos"
) {

    $nombreArchivo .=
        "_mes_" .
        intval($periodo);
}

if ($fechaInicio !== "") {

    $nombreArchivo .=
        "_desde_" .
        str_replace(
            "-",
            "",
            $fechaInicio
        );
}

if ($fechaFin !== "") {

    $nombreArchivo .=
        "_hasta_" .
        str_replace(
            "-",
            "",
            $fechaFin
        );
}

$nombreArchivo .=
    "_" .
    date("Ymd_His") .
    ".pdf";

//=====================================================
// LIMPIAR BUFFER
//=====================================================

if (ob_get_length()) {
    ob_end_clean();
}

//=====================================================
// SALIDA PDF
//=====================================================

$pdf->Output(
    "I",
    $nombreArchivo
);

exit;
