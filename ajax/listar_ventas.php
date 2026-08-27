<?php
//=========================================================
// CoDevPro Technology
// ajax/listar_ventas.php
// Módulo: Gestión de Ventas
// Sistema: Inventa
//=========================================================

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;


if (!$idUser) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión inválida"
    ]);

    exit;
}


/*=========================================================
=            FILTROS
=========================================================*/

$buscar = trim(
    $_POST["buscar"] ?? ""
);

$estadoVenta = trim(
    $_POST["estadoVenta"] ?? ""
);

$estadoEnvio = trim(
    $_POST["estadoEnvio"] ?? ""
);

$metodoPago = trim(
    $_POST["metodoPago"] ?? ""
);

$empleado = trim(
    $_POST["empleado"] ?? ""
);

$fechaInicio = trim(
    $_POST["fechaInicio"] ?? ""
);

$fechaFin = trim(
    $_POST["fechaFin"] ?? ""
);


$pagina = max(
    1,
    (int)($_POST["pagina"] ?? 1)
);


$limiteSolicitado = (int)(
    $_POST["limite"] ?? 10
);


$limitesPermitidos = [
    10,
    25,
    50,
    100
];


if (
    !in_array(
        $limiteSolicitado,
        $limitesPermitidos,
        true
    )
) {

    $limiteSolicitado = 10;
}


$limite = $limiteSolicitado;


$offset =
    ($pagina - 1) * $limite;


/*=========================================================
=            VALIDAR FECHAS
=========================================================*/

if (
    $fechaInicio !== "" &&
    $fechaFin !== "" &&
    $fechaInicio > $fechaFin
) {

    echo json_encode([
        "estado" => false,
        "mensaje" =>
        "La Fecha Inicio no puede ser mayor que la Fecha Fin."
    ]);

    exit;
}


/*=========================================================
=            WHERE
=========================================================*/

$where = "

    tv.id_user = ?

";


$params = [
    $idUser
];


$types = "i";


/*=========================================================
=            BUSCAR
=========================================================*/

if ($buscar !== "") {

    $where .= "

        AND (

            c.nombre LIKE ?

            OR CONCAT(
                COALESCE(c.nombre,''),
                ' ',
                COALESCE(c.apellido,'')
            ) LIKE ?

            OR CONCAT(
                tv.serie,
                '-',
                tv.numero
            ) LIKE ?

            OR tv.numero LIKE ?

        )

    ";


    $buscarLike =
        "%" . $buscar . "%";


    $params[] =
        $buscarLike;

    $params[] =
        $buscarLike;

    $params[] =
        $buscarLike;

    $params[] =
        $buscarLike;


    $types .= "ssss";
}


/*=========================================================
=            ESTADO VENTA
=========================================================*/

if ($estadoVenta !== "") {

    $where .= "

        AND tv.estado_venta = ?

    ";


    $params[] =
        $estadoVenta;


    $types .= "s";
}


/*=========================================================
=            ESTADO ENVÍO
=========================================================*/

if ($estadoEnvio !== "") {

    $where .= "

        AND tv.estado_envio = ?

    ";


    $params[] =
        $estadoEnvio;


    $types .= "s";
}


/*=========================================================
=            MÉTODO PAGO
=========================================================*/

if ($metodoPago !== "") {

    $where .= "

        AND tv.id_metodo_pago = ?

    ";


    $params[] =
        (int)$metodoPago;


    $types .= "i";
}


/*=========================================================
=            EMPLEADO
=========================================================*/

if ($empleado !== "") {

    $where .= "

        AND tv.id_empleado = ?

    ";


    $params[] =
        (int)$empleado;


    $types .= "i";
}


/*=========================================================
=            FECHA INICIO
=========================================================*/

if ($fechaInicio !== "") {

    $where .= "

        AND tv.fecha_venta >= ?

    ";


    $params[] =
        $fechaInicio . " 00:00:00";


    $types .= "s";
}


/*=========================================================
=            FECHA FIN
=========================================================*/

if ($fechaFin !== "") {

    $where .= "

        AND tv.fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)

    ";


    $params[] =
        $fechaFin;


    $types .= "s";
}


try {


    /*=====================================================
    =            TOTAL REGISTROS
    =====================================================*/

    $sqlTotal = "

        SELECT
            COUNT(*) AS total

        FROM ticket_ventas tv

        LEFT JOIN clientes c
            ON c.idCliente = tv.idCliente

        WHERE {$where}

    ";


    $stmt =
        mysqli_prepare(
            $conexion,
            $sqlTotal
        );


    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );


    mysqli_stmt_execute(
        $stmt
    );


    $resultado =
        mysqli_stmt_get_result(
            $stmt
        );


    $filaTotal =
        mysqli_fetch_assoc(
            $resultado
        );


    $totalRegistros =
        (int)($filaTotal["total"] ?? 0);


    mysqli_stmt_close(
        $stmt
    );


    /*=====================================================
    =            LISTADO
    =====================================================*/

    $sql = "

        SELECT

            tv.id_ticket_ventas,

            tv.tipo_comprobante,

            tv.serie,

            tv.numero,

            tv.total_venta,

            tv.estado_venta,

            tv.estado_envio,

            tv.fecha_venta,

            mp.nombre AS metodo_pago,

            c.nombre AS cliente,

            CONCAT(
                COALESCE(e.nombre,''),
                ' ',
                COALESCE(e.apellido,'')
            ) AS empleado

        FROM ticket_ventas tv

        LEFT JOIN clientes c
            ON c.idCliente = tv.idCliente

        LEFT JOIN metodo_pago mp
            ON mp.id_metodo_pago =
                tv.id_metodo_pago

        LEFT JOIN empleados e
            ON e.id_empleado =
                tv.id_empleado

        WHERE {$where}

        ORDER BY
            tv.fecha_venta DESC,
            tv.id_ticket_ventas DESC

        LIMIT {$offset}, {$limite}

    ";


    $stmt =
        mysqli_prepare(
            $conexion,
            $sql
        );


    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );


    mysqli_stmt_execute(
        $stmt
    );


    $resultado =
        mysqli_stmt_get_result(
            $stmt
        );


    $tabla = "";

    $contador =
        $offset + 1;


    while (
        $fila =
        mysqli_fetch_assoc(
            $resultado
        )
    ) {


        /*=============================================
        ESTADO VENTA
        =============================================*/

        $badgeVenta =
            "secondary";


        switch ($fila["estado_venta"]) {

            case "PAGADO":

                $badgeVenta =
                    "success";

                break;


            case "PENDIENTE":

                $badgeVenta =
                    "warning";

                break;


            case "ANULADO":

                $badgeVenta =
                    "danger";

                break;
        }


        /*=============================================
        ESTADO ENVÍO
        =============================================*/

        $badgeEnvio =
            "secondary";


        switch ($fila["estado_envio"]) {

            case "PENDIENTE":

                $badgeEnvio =
                    "warning";

                break;


            case "CONFIRMADO":

                $badgeEnvio =
                    "info";

                break;


            case "PREPARANDO":

                $badgeEnvio =
                    "primary";

                break;


            case "ENVIADO":

                $badgeEnvio =
                    "dark";

                break;


            case "ENTREGADO":

                $badgeEnvio =
                    "success";

                break;


            case "CANCELADO":

                $badgeEnvio =
                    "danger";

                break;
        }


        /*=============================================
        COMPROBANTE
        =============================================*/

        $comprobante =

            htmlspecialchars(
                $fila["tipo_comprobante"] ??
                    ""
            )

            .

            "<br><small>"

            .

            htmlspecialchars(
                $fila["serie"] ??
                    ""
            )

            .

            "-"

            .

            str_pad(
                $fila["numero"],
                8,
                "0",
                STR_PAD_LEFT
            )

            .

            "</small>";


        $cliente =
            trim(
                $fila["cliente"] ??
                    ""
            );


        if ($cliente === "") {

            $cliente =
                "Cliente eliminado";
        }


        $empleadoNombre =
            trim(
                $fila["empleado"] ??
                    ""
            );


        if (
            $empleadoNombre === ""
        ) {

            $empleadoNombre =
                "Sin empleado";
        }


        $tabla .= '

        <tr>

            <td>
                ' . $contador . '
            </td>

            <td>
                ' . $comprobante . '
            </td>

            <td>
                ' .
            htmlspecialchars(
                $cliente
            )
            . '
            </td>

            <td>
                ' .
            date(
                "d/m/Y H:i",
                strtotime(
                    $fila["fecha_venta"]
                )
            )
            . '
            </td>

            <td>
                ' .
            htmlspecialchars(
                $fila["metodo_pago"]
                    ??
                    "Sin método"
            )
            . '
            </td>

            <td>
                ' .
            htmlspecialchars(
                $empleadoNombre
            )
            . '
            </td>

            <td>

                <span class="badge bg-' .
            $badgeVenta .
            '">

                    ' .
            htmlspecialchars(
                $fila["estado_venta"]
                    ?? ""
            )
            . '

                </span>

            </td>

            <td>

                <span class="badge bg-' .
            $badgeEnvio .
            '">

                    ' .
            htmlspecialchars(
                $fila["estado_envio"]
                    ?? ""
            )
            . '

                </span>

            </td>

            <td class="fw-bold text-success">

                S/ ' .
            number_format(
                (float)$fila["total_venta"],
                2
            )
            . '

            </td>

            <td class="text-center">

                <div class="btn-group btn-group-sm">

                    <button
                        class="btn btn-outline-primary btnVerVenta"
                        data-id="' .
            (int)$fila["id_ticket_ventas"]
            . '"
                        title="Ver detalle">

                        <i class="bi bi-eye-fill"></i>

                    </button>


                    <button
                        class="btn btn-outline-success btnDescargarComprobante"
                        data-id="' .
            (int)$fila["id_ticket_ventas"]
            . '"
                        title="Descargar comprobante">

                        <i class="bi bi-printer-fill"></i>

                    </button>

                </div>

            </td>

        </tr>

        ';


        $contador++;
    }


    mysqli_stmt_close(
        $stmt
    );


    /*=====================================================
    =            SIN RESULTADOS
    =====================================================*/

    if ($tabla === "") {

        $tabla = '

        <tr>

            <td
                colspan="10"
                class="text-center py-5"
            >

                <i
                    class="bi bi-inbox fs-1 text-muted"
                ></i>

                <p class="mt-3 mb-0">

                    No se encontraron ventas.

                </p>

            </td>

        </tr>

        ';
    }


    /*=====================================================
    =            PAGINACIÓN
    =====================================================*/

    $totalPaginas =
        max(
            1,
            (int)ceil(
                $totalRegistros /
                    $limite
            )
        );


    $paginacion = '

        <nav>

            <ul class="pagination justify-content-end mb-0">

    ';


    /*=====================================================
    ANTERIOR
    =====================================================*/

    $disabledPrev =
        ($pagina <= 1)
        ? "disabled"
        : "";


    $paginaAnterior =
        max(
            1,
            $pagina - 1
        );


    $paginacion .= '

        <li class="page-item ' .
        $disabledPrev .
        '">

            <a
                href="#"
                class="page-link pagina-ventas"
                data-pagina="' .
        $paginaAnterior .
        '"
            >

                Anterior

            </a>

        </li>

    ';


    /*=====================================================
    NÚMEROS
    =====================================================*/

    $inicio =
        max(
            1,
            $pagina - 2
        );


    $fin =
        min(
            $totalPaginas,
            $pagina + 2
        );


    for (
        $i = $inicio;
        $i <= $fin;
        $i++
    ) {

        $active =
            ($i == $pagina)
            ? "active"
            : "";


        $paginacion .= '

            <li class="page-item ' .
            $active .
            '">

                <a
                    href="#"
                    class="page-link pagina-ventas"
                    data-pagina="' .
            $i .
            '"
                >

                    ' .
            $i .
            '

                </a>

            </li>

        ';
    }


    /*=====================================================
    SIGUIENTE
    =====================================================*/

    $disabledNext =
        ($pagina >= $totalPaginas)
        ? "disabled"
        : "";


    $paginaSiguiente =
        min(
            $totalPaginas,
            $pagina + 1
        );


    $paginacion .= '

        <li class="page-item ' .
        $disabledNext .
        '">

            <a
                href="#"
                class="page-link pagina-ventas"
                data-pagina="' .
        $paginaSiguiente .
        '"
            >

                Siguiente

            </a>

        </li>

    ';


    $paginacion .= '

            </ul>

        </nav>

    ';


    /*=====================================================
    =            INFORMACIÓN
    =====================================================*/

    $inicioRegistros =
        $totalRegistros > 0
        ? $offset + 1
        : 0;


    $finRegistros =
        min(
            $offset + $limite,
            $totalRegistros
        );


    $info =

        "Mostrando " .
        $inicioRegistros .
        " a " .
        $finRegistros .
        " de " .
        $totalRegistros .
        " registros";


    echo json_encode([

        "estado" =>
        true,

        "tabla" =>
        $tabla,

        "paginacion" =>
        $paginacion,

        "info" =>
        $info,

        "totalRegistros" =>
        $totalRegistros

    ]);
} catch (Throwable $e) {

    echo json_encode([

        "estado" =>
        false,

        "mensaje" =>
        $e->getMessage()

    ]);
}
