<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_exportar_ventas.php
// Módulo: Estadísticas de Ventas
// Sistema: Inventa
//=====================================================

declare(strict_types=1);

//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// PHPSPREADSHEET
//=====================================================

$autoload = "../vendor/autoload.php";

if (!file_exists($autoload)) {
    http_response_code(500);
    exit("No se encontró PhpSpreadsheet.");
}

require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {
    responderError("No se pudo establecer la conexión con la base de datos.");
}

$conexion->set_charset("utf8mb4");


//=====================================================
// OBTENER USUARIO
//=====================================================

$idUser = obtenerIdUsuarioActual();

if ($idUser <= 0) {
    responderError("La sesión de usuario no es válida.");
}


//=====================================================
// ACCIÓN
//=====================================================

$accion = limpiarTexto(
    $_POST["accion"] ?? ""
);

if ($accion !== "exportar") {
    responderError("Acción de exportación no válida.");
}


//=====================================================
// TIPO DE EXPORTACIÓN
//=====================================================

$tipo = strtolower(
    limpiarTexto(
        $_POST["tipo"] ?? "completo"
    )
);

$tiposPermitidos = [
    "resumen",
    "detalle",
    "graficos",
    "completo"
];

if (!in_array($tipo, $tiposPermitidos, true)) {
    responderError("Tipo de exportación no válido.");
}


//=====================================================
// FILTROS
//=====================================================

$fechaDesde = limpiarTexto(
    $_POST["fechaDesde"] ?? ""
);

$fechaHasta = limpiarTexto(
    $_POST["fechaHasta"] ?? ""
);

$sucursal = obtenerEntero(
    $_POST["sucursal"] ?? ""
);

$metodoPago = obtenerEntero(
    $_POST["metodoPago"] ?? ""
);

$estado = strtoupper(
    limpiarTexto(
        $_POST["estado"] ?? ""
    )
);

$empleado = obtenerEntero(
    $_POST["empleado"] ?? ""
);

$cliente = obtenerEntero(
    $_POST["cliente"] ?? ""
);

$categoria = obtenerEntero(
    $_POST["categoria"] ?? ""
);


//=====================================================
// VALIDAR FECHAS
//=====================================================

if ($fechaDesde !== "" && !validarFecha($fechaDesde)) {
    responderError("La fecha desde no tiene un formato válido.");
}

if ($fechaHasta !== "" && !validarFecha($fechaHasta)) {
    responderError("La fecha hasta no tiene un formato válido.");
}

if (
    $fechaDesde !== "" &&
    $fechaHasta !== ""
) {
    if ($fechaDesde > $fechaHasta) {
        responderError(
            "La fecha desde no puede ser mayor que la fecha hasta."
        );
    }
}


//=====================================================
// CONSTRUIR FILTROS
//=====================================================

$filtros = construirFiltrosVentas(
    $idUser,
    $fechaDesde,
    $fechaHasta,
    $sucursal,
    $metodoPago,
    $estado,
    $empleado,
    $cliente,
    $categoria
);


//=====================================================
// CREAR EXCEL
//=====================================================

try {

    $spreadsheet = new Spreadsheet();

    //=================================================
    // DOCUMENTO
    //=================================================

    $spreadsheet
        ->getProperties()
        ->setCreator("CoDevPro Technology")
        ->setLastModifiedBy("CoDevPro Technology")
        ->setTitle("Estadísticas de Ventas")
        ->setSubject("Estadísticas de Ventas - Inventa")
        ->setDescription(
            "Reporte de estadísticas de ventas generado desde Inventa."
        );


    //=================================================
    // RESUMEN
    //=================================================

    if (
        $tipo === "resumen" ||
        $tipo === "completo"
    ) {

        crearHojaResumen(
            $spreadsheet,
            $conexion,
            $filtros
        );
    }


    //=================================================
    // DETALLE
    //=================================================

    if (
        $tipo === "detalle" ||
        $tipo === "completo"
    ) {

        crearHojaDetalle(
            $spreadsheet,
            $conexion,
            $filtros
        );
    }


    //=================================================
    // GRÁFICOS
    //=================================================

    if (
        $tipo === "graficos" ||
        $tipo === "completo"
    ) {

        crearHojaEvolucion(
            $spreadsheet,
            $conexion,
            $filtros
        );

        crearHojaMetodosPago(
            $spreadsheet,
            $conexion,
            $filtros
        );

        crearHojaCategorias(
            $spreadsheet,
            $conexion,
            $filtros
        );

        crearHojaSucursales(
            $spreadsheet,
            $conexion,
            $filtros
        );

        crearHojaRankingProductos(
            $spreadsheet,
            $conexion,
            $filtros
        );

        crearHojaRankingClientes(
            $spreadsheet,
            $conexion,
            $filtros
        );
    }


    //=================================================
    // HOJA ACTIVA
    //=================================================

    $spreadsheet
        ->setActiveSheetIndex(0);


    //=================================================
    // NOMBRE DEL ARCHIVO
    //=================================================

    $fechaArchivo = date("Y-m-d_H-i-s");

    $nombreArchivo =
        "estadisticas_ventas_" .
        $fechaArchivo .
        ".xlsx";


    //=================================================
    // CABECERAS HTTP
    //=================================================

    while (ob_get_level()) {
        ob_end_clean();
    }

    header(
        "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    );

    header(
        'Content-Disposition: attachment; filename="' .
            $nombreArchivo .
            '"'
    );

    header("Cache-Control: max-age=0");
    header("Pragma: public");

    //=================================================
    // GENERAR ARCHIVO
    //=================================================

    $writer = new Xlsx($spreadsheet);

    $writer->save("php://output");

    $spreadsheet->disconnectWorksheets();

    unset($spreadsheet);

    exit;
} catch (Throwable $e) {

    error_log(
        "Error exportando estadísticas de ventas: " .
            $e->getMessage()
    );

    responderError(
        "No fue posible generar el archivo de exportación."
    );
}


//=====================================================
// OBTENER ID USUARIO ACTUAL
//=====================================================

function obtenerIdUsuarioActual(): int
{
    $posiblesSesiones = [
        "id_user",
        "idUser",
        "id_usuario",
        "idUsuario"
    ];

    foreach ($posiblesSesiones as $nombreSesion) {

        if (isset($_SESSION[$nombreSesion])) {

            $valor = (int) $_SESSION[$nombreSesion];

            if ($valor > 0) {
                return $valor;
            }
        }
    }


    if (
        isset($_SESSION["usuario"]) &&
        is_array($_SESSION["usuario"])
    ) {

        $posiblesCampos = [
            "id_user",
            "idUser",
            "id_usuario",
            "idUsuario"
        ];

        foreach ($posiblesCampos as $campo) {

            if (
                isset(
                    $_SESSION["usuario"][$campo]
                )
            ) {

                $valor = (int)
                $_SESSION["usuario"][$campo];

                if ($valor > 0) {
                    return $valor;
                }
            }
        }
    }

    return 0;
}


//=====================================================
// CONSTRUIR FILTROS
//=====================================================

function construirFiltrosVentas(
    int $idUser,
    string $fechaDesde,
    string $fechaHasta,
    int $sucursal,
    int $metodoPago,
    string $estado,
    int $empleado,
    int $cliente,
    int $categoria
): array {

    $where = [];

    $parametros = [];

    $tipos = "";


    //=================================================
    // USUARIO
    //=================================================

    $where[] = "t.id_user = ?";

    $parametros[] = $idUser;

    $tipos .= "i";


    //=================================================
    // FECHA DESDE
    //=================================================

    if ($fechaDesde !== "") {

        $where[] = "
            t.fecha_venta >= ?
        ";

        $parametros[] = $fechaDesde . " 00:00:00";

        $tipos .= "s";
    }


    //=================================================
    // FECHA HASTA
    //=================================================

    if ($fechaHasta !== "") {

        /*
         * Se utiliza < día siguiente para incluir
         * todo el día de fechaHasta.
         */

        $fechaHastaExclusiva = date(
            "Y-m-d",
            strtotime(
                $fechaHasta . " +1 day"
            )
        );

        $where[] = "
            t.fecha_venta < ?
        ";

        $parametros[] =
            $fechaHastaExclusiva . " 00:00:00";

        $tipos .= "s";
    }


    //=================================================
    // SUCURSAL
    //=================================================

    if ($sucursal > 0) {

        $where[] = "
            EXISTS (
                SELECT 1
                FROM detalle_ticket_ventas dt_sucursal
                INNER JOIN producto p_sucursal
                    ON p_sucursal.idProducto =
                       dt_sucursal.idProducto
                WHERE dt_sucursal.id_ticket_ventas =
                      t.id_ticket_ventas
                  AND p_sucursal.id_sucursal = ?
            )
        ";

        $parametros[] = $sucursal;

        $tipos .= "i";
    }


    //=================================================
    // MÉTODO DE PAGO
    //=================================================

    if ($metodoPago > 0) {

        $where[] = "
            t.id_metodo_pago = ?
        ";

        $parametros[] = $metodoPago;

        $tipos .= "i";
    }


    //=================================================
    // ESTADO
    //=================================================

    if ($estado !== "") {

        $estadosPermitidos = [
            "PENDIENTE",
            "CONFIRMADO",
            "PREPARANDO",
            "ASIGNADO",
            "OBTENIDO",
            "ENTREGADO",
            "NO_ENTREGADO",
            "CANCELADO"
        ];

        if (
            in_array(
                $estado,
                $estadosPermitidos,
                true
            )
        ) {

            $where[] = "
                t.estado_envio = ?
            ";

            $parametros[] = $estado;

            $tipos .= "s";
        }
    }


    //=================================================
    // EMPLEADO
    //=================================================

    if ($empleado > 0) {

        $where[] = "
            t.id_empleado = ?
        ";

        $parametros[] = $empleado;

        $tipos .= "i";
    }


    //=================================================
    // CLIENTE
    //=================================================

    if ($cliente > 0) {

        $where[] = "
            t.idCliente = ?
        ";

        $parametros[] = $cliente;

        $tipos .= "i";
    }


    //=================================================
    // CATEGORÍA
    //=================================================

    if ($categoria > 0) {

        $where[] = "
            EXISTS (
                SELECT 1
                FROM detalle_ticket_ventas dt_categoria
                INNER JOIN producto p_categoria
                    ON p_categoria.idProducto =
                       dt_categoria.idProducto
                WHERE dt_categoria.id_ticket_ventas =
                      t.id_ticket_ventas
                  AND p_categoria.id_categorias = ?
            )
        ";

        $parametros[] = $categoria;

        $tipos .= "i";
    }


    return [
        "where" => implode(
            " AND ",
            $where
        ),
        "parametros" => $parametros,
        "tipos" => $tipos
    ];
}


//=====================================================
// HOJA RESUMEN
//=====================================================

function crearHojaResumen(
    Spreadsheet $spreadsheet,
    mysqli $conexion,
    array $filtros
): void {

    $hoja = $spreadsheet->getActiveSheet();

    $hoja->setTitle("Resumen");


    //=================================================
    // TÍTULO
    //=================================================

    $hoja->mergeCells("A1:B1");

    $hoja->setCellValue(
        "A1",
        "ESTADÍSTICAS DE VENTAS"
    );

    aplicarTitulo($hoja, "A1:B1");


    //=================================================
    // FILTROS
    //=================================================

    $hoja->setCellValue(
        "A3",
        "Filtro"
    );

    $hoja->setCellValue(
        "B3",
        "Valor"
    );

    aplicarEncabezado(
        $hoja,
        "A3:B3"
    );


    $fila = 4;


    $hoja->setCellValue(
        "A{$fila}",
        "Fecha desde"
    );

    $hoja->setCellValue(
        "B{$fila}",
        obtenerFiltroFecha(
            $filtros,
            "desde"
        )
    );

    $fila++;


    $hoja->setCellValue(
        "A{$fila}",
        "Fecha hasta"
    );

    $hoja->setCellValue(
        "B{$fila}",
        obtenerFiltroFecha(
            $filtros,
            "hasta"
        )
    );

    $fila++;


    //=================================================
    // RESUMEN
    //=================================================

    $fila += 1;

    $hoja->setCellValue(
        "A{$fila}",
        "Indicador"
    );

    $hoja->setCellValue(
        "B{$fila}",
        "Valor"
    );

    aplicarEncabezado(
        $hoja,
        "A{$fila}:B{$fila}"
    );

    $fila++;


    $resumen = obtenerResumen(
        $conexion,
        $filtros
    );


    $indicadores = [
        [
            "Total de ventas",
            $resumen["totalVentas"]
        ],
        [
            "Ingresos totales",
            $resumen["ingresosTotales"]
        ],
        [
            "Productos vendidos",
            $resumen["productosVendidos"]
        ],
        [
            "Ticket promedio",
            $resumen["ticketPromedio"]
        ],
        [
            "Utilidad estimada",
            $resumen["utilidadEstimada"]
        ],
        [
            "Margen estimado (%)",
            $resumen["margenEstimado"]
        ],
        [
            "Clientes atendidos",
            $resumen["clientesAtendidos"]
        ],
        [
            "Productos diferentes",
            $resumen["productosDiferentes"]
        ]
    ];


    foreach ($indicadores as $indicador) {

        $hoja->setCellValue(
            "A{$fila}",
            $indicador[0]
        );

        $hoja->setCellValue(
            "B{$fila}",
            $indicador[1]
        );

        $fila++;
    }


    //=================================================
    // FORMATO
    //=================================================

    $hoja->getColumnDimension("A")
        ->setWidth(28);

    $hoja->getColumnDimension("B")
        ->setWidth(22);

    $hoja->getStyle(
        "B7:B{$fila}"
    )->getNumberFormat()
        ->setFormatCode('#,##0.00');

    $hoja->getStyle(
        "A1:B{$fila}"
    )->getAlignment()
        ->setVertical(
            Alignment::VERTICAL_CENTER
        );
}


//=====================================================
// HOJA DETALLE
//=====================================================

function crearHojaDetalle(
    Spreadsheet $spreadsheet,
    mysqli $conexion,
    array $filtros
): void {

    $hoja = $spreadsheet->createSheet();

    $hoja->setTitle(
        "Detalle de ventas"
    );


    $encabezados = [
        "ID venta",
        "Fecha",
        "Hora",
        "Comprobante",
        "Cliente",
        "Empleado",
        "Método de pago",
        "Productos",
        "Total",
        "Estado"
    ];


    foreach (
        $encabezados as $indice => $encabezado
    ) {

        $columna =
            columnaExcel($indice + 1);

        $hoja->setCellValue(
            $columna . "1",
            $encabezado
        );
    }


    aplicarEncabezado(
        $hoja,
        "A1:J1"
    );


    $sql = "
        SELECT

            t.id_ticket_ventas,

            DATE_FORMAT(
                t.fecha_venta,
                '%d/%m/%Y'
            ) AS fecha,

            COALESCE(
                t.hora_venta,
                ''
            ) AS hora,

            CASE
                WHEN
                    t.tipo_comprobante IS NULL
                    OR t.tipo_comprobante = ''
                THEN CONCAT(
                    COALESCE(t.serie, ''),
                    '-',
                    COALESCE(t.numero, '')
                )
                ELSE CONCAT(
                    t.tipo_comprobante,
                    ' ',
                    COALESCE(t.serie, ''),
                    '-',
                    COALESCE(t.numero, '')
                )
            END AS comprobante,

            COALESCE(
                c.nombre,
                'Cliente general'
            ) AS cliente,

            CASE
                WHEN e.id_empleado IS NULL
                THEN '-'
                ELSE CONCAT(
                    COALESCE(e.nombre, ''),
                    CASE
                        WHEN e.apellido IS NOT NULL
                             AND e.apellido <> ''
                        THEN CONCAT(
                            ' ',
                            e.apellido
                        )
                        ELSE ''
                    END
                )
            END AS empleado,

            COALESCE(
                mp.nombre,
                '-'
            ) AS metodoPago,

            COALESCE(
                (
                    SELECT SUM(
                        dt2.cantidad_pedido_producto
                    )
                    FROM detalle_ticket_ventas dt2
                    WHERE dt2.id_ticket_ventas =
                          t.id_ticket_ventas
                ),
                0
            ) AS productos,

            COALESCE(
                t.total_venta,
                0
            ) AS total,

            COALESCE(
                t.estado_envio,
                t.estado_venta,
                'SIN ESTADO'
            ) AS estado

        FROM ticket_ventas t

        LEFT JOIN clientes c
            ON c.idCliente =
               t.idCliente

        LEFT JOIN empleados e
            ON e.id_empleado =
               t.id_empleado

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               t.id_metodo_pago

        WHERE {$filtros["where"]}

        ORDER BY
            t.fecha_venta DESC,
            t.hora_venta DESC,
            t.id_ticket_ventas DESC
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $filaExcel = 2;


    while (
        $fila = $resultado->fetch_assoc()
    ) {

        $valores = [
            $fila["id_ticket_ventas"] ?? "",
            $fila["fecha"] ?? "",
            $fila["hora"] ?? "",
            $fila["comprobante"] ?? "",
            $fila["cliente"] ?? "",
            $fila["empleado"] ?? "",
            $fila["metodoPago"] ?? "",
            (int) ($fila["productos"] ?? 0),
            (float) ($fila["total"] ?? 0),
            $fila["estado"] ?? ""
        ];


        foreach (
            $valores as $indice => $valor
        ) {

            $columna =
                columnaExcel($indice + 1);

            $hoja->setCellValue(
                $columna . $filaExcel,
                $valor
            );
        }


        $filaExcel++;
    }


    //=================================================
    // ANCHOS
    //=================================================

    $anchos = [
        12,
        14,
        12,
        20,
        28,
        28,
        22,
        14,
        16,
        20
    ];


    foreach ($anchos as $indice => $ancho) {

        $columna =
            columnaExcel($indice + 1);

        $hoja
            ->getColumnDimension($columna)
            ->setWidth($ancho);
    }


    if ($filaExcel > 2) {

        $hoja->getStyle(
            "I2:I" . ($filaExcel - 1)
        )->getNumberFormat()
            ->setFormatCode('#,##0.00');
    }


    $hoja->freezePane("A2");

    aplicarBordes(
        $hoja,
        "A1:J" . max(
            1,
            $filaExcel - 1
        )
    );
}


//=====================================================
// HOJA EVOLUCIÓN
//=====================================================

function crearHojaEvolucion(
    Spreadsheet $spreadsheet,
    mysqli $conexion,
    array $filtros
): void {

    $hoja = $spreadsheet->createSheet();

    $hoja->setTitle(
        "Evolución ventas"
    );


    $hoja->setCellValue(
        "A1",
        "Período"
    );

    $hoja->setCellValue(
        "B1",
        "Total ventas"
    );

    aplicarEncabezado(
        $hoja,
        "A1:B1"
    );


    $sql = "
        SELECT

            DATE(
                t.fecha_venta
            ) AS periodo,

            COALESCE(
                SUM(t.total_venta),
                0
            ) AS total

        FROM ticket_ventas t

        WHERE {$filtros["where"]}

        GROUP BY
            DATE(t.fecha_venta)

        ORDER BY
            periodo ASC
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $filaExcel = 2;


    while (
        $fila = $resultado->fetch_assoc()
    ) {

        $hoja->setCellValue(
            "A{$filaExcel}",
            $fila["periodo"] ?? ""
        );

        $hoja->setCellValue(
            "B{$filaExcel}",
            (float) (
                $fila["total"] ?? 0
            )
        );

        $filaExcel++;
    }


    $hoja
        ->getColumnDimension("A")
        ->setWidth(18);

    $hoja
        ->getColumnDimension("B")
        ->setWidth(20);


    if ($filaExcel > 2) {

        $hoja->getStyle(
            "B2:B" . ($filaExcel - 1)
        )->getNumberFormat()
            ->setFormatCode('#,##0.00');
    }
}


//=====================================================
// HOJA MÉTODOS DE PAGO
//=====================================================

function crearHojaMetodosPago(
    Spreadsheet $spreadsheet,
    mysqli $conexion,
    array $filtros
): void {

    $hoja = $spreadsheet->createSheet();

    $hoja->setTitle(
        "Métodos de pago"
    );


    $hoja->setCellValue(
        "A1",
        "Método de pago"
    );

    $hoja->setCellValue(
        "B1",
        "Total"
    );

    aplicarEncabezado(
        $hoja,
        "A1:B1"
    );


    $sql = "
        SELECT

            COALESCE(
                mp.nombre,
                'Sin método'
            ) AS nombre,

            COALESCE(
                SUM(t.total_venta),
                0
            ) AS total

        FROM ticket_ventas t

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
               t.id_metodo_pago

        WHERE {$filtros["where"]}

        GROUP BY
            t.id_metodo_pago,
            mp.nombre

        ORDER BY
            total DESC
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $filaExcel = 2;


    while (
        $fila = $resultado->fetch_assoc()
    ) {

        $hoja->setCellValue(
            "A{$filaExcel}",
            $fila["nombre"] ?? "Sin método"
        );

        $hoja->setCellValue(
            "B{$filaExcel}",
            (float) (
                $fila["total"] ?? 0
            )
        );

        $filaExcel++;
    }


    ajustarColumnasDosColumnas(
        $hoja
    );
}


//=====================================================
// HOJA CATEGORÍAS
//=====================================================

function crearHojaCategorias(
    Spreadsheet $spreadsheet,
    mysqli $conexion,
    array $filtros
): void {

    $hoja = $spreadsheet->createSheet();

    $hoja->setTitle(
        "Categorías"
    );


    $hoja->setCellValue(
        "A1",
        "Categoría"
    );

    $hoja->setCellValue(
        "B1",
        "Total"
    );

    aplicarEncabezado(
        $hoja,
        "A1:B1"
    );


    $sql = "
        SELECT

            COALESCE(
                c.nombre,
                'Sin categoría'
            ) AS nombre,

            COALESCE(
                SUM(dt.sub_total),
                0
            ) AS total

        FROM ticket_ventas t

        INNER JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas =
               t.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto =
               dt.idProducto

        LEFT JOIN categorias c
            ON c.id_categorias =
               p.id_categorias

        WHERE {$filtros["where"]}

        GROUP BY
            p.id_categorias,
            c.nombre

        ORDER BY
            total DESC

        LIMIT 10
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $filaExcel = 2;


    while (
        $fila = $resultado->fetch_assoc()
    ) {

        $hoja->setCellValue(
            "A{$filaExcel}",
            $fila["nombre"] ?? "Sin categoría"
        );

        $hoja->setCellValue(
            "B{$filaExcel}",
            (float) (
                $fila["total"] ?? 0
            )
        );

        $filaExcel++;
    }


    ajustarColumnasDosColumnas(
        $hoja
    );
}


//=====================================================
// HOJA SUCURSALES
//=====================================================

function crearHojaSucursales(
    Spreadsheet $spreadsheet,
    mysqli $conexion,
    array $filtros
): void {

    $hoja = $spreadsheet->createSheet();

    $hoja->setTitle(
        "Sucursales"
    );


    $hoja->setCellValue(
        "A1",
        "Sucursal"
    );

    $hoja->setCellValue(
        "B1",
        "Total"
    );

    aplicarEncabezado(
        $hoja,
        "A1:B1"
    );


    $sql = "
        SELECT

            COALESCE(
                s.nombre,
                'Sin sucursal'
            ) AS nombre,

            COALESCE(
                SUM(dt.sub_total),
                0
            ) AS total

        FROM ticket_ventas t

        INNER JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas =
               t.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto =
               dt.idProducto

        LEFT JOIN sucursal s
            ON s.id_sucursal =
               p.id_sucursal

        WHERE {$filtros["where"]}

        GROUP BY
            p.id_sucursal,
            s.nombre

        ORDER BY
            total DESC
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $filaExcel = 2;


    while (
        $fila = $resultado->fetch_assoc()
    ) {

        $hoja->setCellValue(
            "A{$filaExcel}",
            $fila["nombre"] ?? "Sin sucursal"
        );

        $hoja->setCellValue(
            "B{$filaExcel}",
            (float) (
                $fila["total"] ?? 0
            )
        );

        $filaExcel++;
    }


    ajustarColumnasDosColumnas(
        $hoja
    );
}


//=====================================================
// HOJA RANKING PRODUCTOS
//=====================================================

function crearHojaRankingProductos(
    Spreadsheet $spreadsheet,
    mysqli $conexion,
    array $filtros
): void {

    $hoja = $spreadsheet->createSheet();

    $hoja->setTitle(
        "Ranking productos"
    );


    $encabezados = [
        "ID producto",
        "Producto",
        "Cantidad",
        "Total"
    ];


    foreach (
        $encabezados as $indice => $encabezado
    ) {

        $columna =
            columnaExcel($indice + 1);

        $hoja->setCellValue(
            $columna . "1",
            $encabezado
        );
    }


    aplicarEncabezado(
        $hoja,
        "A1:D1"
    );


    $sql = "
        SELECT

            p.idProducto,

            p.nombre,

            COALESCE(
                SUM(
                    dt.cantidad_pedido_producto
                ),
                0
            ) AS cantidad,

            COALESCE(
                SUM(dt.sub_total),
                0
            ) AS total

        FROM ticket_ventas t

        INNER JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas =
               t.id_ticket_ventas

        INNER JOIN producto p
            ON p.idProducto =
               dt.idProducto

        WHERE {$filtros["where"]}

        GROUP BY
            p.idProducto,
            p.nombre

        ORDER BY
            cantidad DESC,
            total DESC

        LIMIT 5
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $filaExcel = 2;


    while (
        $fila = $resultado->fetch_assoc()
    ) {

        $hoja->setCellValue(
            "A{$filaExcel}",
            (int) (
                $fila["idProducto"] ?? 0
            )
        );

        $hoja->setCellValue(
            "B{$filaExcel}",
            $fila["nombre"] ?? "Producto"
        );

        $hoja->setCellValue(
            "C{$filaExcel}",
            (int) (
                $fila["cantidad"] ?? 0
            )
        );

        $hoja->setCellValue(
            "D{$filaExcel}",
            (float) (
                $fila["total"] ?? 0
            )
        );

        $filaExcel++;
    }


    $hoja
        ->getColumnDimension("A")
        ->setWidth(14);

    $hoja
        ->getColumnDimension("B")
        ->setWidth(35);

    $hoja
        ->getColumnDimension("C")
        ->setWidth(14);

    $hoja
        ->getColumnDimension("D")
        ->setWidth(18);
}


//=====================================================
// HOJA RANKING CLIENTES
//=====================================================

function crearHojaRankingClientes(
    Spreadsheet $spreadsheet,
    mysqli $conexion,
    array $filtros
): void {

    $hoja = $spreadsheet->createSheet();

    $hoja->setTitle(
        "Ranking clientes"
    );


    $encabezados = [
        "ID cliente",
        "Cliente",
        "Ventas",
        "Total"
    ];


    foreach (
        $encabezados as $indice => $encabezado
    ) {

        $columna =
            columnaExcel($indice + 1);

        $hoja->setCellValue(
            $columna . "1",
            $encabezado
        );
    }


    aplicarEncabezado(
        $hoja,
        "A1:D1"
    );


    $sql = "
        SELECT

            t.idCliente,

            COALESCE(
                c.nombre,
                'Cliente general'
            ) AS nombre,

            COUNT(
                DISTINCT t.id_ticket_ventas
            ) AS ventas,

            COALESCE(
                SUM(t.total_venta),
                0
            ) AS total

        FROM ticket_ventas t

        LEFT JOIN clientes c
            ON c.idCliente =
               t.idCliente

        WHERE {$filtros["where"]}

          AND t.idCliente IS NOT NULL

          AND t.idCliente > 0

        GROUP BY
            t.idCliente,
            c.nombre

        ORDER BY
            total DESC

        LIMIT 5
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $filaExcel = 2;


    while (
        $fila = $resultado->fetch_assoc()
    ) {

        $hoja->setCellValue(
            "A{$filaExcel}",
            (int) (
                $fila["idCliente"] ?? 0
            )
        );

        $hoja->setCellValue(
            "B{$filaExcel}",
            $fila["nombre"] ?? "Cliente"
        );

        $hoja->setCellValue(
            "C{$filaExcel}",
            (int) (
                $fila["ventas"] ?? 0
            )
        );

        $hoja->setCellValue(
            "D{$filaExcel}",
            (float) (
                $fila["total"] ?? 0
            )
        );

        $filaExcel++;
    }


    $hoja
        ->getColumnDimension("A")
        ->setWidth(14);

    $hoja
        ->getColumnDimension("B")
        ->setWidth(35);

    $hoja
        ->getColumnDimension("C")
        ->setWidth(14);

    $hoja
        ->getColumnDimension("D")
        ->setWidth(18);
}


//=====================================================
// RESUMEN
//=====================================================

function obtenerResumen(
    mysqli $conexion,
    array $filtros
): array {

    $sql = "
        SELECT

            COUNT(
                DISTINCT t.id_ticket_ventas
            ) AS totalVentas,

            COALESCE(
                SUM(
                    t.total_venta
                ),
                0
            ) AS ingresosTotales,

            COALESCE(
                SUM(
                    dt.cantidad_pedido_producto
                ),
                0
            ) AS productosVendidos,

            COUNT(
                DISTINCT
                CASE
                    WHEN t.idCliente IS NOT NULL
                         AND t.idCliente > 0
                    THEN t.idCliente
                END
            ) AS clientesAtendidos,

            COUNT(
                DISTINCT dt.idProducto
            ) AS productosDiferentes,

            COALESCE(
                SUM(
                    dt.cantidad_pedido_producto *
                    COALESCE(
                        p.costo_compra,
                        0
                    )
                ),
                0
            ) AS costoProductos

        FROM ticket_ventas t

        LEFT JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas =
               t.id_ticket_ventas

        LEFT JOIN producto p
            ON p.idProducto =
               dt.idProducto

        WHERE {$filtros["where"]}
    ";


    $resultado = ejecutarConsulta(
        $conexion,
        $sql,
        $filtros["parametros"],
        $filtros["tipos"]
    );


    $fila = $resultado->fetch_assoc();


    $totalVentas = (int) (
        $fila["totalVentas"] ?? 0
    );

    $ingresos = (float) (
        $fila["ingresosTotales"] ?? 0
    );

    $productos = (int) (
        $fila["productosVendidos"] ?? 0
    );

    $clientes = (int) (
        $fila["clientesAtendidos"] ?? 0
    );

    $productosDiferentes = (int) (
        $fila["productosDiferentes"] ?? 0
    );

    $costo = (float) (
        $fila["costoProductos"] ?? 0
    );


    $ticketPromedio =
        $totalVentas > 0
        ? $ingresos / $totalVentas
        : 0;


    $utilidad =
        $ingresos - $costo;


    $margen =
        $ingresos > 0
        ? ($utilidad / $ingresos) * 100
        : 0;


    return [

        "totalVentas" =>
        $totalVentas,

        "ingresosTotales" =>
        redondear($ingresos),

        "productosVendidos" =>
        $productos,

        "ticketPromedio" =>
        redondear($ticketPromedio),

        "utilidadEstimada" =>
        redondear($utilidad),

        "margenEstimado" =>
        redondear($margen),

        "clientesAtendidos" =>
        $clientes,

        "productosDiferentes" =>
        $productosDiferentes
    ];
}


//=====================================================
// EJECUTAR CONSULTA
//=====================================================

function ejecutarConsulta(
    mysqli $conexion,
    string $sql,
    array $parametros = [],
    string $tipos = ""
): ResultadoEstadisticas {

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            "Error preparando consulta: " .
                $conexion->error
        );
    }


    if (!empty($parametros)) {

        $referencias = [];

        $referencias[] = $tipos;

        foreach (
            $parametros as $indice => $valor
        ) {

            $referencias[] =
                &$parametros[$indice];
        }


        if (
            !call_user_func_array(
                [$stmt, "bind_param"],
                $referencias
            )
        ) {

            $error = $stmt->error;

            $stmt->close();

            throw new Exception(
                "Error vinculando parámetros: " .
                    $error
            );
        }
    }


    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        throw new Exception(
            "Error ejecutando consulta: " .
                $error
        );
    }


    $resultado = $stmt->get_result();

    if (!$resultado) {

        $error = $stmt->error;

        $stmt->close();

        throw new Exception(
            "No se pudo obtener el resultado: " .
                $error
        );
    }


    $datos = $resultado->fetch_all(
        MYSQLI_ASSOC
    );


    $stmt->close();


    return new ResultadoEstadisticas(
        $datos
    );
}


//=====================================================
// CLASE RESULTADO
//=====================================================

class ResultadoEstadisticas
{
    private array $datos;

    private int $indice = 0;


    public function __construct(
        array $datos
    ) {
        $this->datos = $datos;
    }


    public function fetch_assoc(): ?array
    {
        if (
            $this->indice >=
            count($this->datos)
        ) {

            return null;
        }


        return $this->datos[$this->indice++];
    }
}


//=====================================================
// ESTILOS
//=====================================================

function aplicarTitulo(
    $hoja,
    string $rango
): void {

    $hoja
        ->getStyle($rango)
        ->getFont()
        ->setBold(true)
        ->setSize(16);


    $hoja
        ->getStyle($rango)
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        )
        ->setVertical(
            Alignment::VERTICAL_CENTER
        );


    $hoja
        ->getRowDimension(1)
        ->setRowHeight(28);
}


//=====================================================
// ENCABEZADO
//=====================================================

function aplicarEncabezado(
    $hoja,
    string $rango
): void {

    $estilo = $hoja->getStyle($rango);

    $estilo
        ->getFont()
        ->setBold(true);


    $estilo
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        )
        ->setVertical(
            Alignment::VERTICAL_CENTER
        );


    $estilo
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(
            Border::BORDER_THIN
        );
}


//=====================================================
// BORDES
//=====================================================

function aplicarBordes(
    $hoja,
    string $rango
): void {

    $hoja
        ->getStyle($rango)
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(
            Border::BORDER_THIN
        );
}


//=====================================================
// DOS COLUMNAS
//=====================================================

function ajustarColumnasDosColumnas(
    $hoja
): void {

    $hoja
        ->getColumnDimension("A")
        ->setWidth(30);

    $hoja
        ->getColumnDimension("B")
        ->setWidth(20);


    $hoja
        ->getStyle("B2:B10000")
        ->getNumberFormat()
        ->setFormatCode(
            '#,##0.00'
        );
}


//=====================================================
// COLUMNA EXCEL
//=====================================================

function columnaExcel(int $numero): string
{
    $columna = "";

    while ($numero > 0) {

        $resto =
            ($numero - 1) % 26;

        $columna =
            chr(65 + $resto) .
            $columna;

        $numero =
            intdiv(
                $numero - 1,
                26
            );
    }

    return $columna;
}


//=====================================================
// OBTENER FECHA DEL FILTRO
//=====================================================

function obtenerFiltroFecha(
    array $filtros,
    string $tipo
): string {

    foreach (
        $filtros["parametros"] as $indice => $valor
    ) {

        if (
            isset(
                $filtros["tipos"][$indice]
            ) &&
            $filtros["tipos"][$indice] === "s"
        ) {

            if ($tipo === "desde") {
                return substr(
                    (string) $valor,
                    0,
                    10
                );
            }

            if (
                $tipo === "hasta" &&
                strpos(
                    (string) $valor,
                    "00:00:00"
                ) !== false
            ) {

                $fecha = substr(
                    (string) $valor,
                    0,
                    10
                );

                $timestamp = strtotime(
                    $fecha . " -1 day"
                );

                return date(
                    "Y-m-d",
                    $timestamp
                );
            }
        }
    }

    return "";
}


//=====================================================
// VALIDAR FECHA
//=====================================================

function validarFecha(
    string $fecha
): bool {

    $objeto = DateTime::createFromFormat(
        "Y-m-d",
        $fecha
    );


    return $objeto !== false &&
        $objeto->format("Y-m-d") === $fecha;
}


//=====================================================
// OBTENER ENTERO
//=====================================================

function obtenerEntero($valor): int
{
    if (
        $valor === null ||
        $valor === ""
    ) {
        return 0;
    }

    return (int) $valor;
}


//=====================================================
// LIMPIAR TEXTO
//=====================================================

function limpiarTexto($valor): string
{
    if ($valor === null) {
        return "";
    }

    return trim(
        (string) $valor
    );
}


//=====================================================
// REDONDEAR
//=====================================================

function redondear($valor): float
{
    return round(
        (float) $valor,
        2
    );
}


//=====================================================
// ERROR
//=====================================================

function responderError(
    string $mensaje
): void {

    while (ob_get_level()) {
        ob_end_clean();
    }

    http_response_code(400);

    header(
        "Content-Type: application/json; charset=UTF-8"
    );

    echo json_encode(
        [
            "success" => false,
            "mensaje" => $mensaje
        ],
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}
