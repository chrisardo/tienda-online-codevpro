<!--======================================================
CoDevPro Technology
DASHBOARD DEL CLIENTE
=======================================================-->
<?php
require_once __DIR__ . "/../../controladores/perfil/dashboard_controller.php";
?>
<div class="row g-4">
    <!--======================================================
RESUMEN DE COMPRAS
=======================================================-->

    <div class="row mt-4">

        <div class="col-12">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-graph-up-arrow text-success"></i>

                        Resumen de compras

                    </h5>

                </div>

                <div class="card-body">

                    <div class="row text-center">

                        <div class="col-md-3">

                            <div>

                                <small class="text-muted">

                                    Pedidos

                                </small>

                                <h3 class="fw-bold mb-0 text-primary">

                                    <?= number_format($totalPedidos) ?>

                                </h3>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <small class="text-muted">

                                Total comprado

                            </small>

                            <h4 class="fw-bold text-success mb-0">

                                S/
                                <?= number_format($totalComprado, 2) ?>

                            </h4>

                        </div>

                        <div class="col-md-3">

                            <small class="text-muted">

                                Favoritos

                            </small>

                            <h3 class="fw-bold mb-0">

                                <?= number_format($totalFavoritos) ?>

                            </h3>

                        </div>

                        <div class="col-md-3">

                            <small class="text-muted">

                                Testimonios

                            </small>

                            <h3 class="fw-bold mb-0">

                                <?= number_format($totalTestimonios) ?>

                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
    <!--=========================================
ÚLTIMO PEDIDO
==========================================-->

    <div class="col-xl-8">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-bag-check-fill text-primary"></i>

                        Último pedido

                    </h5>

                    <a href="mis_pedidos.php"
                        class="btn btn-sm btn-outline-primary">

                        Ver todos

                    </a>

                </div>

            </div>

            <div class="card-body">

                <?php if ($ultimoPedido) { ?>

                    <?php

                    $producto = $detalleUltimoPedido[0];

                    ?>

                    <div class="row align-items-center">

                        <!--==========================
                    Imagen
                    ===========================-->

                        <div class="col-lg-3 text-center">

                            <?php

                            if (!empty($producto["idImagen"])) {

                                $imagen = "mostrar_imagen.php?id=" . $producto["idImagen"];
                            } else {

                                $imagen = "assets/img/sin_imagen.png";
                            }

                            ?>

                            <img
                                src="<?= $imagen ?>"
                                class="img-fluid rounded border"
                                style="max-height:150px;object-fit:contain;">

                        </div>

                        <!--==========================
                    Información
                    ===========================-->

                        <div class="col-lg-6">

                            <h5 class="fw-bold">

                                <?= htmlspecialchars($producto["nombre"]); ?>

                            </h5>

                            <p class="text-muted mb-2">

                                Pedido

                                <strong>

                                    #<?= $ultimoPedido["serie"] ?>-<?= $ultimoPedido["numero"] ?>

                                </strong>

                            </p>

                            <p class="mb-1">

                                Fecha:

                                <strong>

                                    <?= date("d/m/Y", strtotime($ultimoPedido["fecha_venta"])) ?>

                                </strong>

                            </p>

                            <p class="mb-1">

                                Productos:

                                <strong>

                                    <?= count($detalleUltimoPedido) ?>

                                </strong>

                            </p>

                            <p class="mb-0">

                                Total pagado

                                <strong class="text-success fs-5">

                                    S/

                                    <?= number_format($ultimoPedido["total_venta"], 2) ?>

                                </strong>

                            </p>

                        </div>

                        <!--==========================
                    Estado
                    ===========================-->

                        <div class="col-lg-3 text-center">

                            <?php

                            switch ($ultimoPedido["estado_envio"]) {

                                case "PENDIENTE":
                                    $color = "secondary";
                                    break;

                                case "CONFIRMADO":
                                    $color = "info";
                                    break;

                                case "PREPARANDO":
                                    $color = "warning";
                                    break;

                                case "ENVIADO":
                                    $color = "primary";
                                    break;

                                case "ENTREGADO":
                                    $color = "success";
                                    break;

                                case "CANCELADO":
                                    $color = "danger";
                                    break;

                                default:
                                    $color = "dark";
                            }

                            ?>

                            <span class="badge bg-<?= $color ?> fs-6">

                                <?= $ultimoPedido["estado_envio"] ?>

                            </span>

                            <hr>

                            <a
                                href="ver_detalle_pedido_cliente.php?id=<?= $ultimoPedido["id_ticket_ventas"] ?>"
                                class="btn btn-primary w-100">

                                <i class="bi bi-eye"></i>

                                Ver detalle

                            </a>

                        </div>

                    </div>

                    <!--=========================================
                Productos del pedido
                ==========================================-->

                    <?php if (count($detalleUltimoPedido) > 1) { ?>

                        <hr>

                        <h6 class="fw-bold mb-3">

                            Productos del pedido

                        </h6>

                        <div class="table-responsive">

                            <table class="table table-sm align-middle">

                                <thead>

                                    <tr>

                                        <th>Producto</th>

                                        <th class="text-center">

                                            Cantidad

                                        </th>

                                        <th class="text-end">

                                            Subtotal

                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php foreach ($detalleUltimoPedido as $item) { ?>

                                        <tr>

                                            <td>

                                                <?= htmlspecialchars($item["nombre"]) ?>

                                            </td>

                                            <td class="text-center">

                                                <?= $item["cantidad_pedido_producto"] ?>

                                            </td>

                                            <td class="text-end">

                                                S/

                                                <?= number_format($item["sub_total"], 2) ?>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="text-center py-5">

                        <i class="bi bi-bag-x display-4 text-muted"></i>

                        <h5 class="mt-3">

                            Aún no tienes pedidos.

                        </h5>

                        <a
                            href="tienda.php"
                            class="btn btn-primary mt-3">

                            Ir a la tienda

                        </a>

                    </div>

                <?php } ?>

            </div>

        </div>

    </div>

    <!--=========================================
    ESTADO DEL ENVÍO
    ==========================================-->

    <div class="col-xl-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-truck text-success"></i>

                    Estado del envío

                </h5>

            </div>

            <div class="card-body">

                <?php if ($ultimoPedido) { ?>

                    <?php

                    function iconoPaso($activo)
                    {

                        return $activo
                            ? "check-circle-fill text-success"
                            : "circle text-secondary";
                    }

                    ?>

                    <ul class="list-group list-group-flush">

                        <li class="list-group-item">

                            <i class="bi bi-<?= iconoPaso($pasosEnvio["CONFIRMADO"]) ?>"></i>

                            Pedido confirmado

                            <?php if (!empty($ultimoPedido["fecha_confirmado"])) { ?>

                                <br>

                                <small class="text-muted">

                                    <?= date("d/m/Y H:i", strtotime($ultimoPedido["fecha_confirmado"])) ?>

                                </small>

                            <?php } ?>

                        </li>

                        <li class="list-group-item">

                            <i class="bi bi-<?= iconoPaso($pasosEnvio["PREPARANDO"]) ?>"></i>

                            Preparando pedido

                            <?php if (!empty($ultimoPedido["fecha_preparando"])) { ?>

                                <br>

                                <small class="text-muted">

                                    <?= date("d/m/Y H:i", strtotime($ultimoPedido["fecha_preparando"])) ?>

                                </small>

                            <?php } ?>

                        </li>

                        <li class="list-group-item">

                            <i class="bi bi-<?= iconoPaso($pasosEnvio["ENVIADO"]) ?>"></i>

                            Pedido enviado

                            <?php if (!empty($ultimoPedido["fecha_enviado"])) { ?>

                                <br>

                                <small class="text-muted">

                                    <?= date("d/m/Y H:i", strtotime($ultimoPedido["fecha_enviado"])) ?>

                                </small>

                            <?php } ?>

                        </li>

                        <li class="list-group-item">

                            <i class="bi bi-<?= iconoPaso($pasosEnvio["ENTREGADO"]) ?>"></i>

                            Pedido entregado

                        </li>

                        <?php if ($pasosEnvio["CANCELADO"]) { ?>

                            <li class="list-group-item text-danger">

                                <i class="bi bi-x-circle-fill"></i>

                                Pedido cancelado

                                <?php if (!empty($ultimoPedido["fecha_cancelado"])) { ?>

                                    <br>

                                    <small>

                                        <?= date("d/m/Y H:i", strtotime($ultimoPedido["fecha_cancelado"])) ?>

                                    </small>

                                <?php } ?>

                            </li>

                        <?php } ?>

                    </ul>

                    <hr>

                    <?php

                    switch ($estadoEnvio) {

                        case "PENDIENTE":

                            $porcentaje = 5;
                            break;

                        case "CONFIRMADO":

                            $porcentaje = 25;
                            break;

                        case "PREPARANDO":

                            $porcentaje = 55;
                            break;

                        case "ENVIADO":

                            $porcentaje = 80;
                            break;

                        case "ENTREGADO":

                            $porcentaje = 100;
                            break;

                        default:

                            $porcentaje = 0;
                    }

                    ?>

                    <?php if ($estadoEnvio != "CANCELADO") { ?>

                        <div class="mt-3">

                            <small class="fw-semibold">

                                Progreso

                            </small>

                            <div class="progress mt-2" style="height:10px;">

                                <div
                                    class="progress-bar bg-success"
                                    style="width:<?= $porcentaje ?>%">

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="text-center py-5">

                        <i class="bi bi-truck display-4 text-secondary"></i>

                        <h6 class="mt-3">

                            No existen envíos.

                        </h6>

                    </div>

                <?php } ?>

            </div>

        </div>

    </div>
</div>

<!--======================================================
ACTIVIDAD
=======================================================-->

<div class="row mt-4">

    <!-- Actividad -->

    <div class="col-lg-7">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-clock-history text-primary"></i>

                    Actividad reciente

                </h5>

            </div>

            <div class="card-body">

                <?php if (count($actividad) > 0) { ?>

                    <?php foreach ($actividad as $item) { ?>

                        <?php

                        switch ($item["tipo"]) {

                            case "COMPRA":

                                $icono = "bag-check-fill";
                                $color = "success";

                                break;

                            case "FAVORITO":

                                $icono = "heart-fill";
                                $color = "danger";

                                break;

                            case "TESTIMONIO":

                                $icono = "star-fill";
                                $color = "warning";

                                break;

                            default:

                                $icono = "clock-history";
                                $color = "secondary";
                        }

                        ?>

                        <div class="d-flex mb-4">

                            <div class="me-3">

                                <div class="bg-<?= $color ?> rounded-circle p-2 text-white">

                                    <i class="bi bi-<?= $icono ?>"></i>

                                </div>

                            </div>

                            <div class="flex-grow-1">

                                <strong>

                                    <?= htmlspecialchars($item["titulo"]) ?>

                                </strong>

                                <br>

                                <small class="text-muted">

                                    <?= htmlspecialchars($item["descripcion"]) ?>

                                </small>

                                <br>

                                <small class="text-secondary">

                                    <?= date("d/m/Y H:i", strtotime($item["fecha"])) ?>

                                </small>

                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="text-center py-5">

                        <i class="bi bi-clock-history display-5 text-muted"></i>

                        <h6 class="mt-3">

                            Aún no existe actividad.

                        </h6>

                    </div>

                <?php } ?>

            </div>

        </div>

    </div>

    <!-- Producto Favorito -->

    <div class="col-lg-5">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-heart-fill text-danger"></i>

                    Producto favorito

                </h5>

            </div>

            <div class="card-body">

                <?php if ($productoFavorito) { ?>

                    <div class="text-center">

                        <?php

                        if (!empty($productoFavorito["idImagen"])) {

                            $imagen = "mostrar_imagen.php?id=" . $productoFavorito["idImagen"];
                        } else {

                            $imagen = "assets/img/sin_imagen.png";
                        }

                        ?>

                        <img

                            src="<?= $imagen ?>"

                            class="img-fluid rounded border mb-3"

                            style="max-height:180px;object-fit:contain;">

                        <h5 class="fw-bold">

                            <?= htmlspecialchars($productoFavorito["nombre"]) ?>

                        </h5>

                        <?php if ($productoFavorito["oferta"] == 1 && $productoFavorito["precio_anterior"] > 0) { ?>

                            <div>

                                <span class="text-muted text-decoration-line-through">

                                    S/ <?= number_format($productoFavorito["precio_anterior"], 2) ?>

                                </span>

                            </div>

                        <?php } ?>

                        <div class="fs-3 fw-bold text-success">

                            S/ <?= number_format($productoFavorito["precio"], 2) ?>

                        </div>

                        <div class="mt-2">

                            <?php

                            if ($productoFavorito["stock"] > 0) {

                                echo '<span class="badge bg-success">Disponible</span>';
                            } else {

                                echo '<span class="badge bg-danger">Sin stock</span>';
                            }

                            ?>

                            <?php if ($productoFavorito["envio_gratis"]) { ?>

                                <span class="badge bg-primary">

                                    Envío gratis

                                </span>

                            <?php } ?>

                        </div>

                        <div class="d-grid gap-2 mt-4">

                            <button
                                class="btn btn-outline-secondary btnVista"

                                data-id="<?= $productoFavorito['idProducto']; ?>"
                                >

                                <i class="bi bi-eye-fill"></i>

                                Ver producto

                            </button>
                        </div>

                        <small class="text-muted d-block mt-3">

                            Agregado a favoritos el

                            <?= date("d/m/Y", strtotime($productoFavorito["fecha"])) ?>

                        </small>

                    </div>

                <?php } else { ?>

                    <div class="text-center py-5">

                        <i class="bi bi-heart display-1 text-muted"></i>

                        <h5 class="mt-3">

                            No tienes productos favoritos

                        </h5>

                        <p class="text-muted">

                            Agrega productos a favoritos para verlos aquí.

                        </p>

                        <a

                            href="tienda.php"

                            class="btn btn-primary">

                            Ir a la tienda

                        </a>

                    </div>

                <?php } ?>

            </div>

        </div>

    </div>

</div>