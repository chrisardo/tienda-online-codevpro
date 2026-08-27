<?php
//=========================================================
// CoDevPro Technology
// ajax/exportar_clientes_pdf.php
//=========================================================

session_start();

require_once "../controladores/conexion.php";
require_once "../fpdf/fpdf.php";

$idUser = $_SESSION["idUser"] ?? 0;

if (!$idUser) {
    exit;
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
WHERE
=============================================*/

$where = "
WHERE c.id_user = ?
AND c.Eliminado = 0
";

$tipos = "i";
$params = [$idUser];

if ($buscar != "") {

    $where .= "
    AND (
        c.nombre LIKE ?
        OR c.email LIKE ?
        OR c.dni_o_ruc LIKE ?
    )";

    $like = "%{$buscar}%";

    $tipos .= "sss";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($estado != "") {

    $where .= " AND c.estado=? ";

    $tipos .= "s";

    $params[] = $estado;
}

if ($departamento != "") {

    $where .= " AND c.id_departamento=? ";

    $tipos .= "i";

    $params[] = (int)$departamento;
}

if ($fechaInicio != "") {

    $where .= " AND c.fecha_registro>=? ";

    $tipos .= "s";

    $params[] = $fechaInicio;
}

if ($fechaFin != "") {

    $where .= " AND c.fecha_registro<=? ";

    $tipos .= "s";

    $params[] = $fechaFin;
}

/*=============================================
CONSULTA
=============================================*/

$sql = "
SELECT

c.nombre,
c.dni_o_ruc,
c.email,
c.celular,

d.nombre departamento,

COUNT(tv.id_ticket_ventas) pedidos,

COALESCE(
SUM(tv.total_venta),
0
) totalComprado,

MAX(tv.fecha_venta) ultimaCompra,

c.estado

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
    ...$params
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*=============================================
PDF
=============================================*/

class PDF extends FPDF
{
    function Header()
    {
        /*
        $this->Image(
            '../img/logo.png',
            10,
            8,
            25
        );
        */

        $this->SetFont('Arial', 'B', 15);

        $this->Cell(
            0,
            10,
            utf8_decode('CoDevPro Technology'),
            0,
            1,
            'C'
        );

        $this->SetFont('Arial', 'B', 12);

        $this->Cell(
            0,
            8,
            utf8_decode('Reporte de Clientes'),
            0,
            1,
            'C'
        );

        $this->Ln(3);

        $this->SetFont('Arial', '', 8);

        $this->Cell(
            0,
            5,
            'Fecha: ' . date('d/m/Y H:i'),
            0,
            1,
            'R'
        );

        $this->Ln(2);
    }

    function Footer()
    {
        $this->SetY(-15);

        $this->SetFont(
            'Arial',
            'I',
            8
        );

        $this->Cell(
            0,
            10,
            utf8_decode('Página ')
                . $this->PageNo(),
            0,
            0,
            'C'
        );
    }
}

$pdf = new PDF(
    'L',
    'mm',
    'A4'
);

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetFont(
    'Arial',
    'B',
    8
);

/*=============================================
CABECERA TABLA
=============================================*/

$pdf->SetFillColor(
    13,
    110,
    253
);

$pdf->SetTextColor(
    255,
    255,
    255
);

$pdf->Cell(10, 8, '#', 1, 0, 'C', true);
$pdf->Cell(45, 8, 'Cliente', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'DNI/RUC', 1, 0, 'C', true);
$pdf->Cell(55, 8, 'Email', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Celular', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Departamento', 1, 0, 'C', true);
$pdf->Cell(15, 8, 'Ped.', 1, 0, 'C', true);
$pdf->Cell(28, 8, 'Total', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Ult. Compra', 1, 0, 'C', true);
$pdf->Cell(22, 8, 'Estado', 1, 1, 'C', true);

/*=============================================
DATOS
=============================================*/

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 8);

$item = 1;

$totalGeneral = 0;

while ($row = mysqli_fetch_assoc($resultado)) {

    $totalGeneral += $row["totalComprado"];

    $ultimaCompra = '-';

    if (!empty($row["ultimaCompra"])) {

        $ultimaCompra =
            date(
                'd/m/Y',
                strtotime(
                    $row["ultimaCompra"]
                )
            );
    }

    $pdf->Cell(
        10,
        7,
        $item++,
        1,
        0,
        'C'
    );

    $pdf->Cell(
        45,
        7,
        utf8_decode(
            substr(
                $row["nombre"],
                0,
                28
            )
        ),
        1
    );

    $pdf->Cell(
        25,
        7,
        $row["dni_o_ruc"],
        1
    );

    $pdf->Cell(
        55,
        7,
        utf8_decode(
            substr(
                $row["email"],
                0,
                35
            )
        ),
        1
    );

    $pdf->Cell(
        25,
        7,
        $row["celular"],
        1
    );

    $pdf->Cell(
        35,
        7,
        utf8_decode(
            $row["departamento"]
        ),
        1
    );

    $pdf->Cell(
        15,
        7,
        $row["pedidos"],
        1,
        0,
        'C'
    );

    $pdf->Cell(
        28,
        7,
        'S/ ' . number_format(
            $row["totalComprado"],
            2
        ),
        1,
        0,
        'R'
    );

    $pdf->Cell(
        25,
        7,
        $ultimaCompra,
        1,
        0,
        'C'
    );

    $pdf->Cell(
        22,
        7,
        utf8_decode(
            $row["estado"]
        ),
        1,
        1,
        'C'
    );
}

/*=============================================
RESUMEN
=============================================*/

$pdf->Ln(5);

$pdf->SetFont(
    'Arial',
    'B',
    10
);

$pdf->Cell(
    0,
    8,
    'Total comprado por todos los clientes: S/ '
        . number_format(
            $totalGeneral,
            2
        ),
    0,
    1,
    'R'
);

/*=============================================
SALIDA
=============================================*/

$pdf->Output(
    'I',
    'Clientes_' . date('Ymd_His') . '.pdf'
);

exit;
