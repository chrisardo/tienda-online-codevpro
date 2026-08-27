<?php
//=========================================================
// CoDevPro Technology
// Archivo: ajax/exportar_ventas_pdf.php
//=========================================================

session_start();

require_once "../controladores/conexion.php";
require_once "../fpdf/fpdf.php";

/*=========================================================
=            VALIDAR SESION
=========================================================*/

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {

    die("Acceso denegado.");
}

/*=========================================================
=            RECIBIR PARAMETROS
=========================================================*/

$buscar       = trim($_GET["buscar"] ?? "");
$estadoVenta  = trim($_GET["estadoVenta"] ?? "");
$estadoEnvio  = trim($_GET["estadoEnvio"] ?? "");
$metodoPago   = trim($_GET["metodoPago"] ?? "");
$empleado     = trim($_GET["empleado"] ?? "");
$fechaInicio  = trim($_GET["fechaInicio"] ?? "");
$fechaFin     = trim($_GET["fechaFin"] ?? "");

/*=========================================================
=            EMPRESA
=========================================================*/

$sqlEmpresa = "

    SELECT
        nombreEmpresa,
        direccion,
        celular,
        email,
        ruc

    FROM usuario_acceso

    WHERE id_user = ?

    LIMIT 1

";

$stmtEmpresa = mysqli_prepare($conexion, $sqlEmpresa);

mysqli_stmt_bind_param(
    $stmtEmpresa,
    "i",
    $idUser
);

mysqli_stmt_execute($stmtEmpresa);

$empresa = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmtEmpresa)
);

/*=========================================================
=            WHERE DINAMICO
=========================================================*/

$where = [];
$where[] = "tv.id_user = {$idUser}";

if (!empty($buscar)) {

    $buscar = mysqli_real_escape_string(
        $conexion,
        $buscar
    );

    $where[] = "(
        c.nombre LIKE '%{$buscar}%'
        OR tv.serie LIKE '%{$buscar}%'
        OR tv.numero LIKE '%{$buscar}%'
    )";
}

if (!empty($estadoVenta)) {

    $estadoVenta = mysqli_real_escape_string(
        $conexion,
        $estadoVenta
    );

    $where[] = "
        tv.estado_venta = '{$estadoVenta}'
    ";
}

if (!empty($estadoEnvio)) {

    $estadoEnvio = mysqli_real_escape_string(
        $conexion,
        $estadoEnvio
    );

    $where[] = "
        tv.estado_envio = '{$estadoEnvio}'
    ";
}

if (!empty($metodoPago)) {

    $metodoPago = (int)$metodoPago;

    $where[] = "
        tv.id_metodo_pago = {$metodoPago}
    ";
}

if (!empty($empleado)) {

    $empleado = (int)$empleado;

    $where[] = "
        tv.id_empleado = {$empleado}
    ";
}

if (!empty($fechaInicio)) {

    $where[] = "
        tv.fecha_venta >= '{$fechaInicio}'
    ";
}

if (!empty($fechaFin)) {

    $where[] = "
        tv.fecha_venta <= '{$fechaFin}'
    ";
}

$whereSQL = implode(" AND ", $where);

/*=========================================================
=            CONSULTA PRINCIPAL
=========================================================*/

$sqlVentas = "

SELECT

    tv.id_ticket_ventas,

    tv.fecha_venta,

    tv.hora_venta,

    tv.tipo_comprobante,

    tv.serie,

    tv.numero,

    tv.total_venta,

    tv.estado_venta,

    tv.estado_envio,

    c.nombre AS cliente,

    c.dni_o_ruc,

    c.celular,

    mp.nombre AS metodo_pago,

    CONCAT(
        IFNULL(e.nombre,''),
        ' ',
        IFNULL(e.apellido,'')
    ) AS empleado

FROM ticket_ventas tv

LEFT JOIN clientes c
ON c.idCliente = tv.idCliente

LEFT JOIN metodo_pago mp
ON mp.id_metodo_pago = tv.id_metodo_pago

LEFT JOIN empleados e
ON e.id_empleado = tv.id_empleado

WHERE {$whereSQL}

ORDER BY
tv.id_ticket_ventas DESC

";

$rsVentas = mysqli_query(
    $conexion,
    $sqlVentas
);

if (!$rsVentas) {

    die("Error SQL: " .
        mysqli_error($conexion));
}

/*=========================================================
=            KPI GENERALES
=========================================================*/

$sqlResumen = "

SELECT

    COUNT(*) total_ventas,

    COALESCE(
        SUM(total_venta),
        0
    ) total_ingresos,

    COALESCE(
        AVG(total_venta),
        0
    ) ticket_promedio,

    SUM(
        CASE
            WHEN estado_envio='ENTREGADO'
            THEN 1
            ELSE 0
        END
    ) entregados

FROM ticket_ventas tv

WHERE {$whereSQL}

";

$rsResumen = mysqli_query(
    $conexion,
    $sqlResumen
);

$resumen = mysqli_fetch_assoc(
    $rsResumen
);

$totalVentas      = (int)$resumen["total_ventas"];
$totalIngresos    = (float)$resumen["total_ingresos"];
$ticketPromedio   = (float)$resumen["ticket_promedio"];
$totalEntregados  = (int)$resumen["entregados"];

/*=========================================================
=            PDF CORPORATIVO
=========================================================*/

class PDF extends FPDF
{
    public $empresa = [];

    function Header()
    {
        /*=====================================
        LOGO
        =====================================*/

        $logo = "../assets/logos/logo.png";

        if (file_exists($logo)) {

            $this->Image(
                $logo,
                10,
                8,
                28
            );
        }

        /*=====================================
        TITULO
        =====================================*/

        $this->SetFont(
            'Arial',
            'B',
            18
        );

        $this->SetTextColor(
            13,
            110,
            253
        );

        $this->Cell(
            0,
            8,
            utf8_decode(
                'CoDevPro Technology'
            ),
            0,
            1,
            'C'
        );

        $this->SetFont(
            'Arial',
            '',
            10
        );

        $this->SetTextColor(
            100,
            100,
            100
        );

        $this->Cell(
            0,
            5,
            utf8_decode(
                'Reporte Profesional de Ventas'
            ),
            0,
            1,
            'C'
        );

        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);

        $this->SetFont(
            'Arial',
            'I',
            8
        );

        $this->SetTextColor(
            120,
            120,
            120
        );

        $this->Cell(
            0,
            10,
            utf8_decode(
                'CoDevPro Technology | Página '
            ) .
                $this->PageNo() .
                '/{nb}',
            0,
            0,
            'C'
        );
    }
}

/*=========================================================
=            CREAR PDF
=========================================================*/

$pdf = new PDF(
    'L',
    'mm',
    'A4'
);

$pdf->empresa = $empresa;

$pdf->AliasNbPages();

$pdf->SetAutoPageBreak(
    true,
    18
);

$pdf->AddPage();

$pdf->SetMargins(
    10,
    10,
    10
);
/*=========================================================
=            DASHBOARD EJECUTIVO
=========================================================*/

$pdf->SetFillColor(13, 110, 253);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(
    0,
    12,
    utf8_decode('RESUMEN EJECUTIVO DE VENTAS'),
    0,
    1,
    'C',
    true
);

$pdf->Ln(4);

/*=========================================================
=            DATOS EMPRESA
=========================================================*/

$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(
    40,
    7,
    utf8_decode('Empresa:')
);

$pdf->SetFont('Arial', '', 11);

$pdf->Cell(
    120,
    7,
    utf8_decode($empresa["nombreEmpresa"] ?? '-')
);

$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(
    25,
    7,
    'RUC:'
);

$pdf->SetFont('Arial', '', 11);

$pdf->Cell(
    0,
    7,
    $empresa["ruc"] ?? '-',
    0,
    1
);

$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(
    40,
    7,
    utf8_decode('Dirección:')
);

$pdf->SetFont('Arial', '', 11);

$pdf->Cell(
    120,
    7,
    utf8_decode($empresa["direccion"] ?? '-')
);

$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(
    25,
    7,
    'Celular:'
);

$pdf->SetFont('Arial', '', 11);

$pdf->Cell(
    0,
    7,
    $empresa["celular"] ?? '-',
    0,
    1
);

$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(
    40,
    7,
    'Email:'
);

$pdf->SetFont('Arial', '', 11);

$pdf->Cell(
    0,
    7,
    $empresa["email"] ?? '-',
    0,
    1
);

$pdf->Ln(4);

/*=========================================================
=            FECHA GENERACION
=========================================================*/

$pdf->SetFillColor(245, 247, 250);

$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(
    0,
    8,
    utf8_decode(
        'Generado el: ' .
            date('d/m/Y H:i:s')
    ),
    0,
    1,
    'R',
    true
);

$pdf->Ln(3);

/*=========================================================
=            FILTROS APLICADOS
=========================================================*/

$pdf->SetFillColor(33, 37, 41);

$pdf->SetTextColor(255, 255, 255);

$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(
    0,
    8,
    utf8_decode('FILTROS APLICADOS'),
    0,
    1,
    'L',
    true
);

$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', '', 10);

$filtrosTexto = [];

if ($buscar !== '') {
    $filtrosTexto[] = "Busqueda: " . $buscar;
}

if ($estadoVenta !== '') {
    $filtrosTexto[] = "Estado Venta: " . $estadoVenta;
}

if ($estadoEnvio !== '') {
    $filtrosTexto[] = "Estado Envio: " . $estadoEnvio;
}

if ($metodoPago !== '') {
    $filtrosTexto[] = "Metodo Pago ID: " . $metodoPago;
}

if ($empleado !== '') {
    $filtrosTexto[] = "Empleado ID: " . $empleado;
}

if ($fechaInicio !== '') {
    $filtrosTexto[] = "Desde: " . $fechaInicio;
}

if ($fechaFin !== '') {
    $filtrosTexto[] = "Hasta: " . $fechaFin;
}

if (empty($filtrosTexto)) {
    $filtrosTexto[] = "Sin filtros";
}

$pdf->MultiCell(
    0,
    7,
    utf8_decode(
        implode(" | ", $filtrosTexto)
    ),
    1
);

$pdf->Ln(5);

/*=========================================================
=            KPI CARDS
=========================================================*/

$anchoCard = 67;

$pdf->SetFont('Arial', 'B', 11);

/*=====================================
TOTAL VENTAS
=====================================*/

$pdf->SetFillColor(13, 110, 253);

$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(
    $anchoCard,
    10,
    'TOTAL VENTAS',
    1,
    0,
    'C',
    true
);

/*=====================================
INGRESOS
=====================================*/

$pdf->SetFillColor(25, 135, 84);

$pdf->Cell(
    $anchoCard,
    10,
    'INGRESOS',
    1,
    0,
    'C',
    true
);

/*=====================================
TICKET
=====================================*/

$pdf->SetFillColor(255, 193, 7);

$pdf->SetTextColor(0, 0, 0);

$pdf->Cell(
    $anchoCard,
    10,
    'TICKET PROMEDIO',
    1,
    0,
    'C',
    true
);

/*=====================================
ENTREGADOS
=====================================*/

$pdf->SetFillColor(220, 53, 69);

$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(
    $anchoCard,
    10,
    'ENTREGADOS',
    1,
    1,
    'C',
    true
);

/*=========================================================
=            VALORES KPI
=========================================================*/

$pdf->SetFont('Arial', 'B', 13);

$pdf->SetTextColor(0, 0, 0);

$pdf->SetFillColor(255, 255, 255);

$pdf->Cell(
    $anchoCard,
    14,
    number_format($totalVentas),
    1,
    0,
    'C'
);

$pdf->Cell(
    $anchoCard,
    14,
    'S/ ' . number_format(
        $totalIngresos,
        2
    ),
    1,
    0,
    'C'
);

$pdf->Cell(
    $anchoCard,
    14,
    'S/ ' . number_format(
        $ticketPromedio,
        2
    ),
    1,
    0,
    'C'
);

$pdf->Cell(
    $anchoCard,
    14,
    number_format(
        $totalEntregados
    ),
    1,
    1,
    'C'
);

$pdf->Ln(8);

/*=========================================================
=            TITULO TABLA
=========================================================*/

$pdf->SetFillColor(13, 110, 253);

$pdf->SetTextColor(255, 255, 255);

$pdf->SetFont('Arial', 'B', 12);

$pdf->Cell(
    0,
    10,
    utf8_decode('DETALLE DE VENTAS'),
    0,
    1,
    'C',
    true
);

$pdf->Ln(2);
/*=========================================================
=            CONFIGURACION TABLA
=========================================================*/

$anchoColumnas = [

    'id'          => 12,
    'fecha'       => 22,
    'cliente'     => 55,
    'comprobante' => 32,
    'pago'        => 28,
    'estado'      => 28,
    'total'       => 28

];

$pdf->SetFont('Arial', 'B', 9);

$pdf->SetFillColor(13, 110, 253);

$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(
    $anchoColumnas['id'],
    8,
    '#',
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    $anchoColumnas['fecha'],
    8,
    'Fecha',
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    $anchoColumnas['cliente'],
    8,
    utf8_decode('Cliente'),
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    $anchoColumnas['comprobante'],
    8,
    'Comp.',
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    $anchoColumnas['pago'],
    8,
    'Pago',
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    $anchoColumnas['estado'],
    8,
    'Envio',
    1,
    0,
    'C',
    true
);

$pdf->Cell(
    $anchoColumnas['total'],
    8,
    'Total',
    1,
    1,
    'C',
    true
);

/*=========================================================
=            VARIABLES ACUMULADORAS
=========================================================*/

$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', '', 8);

$fila = 0;

$totalVentas       = 0;
$totalIngresos     = 0;
$totalEntregados   = 0;
$totalCancelados   = 0;

while ($venta = mysqli_fetch_assoc($resultadoVentas)) {

    $fila++;

    $totalVentas++;

    $totalIngresos += (float)$venta["total_venta"];

    if ($venta["estado_envio"] === "ENTREGADO") {
        $totalEntregados++;
    }

    if ($venta["estado_envio"] === "CANCELADO") {
        $totalCancelados++;
    }

    /*=====================================
    SALTO DE PAGINA
    =====================================*/

    if ($pdf->GetY() > 260) {

        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 9);

        $pdf->SetFillColor(13, 110, 253);

        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell($anchoColumnas['id'], 8, '#', 1, 0, 'C', true);
        $pdf->Cell($anchoColumnas['fecha'], 8, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell($anchoColumnas['cliente'], 8, 'Cliente', 1, 0, 'C', true);
        $pdf->Cell($anchoColumnas['comprobante'], 8, 'Comp.', 1, 0, 'C', true);
        $pdf->Cell($anchoColumnas['pago'], 8, 'Pago', 1, 0, 'C', true);
        $pdf->Cell($anchoColumnas['estado'], 8, 'Envio', 1, 0, 'C', true);
        $pdf->Cell($anchoColumnas['total'], 8, 'Total', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 8);

        $pdf->SetTextColor(0, 0, 0);
    }

    /*=====================================
    COLOR CEBRA
    =====================================*/

    if ($fila % 2 == 0) {

        $pdf->SetFillColor(
            245,
            247,
            250
        );
    } else {

        $pdf->SetFillColor(
            255,
            255,
            255
        );
    }

    /*=====================================
    DATOS
    =====================================*/

    $cliente = utf8_decode(
        mb_substr(
            $venta["cliente"],
            0,
            28
        )
    );

    $comprobante = utf8_decode(
        $venta["serie"] .
            '-' .
            str_pad(
                $venta["numero"],
                8,
                "0",
                STR_PAD_LEFT
            )
    );

    $metodoPago = utf8_decode(
        mb_substr(
            $venta["metodo_pago"],
            0,
            12
        )
    );

    $estadoEnvio = utf8_decode(
        $venta["estado_envio"]
    );

    $pdf->Cell(
        $anchoColumnas['id'],
        8,
        $venta["id_ticket_ventas"],
        1,
        0,
        'C',
        true
    );

    $pdf->Cell(
        $anchoColumnas['fecha'],
        8,
        date(
            'd/m/Y',
            strtotime(
                $venta["fecha_venta"]
            )
        ),
        1,
        0,
        'C',
        true
    );

    $pdf->Cell(
        $anchoColumnas['cliente'],
        8,
        $cliente,
        1,
        0,
        'L',
        true
    );

    $pdf->Cell(
        $anchoColumnas['comprobante'],
        8,
        $comprobante,
        1,
        0,
        'C',
        true
    );

    $pdf->Cell(
        $anchoColumnas['pago'],
        8,
        $metodoPago,
        1,
        0,
        'C',
        true
    );

    $pdf->Cell(
        $anchoColumnas['estado'],
        8,
        $estadoEnvio,
        1,
        0,
        'C',
        true
    );

    $pdf->Cell(
        $anchoColumnas['total'],
        8,
        'S/ ' .
            number_format(
                $venta["total_venta"],
                2
            ),
        1,
        1,
        'R',
        true
    );
}
/*=========================================================
=            RESUMEN EJECUTIVO
=========================================================*/

$pdf->Ln(8);

$pdf->SetFillColor(13, 110, 253);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(190, 10, utf8_decode('RESUMEN GENERAL DE VENTAS'), 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(95, 8, 'Total de ventas registradas:', 1, 0);
$pdf->Cell(95, 8, number_format($totalVentas), 1, 1, 'R');

$pdf->Cell(95, 8, 'Monto total vendido:', 1, 0);
$pdf->Cell(
    95,
    8,
    'S/ ' . number_format($totalMontoVentas, 2),
    1,
    1,
    'R'
);

$pdf->Cell(95, 8, 'Ticket promedio:', 1, 0);
$pdf->Cell(
    95,
    8,
    'S/ ' . number_format($ticketPromedio, 2),
    1,
    1,
    'R'
);


/*=========================================================
=            SALTO DE PAGINA
=========================================================*/

$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(13, 110, 253);

$pdf->Cell(
    190,
    10,
    utf8_decode('DETALLE COMPLETO DE VENTAS'),
    0,
    1,
    'C'
);

$pdf->Ln(4);


/*=========================================================
=            TABLA DE VENTAS
=========================================================*/

$pdf->SetFillColor(13, 110, 253);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8);

$pdf->Cell(10, 8, '#', 1, 0, 'C', true);
$pdf->Cell(22, 8, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(45, 8, 'Cliente', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Comprobante', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Metodo Pago', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Estado', 1, 0, 'C', true);
$pdf->Cell(38, 8, 'Total', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 8);

$item = 1;

foreach ($ventas as $venta) {

    if ($pdf->GetY() > 260) {

        $pdf->AddPage();

        $pdf->SetFillColor(13, 110, 253);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 8);

        $pdf->Cell(10, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(22, 8, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(45, 8, 'Cliente', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Comprobante', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Metodo Pago', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Estado', 1, 0, 'C', true);
        $pdf->Cell(38, 8, 'Total', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8);
    }

    $cliente = substr(
        utf8_decode($venta['cliente']),
        0,
        28
    );

    $comprobante =
        $venta['serie'] . '-' .
        str_pad($venta['numero'], 8, '0', STR_PAD_LEFT);

    $pdf->Cell(10, 7, $item, 1, 0, 'C');

    $pdf->Cell(
        22,
        7,
        date(
            'd/m/Y',
            strtotime($venta['fecha_venta'])
        ),
        1,
        0,
        'C'
    );

    $pdf->Cell(
        45,
        7,
        $cliente,
        1,
        0
    );

    $pdf->Cell(
        25,
        7,
        utf8_decode($comprobante),
        1,
        0,
        'C'
    );

    $pdf->Cell(
        25,
        7,
        utf8_decode($venta['metodo_pago']),
        1,
        0,
        'C'
    );

    $pdf->Cell(
        25,
        7,
        utf8_decode($venta['estado_envio']),
        1,
        0,
        'C'
    );

    $pdf->Cell(
        38,
        7,
        'S/ ' . number_format(
            $venta['total_venta'],
            2
        ),
        1,
        1,
        'R'
    );

    $item++;
}


/*=========================================================
=            TOTAL GENERAL
=========================================================*/

$pdf->SetFont('Arial', 'B', 10);

$pdf->SetFillColor(40, 167, 69);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(
    152,
    10,
    utf8_decode('TOTAL GENERAL'),
    1,
    0,
    'R',
    true
);

$pdf->Cell(
    38,
    10,
    'S/ ' . number_format(
        $totalMontoVentas,
        2
    ),
    1,
    1,
    'R',
    true
);


/*=========================================================
=            PRODUCTOS VENDIDOS
=========================================================*/

if ($incluirProductos && !empty($productosVendidos)) {

    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 13);
    $pdf->SetTextColor(13, 110, 253);

    $pdf->Cell(
        190,
        10,
        utf8_decode('PRODUCTOS VENDIDOS'),
        0,
        1,
        'C'
    );

    $pdf->Ln(3);

    $pdf->SetFillColor(13, 110, 253);
    $pdf->SetTextColor(255, 255, 255);

    $pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
    $pdf->Cell(90, 8, 'Producto', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Cantidad', 1, 0, 'C', true);
    $pdf->Cell(45, 8, 'Ingresos', 1, 1, 'C', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 8);

    foreach ($productosVendidos as $producto) {

        $pdf->Cell(
            15,
            7,
            $producto['idProducto'],
            1,
            0,
            'C'
        );

        $pdf->Cell(
            90,
            7,
            utf8_decode(
                substr($producto['producto'], 0, 50)
            ),
            1,
            0
        );

        $pdf->Cell(
            40,
            7,
            number_format(
                $producto['cantidad_vendida']
            ),
            1,
            0,
            'C'
        );

        $pdf->Cell(
            45,
            7,
            'S/ ' . number_format(
                $producto['ingresos'],
                2
            ),
            1,
            1,
            'R'
        );
    }
}


/*=========================================================
=            PIE DE PAGINA CORPORATIVO
=========================================================*/

$pdf->SetY(-20);

$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(120, 120, 120);

$pdf->Cell(
    0,
    5,
    utf8_decode(
        'Generado por CoDevPro Technology'
    ),
    0,
    1,
    'C'
);

$pdf->Cell(
    0,
    5,
    utf8_decode(
        'Fecha: ' . date('d/m/Y H:i:s')
    ),
    0,
    1,
    'C'
);


/*=========================================================
=            DESCARGAR PDF
=========================================================*/

$nombreArchivo =
    'Reporte_Ventas_' .
    date('Ymd_His') .
    '.pdf';

$pdf->Output(
    'D',
    $nombreArchivo
);

exit;
