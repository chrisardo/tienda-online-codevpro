<?php
//=====================================================
// CoDevPro Technology
// includes/pedidos_clientes/kpis_pedidos.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "./controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;


/*=====================================================
=            CONTADORES
=====================================================*/

$pendientes  = 0;
$confirmados = 0;
$preparando  = 0;
$enviados    = 0;
$entregados  = 0;
$cancelados  = 0;


/*=====================================================
=            OBTENER ESTADÍSTICAS
=====================================================*/

$sql = "SELECT
            estado_envio,
            COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        GROUP BY estado_envio";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

while ($fila = mysqli_fetch_assoc($resultado)) {

    switch ($fila["estado_envio"]) {

        case "PENDIENTE":
            $pendientes = $fila["total"];
            break;

        case "CONFIRMADO":
            $confirmados = $fila["total"];
            break;

        case "PREPARANDO":
            $preparando = $fila["total"];
            break;

        case "ENVIADO":
            $enviados = $fila["total"];
            break;

        case "ENTREGADO":
            $entregados = $fila["total"];
            break;

        case "CANCELADO":
            $cancelados = $fila["total"];
            break;
    }
}

$totalPedidos =
    $pendientes +
    $confirmados +
    $preparando +
    $enviados +
    $entregados +
    $cancelados;

?>

<div class="row g-4 mb-4">

    <!-- TOTAL -->

    <div class="col-xl-2 col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm rounded-4 h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">

                            Total Pedidos

                        </small>

                        <h3 class="fw-bold mb-0">

                            <?= number_format($totalPedidos); ?>

                        </h3>

                    </div>

                    <div class="fs-1 text-primary">

                        <i class="bi bi-bag-fill"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- PENDIENTES -->

    <div class="col-xl-2 col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm rounded-4 h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">

                            Pendientes

                        </small>

                        <h3 class="fw-bold text-warning mb-0">

                            <?= number_format($pendientes); ?>

                        </h3>

                    </div>

                    <div class="fs-1 text-warning">

                        <i class="bi bi-hourglass-split"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- CONFIRMADOS -->

    <div class="col-xl-2 col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm rounded-4 h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">

                            Confirmados

                        </small>

                        <h3 class="fw-bold text-info mb-0">

                            <?= number_format($confirmados); ?>

                        </h3>

                    </div>

                    <div class="fs-1 text-info">

                        <i class="bi bi-check-circle-fill"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- PREPARANDO -->

    <div class="col-xl-2 col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm rounded-4 h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">

                            Preparando

                        </small>

                        <h3 class="fw-bold text-primary mb-0">

                            <?= number_format($preparando); ?>

                        </h3>

                    </div>

                    <div class="fs-1 text-primary">

                        <i class="bi bi-box-seam-fill"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ENVIADOS -->

    <div class="col-xl-2 col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm rounded-4 h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">

                            Enviados

                        </small>

                        <h3 class="fw-bold text-secondary mb-0">

                            <?= number_format($enviados); ?>

                        </h3>

                    </div>

                    <div class="fs-1 text-secondary">

                        <i class="bi bi-truck"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ENTREGADOS -->

    <div class="col-xl-2 col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm rounded-4 h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">

                            Entregados

                        </small>

                        <h3 class="fw-bold text-success mb-0">

                            <?= number_format($entregados); ?>

                        </h3>

                    </div>

                    <div class="fs-1 text-success">

                        <i class="bi bi-check2-square"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- CANCELADOS -->

    <div class="col-xl-2 col-lg-4 col-md-6">

        <div class="card border-0 shadow-sm rounded-4 h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">

                            Cancelados

                        </small>

                        <h3 class="fw-bold text-danger mb-0">

                            <?= number_format($cancelados); ?>

                        </h3>

                    </div>

                    <div class="fs-1 text-danger">

                        <i class="bi bi-x-circle-fill"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>