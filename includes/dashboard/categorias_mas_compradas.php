<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/categorias_mas_compradas.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

$categorias = [];

/*=====================================================
=            TOP CATEGORÍAS MÁS COMPRADAS
=====================================================*/

$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");

$sql = "SELECT
            c.nombre,
            SUM(dtv.cantidad_pedido_producto) AS total_vendidos
        FROM detalle_ticket_ventas dtv

        INNER JOIN ticket_ventas tv
            ON dtv.id_ticket_ventas = tv.id_ticket_ventas

        INNER JOIN producto p
            ON dtv.idProducto = p.idProducto

        INNER JOIN categorias c
            ON p.id_categorias = c.id_categorias

        WHERE tv.id_user = ?
        AND tv.estado_envio = 'ENTREGADO'
        AND tv.fecha_venta BETWEEN ? AND ?

        GROUP BY c.id_categorias

        ORDER BY total_vendidos DESC

        LIMIT 5";

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

$totalGeneral = 0;

$temp = [];

while ($fila = mysqli_fetch_assoc($resultado)) {

    $totalGeneral += $fila["total_vendidos"];

    $temp[] = $fila;
}


/*=====================================================
=            CALCULAR PORCENTAJES
=====================================================*/

foreach ($temp as $fila) {

    $porcentaje = 0;

    if ($totalGeneral > 0) {

        $porcentaje = round(
            ($fila["total_vendidos"] * 100) / $totalGeneral
        );
    }

    $categorias[] = [

        "nombre" => $fila["nombre"],
        "cantidad" => $fila["total_vendidos"],
        "porcentaje" => $porcentaje
    ];
}
?>

<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">

        <!-- TITULO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                <?php if ($fechaInicio == $fechaFin): ?>

                    Categorías Más Compradas (<?= date("d/m/Y", strtotime($fechaInicio)); ?>)

                <?php else: ?>

                    Categorías Más Compradas

                <?php endif; ?>

            </h5>

            <span class="badge bg-primary">

                <?php if (
                    $fechaInicio == date("Y-m-01") &&
                    $fechaFin == date("Y-m-d")
                ): ?>

                    Este Mes - TOP 5

                <?php else: ?>

                    Filtrado

                <?php endif; ?>

            </span>

        </div>


        <?php if (!empty($categorias)): ?>

            <?php foreach ($categorias as $categoria): ?>

                <div class="mb-4">

                    <div class="d-flex justify-content-between">

                        <span>

                            <?= htmlspecialchars($categoria["nombre"]); ?>

                        </span>

                        <strong>

                            <?= $categoria["cantidad"]; ?>

                        </strong>

                    </div>

                    <div class="progress mt-2"
                        style="height:8px;">

                        <div class="progress-bar bg-info"
                            role="progressbar"
                            style="width: <?= $categoria["porcentaje"]; ?>%">

                        </div>

                    </div>

                    <small class="text-muted">

                        <?= $categoria["porcentaje"]; ?>%
                        de las ventas

                    </small>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="text-center py-5">

                <i class="bi bi-tags-fill fs-1 text-muted"></i>

                <p class="text-muted mt-3 mb-0">

                    No existen ventas entregadas.

                </p>

            </div>

        <?php endif; ?>


        <hr>

        <div class="text-center">

            <small class="text-muted">

                Categoría líder

            </small>

            <h6 class="fw-bold text-info mt-2">

                <?= !empty($categorias)
                    ? htmlspecialchars($categorias[0]["nombre"])
                    : "Sin datos"; ?>

            </h6>

        </div>

    </div>

</div>