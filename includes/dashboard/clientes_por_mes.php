<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/clientes_por_mes.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;


//=====================================================
// CLIENTES POR MES (AÑO ACTUAL)
//=====================================================

$meses = [
    "Ene",
    "Feb",
    "Mar",
    "Abr",
    "May",
    "Jun",
    "Jul",
    "Ago",
    "Sep",
    "Oct",
    "Nov",
    "Dic"
];

$clientesMes = array_fill(0, 12, 0);

$sql = "SELECT
            MONTH(fecha_registro) AS mes,
            COUNT(*) AS total
        FROM clientes
        WHERE id_user = ?
        AND Eliminado = 0
        AND YEAR(fecha_registro) = YEAR(CURDATE())
        GROUP BY MONTH(fecha_registro)";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "i", $idUser);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

while ($fila = mysqli_fetch_assoc($resultado)) {

    $clientesMes[$fila["mes"] - 1] = (int)$fila["total"];
}


//=====================================================
// TOTAL CLIENTES DEL AÑO
//=====================================================

$totalClientesAno = array_sum($clientesMes);


//=====================================================
// MEJOR MES
//=====================================================

$mayorCantidad = max($clientesMes);

$mejorMes = "-";

if ($mayorCantidad > 0) {

    $indice = array_search($mayorCantidad, $clientesMes);

    $mejorMes = $meses[$indice];
}

?>

<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                Clientes obtenidos por mes

            </h5>

            <span class="badge bg-success">

                <?= date("Y"); ?>

            </span>

        </div>


        <canvas id="graficoClientesMes"></canvas>


        <hr>


        <div class="row text-center">

            <div class="col-6">

                <h4 class="fw-bold text-primary">

                    <?= $totalClientesAno; ?>

                </h4>

                <small class="text-muted">

                    Clientes del año

                </small>

            </div>


            <div class="col-6">

                <h4 class="fw-bold text-success">

                    <?= $mejorMes; ?>

                </h4>

                <small class="text-muted">

                    Mejor mes

                </small>

            </div>

        </div>

    </div>

</div>

<script>
    const labelsClientesMes = <?= json_encode($meses); ?>;

    const datosClientesMes = <?= json_encode($clientesMes); ?>;
</script>