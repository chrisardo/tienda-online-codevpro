<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_obtener_detalle_pedido.php
// Módulo: Gestión de Pedidos de Clientes
// Sistema: Inventa
//=====================================================

session_start();

require_once "../controladores/conexion.php";

//=====================================================
// VARIABLES
//=====================================================

$idUser = intval($_SESSION["idUser"] ?? 0);

$idPedido = intval($_GET["id"] ?? 0);

//=====================================================
// VALIDAR
//=====================================================

if (!$idUser || !$idPedido) {

    echo '
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Pedido inválido.
        </div>
    ';

    exit;
}


//=====================================================
// OBTENER PEDIDO
//
// IMPORTANTE:
// tv.id_empleado corresponde al repartidor asignado.
//=====================================================

$sql = "SELECT

            tv.*,

            c.nombre AS cliente,
            c.email,
            c.celular,
            c.direccion,

            mp.nombre AS metodo_pago

        FROM ticket_ventas tv

        INNER JOIN clientes c
        ON tv.idCliente = c.idCliente

        LEFT JOIN metodo_pago mp
        ON tv.id_metodo_pago = mp.id_metodo_pago

        WHERE tv.id_ticket_ventas = ?
        AND tv.id_user = ?

        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo '
        <div class="alert alert-danger">
            Error preparando la consulta del pedido.
        </div>
    ';

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idPedido,
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$pedido = mysqli_fetch_assoc($resultado);

mysqli_stmt_close($stmt);


//=====================================================
// VALIDAR PEDIDO
//=====================================================

if (!$pedido) {

    echo '
        <div class="alert alert-warning">
            <i class="bi bi-info-circle-fill me-2"></i>
            No se encontró el pedido.
        </div>
    ';

    exit;
}


//=====================================================
// OBTENER REPARTIDOR
//
// Se consulta solamente si el pedido tiene
// un empleado/repartidor asignado.
//
// Se utiliza LEFT JOIN porque algunos pedidos
// pueden no tener repartidor.
//=====================================================

$repartidor = null;

$idEmpleado = intval($pedido["id_empleado"] ?? 0);


//=====================================================
// SI EXISTE REPARTIDOR
//=====================================================

if ($idEmpleado > 0) {

    $sqlRepartidor = "SELECT

                        e.id_empleado,

                        e.nombre,
                        e.apellido,

                        e.celular,
                        e.email,

                        e.direccion,

                        e.estado,

                        r.nombre AS rol

                      FROM empleados e

                      LEFT JOIN rol r
                      ON e.id_rol = r.id_rol

                      WHERE e.id_empleado = ?

                      LIMIT 1";

    $stmtRepartidor = mysqli_prepare(
        $conexion,
        $sqlRepartidor
    );

    if ($stmtRepartidor) {

        mysqli_stmt_bind_param(
            $stmtRepartidor,
            "i",
            $idEmpleado
        );

        mysqli_stmt_execute(
            $stmtRepartidor
        );

        $resultadoRepartidor =
            mysqli_stmt_get_result(
                $stmtRepartidor
            );

        $repartidor =
            mysqli_fetch_assoc(
                $resultadoRepartidor
            );

        mysqli_stmt_close(
            $stmtRepartidor
        );
    }
}


//=====================================================
// PRODUCTOS DEL PEDIDO
//=====================================================

$sqlProductos = "SELECT

                    dtv.*,

                    p.nombre,
                    p.precio,

                    (
                        SELECT imagenes
                        FROM imagenes
                        WHERE idProducto = p.idProducto
                        ORDER BY orden ASC
                        LIMIT 1
                    ) AS imagen

                FROM detalle_ticket_ventas dtv

                INNER JOIN producto p
                ON dtv.idProducto = p.idProducto

                WHERE dtv.id_ticket_ventas = ?";

$stmtProductos = mysqli_prepare(
    $conexion,
    $sqlProductos
);

if (!$stmtProductos) {

    echo '
        <div class="alert alert-danger">
            Error obteniendo los productos del pedido.
        </div>
    ';

    exit;
}

mysqli_stmt_bind_param(
    $stmtProductos,
    "i",
    $idPedido
);

mysqli_stmt_execute(
    $stmtProductos
);

$resultadoProductos =
    mysqli_stmt_get_result(
        $stmtProductos
    );

?>

<!--=====================================================
    CONTENEDOR PRINCIPAL
=====================================================-->

<div class="container-fluid">


    <!--=================================================
        CABECERA
    =================================================-->

    <div class="row mb-4">

        <div class="col-md-6">

            <h4 class="fw-bold">

                <i class="bi bi-receipt-cutoff me-2"></i>

                Pedido #<?= intval($pedido["id_ticket_ventas"]); ?>

            </h4>

            <p class="text-muted mb-0">

                <?= date(
                    "d/m/Y",
                    strtotime($pedido["fecha_venta"])
                ); ?>

                -

                <?= htmlspecialchars(
                    $pedido["hora_venta"] ?? ""
                ); ?>

            </p>

        </div>


        <div class="col-md-6 text-md-end">

            <?php

            $estadoPedido = strtoupper(
                trim(
                    $pedido["estado_envio"] ?? ""
                )
            );

            $badge = "secondary";

            switch ($estadoPedido) {

                case "PENDIENTE":
                    $badge = "warning";
                    break;

                case "CONFIRMADO":
                    $badge = "info";
                    break;

                case "PREPARANDO":
                    $badge = "primary";
                    break;

                case "ASIGNADO":
                    $badge = "primary";
                    break;

                case "OBTENIDO":
                    $badge = "secondary";
                    break;

                case "ENVIADO":
                    $badge = "secondary";
                    break;

                case "ENTREGADO":
                    $badge = "success";
                    break;

                case "NO_ENTREGADO":
                    $badge = "danger";
                    break;

                case "CANCELADO":
                    $badge = "danger";
                    break;
            }

            ?>

            <span class="badge bg-<?= $badge; ?> fs-6">

                <?= htmlspecialchars(
                    $pedido["estado_envio"] ?? ""
                ); ?>

            </span>

        </div>

    </div>


    <!--=================================================
        DATOS DEL CLIENTE
    =================================================-->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-light">

            <strong>

                <i class="bi bi-person-fill me-2"></i>

                Datos del Cliente

            </strong>

        </div>


        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3 mb-md-0">

                    <strong>Cliente:</strong>

                    <div class="mt-1">

                        <?= htmlspecialchars(
                            $pedido["cliente"] ?? ""
                        ); ?>

                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Email:</strong>

                    <div class="mt-1">

                        <?= htmlspecialchars(
                            $pedido["email"] ?? ""
                        ); ?>

                    </div>

                </div>

            </div>


            <hr>


            <div class="row">

                <div class="col-md-6 mb-3 mb-md-0">

                    <strong>Celular:</strong>

                    <div class="mt-1">

                        <?= htmlspecialchars(
                            $pedido["celular"] ?? ""
                        ); ?>

                    </div>

                </div>


                <div class="col-md-6">

                    <strong>Dirección de entrega:</strong>

                    <div class="mt-1">

                        <?= htmlspecialchars(
                            $pedido["direccion_envio"] ?? ""
                        ); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <?php
    //=================================================
    // DATOS DEL REPARTIDOR
    //
    // SE MUESTRA SI:
    //
    // id_empleado > 0
    //
    // Y EL EMPLEADO EXISTE.
    //
    // Esto incluye también pedidos ENTREGADOS.
    //=================================================
    ?>

    <?php if ($repartidor): ?>

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-light">

                <strong>

                    <i class="bi bi-person-badge-fill me-2 text-primary"></i>

                    Datos del Repartidor

                </strong>

            </div>


            <div class="card-body">

                <div class="row align-items-center">

                    <!--=================================
                        ICONO
                    =================================-->

                    <div class="col-md-2 text-center mb-3 mb-md-0">

                        <div
                            class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                            style="width:80px;height:80px;">

                            <i
                                class="bi bi-person-badge-fill text-primary"
                                style="font-size:40px;">
                            </i>

                        </div>

                    </div>


                    <!--=================================
                        DATOS PRINCIPALES
                    =================================-->

                    <div class="col-md-5 mb-3 mb-md-0">

                        <div class="fw-bold fs-5">

                            <?= htmlspecialchars(
                                trim(
                                    ($repartidor["nombre"] ?? "") .
                                        " " .
                                        ($repartidor["apellido"] ?? "")
                                )
                            ); ?>

                        </div>


                        <div class="text-muted small mt-1">

                            <i class="bi bi-person-badge me-1"></i>

                            <?= htmlspecialchars(
                                $repartidor["rol"] ??
                                    "Repartidor"
                            ); ?>

                        </div>


                        <?php if (!empty($repartidor["estado"])): ?>

                            <div class="mt-2">

                                <?php

                                $estadoEmpleado =
                                    strtoupper(
                                        trim(
                                            $repartidor["estado"]
                                        )
                                    );

                                $badgeEmpleado =
                                    $estadoEmpleado === "ACTIVO"
                                    ? "success"
                                    : "secondary";

                                ?>

                                <span
                                    class="badge bg-<?= $badgeEmpleado; ?>">

                                    <?= htmlspecialchars(
                                        $repartidor["estado"]
                                    ); ?>

                                </span>

                            </div>

                        <?php endif; ?>

                    </div>


                    <!--=================================
    CONTACTO DEL REPARTIDOR
=================================-->

                    <div class="col-md-5">

                        <?php if (!empty($repartidor["celular"])): ?>

                            <?php

                            //=================================================
                            // NÚMERO ORIGINAL
                            //=================================================

                            $celularRepartidor = trim(
                                (string)$repartidor["celular"]
                            );

                            //=================================================
                            // PREPARAR NÚMERO PARA WHATSAPP
                            //
                            // Se eliminan:
                            // espacios
                            // +
                            // -
                            // paréntesis
                            // puntos
                            // cualquier carácter no numérico
                            //=================================================

                            $numeroWhatsApp = preg_replace(
                                '/[^0-9]/',
                                '',
                                $celularRepartidor
                            );

                            //=================================================
                            // SI EL NÚMERO TIENE 9 DÍGITOS
                            //
                            // Se asume número móvil de Perú
                            // y se agrega código de país 51.
                            //=================================================

                            if (
                                strlen($numeroWhatsApp) === 9 &&
                                substr($numeroWhatsApp, 0, 1) === '9'
                            ) {

                                $numeroWhatsApp =
                                    '51' . $numeroWhatsApp;
                            }

                            //=================================================
                            // URL WHATSAPP
                            //=================================================

                            $urlWhatsApp =
                                'https://wa.me/' . $numeroWhatsApp;

                            ?>

                            <div class="mb-3">

                                <i
                                    class="bi bi-whatsapp text-success me-2">
                                </i>

                                <strong>WhatsApp:</strong>

                                <div class="mt-1">

                                    <a
                                        href="<?= htmlspecialchars(
                                                    $urlWhatsApp,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-success text-decoration-none fw-semibold">

                                        <i class="bi bi-whatsapp me-1"></i>

                                        <?= htmlspecialchars(
                                            $celularRepartidor,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </a>

                                </div>

                            </div>

                        <?php endif; ?>


                        <?php if (!empty($repartidor["email"])): ?>

                            <?php

                            $emailRepartidor = trim(
                                (string)$repartidor["email"]
                            );

                            ?>

                            <div class="mb-3">

                                <i
                                    class="bi bi-envelope-fill text-primary me-2">
                                </i>

                                <strong>Email:</strong>

                                <div class="mt-1">

                                    <a
                                        href="mailto:<?= htmlspecialchars(
                                                            $emailRepartidor,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                        class="text-primary text-decoration-none fw-semibold">

                                        <i class="bi bi-envelope me-1"></i>

                                        <?= htmlspecialchars(
                                            $emailRepartidor,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </a>

                                </div>

                            </div>

                        <?php endif; ?>


                        <?php if (!empty($repartidor["direccion"])): ?>

                            <div>

                                <i
                                    class="bi bi-geo-alt-fill text-primary me-2">
                                </i>

                                <strong>Dirección:</strong>

                                <div class="ms-4 mt-1 text-muted">

                                    <?= htmlspecialchars(
                                        $repartidor["direccion"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>
                </div>


                <!--=====================================
                    INDICADOR DE PEDIDO ENTREGADO
                =====================================-->

                <?php if ($estadoPedido === "ENTREGADO"): ?>

                    <hr>

                    <div
                        class="alert alert-success mb-0 d-flex align-items-center">

                        <i
                            class="bi bi-check-circle-fill fs-5 me-2">
                        </i>

                        <div>

                            <strong>Pedido entregado</strong>

                            <div class="small">

                                Este pedido fue entregado por el
                                repartidor mostrado arriba.

                            </div>

                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>


    <!--=================================================
        PRODUCTOS
    =================================================-->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-light">

            <strong>

                <i class="bi bi-box-seam-fill me-2"></i>

                Productos Comprados

            </strong>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>Imagen</th>

                            <th>Producto</th>

                            <th>Cantidad</th>

                            <th>Precio</th>

                            <th>Subtotal</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $hayProductos = false;

                        ?>

                        <?php while (
                            $producto =
                            mysqli_fetch_assoc(
                                $resultadoProductos
                            )
                        ): ?>

                            <?php

                            $hayProductos = true;

                            if (
                                !empty($producto["imagen"])
                            ) {

                                $imagen =
                                    "data:image/jpeg;base64," .
                                    base64_encode(
                                        $producto["imagen"]
                                    );
                            } else {

                                $imagen =
                                    "../assets/img/sin_imagen.png";
                            }

                            ?>

                            <tr>

                                <td width="80">

                                    <img
                                        src="<?= htmlspecialchars(
                                                    $imagen
                                                ); ?>"
                                        class="img-fluid rounded"
                                        style="
                                            width:60px;
                                            height:60px;
                                            object-fit:cover;
                                        "
                                        alt="Producto">

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $producto["nombre"] ?? ""
                                    ); ?>

                                </td>


                                <td>

                                    <?= intval(
                                        $producto["cantidad_pedido_producto"] ?? 0
                                    ); ?>

                                </td>


                                <td>

                                    S/.

                                    <?= number_format(
                                        (float)(
                                            $producto["precio"] ?? 0
                                        ),
                                        2
                                    ); ?>

                                </td>


                                <td>

                                    <strong>

                                        S/.

                                        <?= number_format(
                                            (float)(
                                                $producto["sub_total"] ?? 0
                                            ),
                                            2
                                        ); ?>

                                    </strong>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                        <?php if (!$hayProductos): ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center text-muted py-4">

                                    <i
                                        class="bi bi-box-seam me-2">
                                    </i>

                                    No hay productos registrados
                                    para este pedido.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!--=================================================
        RESUMEN
    =================================================-->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-light">

            <strong>

                <i class="bi bi-calculator-fill me-2"></i>

                Resumen del Pedido

            </strong>

        </div>


        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <p>

                        <strong>
                            Método de Pago:
                        </strong>

                        <?= htmlspecialchars(
                            $pedido["metodo_pago"] ?? ""
                        ); ?>

                    </p>


                    <p>

                        <strong>
                            Comprobante:
                        </strong>

                        <?= htmlspecialchars(
                            $pedido["tipo_comprobante"] ?? ""
                        ); ?>

                    </p>


                    <p>

                        <strong>
                            Serie:
                        </strong>

                        <?= htmlspecialchars(
                            $pedido["serie"] ?? ""
                        ); ?>

                        -

                        <?= htmlspecialchars(
                            $pedido["numero"] ?? ""
                        ); ?>

                    </p>

                </div>


                <div class="col-md-6 text-md-end">

                    <h4 class="fw-bold text-success">

                        Total:

                        S/.

                        <?= number_format(
                            (float)(
                                $pedido["total_venta"] ?? 0
                            ),
                            2
                        ); ?>

                    </h4>

                </div>

            </div>

        </div>

    </div>


    <!--=================================================
        TIMELINE
    =================================================-->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-light">

            <strong>

                <i class="bi bi-clock-history me-2"></i>

                Seguimiento del Pedido

            </strong>

        </div>


        <div class="card-body">

            <ul class="list-group list-group-flush">


                <!-- PEDIDO REGISTRADO -->

                <li class="list-group-item">

                    🟡 Pedido registrado

                    <div class="small text-muted">

                        <?= htmlspecialchars(
                            $pedido["fecha_venta"] ?? ""
                        ); ?>

                        <?= htmlspecialchars(
                            $pedido["hora_venta"] ?? ""
                        ); ?>

                    </div>

                </li>


                <!-- CONFIRMADO -->

                <?php if (
                    !empty($pedido["fecha_confirmado"])
                ): ?>

                    <li class="list-group-item">

                        🔵 Pedido confirmado

                        <div class="small text-muted">

                            <?= htmlspecialchars(
                                $pedido["fecha_confirmado"]
                            ); ?>

                        </div>

                    </li>

                <?php endif; ?>


                <!-- PREPARANDO -->

                <?php if (
                    !empty($pedido["fecha_preparando"])
                ): ?>

                    <li class="list-group-item">

                        🟣 Pedido preparando

                        <div class="small text-muted">

                            <?= htmlspecialchars(
                                $pedido["fecha_preparando"]
                            ); ?>

                        </div>

                    </li>

                <?php endif; ?>


                <!-- ENVIADO -->

                <?php if (
                    !empty($pedido["fecha_enviado"])
                ): ?>

                    <li class="list-group-item">

                        🚚 Pedido enviado

                        <div class="small text-muted">

                            <?= htmlspecialchars(
                                $pedido["fecha_enviado"]
                            ); ?>

                        </div>

                    </li>

                <?php endif; ?>


                <!-- ENTREGADO -->

                <?php if (
                    !empty($pedido["fecha_entregado"])
                ): ?>

                    <li
                        class="list-group-item text-success">

                        ✅ Pedido entregado

                        <div class="small text-muted">

                            <?= htmlspecialchars(
                                $pedido["fecha_entregado"]
                            ); ?>

                        </div>

                    </li>

                <?php endif; ?>


                <!-- CANCELADO -->

                <?php if (
                    $estadoPedido === "CANCELADO"
                ): ?>

                    <li
                        class="list-group-item text-danger">

                        ❌ Pedido cancelado

                        <div class="small text-muted">

                            <?= htmlspecialchars(
                                $pedido["fecha_cancelado"] ?? ""
                            ); ?>

                        </div>

                    </li>

                <?php endif; ?>


            </ul>

        </div>

    </div>


</div>

<?php

//=====================================================
// CERRAR STATEMENT DE PRODUCTOS
//=====================================================

mysqli_stmt_close(
    $stmtProductos
);

?>