<?php
//======================================================
// CoDevPro Technology
// ajax/exportar_productos_pdf.php
//======================================================

session_start();

if (!isset($_SESSION["idUser"])) {
    exit("Acceso denegado");
}

require_once "../controladores/conexion.php";
require_once "../fpdf/fpdf.php";

$idUser = (int)$_SESSION["idUser"];

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

if ($buscar != "") {

    $buscar = mysqli_real_escape_string($conexion, $buscar);

    $where .= "
    AND (
        p.nombre LIKE '%$buscar%'
        OR p.codigo LIKE '%$buscar%'
    )";
}

if ($categoria != "") {

    $categoria = (int)$categoria;

    $where .= "
    AND p.id_categorias = '$categoria'";
}

if ($marca != "") {

    $marca = (int)$marca;

    $where .= "
    AND p.id_marca = '$marca'";
}

if ($proveedor != "") {

    $proveedor = (int)$proveedor;

    $where .= "
    AND p.id_provedor = '$proveedor'";
}

if ($tipo != "") {

    $tipo = mysqli_real_escape_string($conexion, $tipo);

    $where .= "
    AND p.tipo = '$tipo'";
}

if (
    $scope === "seleccionados"
    && !empty($ids)
) {

    $ids = array_map("intval", $ids);

    $where .= "
    AND p.idProducto IN (" . implode(",", $ids) . ")";
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

$query = mysqli_query($conexion, $sql);

if (!$query) {
    die(mysqli_error($conexion));
}
/*=============================================
DATOS EMPRESA
=============================================*/

$sqlEmpresa = mysqli_query(
    $conexion,
    "SELECT
        nombreEmpresa,
        ruc,
        direccion,
        celular,
        imagen
    FROM usuario_acceso
    WHERE id_user = '$idUser'
    LIMIT 1"
);

$empresa = mysqli_fetch_assoc($sqlEmpresa);

$nombreEmpresa = $empresa["nombreEmpresa"] ?? "CoDevPro Technology";
$ruc           = $empresa["ruc"] ?? "";
$direccion     = $empresa["direccion"] ?? "";
$celular       = $empresa["celular"] ?? "";
$logoTemporal = "";

if (!empty($empresa["imagen"])) {

    $logoTemporal =
        sys_get_temp_dir() .
        "/logo_empresa_" .
        $idUser .
        ".png";

    file_put_contents(
        $logoTemporal,
        $empresa["imagen"]
    );
}
/*=============================================
PDF
=============================================*/

class PDF extends FPDF
{
    function Header()
    {
        global $logoTemporal;
        global $nombreEmpresa;
        global $ruc;
        global $direccion;
        global $celular;

        /*=========================================
    LOGO
    =========================================*/

        if (
            !empty($logoTemporal) &&
            file_exists($logoTemporal)
        ) {

            $this->Image(
                $logoTemporal,
                10,
                8,
                25
            );
        }

        /*=========================================
    DATOS EMPRESA
    =========================================*/

        $this->SetXY(40, 8);

        $this->SetFont(
            'Arial',
            'B',
            16
        );

        $this->Cell(
            0,
            8,
            utf8_decode($nombreEmpresa),
            0,
            1
        );

        $this->SetX(40);

        $this->SetFont(
            'Arial',
            '',
            9
        );

        $this->Cell(
            0,
            5,
            utf8_decode("RUC: " . $ruc),
            0,
            1
        );

        $this->SetX(40);

        $this->Cell(
            0,
            5,
            utf8_decode($direccion),
            0,
            1
        );

        $this->SetX(40);

        $this->Cell(
            0,
            5,
            utf8_decode("Celular: " . $celular),
            0,
            1
        );

        /*=========================================
    TITULO REPORTE
    =========================================*/

        $this->Ln(5);

        $this->SetFont(
            'Arial',
            'B',
            13
        );

        $this->Cell(
            0,
            8,
            utf8_decode("REPORTE DE PRODUCTOS"),
            0,
            1,
            'C'
        );

        $this->SetFont(
            'Arial',
            '',
            9
        );

        $this->Cell(
            0,
            5,
            utf8_decode(
                "Generado: " .
                    date("d/m/Y H:i:s")
            ),
            0,
            1,
            'R'
        );

        /*=========================================
    LINEA
    =========================================*/

        $this->Line(
            10,
            45,
            287,
            45
        );

        $this->Ln(8);
    }

    function Footer()
    {
        $this->SetY(-15);

        $this->SetFont('Arial', 'I', 8);

        $this->Cell(
            0,
            10,
            utf8_decode('Página ') . $this->PageNo(),
            0,
            0,
            'C'
        );
    }
}

$pdf = new PDF('L', 'mm', 'A4');

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 8);

/*=============================================
COLUMNAS
=============================================*/

$columnas = [];

if (!empty($campos["codigo"])) {
    $columnas[] = ["titulo" => "Codigo", "ancho" => 25];
}

if (!empty($campos["nombre"])) {
    $columnas[] = ["titulo" => "Nombre", "ancho" => 50];
}

if (!empty($campos["tipo"])) {
    $columnas[] = ["titulo" => "Tipo", "ancho" => 20];
}

if (!empty($campos["categoria"])) {
    $columnas[] = ["titulo" => "Categoria", "ancho" => 30];
}

if (!empty($campos["marca"])) {
    $columnas[] = ["titulo" => "Marca", "ancho" => 25];
}

if (!empty($campos["proveedor"])) {
    $columnas[] = ["titulo" => "Proveedor", "ancho" => 30];
}

if (!empty($campos["sucursal"])) {
    $columnas[] = ["titulo" => "Sucursal", "ancho" => 25];
}

if (!empty($campos["precio"])) {
    $columnas[] = ["titulo" => "Precio", "ancho" => 20];
}

if (!empty($campos["precioAnterior"])) {
    $columnas[] = ["titulo" => "Precio Ant.", "ancho" => 22];
}

if (!empty($campos["costoCompra"])) {
    $columnas[] = ["titulo" => "Costo", "ancho" => 20];
}

if (!empty($campos["stock"])) {
    $columnas[] = ["titulo" => "Stock", "ancho" => 15];
}

if (!empty($campos["vendidos"])) {
    $columnas[] = ["titulo" => "Vendidos", "ancho" => 18];
}

if (!empty($campos["oferta"])) {
    $columnas[] = ["titulo" => "Oferta", "ancho" => 15];
}

if (!empty($campos["destacado"])) {
    $columnas[] = ["titulo" => "Destacado", "ancho" => 18];
}

if (!empty($campos["nuevo"])) {
    $columnas[] = ["titulo" => "Nuevo", "ancho" => 15];
}

if (!empty($campos["descuento"])) {
    $columnas[] = ["titulo" => "Desc.", "ancho" => 15];
}

if (!empty($campos["envioGratis"])) {
    $columnas[] = ["titulo" => "Envio", "ancho" => 15];
}

if (!empty($campos["fechaRegistro"])) {
    $columnas[] = ["titulo" => "F. Registro", "ancho" => 25];
}

if (!empty($campos["fechaActualizado"])) {
    $columnas[] = ["titulo" => "F. Actual.", "ancho" => 25];
}

if (!empty($campos["descripcion"])) {
    $columnas[] = ["titulo" => "Descripcion", "ancho" => 60];
}

/*=============================================
CABECERA TABLA
=============================================*/

foreach ($columnas as $columna) {

    $pdf->Cell(
        $columna["ancho"],
        8,
        utf8_decode($columna["titulo"]),
        1,
        0,
        'C'
    );
}

$pdf->Ln();

$pdf->SetFont('Arial', '', 7);

/*=============================================
DATOS
=============================================*/

while ($producto = mysqli_fetch_assoc($query)) {

    foreach ($columnas as $columna) {

        $valor = "";

        switch ($columna["titulo"]) {

            case "Codigo":
                $valor = $producto["codigo"];
                break;

            case "Nombre":
                $valor = $producto["nombre"];
                break;

            case "Tipo":
                $valor = $producto["tipo"];
                break;

            case "Categoria":
                $valor = $producto["categoria"];
                break;

            case "Marca":
                $valor = $producto["marca"];
                break;

            case "Proveedor":
                $valor = $producto["proveedor"];
                break;

            case "Sucursal":
                $valor = $producto["sucursal"];
                break;

            case "Precio":
                $valor = "S/ " . number_format($producto["precio"], 2);
                break;

            case "Precio Ant.":
                $valor = "S/ " . number_format($producto["precio_anterior"], 2);
                break;

            case "Costo":
                $valor = "S/ " . number_format($producto["costo_compra"], 2);
                break;

            case "Stock":
                $valor = $producto["stock"];
                break;

            case "Vendidos":
                $valor = $producto["vendidos"];
                break;

            case "Oferta":
                $valor = $producto["oferta"] ? "Si" : "No";
                break;

            case "Destacado":
                $valor = $producto["destacado"] ? "Si" : "No";
                break;

            case "Nuevo":
                $valor = $producto["nuevo"] ? "Si" : "No";
                break;

            case "Desc.":
                $valor = $producto["descuento"] . "%";
                break;

            case "Envio":
                $valor = $producto["envio_gratis"] ? "Si" : "No";
                break;

            case "F. Registro":
                $valor = $producto["fecha_registro"];
                break;

            case "F. Actual.":
                $valor = $producto["fecha_actualizado"];
                break;

            case "Descripcion":
                $valor = substr(
                    strip_tags($producto["descripcion"]),
                    0,
                    80
                );
                break;
        }

        $pdf->Cell(
            $columna["ancho"],
            7,
            utf8_decode($valor),
            1
        );
    }

    $pdf->Ln();

    if ($pdf->GetY() > 180) {

        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 8);

        foreach ($columnas as $columna) {

            $pdf->Cell(
                $columna["ancho"],
                8,
                utf8_decode($columna["titulo"]),
                1,
                0,
                'C'
            );
        }

        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
    }
}

/*=============================================
SALIDA
=============================================*/

$pdf->Output(
    'D',
    'Productos_' . date('Ymd_His') . '.pdf'
);

exit;
