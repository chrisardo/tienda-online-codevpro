<?php
//======================================================
// CoDevPro Technology
// ajax/exportar_categorias_excel.php
//======================================================

session_start();

require_once "../controladores/conexion.php";

require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION["idUser"])) {
    exit;
}

$idUser = intval($_SESSION["idUser"]);

$sql = "

SELECT
c.id_categorias,
c.nombre,

(
    SELECT COUNT(*)
    FROM producto p
    WHERE p.id_categorias = c.id_categorias
    AND p.Eliminado = 0
) total_productos,

(
    SELECT COALESCE(SUM(cpv.cantidad_total),0)
    FROM cantidad_producto_vendido cpv
    INNER JOIN producto p2
        ON p2.idProducto = cpv.idProducto
    WHERE p2.id_categorias = c.id_categorias
) total_vendidos

FROM categorias c

WHERE c.id_user = ?
AND c.Eliminado = 0

ORDER BY c.nombre ASC

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$excel = new Spreadsheet();

$hoja = $excel->getActiveSheet();

$hoja->setTitle("Categorias");

$hoja->setCellValue("A1", "ID");
$hoja->setCellValue("B1", "Categoría");
$hoja->setCellValue("C1", "Productos");
$hoja->setCellValue("D1", "Vendidos");
$hoja->setCellValue("E1", "Estado");

$fila = 2;

while ($row = mysqli_fetch_assoc($resultado)) {

    $estado =
        $row["total_productos"] > 0
        ? "Con productos"
        : "Sin productos";

    $hoja->setCellValue(
        "A" . $fila,
        $row["id_categorias"]
    );

    $hoja->setCellValue(
        "B" . $fila,
        $row["nombre"]
    );

    $hoja->setCellValue(
        "C" . $fila,
        $row["total_productos"]
    );

    $hoja->setCellValue(
        "D" . $fila,
        $row["total_vendidos"]
    );

    $hoja->setCellValue(
        "E" . $fila,
        $estado
    );

    $fila++;
}

foreach (range('A', 'F') as $col) {
    $hoja->getColumnDimension($col)
        ->setAutoSize(true);
}

$nombreArchivo =
    "Categorias_" .
    date("Ymd_His") .
    ".xlsx";

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' . $nombreArchivo . '"'
);

$writer = new Xlsx($excel);

$writer->save('php://output');

exit;
