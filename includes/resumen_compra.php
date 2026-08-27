<?php
// ======================================================
// CoDevPro Technology
// Archivo: includes/resumen_compra.php
// Módulo: Carrito de Compras
// Sistema: Inventa
//
// RESUMEN DE COMPRA
// ======================================================
//
// LÓGICA:
//
// 1. Obtiene el precio ACTUAL desde producto.precio.
//
// 2. El subtotal considera:
//       precio actual × cantidad
//
// 3. El impuesto se considera UNA SOLA VEZ POR PRODUCTO
//    DISTINTO.
//
//    Ejemplo:
//
//       Producto A
//       Precio: S/ 100.00
//       Cantidad: 5
//       IGV: 18%
//
//       Subtotal: S/ 500.00
//       Impuesto: S/ 18.00
//
//    NO:
//       S/ 90.00
//
// 4. Si el mismo producto aparece en varias filas del
//    carrito_online, se agrupa por idProducto y el
//    impuesto solamente se calcula una vez.
//
// 5. Si los precios incluyen impuesto:
//       impuesto = precio - base imponible
//
// 6. Si los precios NO incluyen impuesto:
//       impuesto = precio × porcentaje
//
// 7. El total:
//       - precios sin impuesto:
//             subtotal + impuesto
//
//       - precios con impuesto:
//             subtotal
//
// ======================================================



// ======================================================
// INICIAR SESIÓN
// ======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ======================================================
// CONEXIÓN
// ======================================================

require_once __DIR__ . "/../controladores/conexion.php";
require_once __DIR__ . "/../controladores/token_carrito.php";


// ======================================================
// TOKEN DEL CARRITO
// ======================================================

$token = obtenerTokenCarrito();


// ======================================================
// CLIENTE
// ======================================================

$idCliente = isset($_SESSION["idCliente"])
    ? intval($_SESSION["idCliente"])
    : 0;


// ======================================================
// VARIABLES GENERALES
// ======================================================

$productos = 0;

$subtotal = 0.00;

$impuestoTotal = 0.00;

$total = 0.00;


// ======================================================
// CONFIGURACIÓN DEL IMPUESTO
// ======================================================

$nombreImpuesto = "Impuesto";

$porcentajeImpuesto = 0.00;

$impuestoActivo = 0;

$preciosIncluyenImpuesto = 0;


// ======================================================
// ID DEL DUEÑO DEL PRODUCTO
// ======================================================

$idUser = 0;


// ======================================================
// OBTENER id_user DEL CARRITO
// ======================================================
//
// Se obtiene el propietario a partir del producto.
//
// ======================================================

if ($idCliente > 0) {

    // ==================================================
    // CLIENTE LOGUEADO
    // ==================================================

    $sqlUser = "
        SELECT
            p.id_user

        FROM carrito_online c

        INNER JOIN producto p
            ON p.idProducto = c.idProducto

        WHERE c.idCliente = ?
        AND c.estado = 'pendiente'
        AND p.Eliminado = 0

        ORDER BY c.idCarrito ASC

        LIMIT 1
    ";

    $stmtUser = mysqli_prepare(
        $conexion,
        $sqlUser
    );

    if ($stmtUser) {

        mysqli_stmt_bind_param(
            $stmtUser,
            "i",
            $idCliente
        );

        mysqli_stmt_execute(
            $stmtUser
        );

        $resultadoUser = mysqli_stmt_get_result(
            $stmtUser
        );

        if ($filaUser = mysqli_fetch_assoc(
            $resultadoUser
        )) {

            $idUser = intval(
                $filaUser["id_user"] ?? 0
            );
        }

        mysqli_stmt_close(
            $stmtUser
        );
    }

} else {

    // ==================================================
    // CLIENTE INVITADO
    // ==================================================

    $sqlUser = "
        SELECT
            p.id_user

        FROM carrito_online c

        INNER JOIN producto p
            ON p.idProducto = c.idProducto

        WHERE c.token = ?
        AND c.estado = 'pendiente'
        AND p.Eliminado = 0

        ORDER BY c.idCarrito ASC

        LIMIT 1
    ";

    $stmtUser = mysqli_prepare(
        $conexion,
        $sqlUser
    );

    if ($stmtUser) {

        mysqli_stmt_bind_param(
            $stmtUser,
            "s",
            $token
        );

        mysqli_stmt_execute(
            $stmtUser
        );

        $resultadoUser = mysqli_stmt_get_result(
            $stmtUser
        );

        if ($filaUser = mysqli_fetch_assoc(
            $resultadoUser
        )) {

            $idUser = intval(
                $filaUser["id_user"] ?? 0
            );
        }

        mysqli_stmt_close(
            $stmtUser
        );
    }
}


// ======================================================
// OBTENER CONFIGURACIÓN TRIBUTARIA
// ======================================================

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

    $stmtConfiguracion = mysqli_prepare(
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

            // ==========================================
            // IMPUESTO ACTIVO
            // ==========================================

            $impuestoActivo = intval(
                $configuracion["impuesto_activo"] ?? 0
            );


            // ==========================================
            // NOMBRE DEL IMPUESTO
            // ==========================================

            $nombreImpuesto = trim(
                $configuracion["nombre_impuesto"] ?? ""
            );

            if ($nombreImpuesto === "") {

                $nombreImpuesto = "Impuesto";
            }


            // ==========================================
            // PORCENTAJE
            // ==========================================

            $porcentajeImpuesto = floatval(
                $configuracion["porcentaje_impuesto"] ?? 0
            );


            // ==========================================
            // PRECIOS INCLUYEN IMPUESTO
            // ==========================================

            $preciosIncluyenImpuesto = intval(
                $configuracion["precios_incluyen_impuesto"] ?? 0
            );
        }

        mysqli_stmt_close(
            $stmtConfiguracion
        );
    }
}


// ======================================================
// OBTENER PRODUCTOS DEL CARRITO
// ======================================================
//
// IMPORTANTE:
//
// Se utiliza GROUP BY idProducto.
//
// Esto garantiza que:
//
// Producto 10 -> fila 1 -> cantidad 2
// Producto 10 -> fila 2 -> cantidad 3
//
// Se convierta en:
//
// Producto 10 -> cantidad 5
//
// De esta manera el impuesto del producto se calcula
// UNA SOLA VEZ.
//
// ======================================================

$resultadoCarrito = false;

$stmt = null;


// ======================================================
// CLIENTE LOGUEADO
// ======================================================

if ($idCliente > 0) {

    $sql = "
        SELECT

            c.idProducto,

            SUM(c.cantidad) AS cantidad,

            p.precio,
            p.aplica_impuesto,
            p.id_user

        FROM carrito_online c

        INNER JOIN producto p
            ON p.idProducto = c.idProducto

        WHERE c.idCliente = ?
        AND c.estado = 'pendiente'
        AND p.Eliminado = 0

        GROUP BY

            c.idProducto,
            p.precio,
            p.aplica_impuesto,
            p.id_user

        ORDER BY c.idProducto ASC
    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $idCliente
        );

        mysqli_stmt_execute(
            $stmt
        );

        $resultadoCarrito =
            mysqli_stmt_get_result(
                $stmt
            );
    }


// ======================================================
// CLIENTE INVITADO
// ======================================================

} else {

    $sql = "
        SELECT

            c.idProducto,

            SUM(c.cantidad) AS cantidad,

            p.precio,
            p.aplica_impuesto,
            p.id_user

        FROM carrito_online c

        INNER JOIN producto p
            ON p.idProducto = c.idProducto

        WHERE c.token = ?
        AND c.estado = 'pendiente'
        AND p.Eliminado = 0

        GROUP BY

            c.idProducto,
            p.precio,
            p.aplica_impuesto,
            p.id_user

        ORDER BY c.idProducto ASC
    ";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $token
        );

        mysqli_stmt_execute(
            $stmt
        );

        $resultadoCarrito =
            mysqli_stmt_get_result(
                $stmt
            );
    }
}


// ======================================================
// PROCESAR PRODUCTOS
// ======================================================

if ($resultadoCarrito) {

    while (
        $item =
        mysqli_fetch_assoc(
            $resultadoCarrito
        )
    ) {

        // ==================================================
        // CANTIDAD
        // ==================================================

        $cantidad = intval(
            $item["cantidad"] ?? 0
        );

        if ($cantidad <= 0) {
            continue;
        }


        // ==================================================
        // PRECIO ACTUAL
        // ==================================================

        $precio = floatval(
            $item["precio"] ?? 0
        );


        // ==================================================
        // SUBTOTAL DEL PRODUCTO
        // ==================================================
        //
        // El subtotal SÍ considera todas las unidades.
        //
        // Ejemplo:
        //
        // Precio = S/100
        // Cantidad = 5
        //
        // Subtotal = S/500
        //
        // ==================================================

        $subtotalProducto =
            $precio * $cantidad;


        // ==================================================
        // ACUMULAR CANTIDAD DE PRODUCTOS
        // ==================================================

        $productos += $cantidad;


        // ==================================================
        // ACUMULAR SUBTOTAL
        // ==================================================

        $subtotal +=
            $subtotalProducto;


        // ==================================================
        // APLICA IMPUESTO
        // ==================================================

        $aplicaImpuestoProducto =
            intval(
                $item["aplica_impuesto"] ?? 0
            );


        // ==================================================
        // CALCULAR IMPUESTO
        // ==================================================
        //
        // IMPORTANTE:
        //
        // En este punto cada $item representa UN SOLO
        // idProducto gracias al GROUP BY.
        //
        // Por lo tanto el impuesto se calcula UNA SOLA VEZ.
        //
        // NO se multiplica por $cantidad.
        //
        // ==================================================

        if (
            $impuestoActivo == 1
            &&
            $aplicaImpuestoProducto == 1
            &&
            $porcentajeImpuesto > 0
        ) {

            // ==================================================
            // PRECIO CON IMPUESTO INCLUIDO
            // ==================================================

            if ($preciosIncluyenImpuesto == 1) {

                // ==============================================
                // Extraer el impuesto de UNA unidad.
                // ==============================================

                $baseImponible =
                    $precio /
                    (
                        1 +
                        ($porcentajeImpuesto / 100)
                    );


                $impuestoProducto =
                    $precio -
                    $baseImponible;


            } else {

                // ==================================================
                // PRECIO SIN IMPUESTO
                // ==================================================
                //
                // El impuesto se calcula UNA SOLA VEZ sobre
                // el precio del producto.
                //
                // NO se multiplica por la cantidad.
                //
                // ==================================================

                $impuestoProducto =
                    $precio *
                    ($porcentajeImpuesto / 100);
            }


            // ==================================================
            // ACUMULAR IMPUESTO
            // ==================================================

            $impuestoTotal +=
                $impuestoProducto;
        }
    }


    // ==================================================
    // CERRAR STATEMENT
    // ==================================================

    if ($stmt) {

        mysqli_stmt_close(
            $stmt
        );
    }
}


// ======================================================
// REDONDEAR SUBTOTAL
// ======================================================

$subtotal = round(
    $subtotal,
    2
);


// ======================================================
// REDONDEAR IMPUESTO
// ======================================================

$impuestoTotal = round(
    $impuestoTotal,
    2
);


// ======================================================
// CALCULAR TOTAL
// ======================================================
//
// Si el precio NO incluye impuesto:
//
//     Total = Subtotal + Impuesto
//
// Si el precio YA incluye impuesto:
//
//     Total = Subtotal
//
// ======================================================

if (
    $impuestoActivo == 1
    &&
    $preciosIncluyenImpuesto == 0
) {

    $total =
        $subtotal +
        $impuestoTotal;

} else {

    $total =
        $subtotal;
}


// ======================================================
// REDONDEAR TOTAL
// ======================================================

$total = round(
    $total,
    2
);

?>

<!-- =====================================================
     RESUMEN DE COMPRA
====================================================== -->

<div class="card border-0 shadow-sm sticky-top">


    <!-- ==================================================
         ENCABEZADO
    ================================================== -->

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            <i class="bi bi-receipt"></i>

            Resumen de Compra

        </h5>

    </div>


    <!-- ==================================================
         CUERPO
    ================================================== -->

    <div class="card-body">


        <!-- ==============================================
             PRODUCTOS
        =============================================== -->

        <div class="d-flex justify-content-between mb-3">

            <span>

                Productos

            </span>

            <strong>

                <?= $productos ?>

            </strong>

        </div>


        <!-- ==============================================
             SUBTOTAL
        =============================================== -->

        <div class="d-flex justify-content-between mb-3">

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


        <!-- ==============================================
             IMPUESTO
        =============================================== -->

        <?php if (
            $impuestoActivo == 1
            &&
            $impuestoTotal > 0
        ): ?>

            <div class="d-flex justify-content-between mb-3">

                <span>

                    <?= htmlspecialchars(
                        $nombreImpuesto,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                    <?php if (
                        $porcentajeImpuesto > 0
                    ): ?>

                        (<?= number_format(
                            $porcentajeImpuesto,
                            2
                        ) ?>%)

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


        <!-- ==============================================
             TOTAL
        =============================================== -->

        <hr>

        <div class="d-flex justify-content-between">

            <h5 class="mb-0">

                Total

            </h5>

            <h4 class="text-primary mb-0">

                S/
                <?= number_format(
                    $total,
                    2
                ) ?>

            </h4>

        </div>


        <!-- ==============================================
             INFORMACIÓN IMPUESTO INCLUIDO
        =============================================== -->

        <?php if (
            $impuestoActivo == 1
            &&
            $preciosIncluyenImpuesto == 1
            &&
            $impuestoTotal > 0
        ): ?>

            <div class="mt-3">

                <small class="text-muted">

                    <i class="bi bi-info-circle"></i>

                    Los precios mostrados incluyen
                    <?= htmlspecialchars(
                        $nombreImpuesto,
                        ENT_QUOTES,
                        "UTF-8")
                    ?>

                </small>

            </div>

        <?php endif; ?>


        <!-- ==============================================
             CONTINUAR COMPRA
        =============================================== -->

        <hr>

        <?php if (
            isset($_SESSION["idCliente"])
            &&
            intval($_SESSION["idCliente"]) > 0
        ): ?>

            <a
                href="checkout.php"
                class="btn btn-success w-100 mb-2">

                <i class="bi bi-credit-card"></i>

                Continuar Compra

            </a>

        <?php else: ?>

            <button
                type="button"
                id="btnLoginCheckout"
                class="btn btn-success w-100 mb-2">

                <i class="bi bi-credit-card"></i>

                Continuar Compra

            </button>

        <?php endif; ?>


        <!-- ==============================================
             SEGUIR COMPRANDO
        =============================================== -->

        <a
            href="tienda.php"
            class="btn btn-outline-primary w-100 mb-2">

            <i class="bi bi-shop"></i>

            Seguir Comprando

        </a>


        <!-- ==============================================
             VACIAR CARRITO
        =============================================== -->

        <button
            type="button"
            class="btn btn-outline-danger w-100"
            id="btnVaciarCarrito">

            <i class="bi bi-trash"></i>

            Vaciar Carrito

        </button>

    </div>

</div>