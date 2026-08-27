<?php
//======================================================
// CoDevPro Technology
// ajax/exportar_marcas_excel.php
//======================================================

session_start();

require_once "../controladores/conexion.php";

require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION["idUser"])) {
    exit("Sesión no válida");
}

$idUser = intval($_SESSION["idUser"]);

/*======================================================
= CONSULTAR MARCAS
======================================================*/

$sql = "

SELECT

m.id_marca,
m.nombre,

(
    SELECT COUNT(*)
    FROM producto p
    WHERE p.id_marca = m.id_marca
    AND p.Eliminado = 0
) productos,

(
    SELECT COALESCE(
        SUM(cpv.cantidad_total),
        0
    )
    FROM cantidad_producto_vendido cpv

    INNER JOIN producto p
        ON p.idProducto = cpv.idProducto

    WHERE p.id_marca = m.id_marca
    AND p.Eliminado = 0
) vendidos

FROM marcas m

WHERE m.id_user = ?
AND m.Eliminado = 0

ORDER BY m.nombre ASC

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*======================================================
= CREAR EXCEL
======================================================*/

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Marcas');

/*======================================================
= TITULO
======================================================*/

$sheet->mergeCells('A1:E1');

$sheet->setCellValue(
    'A1',
    'REPORTE DE MARCAS'
);

/*======================================================
= CABECERAS
======================================================*/

$sheet->setCellValue('A3', 'ID');
$sheet->setCellValue('B3', 'MARCA');
$sheet->setCellValue('C3', 'PRODUCTOS');
$sheet->setCellValue('D3', 'VENDIDOS');
$sheet->setCellValue('E3', 'ESTADO');

/*======================================================
= ESTILOS CABECERA
======================================================*/

$sheet->getStyle('A3:E3')
    ->getFont()
    ->setBold(true);

$fila = 4;

/*======================================================
= DATOS
======================================================*/

while ($row = mysqli_fetch_assoc($resultado)) {

    $estado =
        ($row["productos"] > 0)
        ? "EN USO"
        : "SIN PRODUCTOS";

    $sheet->setCellValue(
        'A' . $fila,
        $row["id_marca"]
    );

    $sheet->setCellValue(
        'B' . $fila,
        $row["nombre"]
    );

    $sheet->setCellValue(
        'C' . $fila,
        $row["productos"]
    );

    $sheet->setCellValue(
        'D' . $fila,
        $row["vendidos"]
    );

    $sheet->setCellValue(
        'E' . $fila,
        $estado
    );

    $fila++;
}

/*======================================================
= AUTO AJUSTAR COLUMNAS
======================================================*/

foreach (range('A', 'E') as $columna) {

    $sheet->getColumnDimension($columna)
        ->setAutoSize(true);
}

/*======================================================
= FORMATO TITULO
======================================================*/

$sheet->getStyle('A1')->getFont()
    ->setBold(true)
    ->setSize(16);

/*======================================================
= DESCARGA
======================================================*/

$nombreArchivo =
    "Marcas_" .
    date("Y-m-d_H-i-s") .
    ".xlsx";

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment;filename="' .
        $nombreArchivo .
        '"'
);

header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;
