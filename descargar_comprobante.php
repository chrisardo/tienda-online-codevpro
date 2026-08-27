<?php
//=========================================================
// CoDevPro Technology
// Archivo: descargar_comprobante.php
// Comprobante PDF profesional con FPDF
//
// IMPORTANTE:
// - Solo permite acceder a clientes autenticados.
// - Solo permite descargar comprobantes de pedidos
//   pertenecientes al cliente de la sesión.
// - SOLO permite descargar si estado_envio = ENTREGADO.
//=========================================================


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//=========================================================
// VALIDAR CLIENTE
//=========================================================

if (
    !isset($_SESSION["idCliente"]) ||
    (int)$_SESSION["idCliente"] <= 0
) {

    header("Location: login.php");
    exit;
}


$idCliente = (int)$_SESSION["idCliente"];


//=========================================================
// OBTENER ID DEL PEDIDO
//=========================================================
//
// Se permite:
//
// descargar_comprobante.php?id_ticket_ventas=123
//
// También se utiliza como respaldo la sesión,
// por compatibilidad con el sistema actual.
//=========================================================

$idTicket = isset($_GET["id_ticket_ventas"])
    ? (int)$_GET["id_ticket_ventas"]
    : 0;


if ($idTicket <= 0) {

    $idTicket = isset($_SESSION["pedido_detalle"])
        ? (int)$_SESSION["pedido_detalle"]
        : 0;
}


if ($idTicket <= 0) {

    header("Location: mis_pedidos.php");
    exit;
}


//=========================================================
// CONEXIÓN
//=========================================================

require_once "controladores/conexion.php";


if (
    !isset($conexion) ||
    !$conexion
) {

    die("No fue posible conectar con la base de datos.");
}


mysqli_set_charset(
    $conexion,
    "utf8mb4"
);


//=========================================================
// OBTENER DATOS DEL PEDIDO
//=========================================================
//
// SEGURIDAD:
//
// 1. El pedido debe pertenecer al cliente autenticado.
// 2. El pedido debe estar ENTREGADO.
//=========================================================

$sqlVenta = "
    SELECT
        tv.id_ticket_ventas,
        tv.id_user,
        tv.idCliente,
        tv.direccion_envio,
        tv.pago_cliente,
        tv.total_venta,
        tv.id_metodo_pago,
        tv.estado_venta,
        tv.fecha_venta,
        tv.hora_venta,
        tv.vuelto_venta,
        tv.id_empleado,
        tv.id_repartidor,
        tv.tipo_comprobante,
        tv.serie,
        tv.numero,
        tv.aplica_igv,
        tv.estado_envio,
        tv.fecha_entregado,
        tv.observacion_envio,

        c.nombre AS nombre_cliente,
        c.dni_o_ruc,
        c.celular,
        c.email,

        mp.nombre AS metodo_pago,

        ua.nombreEmpresa,
        ua.direccion AS direccion_empresa,
        ua.celular AS celular_empresa,
        ua.ruc AS ruc_empresa

    FROM ticket_ventas tv

    INNER JOIN clientes c
        ON c.idCliente = tv.idCliente

    LEFT JOIN metodo_pago mp
        ON mp.id_metodo_pago = tv.id_metodo_pago

    LEFT JOIN usuario_acceso ua
        ON ua.id_user = tv.id_user

    WHERE
        tv.id_ticket_ventas = ?
        AND tv.idCliente = ?
        AND tv.estado_envio = 'ENTREGADO'

    LIMIT 1
";


$stmtVenta = mysqli_prepare(
    $conexion,
    $sqlVenta
);


if (!$stmtVenta) {

    die(
        "Error preparando la consulta del pedido."
    );
}


mysqli_stmt_bind_param(
    $stmtVenta,
    "ii",
    $idTicket,
    $idCliente
);


mysqli_stmt_execute(
    $stmtVenta
);


$resultadoVenta =
    mysqli_stmt_get_result(
        $stmtVenta
    );


$venta =
    mysqli_fetch_assoc(
        $resultadoVenta
    );


mysqli_stmt_close(
    $stmtVenta
);


//=========================================================
// VALIDAR PEDIDO
//=========================================================
//
// Si no existe significa que:
// - no pertenece al cliente,
// - no existe,
// - o todavía no está ENTREGADO.
//=========================================================

if (
    !$venta ||
    !is_array($venta)
) {

    http_response_code(403);

    ?>

    <!DOCTYPE html>
    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0">

        <title>Comprobante no disponible</title>

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
            rel="stylesheet">

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css"
            rel="stylesheet">

    </head>

    <body class="bg-light">

        <div class="container py-5">

            <div class="row justify-content-center">

                <div class="col-md-7 col-lg-6">

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body p-5 text-center">

                            <div
                                class="mb-4 text-danger">

                                <i
                                    class="bi bi-file-earmark-lock-fill"
                                    style="font-size:4rem;">
                                </i>

                            </div>

                            <h3 class="fw-bold mb-3">

                                Comprobante no disponible

                            </h3>

                            <p class="text-muted mb-4">

                                El comprobante solo está disponible
                                cuando el pedido ha sido marcado
                                como <strong>ENTREGADO</strong>.

                            </p>

                            <a
                                href="mis_pedidos.php"
                                class="btn btn-primary">

                                <i
                                    class="bi bi-arrow-left me-2">
                                </i>

                                Volver a mis pedidos

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </body>

    </html>

    <?php

    exit;
}


//=========================================================
// VALIDACIÓN EXTRA DEL ESTADO
//=========================================================
//
// Aunque la consulta SQL ya lo valida,
// mantenemos esta comprobación adicional.
//=========================================================

$estadoEnvio = strtoupper(
    trim(
        (string)(
            $venta["estado_envio"]
            ?? ""
        )
    )
);


if ($estadoEnvio !== "ENTREGADO") {

    http_response_code(403);

    die(
        "El comprobante solo está disponible para pedidos entregados."
    );
}


//=========================================================
// OBTENER PRODUCTOS
//=========================================================

$sqlProductos = "
    SELECT
        dtv.id_detalle_ticket,
        dtv.idProducto,
        dtv.cantidad_pedido_producto,
        dtv.aplica_impuesto,
        dtv.porcentaje_impuesto,
        dtv.monto_impuesto,
        dtv.sub_total,

        p.codigo,
        p.nombre,
        p.precio,
        p.aplica_impuesto AS producto_aplica_impuesto

    FROM detalle_ticket_ventas dtv

    INNER JOIN producto p
        ON p.idProducto = dtv.idProducto

    WHERE
        dtv.id_ticket_ventas = ?
        AND dtv.id_user = ?

    ORDER BY
        dtv.id_detalle_ticket ASC
";


$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);


if (!$stmtProductos) {

    die(
        "Error preparando la consulta de productos."
    );
}


//=========================================================
// id_user DEL PEDIDO
//=========================================================

$idUserVenta = (int)(
    $venta["id_user"]
    ?? 0
);


mysqli_stmt_bind_param(
    $stmtProductos,
    "ii",
    $idTicket,
    $idUserVenta
);


mysqli_stmt_execute(
    $stmtProductos
);


$resultadoProductos =
    mysqli_stmt_get_result(
        $stmtProductos
    );


$productos = [];


while (
    $fila =
    mysqli_fetch_assoc(
        $resultadoProductos
    )
) {

    $productos[] = $fila;
}


mysqli_stmt_close(
    $stmtProductos
);


//=========================================================
// VALIDAR PRODUCTOS
//=========================================================

if (empty($productos)) {

    die(
        "No se encontraron productos asociados al pedido."
    );
}


//=========================================================
// DATOS EMPRESA
//=========================================================

$empresa = [

    "nombreEmpresa" =>
        $venta["nombreEmpresa"]
        ?? "CoDevPro Technology",

    "direccion" =>
        $venta["direccion_empresa"]
        ?? "",

    "telefono" =>
        $venta["celular_empresa"]
        ?? "",

    "ruc" =>
        $venta["ruc_empresa"]
        ?? ""

];


//=========================================================
// FPDF
//=========================================================

require_once "fpdf/fpdf.php";


//=========================================================
// FUNCIONES AUXILIARES
//=========================================================

function pdfTexto($texto)
{
    return utf8_decode(
        (string)$texto
    );
}


function pdfNumero($valor)
{
    return number_format(
        (float)$valor,
        2,
        ".",
        ","
    );
}


//=========================================================
// CLASE PDF
//=========================================================

class PDF extends FPDF
{

    public $empresa;


    //=====================================================
    // HEADER
    //=====================================================

    function Header()
    {

        //-------------------------------------------------
        // LOGO
        //-------------------------------------------------

        $rutasLogo = [

            "assets/logos/logo.png",

            "assets/img/logo.png",

            "assets/logo.png"

        ];


        $logoEncontrado = "";


        foreach ($rutasLogo as $ruta) {

            if (
                file_exists($ruta) &&
                is_file($ruta)
            ) {

                $logoEncontrado = $ruta;

                break;
            }
        }


        if ($logoEncontrado !== "") {

            $this->Image(
                $logoEncontrado,
                12,
                10,
                28
            );
        }


        //-------------------------------------------------
        // NOMBRE EMPRESA
        //-------------------------------------------------

        $nombreEmpresa =
            $this->empresa["nombreEmpresa"]
            ?? "CoDevPro Technology";


        $this->SetFont(
            "Arial",
            "B",
            16
        );


        $this->Cell(
            0,
            8,
            pdfTexto(
                $nombreEmpresa
            ),
            0,
            1,
            "C"
        );


        //-------------------------------------------------
        // RUC
        //-------------------------------------------------

        if (
            !empty(
                $this->empresa["ruc"]
            )
        ) {

            $this->SetFont(
                "Arial",
                "",
                9
            );


            $this->Cell(
                0,
                5,
                pdfTexto(
                    "RUC: " .
                    $this->empresa["ruc"]
                ),
                0,
                1,
                "C"
            );
        }


        //-------------------------------------------------
        // DESCRIPCIÓN
        //-------------------------------------------------

        $this->SetFont(
            "Arial",
            "",
            9
        );


        $this->Cell(
            0,
            5,
            pdfTexto(
                "Comprobante de compra"
            ),
            0,
            1,
            "C"
        );


        //-------------------------------------------------
        // LÍNEA
        //-------------------------------------------------

        $this->SetDrawColor(
            180,
            180,
            180
        );


        $this->Line(
            10,
            40,
            200,
            40
        );


        $this->Ln(10);
    }


    //=====================================================
    // FOOTER
    //=====================================================

    function Footer()
    {

        $this->SetY(
            -25
        );


        $this->SetFont(
            "Arial",
            "",
            8
        );


        $nombreEmpresa =
            $this->empresa["nombreEmpresa"]
            ?? "CoDevPro Technology";


        $telefono =
            $this->empresa["telefono"]
            ?? "";


        $this->Cell(
            0,
            5,
            pdfTexto(
                "Gracias por comprar en " .
                $nombreEmpresa
            ),
            0,
            1,
            "C"
        );


        if ($telefono !== "") {

            $this->Cell(
                0,
                5,
                pdfTexto(
                    "WhatsApp: " .
                    $telefono
                ),
                0,
                0,
                "C"
            );

        } else {

            $this->Cell(
                0,
                5,
                pdfTexto(
                    "Gracias por confiar en nosotros."
                ),
                0,
                0,
                "C"
            );
        }
    }


    //=====================================================
    // TÍTULO DE SECCIÓN
    //=====================================================

    function tituloSeccion($texto)
    {

        $this->SetFillColor(
            240,
            240,
            240
        );


        $this->SetDrawColor(
            210,
            210,
            210
        );


        $this->SetFont(
            "Arial",
            "B",
            10
        );


        $this->Cell(
            0,
            7,
            pdfTexto($texto),
            1,
            1,
            "L",
            true
        );


        $this->Ln(2);
    }
}


//=========================================================
// CREAR PDF
//=========================================================

$pdf = new PDF();


$pdf->empresa =
    $empresa;


$pdf->SetMargins(
    10,
    10,
    10
);


$pdf->SetAutoPageBreak(
    true,
    30
);


$pdf->AddPage();


//=========================================================
// DATOS EMPRESA
//=========================================================

$pdf->SetFont(
    "Arial",
    "",
    9
);


if (
    !empty(
        $empresa["direccion"]
    )
) {

    $pdf->Cell(
        0,
        5,
        pdfTexto(
            "Dirección: " .
            $empresa["direccion"]
        ),
        0,
        1
    );
}


if (
    !empty(
        $empresa["telefono"]
    )
) {

    $pdf->Cell(
        0,
        5,
        pdfTexto(
            "WhatsApp: " .
            $empresa["telefono"]
        ),
        0,
        1
    );
}


$pdf->Ln(5);


//=========================================================
// COMPROBANTE
//=========================================================

$pdf->tituloSeccion(
    "COMPROBANTE DE COMPRA"
);


$pdf->SetFont(
    "Arial",
    "",
    10
);


//---------------------------------------------------------
// NÚMERO
//---------------------------------------------------------

$serie =
    trim(
        (string)(
            $venta["serie"]
            ?? ""
        )
    );


$numero =
    (int)(
        $venta["numero"]
        ?? 0
    );


$numeroFormateado =
    str_pad(
        (string)$numero,
        8,
        "0",
        STR_PAD_LEFT
    );


$comprobante =
    trim(
        $serie .
        "-" .
        $numeroFormateado,
        "-"
    );


//---------------------------------------------------------
// DATOS
//---------------------------------------------------------

$pdf->Cell(
    95,
    7,
    pdfTexto(
        "Comprobante: " .
        $comprobante
    ),
    0
);


$pdf->Cell(
    95,
    7,
    pdfTexto(
        "Fecha: " .
        ($venta["fecha_venta"] ?? "-")
    ),
    0,
    1
);


$pdf->Cell(
    95,
    7,
    pdfTexto(
        "Hora: " .
        ($venta["hora_venta"] ?? "-")
    ),
    0
);


$pdf->Cell(
    95,
    7,
    pdfTexto(
        "Pago: " .
        ($venta["metodo_pago"] ?? "-")
    ),
    0,
    1
);


$pdf->Cell(
    95,
    7,
    pdfTexto(
        "Estado: " .
        $estadoEnvio
    ),
    0
);


if (
    !empty(
        $venta["fecha_entregado"]
    )
) {

    $fechaEntregado =
        $venta["fecha_entregado"];


    $pdf->Cell(
        95,
        7,
        pdfTexto(
            "Entregado: " .
            $fechaEntregado
        ),
        0,
        1
    );

} else {

    $pdf->Ln(7);
}


$pdf->Ln(5);


//=========================================================
// CLIENTE
//=========================================================

$pdf->tituloSeccion(
    "DATOS DEL CLIENTE"
);


$pdf->SetFont(
    "Arial",
    "",
    9
);


$pdf->Cell(
    0,
    6,
    pdfTexto(
        "Nombre: " .
        ($venta["nombre_cliente"] ?? "-")
    ),
    0,
    1
);


$pdf->Cell(
    0,
    6,
    pdfTexto(
        "DNI/RUC: " .
        ($venta["dni_o_ruc"] ?? "-")
    ),
    0,
    1
);


$pdf->Cell(
    0,
    6,
    pdfTexto(
        "Teléfono: " .
        ($venta["celular"] ?? "-")
    ),
    0,
    1
);


$pdf->Cell(
    0,
    6,
    pdfTexto(
        "Correo: " .
        ($venta["email"] ?? "-")
    ),
    0,
    1
);


$pdf->Cell(
    0,
    6,
    pdfTexto(
        "Dirección de envío: " .
        ($venta["direccion_envio"] ?? "-")
    ),
    0,
    1
);


$pdf->Ln(8);


//=========================================================
// PRODUCTOS
//=========================================================

$pdf->tituloSeccion(
    "DETALLE DE PRODUCTOS"
);


$pdf->SetFont(
    "Arial",
    "B",
    9
);


//---------------------------------------------------------
// CABECERA
//---------------------------------------------------------

$pdf->Cell(
    75,
    8,
    pdfTexto("Producto"),
    1
);


$pdf->Cell(
    20,
    8,
    "Cant.",
    1,
    0,
    "C"
);


$pdf->Cell(
    40,
    8,
    "Precio",
    1,
    0,
    "C"
);


$pdf->Cell(
    45,
    8,
    "Subtotal",
    1,
    1,
    "C"
);


//---------------------------------------------------------
// PRODUCTOS
//---------------------------------------------------------

$pdf->SetFont(
    "Arial",
    "",
    8
);


$subtotalProductos = 0;

$totalImpuestos = 0;


foreach (
    $productos
    as $producto
) {

    $nombre =
        trim(
            (string)(
                $producto["nombre"]
                ?? "Producto"
            )
        );


    //-----------------------------------------------------
    // LIMITAR NOMBRE
    //-----------------------------------------------------

    if (
        function_exists("mb_substr")
    ) {

        $nombre =
            mb_substr(
                $nombre,
                0,
                42,
                "UTF-8"
            );

    } else {

        $nombre =
            substr(
                $nombre,
                0,
                42
            );
    }


    $cantidad =
        (int)(
            $producto[
                "cantidad_pedido_producto"
            ]
            ?? 0
        );


    $precio =
        (float)(
            $producto["precio"]
            ?? 0
        );


    $subTotal =
        (float)(
            $producto["sub_total"]
            ?? 0
        );


    $impuestoProducto =
        (float)(
            $producto["monto_impuesto"]
            ?? 0
        );


    $subtotalProductos +=
        $subTotal;


    $totalImpuestos +=
        $impuestoProducto;


    //-----------------------------------------------------
    // FILA
    //-----------------------------------------------------

    $pdf->Cell(
        75,
        8,
        pdfTexto($nombre),
        1
    );


    $pdf->Cell(
        20,
        8,
        (string)$cantidad,
        1,
        0,
        "C"
    );


    $pdf->Cell(
        40,
        8,
        "S/ " .
        pdfNumero($precio),
        1,
        0,
        "R"
    );


    $pdf->Cell(
        45,
        8,
        "S/ " .
        pdfNumero($subTotal),
        1,
        1,
        "R"
    );
}


//=========================================================
// TOTALES
//=========================================================

$pdf->Ln(8);


//---------------------------------------------------------
// DETERMINAR IMPUESTO
//---------------------------------------------------------
//
// Si detalle_ticket_ventas ya contiene monto_impuesto,
// utilizamos ese valor.
//
// Si no existe y la venta indica IGV, calculamos la
// diferencia entre total_venta y subtotal.
//---------------------------------------------------------

$totalVenta =
    (float)(
        $venta["total_venta"]
        ?? 0
    );


$aplicaIgv =
    (int)(
        $venta["aplica_igv"]
        ?? 0
    );


if (
    $totalImpuestos <= 0 &&
    $aplicaIgv === 1 &&
    $totalVenta > $subtotalProductos
) {

    $totalImpuestos =
        $totalVenta -
        $subtotalProductos;
}


//---------------------------------------------------------
// SUBTOTAL
//---------------------------------------------------------

$pdf->SetFont(
    "Arial",
    "",
    10
);


$pdf->Cell(
    135,
    7,
    "Subtotal:",
    0,
    0,
    "R"
);


$pdf->Cell(
    45,
    7,
    "S/ " .
    pdfNumero(
        $subtotalProductos
    ),
    0,
    1,
    "R"
);


//---------------------------------------------------------
// IMPUESTO
//---------------------------------------------------------

if (
    $totalImpuestos > 0
) {

    $pdf->Cell(
        135,
        7,
        pdfTexto(
            "IGV / Impuesto:"
        ),
        0,
        0,
        "R"
    );


    $pdf->Cell(
        45,
        7,
        "S/ " .
        pdfNumero(
            $totalImpuestos
        ),
        0,
        1,
        "R"
    );
}


//---------------------------------------------------------
// TOTAL
//---------------------------------------------------------

$pdf->SetFont(
    "Arial",
    "B",
    13
);


$pdf->Cell(
    135,
    10,
    "TOTAL PAGADO:",
    0,
    0,
    "R"
);


$pdf->Cell(
    45,
    10,
    "S/ " .
    pdfNumero(
        $totalVenta
    ),
    0,
    1,
    "R"
);


//=========================================================
// MENSAJE FINAL
//=========================================================

$pdf->Ln(8);


$pdf->SetFont(
    "Arial",
    "",
    9
);


$pdf->SetFillColor(
    240,
    248,
    240
);


$pdf->SetDrawColor(
    190,
    220,
    190
);


$pdf->MultiCell(
    0,
    6,
    pdfTexto(
        "Pedido entregado correctamente. " .
        "Este documento corresponde al comprobante " .
        "de compra del pedido."
    ),
    1,
    "C",
    true
);


//=========================================================
// NOMBRE DEL ARCHIVO
//=========================================================

$nombreArchivo =
    "Comprobante_" .
    $idTicket .
    ".pdf";


//=========================================================
// SALIDA PDF
//=========================================================

$pdf->Output(
    "I",
    $nombreArchivo
);


exit;