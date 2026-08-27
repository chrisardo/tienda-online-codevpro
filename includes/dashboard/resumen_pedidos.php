<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/resumen_pedidos.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;
$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");
//=====================================================
// TOTAL DE PEDIDOS DE HOY
//=====================================================

$totalPedidos = 0;

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

    $totalPedidos = $fila["total"];
}


//=====================================================
// PEDIDOS ENTREGADOS
//=====================================================

$entregados = 0;

$sql = "SELECT COUNT(*) AS total
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

    $entregados = $fila["total"];
}


//=====================================================
// PEDIDOS PENDIENTES
//=====================================================

$pendientes = 0;

$sql = "SELECT COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio IN (
            'PENDIENTE',
            'CONFIRMADO',
            'PREPARANDO',
            'ENVIADO'
        )
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

    $pendientes = $fila["total"];
}


//=====================================================
// PEDIDOS CANCELADOS
//=====================================================

$cancelados = 0;

$sql = "SELECT COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio = 'CANCELADO'
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

    $cancelados = $fila["total"];
}

//=====================================================
// PORCENTAJES
//=====================================================

$porcentajeEntregados = 0;
$porcentajePendientes = 0;
$porcentajeCancelados = 0;

if ($totalPedidos > 0) {

    $porcentajeEntregados = round(($entregados / $totalPedidos) * 100);

    $porcentajePendientes = round(($pendientes / $totalPedidos) * 100);

    $porcentajeCancelados = round(($cancelados / $totalPedidos) * 100);
}

?>


<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">


        <!-- TITULO -->

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="fw-bold mb-0">

                <?php if ($fechaInicio == $fechaFin): ?>

                    Resumen de pedidos (<?= date("d/m/Y", strtotime($fechaInicio)); ?>)

                <?php else: ?>

                    Resumen de pedidos

                <?php endif; ?>

            </h5>


            <span class="badge bg-success">
                <?php if (
                    $fechaInicio == date("Y-m-01") &&
                    $fechaFin == date("Y-m-d")
                ): ?>

                    Este Mes

                <?php else: ?>

                    Filtrado

                <?php endif; ?>
            </span>

        </div>



        <!-- TOTAL DE PEDIDOS -->

        <div class="text-center mb-3">

            <h2 class="fw-bold text-primary">

                <?php echo $totalPedidos; ?>

            </h2>

            <small class="text-muted">
                Pedidos registrados en el período seleccionado
            </small>

        </div>



        <!-- GRAFICO -->

        <?php if ($totalPedidos > 0): ?>

            <div class="text-center">

                <canvas id="graficoPedidos"></canvas>

            </div>

        <?php else: ?>

            <div class="text-center py-4">

                <i class="bi bi-bag-x fs-1 text-muted"></i>

                <p class="text-muted mt-3 mb-0">

                    No hay pedidos registrados hoy.

                </p>

            </div>

        <?php endif; ?>



        <hr>



        <!-- RESUMEN -->

        <div class="row text-center">


            <!-- ENTREGADOS -->

            <div class="col-4">

                <h6 class="text-success fw-bold">

                    <?php echo $porcentajeEntregados; ?>%

                </h6>

                <small class="text-muted">

                    Entregados

                </small>

            </div>



            <!-- PENDIENTES -->

            <div class="col-4">

                <h6 class="text-warning fw-bold">

                    <?php echo $porcentajePendientes; ?>%

                </h6>

                <small class="text-muted">

                    Pendientes

                </small>

            </div>



            <!-- CANCELADOS -->

            <div class="col-4">

                <h6 class="text-danger fw-bold">

                    <?php echo $porcentajeCancelados; ?>%

                </h6>

                <small class="text-muted">

                    Cancelados

                </small>

            </div>


        </div>


    </div>

</div>



<!--==================================================
DATOS DEL GRÁFICO
===================================================-->

<script>
    const pedidosEntregados = <?= $entregados; ?>;
    const pedidosPendientes = <?= $pendientes; ?>;
    const pedidosCancelados = <?= $cancelados; ?>;
</script>