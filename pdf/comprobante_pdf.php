<?php
//==========================================================
// CoDevPro Technology
// pdf/comprobante_pdf.php
// Generación comprobante PDF tipo SUNAT
// FPDF
//==========================================================


session_start();


require_once "../controladores/conexion.php";

require_once "../fpdf/fpdf.php";



/*==========================================================
=            VALIDAR ID
==========================================================*/


$idTicket = intval($_GET["id"] ?? 0);



if ($idTicket <= 0) {

    die("Comprobante inválido");
}





/*==========================================================
=            OBTENER COMPROBANTE
==========================================================*/


$sql = "

SELECT

tv.*,


c.nombre AS cliente,

c.dni_o_ruc,

c.direccion,



mp.nombre AS metodo_pago,



CONCAT(
e.nombre,
' ',
e.apellido
) AS empleado



FROM ticket_ventas tv



LEFT JOIN clientes c

ON tv.idCliente=c.idCliente



LEFT JOIN metodo_pago mp

ON tv.id_metodo_pago=mp.id_metodo_pago



LEFT JOIN empleados e

ON tv.id_empleado=e.id_empleado



WHERE tv.id_ticket_ventas=?



LIMIT 1

";



$stmt = mysqli_prepare(
    $conexion,
    $sql
);



mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idTicket
);



mysqli_stmt_execute($stmt);



$result = mysqli_stmt_get_result($stmt);



if (mysqli_num_rows($result) == 0) {

    die("Comprobante no encontrado");
}


$comprobante = mysqli_fetch_assoc($result);



/*==========================================================
=            BLOQUEAR IMPRESIÓN SI ESTÁ ANULADO
==========================================================*/


if (
    strtoupper($comprobante["estado_venta"]) == "ANULADO"
) {

    die("
    
    <div style='
        font-family:Arial;
        text-align:center;
        margin-top:50px;
    '>

        <h2 style='color:red'>
            COMPROBANTE ANULADO
        </h2>


        <p>
            Este comprobante no puede ser impreso.
        </p>


        <p>
            Estado actual:
            <b>ANULADO</b>
        </p>


    </div>

    ");
}







/*==========================================================
=            DETALLE
==========================================================*/


$sqlDetalle = "


SELECT


dt.cantidad_pedido_producto AS cantidad,


dt.sub_total,


p.nombre,


p.precio



FROM detalle_ticket_ventas dt



INNER JOIN producto p

ON dt.idProducto=p.idProducto



WHERE

dt.id_ticket_ventas=?



ORDER BY dt.id_detalle_ticket ASC



";



$stmt2 = mysqli_prepare(
    $conexion,
    $sqlDetalle
);



mysqli_stmt_bind_param(
    $stmt2,
    "i",
    $idTicket
);



mysqli_stmt_execute($stmt2);



$detalle = mysqli_stmt_get_result($stmt2);






/*==========================================================
=            CLASE PDF TICKET
==========================================================*/


class PDF_Ticket extends FPDF
{


    function Header()
    {


        // Logo opcional

        if (file_exists("../img/logo.png")) {


            $this->Image(
                "../img/logo.png",
                30,
                5,
                30
            );


            $this->Ln(22);
        }



        $this->SetFont(
            "Arial",
            "B",
            12
        );


        $this->Cell(
            0,
            5,
            utf8_decode("CoDevPro Technology"),
            0,
            1,
            "C"
        );



        $this->SetFont(
            "Arial",
            "",
            8
        );



        $this->Cell(
            0,
            4,
            utf8_decode("RUC: 20XXXXXXXXX"),
            0,
            1,
            "C"
        );



        $this->Cell(
            0,
            4,
            utf8_decode("Monitor Huáscar #811"),
            0,
            1,
            "C"
        );



        $this->Cell(
            0,
            4,
            utf8_decode("Iquitos - Perú"),
            0,
            1,
            "C"
        );



        $this->Ln(3);
    }





    function Footer()
    {


        $this->SetY(-15);


        $this->SetFont(
            "Arial",
            "",
            7
        );


        $this->Cell(
            0,
            5,
            utf8_decode("Gracias por su compra - CoDevPro Technology"),
            0,
            1,
            "C"
        );
    }
}






/*==========================================================
=            CREAR PDF
==========================================================*/


$pdf = new PDF_Ticket(
    "P",
    "mm",
    array(80, 250)
);



$pdf->AddPage();





$pdf->SetFont(
    "Arial",
    "B",
    10
);



$pdf->Cell(
    0,
    5,
    utf8_decode(
        $comprobante["tipo_comprobante"]
    ),
    0,
    1,
    "C"
);



$pdf->Cell(
    0,
    5,
    "N° " . $comprobante["serie"] . "-" . $comprobante["numero"],
    0,
    1,
    "C"
);



$pdf->Ln(3);






/*==========================================================
=            ESTADO
==========================================================*/


if ($comprobante["estado_venta"] == "ANULADO") {


    $pdf->SetFont(
        "Arial",
        "B",
        10
    );


    $pdf->Cell(
        0,
        6,
        utf8_decode("*** ANULADO ***"),
        0,
        1,
        "C"
    );
}






$pdf->SetFont(
    "Arial",
    "",
    8
);




$pdf->Cell(
    0,
    5,
    "Fecha: " . $comprobante["fecha_venta"],
    0,
    1
);



$pdf->Cell(
    0,
    5,
    "Hora: " . $comprobante["hora_venta"],
    0,
    1
);



$pdf->Ln(2);





/*==========================================================
=            CLIENTE
==========================================================*/


$pdf->SetFont(
    "Arial",
    "B",
    8
);


$pdf->Cell(
    0,
    5,
    "CLIENTE",
    0,
    1
);



$pdf->SetFont(
    "Arial",
    "",
    8
);



$pdf->MultiCell(
    0,
    4,
    utf8_decode(
        $comprobante["cliente"] ?? "Cliente general"
    )
);



$pdf->Cell(
    0,
    4,
    "DNI/RUC: " .
        ($comprobante["dni_o_ruc"] ?? "-"),
    0,
    1
);



$pdf->Ln(3);






/*==========================================================
=            TABLA PRODUCTOS
==========================================================*/


$pdf->SetFont(
    "Arial",
    "B",
    8
);



$pdf->Cell(
    35,
    5,
    "Producto",
    0
);


$pdf->Cell(
    10,
    5,
    "Cant",
    0,
    0,
    "C"
);


$pdf->Cell(
    15,
    5,
    "Precio",
    0,
    0,
    "R"
);


$pdf->Cell(
    15,
    5,
    "Total",
    0,
    1,
    "R"
);



$pdf->Line(
    5,
    $pdf->GetY(),
    75,
    $pdf->GetY()
);




$pdf->SetFont(
    "Arial",
    "",
    7
);



$subtotal = 0;



while ($p = mysqli_fetch_assoc($detalle)) {


    $subtotal += $p["sub_total"];



    $pdf->MultiCell(
        35,
        4,
        utf8_decode(
            substr($p["nombre"], 0, 25)
        ),
        0
    );



    $y = $pdf->GetY() - 4;


    $pdf->SetXY(
        40,
        $y
    );


    $pdf->Cell(
        10,
        4,
        $p["cantidad"],
        0,
        0,
        "C"
    );



    $pdf->Cell(
        15,
        4,
        number_format(
            $p["precio"],
            2
        ),
        0,
        0,
        "R"
    );



    $pdf->Cell(
        15,
        4,
        number_format(
            $p["sub_total"],
            2
        ),
        0,
        1,
        "R"
    );
}






/*==========================================================
=            TOTALES
==========================================================*/


$pdf->Ln(3);



$igv = 0;



if ($comprobante["aplica_igv"] == 1) {

    $igv = $subtotal * 0.18;
}



$total = $subtotal + $igv;





$pdf->SetFont(
    "Arial",
    "",
    8
);



$pdf->Cell(
    45,
    5,
    "Subtotal:",
    0
);


$pdf->Cell(
    20,
    5,
    "S/ " . number_format($subtotal, 2),
    0,
    1,
    "R"
);




$pdf->Cell(
    45,
    5,
    "IGV:",
    0
);


$pdf->Cell(
    20,
    5,
    "S/ " . number_format($igv, 2),
    0,
    1,
    "R"
);





$pdf->SetFont(
    "Arial",
    "B",
    11
);



$pdf->Cell(
    45,
    7,
    "TOTAL:",
    0
);



$pdf->Cell(
    20,
    7,
    "S/ " . number_format($total, 2),
    0,
    1,
    "R"
);






$pdf->Ln(5);



$pdf->SetFont(
    "Arial",
    "",
    8
);



$pdf->Cell(
    0,
    5,
    utf8_decode(
        "Pago: " . $comprobante["metodo_pago"]
    ),
    0,
    1
);



$pdf->Cell(
    0,
    5,
    utf8_decode(
        "Atendido por: " . $comprobante["empleado"]
    ),
    0,
    1
);





$pdf->Ln(5);



$pdf->SetFont(
    "Arial",
    "",
    7
);



$pdf->MultiCell(
    0,
    4,
    utf8_decode(
        "Representación impresa del comprobante electrónico"
    ),
    0,
    "C"
);





$pdf->Output(
    "I",
    "comprobante_" . $comprobante["numero"] . ".pdf"
);
