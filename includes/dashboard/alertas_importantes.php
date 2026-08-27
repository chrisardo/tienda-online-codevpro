<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/alertas_importantes.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;


/*=====================================================
=            STOCK BAJO
=====================================================*/

$stockBajo = 0;

$sql = "SELECT COUNT(*) AS total
        FROM producto
        WHERE id_user = ?
        AND Eliminado = 0
        AND stock <= 5";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$fila = mysqli_fetch_assoc($resultado);

$stockBajo = $fila["total"];


/*=====================================================
=            PEDIDOS PENDIENTES
=====================================================*/

$pedidosPendientes = 0;

$sql = "SELECT COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio IN(
            'PENDIENTE',
            'CONFIRMADO',
            'PREPARANDO'
        )";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$fila = mysqli_fetch_assoc($resultado);

$pedidosPendientes = $fila["total"];


/*=====================================================
=            TESTIMONIOS PENDIENTES
=====================================================*/

$testimoniosPendientes = 0;

$sql = "SELECT COUNT(*) AS total
        FROM testimonios
        WHERE id_user = ?
        AND estado = 'PENDIENTE'";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$fila = mysqli_fetch_assoc($resultado);

$testimoniosPendientes = $fila["total"];


/*=====================================================
=            FAVORITOS
=====================================================*/

$favoritos = 0;

$sql = "SELECT COUNT(*) AS total
        FROM favoritos f
        INNER JOIN producto p
        ON f.idProducto = p.idProducto
        WHERE p.id_user = ?";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$fila = mysqli_fetch_assoc($resultado);

$favoritos = $fila["total"];


/*=====================================================
=            NUEVOS CLIENTES
=====================================================*/

$nuevosClientes = 0;

$sql = "SELECT COUNT(*) AS total
        FROM clientes
        WHERE id_user = ?
        AND fecha_registro = CURDATE()
        AND Eliminado = 0";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$fila = mysqli_fetch_assoc($resultado);

$nuevosClientes = $fila["total"];


/*=====================================================
=            PEDIDOS CANCELADOS HOY
=====================================================*/

$cancelados = 0;

$sql = "SELECT COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio = 'CANCELADO'
        AND fecha_venta = CURDATE()";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$fila = mysqli_fetch_assoc($resultado);

$cancelados = $fila["total"];


/*=====================================================
=            TOTAL DE ALERTAS
=====================================================*/

$totalAlertas = 0;

$totalAlertas += $stockBajo;
$totalAlertas += $pedidosPendientes;
$totalAlertas += $testimoniosPendientes;
$totalAlertas += $favoritos;
$totalAlertas += $nuevosClientes;
$totalAlertas += $cancelados;

?>


<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">


        <!-- TITULO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                Alertas Importantes

            </h5>

            <span class="badge bg-danger">

                <?= $totalAlertas; ?>

            </span>

        </div>



        <!-- STOCK BAJO -->

        <div class="alerta-item">

            <div class="d-flex align-items-center">

                <div class="alerta-icono bg-danger-subtle text-danger">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>

                <div class="ms-3 flex-grow-1">

                    <div class="alerta-titulo">

                        Stock bajo

                    </div>

                    <div class="alerta-texto">

                        Productos por reponer.

                    </div>

                </div>

                <div class="alerta-numero text-danger">

                    <?= $stockBajo; ?>

                </div>

            </div>

        </div>



        <!-- PEDIDOS -->

        <div class="alerta-item">

            <div class="d-flex align-items-center">

                <div class="alerta-icono bg-warning-subtle text-warning">

                    <i class="bi bi-bag-fill"></i>

                </div>

                <div class="ms-3 flex-grow-1">

                    <div class="alerta-titulo">

                        Pedidos pendientes

                    </div>

                    <div class="alerta-texto">

                        Esperando confirmación.

                    </div>

                </div>

                <div class="alerta-numero text-warning">

                    <?= $pedidosPendientes; ?>

                </div>

            </div>

        </div>



        <!-- TESTIMONIOS -->

        <div class="alerta-item">

            <div class="d-flex align-items-center">

                <div class="alerta-icono bg-info-subtle text-info">

                    <i class="bi bi-chat-heart-fill"></i>

                </div>

                <div class="ms-3 flex-grow-1">

                    <div class="alerta-titulo">

                        Testimonios pendientes

                    </div>

                    <div class="alerta-texto">

                        Esperando aprobación.

                    </div>

                </div>

                <div class="alerta-numero text-info">

                    <?= $testimoniosPendientes; ?>

                </div>

            </div>

        </div>



        <!-- FAVORITOS -->

        <div class="alerta-item">

            <div class="d-flex align-items-center">

                <div class="alerta-icono bg-success-subtle text-success">

                    <i class="bi bi-heart-fill"></i>

                </div>

                <div class="ms-3 flex-grow-1">

                    <div class="alerta-titulo">

                        Productos favoritos

                    </div>

                    <div class="alerta-texto">

                        Alta demanda de clientes.

                    </div>

                </div>

                <div class="alerta-numero text-success">

                    <?= $favoritos; ?>

                </div>

            </div>

        </div>



        <!-- CLIENTES -->

        <div class="alerta-item">

            <div class="d-flex align-items-center">

                <div class="alerta-icono bg-primary-subtle text-primary">

                    <i class="bi bi-person-fill-add"></i>

                </div>

                <div class="ms-3 flex-grow-1">

                    <div class="alerta-titulo">

                        Nuevos clientes

                    </div>

                    <div class="alerta-texto">

                        Registrados hoy.

                    </div>

                </div>

                <div class="alerta-numero text-primary">

                    <?= $nuevosClientes; ?>

                </div>

            </div>

        </div>



        <!-- PEDIDOS CANCELADOS -->

        <div class="alerta-item mb-0">

            <div class="d-flex align-items-center">

                <div class="alerta-icono bg-secondary-subtle text-secondary">

                    <i class="bi bi-x-circle-fill"></i>

                </div>

                <div class="ms-3 flex-grow-1">

                    <div class="alerta-titulo">

                        Pedidos cancelados

                    </div>

                    <div class="alerta-texto">

                        Cancelados el día de hoy.

                    </div>

                </div>

                <div class="alerta-numero text-secondary">

                    <?= $cancelados; ?>

                </div>

            </div>

        </div>


    </div>

</div>