<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/cantidad_ventas.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");

$labelsMeses = [];
$datosMeses  = [];


/*=====================================================
=            OBTENER CANTIDAD DE VENTAS POR MES
=====================================================*/

$sql = "SELECT
            YEAR(fecha_venta) AS anio,
            MONTH(fecha_venta) AS mes,
            COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND fecha_venta BETWEEN ? AND ?
        GROUP BY YEAR(fecha_venta), MONTH(fecha_venta)
        ORDER BY YEAR(fecha_venta), MONTH(fecha_venta)";

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

$meses = [
    1  => "Ene",
    2  => "Feb",
    3  => "Mar",
    4  => "Abr",
    5  => "May",
    6  => "Jun",
    7  => "Jul",
    8  => "Ago",
    9  => "Sep",
    10 => "Oct",
    11 => "Nov",
    12 => "Dic"
];

$totalVentas = 0;

while ($fila = mysqli_fetch_assoc($resultado)) {

    $labelsMeses[] =
        $meses[(int)$fila["mes"]] . " " . $fila["anio"];

    $datosMeses[] = (int)$fila["total"];

    $totalVentas += (int)$fila["total"];
}


/*=====================================================
=            MES CON MÁS VENTAS
=====================================================*/

$mejorMes = "Sin datos";

if (!empty($datosMeses)) {

    $indice = array_keys(
        $datosMeses,
        max($datosMeses)
    )[0];

    $mejorMes = $labelsMeses[$indice];
}

?>

<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">

        <!-- TITULO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                Cantidad de Ventas

            </h5>

            <span class="badge bg-primary">

                Por mes

            </span>

        </div>


        <!-- GRAFICO -->

        <?php if (!empty($datosMeses)): ?>

            <div style="height:320px;">

                <canvas id="graficoCantidadVentas"></canvas>

            </div>

        <?php else: ?>

            <div class="text-center py-5">

                <i class="bi bi-bar-chart-fill display-5 text-muted"></i>

                <h6 class="mt-3">

                    No existen ventas registradas.

                </h6>

                <p class="text-muted mb-0">

                    Cuando existan ventas aparecerán aquí.

                </p>

            </div>

        <?php endif; ?>


        <hr>


        <!-- RESUMEN -->

        <div class="row text-center">

            <div class="col-6">

                <h5 class="fw-bold text-success">

                    <?= number_format($totalVentas); ?>

                </h5>

                <small class="text-muted">

                    Total ventas

                </small>

            </div>

            <div class="col-6">

                <h5 class="fw-bold text-primary">

                    <?= htmlspecialchars($mejorMes); ?>

                </h5>

                <small class="text-muted">

                    Mejor mes

                </small>

            </div>

        </div>

    </div>

</div>


<!--==================================================
=            DATOS PARA CHARTJS
===================================================-->

<script>
    const labelsCantidadVentas =
        <?= json_encode($labelsMeses); ?>;

    const datosCantidadVentas =
        <?= json_encode($datosMeses); ?>;
</script>

<?php if (!empty($datosMeses)): ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const canvas = document.getElementById(
                "graficoCantidadVentas"
            );

            if (!canvas) return;

            new Chart(canvas, {

                type: "bar",

                data: {

                    labels: labelsCantidadVentas,

                    datasets: [{

                        label: "Cantidad de ventas",

                        data: datosCantidadVentas,

                        borderWidth: 1,

                        borderRadius: 8,

                        maxBarThickness: 45

                    }]
                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {

                            display: false
                        }
                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            ticks: {

                                precision: 0
                            }
                        }
                    }
                }
            });

        });
    </script>

<?php endif; ?>