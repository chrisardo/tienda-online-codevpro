<?php
//=====================================================
// CoDevPro Technology
// ajax/exportar_clientes_excel.php
//=====================================================

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

/*=====================================================
VALIDAR DATOS
=====================================================*/

$columnas = [];

if (isset($_POST["columnas"])) {

    $columnas = json_decode($_POST["columnas"], true);

    if (!is_array($columnas)) {
        $columnas = [];
    }
}

if (empty($columnas)) {
    exit("No se recibieron columnas para exportar.");
}

$idUser = (int) $_SESSION["idUser"];

$buscar  = trim($_POST["buscar"] ?? "");
$estado  = trim($_POST["estado"] ?? "");
$pais    = trim($_POST["pais"] ?? "");
$rubro   = trim($_POST["rubro"] ?? "");
$ordenar = trim($_POST["ordenar"] ?? "nombre_asc");

/*=====================================================
COLUMNAS DISPONIBLES
=====================================================*/

$columnasSQL = [

    "nombre"           => "c.nombre",
    "dni_o_ruc"        => "c.dni_o_ruc",
    "celular"          => "c.celular",
    "email"            => "c.email",
    "direccion"        => "c.direccion",
    "estado"           => "c.estado",
    "pais"             => "p.nombre AS pais",
    "departamento"     => "dep.nombre AS departamento",
    "provincia"        => "pro.nombre AS provincia",
    "distrito"         => "dis.nombre AS distrito",
    "rubro"            => "r.nombre AS rubro",
    "fecha_registro"   => "c.fecha_registro"
];

/*=====================================================
CABECERAS EXCEL
=====================================================*/

$titulos = [

    "nombre" => "Nombre",
    "dni_o_ruc" => "DNI / RUC",
    "celular" => "Celular",
    "email" => "Email",
    "direccion" => "Dirección",
    "estado" => "Estado",
    "pais" => "País",
    "departamento" => "Departamento",
    "provincia" => "Provincia",
    "distrito" => "Distrito",
    "rubro" => "Rubro",
    "fecha_registro" => "Fecha Registro"

];

/*=====================================================
ARMAR SELECT
=====================================================*/

$select = [];

foreach ($columnas as $columna) {

    if (isset($columnasSQL[$columna])) {

        $select[] = $columnasSQL[$columna];
    }
}

if (empty($select)) {
    exit("No existen columnas válidas.");
}

$sql = "

SELECT

" . implode(",", $select) . "

FROM clientes c

LEFT JOIN pais p
ON p.id_pais = c.id_pais

LEFT JOIN departamento dep
ON dep.id_departamento = c.id_departamento

LEFT JOIN provincia pro
ON pro.id_provincia = c.id_provincia

LEFT JOIN distrito dis
ON dis.id_distrito = c.id_distrito

LEFT JOIN rubros r
ON r.id_rubro = c.id_rubro

WHERE c.Eliminado = 0
AND c.id_user = ?

";

$parametros = [$idUser];
$tipos = "i";

/*=====================================================
BUSCAR
=====================================================*/

if (!empty($buscar)) {

    $sql .= "

    AND (

        c.nombre LIKE ?
        OR c.dni_o_ruc LIKE ?
        OR c.email LIKE ?
        OR c.celular LIKE ?

    )

    ";

    $buscarLike = "%{$buscar}%";

    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;
    $parametros[] = $buscarLike;

    $tipos .= "ssss";
}

/*=====================================================
ESTADO
=====================================================*/

if (!empty($estado)) {

    $sql .= " AND c.estado = ? ";

    $parametros[] = $estado;

    $tipos .= "s";
}

/*=====================================================
PAIS
=====================================================*/

if (!empty($pais)) {

    $sql .= " AND c.id_pais = ? ";

    $parametros[] = $pais;

    $tipos .= "i";
}

/*=====================================================
RUBRO
=====================================================*/

if (!empty($rubro)) {

    $sql .= " AND c.id_rubro = ? ";

    $parametros[] = $rubro;

    $tipos .= "i";
}

/*=====================================================
ORDEN
=====================================================*/

switch ($ordenar) {

    case "nombre_desc":
        $sql .= " ORDER BY c.nombre DESC";
        break;

    case "fecha_desc":
        $sql .= " ORDER BY c.fecha_registro DESC";
        break;

    default:
        $sql .= " ORDER BY c.nombre ASC";
        break;
}

/*=====================================================
CONSULTA
=====================================================*/

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    exit(mysqli_error($conexion));
}

mysqli_stmt_bind_param(
    $stmt,
    $tipos,
    ...$parametros
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*=====================================================
EXCEL
=====================================================*/

$excel = new Spreadsheet();

$hoja = $excel->getActiveSheet();

$hoja->setTitle("Clientes");

/*=====================================================
TITULO
=====================================================*/

$ultimaColumna = chr(64 + count($columnas));

$hoja->mergeCells("A1:" . $ultimaColumna . "1");

$hoja->setCellValue(
    "A1",
    "REPORTE DE CLIENTES"
);

$hoja->getStyle("A1")->getFont()->setBold(true);
$hoja->getStyle("A1")->getFont()->setSize(14);

/*=====================================================
CABECERAS
=====================================================*/

$fila = 3;
$columnaExcel = 1;

foreach ($columnas as $columna) {

    $hoja->setCellValueByColumnAndRow(
        $columnaExcel,
        $fila,
        $titulos[$columna]
    );

    $columnaExcel++;
}

$hoja->getStyle("A3:" . $ultimaColumna . "3")
    ->getFont()
    ->setBold(true);

$hoja->getStyle("A3:" . $ultimaColumna . "3")
    ->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB("D9EAD3");

/*=====================================================
DATOS
=====================================================*/

$fila = 4;

while ($cliente = mysqli_fetch_assoc($resultado)) {

    $columnaExcel = 1;

    foreach ($columnas as $campo) {

        $valor = $cliente[$campo] ?? "";

        $hoja->setCellValueByColumnAndRow(
            $columnaExcel,
            $fila,
            $valor
        );

        $columnaExcel++;
    }

    $fila++;
}

/*=====================================================
AUTO AJUSTAR
=====================================================*/

foreach (range('A', $ultimaColumna) as $col) {

    $hoja->getColumnDimension($col)
        ->setAutoSize(true);
}

/*=====================================================
BORDES
=====================================================*/

$hoja->getStyle(
    "A3:" .
        $ultimaColumna .
        ($fila - 1)
)->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);

/*=====================================================
DESCARGA
=====================================================*/

$nombreArchivo =
    "Clientes_" .
    date("Ymd_His") .
    ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

header(
    'Content-Disposition: attachment; filename="' .
        $nombreArchivo .
        '"'
);

header('Cache-Control: max-age=0');

$writer = new Xlsx($excel);

$writer->save('php://output');

exit;
