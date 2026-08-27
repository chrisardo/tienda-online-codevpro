<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/clientes_mas_compran.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";


/*=============================================
=            VALIDAR USUARIO
=============================================*/

$idUser = $_SESSION["idUser"] ?? 0;
$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");

/*=============================================
=            OBTENER TOP CLIENTES
=============================================*/

$clientes = [];

$sql = "SELECT
            c.idCliente,
            c.nombre,
            c.imagen,

            COUNT(tv.id_ticket_ventas) AS total_pedidos,

            SUM(tv.total_venta) AS total_compras

        FROM ticket_ventas tv

        INNER JOIN clientes c
            ON tv.idCliente = c.idCliente

        WHERE tv.id_user = ?
        AND c.Eliminado = 0
        AND tv.estado_envio = 'ENTREGADO'
        AND tv.fecha_venta BETWEEN ? AND ?

        GROUP BY c.idCliente

        ORDER BY total_compras DESC

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


while ($fila = mysqli_fetch_assoc($resultado)) {

    $clientes[] = $fila;
}


/*=============================================
=            TOTAL COMPRADO
=============================================*/

$totalGeneral = 0;

foreach ($clientes as $item) {

    $totalGeneral += $item["total_compras"];
}
?>


<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">


        <!--=====================================
        =            TITULO
        =====================================-->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                <?php if ($fechaInicio == $fechaFin): ?>

                    CLIENTES QUE MÁS COMPRAN (<?= date("d/m/Y", strtotime($fechaInicio)); ?>)

                <?php else: ?>

                    CLIENTES QUE MAS COMPRAN

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



        <!--=====================================
        =            CLIENTES
        =====================================-->

        <?php if (!empty($clientes)): ?>


            <?php foreach ($clientes as $cliente): ?>


                <?php

                $porcentaje = 0;

                if ($totalGeneral > 0) {

                    $porcentaje = round(
                        ($cliente["total_compras"] * 100)
                            / $totalGeneral
                    );
                }


                // Imagen por defecto

                if (!empty($cliente["imagen"])) {

                    $imagenCliente = "data:image/jpeg;base64,"
                        . base64_encode($cliente["imagen"]);
                } else {

                    $imagenCliente = "img/default/user.png";
                }

                ?>


                <div class="producto-top mb-3">


                    <div class="d-flex align-items-center">


                        <!-- IMAGEN -->

                        <img src="<?= $imagenCliente; ?>"
                            alt="Cliente">



                        <!-- INFORMACIÓN -->

                        <div class="ms-3 flex-grow-1">


                            <div class="fw-semibold">

                                <?= htmlspecialchars($cliente["nombre"]); ?>

                            </div>


                            <small class="text-muted">

                                Pedidos:
                                <strong>

                                    <?= $cliente["total_pedidos"]; ?>

                                </strong>

                                &nbsp; | &nbsp;

                                Compró:

                                <strong>

                                    S/.
                                    <?= number_format($cliente["total_compras"], 2); ?>

                                </strong>

                            </small>



                            <!-- BARRA -->

                            <div class="progress mt-2"
                                style="height:8px;">

                                <div class="progress-bar bg-success"

                                    role="progressbar"

                                    style="width:<?= $porcentaje; ?>%">

                                </div>

                            </div>


                        </div>



                        <!-- PORCENTAJE -->

                        <div class="ms-3 fw-bold text-success">

                            <?= $porcentaje; ?>%

                        </div>


                    </div>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="text-center py-5">

                <i class="bi bi-people display-5 text-muted"></i>

                <p class="text-muted mt-3 mb-0">

                    Aún no existen compras registradas.

                </p>

            </div>


        <?php endif; ?>


        <!--=====================================
        =            RESUMEN
        =====================================-->

        <hr>


        <div class="text-center">

            <small class="text-muted">

                Total vendido a estos clientes

            </small>


            <h5 class="fw-bold text-primary mt-2">

                S/. <?= number_format($totalGeneral, 2); ?>

            </h5>

        </div>


    </div>

</div>