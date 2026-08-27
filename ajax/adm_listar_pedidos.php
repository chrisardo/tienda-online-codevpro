<?php
//=====================================================
// CoDevPro Technology
// ajax/adm_listar_pedidos.php
//=====================================================

session_start();

require_once "../controladores/conexion.php";


header("Content-Type: text/html; charset=utf-8");


/*=====================================================
=            VALIDAR SESIÓN
=====================================================*/

$idUser = $_SESSION["idUser"] ?? 0;


if (!$idUser) {

    echo '
    <tr>
        <td colspan="7" class="text-center text-danger py-4">

            Sesión no válida.

        </td>
    </tr>';

    exit;
}



/*=====================================================
=            VARIABLES
=====================================================*/

$buscar = trim($_GET["buscar"] ?? "");

$estado = trim($_GET["estado"] ?? "");

$metodoPago = intval($_GET["metodo_pago"] ?? 0);

$fechaInicio = $_GET["fecha_inicio"] ?? "";

$fechaFin = $_GET["fecha_fin"] ?? "";

$orden = $_GET["orden"] ?? "recientes";


$pagina = intval($_GET["pagina"] ?? 1);


if ($pagina < 1) {

    $pagina = 1;
}


$limite = 10;


$inicio = ($pagina - 1) * $limite;



/*=====================================================
=            CONSULTA BASE
=====================================================*/


$sqlBase = "

FROM ticket_ventas tv

INNER JOIN clientes c

ON tv.idCliente = c.idCliente


LEFT JOIN metodo_pago mp

ON tv.id_metodo_pago = mp.id_metodo_pago


WHERE tv.id_user = ?

";



$condiciones = "";



$tipos = "i";



$parametros = [

    $idUser

];





/*=====================================================
=            BUSCAR
=====================================================*/


if ($buscar != "") {


    $condiciones .= "

    AND (

        tv.id_ticket_ventas LIKE ?

        OR c.nombre LIKE ?

        OR c.dni_o_ruc LIKE ?

        OR c.celular LIKE ?

    )

    ";


    $like = "%" . $buscar . "%";


    $tipos .= "ssss";


    $parametros[] = $like;
    $parametros[] = $like;
    $parametros[] = $like;
    $parametros[] = $like;
}





/*=====================================================
=            ESTADO
=====================================================*/


if ($estado != "") {


    $condiciones .= "

    AND tv.estado_envio = ?

    ";


    $tipos .= "s";


    $parametros[] = $estado;
}




/*=====================================================
=            METODO PAGO
=====================================================*/


if ($metodoPago > 0) {


    $condiciones .= "

    AND tv.id_metodo_pago = ?

    ";


    $tipos .= "i";


    $parametros[] = $metodoPago;
}




/*=====================================================
=            FECHAS
=====================================================*/


if ($fechaInicio != "") {


    $condiciones .= "

    AND tv.fecha_venta >= ?

    ";


    $tipos .= "s";


    $parametros[] = $fechaInicio;
}




if ($fechaFin != "") {


    $condiciones .= "

    AND tv.fecha_venta <= ?

    ";


    $tipos .= "s";


    $parametros[] = $fechaFin;
}




/*=====================================================
=            TOTAL REGISTROS
=====================================================*/


$sqlCount = "

SELECT COUNT(*) total

" . $sqlBase . $condiciones;



$stmt = mysqli_prepare(
    $conexion,
    $sqlCount
);



mysqli_stmt_bind_param(
    $stmt,
    $tipos,
    ...$parametros
);



mysqli_stmt_execute($stmt);



$resultCount = mysqli_stmt_get_result($stmt);


$totalPedidos = mysqli_fetch_assoc($resultCount)["total"];



$totalPaginas = ceil(
    $totalPedidos / $limite
);



/*=====================================================
=            ORDEN
=====================================================*/


switch ($orden) {


    case "antiguos":

        $order = "ORDER BY tv.id_ticket_ventas ASC";

        break;



    case "mayor":

        $order = "ORDER BY tv.total_venta DESC";

        break;



    case "menor":

        $order = "ORDER BY tv.total_venta ASC";

        break;



    default:

        $order = "ORDER BY tv.id_ticket_ventas DESC";

        break;
}





/*=====================================================
=            LISTAR PEDIDOS
=====================================================*/


$sql = "

SELECT

tv.id_ticket_ventas,

tv.fecha_venta,

tv.hora_venta,

tv.total_venta,

tv.estado_envio,

tv.serie,

tv.numero,


c.nombre cliente,

c.celular,

c.email,


mp.nombre metodo_pago


" . $sqlBase . $condiciones . "

" . $order . "

LIMIT ?,?


";



$tiposFinal = $tipos . "ii";



$parametrosFinal = $parametros;


$parametrosFinal[] = $inicio;

$parametrosFinal[] = $limite;



$stmt = mysqli_prepare(
    $conexion,
    $sql
);



mysqli_stmt_bind_param(
    $stmt,
    $tiposFinal,
    ...$parametrosFinal
);



mysqli_stmt_execute($stmt);



$resultado = mysqli_stmt_get_result($stmt);




/*=====================================================
=            GENERAR TABLA
=====================================================*/


if (mysqli_num_rows($resultado) == 0) {


    echo '

<tr>

<td colspan="7" class="text-center py-5">


<i class="bi bi-bag-x display-5 text-muted"></i>


<p class="mt-3 text-muted">

No existen pedidos registrados.

</p>


</td>

</tr>

';


    exit;
}





while ($pedido = mysqli_fetch_assoc($resultado)) {



    switch ($pedido["estado_envio"]) {


        case "PENDIENTE":
            $badge = "warning";
            break;


        case "CONFIRMADO":
            $badge = "info";
            break;


        case "PREPARANDO":
            $badge = "primary";
            break;


        case "ENVIADO":
            $badge = "secondary";
            break;


        case "ENTREGADO":
            $badge = "success";
            break;


        case "CANCELADO":
            $badge = "danger";
            break;


        default:
            $badge = "dark";
    }




?>


    <tr>


        <td>


            <strong>

                #<?= $pedido["id_ticket_ventas"]; ?>

            </strong>


            <br>


            <small class="text-muted">

                <?= $pedido["serie"]; ?>

                -

                <?= $pedido["numero"]; ?>

            </small>


        </td>



        <td>


            <strong>

                <?= htmlspecialchars($pedido["cliente"]); ?>

            </strong>


            <br>


            <small class="text-muted">

                <?= $pedido["celular"]; ?>

            </small>


        </td>




        <td>


            <?= date(
                "d/m/Y",
                strtotime($pedido["fecha_venta"])
            ); ?>


            <br>


            <small>

                <?= $pedido["hora_venta"]; ?>

            </small>


        </td>




        <td>

            <?= htmlspecialchars(
                $pedido["metodo_pago"] ?? "Sin definir"
            ); ?>

        </td>




        <td>


            <strong>

                S/.

                <?= number_format(
                    $pedido["total_venta"],
                    2
                ); ?>


            </strong>


        </td>




        <td>


            <span class="badge bg-<?= $badge; ?>">


                <?= $pedido["estado_envio"]; ?>


            </span>


        </td>




        <td class="text-center">


            <div class="btn-group">


                <button

                    class="btn btn-sm btn-primary"

                    data-bs-toggle="modal"

                    data-bs-target="#modalVerPedido"

                    data-id="<?= $pedido["id_ticket_ventas"]; ?>">

                    <i class="bi bi-eye-fill"></i>

                </button>




                <button

                    class="btn btn-sm btn-success"

                    data-bs-toggle="modal"

                    data-bs-target="#modalEstadoPedido"

                    data-id="<?= $pedido["id_ticket_ventas"]; ?>"

                    data-estado="<?= $pedido["estado_envio"]; ?>">

                    <i class="bi bi-pencil-fill"></i>

                </button>



            </div>


        </td>



    </tr>



<?php

}



?>



<script>
    document.getElementById("totalPedidos").innerHTML =

        "<?= $totalPedidos ?> Pedidos";
</script>



<nav>


    <ul class="pagination justify-content-center">


        <?php if ($pagina > 1): ?>


            <li class="page-item">

                <a class="page-link pagina-pedido"
                    href="#"
                    data-pagina="<?= $pagina - 1 ?>">
                    Anterior
                </a>

            </li>


        <?php endif; ?>




        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>


            <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">


                <a class="page-link pagina-pedido"
                    href="#"
                    data-pagina="<?= $i ?>">


                    <?= $i ?>


                </a>


            </li>



        <?php endfor; ?>




        <?php if ($pagina < $totalPaginas): ?>


            <li class="page-item">


                <a class="page-link pagina-pedido"
                    href="#"
                    data-pagina="<?= $pagina + 1 ?>">
                    Siguiente
                </a>


            </li>


        <?php endif; 
        ?>



    </ul>


</nav>