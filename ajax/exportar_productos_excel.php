<?php
//======================================================
// CoDevPro Technology
// ajax/exportar_productos_excel.php
//======================================================

session_start();

if (!isset($_SESSION["idUser"])) {
    exit("Acceso denegado");
}

$idUser = (int) $_SESSION["idUser"];

require_once "../controladores/conexion.php";
require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*=============================================
RECIBIR PARÁMETROS
=============================================*/

$scope      = $_GET["scope"] ?? "todos";

$buscar     = trim($_GET["buscar"] ?? "");
$categoria  = $_GET["categoria"] ?? "";
$marca      = $_GET["marca"] ?? "";
$proveedor  = $_GET["proveedor"] ?? "";
$tipo       = $_GET["tipo"] ?? "";

$ids        = json_decode($_GET["ids"] ?? "[]", true);
$campos     = json_decode($_GET["campos"] ?? "{}", true);

/*=============================================
WHERE DINÁMICO
=============================================*/

$where = "
WHERE p.Eliminado = 0
AND p.id_user = '$idUser'
";

/*=============================================
BUSCADOR
=============================================*/

if ($buscar != "") {

    $buscar = mysqli_real_escape_string(
        $conexion,
        $buscar
    );

    $where .= "
    AND (
        p.nombre LIKE '%$buscar%'
        OR p.codigo LIKE '%$buscar%'
    )";
}

/*=============================================
CATEGORÍA
=============================================*/

if ($categoria != "") {

    $categoria = (int)$categoria;

    $where .= "
    AND p.id_categorias = '$categoria'";
}

/*=============================================
MARCA
=============================================*/

if ($marca != "") {

    $marca = (int)$marca;

    $where .= "
    AND p.id_marca = '$marca'";
}

/*=============================================
PROVEEDOR
=============================================*/

if ($proveedor != "") {

    $proveedor = (int)$proveedor;

    $where .= "
    AND p.id_provedor = '$proveedor'";
}

/*=============================================
TIPO
=============================================*/

if ($tipo != "") {

    $tipo = mysqli_real_escape_string(
        $conexion,
        $tipo
    );

    $where .= "
    AND p.tipo = '$tipo'";
}

/*=============================================
SELECCIONADOS
=============================================*/

if (
    $scope === "seleccionados"
    && !empty($ids)
) {

    $ids = array_map("intval", $ids);

    $where .= "
    AND p.idProducto IN (" .
        implode(",", $ids) .
        ")";
}

/*=============================================
CONSULTA
=============================================*/

$sql = "
SELECT

    p.idProducto,
    p.codigo,
    p.nombre,
    p.tipo,

    p.precio,
    p.precio_anterior,
    p.costo_compra,
    p.stock,

    p.descuento,
    p.oferta,
    p.destacado,
    p.nuevo,
    p.envio_gratis,

    p.fecha_registro,
    p.fecha_actualizado,

    p.descripcion,

    c.nombre AS categoria,
    m.nombre AS marca,
    pr.nombre AS proveedor,
    s.nombre AS sucursal,

    IFNULL(v.cantidad_total,0) AS vendidos

FROM producto p

LEFT JOIN categorias c
ON c.id_categorias = p.id_categorias

LEFT JOIN marcas m
ON m.id_marca = p.id_marca

LEFT JOIN provedores pr
ON pr.id_provedor = p.id_provedor

LEFT JOIN sucursal s
ON s.id_sucursal = p.id_sucursal

LEFT JOIN cantidad_producto_vendido v
ON v.idProducto = p.idProducto

$where

ORDER BY p.nombre ASC
";

$query = mysqli_query(
    $conexion,
    $sql
);

/*=============================================
CREAR EXCEL
=============================================*/

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle("Productos");

/*=============================================
ENCABEZADOS
=============================================*/

$fila = 1;
$col  = 1;

if (!empty($campos["codigo"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Código");
}

if (!empty($campos["nombre"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Nombre");
}

if (!empty($campos["tipo"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Tipo");
}

if (!empty($campos["categoria"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Categoría");
}

if (!empty($campos["marca"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Marca");
}

if (!empty($campos["proveedor"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Proveedor");
}

if (!empty($campos["sucursal"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Sucursal");
}

if (!empty($campos["precio"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Precio");
}

if (!empty($campos["precioAnterior"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Precio Anterior");
}

if (!empty($campos["costoCompra"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Costo Compra");
}

if (!empty($campos["stock"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Stock");
}

if (!empty($campos["vendidos"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Vendidos");
}

if (!empty($campos["oferta"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Oferta");
}

if (!empty($campos["destacado"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Destacado");
}

if (!empty($campos["nuevo"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Nuevo");
}

if (!empty($campos["descuento"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Descuento (%)");
}

if (!empty($campos["envioGratis"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Envío Gratis");
}

if (!empty($campos["fechaRegistro"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Fecha Registro");
}

if (!empty($campos["fechaActualizado"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Fecha Actualizado");
}

if (!empty($campos["descripcion"])) {
    $sheet->setCellValueByColumnAndRow($col++, $fila, "Descripción");
}

/*=============================================
ESTILO CABECERA
=============================================*/

$ultimaColumna = $sheet->getHighestColumn();

$sheet
    ->getStyle("A1:$ultimaColumna" . "1")
    ->getFont()
    ->setBold(true);

/*=============================================
DATOS
=============================================*/

$fila = 2;

while ($producto = mysqli_fetch_assoc($query)) {

    $col = 1;

    if (!empty($campos["codigo"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["codigo"]);
    }

    if (!empty($campos["nombre"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["nombre"]);
    }

    if (!empty($campos["tipo"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["tipo"]);
    }

    if (!empty($campos["categoria"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["categoria"]);
    }

    if (!empty($campos["marca"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["marca"]);
    }

    if (!empty($campos["proveedor"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["proveedor"]);
    }

    if (!empty($campos["sucursal"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["sucursal"]);
    }

    if (!empty($campos["precio"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["precio"]);
    }

    if (!empty($campos["precioAnterior"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["precio_anterior"]);
    }

    if (!empty($campos["costoCompra"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["costo_compra"]);
    }

    if (!empty($campos["stock"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["stock"]);
    }

    if (!empty($campos["vendidos"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["vendidos"]);
    }

    if (!empty($campos["oferta"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["oferta"] ? "Sí" : "No");
    }

    if (!empty($campos["destacado"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["destacado"] ? "Sí" : "No");
    }

    if (!empty($campos["nuevo"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["nuevo"] ? "Sí" : "No");
    }

    if (!empty($campos["descuento"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["descuento"]);
    }

    if (!empty($campos["envioGratis"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["envio_gratis"] ? "Sí" : "No");
    }

    if (!empty($campos["fechaRegistro"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["fecha_registro"]);
    }

    if (!empty($campos["fechaActualizado"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["fecha_actualizado"]);
    }

    if (!empty($campos["descripcion"])) {
        $sheet->setCellValueByColumnAndRow($col++, $fila, $producto["descripcion"]);
    }

    $fila++;
}

/*=============================================
AUTO AJUSTAR COLUMNAS
=============================================*/

foreach (
    range('A', $sheet->getHighestColumn())
    as $column
) {
    $sheet
        ->getColumnDimension($column)
        ->setAutoSize(true);
}

/*=============================================
DESCARGA
=============================================*/

$nombreArchivo =
    "Productos_" .
    date("Ymd_His") .
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

$writer->save("php://output");

exit;
