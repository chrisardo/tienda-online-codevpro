<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/productos_mas_vendidos.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";


/*=============================================
=            USUARIO LOGUEADO
=============================================*/

$idUser = $_SESSION["idUser"] ?? 0;
$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");
/*=============================================
=            PRODUCTOS MÁS VENDIDOS
=============================================*/

$sql = "SELECT

            p.idProducto,
            p.nombre,
            p.stock,

            IFNULL(SUM(dtv.cantidad_pedido_producto),0) AS vendidos,

            (
                SELECT imagenes
                FROM imagenes
                WHERE idProducto = p.idProducto
                ORDER BY orden ASC
                LIMIT 1

            ) AS imagen

        FROM producto p

        LEFT JOIN detalle_ticket_ventas dtv
            ON dtv.idProducto = p.idProducto

        LEFT JOIN ticket_ventas tv
            ON tv.id_ticket_ventas = dtv.id_ticket_ventas

        WHERE p.id_user = ?
        AND p.Eliminado = 0

        AND (
                tv.fecha_venta BETWEEN ? AND ?
                OR tv.fecha_venta IS NULL
            )

        GROUP BY p.idProducto

        ORDER BY vendidos DESC

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


$productos = [];


while ($fila = mysqli_fetch_assoc($resultado)) {

    $productos[] = $fila;
}


/*=============================================
=            PRODUCTO MÁS VENDIDO
=============================================*/

$maximoVendidos = 1;

if (!empty($productos)) {

    $maximoVendidos = max(array_column($productos, "vendidos"));

    if ($maximoVendidos <= 0) {

        $maximoVendidos = 1;
    }
}

?>


<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">


        <!-- TITULO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                Productos más vendidos del período

            </h5>

            <span class="badge bg-primary">

                <?php if (
                    $fechaInicio == date("Y-m-01") &&
                    $fechaFin == date("Y-m-d")
                ): ?>

                    Este Mes- Top 5

                <?php else: ?>

                    Filtrado

                <?php endif; ?>

            </span>

        </div>


        <?php if (!empty($productos)): ?>


            <?php foreach ($productos as $item):


                /*=============================================
                =            PORCENTAJE
                =============================================*/

                $porcentaje = round(($item["vendidos"] / $maximoVendidos) * 100);


                /*=============================================
                =            IMAGEN
                =============================================*/

                if (!empty($item["imagen"])) {

                    $imagenProducto = "data:image/jpeg;base64," .
                        base64_encode($item["imagen"]);
                } else {

                    $imagenProducto = "./assets/img/sin_imagen.png";
                }

            ?>


                <div class="producto-top mb-3">

                    <div class="d-flex align-items-center">


                        <!-- IMAGEN -->

                        <img src="<?= $imagenProducto; ?>"
                            alt="Producto">



                        <!-- INFORMACIÓN -->

                        <div class="ms-3 flex-grow-1">


                            <div class="nombre-producto fw-bold">

                                <?= htmlspecialchars($item["nombre"]); ?>

                            </div>


                            <div class="info-producto">

                                Vendidos:
                                <strong>

                                    <?= number_format($item["vendidos"]); ?>

                                </strong>

                                &nbsp; | &nbsp;

                                Stock:
                                <strong>

                                    <?= number_format($item["stock"]); ?>

                                </strong>

                            </div>


                            <!-- BARRA -->

                            <div class="progress mt-2"
                                style="height:8px;">


                                <div class="progress-bar"

                                    role="progressbar"

                                    style="width: <?= $porcentaje; ?>%;">

                                </div>

                            </div>


                        </div>



                        <!-- PORCENTAJE -->

                        <div class="text-success ms-3 fw-bold">

                            <?= $porcentaje; ?>%

                        </div>


                    </div>

                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="text-center py-5">

                <i class="bi bi-box-seam display-5 text-muted"></i>

                <h6 class="mt-3">

                    No existen productos registrados.

                </h6>

                <p class="text-muted mb-0">

                    Cuando realices ventas aparecerán aquí los
                    productos más vendidos.

                </p>

            </div>


        <?php endif; ?>


    </div>

</div>