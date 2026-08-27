<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/ventas_7_dias.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;
$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");
/*=====================================================
=            VENTAS POR PERÍODO SELECCIONADO
=====================================================*/

$labelsVentas = [];
$datosVentas = [];
$totalVentasPeriodo = 0;

$sql = "SELECT
            fecha_venta,
            IFNULL(SUM(total_venta),0) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio = 'ENTREGADO'
        AND fecha_venta BETWEEN ? AND ?
        GROUP BY fecha_venta
        ORDER BY fecha_venta ASC";

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

while ($fila = mysqli_fetch_assoc($resultado)) {

    $labelsVentas[] = date(
        "d/m",
        strtotime($fila["fecha_venta"])
    );

    $datosVentas[] = (float)$fila["total"];

    $totalVentasPeriodo += (float)$fila["total"];
}
/*=====================================================
=            CRECIMIENTO DEL PERÍODO
=====================================================*/

$crecimiento = 0;

if ($fechaFin == date("Y-m-d")) {

    $diasPeriodo = (
        strtotime($fechaFin) -
        strtotime($fechaInicio)
    ) / 86400 + 1;

    $fechaInicioAnterior = date(
        "Y-m-d",
        strtotime($fechaInicio . " -{$diasPeriodo} days")
    );

    $fechaFinAnterior = date(
        "Y-m-d",
        strtotime($fechaInicio . " -1 day")
    );

    $totalPeriodoAnterior = 0;

    $sql = "SELECT
                IFNULL(SUM(total_venta),0) AS total
            FROM ticket_ventas
            WHERE id_user = ?
            AND estado_envio = 'ENTREGADO'
            AND fecha_venta BETWEEN ? AND ?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "iss",
        $idUser,
        $fechaInicioAnterior,
        $fechaFinAnterior
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if ($fila = mysqli_fetch_assoc($resultado)) {

        $totalPeriodoAnterior = (float)$fila["total"];
    }

    if ($totalPeriodoAnterior > 0) {

        $crecimiento = round(
            (
                ($totalVentasPeriodo - $totalPeriodoAnterior)
                / $totalPeriodoAnterior
            ) * 100
        );
    }
}

/*=====================================================
=            FORMATEAR TOTAL
=====================================================*/
$totalFormateado = "S/. " . number_format($totalVentasPeriodo, 2);
?>



<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">

        <!-- TITULO -->

        <div class="d-flex justify-content-between mb-4">

            <h5 class="fw-bold">

                <?php if ($fechaFin == date("Y-m-d")): ?>

                    Ventas (Últimos 7 días)

                <?php else: ?>

                    Ventas del período

                <?php endif; ?>

            </h5>


            <span class="badge bg-primary">

                <?php if ($fechaFin == date("Y-m-d")): ?>

                    Semanal

                <?php else: ?>

                    Filtrado

                <?php endif; ?>

            </span>

        </div>



        <!-- GRÁFICO -->

        <canvas id="graficoVentas"></canvas>


        <hr>



        <!-- RESUMEN -->

        <div class="row text-center">

            <div class="col">

                <h5 class="text-success fw-bold">

                    <?= $totalFormateado; ?>

                </h5>

                <small class="text-muted">

                    Total vendido

                </small>

            </div>
            <?php

            $mostrarCrecimiento = (
                $fechaFin == date("Y-m-d")
            );

            ?>

            <?php if ($mostrarCrecimiento): ?>

                <div class="col">

                    <h5 class="text-primary fw-bold">

                        <?= $crecimiento; ?>%

                    </h5>

                    <small class="text-muted">

                        Crecimiento

                    </small>

                </div>

            <?php endif; ?>
        </div>


    </div>

</div>



<!--==================================================
=            DATOS PARA EL GRÁFICO
===================================================-->

<script>
    const labelsVentas = <?= json_encode($labelsVentas); ?>;

    const datosVentas = <?= json_encode($datosVentas); ?>;
</script>