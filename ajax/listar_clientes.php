<?php
//=====================================================
// CoDevPro Technology
// ajax/listar_clientes.php
//=====================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false
    ]);

    exit;
}

$idUser = (int) $_SESSION["idUser"];

$buscar  = trim($_POST["buscar"] ?? "");
$estado  = trim($_POST["estado"] ?? "");
$ordenar = trim($_POST["ordenar"] ?? "recientes");
$pagina  = max(1, (int)($_POST["pagina"] ?? 1));
$rubro   = intval($_POST["rubro"] ?? 0);

$limite = 6;
$inicio = ($pagina - 1) * $limite;


/*=====================================
FILTROS
=====================================*/

$where = " WHERE c.id_user = ? AND c.Eliminado = 0 ";

if ($rubro > 0) {

    $where .= " AND c.id_rubro = {$rubro}";
}

$tipos = "i";
$parametros = [$idUser];

if ($buscar != "") {

    $where .= " AND (
        c.nombre LIKE ?
        OR c.email LIKE ?
        OR c.dni_o_ruc LIKE ?
        OR c.celular LIKE ?
    ) ";

    $like = "%{$buscar}%";

    $tipos .= "ssss";

    $parametros[] = $like;
    $parametros[] = $like;
    $parametros[] = $like;
    $parametros[] = $like;
}

if ($estado != "") {

    $where .= " AND c.estado = ? ";

    $tipos .= "s";

    $parametros[] = $estado;
}


/*=====================================
ORDENAMIENTO
=====================================*/

$orderBy = " ORDER BY c.idCliente DESC ";

switch ($ordenar) {

    case "nombre_asc":

        $orderBy = " ORDER BY c.nombre ASC ";

        break;

    case "nombre_desc":

        $orderBy = " ORDER BY c.nombre DESC ";

        break;

    case "compras_desc":

        $orderBy = " ORDER BY total_compras DESC ";

        break;

    case "antiguos":

        $orderBy = " ORDER BY c.idCliente ASC ";

        break;
}


/*=====================================
TOTAL REGISTROS
=====================================*/

$sqlTotal = "
SELECT COUNT(*) total
FROM clientes c
$where
";

$stmtTotal = mysqli_prepare($conexion, $sqlTotal);

mysqli_stmt_bind_param(
    $stmtTotal,
    $tipos,
    ...$parametros
);

mysqli_stmt_execute($stmtTotal);

$resultadoTotal = mysqli_stmt_get_result($stmtTotal);

$totalRegistros = mysqli_fetch_assoc($resultadoTotal)["total"];

$totalPaginas = ceil($totalRegistros / $limite);


/*=====================================
CONSULTA PRINCIPAL
=====================================*/

$sql = "
SELECT
c.*,

(
    SELECT COUNT(*)
    FROM ticket_ventas tv
    WHERE tv.idCliente = c.idCliente
) pedidos,

(
    SELECT IFNULL(SUM(tv.total_venta),0)
    FROM ticket_ventas tv
    WHERE tv.idCliente = c.idCliente
) total_compras,

(
    SELECT COUNT(*)
    FROM favoritos f
    WHERE f.idCliente = c.idCliente
) favoritos,

(
    SELECT COUNT(*)
    FROM testimonios t
    WHERE t.idCliente = c.idCliente
) testimonios

FROM clientes c

$where

$orderBy

LIMIT ?, ?
";

$stmt = mysqli_prepare($conexion, $sql);

$tiposFinal = $tipos . "ii";

$parametros[] = $inicio;
$parametros[] = $limite;

mysqli_stmt_bind_param(
    $stmt,
    $tiposFinal,
    ...$parametros
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


/*=====================================
TABLA
=====================================*/

$html = '

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>

<th width="80">
    Foto
</th>

<th>
    Cliente
</th>

<th width="180">
    Contacto
</th>

<th width="100">
    Pedidos
</th>

<th width="130">
    Compras
</th>

<th width="100">
    Favoritos
</th>

<th width="110">
    Testimonios
</th>

<th width="110">
    Estado
</th>

<th width="150">
    Acciones
</th>

</tr>

</thead>

<tbody>
';


if (mysqli_num_rows($resultado) > 0) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        /*=====================================
        IMAGEN
        =====================================*/

        $imagen = !empty($fila["imagen"])
            ? "data:image/jpeg;base64," . base64_encode($fila["imagen"])
            : "assets/img/sin_imagen.png";


        /*=====================================
        ESTADO
        =====================================*/

        $badgeEstado =
            $fila["estado"] == "ACTIVO"
            ? '<span class="badge bg-success">ACTIVO</span>'
            : '<span class="badge bg-secondary">INACTIVO</span>';


        /*=====================================
CONTACTO
=====================================*/

        $celular = trim($fila["celular"] ?? "");
        $email   = trim($fila["email"] ?? "");

        $contacto = "";


        /*=====================================
WHATSAPP
=====================================*/

        if ($celular !== "") {

            // Limpiar número para WhatsApp
            $numeroWhatsApp = preg_replace('/\D/', '', $celular);

            // Si es un celular peruano de 9 dígitos
            if (
                strlen($numeroWhatsApp) === 9 &&
                substr($numeroWhatsApp, 0, 1) === "9"
            ) {

                $numeroWhatsApp = "51" . $numeroWhatsApp;
            }

            $mensajeWhatsApp =
                "Hola " .
                ($fila["nombre"] ?? "Cliente") .
                ", le escribimos de CoDevPro Technology.";

            $urlWhatsApp =
                "https://wa.me/" .
                $numeroWhatsApp .
                "?text=" .
                rawurlencode($mensajeWhatsApp);


            $contacto .= '

        <a
            href="' . htmlspecialchars($urlWhatsApp, ENT_QUOTES, "UTF-8") . '"
            target="_blank"
            rel="noopener noreferrer"
            class="d-block fw-semibold text-success text-decoration-none">

            ' . htmlspecialchars($celular) . '

        </a>

    ';
        } else {

            $contacto .= '

        <div class="text-muted">

            Sin celular

        </div>

    ';
        }


        /*=====================================
EMAIL
=====================================*/

        if ($email !== "") {

            $asunto = "Comunicación - CoDevPro Technology";

            $mensajeEmail =
                "Hola " .
                ($fila["nombre"] ?? "Cliente") .
                ",\n\n" .
                "Le escribimos de CoDevPro Technology.\n\n" .
                "Saludos.";

            $mailto =
                "mailto:" .
                $email .
                "?subject=" .
                rawurlencode($asunto) .
                "&body=" .
                rawurlencode($mensajeEmail);


            $contacto .= '

        <a
            href="' . htmlspecialchars($mailto, ENT_QUOTES, "UTF-8") . '"
            class="d-block small text-primary text-decoration-none text-break">

            ' . htmlspecialchars($email) . '

        </a>

    ';
        } else {

            $contacto .= '

        <div class="small text-muted">

            Sin email

        </div>

    ';
        }


        /*=====================================
        FILA
        =====================================*/

        $html .= '

        <tr>

            <!-- FOTO -->

            <td>

                <img
                    src="' . $imagen . '"
                    width="50"
                    height="50"
                    class="rounded-circle border"
                    style="object-fit:cover;">

            </td>


            <!-- CLIENTE -->

            <td>

                <div class="fw-bold">

                    ' . htmlspecialchars($fila["nombre"]) . '

                </div>

                <small class="text-muted">

                    ' . htmlspecialchars($fila["dni_o_ruc"]) . '

                </small>

            </td>


            <!-- CONTACTO -->

            <td>

                ' . $contacto . '

            </td>


            <!-- PEDIDOS -->

            <td>

                ' . $fila["pedidos"] . '

            </td>


            <!-- COMPRAS -->

            <td>

                S/ ' . number_format($fila["total_compras"], 2) . '

            </td>


            <!-- FAVORITOS -->

            <td>

                ' . $fila["favoritos"] . '

            </td>


            <!-- TESTIMONIOS -->

            <td>

                ' . $fila["testimonios"] . '

            </td>


            <!-- ESTADO -->

            <td>

                ' . $badgeEstado . '

            </td>


            <!-- ACCIONES -->

            <td>

                <div class="btn-group btn-group-sm">

                    <button
                        class="btn btn-info btn-ver-cliente"
                        data-id="' . $fila["idCliente"] . '">

                        <i class="bi bi-eye-fill"></i>

                    </button>


                    <button
                        class="btn btn-warning btn-editar-cliente"
                        data-id="' . $fila["idCliente"] . '">

                        <i class="bi bi-pencil-fill"></i>

                    </button>


                    <button
                        class="btn btn-danger btn-eliminar-cliente"
                        data-id="' . $fila["idCliente"] . '">

                        <i class="bi bi-trash-fill"></i>

                    </button>

                </div>

            </td>

        </tr>

        ';
    }
} else {

    $html .= '

    <tr>

        <td colspan="9" class="text-center py-5">

            No se encontraron clientes.

        </td>

    </tr>

    ';
}


$html .= '

</tbody>

</table>
';


/*=====================================
PAGINACIÓN
=====================================*/

$paginacion = '';

if ($totalPaginas > 1) {

    $paginacion .= '

    <nav>

        <ul class="pagination justify-content-center">

    ';

    for ($i = 1; $i <= $totalPaginas; $i++) {

        $activo = $pagina == $i ? "active" : "";

        $paginacion .= '

        <li class="page-item ' . $activo . '">

            <a
                href="#"
                class="page-link btn-pagina-cliente"
                data-pagina="' . $i . '">

                ' . $i . '

            </a>

        </li>

        ';
    }

    $paginacion .= '

        </ul>

    </nav>

    ';
}


/*=====================================
RESPUESTA
=====================================*/

echo json_encode([

    "estado" => true,

    "tabla" => $html,

    "paginacion" => $paginacion,

    "totalRegistros" => $totalRegistros

]);

mysqli_close($conexion);
