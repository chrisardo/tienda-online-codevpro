<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/metodos_pago.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";


/*=====================================================
=            USUARIO LOGUEADO
=====================================================*/

$idUser = $_SESSION["idUser"] ?? 0;
$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");
/*=====================================================
=            MÉTODOS DE PAGO DEL MES
=====================================================*/

$sql = "SELECT

            mp.nombre,
            COUNT(tv.id_ticket_ventas) AS total

        FROM ticket_ventas tv

        INNER JOIN metodo_pago mp
            ON tv.id_metodo_pago = mp.id_metodo_pago

        WHERE tv.id_user = ? AND tv.estado_envio = 'ENTREGADO'
        AND tv.fecha_venta BETWEEN ? AND ?

        GROUP BY tv.id_metodo_pago

        ORDER BY total DESC";


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


/*=====================================================
=            RECORRER RESULTADOS
=====================================================*/

$metodos = [];

$totalVentas = 0;

while ($fila = mysqli_fetch_assoc($resultado)) {

    $metodos[] = $fila;

    $totalVentas += $fila["total"];
}


/*=====================================================
=            CALCULAR PORCENTAJES
=====================================================*/

$metodoMasUsado = "Sin registros";

$porcentajeMayor = 0;

foreach ($metodos as $key => $metodo) {

    $porcentaje = 0;

    if ($totalVentas > 0) {

        $porcentaje = round(($metodo["total"] / $totalVentas) * 100);
    }

    $metodos[$key]["porcentaje"] = $porcentaje;


    if ($porcentaje > $porcentajeMayor) {

        $porcentajeMayor = $porcentaje;

        $metodoMasUsado =
            $metodo["nombre"] . " (" . $porcentaje . "%)";
    }
}


/*=====================================================
=            DATOS PARA CHARTJS
=====================================================*/

$labels = [];
$datos = [];

foreach ($metodos as $item) {

    $labels[] = $item["nombre"];

    $datos[] = $item["porcentaje"];
}

?>


<script>
    const labelsMetodoPago = <?= json_encode($labels); ?>;

    const datosMetodoPago = <?= json_encode($datos); ?>;
</script>



<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">


        <!-- TITULO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                <?php if ($fechaInicio == $fechaFin): ?>

                    Métodos de pago (<?= date("d/m/Y", strtotime($fechaInicio)); ?>)

                <?php else: ?>

                    Métodos de pago

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



        <!-- GRAFICO -->

        <div class="mb-4">

            <canvas id="graficoMetodoPago"></canvas>

        </div>



        <!-- LISTA DE MÉTODOS -->

        <?php if (!empty($metodos)): ?>


            <?php foreach ($metodos as $item): ?>


                <div class="mb-3">

                    <div class="d-flex justify-content-between">

                        <span>

                            <?= htmlspecialchars($item["nombre"]); ?>

                        </span>

                        <strong>

                            <?= $item["porcentaje"]; ?>%

                        </strong>

                    </div>


                    <div class="progress mb-2"
                        style="height:6px;">


                        <div class="progress-bar"

                            style="width:<?= $item["porcentaje"]; ?>%">

                        </div>

                    </div>

                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="text-center py-4">

                <i class="bi bi-wallet2 display-6 text-muted"></i>

                <p class="text-muted mt-3">

                    Todavía no existen ventas registradas
                    este mes.

                </p>

            </div>


        <?php endif; ?>



        <!-- RESUMEN -->

        <hr>


        <div class="text-center">

            <small class="text-muted">

                Método más utilizado

            </small>


            <h6 class="fw-bold text-primary mt-2">

                <?= $metodoMasUsado; ?>

            </h6>

        </div>


    </div>

</div>