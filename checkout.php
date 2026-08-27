<?php
//======================================================
// CoDevPro Technology
// Archivo: checkout.php
// Módulo: Checkout / Finalizar Compra
// Sistema: Inventa
//======================================================

session_start();


//======================================================
// VALIDAR CLIENTE
//======================================================

if (
    !isset($_SESSION["idCliente"]) ||
    intval($_SESSION["idCliente"]) <= 0
) {
    header("Location: login.php");
    exit();
}


//======================================================
// CONEXIÓN
//======================================================

require_once "controladores/conexion.php";
require_once "controladores/token_carrito.php";


//======================================================
// TOKEN CARRITO
//======================================================

$token = obtenerTokenCarrito();


//======================================================
// ID CLIENTE
//======================================================

$idCliente = intval($_SESSION["idCliente"]);


//======================================================
// DATOS DE LA EMPRESA
//======================================================

$sqlEmpresa = "
    SELECT *
    FROM usuario_acceso
    ORDER BY id_user ASC
    LIMIT 1
";

$resEmpresa = mysqli_query(
    $conexion,
    $sqlEmpresa
);

$empresa = mysqli_fetch_assoc(
    $resEmpresa
);


//======================================================
// OBTENER CLIENTE
//======================================================

$sqlCliente = "
    SELECT *
    FROM clientes
    WHERE idCliente = ?
";

$stmtCliente = mysqli_prepare(
    $conexion,
    $sqlCliente
);

if (!$stmtCliente) {
    die("Error al preparar consulta del cliente.");
}

mysqli_stmt_bind_param(
    $stmtCliente,
    "i",
    $idCliente
);

mysqli_stmt_execute(
    $stmtCliente
);

$resultadoCliente =
    mysqli_stmt_get_result(
        $stmtCliente
    );

$cliente = mysqli_fetch_assoc(
    $resultadoCliente
);

mysqli_stmt_close(
    $stmtCliente
);


//======================================================
// VALIDAR CLIENTE
//======================================================

if (!$cliente) {

    session_destroy();

    header("Location: login.php");

    exit();
}


//======================================================
// MÉTODOS DE PAGO
//======================================================

$sqlMetodoPago = "
    SELECT *
    FROM metodo_pago
    WHERE Eliminado = 0
    ORDER BY nombre ASC
";

$metodosPago = mysqli_query(
    $conexion,
    $sqlMetodoPago
);


//======================================================
// OBTENER PRODUCTOS DEL CARRITO
//======================================================
//
// IMPORTANTE:
//
// El precio utilizado SIEMPRE es el precio actual
// de producto.precio.
//
// El impuesto se calcula UNA SOLA VEZ POR CADA
// PRODUCTO/LÍNEA DEL CARRITO.
//
// La cantidad NO multiplica el impuesto.
//
// Ejemplo:
//
// Precio = S/ 100
// Cantidad = 5
// IGV = 18%
//
// Subtotal = S/ 500
// Impuesto = S/ 18
//
// NO = S/ 90.
//
//======================================================

$sql = "
    SELECT

        c.idCarrito,
        c.idProducto,
        c.cantidad,

        p.precio,
        p.aplica_impuesto,
        p.id_user,

        p.nombre,
        p.codigo,
        p.stock,

        (
            SELECT id_imagen
            FROM imagenes
            WHERE idProducto = p.idProducto
            ORDER BY orden ASC, id_imagen ASC
            LIMIT 1
        ) AS imagen

    FROM carrito_online c

    INNER JOIN producto p
        ON p.idProducto = c.idProducto

    WHERE c.idCliente = ?
    AND c.estado = 'pendiente'
    AND p.Eliminado = 0

    ORDER BY c.idCarrito DESC
";

$stmtProductos = mysqli_prepare(
    $conexion,
    $sql
);

if (!$stmtProductos) {
    die("Error al preparar consulta del carrito.");
}

mysqli_stmt_bind_param(
    $stmtProductos,
    "i",
    $idCliente
);

mysqli_stmt_execute(
    $stmtProductos
);

$productos = mysqli_stmt_get_result(
    $stmtProductos
);


//======================================================
// VALIDAR CARRITO
//======================================================

if (
    !$productos ||
    mysqli_num_rows($productos) == 0
) {

    mysqli_stmt_close(
        $stmtProductos
    );

    header("Location: carrito.php");

    exit();
}


//======================================================
// VARIABLES DEL CARRITO
//======================================================

$subtotal = 0;

$impuestoTotal = 0;

$total = 0;

$totalProductos = 0;


//======================================================
// VARIABLES DE IMPUESTO
//======================================================

$nombreImpuesto = "Impuesto";

$porcentajeImpuesto = 0;

$impuestoActivo = 0;

$preciosIncluyenImpuesto = 0;

$idUser = 0;


//======================================================
// OBTENER id_user
//======================================================
//
// Se toma el propietario del primer producto.
//
//======================================================

mysqli_data_seek(
    $productos,
    0
);

if (
    $primerProducto =
    mysqli_fetch_assoc($productos)
) {

    $idUser = intval(
        $primerProducto["id_user"] ?? 0
    );
}


//======================================================
// RESPALDO id_user
//======================================================

if ($idUser <= 0) {

    $sqlEmpresaImpuesto = "
        SELECT id_user
        FROM usuario_acceso
        ORDER BY id_user ASC
        LIMIT 1
    ";

    $resultadoEmpresaImpuesto =
        mysqli_query(
            $conexion,
            $sqlEmpresaImpuesto
        );

    if ($resultadoEmpresaImpuesto) {

        $filaEmpresa =
            mysqli_fetch_assoc(
                $resultadoEmpresaImpuesto
            );

        $idUser = intval(
            $filaEmpresa["id_user"] ?? 0
        );
    }
}


//======================================================
// OBTENER CONFIGURACIÓN TRIBUTARIA
//======================================================

if ($idUser > 0) {

    $sqlConfiguracion = "
        SELECT

            impuesto_activo,
            nombre_impuesto,
            porcentaje_impuesto,
            precios_incluyen_impuesto

        FROM configuracion_monedas_impuestos

        WHERE id_user = ?

        ORDER BY id_configuracion DESC

        LIMIT 1
    ";

    $stmtConfiguracion =
        mysqli_prepare(
            $conexion,
            $sqlConfiguracion
        );

    if ($stmtConfiguracion) {

        mysqli_stmt_bind_param(
            $stmtConfiguracion,
            "i",
            $idUser
        );

        mysqli_stmt_execute(
            $stmtConfiguracion
        );

        $resultadoConfiguracion =
            mysqli_stmt_get_result(
                $stmtConfiguracion
            );

        if (
            $configuracion =
            mysqli_fetch_assoc(
                $resultadoConfiguracion
            )
        ) {

            //==================================================
            // IMPUESTO ACTIVO
            //==================================================

            $impuestoActivo = intval(
                $configuracion["impuesto_activo"] ?? 0
            );


            //==================================================
            // NOMBRE
            //==================================================

            $nombreImpuesto = trim(
                $configuracion["nombre_impuesto"] ?? ""
            );

            if ($nombreImpuesto === "") {
                $nombreImpuesto = "Impuesto";
            }


            //==================================================
            // PORCENTAJE
            //==================================================

            $porcentajeImpuesto = floatval(
                $configuracion["porcentaje_impuesto"] ?? 0
            );


            //==================================================
            // PRECIOS INCLUYEN IMPUESTO
            //==================================================

            $preciosIncluyenImpuesto = intval(
                $configuracion["precios_incluyen_impuesto"] ?? 0
            );
        }

        mysqli_stmt_close(
            $stmtConfiguracion
        );
    }
}


//======================================================
// VOLVER AL PRIMER PRODUCTO
//======================================================

mysqli_data_seek(
    $productos,
    0
);


//======================================================
// PROCESAR PRODUCTOS
//======================================================

while (
    $item =
    mysqli_fetch_assoc($productos)
) {

    //==================================================
    // CANTIDAD
    //==================================================

    $cantidad = intval(
        $item["cantidad"] ?? 0
    );

    if ($cantidad <= 0) {
        continue;
    }


    //==================================================
    // PRECIO ACTUAL
    //==================================================

    $precio = floatval(
        $item["precio"] ?? 0
    );


    //==================================================
    // SUBTOTAL DE LA LÍNEA
    //==================================================

    $subtotalProducto =
        $cantidad * $precio;


    //==================================================
    // TOTAL DE UNIDADES
    //==================================================

    $totalProductos += $cantidad;


    //==================================================
    // ACUMULAR SUBTOTAL
    //==================================================

    $subtotal +=
        $subtotalProducto;


    //==================================================
    // APLICA IMPUESTO
    //==================================================

    $aplicaImpuestoProducto =
        intval(
            $item["aplica_impuesto"] ?? 0
        );


    //==================================================
    // IMPUESTO DEL PRODUCTO
    //==================================================

    $impuestoProducto = 0;


    //==================================================
    // CALCULAR IMPUESTO
    //======================================================
    //
    // IMPORTANTE:
    //
    // El impuesto se calcula UNA SOLA VEZ por producto.
    //
    // NO se utiliza:
    //
    //     $subtotalProducto * porcentaje
    //
    // porque eso multiplicaría el impuesto por cantidad.
    //
    // Se utiliza únicamente el precio de UNA unidad.
    //
    //======================================================

    if (
        $impuestoActivo == 1 &&
        $aplicaImpuestoProducto == 1 &&
        $porcentajeImpuesto > 0
    ) {

        //==================================================
        // PRECIO YA INCLUYE IMPUESTO
        //==================================================

        if (
            $preciosIncluyenImpuesto == 1
        ) {

            $baseUnitario =
                $precio /
                (
                    1 +
                    (
                        $porcentajeImpuesto / 100
                    )
                );

            $impuestoProducto =
                $precio -
                $baseUnitario;

        } else {

            //==================================================
            // PRECIO SIN IMPUESTO
            //==================================================

            $impuestoProducto =
                $precio *
                (
                    $porcentajeImpuesto / 100
                );
        }
    }


    //==================================================
    // ACUMULAR IMPUESTO
    //==================================================

    $impuestoTotal +=
        $impuestoProducto;
}


//======================================================
// CERRAR STATEMENT
//======================================================

mysqli_stmt_close(
    $stmtProductos
);


//======================================================
// REDONDEAR
//======================================================

$subtotal = round(
    $subtotal,
    2
);

$impuestoTotal = round(
    $impuestoTotal,
    2
);


//======================================================
// CALCULAR TOTAL
//======================================================
//
// Si el precio ya incluye impuesto:
//
//     total = subtotal
//
// Si el precio NO incluye impuesto:
//
//     total = subtotal + impuesto
//
//======================================================

if (
    $impuestoActivo == 1 &&
    $preciosIncluyenImpuesto == 0
) {

    $total =
        $subtotal +
        $impuestoTotal;

} else {

    $total =
        $subtotal;
}


$total = round(
    $total,
    2
);

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        Checkout |
        <?= htmlspecialchars(
            $empresa["nombreEmpresa"] ?? "Tienda"
        ) ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

</head>


<body class="bg-light">


<?php include "includes/navbar.php"; ?>


<div class="container py-4">


    <!--=========================================
    BREADCRUMB
    ==========================================-->

    <nav
        aria-label="breadcrumb"
        class="mb-4">

        <ol
            class="breadcrumb bg-white rounded-3 shadow-sm px-3 py-2 mb-0">

            <li class="breadcrumb-item">

                <a
                    href="index.php"
                    class="text-decoration-none">

                    <i class="bi bi-house-door"></i>

                    Inicio

                </a>

            </li>

            <li class="breadcrumb-item">

                <a
                    href="tienda.php"
                    class="text-decoration-none">

                    Tienda

                </a>

            </li>

            <li class="breadcrumb-item">

                <a
                    href="carrito.php"
                    class="text-decoration-none">

                    Carrito

                </a>

            </li>

            <li class="breadcrumb-item active">

                Checkout

            </li>

        </ol>

    </nav>


    <!--=========================================
    BANNER
    ==========================================-->

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-lg-8">

                    <div class="d-flex align-items-center">

                        <div
                            class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                            style="width:70px;height:70px;">

                            <i
                                class="bi bi-shield-check text-white fs-2">
                            </i>

                        </div>

                        <div class="ms-4">

                            <h2 class="fw-bold mb-1">

                                Finalizar Compra

                            </h2>

                            <p class="text-muted mb-0">

                                Estás a un paso de completar tu pedido.

                                Revisa la información antes de confirmar la compra.

                            </p>

                        </div>

                    </div>

                </div>


                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">

                    <div
                        class="d-inline-block text-center bg-light rounded-4 border px-4 py-3">

                        <div class="text-muted small">

                            Productos

                        </div>

                        <div class="display-6 fw-bold text-success">

                            <?= $totalProductos ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!--=========================================
    PASOS
    ==========================================-->

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body py-4">

            <div class="row text-center">


                <div class="col-4">

                    <div class="mb-2">

                        <span
                            class="badge rounded-circle bg-success p-3">

                            <i class="bi bi-cart-fill fs-5"></i>

                        </span>

                    </div>

                    <div class="fw-semibold">

                        Carrito

                    </div>

                    <small class="text-success">

                        Completado

                    </small>

                </div>


                <div class="col-4">

                    <div class="mb-2">

                        <span
                            class="badge rounded-circle bg-primary p-3">

                            <i class="bi bi-credit-card fs-5"></i>

                        </span>

                    </div>

                    <div class="fw-bold">

                        Checkout

                    </div>

                    <small class="text-primary">

                        En proceso

                    </small>

                </div>


                <div class="col-4">

                    <div class="mb-2">

                        <span
                            class="badge rounded-circle bg-secondary p-3">

                            <i class="bi bi-check-circle fs-5"></i>

                        </span>

                    </div>

                    <div class="fw-semibold text-muted">

                        Confirmación

                    </div>

                    <small class="text-muted">

                        Pendiente

                    </small>

                </div>

            </div>


            <div
                class="progress mt-4"
                style="height:8px;">

                <div
                    class="progress-bar bg-success"
                    style="width:50%">
                </div>

            </div>

        </div>

    </div>


    <div class="row g-4">


        <!--=========================================
        DATOS CLIENTE
        ==========================================-->

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-header bg-white border-0 py-4">

                    <div class="d-flex align-items-center">

                        <div
                            class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                            style="width:55px;height:55px;">

                            <i
                                class="bi bi-person-fill text-white fs-4">
                            </i>

                        </div>

                        <div class="ms-3">

                            <h4 class="fw-bold mb-0">

                                Datos del Cliente

                            </h4>

                            <small class="text-muted">

                                Tu información ya fue verificada al iniciar sesión.

                            </small>

                        </div>

                    </div>

                </div>


                <div class="card-body">

                    <form id="formCheckout">

                        <div class="row">


                            <!--=====================================
                            INFORMACIÓN
                            ======================================-->

                            <div class="col-12 mb-4">

                                <div
                                    class="alert alert-primary border-0 rounded-4">

                                    <div
                                        class="d-flex align-items-center">

                                        <i
                                            class="bi bi-info-circle-fill fs-2 me-3">
                                        </i>

                                        <div>

                                            <strong>
                                                Información del cliente
                                            </strong>

                                            <br>

                                            Estos datos provienen de tu cuenta registrada y no pueden modificarse desde esta pantalla.

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!--=====================================
                            NOMBRE
                            ======================================-->

                            <div class="col-md-6 mb-4">

                                <div
                                    class="card border rounded-4 h-100">

                                    <div class="card-body">

                                        <small
                                            class="text-muted text-uppercase">

                                            <i
                                                class="bi bi-person-fill me-1">
                                            </i>

                                            Nombre Completo

                                        </small>

                                        <h5
                                            class="fw-bold mt-2 mb-0">

                                            <?= htmlspecialchars(
                                                $cliente["nombre"] ?? ""
                                            ) ?>

                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!--=====================================
                            DNI
                            ======================================-->

                            <div class="col-md-6 mb-4">

                                <div
                                    class="card border rounded-4 h-100">

                                    <div class="card-body">

                                        <small
                                            class="text-muted text-uppercase">

                                            <i
                                                class="bi bi-card-text me-1">
                                            </i>

                                            DNI / RUC

                                        </small>

                                        <h5
                                            class="fw-bold mt-2 mb-0">

                                            <?= htmlspecialchars(
                                                $cliente["dni_o_ruc"] ?? ""
                                            ) ?>

                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!--=====================================
                            CELULAR
                            ======================================-->

                            <div class="col-md-6 mb-4">

                                <div
                                    class="card border rounded-4 h-100">

                                    <div class="card-body">

                                        <small
                                            class="text-muted text-uppercase">

                                            <i
                                                class="bi bi-phone-fill me-1">
                                            </i>

                                            Celular

                                        </small>

                                        <h5
                                            class="fw-bold mt-2 mb-0">

                                            <?= htmlspecialchars(
                                                $cliente["celular"] ?? ""
                                            ) ?>

                                        </h5>

                                    </div>

                                </div>

                            </div>


                            <!--=====================================
                            CORREO
                            ======================================-->

                            <div class="col-md-6 mb-4">

                                <div
                                    class="card border rounded-4 h-100">

                                    <div class="card-body">

                                        <small
                                            class="text-muted text-uppercase">

                                            <i
                                                class="bi bi-envelope-fill me-1">
                                            </i>

                                            Correo Electrónico

                                        </small>

                                        <h6
                                            class="fw-semibold mt-2 mb-0">

                                            <?= htmlspecialchars(
                                                $cliente["email"] ?? ""
                                            ) ?>

                                        </h6>

                                    </div>

                                </div>

                            </div>


                            <!--=====================================
                            DIRECCIÓN
                            ======================================-->

                            <div class="col-12 mb-4">

                                <div
                                    class="card border-primary rounded-4">

                                    <div
                                        class="card-header bg-primary text-white rounded-top-4">

                                        <h5 class="mb-0">

                                            <i
                                                class="bi bi-geo-alt-fill me-2">
                                            </i>

                                            Dirección de entrega

                                        </h5>

                                    </div>

                                    <div class="card-body">

                                        <p class="text-muted">

                                            Puedes cambiar la dirección únicamente para este pedido.

                                        </p>

                                        <textarea
                                            class="form-control form-control-lg"
                                            id="direccion"
                                            name="direccion"
                                            rows="4"
                                            required><?= htmlspecialchars(
                                                            $cliente["direccion"] ?? ""
                                                        ) ?></textarea>


                                        <div
                                            class="form-check mt-3">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                id="guardarDireccion"
                                                name="guardarDireccion">

                                            <label
                                                class="form-check-label"
                                                for="guardarDireccion">

                                                Guardar esta dirección como mi dirección principal.

                                            </label>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!--=====================================
                            OBSERVACIONES
                            ======================================-->

                            <div class="col-12">

                                <div
                                    class="card rounded-4 border">

                                    <div
                                        class="card-header bg-light">

                                        <h5 class="mb-0">

                                            <i
                                                class="bi bi-chat-left-dots-fill me-2">
                                            </i>

                                            Observaciones del pedido

                                        </h5>

                                    </div>

                                    <div class="card-body">

                                        <textarea
                                            class="form-control"
                                            id="comentarios"
                                            name="comentarios"
                                            rows="5"
                                            placeholder="Ejemplo: Llamar antes de llegar, dejar en recepción, tocar el timbre, etc."></textarea>

                                        <small
                                            class="text-muted d-block mt-2">

                                            Estas observaciones serán visibles únicamente para este pedido.

                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        <!--=========================================
        RESUMEN
        ==========================================-->

        <div class="col-lg-4">

            <div class="checkout-resumen">


                <!--=====================================
                RESUMEN PEDIDO
                ======================================-->

                <div
                    class="card border-0 shadow rounded-4 overflow-hidden mb-4">

                    <div
                        class="card-header bg-success text-white py-3">

                        <h4 class="mb-0 fw-bold">

                            <i
                                class="bi bi-bag-check-fill me-2">
                            </i>

                            Resumen del Pedido

                        </h4>

                    </div>


                    <div class="card-body p-0">


                        <?php

                        mysqli_data_seek(
                            $productos,
                            0
                        );

                        ?>


                        <?php while (
                            $item =
                            mysqli_fetch_assoc($productos)
                        ):

                            $cantidadItem =
                                intval(
                                    $item["cantidad"] ?? 0
                                );

                            $precioItem =
                                floatval(
                                    $item["precio"] ?? 0
                                );

                            $sub =
                                $precioItem *
                                $cantidadItem;

                        ?>


                            <div
                                class="p-3 border-bottom">

                                <div
                                    class="d-flex align-items-center">


                                    <!-- IMAGEN -->

                                    <div
                                        style="width:80px;">

                                        <?php if (
                                            !empty(
                                                $item["imagen"]
                                            )
                                        ): ?>

                                            <img
                                                src="mostrar_imagen.php?id=<?= intval($item["imagen"]) ?>"
                                                class="img-fluid rounded-3 border"
                                                alt="Producto">

                                        <?php else: ?>

                                            <img
                                                src="assets/img/sin-imagen.png"
                                                class="img-fluid rounded-3 border"
                                                alt="Sin imagen">

                                        <?php endif; ?>

                                    </div>


                                    <!-- INFORMACIÓN -->

                                    <div
                                        class="ms-3 flex-grow-1">

                                        <h6
                                            class="fw-bold mb-1">

                                            <?= htmlspecialchars(
                                                $item["nombre"] ?? ""
                                            ) ?>

                                        </h6>


                                        <small
                                            class="text-muted">

                                            Código:

                                            <?= htmlspecialchars(
                                                $item["codigo"] ?? ""
                                            ) ?>

                                        </small>


                                        <br>


                                        <span
                                            class="badge bg-light text-dark mt-2">

                                            <?= $cantidadItem ?>

                                            ×

                                            S/

                                            <?= number_format(
                                                $precioItem,
                                                2
                                            ) ?>

                                        </span>

                                    </div>


                                    <!-- SUBTOTAL -->

                                    <div
                                        class="text-end">

                                        <div
                                            class="fw-bold fs-5 text-success">

                                            S/

                                            <?= number_format(
                                                $sub,
                                                2
                                            ) ?>

                                        </div>

                                    </div>

                                </div>

                            </div>


                        <?php endwhile; ?>


                        <!--=====================================
                        TOTALES
                        ======================================-->

                        <div
                            class="p-4 bg-light">


                            <!-- PRODUCTOS -->

                            <div
                                class="d-flex justify-content-between mb-3">

                                <span>

                                    Productos

                                </span>

                                <strong>

                                    <?= $totalProductos ?>

                                </strong>

                            </div>


                            <!-- SUBTOTAL -->

                            <div
                                class="d-flex justify-content-between mb-3">

                                <span>

                                    Subtotal

                                </span>

                                <strong>

                                    S/

                                    <?= number_format(
                                        $subtotal,
                                        2
                                    ) ?>

                                </strong>

                            </div>


                            <!-- ENVÍO -->

                            <div
                                class="d-flex justify-content-between mb-3">

                                <span>

                                    Envío

                                </span>

                                <span
                                    class="badge bg-success">

                                    GRATIS

                                </span>

                            </div>


                            <!-- IMPUESTO -->

                            <?php if (
                                $impuestoActivo == 1 &&
                                $impuestoTotal > 0
                            ): ?>

                                <div
                                    class="d-flex justify-content-between mb-3">

                                    <span>

                                        <?= htmlspecialchars(
                                            $nombreImpuesto
                                        ) ?>

                                        <?php if (
                                            $porcentajeImpuesto > 0
                                        ): ?>

                                            (
                                            <?= number_format(
                                                $porcentajeImpuesto,
                                                2
                                            ) ?>%
                                            )

                                        <?php endif; ?>

                                    </span>

                                    <strong>

                                        S/

                                        <?= number_format(
                                            $impuestoTotal,
                                            2
                                        ) ?>

                                    </strong>

                                </div>

                            <?php endif; ?>


                        </div>


                        <!--=====================================
                        INFORMACIÓN IMPUESTO INCLUIDO
                        ======================================-->

                        <?php if (
                            $impuestoActivo == 1 &&
                            $preciosIncluyenImpuesto == 1 &&
                            $impuestoTotal > 0
                        ): ?>

                            <div
                                class="px-4 pb-3 bg-light">

                                <small
                                    class="text-muted">

                                    <i
                                        class="bi bi-info-circle">
                                    </i>

                                    Los precios mostrados incluyen

                                    <?= htmlspecialchars(
                                        $nombreImpuesto
                                    ) ?>.

                                </small>

                            </div>

                        <?php endif; ?>


                        <!--=====================================
                        TOTAL
                        ======================================-->

                        <div
                            class="bg-success text-white text-center py-4">

                            <div
                                class="text-uppercase small">

                                Total a pagar

                            </div>

                            <div
                                class="display-5 fw-bold">

                                S/

                                <?= number_format(
                                    $total,
                                    2
                                ) ?>

                            </div>

                        </div>


                        <!--=====================================
                        COMPRA SEGURA
                        ======================================-->

                        <div class="p-4">


                            <div class="d-flex mb-3">

                                <i
                                    class="bi bi-shield-lock-fill text-success fs-3 me-3">
                                </i>

                                <div>

                                    <strong>
                                        Compra 100% segura
                                    </strong>

                                    <br>

                                    <small
                                        class="text-muted">

                                        Toda la información viaja cifrada.

                                    </small>

                                </div>

                            </div>


                            <div class="d-flex mb-3">

                                <i
                                    class="bi bi-truck text-primary fs-3 me-3">
                                </i>

                                <div>

                                    <strong>
                                        Envío confiable
                                    </strong>

                                    <br>

                                    <small
                                        class="text-muted">

                                        Tu pedido será preparado inmediatamente.

                                    </small>

                                </div>

                            </div>


                            <div class="d-flex">

                                <i
                                    class="bi bi-headset text-warning fs-3 me-3">
                                </i>

                                <div>

                                    <strong>
                                        Soporte
                                    </strong>

                                    <br>

                                    <small
                                        class="text-muted">

                                        Te acompañamos durante todo el proceso.

                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!--=====================================
                MÉTODOS DE PAGO
                ======================================-->

                <div
                    class="card border-0 shadow rounded-4 mb-4">

                    <div
                        class="card-header bg-primary text-white py-3">

                        <h5 class="mb-0 fw-bold">

                            <i
                                class="bi bi-credit-card-2-front-fill me-2">
                            </i>

                            Método de Pago

                        </h5>

                    </div>


                    <div class="card-body">

                        <?php

                        mysqli_data_seek(
                            $metodosPago,
                            0
                        );

                        $primero = true;

                        ?>


                        <?php while (
                            $metodo =
                            mysqli_fetch_assoc(
                                $metodosPago
                            )
                        ): ?>

                            <label
                                class="card border rounded-4 mb-3 shadow-sm cursor-pointer">

                                <div class="card-body">

                                    <div
                                        class="form-check d-flex align-items-center">

                                        <input
                                            class="form-check-input me-3"
                                            type="radio"
                                            name="id_metodo_pago"
                                            id="metodo<?= intval($metodo["id_metodo_pago"]) ?>"
                                            value="<?= intval($metodo["id_metodo_pago"]) ?>"
                                            <?= $primero ? "checked" : "" ?>>

                                        <label
                                            class="form-check-label w-100"
                                            for="metodo<?= intval($metodo["id_metodo_pago"]) ?>">

                                            <div
                                                class="d-flex justify-content-between align-items-center">

                                                <div>

                                                    <h6
                                                        class="fw-bold mb-1">

                                                        <?= htmlspecialchars(
                                                            $metodo["nombre"] ?? ""
                                                        ) ?>

                                                    </h6>

                                                    <small
                                                        class="text-muted">

                                                        Pago seguro y verificado.

                                                    </small>

                                                </div>


                                                <i
                                                    class="bi bi-shield-check text-success fs-3">
                                                </i>

                                            </div>

                                        </label>

                                    </div>

                                </div>

                            </label>

                        <?php

                            $primero = false;

                        endwhile;

                        ?>

                    </div>

                </div>


                <!--=====================================
                TÉRMINOS
                ======================================-->

                <div
                    class="card border-0 shadow rounded-4 mb-4">

                    <div class="card-body">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="aceptoTerminos">

                            <label
                                class="form-check-label"
                                for="aceptoTerminos">

                                He leído y acepto los

                                <a
                                    href="#"
                                    class="text-decoration-none">

                                    términos y condiciones

                                </a>

                                de compra.

                            </label>

                        </div>

                    </div>

                </div>


                <!--=====================================
                FINALIZAR
                ======================================-->

                <div class="d-grid mb-4">

                    <button
                        type="button"
                        class="btn btn-success btn-lg py-3 fw-bold shadow"
                        id="btnFinalizarCompra">

                        <i
                            class="bi bi-bag-check-fill me-2">
                        </i>

                        FINALIZAR PEDIDO

                    </button>

                </div>


                <!--=====================================
                INFORMACIÓN
                ======================================-->

                <div
                    class="card border-0 shadow rounded-4 mb-4">

                    <div class="card-body">


                        <div class="d-flex mb-3">

                            <i
                                class="bi bi-geo-alt-fill text-danger fs-2 me-3">
                            </i>

                            <div>

                                <h6
                                    class="fw-bold mb-1">

                                    Dirección de entrega

                                </h6>

                                <small
                                    class="text-muted">

                                    Se utilizará la dirección que acabas de ingresar para este pedido.

                                </small>

                            </div>

                        </div>


                        <hr>


                        <div class="d-flex mb-3">

                            <i
                                class="bi bi-bookmark-check-fill text-primary fs-2 me-3">
                            </i>

                            <div>

                                <h6
                                    class="fw-bold mb-1">

                                    Dirección principal

                                </h6>

                                <small
                                    class="text-muted">

                                    Solo se actualizará tu dirección registrada si marcas la opción

                                    <strong>
                                        "Guardar esta dirección como mi dirección principal"
                                    </strong>.

                                </small>

                            </div>

                        </div>


                        <hr>


                        <div class="d-flex">

                            <i
                                class="bi bi-clock-history text-success fs-2 me-3">
                            </i>

                            <div>

                                <h6
                                    class="fw-bold mb-1">

                                    Tiempo de atención

                                </h6>

                                <small
                                    class="text-muted">

                                    Una vez confirmado el pedido comenzaremos inmediatamente con su preparación.

                                </small>

                            </div>

                        </div>


                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<?php include "includes/carrito_offcanvas.php"; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script src="js/notificaciones.js"></script>

<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</script>

<script src="js/checkout.js"></script>

<script src="js/carrito.js"></script>


</body>

</html>