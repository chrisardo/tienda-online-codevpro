<?php
session_start();

require_once "controladores/conexion.php";
require_once "fpdf/fpdf.php";

/*=========================================================
=            VALIDAR ID DEL PEDIDO
=========================================================*/

if (!isset($_GET["id"])) {

    die("Pedido no especificado.");
}

$idTicket = intval($_GET["id"]);

if ($idTicket <= 0) {

    die("Pedido inválido.");
}

/*=========================================================
=            OBTENER DATOS DEL PEDIDO
=========================================================*/

$sql = "SELECT

            tv.*,

            c.nombre,
            c.dni_o_ruc,
            c.email,
            c.celular,
            c.direccion,
            pr.nombre AS provincia,
            di.nombre AS distrito,
            dep.nombre AS departamento,
            pa.nombre AS pais,
            mp.nombre AS metodo_pago

        FROM ticket_ventas tv

        LEFT JOIN clientes c
            ON c.idCliente = tv.idCLiente
            LEFT JOIN provincia pr
            ON pr.id_provincia = c.id_provincia

        LEFT JOIN distrito di
            ON di.id_distrito = c.id_distrito

        LEFT JOIN departamento dep
            ON dep.id_departamento = c.id_departamento

        LEFT JOIN pais pa
            ON pa.id_pais = c.id_pais
        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago = tv.id_metodo_pago

        WHERE tv.id_ticket_ventas=?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idTicket
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) == 0) {

    die("El pedido no existe.");
}

$pedido = mysqli_fetch_assoc($resultado);

/*=========================================================
=            OBTENER EMPRESA
=========================================================*/

$sqlEmpresa = "SELECT

                    nombreEmpresa,
                    direccion,
                    celular,
                    ruc

                FROM usuario_acceso

                WHERE id_user=?";

$stmtEmpresa = mysqli_prepare(
    $conexion,
    $sqlEmpresa
);

mysqli_stmt_bind_param(

    $stmtEmpresa,

    "i",

    $pedido["id_user"]

);

mysqli_stmt_execute($stmtEmpresa);

$empresa = mysqli_fetch_assoc(

    mysqli_stmt_get_result($stmtEmpresa)

);

/*=========================================================
=            OBTENER PRODUCTOS
=========================================================*/

$sqlProductos = "SELECT

                    d.idProducto,
                    d.cantidad_pedido_producto,
                    d.sub_total,

                    p.codigo,
                    p.nombre,
                    p.precio

                FROM detalle_ticket_ventas d

                INNER JOIN producto p

                    ON p.idProducto = d.idProducto

                WHERE d.id_ticket_ventas=?

                ORDER BY d.id_detalle_ticket ASC";

$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);

mysqli_stmt_bind_param(

    $stmtProductos,

    "i",

    $idTicket

);

mysqli_stmt_execute($stmtProductos);

$productos = mysqli_stmt_get_result(
    $stmtProductos
);

/*=========================================================
=            CALCULAR TOTALES
=========================================================*/

$totalProductos = 0;

$cantidadArticulos = 0;

mysqli_data_seek($productos, 0);

while ($fila = mysqli_fetch_assoc($productos)) {

    $totalProductos++;

    $cantidadArticulos +=

        $fila["cantidad_pedido_producto"];
}

mysqli_data_seek($productos, 0);

/*=========================================================
=            FORMATO DEL COMPROBANTE
=========================================================*/

$comprobante = "";

if (!empty($pedido["serie"])) {

    $comprobante =
        $pedido["serie"] .
        "-" .
        str_pad(
            $pedido["numero"],
            8,
            "0",
            STR_PAD_LEFT
        );
}

$fechaPedido = date(
    "d/m/Y",
    strtotime($pedido["fecha_venta"])
);

$horaPedido = $pedido["hora_venta"];
/*=========================================================
=            OBTENER LOGO DE LA EMPRESA
=========================================================*/

$logoTemporal = "";

$sqlLogo = "SELECT imagen
            FROM usuario_acceso
            WHERE id_user=?";

$stmtLogo = mysqli_prepare(
    $conexion,
    $sqlLogo
);

mysqli_stmt_bind_param(
    $stmtLogo,
    "i",
    $pedido["id_user"]
);

mysqli_stmt_execute($stmtLogo);

$resLogo = mysqli_stmt_get_result($stmtLogo);

if ($filaLogo = mysqli_fetch_assoc($resLogo)) {

    if (!empty($filaLogo["imagen"])) {

        if (!is_dir("temp")) {

            mkdir("temp", 0777, true);
        }

        $logoTemporal =
            "temp/logo_" .
            $pedido["id_user"] .
            ".png";

        file_put_contents(
            $logoTemporal,
            $filaLogo["imagen"]
        );
    }
}
/*=========================================================
=            CLASE PDF
=========================================================*/

class PDF extends FPDF
{

    public $empresa;

    public $pedido;

    public $comprobante;

    public $logo;

    function Header()
    {

        //==========================
        // LOGO
        //==========================

        if (
            !empty($this->logo)
            &&
            file_exists($this->logo)
        ) {

            $this->Image(
                $this->logo,
                10,
                10,
                28
            );
        }

        //==========================
        // EMPRESA
        //==========================

        $this->SetFont(
            "Arial",
            "B",
            16
        );

        $this->Cell(
            0,
            8,
            utf8_decode(
                $this->empresa["nombreEmpresa"]
            ),
            0,
            1,
            "C"
        );

        $this->SetFont(
            "Arial",
            "",
            10
        );

        $this->Cell(
            0,
            6,
            utf8_decode(
                "RUC: " .
                    $this->empresa["ruc"]
            ),
            0,
            1,
            "C"
        );

        $this->Cell(
            0,
            6,
            utf8_decode(
                $this->empresa["direccion"]
            ),
            0,
            1,
            "C"
        );

        $this->Cell(
            0,
            6,
            utf8_decode(
                "Celular: " .
                    $this->empresa["celular"]
            ),
            0,
            1,
            "C"
        );

        //==========================
        // RECUADRO COMPROBANTE
        //==========================

        $this->Ln(4);

        $this->SetFillColor(
            240,
            240,
            240
        );

        $this->SetFont(
            "Arial",
            "B",
            12
        );

        $this->Cell(
            190,
            9,
            utf8_decode(
                $this->pedido["tipo_comprobante"]
            ),
            1,
            1,
            "C",
            true
        );

        $this->SetFont(
            "Arial",
            "",
            11
        );

        $this->Cell(
            190,
            8,
            $this->comprobante,
            1,
            1,
            "C"
        );

        $this->Ln(6);
    }

    function Footer()
    {

        $this->SetY(-18);

        $this->SetFont(
            "Arial",
            "I",
            8
        );

        $this->Cell(
            0,
            5,
            utf8_decode(
                "Documento generado por CoDevPro Technology"
            ),
            0,
            1,
            "C"
        );

        $this->Cell(
            0,
            5,
            utf8_decode(
                "Página "
            ) .
                $this->PageNo(),
            0,
            0,
            "C"
        );
    }
}
/*=========================================================
=            CREAR PDF
=========================================================*/

$pdf = new PDF("P", "mm", "A4");

$pdf->empresa = $empresa;

$pdf->pedido = $pedido;

$pdf->logo = $logoTemporal;

$pdf->comprobante = $comprobante;

$pdf->AliasNbPages();

$pdf->AddPage();

$pdf->SetAutoPageBreak(true, 20);
/*=========================================================
=            DATOS DEL CLIENTE
=========================================================*/

$pdf->SetFont("Arial", "B", 11);

$pdf->SetFillColor(230, 230, 230);

$pdf->Cell(
    190,
    8,
    utf8_decode("DATOS DEL CLIENTE"),
    1,
    1,
    "L",
    true
);

$pdf->SetFont("Arial", "", 10);

$pdf->Cell(35, 7, "Cliente:", 1, 0);

$pdf->Cell(
    155,
    7,
    utf8_decode($pedido["nombre"]),
    1,
    1
);

$pdf->Cell(35, 7, "Documento:", 1, 0);

$pdf->Cell(
    155,
    7,
    $pedido["dni_o_ruc"],
    1,
    1
);

$pdf->Cell(35, 7, "Celular:", 1, 0);

$pdf->Cell(
    155,
    7,
    $pedido["celular"],
    1,
    1
);

$pdf->Cell(35, 7, "Correo:", 1, 0);

$pdf->Cell(
    155,
    7,
    utf8_decode($pedido["email"]),
    1,
    1
);
$pdf->Cell(35, 7, "Dirección:", 1, 0);

$direccion =

    $pedido["direccion"]

    . ", "

    . $pedido["distrito"]

    . ", "

    . $pedido["provincia"];

$pdf->Cell(

    155,

    7,

    utf8_decode($direccion),

    1,

    1

);
$pdf->Ln(5);
/*=========================================================
=            DATOS DEL PEDIDO
=========================================================*/

$pdf->SetFont("Arial", "B", 11);

$pdf->SetFillColor(230, 230, 230);

$pdf->Cell(
    190,
    8,
    utf8_decode("INFORMACIÓN DEL PEDIDO"),
    1,
    1,
    "L",
    true
);

$pdf->SetFont("Arial", "", 10);
$pdf->Cell(
    40,
    7,
    "Pedido N°",
    1,
    0
);

$pdf->Cell(
    55,
    7,
    $pedido["id_ticket_ventas"],
    1,
    0
);

$pdf->Cell(
    40,
    7,
    "Estado",
    1,
    0
);

$pdf->Cell(
    55,
    7,
    utf8_decode($pedido["estado_venta"]),
    1,
    1
);
$pdf->Cell(
    40,
    7,
    "Fecha",
    1,
    0
);

$pdf->Cell(
    55,
    7,
    $fechaPedido,
    1,
    0
);

$pdf->Cell(
    40,
    7,
    "Hora",
    1,
    0
);

$pdf->Cell(
    55,
    7,
    $horaPedido,
    1,
    1
);
$pdf->Cell(
    40,
    7,
    utf8_decode("Método Pago"),
    1,
    0
);

$pdf->Cell(
    55,
    7,
    utf8_decode($pedido["metodo_pago"]),
    1,
    0
);

$pdf->Cell(
    40,
    7,
    "Articulos",
    1,
    0
);

$pdf->Cell(
    55,
    7,
    $cantidadArticulos,
    1,
    1
);
$pdf->Cell(
    40,
    7,
    "Comprobante",
    1,
    0
);

$pdf->Cell(
    150,
    7,
    $comprobante,
    1,
    1
);
$pdf->Ln(8);
/*=========================================================
=            TABLA DE PRODUCTOS
=========================================================*/

$pdf->SetFont("Arial", "B", 10);

$pdf->SetFillColor(52, 73, 94);

$pdf->SetTextColor(255);

$pdf->Cell(30, 8, "CODIGO", 1, 0, "C", true);

$pdf->Cell(70, 8, "PRODUCTO", 1, 0, "C", true);

$pdf->Cell(25, 8, "PRECIO", 1, 0, "C", true);

$pdf->Cell(20, 8, "CANT.", 1, 0, "C", true);

$pdf->Cell(45, 8, "SUBTOTAL", 1, 1, "C", true);

$pdf->SetTextColor(0);
$totalGeneral = 0;
mysqli_data_seek($productos, 0);

while ($producto = mysqli_fetch_assoc($productos)) {
    $precioUnitario = 0;

    if ($producto["cantidad_pedido_producto"] > 0) {

        $precioUnitario =

            $producto["sub_total"] /

            $producto["cantidad_pedido_producto"];
    }

    $totalGeneral += $producto["sub_total"];
    if ($pdf->GetY() > 250) {

        $pdf->AddPage();
    }
    $pdf->SetFont("Arial", "", 9);

    $pdf->Cell(

        30,

        8,

        utf8_decode($producto["codigo"]),

        1,

        0,

        "C"

    );
    $x = $pdf->GetX();

    $y = $pdf->GetY();

    $pdf->MultiCell(

        70,

        8,

        utf8_decode($producto["nombre"]),

        1

    );

    $altura = $pdf->GetY() - $y;

    $pdf->SetXY($x + 70, $y);
    $pdf->Cell(

        25,

        $altura,

        "S/ " . number_format($precioUnitario, 2),

        1,

        0,

        "R"

    );
    $pdf->Cell(

        20,

        $altura,

        $producto["cantidad_pedido_producto"],

        1,

        0,

        "C"

    );
    $pdf->Cell(

        45,

        $altura,

        "S/ " . number_format($producto["sub_total"], 2),

        1,

        1,

        "R"

    );
}

$pdf->Ln(6);

$pdf->Line(

    10,

    $pdf->GetY(),

    200,

    $pdf->GetY()

);

$pdf->Ln(4);
/*=========================================================
=            TOTALES
=========================================================*/

$pdf->SetFont("Arial", "B", 11);

$pdf->SetX(120);

$pdf->Cell(35, 8, "Subtotal:", 1, 0, "L");

$pdf->Cell(

    35,

    8,

    "S/ " . number_format($totalGeneral, 2),

    1,

    1,

    "R"

);

$pdf->SetX(120);

$pdf->Cell(35, 8, "IGV:", 1, 0, "L");

$pdf->Cell(

    35,

    8,

    $pedido["aplica_igv"] ? "Incluido" : "No aplica",

    1,

    1,

    "R"

);

$pdf->SetFont("Arial", "B", 12);

$pdf->SetFillColor(230, 230, 230);

$pdf->SetX(120);

$pdf->Cell(

    35,

    10,

    "TOTAL:",

    1,

    0,

    "L",

    true

);

$pdf->Cell(

    35,

    10,

    "S/ " . number_format($pedido["total_venta"], 2),

    1,

    1,

    "R",

    true

);
$pdf->Ln(12);

$pdf->SetFont("Arial", "B", 11);

$pdf->Cell(

    190,

    8,

    utf8_decode("OBSERVACIONES"),

    0,

    1

);

$pdf->SetFont("Arial", "", 10);

$pdf->MultiCell(

    190,

    6,

    utf8_decode(

        "Este documento fue generado automáticamente por el sistema de ventas. Conserve este comprobante como constancia de su compra."

    )

);
$pdf->Ln(8);

$pdf->SetFont("Arial", "B", 12);

$pdf->SetTextColor(40, 167, 69);

$pdf->Cell(

    190,

    8,

    utf8_decode(

        "¡Gracias por confiar en nosotros!"

    ),

    0,

    1,

    "C"

);

$pdf->SetTextColor(0);
$pdf->SetFont("Arial", "", 9);

$pdf->Cell(

    190,

    6,

    utf8_decode(

        "Documento generado el "

            .

            date("d/m/Y H:i:s")

    ),

    0,

    1,

    "C"

);
$nombrePDF =

    "Pedido_"

    .

    $comprobante

    .

    ".pdf";
$pdf->Output(

    "I",

    $nombrePDF

);
if (

    !empty($logoTemporal)

    &&

    file_exists($logoTemporal)

) {

    unlink($logoTemporal);
}
