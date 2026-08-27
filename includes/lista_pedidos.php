<?php
//=========================================================
// CoDevPro Technology
// includes/lista_pedidos.php
// Módulo: Mis Pedidos
// Sistema: Inventa
//=========================================================

require_once "../controladores/obtener_mis_pedidos.php";


/*=========================================================
SIN PEDIDOS
=========================================================*/

if (
    !$resultadoPedidos ||
    mysqli_num_rows($resultadoPedidos) == 0
) {
?>

    <div class="text-center py-5">

        <img
            src="../assets/img/sin_imagen.png"
            class="img-fluid mb-4"
            style="max-width:220px;"
            alt="Sin pedidos">

        <h3 class="fw-bold">

            Aún no tienes pedidos realizados

        </h3>

        <p class="text-muted mb-4">

            Cuando realices una compra, aquí podrás consultar
            el historial completo de todos tus pedidos.

        </p>

        <a
            href="tienda.php"
            class="btn btn-primary btn-lg">

            <i class="bi bi-shop"></i>

            Ir a comprar

        </a>

    </div>

<?php

    return;
}

?>

<!--=====================================================
LISTA DE PEDIDOS
======================================================-->

<div class="row g-4">

    <?php while ($pedido = mysqli_fetch_assoc($resultadoPedidos)) { ?>

        <?php

        /*=================================================
        FORMATEAR FECHA
        =================================================*/

        $fechaPedido = date(
            "d/m/Y",
            strtotime($pedido["fecha_venta"])
        );


        /*=================================================
        ESTADO REAL
        =================================================*/

        $estadoReal = strtoupper(
            trim(
                $pedido["estado_envio"] ?? ""
            )
        );


        /*=================================================
        CONFIGURACIÓN DEL ESTADO PARA EL CLIENTE
        =================================================*/

        $estadoMostrar = "Pendiente";

        $badge = "secondary";

        $icono = "bi-clock-history";

        $mensaje = "Estado del pedido";


        /*=================================================
        PENDIENTE
        =================================================*/

        switch ($estadoReal) {

            case "PENDIENTE":

                $estadoMostrar = "Pendiente";

                $badge = "warning";

                $icono = "bi-hourglass-split";

                $mensaje =
                    "Estamos esperando la confirmación de tu pedido.";

                break;


            /*=================================================
            CONFIRMADO
            =================================================*/

            case "CONFIRMADO":

                $estadoMostrar = "Confirmado";

                $badge = "secondary";

                $icono = "bi-check2-square";

                $mensaje =
                    "Tu pedido ha sido confirmado.";

                break;


            /*=================================================
            PREPARANDO
            =================================================*/

            case "PREPARANDO":

                $estadoMostrar = "Preparando";

                $badge = "info";

                $icono = "bi-box-seam";

                $mensaje =
                    "Estamos preparando tu pedido.";

                break;


            /*=================================================
            EN CAMINO

            ASIGNADO
            OBTENIDO
            ENVIADO
            =================================================*/

            case "ASIGNADO":

            case "OBTENIDO":

            case "ENVIADO":

                $estadoMostrar = "En camino";

                $badge = "primary";

                $icono = "bi-truck";

                /*-----------------------------------------
                MENSAJE SEGÚN EL ESTADO REAL
                -----------------------------------------*/

                if ($estadoReal === "ASIGNADO") {

                    $mensaje =
                        "Tu pedido ha sido asignado al repartidor.";
                } elseif ($estadoReal === "OBTENIDO") {

                    $mensaje =
                        "El repartidor ha recogido tu pedido.";
                } else {

                    $mensaje =
                        "Tu pedido está en camino.";
                }

                break;


            /*=================================================
            ENTREGADO
            =================================================*/

            case "ENTREGADO":

                $estadoMostrar = "Entregado";

                $badge = "success";

                $icono = "bi-check-circle-fill";

                $mensaje =
                    "Pedido entregado correctamente.";

                break;


            /*=================================================
            NO ENTREGADO
            =================================================*/

            case "NO_ENTREGADO":

                $estadoMostrar = "No entregado";

                $badge = "danger";

                $icono = "bi-exclamation-circle";

                $mensaje =
                    "El pedido no pudo ser entregado.";

                break;


            /*=================================================
            CANCELADO
            =================================================*/

            case "CANCELADO":

                $estadoMostrar = "Cancelado";

                $badge = "danger";

                $icono = "bi-x-circle";

                $mensaje =
                    "Pedido cancelado.";

                break;


            /*=================================================
            ESTADO DESCONOCIDO
            =================================================*/

            default:

                $estadoMostrar =
                    !empty($estadoReal)
                    ? ucwords(
                        strtolower(
                            str_replace(
                                "_",
                                " ",
                                $estadoReal
                            )
                        )
                    )
                    : "Sin estado";

                $badge = "secondary";

                $icono = "bi-question-circle";

                $mensaje =
                    "Estado actual del pedido.";

                break;
        }

        ?>

        <!--=================================================
        PEDIDO
        ==================================================-->

        <div class="col-12">

            <div class="card shadow-sm border-0 pedido-card">


                <!--=================================================
                CABECERA
                ==================================================-->

                <div class="card-header bg-white">

                    <div class="row align-items-center">

                        <div class="col-lg-8">

                            <div
                                class="d-flex flex-wrap align-items-center gap-3">

                                <!-- Número de pedido -->

                                <span class="fw-bold">

                                    Pedido #

                                    <?= str_pad(
                                        $pedido["id_ticket_ventas"],
                                        6,
                                        "0",
                                        STR_PAD_LEFT
                                    ); ?>

                                </span>


                                <!-- Estado -->

                                <span class="badge bg-<?= $badge; ?>">

                                    <?= htmlspecialchars(
                                        $estadoMostrar,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>

                                </span>


                                <!-- Fecha -->

                                <small class="text-muted">

                                    <?= $fechaPedido; ?>

                                </small>

                            </div>

                        </div>


                        <!--=================================================
                        TOTAL
                        ==================================================-->

                        <div
                            class="col-lg-4 text-lg-end mt-2 mt-lg-0">

                            <strong>

                                Total:

                                S/

                                <?= number_format(
                                    $pedido["total_venta"],
                                    2
                                ); ?>

                            </strong>

                        </div>

                    </div>

                </div>


                <!--=================================================
                CUERPO DEL PEDIDO
                ==================================================-->

                <div class="card-body">

                    <div class="row align-items-center">


                        <!--=================================================
                        IMAGEN DEL PRODUCTO
                        ==================================================-->

                        <div
                            class="col-lg-3 text-center mb-3 mb-lg-0">

                            <?php if (!empty($pedido["id_imagen"])) { ?>

                                <img
                                    src="mostrar_imagen.php?id=<?= (int)$pedido["idProducto"]; ?>&img=<?= (int)$pedido["id_imagen"]; ?>"
                                    class="img-fluid rounded"
                                    style="
                                        width:160px;
                                        height:160px;
                                        object-fit:contain;
                                    "
                                    alt="<?= htmlspecialchars(
                                                $pedido["primer_producto"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ); ?>">

                            <?php } else { ?>

                                <img
                                    src="../assets/img/sin_imagen.png"
                                    class="img-fluid rounded"
                                    style="
                                        width:160px;
                                        height:160px;
                                        object-fit:contain;
                                    "
                                    alt="Sin imagen">

                            <?php } ?>

                        </div>


                        <!--=================================================
                        INFORMACIÓN DEL PEDIDO
                        ==================================================-->

                        <div class="col-lg-5">

                            <h5 class="fw-bold mb-3">

                                <?= htmlspecialchars(
                                    $pedido["primer_producto"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>

                            </h5>


                            <!-- Método de pago -->

                            <div class="mb-2">

                                <i
                                    class="bi bi-credit-card text-primary">
                                </i>

                                <strong>

                                    Método de pago:

                                </strong>

                                <?= htmlspecialchars(
                                    $pedido["metodo_pago"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>

                            </div>


                            <!-- Dirección -->

                            <div class="mb-2">

                                <i
                                    class="bi bi-geo-alt text-danger">
                                </i>

                                <strong>

                                    Dirección:

                                </strong>

                                <span class="text-muted">

                                    <?= htmlspecialchars(
                                        $pedido["direccion_envio"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>

                                </span>

                            </div>


                            <!-- Productos -->

                            <div class="mb-2">

                                <i
                                    class="bi bi-box-seam text-primary">
                                </i>

                                <strong>

                                    Productos comprados:

                                </strong>

                                <?= intval(
                                    $pedido["cantidad_productos"]
                                ); ?>

                            </div>


                            <!-- Comprobante -->

                            <div class="mb-2">

                                <i
                                    class="bi bi-receipt text-secondary">
                                </i>

                                <strong>

                                    Comprobante:

                                </strong>

                                <?= htmlspecialchars(
                                    $pedido["tipo_comprobante"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>


                                <?php if (!empty($pedido["serie"])) { ?>

                                    -

                                    <?= htmlspecialchars(
                                        $pedido["serie"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>

                                    -

                                    <?= htmlspecialchars(
                                        $pedido["numero"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>

                                <?php } ?>

                            </div>


                            <!--=================================================
                            ENVÍO
                            ==================================================-->

                            <div class="mt-3">

                                <?php if (
                                    isset($pedido["envio_gratis"]) &&
                                    (int)$pedido["envio_gratis"] === 1
                                ) { ?>

                                    <span class="badge bg-success">

                                        <i class="bi bi-truck"></i>

                                        Envío gratis

                                    </span>

                                <?php } else { ?>

                                    <span class="badge bg-secondary">

                                        <i class="bi bi-truck"></i>

                                        Envío con costo

                                    </span>

                                <?php } ?>

                            </div>

                        </div>


                        <!--=================================================
                        ESTADO DEL ENVÍO
                        ==================================================-->

                        <div class="col-lg-4">

                            <div
                                class="border rounded p-3 bg-light h-100">

                                <div class="mb-2">

                                    <i
                                        class="bi <?= $icono; ?> text-primary">
                                    </i>

                                    <strong>

                                        Estado del envío

                                    </strong>

                                </div>


                                <!-- Estado visible -->

                                <div
                                    class="fw-bold text-<?= $badge; ?> mb-2">

                                    <?= htmlspecialchars(
                                        $estadoMostrar,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>

                                </div>


                                <!-- Mensaje -->

                                <small class="text-muted">

                                    <?= htmlspecialchars(
                                        $mensaje,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>

                                </small>


                                <?php if (
                                    in_array(
                                        $estadoReal,
                                        [
                                            "ASIGNADO",
                                            "OBTENIDO",
                                            "ENVIADO"
                                        ],
                                        true
                                    )
                                ) { ?>

                                    <div class="mt-3">

                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary">

                                            <i class="bi bi-truck"></i>

                                            En camino

                                        </span>

                                    </div>

                                <?php } ?>

                            </div>

                        </div>

                    </div>

                </div>


                <!--=====================================
BOTONES
======================================-->

                <div class="card-footer bg-white">

                    <div class="d-flex flex-wrap justify-content-end gap-2">


                        <!--=====================================
        VER DETALLE
        =====================================-->

                        <a
                            href="controladores/seleccionar_pedido.php?id=<?= (int)$pedido['id_ticket_ventas']; ?>"
                            class="btn btn-primary btn-sm">

                            <i class="bi bi-eye me-1"></i>

                            Ver detalle

                        </a>


                        <?php

                        /*=============================================
        MOSTRAR CONFIRMAR ENTREGA

        EN CAMINO =
        ASIGNADO
        OBTENIDO
        ENVIADO
        =============================================*/

                        $puedeConfirmarEntrega = in_array(
                            strtoupper(trim($pedido["estado_envio"] ?? "")),
                            [
                                "ASIGNADO",
                                "OBTENIDO",
                                "ENVIADO"
                            ],
                            true
                        );

                        ?>


                        <?php if ($puedeConfirmarEntrega) { ?>

                            <!--=====================================
            CONFIRMAR ENTREGA
            =====================================-->

                            <button
                                type="button"
                                class="btn btn-success btn-sm btnAbrirConfirmarEntrega"
                                data-id-pedido="<?= (int)$pedido['id_ticket_ventas']; ?>"
                                data-numero-pedido="#<?= str_pad(
                                                            $pedido["id_ticket_ventas"],
                                                            6,
                                                            "0",
                                                            STR_PAD_LEFT
                                                        ); ?>">

                                <i class="bi bi-check-circle me-1"></i>

                                Confirmar entrega

                            </button>

                        <?php } ?>
                        <?php if ($estadoReal === "ENTREGADO") { ?>

                            <!--=====================================
                            DESCARGAR COMPROBANTE
                            SOLO PEDIDOS ENTREGADOS
                            =====================================-->

                            <a
                                href="descargar_comprobante.php?id_ticket_ventas=<?= (int)$pedido['id_ticket_ventas']; ?>"
                                target="_blank"
                                class="btn btn-danger btn-sm">

                                <i class="bi bi-file-earmark-pdf me-1"></i>

                                Ver comprobante

                            </a>

                        <?php } ?>

                    </div>

                </div>

            </div>

        </div>

    <?php } ?>

</div>


<!--=====================================================
PAGINACIÓN DINÁMICA
======================================================-->

<?php if ($totalPaginas > 1) { ?>

    <nav
        class="mt-4"
        aria-label="Paginación de pedidos">

        <ul class="pagination justify-content-center">


            <!--=================================================
            ANTERIOR
            ==================================================-->

            <li
                class="page-item
                <?= ($pagina <= 1) ? "disabled" : ""; ?>">

                <a
                    href="#"
                    class="page-link paginaPedido"
                    data-pagina="<?= max(
                                        1,
                                        $pagina - 1
                                    ); ?>">

                    <i class="bi bi-chevron-left"></i>

                </a>

            </li>


            <!--=================================================
            NÚMEROS
            ==================================================-->

            <?php for (
                $i = 1;
                $i <= $totalPaginas;
                $i++
            ) { ?>

                <li
                    class="page-item
                    <?= ($i == $pagina) ? "active" : ""; ?>">

                    <a
                        href="#"
                        class="page-link paginaPedido"
                        data-pagina="<?= $i; ?>">

                        <?= $i; ?>

                    </a>

                </li>

            <?php } ?>


            <!--=================================================
            SIGUIENTE
            ==================================================-->

            <li
                class="page-item
                <?= ($pagina >= $totalPaginas)
                    ? "disabled"
                    : ""; ?>">

                <a
                    href="#"
                    class="page-link paginaPedido"
                    data-pagina="<?= min(
                                        $totalPaginas,
                                        $pagina + 1
                                    ); ?>">

                    <i class="bi bi-chevron-right"></i>

                </a>

            </li>

        </ul>

    </nav>

<?php } ?>