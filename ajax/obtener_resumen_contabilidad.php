<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_resumen_contabilidad.php
// Módulo: Contabilidad
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=utf-8");

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR USUARIO
//=====================================================

$idUser = (int)($_SESSION["idUser"] ?? 0);

if ($idUser <= 0) {

    echo json_encode(
        [
            "estado" => false,
            "mensaje" => "No se pudo identificar al usuario."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!$conexion) {

    echo json_encode(
        [
            "estado" => false,
            "mensaje" => "No existe conexión con la base de datos."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

//=====================================================
// PARÁMETROS
//=====================================================

$anio = trim(
    $_GET["anio"] ?? ""
);

$periodo = trim(
    $_GET["periodo"] ?? "todos"
);

$fechaInicio = trim(
    $_GET["fecha_inicio"] ?? ""
);

$fechaFin = trim(
    $_GET["fecha_fin"] ?? ""
);

$mesesGrafico = (int)(
    $_GET["meses"] ?? 12
);

if (
    $mesesGrafico !== 3 &&
    $mesesGrafico !== 6 &&
    $mesesGrafico !== 12
) {

    $mesesGrafico = 12;
}

//=====================================================
// FUNCIONES AUXILIARES
//=====================================================

function responderJSON($datos)
{
    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// VALIDAR FECHA
//=====================================================

function fechaValida($fecha)
{

    if (
        empty($fecha)
    ) {

        return false;
    }

    $objeto =
        DateTime::createFromFormat(
            "Y-m-d",
            $fecha
        );

    return (
        $objeto !== false &&
        $objeto->format("Y-m-d") === $fecha
    );
}

//=====================================================
// VALIDAR AÑO
//=====================================================

if (
    $anio !== "" &&
    !preg_match(
        "/^\d{4}$/",
        $anio
    )
) {

    $anio = "";
}

//=====================================================
// VALIDAR PERÍODO
//=====================================================

if (
    $periodo !== "todos" &&
    !preg_match(
        "/^(0[1-9]|1[0-2])$/",
        $periodo
    )
) {

    $periodo = "todos";
}

//=====================================================
// NORMALIZAR FECHAS
//=====================================================

if (
    !fechaValida(
        $fechaInicio
    )
) {

    $fechaInicio = "";
}

if (
    !fechaValida(
        $fechaFin
    )
) {

    $fechaFin = "";
}

//=====================================================
// SI LAS FECHAS ESTÁN INVERTIDAS
//=====================================================

if (
    $fechaInicio !== "" &&
    $fechaFin !== ""
) {

    if (
        $fechaInicio > $fechaFin
    ) {

        $temporal =
            $fechaInicio;

        $fechaInicio =
            $fechaFin;

        $fechaFin =
            $temporal;
    }
}

//=====================================================
// FECHAS DEL FILTRO
//=====================================================

$whereFecha =
    " AND dg.fecha ";

$whereFechaDG =
    "";

$paramsDG =
    [$idUser];

$typesDG =
    "i";

//=====================================================
// CONDICIONES DE DEPÓSITOS / GASTOS
//=====================================================

$whereDG =
    "dg.id_user = ?
     AND dg.Eliminado = 0";

$paramsDGBase =
    [$idUser];

$typesDGBase =
    "i";

//-----------------------------------------------------
// AÑO
//-----------------------------------------------------

if (
    $anio !== ""
) {

    $whereDG .=
        " AND YEAR(dg.fecha) = ? ";

    $paramsDGBase[] =
        (int)$anio;

    $typesDGBase .=
        "i";
}

//-----------------------------------------------------
// PERÍODO
//-----------------------------------------------------

if (
    $periodo !== "todos"
) {

    $whereDG .=
        " AND MONTH(dg.fecha) = ? ";

    $paramsDGBase[] =
        (int)$periodo;

    $typesDGBase .=
        "i";
}

//-----------------------------------------------------
// FECHA INICIAL
//-----------------------------------------------------

if (
    $fechaInicio !== ""
) {

    $whereDG .=
        " AND dg.fecha >= ? ";

    $paramsDGBase[] =
        $fechaInicio;

    $typesDGBase .=
        "s";
}

//-----------------------------------------------------
// FECHA FINAL
//-----------------------------------------------------

if (
    $fechaFin !== ""
) {

    $whereDG .=
        " AND dg.fecha <= ? ";

    $paramsDGBase[] =
        $fechaFin;

    $typesDGBase .=
        "s";
}

//=====================================================
// CONDICIONES DE TICKET VENTA
//=====================================================

$whereTV =
    "tv.id_user = ?
     AND LOWER(TRIM(tv.estado_venta))
     NOT IN (
        'cancelado',
        'cancelada',
        'anulado',
        'anulada'
     )";

$paramsTVBase =
    [$idUser];

$typesTVBase =
    "i";

//-----------------------------------------------------
// AÑO
//-----------------------------------------------------

if (
    $anio !== ""
) {

    $whereTV .=
        " AND YEAR(tv.fecha_venta) = ? ";

    $paramsTVBase[] =
        (int)$anio;

    $typesTVBase .=
        "i";
}

//-----------------------------------------------------
// PERÍODO
//-----------------------------------------------------

if (
    $periodo !== "todos"
) {

    $whereTV .=
        " AND MONTH(tv.fecha_venta) = ? ";

    $paramsTVBase[] =
        (int)$periodo;

    $typesTVBase .=
        "i";
}

//-----------------------------------------------------
// FECHA INICIAL
//-----------------------------------------------------

if (
    $fechaInicio !== ""
) {

    $whereTV .=
        " AND tv.fecha_venta >= ? ";

    $paramsTVBase[] =
        $fechaInicio;

    $typesTVBase .=
        "s";
}

//-----------------------------------------------------
// FECHA FINAL
//-----------------------------------------------------

if (
    $fechaFin !== ""
) {

    $whereTV .=
        " AND tv.fecha_venta <= ? ";

    $paramsTVBase[] =
        $fechaFin;

    $typesTVBase .=
        "s";
}

//=====================================================
// FUNCIÓN EJECUTAR CONSULTA PREPARADA
//=====================================================

function ejecutarConsulta(
    $conexion,
    $sql,
    $types = "",
    $params = []
) {

    $stmt =
        mysqli_prepare(
            $conexion,
            $sql
        );

    if (!$stmt) {

        throw new Exception(
            "Error preparando consulta: " .
                mysqli_error($conexion)
        );
    }

    if (
        !empty($types) &&
        !empty($params)
    ) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    if (
        !mysqli_stmt_execute(
            $stmt
        )
    ) {

        $error =
            mysqli_stmt_error(
                $stmt
            );

        mysqli_stmt_close(
            $stmt
        );

        throw new Exception(
            "Error ejecutando consulta: " .
                $error
        );
    }

    $resultado =
        mysqli_stmt_get_result(
            $stmt
        );

    if (
        $resultado === false
    ) {

        $error =
            mysqli_stmt_error(
                $stmt
            );

        mysqli_stmt_close(
            $stmt
        );

        throw new Exception(
            "No se pudo obtener el resultado: " .
                $error
        );
    }

    mysqli_stmt_close(
        $stmt
    );

    return $resultado;
}

//=====================================================
// INICIAR PROCESO
//=====================================================

try {

    //=================================================
    // INGRESOS POR VENTAS
    //=================================================

    $sqlVentas =
        "SELECT
            COALESCE(
                SUM(tv.total_venta),
                0
            ) AS total
         FROM ticket_ventas tv
         WHERE $whereTV";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sqlVentas,
            $typesTVBase,
            $paramsTVBase
        );

    $fila =
        mysqli_fetch_assoc(
            $resultado
        );

    $ingresosVentas =
        (float)(
            $fila["total"] ?? 0
        );

    //=================================================
    // ENTRADAS MANUALES
    //=================================================

    $sqlEntradas =
        "SELECT
            COALESCE(
                SUM(dg.monto_pago),
                0
            ) AS total
         FROM deposito_gasto dg
         WHERE
            $whereDG
            AND LOWER(TRIM(dg.tipo))
            IN (
                'entrada',
                'ingreso',
                'deposito',
                'depósito'
            )";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sqlEntradas,
            $typesDGBase,
            $paramsDGBase
        );

    $fila =
        mysqli_fetch_assoc(
            $resultado
        );

    $ingresosEntradas =
        (float)(
            $fila["total"] ?? 0
        );

    //=================================================
    // TOTAL INGRESOS
    //=================================================

    $ingresos =
        $ingresosVentas +
        $ingresosEntradas;

    //=================================================
    // GASTOS
    //=================================================

    $sqlGastos =
        "SELECT
            COALESCE(
                SUM(dg.monto_pago),
                0
            ) AS total
         FROM deposito_gasto dg
         WHERE
            $whereDG
            AND LOWER(TRIM(dg.tipo))
            IN (
                'salida',
                'gasto',
                'egreso'
            )";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sqlGastos,
            $typesDGBase,
            $paramsDGBase
        );

    $fila =
        mysqli_fetch_assoc(
            $resultado
        );

    $gastos =
        (float)(
            $fila["total"] ?? 0
        );

    //=================================================
    // UTILIDAD
    //=================================================

    $utilidad =
        $ingresos -
        $gastos;

    //=================================================
    // BALANCE BANCARIO
    //
    // IMPORTANTE:
    // No depende del filtro de fechas.
    // Es el saldo actual de las cuentas.
    //=================================================

    $sqlBalance =
        "SELECT
            COALESCE(
                SUM(balance),
                0
            ) AS balance,
            COUNT(*) AS cuentas
         FROM cuenta_banco
         WHERE
            id_user = ?
            AND Eliminado = 0";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sqlBalance,
            "i",
            [$idUser]
        );

    $fila =
        mysqli_fetch_assoc(
            $resultado
        );

    $balance =
        (float)(
            $fila["balance"] ?? 0
        );

    $cantidadCuentas =
        (int)(
            $fila["cuentas"] ?? 0
        );

    //=================================================
    // VARIACIONES
    //=================================================

    $variacionIngresos =
        calcularVariacion(
            $conexion,
            $idUser,
            $anio,
            $periodo,
            $fechaInicio,
            $fechaFin,
            "ingresos"
        );

    $variacionGastos =
        calcularVariacion(
            $conexion,
            $idUser,
            $anio,
            $periodo,
            $fechaInicio,
            $fechaFin,
            "gastos"
        );

    $variacionUtilidad =
        calcularVariacion(
            $conexion,
            $idUser,
            $anio,
            $periodo,
            $fechaInicio,
            $fechaFin,
            "utilidad"
        );

    //=================================================
    // GRÁFICO INGRESOS VS GASTOS
    //=================================================

    $graficoIngresosGastos =
        obtenerGraficoIngresosGastos(
            $conexion,
            $idUser,
            $anio,
            $periodo,
            $fechaInicio,
            $fechaFin
        );

    //=================================================
    // GRÁFICO GASTOS POR CATEGORÍA
    //=================================================

    $graficoGastosCategoria =
        obtenerGraficoGastosCategoria(
            $conexion,
            $idUser,
            $anio,
            $periodo,
            $fechaInicio,
            $fechaFin
        );

    //=================================================
    // EVOLUCIÓN FINANCIERA
    //=================================================

    $graficoEvolucion =
        obtenerEvolucionFinanciera(
            $conexion,
            $idUser,
            $anio,
            $periodo,
            $fechaInicio,
            $fechaFin,
            $mesesGrafico
        );

    //=================================================
    // RESPUESTA
    //=================================================

    responderJSON(
        [

            "estado" => true,

            "resumen" => [

                "ingresos" =>
                round(
                    $ingresos,
                    2
                ),

                "ingresos_ventas" =>
                round(
                    $ingresosVentas,
                    2
                ),

                "ingresos_entradas" =>
                round(
                    $ingresosEntradas,
                    2
                ),

                "gastos" =>
                round(
                    $gastos,
                    2
                ),

                "utilidad" =>
                round(
                    $utilidad,
                    2
                ),

                "utilidad_neta" =>
                round(
                    $utilidad,
                    2
                ),

                "balance" =>
                round(
                    $balance,
                    2
                ),

                "balance_bancario" =>
                round(
                    $balance,
                    2
                ),

                "cuentas" =>
                $cantidadCuentas,

                "cantidad_cuentas" =>
                $cantidadCuentas,

                "variacion_ingresos" =>
                round(
                    $variacionIngresos,
                    2
                ),

                "variacion_gastos" =>
                round(
                    $variacionGastos,
                    2
                ),

                "variacion_utilidad" =>
                round(
                    $variacionUtilidad,
                    2
                )

            ],

            "graficos" => [

                "ingresos_gastos" =>
                $graficoIngresosGastos,

                "gastos_categoria" =>
                $graficoGastosCategoria,

                "evolucion" =>
                $graficoEvolucion

            ]

        ]
    );
} catch (
    Throwable $e
) {

    responderJSON(
        [

            "estado" => false,

            "mensaje" =>
            $e->getMessage()

        ]
    );
}

//=====================================================
// CALCULAR VARIACIÓN
//=====================================================

function calcularVariacion(
    $conexion,
    $idUser,
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin,
    $tipo
) {

    //-------------------------------------------------
    // OBTENER PERÍODO ANTERIOR
    //-------------------------------------------------

    $periodoAnterior =
        obtenerPeriodoAnterior(
            $anio,
            $periodo,
            $fechaInicio,
            $fechaFin
        );

    $actual =
        obtenerTotalesPeriodo(
            $conexion,
            $idUser,
            $anio,
            $periodo,
            $fechaInicio,
            $fechaFin,
            $tipo
        );

    $anterior =
        obtenerTotalesPeriodo(
            $conexion,
            $idUser,
            $periodoAnterior["anio"],
            $periodoAnterior["periodo"],
            $periodoAnterior["fecha_inicio"],
            $periodoAnterior["fecha_fin"],
            $tipo
        );

    if (
        $anterior == 0
    ) {

        if (
            $actual > 0
        ) {

            return 100;
        }

        if (
            $actual < 0
        ) {

            return -100;
        }

        return 0;
    }

    return (
        (
            $actual -
            $anterior
        ) /
        abs($anterior)
    ) * 100;
}

//=====================================================
// OBTENER PERÍODO ANTERIOR
//=====================================================

function obtenerPeriodoAnterior(
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin
) {

    //-------------------------------------------------
    // FILTRO POR FECHAS PERSONALIZADAS
    //-------------------------------------------------

    if (
        $fechaInicio !== "" &&
        $fechaFin !== ""
    ) {

        $inicio =
            new DateTime(
                $fechaInicio
            );

        $fin =
            new DateTime(
                $fechaFin
            );

        $dias =
            (int)(
                $inicio->diff(
                    $fin
                )->days
            ) + 1;

        $finAnterior =
            clone $inicio;

        $finAnterior->modify(
            "-1 day"
        );

        $inicioAnterior =
            clone $finAnterior;

        $inicioAnterior->modify(
            "-" .
                ($dias - 1) .
                " days"
        );

        return [

            "anio" => "",

            "periodo" => "todos",

            "fecha_inicio" =>
            $inicioAnterior->format(
                "Y-m-d"
            ),

            "fecha_fin" =>
            $finAnterior->format(
                "Y-m-d"
            )

        ];
    }

    //-------------------------------------------------
    // FILTRO POR MES
    //-------------------------------------------------

    if (
        $anio !== "" &&
        $periodo !== "todos"
    ) {

        $fecha =
            new DateTime(
                $anio .
                    "-" .
                    $periodo .
                    "-01"
            );

        $fecha->modify(
            "-1 month"
        );

        return [

            "anio" =>
            $fecha->format(
                "Y"
            ),

            "periodo" =>
            $fecha->format(
                "m"
            ),

            "fecha_inicio" => "",

            "fecha_fin" => ""

        ];
    }

    //-------------------------------------------------
    // FILTRO POR AÑO
    //-------------------------------------------------

    if (
        $anio !== "" &&
        $periodo === "todos"
    ) {

        return [

            "anio" =>
            (string)(
                ((int)$anio) - 1
            ),

            "periodo" =>
            "todos",

            "fecha_inicio" => "",

            "fecha_fin" => ""

        ];
    }

    //-------------------------------------------------
    // SIN FILTRO
    //-------------------------------------------------

    return [

        "anio" => "",

        "periodo" => "todos",

        "fecha_inicio" => "",

        "fecha_fin" => ""

    ];
}

//=====================================================
// OBTENER TOTAL DE UN PERÍODO
//=====================================================

function obtenerTotalesPeriodo(
    $conexion,
    $idUser,
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin,
    $tipo
) {

    $whereDG =
        "dg.id_user = ?
         AND dg.Eliminado = 0";

    $whereTV =
        "tv.id_user = ?
         AND LOWER(TRIM(tv.estado_venta))
         NOT IN (
            'cancelado',
            'cancelada',
            'anulado',
            'anulada'
         )";

    $paramsDG =
        [$idUser];

    $typesDG =
        "i";

    $paramsTV =
        [$idUser];

    $typesTV =
        "i";

    //-------------------------------------------------
    // AÑO
    //-------------------------------------------------

    if (
        $anio !== ""
    ) {

        $whereDG .=
            " AND YEAR(dg.fecha) = ? ";

        $paramsDG[] =
            (int)$anio;

        $typesDG .=
            "i";

        $whereTV .=
            " AND YEAR(tv.fecha_venta) = ? ";

        $paramsTV[] =
            (int)$anio;

        $typesTV .=
            "i";
    }

    //-------------------------------------------------
    // MES
    //-------------------------------------------------

    if (
        $periodo !== "todos"
    ) {

        $whereDG .=
            " AND MONTH(dg.fecha) = ? ";

        $paramsDG[] =
            (int)$periodo;

        $typesDG .=
            "i";

        $whereTV .=
            " AND MONTH(tv.fecha_venta) = ? ";

        $paramsTV[] =
            (int)$periodo;

        $typesTV .=
            "i";
    }

    //-------------------------------------------------
    // FECHA INICIO
    //-------------------------------------------------

    if (
        $fechaInicio !== ""
    ) {

        $whereDG .=
            " AND dg.fecha >= ? ";

        $paramsDG[] =
            $fechaInicio;

        $typesDG .=
            "s";

        $whereTV .=
            " AND tv.fecha_venta >= ? ";

        $paramsTV[] =
            $fechaInicio;

        $typesTV .=
            "s";
    }

    //-------------------------------------------------
    // FECHA FIN
    //-------------------------------------------------

    if (
        $fechaFin !== ""
    ) {

        $whereDG .=
            " AND dg.fecha <= ? ";

        $paramsDG[] =
            $fechaFin;

        $typesDG .=
            "s";

        $whereTV .=
            " AND tv.fecha_venta <= ? ";

        $paramsTV[] =
            $fechaFin;

        $typesTV .=
            "s";
    }

    //-------------------------------------------------
    // INGRESOS
    //-------------------------------------------------

    if (
        $tipo === "ingresos" ||
        $tipo === "utilidad"
    ) {

        $sql =
            "SELECT
                (
                    COALESCE(
                        (
                            SELECT SUM(
                                tv.total_venta
                            )
                            FROM ticket_ventas tv
                            WHERE $whereTV
                        ),
                        0
                    )
                    +
                    COALESCE(
                        (
                            SELECT SUM(
                                dg.monto_pago
                            )
                            FROM deposito_gasto dg
                            WHERE
                                $whereDG
                                AND LOWER(
                                    TRIM(dg.tipo)
                                ) IN (
                                    'entrada',
                                    'ingreso',
                                    'deposito',
                                    'depósito'
                                )
                        ),
                        0
                    )
                ) AS total";

        $params =
            array_merge(
                $paramsTV,
                $paramsDG
            );

        $types =
            $typesTV .
            $typesDG;

        $resultado =
            ejecutarConsulta(
                $conexion,
                $sql,
                $types,
                $params
            );

        $fila =
            mysqli_fetch_assoc(
                $resultado
            );

        $ingresos =
            (float)(
                $fila["total"] ?? 0
            );

        if (
            $tipo === "ingresos"
        ) {

            return $ingresos;
        }

        //-------------------------------------------------
        // PARA UTILIDAD TAMBIÉN OBTENER GASTOS
        //-------------------------------------------------

        $sqlGastos =
            "SELECT
                COALESCE(
                    SUM(dg.monto_pago),
                    0
                ) AS total
             FROM deposito_gasto dg
             WHERE
                $whereDG
                AND LOWER(TRIM(dg.tipo))
                IN (
                    'salida',
                    'gasto',
                    'egreso'
                )";

        $resultado =
            ejecutarConsulta(
                $conexion,
                $sqlGastos,
                $typesDG,
                $paramsDG
            );

        $fila =
            mysqli_fetch_assoc(
                $resultado
            );

        $gastos =
            (float)(
                $fila["total"] ?? 0
            );

        return (
            $ingresos -
            $gastos
        );
    }

    //-------------------------------------------------
    // GASTOS
    //-------------------------------------------------

    $sql =
        "SELECT
            COALESCE(
                SUM(dg.monto_pago),
                0
            ) AS total
         FROM deposito_gasto dg
         WHERE
            $whereDG
            AND LOWER(TRIM(dg.tipo))
            IN (
                'salida',
                'gasto',
                'egreso'
            )";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sql,
            $typesDG,
            $paramsDG
        );

    $fila =
        mysqli_fetch_assoc(
            $resultado
        );

    return (float)(
        $fila["total"] ?? 0
    );
}

//=====================================================
// GRÁFICO INGRESOS VS GASTOS
//=====================================================

function obtenerGraficoIngresosGastos(
    $conexion,
    $idUser,
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin
) {

    //-------------------------------------------------
    // SI ES UN MES ESPECÍFICO
    //-------------------------------------------------

    if (
        $anio !== "" &&
        $periodo !== "todos"
    ) {

        $fecha =
            $anio .
            "-" .
            $periodo .
            "-01";

        $inicio =
            new DateTime(
                $fecha
            );

        $fin =
            clone $inicio;

        $fin->modify(
            "last day of this month"
        );

        return obtenerGraficoPorRango(
            $conexion,
            $idUser,
            $inicio->format("Y-m-d"),
            $fin->format("Y-m-d")
        );
    }

    //-------------------------------------------------
    // AÑO COMPLETO
    //-------------------------------------------------

    if (
        $anio !== ""
    ) {

        return obtenerGraficoPorRango(
            $conexion,
            $idUser,
            $anio . "-01-01",
            $anio . "-12-31"
        );
    }

    //-------------------------------------------------
    // FECHAS PERSONALIZADAS
    //-------------------------------------------------

    if (
        $fechaInicio !== "" &&
        $fechaFin !== ""
    ) {

        return obtenerGraficoPorRango(
            $conexion,
            $idUser,
            $fechaInicio,
            $fechaFin
        );
    }

    //-------------------------------------------------
    // TODOS LOS AÑOS
    //-------------------------------------------------

    $sql =
        "SELECT
            DATE_FORMAT(
                fecha_movimiento,
                '%Y-%m'
            ) AS periodo,

            DATE_FORMAT(
                fecha_movimiento,
                '%b %Y'
            ) AS etiqueta,

            SUM(
                ingreso
            ) AS ingresos,

            SUM(
                gasto
            ) AS gastos

         FROM (

            SELECT
                fecha_venta AS fecha_movimiento,
                total_venta AS ingreso,
                0 AS gasto
            FROM ticket_ventas
            WHERE
                id_user = ?
                AND LOWER(
                    TRIM(estado_venta)
                ) NOT IN (
                    'cancelado',
                    'cancelada',
                    'anulado',
                    'anulada'
                )

            UNION ALL

            SELECT
                fecha,
                CASE
                    WHEN LOWER(TRIM(tipo))
                    IN (
                        'entrada',
                        'ingreso',
                        'deposito',
                        'depósito'
                    )
                    THEN monto_pago
                    ELSE 0
                END,

                CASE
                    WHEN LOWER(TRIM(tipo))
                    IN (
                        'salida',
                        'gasto',
                        'egreso'
                    )
                    THEN monto_pago
                    ELSE 0
                END

            FROM deposito_gasto
            WHERE
                id_user = ?
                AND Eliminado = 0

         ) movimientos

         GROUP BY
            DATE_FORMAT(
                fecha_movimiento,
                '%Y-%m'
            )

         ORDER BY
            periodo ASC";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sql,
            "ii",
            [
                $idUser,
                $idUser
            ]
        );

    $etiquetas = [];
    $ingresos = [];
    $gastos = [];

    while (
        $fila =
        mysqli_fetch_assoc(
            $resultado
        )
    ) {

        $etiquetas[] =
            formatearEtiquetaMes(
                $fila["periodo"]
            );

        $ingresos[] =
            round(
                (float)$fila["ingresos"],
                2
            );

        $gastos[] =
            round(
                (float)$fila["gastos"],
                2
            );
    }

    return [

        "etiquetas" =>
        $etiquetas,

        "ingresos" =>
        $ingresos,

        "gastos" =>
        $gastos

    ];
}

//=====================================================
// GRÁFICO POR RANGO
//=====================================================

function obtenerGraficoPorRango(
    $conexion,
    $idUser,
    $fechaInicio,
    $fechaFin
) {

    $sql =
        "SELECT
            DATE_FORMAT(
                fecha_movimiento,
                '%Y-%m'
            ) AS periodo,

            SUM(
                ingreso
            ) AS ingresos,

            SUM(
                gasto
            ) AS gastos

         FROM (

            SELECT
                fecha_venta AS fecha_movimiento,

                total_venta AS ingreso,

                0 AS gasto

            FROM ticket_ventas

            WHERE
                id_user = ?
                AND fecha_venta >= ?
                AND fecha_venta <= ?
                AND LOWER(
                    TRIM(estado_venta)
                ) NOT IN (
                    'cancelado',
                    'cancelada',
                    'anulado',
                    'anulada'
                )

            UNION ALL

            SELECT
                fecha AS fecha_movimiento,

                CASE
                    WHEN LOWER(TRIM(tipo))
                    IN (
                        'entrada',
                        'ingreso',
                        'deposito',
                        'depósito'
                    )
                    THEN monto_pago
                    ELSE 0
                END AS ingreso,

                CASE
                    WHEN LOWER(TRIM(tipo))
                    IN (
                        'salida',
                        'gasto',
                        'egreso'
                    )
                    THEN monto_pago
                    ELSE 0
                END AS gasto

            FROM deposito_gasto

            WHERE
                id_user = ?
                AND Eliminado = 0
                AND fecha >= ?
                AND fecha <= ?

         ) movimientos

         GROUP BY
            DATE_FORMAT(
                fecha_movimiento,
                '%Y-%m'
            )

         ORDER BY
            periodo ASC";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sql,
            "ississ",
            [
                $idUser,
                $fechaInicio,
                $fechaFin,
                $idUser,
                $fechaInicio,
                $fechaFin
            ]
        );

    $etiquetas = [];
    $ingresos = [];
    $gastos = [];

    while (
        $fila =
        mysqli_fetch_assoc(
            $resultado
        )
    ) {

        $etiquetas[] =
            formatearEtiquetaMes(
                $fila["periodo"]
            );

        $ingresos[] =
            round(
                (float)$fila["ingresos"],
                2
            );

        $gastos[] =
            round(
                (float)$fila["gastos"],
                2
            );
    }

    return [

        "etiquetas" =>
        $etiquetas,

        "ingresos" =>
        $ingresos,

        "gastos" =>
        $gastos

    ];
}

//=====================================================
// GASTOS POR CATEGORÍA
//=====================================================

function obtenerGraficoGastosCategoria(
    $conexion,
    $idUser,
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin
) {

    $where =
        "dg.id_user = ?
         AND dg.Eliminado = 0
         AND LOWER(TRIM(dg.tipo))
         IN (
            'salida',
            'gasto',
            'egreso'
         )";

    $params =
        [$idUser];

    $types =
        "i";

    if (
        $anio !== ""
    ) {

        $where .=
            " AND YEAR(dg.fecha) = ? ";

        $params[] =
            (int)$anio;

        $types .=
            "i";
    }

    if (
        $periodo !== "todos"
    ) {

        $where .=
            " AND MONTH(dg.fecha) = ? ";

        $params[] =
            (int)$periodo;

        $types .=
            "i";
    }

    if (
        $fechaInicio !== ""
    ) {

        $where .=
            " AND dg.fecha >= ? ";

        $params[] =
            $fechaInicio;

        $types .=
            "s";
    }

    if (
        $fechaFin !== ""
    ) {

        $where .=
            " AND dg.fecha <= ? ";

        $params[] =
            $fechaFin;

        $types .=
            "s";
    }

    $sql =
        "SELECT

            COALESCE(
                c.nombre,
                'Sin categoría'
            ) AS categoria,

            COALESCE(
                SUM(dg.monto_pago),
                0
            ) AS total

         FROM deposito_gasto dg

         LEFT JOIN categorias c
            ON c.id_categorias =
               dg.id_categoria

         WHERE
            $where

         GROUP BY
            dg.id_categoria,
            c.nombre

         ORDER BY
            total DESC";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sql,
            $types,
            $params
        );

    $etiquetas = [];
    $valores = [];

    while (
        $fila =
        mysqli_fetch_assoc(
            $resultado
        )
    ) {

        $etiquetas[] =
            $fila["categoria"];

        $valores[] =
            round(
                (float)$fila["total"],
                2
            );
    }

    return [

        "etiquetas" =>
        $etiquetas,

        "valores" =>
        $valores

    ];
}

//=====================================================
// EVOLUCIÓN FINANCIERA
//=====================================================

function obtenerEvolucionFinanciera(
    $conexion,
    $idUser,
    $anio,
    $periodo,
    $fechaInicio,
    $fechaFin,
    $cantidadMeses
) {

    //-------------------------------------------------
    // CASO: MES ESPECÍFICO
    //-------------------------------------------------

    if (
        $anio !== "" &&
        $periodo !== "todos"
    ) {

        $inicio =
            new DateTime(
                $anio .
                    "-" .
                    $periodo .
                    "-01"
            );

        $fin =
            clone $inicio;

        $fin->modify(
            "last day of this month"
        );

        return obtenerEvolucionRango(
            $conexion,
            $idUser,
            $inicio,
            $fin
        );
    }

    //-------------------------------------------------
    // CASO: AÑO
    //-------------------------------------------------

    if (
        $anio !== ""
    ) {

        $inicio =
            new DateTime(
                $anio .
                    "-01-01"
            );

        $fin =
            new DateTime(
                $anio .
                    "-12-31"
            );

        return obtenerEvolucionRango(
            $conexion,
            $idUser,
            $inicio,
            $fin
        );
    }

    //-------------------------------------------------
    // FECHAS PERSONALIZADAS
    //-------------------------------------------------

    if (
        $fechaInicio !== "" &&
        $fechaFin !== ""
    ) {

        $inicio =
            new DateTime(
                $fechaInicio
            );

        $fin =
            new DateTime(
                $fechaFin
            );

        return obtenerEvolucionRango(
            $conexion,
            $idUser,
            $inicio,
            $fin
        );
    }

    //-------------------------------------------------
    // SIN FILTRO:
    // ÚLTIMOS N MESES
    //-------------------------------------------------

    $fin =
        new DateTime();

    $inicio =
        new DateTime();

    $inicio->modify(
        "-" .
            ($cantidadMeses - 1) .
            " months"
    );

    $inicio->modify(
        "first day of this month"
    );

    $fin->modify(
        "last day of this month"
    );

    return obtenerEvolucionRango(
        $conexion,
        $idUser,
        $inicio,
        $fin
    );
}

//=====================================================
// EVOLUCIÓN POR RANGO
//=====================================================

function obtenerEvolucionRango(
    $conexion,
    $idUser,
    DateTime $inicio,
    DateTime $fin
) {

    $fechaInicio =
        $inicio->format(
            "Y-m-d"
        );

    $fechaFin =
        $fin->format(
            "Y-m-d"
        );

    $sql =
        "SELECT

            DATE_FORMAT(
                fecha_movimiento,
                '%Y-%m'
            ) AS periodo,

            SUM(
                ingreso
            ) AS ingresos,

            SUM(
                gasto
            ) AS gastos

         FROM (

            SELECT

                fecha_venta
                    AS fecha_movimiento,

                total_venta
                    AS ingreso,

                0
                    AS gasto

            FROM ticket_ventas

            WHERE
                id_user = ?

                AND fecha_venta >= ?

                AND fecha_venta <= ?

                AND LOWER(
                    TRIM(estado_venta)
                ) NOT IN (
                    'cancelado',
                    'cancelada',
                    'anulado',
                    'anulada'
                )

            UNION ALL

            SELECT

                fecha
                    AS fecha_movimiento,

                CASE

                    WHEN LOWER(
                        TRIM(tipo)
                    )
                    IN (
                        'entrada',
                        'ingreso',
                        'deposito',
                        'depósito'
                    )

                    THEN monto_pago

                    ELSE 0

                END
                    AS ingreso,

                CASE

                    WHEN LOWER(
                        TRIM(tipo)
                    )
                    IN (
                        'salida',
                        'gasto',
                        'egreso'
                    )

                    THEN monto_pago

                    ELSE 0

                END
                    AS gasto

            FROM deposito_gasto

            WHERE

                id_user = ?

                AND Eliminado = 0

                AND fecha >= ?

                AND fecha <= ?

         ) movimientos

         GROUP BY
            DATE_FORMAT(
                fecha_movimiento,
                '%Y-%m'
            )

         ORDER BY
            periodo ASC";

    $resultado =
        ejecutarConsulta(
            $conexion,
            $sql,
            "ississ",
            [
                $idUser,
                $fechaInicio,
                $fechaFin,
                $idUser,
                $fechaInicio,
                $fechaFin
            ]
        );

    //-------------------------------------------------
    // GUARDAR RESULTADOS
    //-------------------------------------------------

    $datos =
        [];

    while (
        $fila =
        mysqli_fetch_assoc(
            $resultado
        )
    ) {

        $datos[$fila["periodo"]] = [

            "ingresos" =>
            (float)$fila["ingresos"],

            "gastos" =>
            (float)$fila["gastos"]

        ];
    }

    //-------------------------------------------------
    // GENERAR TODOS LOS MESES DEL RANGO
    //
    // Esto evita que el gráfico desaparezca
    // cuando un mes no tiene movimientos.
    //-------------------------------------------------

    $etiquetas = [];
    $ingresos = [];
    $gastos = [];
    $utilidad = [];

    $cursor =
        clone $inicio;

    $cursor->modify(
        "first day of this month"
    );

    $limite =
        clone $fin;

    $limite->modify(
        "first day of next month"
    );

    while (
        $cursor < $limite
    ) {

        $clave =
            $cursor->format(
                "Y-m"
            );

        $valorIngresos =
            $datos[$clave]["ingresos"] ?? 0;

        $valorGastos =
            $datos[$clave]["gastos"] ?? 0;

        $valorUtilidad =
            $valorIngresos -
            $valorGastos;

        $etiquetas[] =
            formatearEtiquetaMes(
                $clave
            );

        $ingresos[] =
            round(
                $valorIngresos,
                2
            );

        $gastos[] =
            round(
                $valorGastos,
                2
            );

        $utilidad[] =
            round(
                $valorUtilidad,
                2
            );

        $cursor->modify(
            "+1 month"
        );
    }

    return [

        "etiquetas" =>
        $etiquetas,

        "ingresos" =>
        $ingresos,

        "gastos" =>
        $gastos,

        "utilidad" =>
        $utilidad

    ];
}

//=====================================================
// ETIQUETA MES
//=====================================================

function formatearEtiquetaMes(
    $periodo
) {

    $partes =
        explode(
            "-",
            $periodo
        );

    if (
        count($partes) !== 2
    ) {

        return $periodo;
    }

    $mes =
        (int)$partes[1];

    $anio =
        $partes[0];

    $meses = [

        1 => "Ene",

        2 => "Feb",

        3 => "Mar",

        4 => "Abr",

        5 => "May",

        6 => "Jun",

        7 => "Jul",

        8 => "Ago",

        9 => "Sep",

        10 => "Oct",

        11 => "Nov",

        12 => "Dic"

    ];

    return (
        ($meses[$mes] ?? $periodo) .
        " " .
        $anio
    );
}

//=====================================================
// FIN
//=====================================================