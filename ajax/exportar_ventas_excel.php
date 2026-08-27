<?php
//=========================================================
// CoDevPro Technology
// Archivo: ajax/exportar_ventas_excel.php
//=========================================================

session_start();

if (!isset($_SESSION["idUser"])) {

    exit("Acceso denegado");
}

require_once "../controladores/conexion.php";
require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/*=========================================================
=            DATOS USUARIO
=========================================================*/

$idUser = (int)$_SESSION["idUser"];

/*=========================================================
=            CAMPOS SELECCIONADOS
=========================================================*/

$campos = [];

if (isset($_GET["campos"])) {

    $campos = json_decode($_GET["campos"], true);
}

if (!is_array($campos)) {

    $campos = [];
}

$incluirProductos = isset($_GET["productos"])
    ? (int)$_GET["productos"]
    : 0;

/*=========================================================
=            FILTROS
=========================================================*/

$buscar        = trim($_GET["buscar"] ?? "");
$estadoVenta   = trim($_GET["estadoVenta"] ?? "");
$estadoEnvio   = trim($_GET["estadoEnvio"] ?? "");
$metodoPago    = trim($_GET["metodoPago"] ?? "");
$empleado      = trim($_GET["empleado"] ?? "");
$fechaInicio   = trim($_GET["fechaInicio"] ?? "");
$fechaFin      = trim($_GET["fechaFin"] ?? "");

/*=========================================================
=            OBTENER EMPRESA
=========================================================*/

$sqlEmpresa = "
SELECT
    nombreEmpresa,
    ruc,
    direccion,
    celular,
    email
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

mysqli_stmt_close($stmtEmpresa);

/*=========================================================
=            QUERY PRINCIPAL
=========================================================*/

$sql = "
SELECT

tv.id_ticket_ventas,
tv.fecha_venta,
tv.hora_venta,
tv.tipo_comprobante,
tv.serie,
tv.numero,
tv.total_venta,
tv.pago_cliente,
tv.vuelto_venta,
tv.estado_venta,
tv.estado_envio,
tv.direccion_envio,

c.nombre AS cliente,
c.dni_o_ruc,
c.celular,
c.email,

mp.nombre AS metodo_pago,

CONCAT(
    COALESCE(e.nombre,''),
    ' ',
    COALESCE(e.apellido,'')
) AS empleado

FROM ticket_ventas tv

LEFT JOIN clientes c
ON tv.idCliente = c.idCliente

LEFT JOIN metodo_pago mp
ON tv.id_metodo_pago = mp.id_metodo_pago

LEFT JOIN empleados e
ON tv.id_empleado = e.id_empleado

WHERE tv.id_user = ?
";

$tipos = "i";
$parametros = [$idUser];

/*=========================================================
=            BUSQUEDA
=========================================================*/

if (!empty($buscar)) {

    $sql .= "
    AND (
        c.nombre LIKE ?
        OR tv.serie LIKE ?
        OR tv.numero LIKE ?
    )
    ";

    $like = "%{$buscar}%";

    $tipos .= "sss";

    $parametros[] = $like;
    $parametros[] = $like;
    $parametros[] = $like;
}

/*=========================================================
=            ESTADO VENTA
=========================================================*/

if (!empty($estadoVenta)) {

    $sql .= " AND tv.estado_venta = ? ";

    $tipos .= "s";

    $parametros[] = $estadoVenta;
}

/*=========================================================
=            ESTADO ENVIO
=========================================================*/

if (!empty($estadoEnvio)) {

    $sql .= " AND tv.estado_envio = ? ";

    $tipos .= "s";

    $parametros[] = $estadoEnvio;
}

/*=========================================================
=            METODO PAGO
=========================================================*/

if (!empty($metodoPago)) {

    $sql .= " AND tv.id_metodo_pago = ? ";

    $tipos .= "i";

    $parametros[] = $metodoPago;
}

/*=========================================================
=            EMPLEADO
=========================================================*/

if (!empty($empleado)) {

    $sql .= " AND tv.id_empleado = ? ";

    $tipos .= "i";

    $parametros[] = $empleado;
}

/*=========================================================
=            FECHAS
=========================================================*/

if (!empty($fechaInicio)) {

    $sql .= " AND tv.fecha_venta >= ? ";

    $tipos .= "s";

    $parametros[] = $fechaInicio;
}

if (!empty($fechaFin)) {

    $sql .= " AND tv.fecha_venta <= ? ";

    $tipos .= "s";

    $parametros[] = $fechaFin;
}

$sql .= "
ORDER BY
tv.id_ticket_ventas DESC
";

/*=========================================================
=            EJECUTAR QUERY
=========================================================*/

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    $tipos,
    ...$parametros
);

mysqli_stmt_execute($stmt);

$resultadoVentas = mysqli_stmt_get_result($stmt);

/*=========================================================
=            CREAR EXCEL
=========================================================*/

$spreadsheet = new Spreadsheet();

$hoja = $spreadsheet->getActiveSheet();

$hoja->setTitle("Ventas");

/*=========================================================
=            PROPIEDADES
=========================================================*/

$spreadsheet->getProperties()
    ->setCreator("CoDevPro Technology")
    ->setCompany("CoDevPro Technology")
    ->setTitle("Reporte de Ventas")
    ->setDescription("Reporte profesional de ventas");

/*=========================================================
=            LOGO EMPRESA
=========================================================*/

$logo = "../assets/logos/logo.png";

if (file_exists($logo)) {

    $drawing = new Drawing();

    $drawing->setName("Logo");

    $drawing->setDescription("CoDevPro Technology");

    $drawing->setPath($logo);

    $drawing->setHeight(75);

    $drawing->setCoordinates("A1");

    $drawing->setWorksheet($hoja);
}

/*=========================================================
=            TITULO PRINCIPAL
=========================================================*/

$hoja->mergeCells("C1:J2");

$hoja->setCellValue(
    "C1",
    "REPORTE GENERAL DE VENTAS"
);

$hoja->getStyle("C1:J2")->applyFromArray([
    "font" => [
        "bold" => true,
        "size" => 18,
        "color" => [
            "rgb" => "FFFFFF"
        ]
    ],
    "alignment" => [
        "horizontal" => Alignment::HORIZONTAL_CENTER,
        "vertical"   => Alignment::VERTICAL_CENTER
    ],
    "fill" => [
        "fillType" => Fill::FILL_SOLID,
        "startColor" => [
            "rgb" => "0D6EFD"
        ]
    ]
]);

/*=========================================================
=            DATOS EMPRESA
=========================================================*/

$hoja->mergeCells("C3:J3");

$hoja->setCellValue(
    "C3",
    $empresa["nombreEmpresa"] ?? "CoDevPro Technology"
);

$hoja->mergeCells("C4:J4");

$hoja->setCellValue(
    "C4",
    "RUC: " . ($empresa["ruc"] ?? "")
);

$hoja->mergeCells("C5:J5");

$hoja->setCellValue(
    "C5",
    "Dirección: " . ($empresa["direccion"] ?? "")
);

$hoja->mergeCells("C6:J6");

$hoja->setCellValue(
    "C6",
    "Generado: " . date("d/m/Y H:i:s")
);

$hoja->getStyle("C3:J6")->applyFromArray([
    "font" => [
        "bold" => true,
        "size" => 10
    ]
]);

/*=========================================================
=            FILA DE INICIO TABLA
=========================================================*/

$filaCabecera = 9;
/*=========================================================
=            COLUMNAS DINAMICAS
=========================================================*/

$columnasDisponibles = [

    "id_ticket_ventas" => "ID Venta",
    "fecha_venta"      => "Fecha Venta",
    "tipo_comprobante" => "Comprobante",
    "total_venta"      => "Total Venta",
    "estado_venta"     => "Estado Venta",
    "estado_envio"     => "Estado Envío",

    "cliente"          => "Cliente",
    "dni_o_ruc"        => "DNI / RUC",
    "celular"          => "Celular",
    "metodo_pago"      => "Método Pago",
    "empleado"         => "Empleado"

];

if (empty($campos)) {

    $campos = [
        "id_ticket_ventas",
        "fecha_venta",
        "tipo_comprobante",
        "cliente",
        "metodo_pago",
        "estado_venta",
        "estado_envio",
        "total_venta"
    ];
}

/*=========================================================
=            CABECERAS
=========================================================*/

$columna = "A";

foreach ($campos as $campo) {

    $titulo = $columnasDisponibles[$campo] ?? strtoupper($campo);

    $hoja->setCellValue(
        $columna . $filaCabecera,
        $titulo
    );

    $columna++;
}

/*=========================================================
=            ESTILO CABECERAS PREMIUM
=========================================================*/

$ultimaColumna =
    chr(ord("A") + count($campos) - 1);

$rangoCabecera =
    "A{$filaCabecera}:{$ultimaColumna}{$filaCabecera}";

$hoja->getStyle($rangoCabecera)
    ->applyFromArray([

        "font" => [

            "bold" => true,

            "size" => 11,

            "color" => [
                "rgb" => "FFFFFF"
            ]
        ],

        "fill" => [

            "fillType" => Fill::FILL_SOLID,

            "startColor" => [
                "rgb" => "198754"
            ]
        ],

        "alignment" => [

            "horizontal" =>
            Alignment::HORIZONTAL_CENTER,

            "vertical" =>
            Alignment::VERTICAL_CENTER
        ],

        "borders" => [

            "allBorders" => [

                "borderStyle" =>
                Border::BORDER_THIN,

                "color" => [
                    "rgb" => "DADADA"
                ]
            ]
        ]
    ]);

/*=========================================================
=            ALTURA CABECERA
=========================================================*/

$hoja->getRowDimension($filaCabecera)
    ->setRowHeight(24);

/*=========================================================
=            LLENAR DATOS
=========================================================*/

$fila = $filaCabecera + 1;

$totalGeneral = 0;
$cantidadVentas = 0;

while ($venta = mysqli_fetch_assoc($resultadoVentas)) {

    $columna = "A";

    foreach ($campos as $campo) {

        switch ($campo) {

            case "tipo_comprobante":

                $valor =
                    $venta["tipo_comprobante"] .
                    " " .
                    $venta["serie"] .
                    "-" .
                    str_pad(
                        $venta["numero"],
                        8,
                        "0",
                        STR_PAD_LEFT
                    );

                break;

            default:

                $valor =
                    $venta[$campo] ?? "";
        }

        $hoja->setCellValue(
            $columna . $fila,
            $valor
        );

        $columna++;
    }

    if (in_array("total_venta", $campos)) {

        $totalGeneral +=
            (float)$venta["total_venta"];
    }

    $cantidadVentas++;

    $fila++;
}

/*=========================================================
=            ESTILO CUERPO TABLA
=========================================================*/

$rangoDatos =
    "A" . ($filaCabecera + 1) .
    ":" .
    $ultimaColumna .
    ($fila - 1);

$hoja->getStyle($rangoDatos)
    ->applyFromArray([

        "borders" => [

            "allBorders" => [

                "borderStyle" =>
                Border::BORDER_THIN,

                "color" => [
                    "rgb" => "E5E5E5"
                ]
            ]
        ],

        "alignment" => [

            "vertical" =>
            Alignment::VERTICAL_CENTER
        ]
    ]);

/*=========================================================
=            FORMATO MONEDA
=========================================================*/

if (in_array("total_venta", $campos)) {

    $indiceTotal =
        array_search(
            "total_venta",
            $campos
        );

    $columnaTotal =
        chr(ord("A") + $indiceTotal);

    $hoja->getStyle(
        "{$columnaTotal}" .
            ($filaCabecera + 1) .
            ":" .
            "{$columnaTotal}" .
            ($fila - 1)
    )->getNumberFormat()
        ->setFormatCode(
            '"S/" #,##0.00'
        );
}

/*=========================================================
=            COLORES POR ESTADO VENTA
=========================================================*/

if (in_array("estado_venta", $campos)) {

    $indiceEstado =
        array_search(
            "estado_venta",
            $campos
        );

    $colEstado =
        chr(ord("A") + $indiceEstado);

    for (
        $i = $filaCabecera + 1;
        $i < $fila;
        $i++
    ) {

        $estado =
            trim(
                $hoja->getCell(
                    $colEstado . $i
                )->getValue()
            );

        $color = "FFFFFF";

        switch ($estado) {

            case "PAGADO":
                $color = "D1E7DD";
                break;

            case "PENDIENTE":
                $color = "FFF3CD";
                break;

            case "ANULADO":
                $color = "F8D7DA";
                break;
        }

        $hoja->getStyle(
            $colEstado . $i
        )->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB($color);
    }
}

/*=========================================================
=            COLORES ESTADO ENVIO
=========================================================*/

if (in_array("estado_envio", $campos)) {

    $indiceEnvio =
        array_search(
            "estado_envio",
            $campos
        );

    $colEnvio =
        chr(ord("A") + $indiceEnvio);

    for (
        $i = $filaCabecera + 1;
        $i < $fila;
        $i++
    ) {

        $estado =
            trim(
                $hoja->getCell(
                    $colEnvio . $i
                )->getValue()
            );

        $color = "FFFFFF";

        switch ($estado) {

            case "PENDIENTE":
                $color = "FFF3CD";
                break;

            case "CONFIRMADO":
                $color = "CFE2FF";
                break;

            case "PREPARANDO":
                $color = "FFE5B4";
                break;

            case "ENVIADO":
                $color = "D1ECF1";
                break;

            case "ENTREGADO":
                $color = "D1E7DD";
                break;

            case "CANCELADO":
                $color = "F8D7DA";
                break;
        }

        $hoja->getStyle(
            $colEnvio . $i
        )->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setRGB($color);
    }
}

/*=========================================================
=            FILA TOTALES
=========================================================*/

$filaTotales = $fila + 2;

$hoja->mergeCells(
    "A{$filaTotales}:D{$filaTotales}"
);

$hoja->setCellValue(
    "A{$filaTotales}",
    "RESUMEN GENERAL"
);

$hoja->getStyle(
    "A{$filaTotales}:D{$filaTotales}"
)->applyFromArray([

    "font" => [
        "bold" => true,
        "size" => 12,
        "color" => [
            "rgb" => "FFFFFF"
        ]
    ],

    "fill" => [
        "fillType" => Fill::FILL_SOLID,
        "startColor" => [
            "rgb" => "0D6EFD"
        ]
    ]
]);

$filaTotales++;

$hoja->setCellValue(
    "A{$filaTotales}",
    "Cantidad de Ventas"
);

$hoja->setCellValue(
    "B{$filaTotales}",
    $cantidadVentas
);

$filaTotales++;

$hoja->setCellValue(
    "A{$filaTotales}",
    "Monto Total"
);

$hoja->setCellValue(
    "B{$filaTotales}",
    $totalGeneral
);

$hoja->getStyle(
    "B{$filaTotales}"
)->getNumberFormat()
    ->setFormatCode(
        '"S/" #,##0.00'
    );

/*=========================================================
=            AUTOFILTROS
=========================================================*/

$hoja->setAutoFilter(
    $rangoCabecera
);

/*=========================================================
=            CONGELAR CABECERA
=========================================================*/

$hoja->freezePane(
    "A" . ($filaCabecera + 1)
);

/*=========================================================
=            AUTOAJUSTAR COLUMNAS
=========================================================*/

foreach (
    range(
        "A",
        $ultimaColumna
    ) as $col
) {

    $hoja->getColumnDimension($col)
        ->setAutoSize(true);
}

/*=========================================================
=            ORIENTACION Y MARGENES
=========================================================*/

$hoja->getPageSetup()
    ->setOrientation(
        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
    );

$hoja->getPageSetup()
    ->setFitToWidth(1);

$hoja->getPageMargins()
    ->setTop(0.5)
    ->setBottom(0.5)
    ->setLeft(0.3)
    ->setRight(0.3);

/*=========================================================
=            PARTE 3
=            HOJA PRODUCTOS VENDIDOS
=========================================================*/

if ($incluirProductos) {

    $hojaProductos = $spreadsheet->createSheet();

    $hojaProductos->setTitle("Productos Vendidos");

    /*=====================================================
    =            TITULO
    =====================================================*/

    $hojaProductos->mergeCells("A1:H2");

    $hojaProductos->setCellValue(
        "A1",
        "REPORTE DE PRODUCTOS VENDIDOS"
    );

    $hojaProductos->getStyle("A1")
        ->applyFromArray([

            "font" => [
                "bold" => true,
                "size" => 18,
                "color" => ["rgb" => "FFFFFF"]
            ],

            "alignment" => [
                "horizontal" => Alignment::HORIZONTAL_CENTER,
                "vertical"   => Alignment::VERTICAL_CENTER
            ],

            "fill" => [
                "fillType" => Fill::FILL_SOLID,
                "startColor" => [
                    "rgb" => "198754"
                ]
            ]
        ]);

    $hojaProductos->getRowDimension(1)
        ->setRowHeight(30);

    /*=====================================================
    =            CABECERAS
    =====================================================*/

    $filaProductos = 5;

    $cabecerasProductos = [

        "ID Producto",
        "Código",
        "Producto",
        "Categoría",
        "Marca",
        "Cantidad Vendida",
        "Precio",
        "Total Generado"

    ];

    $col = "A";

    foreach ($cabecerasProductos as $titulo) {

        $hojaProductos->setCellValue(
            $col . $filaProductos,
            $titulo
        );

        $col++;
    }

    $hojaProductos->getStyle(
        "A{$filaProductos}:H{$filaProductos}"
    )->applyFromArray([

        "font" => [
            "bold" => true,
            "color" => [
                "rgb" => "FFFFFF"
            ]
        ],

        "fill" => [
            "fillType" => Fill::FILL_SOLID,
            "startColor" => [
                "rgb" => "0D6EFD"
            ]
        ],

        "alignment" => [
            "horizontal" =>
            Alignment::HORIZONTAL_CENTER
        ],

        "borders" => [
            "allBorders" => [
                "borderStyle" =>
                Border::BORDER_THIN
            ]
        ]
    ]);

    /*=====================================================
    =            CONSULTA PRODUCTOS
    =====================================================*/

    $sqlProductos = "

        SELECT

            p.idProducto,
            p.codigo,
            p.nombre,

            COALESCE(c.nombre,'Sin categoría')
            AS categoria,

            COALESCE(m.nombre,'Sin marca')
            AS marca,

            SUM(dtv.cantidad_pedido_producto)
            AS cantidad_vendida,

            p.precio,

            SUM(dtv.sub_total)
            AS total_generado

        FROM detalle_ticket_ventas dtv

        INNER JOIN producto p
            ON p.idProducto = dtv.idProducto

        LEFT JOIN categorias c
            ON c.id_categorias = p.id_categorias

        LEFT JOIN marcas m
            ON m.id_marca = p.id_marca

        INNER JOIN ticket_ventas tv
            ON tv.id_ticket_ventas =
               dtv.id_ticket_ventas

        WHERE tv.id_user = {$idUser}

        GROUP BY p.idProducto

        ORDER BY cantidad_vendida DESC

    ";

    $resultadoProductos =
        mysqli_query(
            $conexion,
            $sqlProductos
        );

    /*=====================================================
    =            DATOS
    =====================================================*/

    $filaProductos++;

    $totalCantidadVendida = 0;
    $totalIngresosProductos = 0;

    while (
        $producto =
        mysqli_fetch_assoc(
            $resultadoProductos
        )
    ) {

        $hojaProductos->setCellValue(
            "A{$filaProductos}",
            $producto["idProducto"]
        );

        $hojaProductos->setCellValue(
            "B{$filaProductos}",
            $producto["codigo"]
        );

        $hojaProductos->setCellValue(
            "C{$filaProductos}",
            $producto["nombre"]
        );

        $hojaProductos->setCellValue(
            "D{$filaProductos}",
            $producto["categoria"]
        );

        $hojaProductos->setCellValue(
            "E{$filaProductos}",
            $producto["marca"]
        );

        $hojaProductos->setCellValue(
            "F{$filaProductos}",
            $producto["cantidad_vendida"]
        );

        $hojaProductos->setCellValue(
            "G{$filaProductos}",
            $producto["precio"]
        );

        $hojaProductos->setCellValue(
            "H{$filaProductos}",
            $producto["total_generado"]
        );

        $totalCantidadVendida +=
            (int)$producto["cantidad_vendida"];

        $totalIngresosProductos +=
            (float)$producto["total_generado"];

        $filaProductos++;
    }

    /*=====================================================
    =            ESTILO CUERPO TABLA
    =====================================================*/

    $hojaProductos->getStyle(
        "A6:H" . ($filaProductos - 1)
    )->applyFromArray([

        "borders" => [

            "allBorders" => [

                "borderStyle" =>
                Border::BORDER_THIN,

                "color" => [
                    "rgb" => "E0E0E0"
                ]
            ]
        ]
    ]);

    /*=====================================================
    =            FORMATO MONEDA
    =====================================================*/

    $hojaProductos->getStyle(
        "G6:H" . ($filaProductos - 1)
    )->getNumberFormat()
        ->setFormatCode(
            '"S/" #,##0.00'
        );

    /*=====================================================
    =            RESUMEN PRODUCTOS
    =====================================================*/

    $filaResumen =
        $filaProductos + 2;

    $hojaProductos->mergeCells(
        "A{$filaResumen}:D{$filaResumen}"
    );

    $hojaProductos->setCellValue(
        "A{$filaResumen}",
        "RESUMEN DE PRODUCTOS"
    );

    $hojaProductos->getStyle(
        "A{$filaResumen}:D{$filaResumen}"
    )->applyFromArray([

        "font" => [
            "bold" => true,
            "size" => 12,
            "color" => [
                "rgb" => "FFFFFF"
            ]
        ],

        "fill" => [
            "fillType" => Fill::FILL_SOLID,
            "startColor" => [
                "rgb" => "198754"
            ]
        ]
    ]);

    $filaResumen++;

    $hojaProductos->setCellValue(
        "A{$filaResumen}",
        "Cantidad Total Vendida"
    );

    $hojaProductos->setCellValue(
        "B{$filaResumen}",
        $totalCantidadVendida
    );

    $filaResumen++;

    $hojaProductos->setCellValue(
        "A{$filaResumen}",
        "Ingresos Generados"
    );

    $hojaProductos->setCellValue(
        "B{$filaResumen}",
        $totalIngresosProductos
    );

    $hojaProductos->getStyle(
        "B{$filaResumen}"
    )->getNumberFormat()
        ->setFormatCode(
            '"S/" #,##0.00'
        );

    /*=====================================================
    =            AUTOFILTRO
    =====================================================*/

    $hojaProductos->setAutoFilter(
        "A5:H5"
    );

    /*=====================================================
    =            CONGELAR CABECERA
    =====================================================*/

    $hojaProductos->freezePane("A6");

    /*=====================================================
    =            AUTO AJUSTAR
    =====================================================*/

    foreach (range("A", "H") as $columna) {

        $hojaProductos
            ->getColumnDimension($columna)
            ->setAutoSize(true);
    }
}

/*=========================================================
=            PARTE 4
=            DASHBOARD EJECUTIVO + DESCARGA XLSX
=========================================================*/
/*=========================================================
=            KPI DASHBOARD
=========================================================*/

$sqlKpi = "

    SELECT

        COUNT(*) AS total_ventas,

        COALESCE(SUM(total_venta),0) AS monto_total,

        COALESCE(AVG(total_venta),0) AS ticket_promedio

    FROM ticket_ventas

    WHERE id_user = {$idUser}

";

$rsKpi = mysqli_query($conexion, $sqlKpi);

$kpi = mysqli_fetch_assoc($rsKpi);

$totalVentas      = (int)$kpi["total_ventas"];
$totalMontoVentas = (float)$kpi["monto_total"];
$ticketPromedio   = (float)$kpi["ticket_promedio"];
/*=========================================================
=            HOJA DASHBOARD EJECUTIVO
=========================================================*/

$dashboard = $spreadsheet->createSheet();

$dashboard->setTitle("Dashboard");

/*=========================================================
=            TITULO
=========================================================*/

$dashboard->mergeCells("A1:H2");

$dashboard->setCellValue(
    "A1",
    "DASHBOARD EJECUTIVO - CoDevPro Technology"
);

$dashboard->getStyle("A1")->applyFromArray([

    "font" => [
        "bold" => true,
        "size" => 20,
        "color" => ["rgb" => "FFFFFF"]
    ],

    "alignment" => [
        "horizontal" => Alignment::HORIZONTAL_CENTER,
        "vertical"   => Alignment::VERTICAL_CENTER
    ],

    "fill" => [
        "fillType" => Fill::FILL_SOLID,
        "startColor" => [
            "rgb" => "0D6EFD"
        ]
    ]
]);

/*=========================================================
=            TARJETAS KPI
=========================================================*/

$filaKpi = 5;

$dashboard->setCellValue("A{$filaKpi}", "Total Ventas");
$dashboard->setCellValue("B{$filaKpi}", $totalVentas);

$dashboard->setCellValue("D{$filaKpi}", "Ingresos");
$dashboard->setCellValue("E{$filaKpi}", $totalMontoVentas);

$dashboard->setCellValue("G{$filaKpi}", "Ticket Promedio");
$dashboard->setCellValue("H{$filaKpi}", $ticketPromedio);

$dashboard->getStyle("A{$filaKpi}:H{$filaKpi}")
    ->applyFromArray([

        "font" => [
            "bold" => true,
            "color" => ["rgb" => "FFFFFF"]
        ],

        "fill" => [
            "fillType" => Fill::FILL_SOLID,
            "startColor" => [
                "rgb" => "198754"
            ]
        ]
    ]);

$dashboard->getStyle("B{$filaKpi}")
    ->getNumberFormat()
    ->setFormatCode('"S/" #,##0.00');

$dashboard->getStyle("E{$filaKpi}")
    ->getNumberFormat()
    ->setFormatCode('"S/" #,##0.00');

$dashboard->getStyle("H{$filaKpi}")
    ->getNumberFormat()
    ->setFormatCode('"S/" #,##0.00');

/*=========================================================
=            TOP 10 PRODUCTOS
=========================================================*/

$filaTop = 9;

$dashboard->mergeCells("A{$filaTop}:D{$filaTop}");

$dashboard->setCellValue(
    "A{$filaTop}",
    "TOP 10 PRODUCTOS MÁS VENDIDOS"
);

$dashboard->getStyle("A{$filaTop}:D{$filaTop}")
    ->applyFromArray([

        "font" => [
            "bold" => true,
            "size" => 13,
            "color" => ["rgb" => "FFFFFF"]
        ],

        "fill" => [
            "fillType" => Fill::FILL_SOLID,
            "startColor" => [
                "rgb" => "0D6EFD"
            ]
        ]
    ]);

$filaTop++;

$dashboard->setCellValue("A{$filaTop}", "#");
$dashboard->setCellValue("B{$filaTop}", "Producto");
$dashboard->setCellValue("C{$filaTop}", "Cantidad");
$dashboard->setCellValue("D{$filaTop}", "Ventas");
$estiloEncabezado = [

    "font" => [

        "bold" => true,

        "color" => [
            "rgb" => "FFFFFF"
        ]

    ],

    "fill" => [

        "fillType" => Fill::FILL_SOLID,

        "startColor" => [
            "rgb" => "0D6EFD"
        ]

    ],

    "alignment" => [

        "horizontal" =>
        Alignment::HORIZONTAL_CENTER,

        "vertical" =>
        Alignment::VERTICAL_CENTER

    ],

    "borders" => [

        "allBorders" => [

            "borderStyle" =>
            Border::BORDER_THIN,

            "color" => [
                "rgb" => "D9D9D9"
            ]

        ]

    ]

];
$dashboard->getStyle("A{$filaTop}:D{$filaTop}")
    ->applyFromArray($estiloEncabezado);

$sqlTopProductos = "

SELECT

    p.nombre,

    SUM(dtv.cantidad_pedido_producto) cantidad,

    SUM(dtv.sub_total) ventas

FROM detalle_ticket_ventas dtv

INNER JOIN producto p
ON p.idProducto = dtv.idProducto

INNER JOIN ticket_ventas tv
ON tv.id_ticket_ventas = dtv.id_ticket_ventas

WHERE tv.id_user = {$idUser}

GROUP BY p.idProducto

ORDER BY cantidad DESC

LIMIT 10

";

$rsTop = mysqli_query($conexion, $sqlTopProductos);

$i = 1;
$filaTop++;

while ($row = mysqli_fetch_assoc($rsTop)) {

    $dashboard->setCellValue("A{$filaTop}", $i);
    $dashboard->setCellValue("B{$filaTop}", $row["nombre"]);
    $dashboard->setCellValue("C{$filaTop}", $row["cantidad"]);
    $dashboard->setCellValue("D{$filaTop}", $row["ventas"]);

    $filaTop++;
    $i++;
}

$dashboard->getStyle(
    "D11:D" . ($filaTop - 1)
)->getNumberFormat()
    ->setFormatCode('"S/" #,##0.00');

/*=========================================================
=            TOP CLIENTES
=========================================================*/

$filaCliente = 9;

$dashboard->mergeCells("F{$filaCliente}:H{$filaCliente}");

$dashboard->setCellValue(
    "F{$filaCliente}",
    "TOP CLIENTES"
);

$dashboard->getStyle("F{$filaCliente}:H{$filaCliente}")
    ->applyFromArray([

        "font" => [
            "bold" => true,
            "size" => 13,
            "color" => ["rgb" => "FFFFFF"]
        ],

        "fill" => [
            "fillType" => Fill::FILL_SOLID,
            "startColor" => [
                "rgb" => "DC3545"
            ]
        ]
    ]);

$filaCliente++;

$dashboard->setCellValue("F{$filaCliente}", "Cliente");
$dashboard->setCellValue("G{$filaCliente}", "Pedidos");
$dashboard->setCellValue("H{$filaCliente}", "Monto");

$dashboard->getStyle("F{$filaCliente}:H{$filaCliente}")
    ->applyFromArray($estiloEncabezado);

$sqlClientes = "

SELECT

    c.nombre,

    COUNT(tv.id_ticket_ventas) pedidos,

    SUM(tv.total_venta) total

FROM ticket_ventas tv

INNER JOIN clientes c
ON c.idCliente = tv.idCliente

WHERE tv.id_user = {$idUser}

GROUP BY c.idCliente

ORDER BY total DESC

LIMIT 10

";

$rsClientes = mysqli_query($conexion, $sqlClientes);

$filaCliente++;

while ($row = mysqli_fetch_assoc($rsClientes)) {

    $dashboard->setCellValue(
        "F{$filaCliente}",
        $row["nombre"]
    );

    $dashboard->setCellValue(
        "G{$filaCliente}",
        $row["pedidos"]
    );

    $dashboard->setCellValue(
        "H{$filaCliente}",
        $row["total"]
    );

    $filaCliente++;
}

$dashboard->getStyle(
    "H11:H" . ($filaCliente - 1)
)->getNumberFormat()
    ->setFormatCode('"S/" #,##0.00');

/*=========================================================
=            METODOS DE PAGO
=========================================================*/

$filaMetodo = max(
    $filaTop,
    $filaCliente
) + 3;

$dashboard->mergeCells(
    "A{$filaMetodo}:D{$filaMetodo}"
);

$dashboard->setCellValue(
    "A{$filaMetodo}",
    "MÉTODOS DE PAGO"
);

$dashboard->getStyle(
    "A{$filaMetodo}:D{$filaMetodo}"
)->applyFromArray([

    "font" => [
        "bold" => true,
        "size" => 13,
        "color" => ["rgb" => "FFFFFF"]
    ],

    "fill" => [
        "fillType" => Fill::FILL_SOLID,
        "startColor" => [
            "rgb" => "6F42C1"
        ]
    ]
]);

$filaMetodo++;

$dashboard->setCellValue("A{$filaMetodo}", "Método");
$dashboard->setCellValue("B{$filaMetodo}", "Ventas");
$dashboard->setCellValue("C{$filaMetodo}", "Total");

$dashboard->getStyle(
    "A{$filaMetodo}:C{$filaMetodo}"
)->applyFromArray($estiloEncabezado);

$sqlMetodos = "

SELECT

    mp.nombre,

    COUNT(tv.id_ticket_ventas) ventas,

    SUM(tv.total_venta) total

FROM ticket_ventas tv

INNER JOIN metodo_pago mp
ON mp.id_metodo_pago = tv.id_metodo_pago

WHERE tv.id_user = {$idUser}

GROUP BY mp.id_metodo_pago

ORDER BY total DESC

";

$rsMetodo = mysqli_query(
    $conexion,
    $sqlMetodos
);

$filaMetodo++;

while ($row = mysqli_fetch_assoc($rsMetodo)) {

    $dashboard->setCellValue(
        "A{$filaMetodo}",
        $row["nombre"]
    );

    $dashboard->setCellValue(
        "B{$filaMetodo}",
        $row["ventas"]
    );

    $dashboard->setCellValue(
        "C{$filaMetodo}",
        $row["total"]
    );

    $filaMetodo++;
}

$dashboard->getStyle(
    "C" . ($filaMetodo - 20) . ":C" . ($filaMetodo - 1)
)->getNumberFormat()
    ->setFormatCode('"S/" #,##0.00');

/*=========================================================
=            AUTO AJUSTAR COLUMNAS
=========================================================*/

foreach (range("A", "H") as $columna) {

    $dashboard
        ->getColumnDimension($columna)
        ->setAutoSize(true);
}

/*=========================================================
=            ACTIVAR PRIMERA HOJA
=========================================================*/

$spreadsheet->setActiveSheetIndex(0);

/*=========================================================
=            NOMBRE ARCHIVO
=========================================================*/

$nombreArchivo =
    "Ventas_CoDevPro_" .
    date("Ymd_His") .
    ".xlsx";

/*=========================================================
=            DESCARGA XLSX
=========================================================*/

header(
    "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
);

header(
    "Content-Disposition: attachment; filename=\"" .
        $nombreArchivo .
        "\""
);

header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);

$writer->save("php://output");

exit;
