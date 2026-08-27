<?php

session_start();

require_once "../controladores/conexion.php";

require_once "../fpdf/fpdf.php";

$idUser  = (int)($_SESSION["idUser"] ?? 0);
$idVenta = (int)($_GET["idVenta"] ?? 0);

if ($idUser <= 0 || $idVenta <= 0) {

    exit("Acceso denegado");
}
class PDF extends FPDF
{
    protected $angle = 0;

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;

        if ($this->angle != 0) {
            $this->_out('Q');
        }

        $this->angle = $angle;

        if ($angle != 0) {

            $angle *= M_PI / 180;

            $c = cos($angle);
            $s = sin($angle);

            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;

            $this->_out(
                sprintf(
                    'q %.5F %.5F %.5F %.5F %.2F %.2F cm',
                    $c,
                    $s,
                    -$s,
                    $c,
                    $cx,
                    $cy
                )
            );

            $this->_out(
                sprintf(
                    '1 0 0 1 %.2F %.2F cm',
                    -$cx,
                    -$cy
                )
            );
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {

            $this->angle = 0;

            $this->_out('Q');
        }

        parent::_endpage();
    }

    function Footer()
    {
        $this->SetY(-15);

        $this->SetFont('Arial', 'I', 8);

        $this->Cell(
            0,
            5,
            utf8_decode(
                'Página ' .
                    $this->PageNo() .
                    '/{nb}'
            ),
            0,
            0,
            'C'
        );
    }
}
/*=========================================================
=            CABECERA VENTA
=========================================================*/

$sql = "

SELECT

    tv.*,

    c.nombre AS cliente,
    c.dni_o_ruc,
    c.celular,
    c.email,

    mp.nombre AS metodo_pago,

    ua.nombreEmpresa,
    ua.ruc,
    ua.direccion,
    ua.celular AS celular_empresa,
    ua.imagen AS logo_empresa

FROM ticket_ventas tv

LEFT JOIN clientes c
ON c.idCliente = tv.idCliente

LEFT JOIN metodo_pago mp
ON mp.id_metodo_pago = tv.id_metodo_pago

LEFT JOIN usuario_acceso ua
ON ua.id_user = tv.id_user

WHERE tv.id_ticket_ventas = ?
AND tv.id_user = ?

LIMIT 1

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idVenta,
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$venta = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);

if (!$venta) {

    exit("Venta no encontrada");
}
$logoTmp = "";

if (!empty($venta["logo_empresa"])) {

    $logoTmp =
        sys_get_temp_dir() .
        "/logo_" .
        $idUser .
        ".png";

    file_put_contents(
        $logoTmp,
        $venta["logo_empresa"]
    );
}
/*=========================================================
=            PRODUCTOS
=========================================================*/

$sqlProductos = "

SELECT

    p.nombre,
    p.codigo,
    p.precio,

    dt.cantidad_pedido_producto,
    dt.sub_total

FROM detalle_ticket_ventas dt

INNER JOIN producto p
ON p.idProducto = dt.idProducto

WHERE dt.id_ticket_ventas = ?

ORDER BY p.nombre ASC

";

$stmt = mysqli_prepare(
    $conexion,
    $sqlProductos
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idVenta
);

mysqli_stmt_execute($stmt);

$resultadoProductos =
    mysqli_stmt_get_result($stmt);

/*=========================================================
=            PDF
=========================================================*/

$pdf = new PDF();

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetAutoPageBreak(true, 20);

/*=========================================================
LOGO
=========================================================*/

if (
    !empty($logoTmp)
    && file_exists($logoTmp)
) {

    $pdf->Image(
        $logoTmp,
        10,
        10,
        30
    );
}

/*=========================================
MARCA DE AGUA SOLO SI ESTA ENTREGADO
=========================================*/

if (
    strtoupper($venta["estado_envio"]) === "ENTREGADO"
    &&
    strtoupper($venta["estado_venta"]) === "PAGADO"
) {

    $pdf->SetFont('Arial', 'B', 55);

    $pdf->SetTextColor(220, 220, 220);

    $pdf->Rotate(45, 55, 190);

    $pdf->Text(
        35,
        190,
        utf8_decode('PAGADO')
    );

    $pdf->Rotate(0);

    $pdf->SetTextColor(0, 0, 0);
}

/*=========================================================
EMPRESA
=========================================================*/

$pdf->SetXY(45, 10);

$pdf->SetFont('Arial', 'B', 15);

$pdf->Cell(
    90,
    7,
    utf8_decode($venta["nombreEmpresa"]),
    0,
    1
);

$pdf->SetFont('Arial', '', 9);

$pdf->SetX(45);

$pdf->Cell(
    90,
    5,
    utf8_decode($venta["direccion"]),
    0,
    1
);

$pdf->SetX(45);

$pdf->Cell(
    90,
    5,
    utf8_decode(
        "Celular: " .
            $venta["celular_empresa"]
    ),
    0,
    1
);

/*=========================================================
CAJA SUNAT
=========================================================*/

$pdf->SetXY(140, 10);

$pdf->SetFont(
    'Arial',
    'B',
    11
);

$pdf->Cell(
    60,
    10,
    utf8_decode(
        $venta["tipo_comprobante"]
    ),
    1,
    2,
    'C'
);

$pdf->Cell(
    60,
    10,
    "RUC " .
        $venta["ruc"],
    1,
    2,
    'C'
);

$pdf->Cell(
    60,
    10,
    $venta["serie"] .
        "-" .
        str_pad(
            $venta["numero"],
            8,
            "0",
            STR_PAD_LEFT
        ),
    1,
    2,
    'C'
);

$pdf->Ln(20);

/*=========================================================
CLIENTE
=========================================================*/

$pdf->SetFont(
    'Arial',
    'B',
    10
);

$pdf->Cell(35, 7, 'Cliente:');

$pdf->SetFont(
    'Arial',
    '',
    10
);

$pdf->Cell(
    120,
    7,
    utf8_decode(
        $venta["cliente"]
    ),
    0,
    1
);

$pdf->SetFont(
    'Arial',
    'B',
    10
);

$pdf->Cell(35, 7, 'DNI/RUC:');

$pdf->SetFont(
    'Arial',
    '',
    10
);

$pdf->Cell(
    120,
    7,
    $venta["dni_o_ruc"],
    0,
    1
);

$pdf->SetFont(
    'Arial',
    'B',
    10
);

$pdf->Cell(35, 7, 'Metodo Pago:');

$pdf->SetFont(
    'Arial',
    '',
    10
);

$pdf->Cell(
    120,
    7,
    utf8_decode(
        $venta["metodo_pago"]
    ),
    0,
    1
);

$pdf->Cell(
    190,
    7,
    utf8_decode(
        "Fecha: " .
            $venta["fecha_venta"]
    ),
    0,
    1
);

$pdf->Ln(3);

/*=========================================================
CABECERA TABLA
=========================================================*/

$pdf->SetFillColor(
    45,
    45,
    45
);

$pdf->SetTextColor(
    255,
    255,
    255
);

$pdf->SetFont(
    'Arial',
    'B',
    9
);

$pdf->Cell(10, 8, '#', 1, 0, 'C', true);
$pdf->Cell(70, 8, 'PRODUCTO', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'CODIGO', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'CANT.', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'P.UNIT', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'IMPORTE', 1, 1, 'C', true);

$pdf->SetTextColor(
    0,
    0,
    0
);

/*=========================================================
PRODUCTOS
=========================================================*/

$pdf->SetFont(
    'Arial',
    '',
    9
);

$itemN = 1;

mysqli_data_seek(
    $resultadoProductos,
    0
);

while ($item = mysqli_fetch_assoc($resultadoProductos)) {

    $pdf->Cell(
        10,
        8,
        $itemN++,
        1,
        0,
        'C'
    );

    $pdf->Cell(
        70,
        8,
        utf8_decode(
            substr(
                $item["nombre"],
                0,
                35
            )
        ),
        1
    );

    $pdf->Cell(
        25,
        8,
        $item["codigo"],
        1
    );

    $pdf->Cell(
        20,
        8,
        $item["cantidad_pedido_producto"],
        1,
        0,
        'C'
    );

    $pdf->Cell(
        30,
        8,
        "S/ " .
            number_format(
                $item["precio"],
                2
            ),
        1,
        0,
        'R'
    );

    $pdf->Cell(
        35,
        8,
        "S/ " .
            number_format(
                $item["sub_total"],
                2
            ),
        1,
        1,
        'R'
    );
}

/*=========================================================
RESUMEN
=========================================================*/

$subtotal = $venta["total_venta"];
$igv = 0;

if (
    (int)$venta["aplica_igv"] === 1
) {

    $subtotal =
        $venta["total_venta"] / 1.18;

    $igv =
        $venta["total_venta"] -
        $subtotal;
}

$pdf->Ln(5);

$pdf->SetX(120);

$pdf->Cell(35, 8, 'SUBTOTAL', 1);

$pdf->Cell(
    35,
    8,
    'S/ ' .
        number_format(
            $subtotal,
            2
        ),
    1,
    1,
    'R'
);

$pdf->SetX(120);

$pdf->Cell(35, 8, 'IGV', 1);

$pdf->Cell(
    35,
    8,
    'S/ ' .
        number_format(
            $igv,
            2
        ),
    1,
    1,
    'R'
);

$pdf->SetX(120);

$pdf->SetFont(
    'Arial',
    'B',
    11
);

$pdf->Cell(35, 10, 'TOTAL', 1);

$pdf->Cell(
    35,
    10,
    'S/ ' .
        number_format(
            $venta["total_venta"],
            2
        ),
    1,
    1,
    'R'
);

/*=========================================================
PIE
=========================================================*/

$pdf->Ln(15);

$pdf->SetFont(
    'Arial',
    'I',
    9
);

$pdf->Cell(
    190,
    5,
    utf8_decode(
        'Representación impresa del comprobante'
    ),
    0,
    1,
    'C'
);

$pdf->Cell(
    190,
    5,
    utf8_decode(
        'Gracias por comprar en ' .
            $venta["nombreEmpresa"]
    ),
    0,
    1,
    'C'
);

$pdf->Cell(
    190,
    5,
    utf8_decode(
        'Generado: ' .
            date('d/m/Y H:i:s')
    ),
    0,
    1,
    'C'
);

$pdf->Output(
    'I',
    'Comprobante_' .
        $idVenta .
        '.pdf'
);

if (
    !empty($logoTmp)
    && file_exists($logoTmp)
) {
    unlink($logoTmp);
}
