<?php
//=========================================================
// CoDevPro Technology
// ajax/exportar_clientes_excel.php
//=========================================================

session_start();

require_once "../controladores/conexion.php";
require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {
    exit("Sesión inválida");
}

/*=============================================
FILTROS
=============================================*/

$buscar       = trim($_GET["buscar"] ?? "");
$estado       = trim($_GET["estado"] ?? "");
$departamento = trim($_GET["departamento"] ?? "");
$fechaInicio  = trim($_GET["fechaInicio"] ?? "");
$fechaFin     = trim($_GET["fechaFin"] ?? "");

/*=============================================
WHERE DINAMICO
=============================================*/

$where = "
    WHERE c.id_user = ?
    AND c.Eliminado = 0
";

$tipos = "i";
$parametros = [$idUser];

if (!empty($buscar)) {

    $where .= "
        AND (
            c.nombre LIKE ?
            OR c.email LIKE ?
            OR c.dni_o_ruc LIKE ?
        )
    ";

    $buscarLike = "%{$buscar}%";

    $tipos .= "sss";

    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
}

if (!empty($estado)) {

    $where .= " AND c.estado = ? ";

    $tipos .= "s";

    $parametros[] = $estado;
}

if (!empty($departamento)) {

    $where .= " AND c.id_departamento = ? ";

    $tipos .= "i";

    $parametros[] = (int)$departamento;
}

if (!empty($fechaInicio)) {

    $where .= " AND c.fecha_registro >= ? ";

    $tipos .= "s";

    $parametros[] = $fechaInicio;
}

if (!empty($fechaFin)) {

    $where .= " AND c.fecha_registro <= ? ";

    $tipos .= "s";

    $parametros[] = $fechaFin;
}

/*=============================================
CONSULTA
=============================================*/

$sql = "
SELECT

    c.idCliente,
    c.nombre,
    c.dni_o_ruc,
    c.email,
    c.celular,
    c.estado,
    c.fecha_registro,

    d.nombre AS departamento,

    COUNT(tv.id_ticket_ventas) AS pedidos,

    COALESCE(
        SUM(tv.total_venta),
        0
    ) AS totalComprado,

    MAX(tv.fecha_venta) AS ultimaCompra

FROM clientes c

LEFT JOIN departamento d
    ON d.id_departamento = c.id_departamento

LEFT JOIN ticket_ventas tv
    ON tv.idCliente = c.idCliente
    AND tv.id_user = c.id_user

$where

GROUP BY c.idCliente

ORDER BY c.nombre ASC
";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    $tipos,
    ...$parametros
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*=============================================
CREAR EXCEL
=============================================*/

$excel = new Spreadsheet();

$sheet = $excel->getActiveSheet();

$sheet->setTitle("Clientes");

/*=============================================
TITULO
=============================================*/

$sheet->mergeCells('A1:J1');

$sheet->setCellValue(
    'A1',
    'REPORTE DE ESTADÍSTICAS DE CLIENTES'
);

$sheet->getStyle('A1')->getFont()
    ->setBold(true)
    ->setSize(16);

$sheet->getStyle('A1')->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );

/*=============================================
FECHA
=============================================*/

$sheet->setCellValue(
    'A2',
    'Fecha de generación: ' . date('d/m/Y H:i:s')
);

/*=============================================
CABECERAS
=============================================*/

$filaCabecera = 4;

$sheet->fromArray([
    [
        '#',
        'Cliente',
        'DNI/RUC',
        'Email',
        'Celular',
        'Departamento',
        'Pedidos',
        'Total Comprado',
        'Última Compra',
        'Estado'
    ]
], null, 'A' . $filaCabecera);

/*=============================================
ESTILO CABECERA
=============================================*/

$sheet->getStyle('A4:J4')->applyFromArray([

    'font' => [
        'bold' => true,
        'color' => [
            'rgb' => 'FFFFFF'
        ]
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => '0D6EFD'
        ]
    ],

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]

]);

/*=============================================
DATOS
=============================================*/

$fila = 5;
$item = 1;

while ($row = mysqli_fetch_assoc($resultado)) {

    $sheet->setCellValue('A' . $fila, $item++);
    $sheet->setCellValue('B' . $fila, $row["nombre"]);
    $sheet->setCellValue('C' . $fila, $row["dni_o_ruc"]);
    $sheet->setCellValue('D' . $fila, $row["email"]);
    $sheet->setCellValue('E' . $fila, $row["celular"]);
    $sheet->setCellValue('F' . $fila, $row["departamento"]);
    $sheet->setCellValue('G' . $fila, $row["pedidos"]);
    $sheet->setCellValue('H' . $fila, $row["totalComprado"]);
    $sheet->setCellValue(
        'I' . $fila,
        !empty($row["ultimaCompra"])
            ? date("d/m/Y", strtotime($row["ultimaCompra"]))
            : "-"
    );
    $sheet->setCellValue('J' . $fila, $row["estado"]);

    $fila++;
}

/*=============================================
FORMATO MONEDA
=============================================*/

if ($fila > 5) {

    $sheet->getStyle(
        'H5:H' . ($fila - 1)
    )
        ->getNumberFormat()
        ->setFormatCode('"S/ " #,##0.00');
}

/*=============================================
BORDES
=============================================*/

$sheet->getStyle(
    'A4:J' . ($fila - 1)
)->applyFromArray([

    'borders' => [

        'allBorders' => [

            'borderStyle' => Border::BORDER_THIN

        ]

    ]

]);

/*=============================================
AUTOFILTRO
=============================================*/

$sheet->setAutoFilter(
    'A4:J' . ($fila - 1)
);

/*=============================================
AUTO SIZE
=============================================*/

foreach (range('A', 'J') as $columna) {

    $sheet
        ->getColumnDimension($columna)
        ->setAutoSize(true);
}

/*=============================================
CONGELAR CABECERA
=============================================*/

$sheet->freezePane('A5');

/*=============================================
DESCARGA
=============================================*/

$nombreArchivo =
    'Clientes_' .
    date('Ymd_His') .
    '.xlsx';

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' . $nombreArchivo . '"'
);

header('Cache-Control: max-age=0');

$writer = new Xlsx($excel);

$writer->save('php://output');

exit;
