<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/tarjetas_estadisticas.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;
$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");

//=====================================================
// PEDIDOS DEL DÍA
//=====================================================

$pedidosHoy = 0;

$sql = "SELECT COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND fecha_venta BETWEEN ? AND ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $pedidosHoy = $fila["total"];
}


//=====================================================
// PEDIDOS DE AYER
//=====================================================

$pedidosAyer = 0;

$sql = "SELECT COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND fecha_venta = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $pedidosAyer = $fila["total"];
}


//=====================================================
// PORCENTAJE PEDIDOS
//=====================================================

$porcentajePedidos = 0;

if ($pedidosAyer > 0) {

    $porcentajePedidos = round(
        (($pedidosHoy - $pedidosAyer) / $pedidosAyer) * 100
    );
}


//=====================================================
// VENTAS DEL DÍA
//=====================================================

$totalVentasHoy = 0;

$sql = "SELECT IFNULL(SUM(total_venta),0) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio = 'ENTREGADO'
        AND fecha_venta BETWEEN ? AND ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $totalVentasHoy = $fila["total"];
}

$ventasHoy = "S/. " . number_format($totalVentasHoy, 2);


//=====================================================
// VENTAS DE AYER
//=====================================================

$totalVentasAyer = 0;

$sql = "SELECT IFNULL(SUM(total_venta),0) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND fecha_venta = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        AND estado_envio = 'ENTREGADO'";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $totalVentasAyer = $fila["total"];
}


$porcentajeVentas = 0;

if ($totalVentasAyer > 0) {

    $porcentajeVentas = round(
        (($totalVentasHoy - $totalVentasAyer) / $totalVentasAyer) * 100
    );
}


//=====================================================
// CLIENTES NUEVOS
//=====================================================

$clientesNuevos = 0;

$sql = "SELECT COUNT(*) AS total
        FROM clientes
        WHERE id_user = ?
        AND Eliminado = 0
        AND fecha_registro BETWEEN ? AND ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $clientesNuevos = $fila["total"];
}


//=====================================================
// PRODUCTOS ACTIVOS
//=====================================================

$productos = 0;

$sql = "SELECT COUNT(*) AS total
        FROM producto
        WHERE id_user = ?
        AND Eliminado = 0";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $productos = $fila["total"];
}


//=====================================================
// EMPLEADOS ACTIVOS
//=====================================================

$totalEmpleados = 0;

$sql = "SELECT COUNT(*) AS total
        FROM empleados
        WHERE id_user = ?
        AND estado = 'ACTIVO'";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $totalEmpleados = $fila["total"];
}


//=====================================================
// MONTO TOTAL DEL AÑO
//=====================================================

$totalAnual = 0;

$sql = "SELECT IFNULL(SUM(total_venta),0) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio='ENTREGADO'
        AND fecha_venta BETWEEN ? AND ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $totalAnual = $fila["total"];
}

$montoTotalAnual = "S/. " . number_format($totalAnual, 2);


//=====================================================
// TESTIMONIOS
//=====================================================

$testimonios = 0;

$sql = "SELECT COUNT(*) AS total
        FROM testimonios
        WHERE id_user = ?
        AND fecha BETWEEN ? AND ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $testimonios = $fila["total"];
}


//=====================================================
// TESTIMONIOS PENDIENTES
//=====================================================

$testimoniosPendientes = 0;

$sql = "SELECT COUNT(*) AS total
        FROM testimonios
        WHERE id_user = ?
        AND estado='PENDIENTE'
        AND fecha BETWEEN ? AND ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $testimoniosPendientes = $fila["total"];
}


//=====================================================
// FAVORITOS
//=====================================================

$favoritos = 0;

$sql = "SELECT COUNT(*) AS total
        FROM favoritos f
        INNER JOIN producto p
            ON f.idProducto = p.idProducto
        WHERE p.id_user = ?
        AND DATE(f.fecha) BETWEEN ? AND ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {

    $favoritos = $fila["total"];
}

?>


<div class="row g-4 mb-4">
    <h4> KPI (Indicador Clave de Desempeño)</h4>
    <!-- PEDIDOS -->

    <div class="col-xl-3 col-lg-3 col-md-3">

        <div class="card card-dashboard bg-pedidos">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Pedidos Hoy</small>

                    <h2><?= $pedidosHoy; ?></h2>

                    <small>

                        <i class="bi bi-arrow-up"></i>

                        <?= $porcentajePedidos; ?>% vs ayer

                    </small>

                </div>

                <div class="icon-circle">

                    <i class="bi bi-bag-fill"></i>

                </div>

            </div>

        </div>

    </div>



    <!-- VENTAS -->

    <div class="col-xl-3 col-lg-3 col-md-3">

        <div class="card card-dashboard bg-ventas">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Ventas del día</small>

                    <h2><?= $ventasHoy; ?></h2>

                    <small>

                        <i class="bi bi-arrow-up"></i>

                        <?= $porcentajeVentas; ?>% vs ayer

                    </small>

                </div>

                <div class="icon-circle">

                    <i class="bi bi-currency-dollar"></i>

                </div>

            </div>

        </div>

    </div>



    <!-- CLIENTES -->

    <div class="col-xl-3 col-lg-3 col-md-3">

        <div class="card card-dashboard bg-clientes">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Clientes nuevos</small>

                    <h2><?= $clientesNuevos; ?></h2>

                    <small>Registrados hoy</small>

                </div>

                <div class="icon-circle">

                    <i class="bi bi-person-fill"></i>

                </div>

            </div>

        </div>

    </div>



    <!-- PRODUCTOS -->

    <div class="col-xl-3 col-lg-3 col-md-3">

        <div class="card card-dashboard bg-productos">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Productos</small>

                    <h2><?= $productos; ?></h2>

                    <small>Activos</small>

                </div>

                <div class="icon-circle">

                    <i class="bi bi-box-seam"></i>

                </div>

            </div>

        </div>

    </div>



    <!-- EMPLEADOS -->

    <div class="col-xl-3 col-lg-3 col-md-3">
        <div class="card card-dashboard bg-info">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Empleados</small>

                    <h2><?= $totalEmpleados; ?></h2>

                    <small>Activos</small>

                </div>

                <div class="icon-circle">

                    <i class="bi bi-people-fill"></i>

                </div>

            </div>

        </div>

    </div>



    <!-- TOTAL DEL AÑO -->

    <div class="col-xl-3 col-lg-3 col-md-3">

        <div class="card card-dashboard bg-dark">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Total vendido</small>

                    <h2><?= $montoTotalAnual; ?></h2>

                    <small>
                        <?= date("d/m/Y", strtotime($fechaInicio)); ?>
                        -
                        <?= date("d/m/Y", strtotime($fechaFin)); ?>
                    </small>

                </div>

                <div class="icon-circle">

                    <i class="bi bi-cash-stack"></i>

                </div>

            </div>

        </div>

    </div>



    <!-- TESTIMONIOS -->

    <div class="col-xl-3 col-lg-3 col-md-3">

        <div class="card card-dashboard bg-testimonios">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Testimonios</small>

                    <h2><?= $testimonios; ?></h2>

                    <small class="text-warning fw-bold">

                        <?= $testimoniosPendientes; ?>

                        pendientes

                    </small>

                </div>

                <div class="icon-circle">

                    <i class="bi bi-chat-heart-fill"></i>

                </div>

            </div>

        </div>

    </div>



    <!-- FAVORITOS -->

    <div class="col-xl-3 col-lg-3 col-md-3">

        <div class="card card-dashboard bg-favoritos">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Favoritos</small>

                    <h2><?= $favoritos; ?></h2>

                    <small>En tus productos</small>

                </div>

                <div class="icon-circle">

                    <i class="bi bi-heart-fill"></i>

                </div>

            </div>

        </div>

    </div>


</div>