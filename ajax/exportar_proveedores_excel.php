<?php

//=====================================================
// CoDevPro Technology
// Archivo: ajax/exportar_proveedores_excel.php
// Módulo: Lista de Proveedores
// Sistema: Inventa
//=====================================================

session_start();


//=====================================================
// VALIDAR SESIÓN
//=====================================================

if (
    !isset($_SESSION["idUser"]) ||
    (int) $_SESSION["idUser"] <= 0
) {

    http_response_code(403);

    exit("Acceso denegado.");
}


$idUser = (int) $_SESSION["idUser"];


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// PHPSPREADSHEET
//=====================================================

require_once "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


//=====================================================
// VALIDAR MÉTODO
//=====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    exit("Método no permitido.");
}


//=====================================================
// OPCIONES PERMITIDAS
//=====================================================

$opcionesPermitidas = [

    "proveedor_datos",
    "proveedor_contacto",
    "proveedor_estado",
    "proveedor_fechas",
    "ubicacion",
    "productos",
    "resumen_productos",
    "gastos"

];


//=====================================================
// OBTENER OPCIONES
//=====================================================

$exportar = isset($_POST["exportar"])
    ? $_POST["exportar"]
    : [];


//=====================================================
// ASEGURAR ARRAY
//=====================================================

if (!is_array($exportar)) {

    $exportar = [];
}


//=====================================================
// FILTRAR OPCIONES
//=====================================================

$exportar = array_values(
    array_intersect(
        $exportar,
        $opcionesPermitidas
    )
);


//=====================================================
// VALIDAR
//=====================================================

if (empty($exportar)) {

    http_response_code(400);

    exit("No se seleccionaron datos para exportar.");
}


//=====================================================
// FUNCIONES AUXILIARES
//=====================================================

function aplicarEstiloEncabezado($sheet, $ultimaColumna)
{
    $rango = "A1:" . $ultimaColumna . "1";

    $sheet->getStyle($rango)->applyFromArray([

        "font" => [

            "bold" => true

        ],

        "fill" => [

            "fillType" => Fill::FILL_SOLID,

            "startColor" => [

                "rgb" => "E8F5E9"

            ]

        ],

        "alignment" => [

            "horizontal" =>
            Alignment::HORIZONTAL_CENTER,

            "vertical" =>
            Alignment::VERTICAL_CENTER

        ]

    ]);

    $sheet->freezePane("A2");
}


function ajustarColumnas($sheet)
{

    foreach (
        $sheet->getColumnIterator()
        as $column
    ) {

        $columnID =
            $column->getColumnIndex();

        $sheet
            ->getColumnDimension($columnID)
            ->setAutoSize(true);
    }
}


function ponerTitulo($sheet, $titulo, $ultimaColumna)
{

    $sheet->insertNewRowBefore(1, 1);

    $sheet->mergeCells(
        "A1:" . $ultimaColumna . "1"
    );

    $sheet->setCellValue(
        "A1",
        $titulo
    );

    $sheet->getStyle(
        "A1:" . $ultimaColumna . "1"
    )->applyFromArray([

        "font" => [

            "bold" => true,

            "size" => 14

        ],

        "alignment" => [

            "horizontal" =>
            Alignment::HORIZONTAL_CENTER

        ]

    ]);
}


//=====================================================
// CREAR SPREADSHEET
//=====================================================

$spreadsheet = new Spreadsheet();


//=====================================================
// HOJA PRINCIPAL
//=====================================================

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle("Proveedores");


//=====================================================
// DETERMINAR COLUMNAS
//=====================================================

$columnas = [];


// DATOS GENERALES

if (
    in_array(
        "proveedor_datos",
        $exportar,
        true
    )
) {

    $columnas[] = "ID Proveedor";

    $columnas[] = "Nombre";

    $columnas[] = "RUC";
}


// CONTACTO

if (
    in_array(
        "proveedor_contacto",
        $exportar,
        true
    )
) {

    $columnas[] = "Celular";

    $columnas[] = "Correo";
}


// ESTADO

if (
    in_array(
        "proveedor_estado",
        $exportar,
        true
    )
) {

    $columnas[] = "Estado";
}


// FECHAS

if (
    in_array(
        "proveedor_fechas",
        $exportar,
        true
    )
) {

    $columnas[] = "Fecha Registro";

    $columnas[] = "Fecha Actualización";
}


// UBICACIÓN

if (
    in_array(
        "ubicacion",
        $exportar,
        true
    )
) {

    $columnas[] = "País";

    $columnas[] = "Departamento";

    $columnas[] = "Provincia";

    $columnas[] = "Distrito";

    $columnas[] = "Dirección";
}


//=====================================================
// ENCABEZADOS
//=====================================================

$columna = 1;

foreach (
    $columnas as $nombreColumna
) {

    $sheet->setCellValueByColumnAndRow(
        $columna,
        1,
        $nombreColumna
    );

    $columna++;
}


$ultimaColumna =
    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
        max(1, count($columnas))
    );


if (!empty($columnas)) {

    aplicarEstiloEncabezado(
        $sheet,
        $ultimaColumna
    );
}


//=====================================================
// CONSULTAR PROVEEDORES
//=====================================================

$sql = "
    SELECT

        p.id_provedor,
        p.nombre,
        p.ruc,
        p.imagen,
        p.id_pais,
        p.id_departamento,
        p.id_provincia,
        p.id_distrito,
        p.direccion,
        p.email,
        p.celular,
        p.Eliminado,
        p.fecha_registro,
        p.fecha_actualizado,

        pais.nombre AS nombre_pais,

        dep.nombre AS nombre_departamento,

        prov.nombre AS nombre_provincia,

        dist.nombre AS nombre_distrito

    FROM provedores p

    LEFT JOIN pais
        ON pais.id_pais = p.id_pais

    LEFT JOIN departamento dep
        ON dep.id_departamento =
           p.id_departamento

    LEFT JOIN provincia prov
        ON prov.id_provincia =
           p.id_provincia

    LEFT JOIN distrito dist
        ON dist.id_distrito =
           p.id_distrito

    WHERE p.id_user = ?

    ORDER BY p.nombre ASC
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $idUser
);

$stmt->execute();

$resultado =
    $stmt->get_result();


//=====================================================
// INSERTAR PROVEEDORES
//=====================================================

$fila = 2;


while (
    $proveedor =
    $resultado->fetch_assoc()
) {

    $columna = 1;


    //=================================================
    // DATOS
    //=================================================

    if (
        in_array(
            "proveedor_datos",
            $exportar,
            true
        )
    ) {

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["id_provedor"]
        );

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["nombre"]
        );

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["ruc"]
        );
    }


    //=================================================
    // CONTACTO
    //=================================================

    if (
        in_array(
            "proveedor_contacto",
            $exportar,
            true
        )
    ) {

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["celular"]
        );

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["email"]
        );
    }


    //=================================================
    // ESTADO
    //=================================================

    if (
        in_array(
            "proveedor_estado",
            $exportar,
            true
        )
    ) {

        $estado =
            ((int) $proveedor["Eliminado"] === 0)
            ? "ACTIVO"
            : "INACTIVO";


        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $estado
        );
    }


    //=================================================
    // FECHAS
    //=================================================

    if (
        in_array(
            "proveedor_fechas",
            $exportar,
            true
        )
    ) {

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["fecha_registro"]
        );

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["fecha_actualizado"]
        );
    }


    //=================================================
    // UBICACIÓN
    //=================================================

    if (
        in_array(
            "ubicacion",
            $exportar,
            true
        )
    ) {

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["nombre_pais"] ?? ""
        );

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["nombre_departamento"] ?? ""
        );

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["nombre_provincia"] ?? ""
        );

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["nombre_distrito"] ?? ""
        );

        $sheet->setCellValueByColumnAndRow(
            $columna++,
            $fila,
            $proveedor["direccion"] ?? ""
        );
    }


    $fila++;
}


$stmt->close();


//=====================================================
// AJUSTAR HOJA PRINCIPAL
//=====================================================

ajustarColumnas($sheet);


//=====================================================
// HOJA PRODUCTOS
//=====================================================

if (
    in_array(
        "productos",
        $exportar,
        true
    )
) {

    $sheetProductos =
        $spreadsheet->createSheet();

    $sheetProductos->setTitle(
        "Productos"
    );


    $encabezados = [

        "ID Proveedor",
        "Proveedor",
        "ID Producto",
        "Código",
        "Producto",
        "Tipo",
        "Costo Compra",
        "Precio",
        "Stock",
        "Categoría",
        "Marca",
        "Sucursal",
        "Fecha Registro",
        "Estado"

    ];


    foreach (
        $encabezados as $indice => $encabezado
    ) {

        $sheetProductos->setCellValueByColumnAndRow(
            $indice + 1,
            1,
            $encabezado
        );
    }


    aplicarEstiloEncabezado(
        $sheetProductos,
        "N"
    );


    $sql = "
        SELECT

            pr.id_provedor,
            pr.nombre AS proveedor,

            p.idProducto,
            p.codigo,
            p.nombre AS producto,
            p.tipo,
            p.costo_compra,
            p.precio,
            p.stock,

            c.nombre AS categoria,

            m.nombre AS marca,

            s.nombre AS sucursal,

            p.fecha_registro,

            p.Eliminado

        FROM producto p

        INNER JOIN provedores pr
            ON pr.id_provedor =
               p.id_provedor

        LEFT JOIN categorias c
            ON c.id_categorias =
               p.id_categorias

        LEFT JOIN marcas m
            ON m.id_marca =
               p.id_marca

        LEFT JOIN sucursal s
            ON s.id_sucursal =
               p.id_sucursal

        WHERE
            p.id_user = ?

            AND pr.id_user = ?

            AND pr.Eliminado = 0

        ORDER BY
            pr.nombre ASC,
            p.nombre ASC
    ";


    $stmt =
        $conexion->prepare($sql);


    $stmt->bind_param(
        "ii",
        $idUser,
        $idUser
    );


    $stmt->execute();

    $result =
        $stmt->get_result();


    $fila = 2;


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $datos = [

            $row["id_provedor"],

            $row["proveedor"],

            $row["idProducto"],

            $row["codigo"],

            $row["producto"],

            $row["tipo"],

            $row["costo_compra"],

            $row["precio"],

            $row["stock"],

            $row["categoria"] ?? "",

            $row["marca"] ?? "",

            $row["sucursal"] ?? "",

            $row["fecha_registro"],

            ((int) $row["Eliminado"] === 0)
                ? "ACTIVO"
                : "INACTIVO"

        ];


        foreach (
            $datos as $indice => $valor
        ) {

            $sheetProductos->setCellValueByColumnAndRow(
                $indice + 1,
                $fila,
                $valor
            );
        }


        $fila++;
    }


    $stmt->close();


    ajustarColumnas(
        $sheetProductos
    );
}


//=====================================================
// HOJA RESUMEN DE PRODUCTOS
//=====================================================

if (
    in_array(
        "resumen_productos",
        $exportar,
        true
    )
) {

    $sheetResumen =
        $spreadsheet->createSheet();


    $sheetResumen->setTitle(
        "Resumen Productos"
    );


    $encabezados = [

        "ID Proveedor",
        "Proveedor",
        "Cantidad de Productos",
        "Stock Total",
        "Valor Inventario a Costo"

    ];


    foreach (
        $encabezados as $indice => $encabezado
    ) {

        $sheetResumen->setCellValueByColumnAndRow(
            $indice + 1,
            1,
            $encabezado
        );
    }


    aplicarEstiloEncabezado(
        $sheetResumen,
        "E"
    );


    $sql = "
        SELECT

            pr.id_provedor,

            pr.nombre AS proveedor,

            COUNT(p.idProducto)
                AS cantidad_productos,

            COALESCE(
                SUM(p.stock),
                0
            ) AS stock_total,

            COALESCE(
                SUM(
                    p.stock *
                    p.costo_compra
                ),
                0
            ) AS valor_inventario

        FROM provedores pr

        LEFT JOIN producto p
            ON p.id_provedor =
               pr.id_provedor

            AND p.id_user = ?

            AND p.Eliminado = 0

        WHERE
            pr.id_user = ?

            AND pr.Eliminado = 0

        GROUP BY
            pr.id_provedor,
            pr.nombre

        ORDER BY
            pr.nombre ASC
    ";


    $stmt =
        $conexion->prepare($sql);


    $stmt->bind_param(
        "ii",
        $idUser,
        $idUser
    );


    $stmt->execute();

    $result =
        $stmt->get_result();


    $fila = 2;


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $sheetResumen->setCellValue(
            "A" . $fila,
            $row["id_provedor"]
        );

        $sheetResumen->setCellValue(
            "B" . $fila,
            $row["proveedor"]
        );

        $sheetResumen->setCellValue(
            "C" . $fila,
            $row["cantidad_productos"]
        );

        $sheetResumen->setCellValue(
            "D" . $fila,
            $row["stock_total"]
        );

        $sheetResumen->setCellValue(
            "E" . $fila,
            $row["valor_inventario"]
        );


        $fila++;
    }


    $stmt->close();


    ajustarColumnas(
        $sheetResumen
    );
}


//=====================================================
// HOJA GASTOS
//=====================================================

if (
    in_array(
        "gastos",
        $exportar,
        true
    )
) {

    $sheetGastos =
        $spreadsheet->createSheet();


    $sheetGastos->setTitle(
        "Gastos Proveedor"
    );


    $encabezados = [

        "ID Proveedor",
        "Proveedor",
        "ID Movimiento",
        "Fecha",
        "Concepto",
        "Descripción",
        "Tipo",
        "Método de Pago",
        "Categoría",
        "Monto"

    ];


    foreach (
        $encabezados as $indice => $encabezado
    ) {

        $sheetGastos->setCellValueByColumnAndRow(
            $indice + 1,
            1,
            $encabezado
        );
    }


    aplicarEstiloEncabezado(
        $sheetGastos,
        "J"
    );


    $sql = "
        SELECT

            pr.id_provedor,

            pr.nombre AS proveedor,

            dg.id_deposito,

            dg.fecha,

            dg.concepto,

            dg.descripcion,

            dg.tipo,

            mp.nombre AS metodo_pago,

            c.nombre AS categoria,

            dg.monto_pago

        FROM deposito_gasto dg

        INNER JOIN provedores pr
            ON pr.id_provedor =
               dg.id_proveedor

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               dg.id_metodo_pago

        LEFT JOIN categorias c
            ON c.id_categorias =
               dg.id_categoria

        WHERE
            dg.id_user = ?

            AND pr.id_user = ?

            AND pr.Eliminado = 0

            AND dg.Eliminado = 0

        ORDER BY
            dg.fecha DESC,
            pr.nombre ASC
    ";


    $stmt =
        $conexion->prepare($sql);


    $stmt->bind_param(
        "ii",
        $idUser,
        $idUser
    );


    $stmt->execute();

    $result =
        $stmt->get_result();


    $fila = 2;


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $datos = [

            $row["id_provedor"],

            $row["proveedor"],

            $row["id_deposito"],

            $row["fecha"],

            $row["concepto"],

            $row["descripcion"],

            $row["tipo"],

            $row["metodo_pago"] ?? "",

            $row["categoria"] ?? "",

            $row["monto_pago"]

        ];


        foreach (
            $datos as $indice => $valor
        ) {

            $sheetGastos->setCellValueByColumnAndRow(
                $indice + 1,
                $fila,
                $valor
            );
        }


        $fila++;
    }


    $stmt->close();


    ajustarColumnas(
        $sheetGastos
    );
}


//=====================================================
// AGREGAR INFORMACIÓN DEL ARCHIVO
//=====================================================

$fechaArchivo =
    date("Y-m-d_H-i-s");


//=====================================================
// NOMBRE DEL ARCHIVO
//=====================================================

$nombreArchivo =
    "proveedores_" .
    $fechaArchivo .
    ".xlsx";


//=====================================================
// LIMPIAR BUFFER
//=====================================================

while (
    ob_get_level() > 0
) {

    ob_end_clean();
}


//=====================================================
// HEADERS
//=====================================================

header(
    "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
);

header(
    "Content-Disposition: attachment; filename=\"" .
        $nombreArchivo .
        "\""
);

header(
    "Cache-Control: max-age=0"
);


//=====================================================
// GENERAR EXCEL
//=====================================================

$writer =
    new Xlsx($spreadsheet);


$writer->save("php://output");


//=====================================================
// FINALIZAR
//=====================================================

exit;
