<?php
//=====================================================
// CoDevPro Technology
// ajax/adm_lista_metodos.php
// Parte 1.1
//=====================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

/*=====================================================
=            VALIDAR SESIÓN
=====================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "La sesión ha expirado."
    ]);

    exit;
}

$idUser = (int) $_SESSION["idUser"];

/*=====================================================
=            RECIBIR PARÁMETROS
=====================================================*/

$pagina = isset($_POST["pagina"]) ? (int) $_POST["pagina"] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$registros = isset($_POST["registros"])
    ? (int) $_POST["registros"]
    : 5;

if ($registros !== 5) {
    $registros = 5;
}

$buscar = trim($_POST["buscar"] ?? "");

$estado = trim($_POST["estado"] ?? "");

$orden = trim($_POST["orden"] ?? "nombre_asc");

$fechaInicio = "";
$fechaFin    = "";

if (!empty($_POST["fecha"])) {

    $rango = explode(" a ", $_POST["fecha"]);

    if (count($rango) == 2) {

        $fechaInicio = trim($rango[0]);

        $fechaFin = trim($rango[1]);
    }
}

/*=====================================================
=            ARMAR WHERE
=====================================================*/

$where = [];

$parametros = [];

$tipos = "";

$where[] = "mp.id_user=?";

$parametros[] = $idUser;

$tipos .= "i";
/*=====================================================
=            SOLO MÉTODOS ACTIVOS
=====================================================*/

$where[] = "mp.Eliminado=0";
/*=====================================================
BUSCADOR
=====================================================*/

if ($buscar != "") {

    $where[] = "mp.nombre LIKE ?";

    $parametros[] = "%{$buscar}%";

    $tipos .= "s";
}
/*=====================================================
FECHA
=====================================================*/

if ($fechaInicio != "" && $fechaFin != "") {

    $where[] = "tv.fecha_venta >= ? AND tv.fecha_venta < DATE_ADD(?, INTERVAL 1 DAY)";

    $parametros[] = $fechaInicio;

    $parametros[] = $fechaFin;

    $tipos .= "ss";
}

$whereSQL = implode(" AND ", $where);

/*=====================================================
ORDENAMIENTO
=====================================================*/

switch ($orden) {

    case "nombre_desc":

        $orderSQL = "mp.nombre DESC";

        break;

    case "ventas_desc":

        $orderSQL = "ventas DESC";

        break;

    case "ventas_asc":

        $orderSQL = "ventas ASC";

        break;

    default:

        $orderSQL = "mp.nombre ASC";
}

/*=====================================================
PAGINACIÓN
=====================================================*/

$inicio = ($pagina - 1) * $registros;

/*=====================================================
TOTAL REGISTROS
=====================================================*/

$sqlTotal = "

SELECT
COUNT(DISTINCT mp.id_metodo_pago)

FROM metodo_pago mp

LEFT JOIN ticket_ventas tv
ON tv.id_metodo_pago = mp.id_metodo_pago

WHERE {$whereSQL}

";

$stmt = mysqli_prepare($conexion, $sqlTotal);

mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result($stmt, $totalRegistros);

mysqli_stmt_fetch($stmt);

mysqli_stmt_close($stmt);

$totalPaginas = max(1, ceil($totalRegistros / $registros));

/*=====================================================
CONSULTA PRINCIPAL
=====================================================*/

$sql = "

SELECT

mp.id_metodo_pago,

mp.nombre,

mp.Eliminado,

/*=====================================================
VENTAS
Se mantienen todas las ventas según los filtros.
=====================================================*/

COUNT(tv.id_ticket_ventas) AS ventas,

/*=====================================================
TOTAL VENDIDO
Solo considerar pedidos ENTREGADOS.
=====================================================*/

COALESCE(
    SUM(
        CASE
            WHEN tv.estado_envio = 'ENTREGADO'
            THEN tv.total_venta
            ELSE 0
        END
    ),
    0
) AS total_vendido,

/*=====================================================
TICKET PROMEDIO
Solo considerar pedidos ENTREGADOS.
=====================================================*/

COALESCE(
    AVG(
        CASE
            WHEN tv.estado_envio = 'ENTREGADO'
            THEN tv.total_venta
            ELSE NULL
        END
    ),
    0
) AS ticket_promedio,

/*=====================================================
CLIENTES
Se mantienen los clientes según los filtros.
=====================================================*/

COUNT(DISTINCT tv.idCliente) AS clientes,

/*=====================================================
ÚLTIMA VENTA
=====================================================*/

MAX(tv.fecha_venta) AS ultima_venta

FROM metodo_pago mp

LEFT JOIN ticket_ventas tv
ON tv.id_metodo_pago = mp.id_metodo_pago

WHERE {$whereSQL}

GROUP BY mp.id_metodo_pago

ORDER BY {$orderSQL}

LIMIT ?,?

";
$tiposConsulta = $tipos . "ii";

$paramConsulta = $parametros;

$paramConsulta[] = $inicio;

$paramConsulta[] = $registros;

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    $tiposConsulta,
    ...$paramConsulta
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*=====================================================
=            GENERAR TABLA HTML
=====================================================*/

$tbody = "";

$numero = $inicio + 1;

while ($fila = mysqli_fetch_assoc($resultado)) {

    $idMetodo = (int)$fila["id_metodo_pago"];

    $nombre = htmlspecialchars($fila["nombre"]);

    $ventas = (int)$fila["ventas"];

    $totalVendido = (float)$fila["total_vendido"];

    $ticketPromedio = (float)$fila["ticket_promedio"];

    $clientes = (int)$fila["clientes"];

    $ultimaVenta = $fila["ultima_venta"];

    $eliminado = (int)$fila["Eliminado"];


    /*=============================================
ESTADO
=============================================*/

    $estado = '
    <span class="badge bg-success">
        Activo
    </span>
';

    $botonEstado = '
    <button
        type="button"
        class="btn btn-outline-danger btn-sm btnEliminarMetodo"
        data-id="' . $idMetodo . '"
        title="Eliminar método">

        <i class="bi bi-trash-fill"></i>

    </button>
';


    /*=============================================
    FECHA
    =============================================*/

    $fecha = "-";

    if (!empty($ultimaVenta)) {

        $fecha = date("d/m/Y", strtotime($ultimaVenta));
    }


    /*=============================================
    FILA
    =============================================*/

    $tbody .= '

<tr>

    <td>

        <input
            type="checkbox"
            class="form-check-input checkMetodo"
            value="' . $idMetodo . '">

    </td>

    <td>

        ' . $numero . '

    </td>

    <td>

        <div class="fw-semibold">

            ' . $nombre . '

        </div>

    </td>

    <td>

        <span class="badge bg-primary">

            ' . number_format($ventas) . '

        </span>

    </td>

    <td>

        <span class="text-success fw-bold">

            S/ ' . number_format($totalVendido, 2) . '

        </span>

    </td>

    <td>

        S/ ' . number_format($ticketPromedio, 2) . '

    </td>

    <td>

        ' . number_format($clientes) . '

    </td>

    <td>

        ' . $fecha . '

    </td>

    <td class="text-center">

        <div class="btn-group">

            <button
                class="btn btn-outline-primary btn-sm btnEditarMetodo"
                data-id="' . $idMetodo . '">

                <i class="bi bi-pencil-square"></i>

            </button>

            ' . $botonEstado . '

        </div>

    </td>

</tr>

';

    $numero++;
}

mysqli_stmt_close($stmt);


/*=====================================================
SIN RESULTADOS
=====================================================*/

if ($tbody == "") {

    $tbody = '

<tr>

<td colspan="10" class="text-center py-5">

<i class="bi bi-credit-card-2-front fs-1 text-secondary"></i>

<h5 class="mt-3">

No se encontraron métodos de pago.

</h5>

</td>

</tr>

';
}


/*=====================================================
PAGINACIÓN
=====================================================*/

$paginacion = "";

if ($totalPaginas > 1) {

    /*---------- anterior ----------*/

    if ($pagina > 1) {

        $paginacion .= '

<li class="page-item">

<a
href="#"
class="page-link paginaMetodo"

data-pagina="' . ($pagina - 1) . '">

&laquo;

</a>

</li>

';
    }


    /*---------- páginas ----------*/

    for ($i = 1; $i <= $totalPaginas; $i++) {

        $active = "";

        if ($i == $pagina) {

            $active = "active";
        }

        $paginacion .= '

<li class="page-item ' . $active . '">

<a
href="#"
class="page-link paginaMetodo"

data-pagina="' . $i . '">

' . $i . '

</a>

</li>

';
    }


    /*---------- siguiente ----------*/

    if ($pagina < $totalPaginas) {

        $paginacion .= '

<li class="page-item">

<a
href="#"
class="page-link paginaMetodo"

data-pagina="' . ($pagina + 1) . '">

&raquo;

</a>

</li>

';
    }
}


/*=====================================================
TEXTO REGISTROS
=====================================================*/

$desde = 0;
$hasta = 0;

if ($totalRegistros > 0) {

    $desde = $inicio + 1;

    $hasta = min($inicio + $registros, $totalRegistros);
}

$texto = "Mostrando {$desde} a {$hasta} de {$totalRegistros} registros";


/*=====================================================
RESPUESTA JSON
=====================================================*/

echo json_encode([

    "estado" => true,

    "tbody" => $tbody,

    "paginacion" => $paginacion,

    "texto" => $texto,

    "pagina" => $pagina,

    "totalPaginas" => $totalPaginas,

    "totalRegistros" => $totalRegistros

]);

mysqli_close($conexion);
