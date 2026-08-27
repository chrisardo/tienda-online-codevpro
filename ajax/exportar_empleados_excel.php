<?php

//=====================================================
// CoDevPro Technology
// Archivo: ajax/exportar_empleados_excel.php
// Módulo: Exportación de datos de empleados
//=====================================================

declare(strict_types=1);

session_start();

//=====================================================
// VALIDACIÓN DE SESIÓN
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

$autoload = "../vendor/autoload.php";

if (!file_exists($autoload)) {
    http_response_code(500);
    exit("No se encontró vendor/autoload.php.");
}

require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

//=====================================================
// CONFIGURACIÓN
//=====================================================

$opcionesPermitidas = [
    "empleado_datos",
    "empleado_contacto",
    "empleado_estado",
    "empleado_fechas",
    "rol",
    "ubicacion",
    "permisos",
    "sueldo",
    "pagos",
    "ventas"
];

//=====================================================
// RECIBIR OPCIONES
//=====================================================

$exportar = $_POST["exportar"] ?? [];

if (!is_array($exportar)) {
    $exportar = [];
}

// Limpiar y validar opciones
$exportar = array_values(
    array_unique(
        array_intersect(
            $exportar,
            $opcionesPermitidas
        )
    )
);

//=====================================================
// VALIDAR SELECCIÓN
//=====================================================

if (count($exportar) === 0) {
    http_response_code(400);
    exit("No se seleccionó ninguna opción para exportar.");
}

//=====================================================
// FUNCIONES AUXILIARES
//=====================================================

/**
 * Ejecuta una consulta preparada y devuelve todos los registros.
 */
function ejecutarConsulta(
    mysqli $conexion,
    string $sql,
    string $tipos = "",
    array $parametros = []
): array {

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            "Error preparando consulta: " .
                $conexion->error
        );
    }

    if ($tipos !== "" && count($parametros) > 0) {

        $stmt->bind_param(
            $tipos,
            ...$parametros
        );
    }

    $stmt->execute();

    $resultado = $stmt->get_result();

    $datos = [];

    while ($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
    }

    $stmt->close();

    return $datos;
}


/**
 * Convierte valores NULL en cadena vacía.
 */
function valorExcel($valor): string
{
    if ($valor === null) {
        return "";
    }

    return (string) $valor;
}


/**
 * Evita que Excel interprete determinados valores
 * como fórmulas.
 */
function protegerValorExcel($valor): string
{
    $valor = valorExcel($valor);

    if ($valor === "") {
        return "";
    }

    $primerCaracter = substr($valor, 0, 1);

    if (
        $primerCaracter === "=" ||
        $primerCaracter === "+" ||
        $primerCaracter === "-" ||
        $primerCaracter === "@"
    ) {
        return "'" . $valor;
    }

    return $valor;
}


/**
 * Agrega una hoja y sus datos.
 */
function agregarHoja(
    Spreadsheet $spreadsheet,
    string $titulo,
    array $columnas,
    array $datos
): void {

    // Si es la primera hoja vacía, reutilizarla.
    if (
        $spreadsheet->getSheetCount() === 1 &&
        $spreadsheet->getSheet(0)->getTitle() === "Worksheet"
    ) {
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle($titulo);
    } else {
        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle($titulo);
    }

    //=================================================
    // CABECERAS
    //=================================================

    $columna = 1;

    foreach ($columnas as $nombre) {

        $celda = $hoja->getCellByColumnAndRow(
            $columna,
            1
        );

        $celda->setValue($nombre);

        $columna++;
    }

    //=================================================
    // ESTILO CABECERA
    //=================================================

    $ultimaColumna = count($columnas);

    $rangoCabecera =
        "A1:" .
        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
            $ultimaColumna
        ) .
        "1";

    $hoja
        ->getStyle($rangoCabecera)
        ->getFont()
        ->setBold(true);

    $hoja
        ->getStyle($rangoCabecera)
        ->getFill()
        ->setFillType(
            Fill::FILL_SOLID
        );

    $hoja
        ->getStyle($rangoCabecera)
        ->getFill()
        ->getStartColor()
        ->setARGB("D9EAF7");

    $hoja
        ->getStyle($rangoCabecera)
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        )
        ->setVertical(
            Alignment::VERTICAL_CENTER
        );

    $hoja
        ->getStyle($rangoCabecera)
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(
            Border::BORDER_THIN
        );

    //=================================================
    // DATOS
    //=================================================

    $filaExcel = 2;

    foreach ($datos as $fila) {

        $columna = 1;

        foreach ($columnas as $campo => $nombre) {

            $valor = $fila[$campo] ?? "";

            $valor = protegerValorExcel($valor);

            $celda = $hoja->getCellByColumnAndRow(
                $columna,
                $filaExcel
            );

            $celda->setValueExplicit(
                $valor,
                DataType::TYPE_STRING
            );

            $columna++;
        }

        $filaExcel++;
    }

    //=================================================
    // FILTRO AUTOMÁTICO
    //=================================================

    if ($filaExcel > 2) {

        $rangoFiltro =
            "A1:" .
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                $ultimaColumna
            ) .
            ($filaExcel - 1);

        $hoja->setAutoFilter($rangoFiltro);
    }

    //=================================================
    // CONGELAR CABECERA
    //=================================================

    $hoja->freezePane("A2");

    //=================================================
    // ANCHO AUTOMÁTICO
    //=================================================

    for ($i = 1; $i <= $ultimaColumna; $i++) {

        $letra =
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                $i
            );

        $hoja
            ->getColumnDimension($letra)
            ->setAutoSize(true);
    }

    // Limitar anchos excesivos
    for ($i = 1; $i <= $ultimaColumna; $i++) {

        $letra =
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                $i
            );

        $dimension = $hoja->getColumnDimension($letra);

        if ($dimension->getWidth() > 45) {
            $dimension->setWidth(45);
        }
    }
}


//=====================================================
// CREAR SPREADSHEET
//=====================================================

$spreadsheet = new Spreadsheet();

// Propiedades del archivo
$spreadsheet
    ->getProperties()
    ->setCreator("CoDevPro Technology")
    ->setLastModifiedBy("CoDevPro Technology")
    ->setTitle("Exportación de empleados")
    ->setSubject("Datos de empleados")
    ->setDescription(
        "Exportación de datos de empleados generada desde el sistema."
    );


//=====================================================
// OBTENER EMPLEADOS
//=====================================================

$empleados = ejecutarConsulta(
    $conexion,

    "
    SELECT
        e.id_empleado,
        e.nombre,
        e.apellido,
        e.dni,
        e.celular,
        e.direccion,
        e.email,
        e.estado,
        e.fecha_registro,
        e.fecha_actualizado,
        e.id_rol,
        e.id_pais,
        e.id_departamento,
        e.id_provincia,
        e.id_distrito

    FROM empleados e

    WHERE e.id_user = ?

    ORDER BY
        e.apellido ASC,
        e.nombre ASC
    ",

    "i",

    [$idUser]
);


//=====================================================
// MAPA DE EMPLEADOS
//=====================================================

$mapaEmpleados = [];

foreach ($empleados as $empleado) {

    $mapaEmpleados[(int) $empleado["id_empleado"]] = $empleado;
}


//=====================================================
// 1. DATOS DEL EMPLEADO
//=====================================================

$opcionesEmpleado =
    array_intersect(
        [
            "empleado_datos",
            "empleado_contacto",
            "empleado_estado",
            "empleado_fechas"
        ],
        $exportar
    );

if (count($opcionesEmpleado) > 0) {

    $columnas = [
        "id_empleado" => "ID Empleado"
    ];

    // Datos personales
    if (
        in_array(
            "empleado_datos",
            $exportar,
            true
        )
    ) {

        $columnas["nombre"] = "Nombre";
        $columnas["apellido"] = "Apellido";
        $columnas["dni"] = "DNI";
    }

    // Contacto
    if (
        in_array(
            "empleado_contacto",
            $exportar,
            true
        )
    ) {

        $columnas["celular"] = "Celular";
        $columnas["direccion"] = "Dirección";
        $columnas["email"] = "Correo electrónico";
    }

    // Estado
    if (
        in_array(
            "empleado_estado",
            $exportar,
            true
        )
    ) {

        $columnas["estado"] = "Estado";
    }

    // Fechas
    if (
        in_array(
            "empleado_fechas",
            $exportar,
            true
        )
    ) {

        $columnas["fecha_registro"] = "Fecha de registro";
        $columnas["fecha_actualizado"] = "Fecha de actualización";
    }

    agregarHoja(
        $spreadsheet,
        "Empleados",
        $columnas,
        $empleados
    );
}


//=====================================================
// 2. ROLES / CARGOS
//=====================================================

if (
    in_array(
        "rol",
        $exportar,
        true
    )
) {

    $datosRol = ejecutarConsulta(
        $conexion,

        "
        SELECT

            e.id_empleado,

            CONCAT(
                e.nombre,
                ' ',
                e.apellido
            ) AS empleado,

            e.dni,

            e.id_rol,

            COALESCE(
                r.nombre,
                'Sin rol asignado'
            ) AS rol

        FROM empleados e

        LEFT JOIN rol r
            ON r.id_rol = e.id_rol
            AND r.id_user = e.id_user

        WHERE e.id_user = ?

        ORDER BY
            e.apellido ASC,
            e.nombre ASC
        ",

        "i",

        [$idUser]
    );

    $columnas = [
        "id_empleado" => "ID Empleado",
        "empleado" => "Empleado",
        "dni" => "DNI",
        "id_rol" => "ID Rol",
        "rol" => "Rol / Cargo"
    ];

    agregarHoja(
        $spreadsheet,
        "Roles",
        $columnas,
        $datosRol
    );
}


//=====================================================
// 3. UBICACIÓN
//=====================================================

if (
    in_array(
        "ubicacion",
        $exportar,
        true
    )
) {

    $datosUbicacion = ejecutarConsulta(
        $conexion,

        "
        SELECT

            e.id_empleado,

            CONCAT(
                e.nombre,
                ' ',
                e.apellido
            ) AS empleado,

            e.dni,

            COALESCE(
                p.nombre,
                ''
            ) AS pais,

            COALESCE(
                d.nombre,
                ''
            ) AS departamento,

            COALESCE(
                pr.nombre,
                ''
            ) AS provincia,

            COALESCE(
                di.nombre,
                ''
            ) AS distrito,

            e.direccion

        FROM empleados e

        LEFT JOIN pais p
            ON p.id_pais = e.id_pais
            AND p.id_user = e.id_user

        LEFT JOIN departamento d
            ON d.id_departamento = e.id_departamento
            AND d.id_user = e.id_user

        LEFT JOIN provincia pr
            ON pr.id_provincia = e.id_provincia
            AND pr.id_user = e.id_user

        LEFT JOIN distrito di
            ON di.id_distrito = e.id_distrito
            AND di.id_user = e.id_user

        WHERE e.id_user = ?

        ORDER BY
            e.apellido ASC,
            e.nombre ASC
        ",

        "i",

        [$idUser]
    );

    $columnas = [
        "id_empleado" => "ID Empleado",
        "empleado" => "Empleado",
        "dni" => "DNI",
        "pais" => "País",
        "departamento" => "Departamento",
        "provincia" => "Provincia",
        "distrito" => "Distrito",
        "direccion" => "Dirección"
    ];

    agregarHoja(
        $spreadsheet,
        "Ubicación",
        $columnas,
        $datosUbicacion
    );
}


//=====================================================
// 4. PERMISOS DEL ROL
//=====================================================

if (
    in_array(
        "permisos",
        $exportar,
        true
    )
) {

    $datosPermisos = ejecutarConsulta(
        $conexion,

        "
        SELECT

            e.id_empleado,

            CONCAT(
                e.nombre,
                ' ',
                e.apellido
            ) AS empleado,

            COALESCE(
                r.nombre,
                'Sin rol'
            ) AS rol,

            m.id_modulo,

            m.nombre AS modulo,

            m.codigo,

            CASE
                WHEN pr.ver = 1
                THEN 'SI'
                ELSE 'NO'
            END AS ver,

            CASE
                WHEN pr.crear = 1
                THEN 'SI'
                ELSE 'NO'
            END AS crear,

            CASE
                WHEN pr.editar = 1
                THEN 'SI'
                ELSE 'NO'
            END AS editar,

            CASE
                WHEN pr.eliminar = 1
                THEN 'SI'
                ELSE 'NO'
            END AS eliminar

        FROM empleados e

        LEFT JOIN rol r
            ON r.id_rol = e.id_rol
            AND r.id_user = e.id_user

        LEFT JOIN permisos_rol pr
            ON pr.id_rol = e.id_rol
            AND pr.id_user = e.id_user

        LEFT JOIN modulos m
            ON m.id_modulo = pr.id_modulo
            AND m.id_user = e.id_user

        WHERE e.id_user = ?

        ORDER BY
            e.apellido ASC,
            e.nombre ASC,
            m.orden ASC,
            m.nombre ASC
        ",

        "i",

        [$idUser]
    );

    $columnas = [
        "id_empleado" => "ID Empleado",
        "empleado" => "Empleado",
        "rol" => "Rol",
        "id_modulo" => "ID Módulo",
        "modulo" => "Módulo",
        "codigo" => "Código",
        "ver" => "Ver",
        "crear" => "Crear",
        "editar" => "Editar",
        "eliminar" => "Eliminar"
    ];

    agregarHoja(
        $spreadsheet,
        "Permisos",
        $columnas,
        $datosPermisos
    );
}


//=====================================================
// 5. SUELDO CONFIGURADO
//=====================================================

if (
    in_array(
        "sueldo",
        $exportar,
        true
    )
) {

    $datosSueldo = ejecutarConsulta(
        $conexion,

        "
        SELECT

            e.id_empleado,

            CONCAT(
                e.nombre,
                ' ',
                e.apellido
            ) AS empleado,

            e.dni,

            s.id_sueldo,

            s.sueldo_base,

            s.tipo_pago,

            s.fecha_inicio,

            s.fecha_fin,

            s.estado,

            s.observacion,

            s.fecha_registro,

            s.fecha_actualizado

        FROM empleados e

        INNER JOIN sueldo_empleado s
            ON s.id_empleado = e.id_empleado
            AND s.id_user = e.id_user

        WHERE e.id_user = ?

        ORDER BY
            e.apellido ASC,
            e.nombre ASC,
            s.fecha_inicio DESC
        ",

        "i",

        [$idUser]
    );

    $columnas = [
        "id_empleado" => "ID Empleado",
        "empleado" => "Empleado",
        "dni" => "DNI",
        "id_sueldo" => "ID Sueldo",
        "sueldo_base" => "Sueldo base",
        "tipo_pago" => "Tipo de pago",
        "fecha_inicio" => "Fecha inicio",
        "fecha_fin" => "Fecha fin",
        "estado" => "Estado",
        "observacion" => "Observación",
        "fecha_registro" => "Fecha registro",
        "fecha_actualizado" => "Fecha actualización"
    ];

    agregarHoja(
        $spreadsheet,
        "Sueldos",
        $columnas,
        $datosSueldo
    );
}


//=====================================================
// 6. HISTORIAL DE PAGOS
//=====================================================

if (
    in_array(
        "pagos",
        $exportar,
        true
    )
) {

    $datosPagos = ejecutarConsulta(
        $conexion,

        "
        SELECT

            e.id_empleado,

            CONCAT(
                e.nombre,
                ' ',
                e.apellido
            ) AS empleado,

            e.dni,

            pe.id_pago,

            pe.periodo_inicio,

            pe.periodo_fin,

            pe.monto_base,

            pe.bonificaciones,

            pe.descuentos,

            pe.monto_total,

            pe.fecha_pago,

            pe.estado,

            pe.observacion,

            cb.nombre AS cuenta_bancaria,

            mp.nombre AS metodo_pago,

            pe.fecha_registro,

            pe.fecha_actualizado

        FROM empleados e

        INNER JOIN pago_empleado pe
            ON pe.id_empleado = e.id_empleado
            AND pe.id_user = e.id_user

        LEFT JOIN cuenta_banco cb
            ON cb.id_cuenta_bancaria = pe.id_cuenta_bancaria
            AND cb.id_user = e.id_user

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago = pe.id_metodo_pago
            AND mp.id_user = e.id_user

        WHERE e.id_user = ?

        ORDER BY
            e.apellido ASC,
            e.nombre ASC,
            pe.fecha_pago DESC
        ",

        "i",

        [$idUser]
    );

    $columnas = [
        "id_empleado" => "ID Empleado",
        "empleado" => "Empleado",
        "dni" => "DNI",
        "id_pago" => "ID Pago",
        "periodo_inicio" => "Periodo inicio",
        "periodo_fin" => "Periodo fin",
        "monto_base" => "Monto base",
        "bonificaciones" => "Bonificaciones",
        "descuentos" => "Descuentos",
        "monto_total" => "Monto total",
        "fecha_pago" => "Fecha de pago",
        "estado" => "Estado",
        "observacion" => "Observación",
        "cuenta_bancaria" => "Cuenta bancaria",
        "metodo_pago" => "Método de pago",
        "fecha_registro" => "Fecha registro",
        "fecha_actualizado" => "Fecha actualización"
    ];

    agregarHoja(
        $spreadsheet,
        "Pagos",
        $columnas,
        $datosPagos
    );
}


//=====================================================
// 7. VENTAS ASIGNADAS
//=====================================================

if (
    in_array(
        "ventas",
        $exportar,
        true
    )
) {

    $datosVentas = ejecutarConsulta(
        $conexion,

        "
        SELECT

            e.id_empleado,

            CONCAT(
                e.nombre,
                ' ',
                e.apellido
            ) AS empleado,

            e.dni,

            tv.id_ticket_ventas,

            tv.idCliente,

            COALESCE(
                c.nombre,
                ''
            ) AS cliente,

            c.dni_o_ruc,

            tv.tipo_comprobante,

            tv.serie,

            tv.numero,

            tv.fecha_venta,

            tv.hora_venta,

            tv.total_venta,

            tv.pago_cliente,

            tv.vuelto_venta,

            COALESCE(
                mp.nombre,
                ''
            ) AS metodo_pago,

            tv.estado_venta,

            tv.estado_envio,

            tv.aplica_igv,

            tv.direccion_envio,

            tv.observacion_envio,

            tv.fecha_confirmado,

            tv.fecha_preparando,

            tv.fecha_enviado,

            tv.fecha_entregado,

            tv.fecha_cancelado

        FROM empleados e

        INNER JOIN ticket_ventas tv
            ON tv.id_empleado = e.id_empleado
            AND tv.id_user = e.id_user

        LEFT JOIN clientes c
            ON c.idCliente = tv.idCliente
            AND c.id_user = e.id_user

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago = tv.id_metodo_pago
            AND mp.id_user = e.id_user

        WHERE e.id_user = ?

        ORDER BY
            e.apellido ASC,
            e.nombre ASC,
            tv.fecha_venta DESC,
            tv.hora_venta DESC
        ",

        "i",

        [$idUser]
    );

    $columnas = [
        "id_empleado" => "ID Empleado",
        "empleado" => "Empleado",
        "dni" => "DNI Empleado",
        "id_ticket_ventas" => "ID Venta",
        "idCliente" => "ID Cliente",
        "cliente" => "Cliente",
        "dni_o_ruc" => "DNI / RUC Cliente",
        "tipo_comprobante" => "Tipo comprobante",
        "serie" => "Serie",
        "numero" => "Número",
        "fecha_venta" => "Fecha venta",
        "hora_venta" => "Hora venta",
        "total_venta" => "Total venta",
        "pago_cliente" => "Pago cliente",
        "vuelto_venta" => "Vuelto",
        "metodo_pago" => "Método de pago",
        "estado_venta" => "Estado venta",
        "estado_envio" => "Estado envío",
        "aplica_igv" => "Aplica IGV",
        "direccion_envio" => "Dirección envío",
        "observacion_envio" => "Observación envío",
        "fecha_confirmado" => "Fecha confirmado",
        "fecha_preparando" => "Fecha preparando",
        "fecha_enviado" => "Fecha enviado",
        "fecha_entregado" => "Fecha entregado",
        "fecha_cancelado" => "Fecha cancelado"
    ];

    agregarHoja(
        $spreadsheet,
        "Ventas",
        $columnas,
        $datosVentas
    );


    //=================================================
    // DETALLE DE VENTAS
    //=================================================

    $datosDetalleVentas = ejecutarConsulta(
        $conexion,

        "
        SELECT

            e.id_empleado,

            CONCAT(
                e.nombre,
                ' ',
                e.apellido
            ) AS empleado,

            tv.id_ticket_ventas,

            dt.id_detalle_ticket,

            p.idProducto,

            p.codigo AS codigo_producto,

            p.nombre AS producto,

            dt.cantidad_pedido_producto,

            dt.sub_total

        FROM empleados e

        INNER JOIN ticket_ventas tv
            ON tv.id_empleado = e.id_empleado
            AND tv.id_user = e.id_user

        INNER JOIN detalle_ticket_ventas dt
            ON dt.id_ticket_ventas = tv.id_ticket_ventas
            AND dt.id_user = e.id_user

        LEFT JOIN producto p
            ON p.idProducto = dt.idProducto
            AND p.id_user = e.id_user

        WHERE e.id_user = ?

        ORDER BY
            tv.id_ticket_ventas DESC,
            dt.id_detalle_ticket ASC
        ",

        "i",

        [$idUser]
    );

    $columnasDetalle = [
        "id_empleado" => "ID Empleado",
        "empleado" => "Empleado",
        "id_ticket_ventas" => "ID Venta",
        "id_detalle_ticket" => "ID Detalle",
        "idProducto" => "ID Producto",
        "codigo_producto" => "Código producto",
        "producto" => "Producto",
        "cantidad_pedido_producto" => "Cantidad",
        "sub_total" => "Subtotal"
    ];

    agregarHoja(
        $spreadsheet,
        "Detalle Ventas",
        $columnasDetalle,
        $datosDetalleVentas
    );
}


//=====================================================
// SI NO EXISTEN EMPLEADOS
//=====================================================

if ($spreadsheet->getSheetCount() === 0) {

    $hoja = $spreadsheet->createSheet();

    $hoja->setTitle("Empleados");

    $hoja->setCellValue(
        "A1",
        "No existen empleados registrados."
    );
}


//=====================================================
// SELECCIONAR PRIMERA HOJA
//=====================================================

$spreadsheet->setActiveSheetIndex(0);


//=====================================================
// NOMBRE DEL ARCHIVO
//=====================================================

$fechaArchivo = date("Y-m-d_H-i-s");

$nombreArchivo =
    "empleados_exportacion_" .
    $fechaArchivo .
    ".xlsx";


//=====================================================
// LIMPIAR BUFFER
//=====================================================

while (ob_get_level() > 0) {
    ob_end_clean();
}


//=====================================================
// HEADERS HTTP
//=====================================================

header(
    "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
);

header(
    'Content-Disposition: attachment; filename="' .
        $nombreArchivo .
        '"'
);

header(
    "Cache-Control: max-age=0"
);

header(
    "Pragma: public"
);


//=====================================================
// GENERAR EXCEL
//=====================================================

$writer = new Xlsx($spreadsheet);

$writer->save("php://output");


//=====================================================
// LIBERAR MEMORIA
//=====================================================

$spreadsheet->disconnectWorksheets();

unset($spreadsheet);

exit;
